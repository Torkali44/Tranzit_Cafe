<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Shift;
use App\Models\WaitingList;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        [$from, $to] = $this->period($request);
        $data = $this->buildReport($from, $to);

        return view('admin.reports.index', array_merge($data, compact('from', 'to')));
    }

    public function export(Request $request): StreamedResponse
    {
        [$from, $to] = $this->period($request);
        $data = $this->buildReport($from, $to);

        $filename = 'tranzit-report-'.$from->format('Ymd').'-'.$to->format('Ymd').'.xls';

        return response()->streamDownload(function () use ($data, $from, $to) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM for Excel Arabic
            echo '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel">';
            echo '<head><meta charset="UTF-8"><style>table{border-collapse:collapse;width:100%}td,th{border:1px solid #999;padding:6px;text-align:right}th{background:#0f2744;color:#fff}.section{background:#e2e8f0;font-weight:bold}</style></head><body dir="rtl">';

            echo '<h2>تقرير ترانزيت — من '.$from->format('Y-m-d').' إلى '.$to->format('Y-m-d').'</h2>';

            echo '<table><tr class="section"><td colspan="2">ملخص الفترة</td></tr>';
            foreach ([
                'إجمالي المبيعات' => number_format($data['summary']['sales'], 2),
                'عدد الفواتير' => $data['summary']['orders'],
                'متوسط الفاتورة' => number_format($data['summary']['avg'], 2),
                'إجمالي الخدمة' => number_format($data['summary']['service'], 2),
                'إجمالي الخصومات' => number_format($data['summary']['discounts'], 2),
                'صافي قبل الخدمة' => number_format($data['summary']['subtotal'], 2),
                'طلبات ملغاة' => $data['summary']['cancelled'],
                'طلبات محفوظة مفتوحة' => $data['summary']['open_saved'],
            ] as $label => $val) {
                echo '<tr><td>'.$label.'</td><td>'.$val.'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="4">المبيعات اليومية</td></tr><tr><th>اليوم</th><th>الطلبات</th><th>المبيعات</th><th>متوسط</th></tr>';
            foreach ($data['daily'] as $row) {
                $avg = $row->cnt > 0 ? $row->total / $row->cnt : 0;
                echo '<tr><td>'.$row->day.'</td><td>'.$row->cnt.'</td><td>'.number_format($row->total, 2).'</td><td>'.number_format($avg, 2).'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="3">حسب نوع الطلب</td></tr><tr><th>النوع</th><th>العدد</th><th>المبيعات</th></tr>';
            foreach ($data['byType'] as $row) {
                $label = $row->order_type === 'takeaway' ? 'سفري' : 'داخلي';
                echo '<tr><td>'.$label.'</td><td>'.$row->cnt.'</td><td>'.number_format($row->total, 2).'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="4">حسب الكاشير</td></tr><tr><th>الكاشير</th><th>الفواتير</th><th>المبيعات</th><th>متوسط</th></tr>';
            foreach ($data['byCashier'] as $row) {
                $avg = $row->cnt > 0 ? $row->total / $row->cnt : 0;
                echo '<tr><td>'.e($row->cashier_name).'</td><td>'.$row->cnt.'</td><td>'.number_format($row->total, 2).'</td><td>'.number_format($avg, 2).'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="4">الأكثر مبيعاً</td></tr><tr><th>الصنف</th><th>الكمية</th><th>الإيراد</th><th>متوسط السعر</th></tr>';
            foreach ($data['bestSellers'] as $row) {
                $avg = $row->qty > 0 ? $row->revenue / $row->qty : 0;
                echo '<tr><td>'.e($row->item_name).'</td><td>'.$row->qty.'</td><td>'.number_format($row->revenue, 2).'</td><td>'.number_format($avg, 2).'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="4">حسب الساعة</td></tr><tr><th>الساعة</th><th>الطلبات</th><th>المبيعات</th></tr>';
            foreach ($data['hourly'] as $row) {
                echo '<tr><td>'.str_pad((string) $row->hour, 2, '0', STR_PAD_LEFT).':00</td><td>'.$row->cnt.'</td><td>'.number_format($row->total, 2).'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="6">الشيفتات</td></tr><tr><th>الكاشير</th><th>الحالة</th><th>الطلبات</th><th>المبيعات</th><th>العجز/الزيادة</th><th>من</th></tr>';
            foreach ($data['shifts'] as $shift) {
                echo '<tr><td>'.e($shift->user?->name ?? '—').'</td><td>'.$shift->statusLabel().'</td><td>'.$shift->orders_count.'</td><td>'.number_format((float) $shift->sales_total, 2).'</td><td>'.number_format((float) $shift->variance, 2).'</td><td>'.optional($shift->started_at)->format('Y-m-d H:i').'</td></tr>';
            }
            echo '</table><br>';

            echo '<table><tr class="section"><td colspan="5">الفواتير التفصيلية</td></tr><tr><th>رقم الفاتورة</th><th>الكاشير</th><th>النوع/الطاولة</th><th>الإجمالي</th><th>الوقت</th></tr>';
            foreach ($data['invoicesList'] as $inv) {
                $type = ($inv->order_type === 'takeaway' ? 'سفري' : 'داخلي').($inv->table_label ? ' · '.$inv->table_label : '');
                echo '<tr><td>'.$inv->invoice_number.'</td><td>'.e($inv->cashier_name).'</td><td>'.e($type).'</td><td>'.number_format((float) $inv->total, 2).'</td><td>'.optional($inv->paid_at)->format('Y-m-d H:i').'</td></tr>';
            }
            echo '</table>';

            echo '</body></html>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    private function period(Request $request): array
    {
        $from = $request->filled('from')
            ? Carbon::parse($request->from)->startOfDay()
            : Carbon::today()->startOfMonth();
        $to = $request->filled('to')
            ? Carbon::parse($request->to)->endOfDay()
            : Carbon::today()->endOfDay();

        return [$from, $to];
    }

    private function buildReport(Carbon $from, Carbon $to): array
    {
        $invoices = Invoice::whereBetween('paid_at', [$from, $to]);

        $summary = [
            'sales' => (float) (clone $invoices)->sum('total'),
            'orders' => (clone $invoices)->count(),
            'discounts' => (float) (clone $invoices)->sum('discount_amount'),
            'service' => (float) (clone $invoices)->sum('service_amount'),
            'subtotal' => (float) (clone $invoices)->sum('subtotal'),
            'avg' => 0,
            'cancelled' => Order::where('status', 'cancelled')->whereBetween('updated_at', [$from, $to])->count(),
            'open_saved' => Order::whereIn('status', ['open', 'saved'])->count(),
        ];
        $summary['avg'] = $summary['orders'] > 0 ? $summary['sales'] / $summary['orders'] : 0;

        $today = Invoice::whereDate('paid_at', Carbon::today());
        $todaySummary = [
            'sales' => (float) (clone $today)->sum('total'),
            'orders' => (clone $today)->count(),
        ];

        $month = Invoice::where('paid_at', '>=', Carbon::now()->startOfMonth());
        $monthSummary = [
            'sales' => (float) (clone $month)->sum('total'),
            'orders' => (clone $month)->count(),
        ];

        $daily = Invoice::query()
            ->whereBetween('paid_at', [$from, $to])
            ->select(DB::raw('date(paid_at) as day'), DB::raw('sum(total) as total'), DB::raw('count(*) as cnt'))
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $bestSellers = OrderItem::query()
            ->select('item_name', DB::raw('sum(quantity) as qty'), DB::raw('sum(line_total) as revenue'))
            ->whereHas('order', function ($q) use ($from, $to) {
                $q->where('status', 'paid')->whereBetween('paid_at', [$from, $to]);
            })
            ->groupBy('item_name')
            ->orderByDesc('qty')
            ->limit(20)
            ->get();

        $byCashier = Invoice::query()
            ->whereBetween('paid_at', [$from, $to])
            ->select('cashier_name', DB::raw('count(*) as cnt'), DB::raw('sum(total) as total'))
            ->groupBy('cashier_name')
            ->orderByDesc('total')
            ->get();

        $byType = Invoice::query()
            ->whereBetween('paid_at', [$from, $to])
            ->select('order_type', DB::raw('count(*) as cnt'), DB::raw('sum(total) as total'))
            ->groupBy('order_type')
            ->get();

        $hourly = Invoice::query()
            ->whereBetween('paid_at', [$from, $to])
            ->select(DB::raw('cast(strftime("%H", paid_at) as integer) as hour'), DB::raw('count(*) as cnt'), DB::raw('sum(total) as total'))
            ->groupBy('hour')
            ->orderBy('hour')
            ->get();

        // SQLite uses strftime; MySQL uses HOUR() — detect driver
        if (DB::getDriverName() !== 'sqlite') {
            $hourly = Invoice::query()
                ->whereBetween('paid_at', [$from, $to])
                ->select(DB::raw('HOUR(paid_at) as hour'), DB::raw('count(*) as cnt'), DB::raw('sum(total) as total'))
                ->groupBy('hour')
                ->orderBy('hour')
                ->get();
        }

        $byTable = Invoice::query()
            ->whereBetween('paid_at', [$from, $to])
            ->whereNotNull('table_label')
            ->select('table_label', DB::raw('count(*) as cnt'), DB::raw('sum(total) as total'))
            ->groupBy('table_label')
            ->orderByDesc('total')
            ->limit(15)
            ->get();

        $shifts = Shift::with('user')
            ->where(function ($q) use ($from, $to) {
                $q->whereBetween('started_at', [$from, $to])
                    ->orWhereBetween('ended_at', [$from, $to]);
            })
            ->latest('started_at')
            ->limit(50)
            ->get();

        $tableStats = [
            'available' => CafeTable::where('is_active', true)->where('status', 'available')->count(),
            'occupied' => CafeTable::where('is_active', true)->where('status', 'occupied')->count(),
            'awaiting' => CafeTable::where('is_active', true)->where('status', 'awaiting_payment')->count(),
            'total' => CafeTable::where('is_active', true)->count(),
        ];

        $waitingStats = [
            'waiting' => WaitingList::where('status', 'waiting')->count(),
            'seated' => WaitingList::where('status', 'seated')->whereBetween('seated_at', [$from, $to])->count(),
            'cancelled' => WaitingList::where('status', 'cancelled')->whereBetween('updated_at', [$from, $to])->count(),
        ];

        $invoicesList = Invoice::whereBetween('paid_at', [$from, $to])
            ->latest('paid_at')
            ->limit(100)
            ->get();

        return compact(
            'summary',
            'todaySummary',
            'monthSummary',
            'daily',
            'bestSellers',
            'byCashier',
            'byType',
            'hourly',
            'byTable',
            'shifts',
            'tableStats',
            'waitingStats',
            'invoicesList'
        );
    }
}
