<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\MaintenanceTask;
use App\Models\Payment;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $allVillas = Villa::orderBy('code')->get();
        $totalVillas = $allVillas->count();
        $availableVillas = $allVillas->where('status', Villa::STATUS_AVAILABLE)->count();
        $occupiedVillas = $allVillas->where('status', Villa::STATUS_OCCUPIED)->count();
        $cleaningVillas = $allVillas->where('status', Villa::STATUS_CLEANING)->count();
        $maintenanceVillas = $allVillas->where('status', Villa::STATUS_MAINTENANCE)->count();
        $occupancyRate = $totalVillas > 0 ? round(($occupiedVillas / $totalVillas) * 100, 1) : 0;

        $totalGuests = Guest::count();
        $vipGuestsCount = Guest::vip()->count();

        // 6-month historical revenue (single query instead of N loop queries)
        $sixMonthsAgo = Carbon::now()->subMonths(5)->startOfMonth();
        $recentPayments = Payment::where('status', Payment::STATUS_COMPLETED)
            ->where('payment_date', '>=', $sixMonthsAgo)
            ->get(['amount', 'payment_date']);

        $chartLabels = [];
        $chartRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('M Y');
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();

            $sum = (float) $recentPayments
                ->filter(fn ($p) => $p->payment_date >= $mStart && $p->payment_date <= $mEnd)
                ->sum('amount');

            $chartLabels[] = $monthKey;
            $chartRevenue[] = $sum;
        }

        $currentMonthRevenue = (float) ($chartRevenue[5] ?? 0);

        // Today Check-ins and Check-outs
        $todayCheckIns = Booking::with(['villa', 'guest'])
            ->whereDate('check_in', $today)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->get();

        $todayCheckOuts = Booking::with(['villa', 'guest'])
            ->whereDate('check_out', $today)
            ->whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
            ->get();

        // Overstay Alerts: Still checked-in past scheduled check-out date
        $overdueCheckOuts = Booking::with(['villa', 'guest'])
            ->whereDate('check_out', '<', $today)
            ->where('status', Booking::STATUS_CHECKED_IN)
            ->get();

        // Recent bookings
        $recentBookings = Booking::with(['villa', 'guest'])
            ->latest()
            ->take(6)
            ->get();

        // Priority Maintenance tasks
        $urgentTasks = MaintenanceTask::with(['villa', 'assignedStaff'])
            ->whereIn('status', [MaintenanceTask::STATUS_PENDING, MaintenanceTask::STATUS_IN_PROGRESS])
            ->orderByRaw("CASE priority WHEN 'urgent' THEN 1 WHEN 'high' THEN 2 WHEN 'medium' THEN 3 ELSE 4 END")
            ->take(5)
            ->get();

        // Villa category breakdown computed in-memory from loaded villas
        $categoryBreakdown = $allVillas->groupBy('category')->map->count()->toArray();

        return view('dashboard.index', [
            'totalVillas' => $totalVillas,
            'availableVillas' => $availableVillas,
            'occupiedVillas' => $occupiedVillas,
            'cleaningVillas' => $cleaningVillas,
            'maintenanceVillas' => $maintenanceVillas,
            'occupancyRate' => $occupancyRate,
            'totalGuests' => $totalGuests,
            'vipGuestsCount' => $vipGuestsCount,
            'currentMonthRevenue' => $currentMonthRevenue,
            'todayCheckIns' => $todayCheckIns,
            'todayCheckOuts' => $todayCheckOuts,
            'overdueCheckOuts' => $overdueCheckOuts,
            'recentBookings' => $recentBookings,
            'urgentTasks' => $urgentTasks,
            'allVillas' => $allVillas,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }
}
