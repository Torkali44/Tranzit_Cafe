<?php

namespace App\Http\Controllers\Pos;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\OrderService;
use App\Services\SettingsService;
use App\Services\ShiftService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PosController extends Controller
{
    public function __construct(
        private readonly OrderService $orders,
        private readonly SettingsService $settings,
        private readonly ShiftService $shifts,
    ) {}

    public function index(Request $request)
    {
        $shift = $this->shifts->currentOpenShift();

        if (auth()->user()->isCashier() && ! $shift) {
            return redirect()->route('pos.shift.index')
                ->with('warning', 'افتح الشيفت أولاً قبل البيع.');
        }

        $categories = Category::where('is_active', true)
            ->withCount(['activeItems as items_count'])
            ->orderBy('sort_order')
            ->get();

        $allItems = Item::where('is_active', true)
            ->with(['addons' => fn ($q) => $q->where('is_active', true), 'category'])
            ->orderBy('sort_order')
            ->get();

        $order = null;
        if ($request->filled('order')) {
            $order = Order::with(['items', 'cafeTable', 'user'])
                ->whereIn('status', ['open', 'saved'])
                ->find($request->order);
        }

        $pendingTable = null;
        if (! $order && $request->filled('table')) {
            $pendingTable = CafeTable::query()
                ->where('is_active', true)
                ->find($request->table);
        }

        $availableTables = CafeTable::query()
            ->where('is_active', true)
            ->where('status', 'available')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get(['id', 'name', 'number', 'area']);

        // If editing an order already on a table, include that table in the picker list
        if ($order?->cafeTable && $availableTables->where('id', $order->cafe_table_id)->isEmpty()) {
            $availableTables = $availableTables->prepend($order->cafeTable)->unique('id')->values();
        }

        return view('pos.index', [
            'categories' => $categories,
            'allItems' => $allItems,
            'order' => $order,
            'servicePercent' => $this->settings->servicePercent(),
            'shift' => $shift,
            'availableTables' => $availableTables,
            'tablesUrl' => route('pos.tables'),
            'pendingTable' => $pendingTable,
        ]);
    }

    public function createOrder(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'in:takeaway,dine_in'],
            'cafe_table_id' => ['nullable', 'exists:cafe_tables,id'],
        ]);

        try {
            $order = $this->orders->create(
                auth()->user(),
                $data['type'],
                $data['cafe_table_id'] ?? null
            );
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function showOrder(Order $order)
    {
        $order->load(['items', 'cafeTable', 'user']);

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function addItem(Request $request, Order $order)
    {
        $data = $request->validate([
            'item_id' => ['required', 'exists:items,id'],
            'quantity' => ['nullable', 'integer', 'min:1'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['required_with:addons', 'string'],
            'addons.*.price' => ['required_with:addons', 'numeric', 'min:0'],
        ]);

        try {
            $order = $this->orders->addItem(
                $order,
                (int) $data['item_id'],
                $data['quantity'] ?? 1,
                $data['addons'] ?? []
            );
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function updateItemQty(Request $request, Order $order, OrderItem $orderItem)
    {
        $data = $request->validate(['quantity' => ['required', 'integer', 'min:1']]);

        try {
            $order = $this->orders->updateQty($order, $orderItem, (int) $data['quantity']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function removeItem(Order $order, OrderItem $orderItem)
    {
        try {
            $order = $this->orders->removeItem($order, $orderItem);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function applyDiscount(Request $request, Order $order)
    {
        $data = $request->validate([
            'discount_type' => ['nullable', 'in:percent,fixed'],
            'discount_value' => ['nullable', 'numeric', 'min:0'],
        ]);

        $order = $this->orders->applyDiscount(
            $order,
            $data['discount_type'] ?? null,
            (float) ($data['discount_value'] ?? 0)
        );

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function assignTable(Request $request, Order $order)
    {
        $data = $request->validate([
            'cafe_table_id' => ['required', 'exists:cafe_tables,id'],
        ]);

        try {
            $order = $this->orders->assignTable($order, (int) $data['cafe_table_id']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['order' => $this->orders->payload($order)]);
    }

    public function save(Order $order)
    {
        try {
            $order = $this->orders->save($order);
        } catch (ValidationException $e) {
            $message = collect($e->errors())->flatten()->first();

            return response()->json([
                'message' => $message,
                'needs_table' => isset($e->errors()['cafe_table_id']),
            ], 422);
        }

        return response()->json([
            'order' => $this->orders->payload($order),
            'message' => 'تم حفظ الطلب على '.$order->cafeTable?->name.' — ارجع للطاولة من شاشة الطاولات لما العميل يحاسب.',
            'redirect' => route('pos.tables'),
        ]);
    }

    public function complete(Order $order)
    {
        try {
            $order = $this->orders->completeForPayment($order);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => collect($e->errors())->flatten()->first(),
                'needs_table' => isset($e->errors()['cafe_table_id']),
                'needs_pay' => ! $order->cafe_table_id,
            ], 422);
        }

        return response()->json([
            'order' => $this->orders->payload($order),
            'message' => 'تم إتمام الطلب — الطاولة بانتظار الدفع. افتحها من الطاولات لما العميل يحاسب.',
            'redirect' => route('pos.tables'),
        ]);
    }

    public function pay(Request $request, Order $order)
    {
        $data = $request->validate([
            'amount_paid' => ['required', 'numeric', 'min:0'],
        ]);

        try {
            $invoice = $this->orders->pay($order, auth()->user(), (float) $data['amount_paid']);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json([
            'invoice' => $invoice,
            'order' => $this->orders->payload($order->fresh(['items', 'cafeTable', 'user'])),
            'print_url' => route('invoices.print', $invoice),
        ]);
    }

    public function cancel(Order $order)
    {
        try {
            $this->orders->cancel($order);
        } catch (ValidationException $e) {
            return response()->json(['message' => collect($e->errors())->flatten()->first()], 422);
        }

        return response()->json(['message' => 'تم إلغاء الطلب']);
    }

    public function markAwaitingPayment(Order $order)
    {
        $order = $this->orders->markAwaitingPayment($order);

        return response()->json([
            'order' => $this->orders->payload($order),
            'message' => 'الترابيزة بانتظار الدفع',
        ]);
    }
}
