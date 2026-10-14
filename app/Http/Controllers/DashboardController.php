<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\MaintenanceTask;
use App\Models\Payment;
use App\Models\Villa;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $today = Carbon::today()->toDateString();
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $totalVillas = Villa::count();
        $availableVillas = Villa::where('status', Villa::STATUS_AVAILABLE)->count();
        $occupiedVillas = Villa::where('status', Villa::STATUS_OCCUPIED)->count();
        $cleaningVillas = Villa::where('status', Villa::STATUS_CLEANING)->count();
        $maintenanceVillas = Villa::where('status', Villa::STATUS_MAINTENANCE)->count();
        $occupancyRate = $totalVillas > 0 ? round(($occupiedVillas / $totalVillas) * 100, 1) : 0;

        $totalGuests = Guest::count();
        $vipGuestsCount = Guest::vip()->count();

        // Revenue for current month based on completed payments
        $currentMonthRevenue = (float) Payment::where('status', Payment::STATUS_COMPLETED)
            ->whereBetween('payment_date', [$startOfMonth, $endOfMonth])
            ->sum('amount');

        // Today Check-ins and Check-outs
        $todayCheckIns = Booking::with(['villa', 'guest'])
            ->whereDate('check_in', $today)
            ->whereIn('status', [Booking::STATUS_CONFIRMED, Booking::STATUS_CHECKED_IN])
            ->get();

        $todayCheckOuts = Booking::with(['villa', 'guest'])
            ->whereDate('check_out', $today)
            ->whereIn('status', [Booking::STATUS_CHECKED_IN, Booking::STATUS_CHECKED_OUT])
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

        // All villas for the live status matrix
        $allVillas = Villa::orderBy('code')->get();

        // Monthly revenue history for Chart.js (last 6 months)
        $chartLabels = [];
        $chartRevenue = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthDate = Carbon::now()->subMonths($i);
            $monthKey = $monthDate->format('M Y');
            $mStart = $monthDate->copy()->startOfMonth();
            $mEnd = $monthDate->copy()->endOfMonth();

            $sum = (float) Payment::where('status', Payment::STATUS_COMPLETED)
                ->whereBetween('payment_date', [$mStart, $mEnd])
                ->sum('amount');

            $chartLabels[] = $monthKey;
            $chartRevenue[] = $sum;
        }

        // Villa category breakdown
        $categoryBreakdown = Villa::select('category', DB::raw('count(*) as count'))
            ->groupBy('category')
            ->pluck('count', 'category')
            ->toArray();

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
            'recentBookings' => $recentBookings,
            'urgentTasks' => $urgentTasks,
            'allVillas' => $allVillas,
            'chartLabels' => $chartLabels,
            'chartRevenue' => $chartRevenue,
            'categoryBreakdown' => $categoryBreakdown,
        ]);
    }
}
