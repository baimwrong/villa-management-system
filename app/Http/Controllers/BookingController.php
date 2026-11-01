<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
use App\Models\BookingCharge;
use App\Models\Guest;
use App\Models\MaintenanceTask;
use App\Models\Payment;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class BookingController extends Controller
{
    public function index(Request $request): View
    {
        $query = Booking::with(['villa', 'guest']);

        if ($request->filled('search')) {
            $term = $request->string('search');
            $operator = DB::getDriverName() === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($q) use ($term, $operator): void {
                $q->where('booking_code', $operator, "%{$term}%")
                    ->orWhereHas('guest', function ($g) use ($term, $operator): void {
                        $g->where('first_name', $operator, "%{$term}%")
                            ->orWhere('last_name', $operator, "%{$term}%")
                            ->orWhere('email', $operator, "%{$term}%");
                    })
                    ->orWhereHas('villa', function ($v) use ($term, $operator): void {
                        $v->where('name', $operator, "%{$term}%")
                            ->orWhere('code', $operator, "%{$term}%");
                    });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->string('payment_status'));
        }

        if ($request->filled('villa_id')) {
            $query->where('villa_id', $request->input('villa_id'));
        }

        $bookings = $query->latest('check_in')->paginate(10)->withQueryString();
        $villas = Villa::orderBy('name')->get();

        return view('bookings.index', [
            'bookings' => $bookings,
            'villas' => $villas,
            'filters' => $request->only(['search', 'status', 'payment_status', 'villa_id']),
        ]);
    }

    public function calendar(Request $request): View
    {
        $month = (int) $request->input('month', now()->month);
        $year = (int) $request->input('year', now()->year);

        $currentDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $startOfMonth = $currentDate->copy()->startOfMonth();
        $endOfMonth = $currentDate->copy()->endOfMonth();

        $prevMonthDate = $currentDate->copy()->subMonth();
        $nextMonthDate = $currentDate->copy()->addMonth();

        $daysInMonth = [];
        $temp = $startOfMonth->copy();
        while ($temp->lte($endOfMonth)) {
            $daysInMonth[] = [
                'date' => $temp->toDateString(),
                'day' => $temp->day,
                'dayName' => $temp->format('D'),
                'isWeekend' => $temp->isFriday() || $temp->isSaturday(),
                'isToday' => $temp->isToday(),
            ];
            $temp->addDay();
        }

        $villas = Villa::orderBy('code')->get();

        $bookings = Booking::with('guest')
            ->whereNotIn('status', [Booking::STATUS_CANCELLED, Booking::STATUS_NO_SHOW])
            ->whereDate('check_in', '<=', $endOfMonth->toDateString())
            ->whereDate('check_out', '>=', $startOfMonth->toDateString())
            ->get();

        $villaBookings = [];
        foreach ($villas as $villa) {
            $villaBookings[$villa->id] = $bookings->where('villa_id', $villa->id)->values();
        }

        return view('bookings.calendar', [
            'villas' => $villas,
            'daysInMonth' => $daysInMonth,
            'villaBookings' => $villaBookings,
            'currentMonth' => $month,
            'currentYear' => $year,
            'monthName' => $currentDate->format('F Y'),
            'prevMonth' => $prevMonthDate->month,
            'prevYear' => $prevMonthDate->year,
            'nextMonth' => $nextMonthDate->month,
            'nextYear' => $nextMonthDate->year,
        ]);
    }

    public function create(Request $request): View
    {
        $villas = Villa::orderBy('code')->get();
        $guests = Guest::orderBy('first_name')->get();
        $selectedVillaId = $request->input('villa_id');

        return view('bookings.create', [
            'villas' => $villas,
            'guests' => $guests,
            'selectedVillaId' => $selectedVillaId,
        ]);
    }

    public function store(BookingRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $checkIn = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $validated['total_nights'] = $nights;
        $validated['booking_code'] = 'BK-'.date('Y').'-'.strtoupper(Str::random(5));
        $validated['user_id'] = Auth::id();

        // Calculate total_amount strictly server-side factoring in weekend pricing if defined
        $villaModel = Villa::findOrFail((int) $validated['villa_id']);
        $nightlyRate = (float) $validated['nightly_rate'];
        $stayTariffs = $villaModel->calculateStayTotal($checkIn, $checkOut, $nightlyRate);
        $accommodationTotal = $stayTariffs['accommodation_total'];

        $cleaningFee = (float) ($validated['cleaning_fee'] ?? 0);
        $serviceFee = (float) ($validated['service_fee'] ?? 0);
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $totalAmount = $accommodationTotal + $cleaningFee + $serviceFee + $taxAmount;
        $validated['total_amount'] = $totalAmount;

        $paidAmount = min($totalAmount, (float) ($validated['paid_amount'] ?? 0));
        $validated['paid_amount'] = $paidAmount;
        $validated['balance_due'] = max(0, $totalAmount - $paidAmount);
        $validated['payment_status'] = $validated['balance_due'] <= 0 && $totalAmount > 0
            ? Booking::PAYMENT_PAID
            : ($paidAmount > 0 ? Booking::PAYMENT_PARTIAL : Booking::PAYMENT_PENDING);

        try {
            $booking = DB::transaction(function () use ($validated, $paidAmount, $request, $checkIn, $checkOut) {
                // Pessimistic lock on the target villa row to serialize concurrent booking attempts
                $villa = Villa::where('id', (int) $validated['villa_id'])->lockForUpdate()->firstOrFail();

                // Check for double booking conflicts within the locked transaction
                $hasOverlap = Booking::overlapping(
                    $villa->id,
                    $checkIn->toDateString(),
                    $checkOut->toDateString()
                )->exists();

                if ($hasOverlap) {
                    throw new \DomainException('The selected villa is already booked or occupied during these dates.');
                }

                $booking = Booking::create($validated);

                // Record initial payment if provided
                if ($paidAmount > 0) {
                    Payment::create([
                        'booking_id' => $booking->id,
                        'transaction_reference' => 'TXN-'.strtoupper(Str::random(10)),
                        'amount' => $paidAmount,
                        'payment_method' => $request->input('payment_method', Payment::METHOD_CREDIT_CARD),
                        'payment_date' => now(),
                        'status' => Payment::STATUS_COMPLETED,
                        'notes' => 'Initial booking payment',
                    ]);

                    $booking->guest->syncLifetimeMetrics();
                }

                // Update villa status if checked in
                if ($booking->status === Booking::STATUS_CHECKED_IN) {
                    $villa->update(['status' => Villa::STATUS_OCCUPIED]);
                }

                return $booking;
            });
        } catch (\DomainException $e) {
            return back()->withInput()
                ->withErrors(['check_in' => $e->getMessage()])
                ->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Reservation {$booking->booking_code} confirmed successfully.");
    }

    public function show(Booking $booking): View
    {
        $booking->load(['villa', 'guest', 'user', 'payments' => fn ($q) => $q->latest(), 'extraCharges.creator']);

        return view('bookings.show', [
            'booking' => $booking,
        ]);
    }

    public function edit(Booking $booking): View
    {
        $villas = Villa::orderBy('code')->get();
        $guests = Guest::orderBy('first_name')->get();

        return view('bookings.edit', [
            'booking' => $booking,
            'villas' => $villas,
            'guests' => $guests,
        ]);
    }

    public function update(BookingRequest $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validated();

        $checkIn = Carbon::parse($validated['check_in'])->startOfDay();
        $checkOut = Carbon::parse($validated['check_out'])->startOfDay();

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $validated['total_nights'] = $nights;

        // Recalculate total_amount server-side factoring in weekend pricing if defined
        $villaModel = Villa::findOrFail((int) $validated['villa_id']);
        $nightlyRate = (float) $validated['nightly_rate'];
        $stayTariffs = $villaModel->calculateStayTotal($checkIn, $checkOut, $nightlyRate);
        $accommodationTotal = $stayTariffs['accommodation_total'];

        $cleaningFee = (float) ($validated['cleaning_fee'] ?? 0);
        $serviceFee = (float) ($validated['service_fee'] ?? 0);
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $validated['total_amount'] = $accommodationTotal + $cleaningFee + $serviceFee + $taxAmount;

        try {
            DB::transaction(function () use ($booking, $validated, $checkIn, $checkOut) {
                $villa = Villa::where('id', (int) $validated['villa_id'])->lockForUpdate()->firstOrFail();

                // Check for conflicts inside pessimistic transaction lock
                $hasOverlap = Booking::overlapping(
                    $villa->id,
                    $checkIn->toDateString(),
                    $checkOut->toDateString(),
                    $booking->id
                )->exists();

                if ($hasOverlap) {
                    throw new \DomainException('Date conflict: The selected villa is already booked during these dates.');
                }

                $booking->update($validated);
                $booking->recalculatePaymentStatus();

                if ($booking->status === Booking::STATUS_CHECKED_IN) {
                    $villa->update(['status' => Villa::STATUS_OCCUPIED]);
                }
            });
        } catch (\DomainException $e) {
            return back()->withInput()
                ->withErrors(['check_in' => $e->getMessage()])
                ->with('error', $e->getMessage());
        }

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} updated successfully.");
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_CONFIRMED) {
            return back()->with('error', "Cannot check in: Booking {$booking->booking_code} is currently '{$booking->status}'. Only confirmed reservations can be checked in.");
        }

        if (in_array($booking->villa->status, [Villa::STATUS_OCCUPIED, Villa::STATUS_MAINTENANCE], true)) {
            return back()->with('error', "Cannot check in: Villa '{$booking->villa->name}' is currently {$booking->villa->status}.");
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => Booking::STATUS_CHECKED_IN]);
            $booking->villa->update(['status' => Villa::STATUS_OCCUPIED]);
        });

        return back()->with('success', "Guest has been checked in to {$booking->villa->name}.");
    }

    public function checkOut(Booking $booking): RedirectResponse
    {
        if ($booking->status !== Booking::STATUS_CHECKED_IN) {
            return back()->with('error', "Cannot check out: Booking {$booking->booking_code} is not currently checked in (status: '{$booking->status}').");
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => Booking::STATUS_CHECKED_OUT]);

            // Mark villa as needing cleaning
            $booking->villa->update(['status' => Villa::STATUS_CLEANING]);

            // Auto-create cleaning task
            MaintenanceTask::create([
                'villa_id' => $booking->villa_id,
                'assigned_to' => null,
                'title' => "Post-Checkout Cleaning - {$booking->villa->name}",
                'task_type' => MaintenanceTask::TYPE_CLEANING,
                'priority' => MaintenanceTask::PRIORITY_HIGH,
                'status' => MaintenanceTask::STATUS_PENDING,
                'cost' => $booking->cleaning_fee,
                'scheduled_at' => now(),
                'description' => "Immediate turnaround cleaning after checkout of booking {$booking->booking_code}.",
            ]);

            $booking->guest->syncLifetimeMetrics();
        });

        $message = "Guest checked out. Villa status changed to 'Cleaning' and housekeeping task assigned.";
        if ((float) $booking->balance_due > 0) {
            return back()->with('warning', $message.' NOTE: Guest has an unsettled balance of $'.number_format($booking->balance_due, 2).'.');
        }

        return back()->with('success', $message);
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        if (in_array($booking->status, [Booking::STATUS_CHECKED_OUT, Booking::STATUS_CANCELLED], true)) {
            return back()->with('error', "Booking {$booking->booking_code} is already {$booking->status}.");
        }

        if ($booking->status === Booking::STATUS_CHECKED_IN) {
            return back()->with('error', 'Cannot cancel an active stay where the guest is already checked in. Please process checkout instead.');
        }

        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $booking) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $request->string('cancellation_reason'),
            ]);

            // If villa was occupied or reserved by this booking, restore it
            if ($booking->villa->status === Villa::STATUS_OCCUPIED || $booking->villa->status === Villa::STATUS_RESERVED) {
                $booking->villa->update(['status' => Villa::STATUS_AVAILABLE]);
            }
        });

        return back()->with('success', "Booking {$booking->booking_code} has been cancelled.");
    }

    public function addPayment(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.max(0.01, (float) $booking->balance_due + 1000)],
            'payment_method' => ['required', 'string', 'in:credit_card,bank_transfer,cash,stripe'],
            'notes' => ['nullable', 'string', 'max:255'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            Payment::create([
                'booking_id' => $booking->id,
                'transaction_reference' => 'TXN-'.strtoupper(Str::random(10)),
                'amount' => (float) $validated['amount'],
                'payment_method' => $validated['payment_method'],
                'payment_date' => now(),
                'status' => Payment::STATUS_COMPLETED,
                'notes' => $validated['notes'] ?? 'Manual payment recorded via management console.',
            ]);

            $booking->recalculatePaymentStatus();
            $booking->guest->syncLifetimeMetrics();
        });

        return back()->with('success', 'Payment of $'.number_format($validated['amount'], 2).' recorded successfully.');
    }

    public function invoice(Booking $booking): View
    {
        $booking->load(['villa', 'guest', 'user', 'payments', 'extraCharges.creator']);

        return view('bookings.invoice', [
            'booking' => $booking,
        ]);
    }

    public function addCharge(Request $request, Booking $booking): RedirectResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'in:dining,spa,transportation,experience,damage,other'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:50000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($booking, $validated) {
            $booking->extraCharges()->create([
                'title' => $validated['title'],
                'category' => $validated['category'],
                'amount' => (float) $validated['amount'],
                'charged_at' => now(),
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            // Add charge to total_amount and recalculate balance
            $booking->total_amount = (float) $booking->total_amount + (float) $validated['amount'];
            $booking->recalculatePaymentStatus();
        });

        return back()->with('success', "Incidental charge '{$validated['title']}' of $".number_format($validated['amount'], 2).' posted to guest folio.');
    }

    public function destroyCharge(Booking $booking, BookingCharge $charge): RedirectResponse
    {
        if ($charge->booking_id !== $booking->id) {
            abort(404);
        }

        DB::transaction(function () use ($booking, $charge) {
            $amount = (float) $charge->amount;
            $charge->delete();

            $booking->total_amount = max(0, (float) $booking->total_amount - $amount);
            $booking->recalculatePaymentStatus();
        });

        return back()->with('success', 'Incidental charge voided from folio.');
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $code = $booking->booking_code;
        $booking->delete();

        return redirect()->route('bookings.index')
            ->with('success', "Booking {$code} deleted.");
    }
}
