<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Repair;
use App\Models\Asset;
use App\Models\SparePart;
use App\Models\Department;
use App\Models\DataRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // Base query depending on user role
        $repairQuery = Repair::query();
        $dataRequestQuery = DataRequest::query();

        if ($user->isUser() && $user->department_id) {
            // General user sees tickets from their department
            $repairQuery->where('department_id', $user->department_id);
            $dataRequestQuery->where(function($q) use ($user) {
                $q->where('user_id', $user->id)
                  ->orWhere('department_id', $user->department_id);
            });
        }

        // Summary counts
        $totalRepairs = (clone $repairQuery)->count();
        $pendingRepairs = (clone $repairQuery)->where('status', 'pending')->count();
        $inProgressRepairs = (clone $repairQuery)->whereIn('status', ['in_progress', 'waiting_parts'])->count();
        $completedRepairsThisMonth = (clone $repairQuery)
            ->where('status', 'completed')
            ->whereMonth('completed_at', Carbon::now()->month)
            ->whereYear('completed_at', Carbon::now()->year)
            ->count();

        // Data Request counts
        $totalDataRequests = (clone $dataRequestQuery)->count();
        $pendingDataRequests = (clone $dataRequestQuery)->where('status', 'pending')->count();
        $recentDataRequests = (clone $dataRequestQuery)->with(['department', 'user'])->latest()->take(5)->get();

        // Assets stats
        $totalAssets = Asset::count();
        $brokenAssets = Asset::whereIn('status', ['repairing', 'broken'])->count();
        $spareAssets = Asset::where('status', 'spare')->count();

        // Low stock parts
        $lowStockParts = SparePart::whereRaw('stock_quantity <= minimum_quantity')->get();

        // Recent repairs
        $recentRepairs = (clone $repairQuery)
            ->with(['department', 'asset', 'technician', 'requester'])
            ->latest()
            ->take(7)
            ->get();

        // Chart 1: Monthly repair trend (last 6 months)
        $months = [];
        $monthlyCounts = [];
        for ($i = 5; $i >= 0; $i--) {
            $date = Carbon::now()->subMonths($i);
            $monthName = $this->getThaiMonth($date->month) . ' ' . ($date->year + 543);
            $count = Repair::whereMonth('created_at', $date->month)
                ->whereYear('created_at', $date->year)
                ->count();
            $months[] = $monthName;
            $monthlyCounts[] = $count;
        }

        // Chart 2: Repairs by status
        $statusCounts = [
            'pending' => Repair::where('status', 'pending')->count(),
            'in_progress' => Repair::where('status', 'in_progress')->count(),
            'waiting_parts' => Repair::where('status', 'waiting_parts')->count(),
            'completed' => Repair::where('status', 'completed')->count(),
            'external' => Repair::where('status', 'external')->count(),
            'cancelled' => Repair::where('status', 'cancelled')->count(),
        ];

        // Chart 3: Repairs by department (Top 5)
        $topDepartments = Department::withCount('repairs')
            ->orderByDesc('repairs_count')
            ->take(5)
            ->get();

        return view('dashboard.index', compact(
            'totalRepairs',
            'pendingRepairs',
            'inProgressRepairs',
            'completedRepairsThisMonth',
            'totalAssets',
            'brokenAssets',
            'spareAssets',
            'lowStockParts',
            'recentRepairs',
            'months',
            'monthlyCounts',
            'statusCounts',
            'topDepartments',
            'totalDataRequests',
            'pendingDataRequests',
            'recentDataRequests'
        ));
    }

    private function getThaiMonth($monthNumber): string
    {
        $thaiMonths = [
            1 => 'ม.ค.', 2 => 'ก.พ.', 3 => 'มี.ค.', 4 => 'เม.ย.',
            5 => 'พ.ค.', 6 => 'มิ.ย.', 7 => 'ก.ค.', 8 => 'ส.ค.',
            9 => 'ก.ย.', 10 => 'ต.ค.', 11 => 'พ.ย.', 12 => 'ธ.ค.'
        ];
        return $thaiMonths[(int)$monthNumber] ?? '';
    }
}
