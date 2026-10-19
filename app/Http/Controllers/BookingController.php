<?php

namespace App\Http\Controllers;

use App\Http\Requests\BookingRequest;
use App\Models\Booking;
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
            $query->where(function ($q) use ($term): void {
                $q->where('booking_code', 'like', "%{$term}%")
                    ->orWhereHas('guest', function ($g) use ($term): void {
                        $g->where('first_name', 'like', "%{$term}%")
                            ->orWhere('last_name', 'like', "%{$term}%")
                            ->orWhere('email', 'like', "%{$term}%");
                    })
                    ->orWhereHas('villa', function ($v) use ($term): void {
                        $v->where('name', 'like', "%{$term}%")
                            ->orWhere('code', 'like', "%{$term}%");
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

        // Check for double booking conflicts
        $hasOverlap = Booking::overlapping(
            (int) $validated['villa_id'],
            $checkIn->toDateString(),
            $checkOut->toDateString()
        )->exists();

        if ($hasOverlap) {
            return back()->withInput()
                ->withErrors(['check_in' => 'The selected villa is already booked or occupied during these dates.'])
                ->with('error', 'The selected villa is already booked or occupied during these dates.');
        }

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $validated['total_nights'] = $nights;
        $validated['booking_code'] = 'BK-'.date('Y').'-'.strtoupper(Str::random(5));
        $validated['user_id'] = Auth::id();

        // Calculate total_amount strictly server-side to prevent client-side price tampering
        $nightlyRate = (float) $validated['nightly_rate'];
        $cleaningFee = (float) ($validated['cleaning_fee'] ?? 0);
        $serviceFee = (float) ($validated['service_fee'] ?? 0);
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $totalAmount = ($nights * $nightlyRate) + $cleaningFee + $serviceFee + $taxAmount;
        $validated['total_amount'] = $totalAmount;

        $paidAmount = min($totalAmount, (float) ($validated['paid_amount'] ?? 0));
        $validated['paid_amount'] = $paidAmount;
        $validated['balance_due'] = max(0, $totalAmount - $paidAmount);
        $validated['payment_status'] = $validated['balance_due'] <= 0 && $totalAmount > 0
            ? Booking::PAYMENT_PAID
            : ($paidAmount > 0 ? Booking::PAYMENT_PARTIAL : Booking::PAYMENT_PENDING);

        $booking = DB::transaction(function () use ($validated, $paidAmount, $request) {
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
                $booking->villa->update(['status' => Villa::STATUS_OCCUPIED]);
            }

            return $booking;
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Reservation {$booking->booking_code} confirmed successfully.");
    }

    public function show(Booking $booking): View
    {
        $booking->load(['villa', 'guest', 'user', 'payments' => fn ($q) => $q->latest()]);

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

        // Check for conflicts
        $hasOverlap = Booking::overlapping(
            (int) $validated['villa_id'],
            $checkIn->toDateString(),
            $checkOut->toDateString(),
            $booking->id
        )->exists();

        if ($hasOverlap) {
            return back()->withInput()
                ->withErrors(['check_in' => 'Date conflict: The selected villa is already booked during these dates.'])
                ->with('error', 'Date conflict: The selected villa is already booked during these dates.');
        }

        $nights = max(1, $checkIn->diffInDays($checkOut));
        $validated['total_nights'] = $nights;

        // Recalculate total_amount server-side
        $nightlyRate = (float) $validated['nightly_rate'];
        $cleaningFee = (float) ($validated['cleaning_fee'] ?? 0);
        $serviceFee = (float) ($validated['service_fee'] ?? 0);
        $taxAmount = (float) ($validated['tax_amount'] ?? 0);
        $validated['total_amount'] = ($nights * $nightlyRate) + $cleaningFee + $serviceFee + $taxAmount;

        DB::transaction(function () use ($booking, $validated) {
            $booking->update($validated);
            $booking->recalculatePaymentStatus();

            if ($booking->status === Booking::STATUS_CHECKED_IN) {
                $booking->villa->update(['status' => Villa::STATUS_OCCUPIED]);
            }
        });

        return redirect()->route('bookings.show', $booking)
            ->with('success', "Booking {$booking->booking_code} updated successfully.");
    }

    public function checkIn(Booking $booking): RedirectResponse
    {
        DB::transaction(function () use ($booking) {
            $booking->update(['status' => Booking::STATUS_CHECKED_IN]);
            $booking->villa->update(['status' => Villa::STATUS_OCCUPIED]);
        });

        return back()->with('success', "Guest has been checked in to {$booking->villa->name}.");
    }

    public function checkOut(Booking $booking): RedirectResponse
    {
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

        return back()->with('success', "Guest checked out. Villa status changed to 'Cleaning' and housekeeping task assigned.");
    }

    public function cancel(Request $request, Booking $booking): RedirectResponse
    {
        $request->validate([
            'cancellation_reason' => ['required', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($request, $booking) {
            $booking->update([
                'status' => Booking::STATUS_CANCELLED,
                'cancellation_reason' => $request->string('cancellation_reason'),
            ]);

            // If villa was occupied by this booking, restore it
            if ($booking->villa->status === Villa::STATUS_OCCUPIED) {
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
        $booking->load(['villa', 'guest', 'user', 'payments']);

        return view('bookings.invoice', [
            'booking' => $booking,
        ]);
    }

    public function destroy(Booking $booking): RedirectResponse
    {
        $code = $booking->booking_code;
        $booking->delete();

        return redirect()->route('bookings.index')
            ->with('success', "Booking {$code} deleted.");
    }
}
