<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Invoice;
use App\Models\OrderItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $today = Carbon::today();
        $monthStart = Carbon::now()->startOfMonth();

        $todayInvoices = Invoice::whereDate('paid_at', $today);
        $monthInvoices = Invoice::where('paid_at', '>=', $monthStart);

        $todaySales = (clone $todayInvoices)->sum('total');
        $todayOrders = (clone $todayInvoices)->count();
        $todayDiscounts = (clone $todayInvoices)->sum('discount_amount');
        $avgInvoice = $todayOrders > 0 ? $todaySales / $todayOrders : 0;

        $monthSales = (clone $monthInvoices)->sum('total');
        $monthOrders = (clone $monthInvoices)->count();

        $tables = CafeTable::where('is_active', true)->get();
        $tableStats = [
            'total' => $tables->count(),
            'available' => $tables->where('status', 'available')->count(),
            'occupied' => $tables->where('status', 'occupied')->count(),
            'awaiting_payment' => $tables->where('status', 'awaiting_payment')->count(),
        ];

        $salesChart = Invoice::query()
            ->where('paid_at', '>=', Carbon::today()->subDays(13)->startOfDay())
            ->select(DB::raw('date(paid_at) as day'), DB::raw('sum(total) as total'))
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chartLabels = [];
        $chartValues = [];
        for ($i = 13; $i >= 0; $i--) {
            $day = Carbon::today()->subDays($i);
            $key = $day->toDateString();
            $chartLabels[] = $day->format('d/m');
            $chartValues[] = (float) ($salesChart[$key] ?? 0);
        }

        $latestInvoices = Invoice::with('order')
            ->latest('paid_at')
            ->limit(8)
            ->get();

        $bestSellers = OrderItem::query()
            ->select('item_name', DB::raw('sum(quantity) as qty'), DB::raw('sum(line_total) as revenue'))
            ->whereHas('order', fn ($q) => $q->where('status', 'paid')->where('paid_at', '>=', $monthStart))
            ->groupBy('item_name')
            ->orderByDesc('qty')
            ->limit(5)
            ->get();

        return view('admin.dashboard', compact(
            'todaySales',
            'todayOrders',
            'todayDiscounts',
            'avgInvoice',
            'monthSales',
            'monthOrders',
            'tableStats',
            'chartLabels',
            'chartValues',
            'latestInvoices',
            'bestSellers'
        ));
    }
}
