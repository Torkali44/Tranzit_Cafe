<?php

namespace App\Services;

use App\Models\CafeTable;
use App\Models\Invoice;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly SettingsService $settings,
        private readonly ShiftService $shifts,
    ) {}

    public function create(User $user, string $type, ?int $tableId = null): Order
    {
        $shift = $this->requireOpenShift($user);

        if ($type === 'dine_in' && ! $tableId) {
            throw ValidationException::withMessages(['cafe_table_id' => 'اختر ترابيزة']);
        }

        return DB::transaction(function () use ($user, $type, $tableId, $shift) {
            if ($tableId) {
                $table = CafeTable::lockForUpdate()->findOrFail($tableId);

                $active = Order::query()
                    ->where('cafe_table_id', $tableId)
                    ->whereIn('status', ['open', 'saved'])
                    ->withCount('items')
                    ->get();

                foreach ($active as $existing) {
                    if ($existing->items_count > 0) {
                        throw ValidationException::withMessages([
                            'cafe_table_id' => 'الترابيزة عليها طلب فعلي بالفعل',
                        ]);
                    }
                    // Empty draft — remove
                    $existing->update(['status' => 'cancelled']);
                }

                if ($table->status !== 'available') {
                    $table->update(['status' => 'available']);
                }
            }

            return Order::create([
                'order_number' => Order::nextOrderNumber(),
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'cafe_table_id' => $tableId,
                'type' => $type,
                'status' => 'open',
                'service_percent' => $this->settings->servicePercent(),
            ])->load(['items', 'cafeTable', 'user']);
        });
    }

    public function addItem(Order $order, int $itemId, int $quantity = 1, array $addons = []): Order
    {
        $this->ensureEditable($order);

        if ($quantity < 1) {
            throw ValidationException::withMessages(['quantity' => 'الكمية يجب أن تكون 1 على الأقل']);
        }

        $item = Item::findOrFail($itemId);

        if (! $item->is_active) {
            throw ValidationException::withMessages(['item_id' => 'هذا المنتج غير متاح']);
        }

        $addonsTotal = collect($addons)->sum(fn ($a) => (float) $a['price']);
        $unit = (float) $item->price + $addonsTotal;

        return DB::transaction(function () use ($order, $item, $quantity, $addons, $addonsTotal, $unit) {
            $order->items()->create([
                'item_id' => $item->id,
                'item_name' => $item->name,
                'unit_price' => $item->price,
                'quantity' => $quantity,
                'addons' => $addons,
                'addons_total' => $addonsTotal,
                'line_total' => $unit * $quantity,
            ]);

            if ($order->cafe_table_id) {
                CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'occupied']);
            }

            $order->load('items');
            $order->recalculate();

            return $order->fresh(['items', 'cafeTable', 'user']);
        });
    }

    public function updateQty(Order $order, OrderItem $orderItem, int $quantity): Order
    {
        $this->ensureEditable($order);
        abort_unless($orderItem->order_id === $order->id, 404);

        $unit = (float) $orderItem->unit_price + (float) $orderItem->addons_total;
        $orderItem->update([
            'quantity' => $quantity,
            'line_total' => $unit * $quantity,
        ]);

        $order->load('items');
        $order->recalculate();

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    public function removeItem(Order $order, OrderItem $orderItem): Order
    {
        $this->ensureEditable($order);
        abort_unless($orderItem->order_id === $order->id, 404);
        $orderItem->delete();
        $order->load('items');
        $order->recalculate();

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    public function applyDiscount(Order $order, ?string $type, float $value): Order
    {
        $this->ensureEditable($order);
        $order->update([
            'discount_type' => $type ?: null,
            'discount_value' => $value,
        ]);
        $order->load('items');
        $order->recalculate();

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    public function assignTable(Order $order, int $tableId): Order
    {
        $this->ensureEditable($order);

        if ($order->cafe_table_id && (int) $order->cafe_table_id === $tableId) {
            return $order->fresh(['items', 'cafeTable', 'user']);
        }

        if ($order->cafe_table_id && (int) $order->cafe_table_id !== $tableId) {
            throw ValidationException::withMessages([
                'cafe_table_id' => 'الطلب مربوط بالفعل بطاولة أخرى.',
            ]);
        }

        return DB::transaction(function () use ($order, $tableId) {
            $table = CafeTable::lockForUpdate()->findOrFail($tableId);

            if ($table->status !== 'available') {
                throw ValidationException::withMessages([
                    'cafe_table_id' => 'الترابيزة غير متاحة. اختَر طاولة فاضية.',
                ]);
            }

            // Occupy only if the order already has items
            if ($order->items()->exists()) {
                $table->update(['status' => 'occupied']);
            }

            $order->update([
                'cafe_table_id' => $table->id,
                'type' => 'dine_in',
            ]);

            return $order->fresh(['items', 'cafeTable', 'user']);
        });
    }

    public function save(Order $order): Order
    {
        $this->ensureEditable($order);

        if (! $order->cafe_table_id) {
            throw ValidationException::withMessages([
                'cafe_table_id' => 'لازم تحدد ترابيزة للطلب قبل الحفظ، علشان ترجع تفتحه لما العميل يحاسب.',
            ]);
        }

        if ($order->items()->count() === 0) {
            throw ValidationException::withMessages([
                'order' => 'أضف أصنافاً قبل حفظ الطلب على الطاولة.',
            ]);
        }

        $order->update([
            'status' => 'saved',
            'type' => 'dine_in',
        ]);

        CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'occupied']);

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    /** إتمام طلب الطاولة: جاهز للحساب — بانتظار الدفع */
    public function completeForPayment(Order $order): Order
    {
        $this->ensureEditable($order);

        if ($order->items()->count() === 0) {
            throw ValidationException::withMessages([
                'order' => 'أضف أصنافاً قبل إتمام الطلب.',
            ]);
        }

        if (! $order->cafe_table_id) {
            throw ValidationException::withMessages([
                'cafe_table_id' => 'إتمام الطلب للطاولات يحتاج ترابيزة. للسفري استخدم الدفع مباشرة.',
            ]);
        }

        $order->update([
            'status' => 'saved',
            'type' => 'dine_in',
        ]);

        CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'awaiting_payment']);

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    /** تنظيف ترابيزات مشغولة بدون طلب فعلي بأصناف */
    public function healStaleTables(): int
    {
        $fixed = 0;
        $tables = CafeTable::query()
            ->whereIn('status', ['occupied', 'awaiting_payment'])
            ->with(['currentOrder.items'])
            ->get();

        foreach ($tables as $table) {
            $order = $table->currentOrder;
            // Only free if there is an empty order. If there's no order at all, it might be just seated from waiting list.
            if ($order && $order->items->isEmpty()) {
                $this->freeTable($table, true);
                $fixed++;
            }
        }

        return $fixed;
    }

    public function pay(Order $order, User $user, float $amountPaid): Invoice
    {
        $this->ensureEditable($order);

        if ($order->items()->count() === 0) {
            throw ValidationException::withMessages(['order' => 'الطلب فارغ']);
        }

        $shift = $this->requireOpenShift($user);

        return DB::transaction(function () use ($order, $user, $amountPaid, $shift) {
            $order->load('items');
            $order->recalculate();
            $order->refresh();

            $change = max(0, $amountPaid - (float) $order->total);

            $order->update([
                'status' => 'paid',
                'paid_at' => now(),
                'shift_id' => $shift->id,
            ]);

            if ($order->cafe_table_id) {
                CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'available']);
            }

            $tableLabel = $order->cafeTable?->name
                ?? ($order->type === 'takeaway' ? 'سفري' : null);

            $invoice = Invoice::create([
                'invoice_number' => Invoice::nextInvoiceNumber(),
                'order_id' => $order->id,
                'user_id' => $user->id,
                'shift_id' => $shift->id,
                'cashier_name' => $user->name,
                'table_label' => $tableLabel,
                'order_type' => $order->type,
                'subtotal' => $order->subtotal,
                'service_amount' => $order->service_amount,
                'discount_amount' => $order->discount_amount,
                'total' => $order->total,
                'amount_paid' => $amountPaid,
                'change_amount' => $change,
                'paid_at' => now(),
            ]);

            $this->shifts->refreshTotals($shift);

            return $invoice->load(['order.items', 'user']);
        });
    }

    public function cancel(Order $order): void
    {
        $this->ensureEditable($order);

        DB::transaction(function () use ($order) {
            $order->update(['status' => 'cancelled']);
            if ($order->cafe_table_id) {
                CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'available']);
            }
        });
    }

    public function freeTable(CafeTable $table, bool $cancelOpenOrders = true): void
    {
        DB::transaction(function () use ($table, $cancelOpenOrders) {
            if ($cancelOpenOrders) {
                Order::query()
                    ->where('cafe_table_id', $table->id)
                    ->whereIn('status', ['open', 'saved'])
                    ->update(['status' => 'cancelled']);
            }

            $table->update(['status' => 'available']);
        });
    }

    public function markAwaitingPayment(Order $order): Order
    {
        $this->ensureEditable($order);

        if ($order->cafe_table_id) {
            CafeTable::where('id', $order->cafe_table_id)->update(['status' => 'awaiting_payment']);
            $order->update(['status' => 'saved']);
        }

        return $order->fresh(['items', 'cafeTable', 'user']);
    }

    public function payload(Order $order): array
    {
        return [
            'id' => $order->id,
            'order_number' => $order->order_number,
            'type' => $order->type,
            'type_label' => $order->typeLabel(),
            'status' => $order->status,
            'status_label' => $order->statusLabel(),
            'subtotal' => (float) $order->subtotal,
            'service_percent' => (float) $order->service_percent,
            'service_amount' => (float) $order->service_amount,
            'discount_type' => $order->discount_type,
            'discount_value' => (float) $order->discount_value,
            'discount_amount' => (float) $order->discount_amount,
            'total' => (float) $order->total,
            'cashier' => $order->user?->name,
            'table' => $order->cafeTable ? [
                'id' => $order->cafeTable->id,
                'name' => $order->cafeTable->name,
            ] : null,
            'items' => $order->items->map(fn (OrderItem $i) => [
                'id' => $i->id,
                'item_name' => $i->item_name,
                'unit_price' => (float) $i->unit_price,
                'quantity' => $i->quantity,
                'addons' => $i->addons ?? [],
                'addons_label' => $i->addonsLabel(),
                'addons_total' => (float) $i->addons_total,
                'line_total' => (float) $i->line_total,
            ])->values(),
        ];
    }

    private function ensureEditable(Order $order): void
    {
        if (! in_array($order->status, ['open', 'saved'], true)) {
            throw ValidationException::withMessages(['order' => 'لا يمكن تعديل هذا الطلب']);
        }
    }

    private function requireOpenShift(User $user): \App\Models\Shift
    {
        if ($user->isAdmin()) {
            $shift = $this->shifts->currentOpenShift($user);
            if (! $shift) {
                $shift = $this->shifts->start($user, 0);
            }

            return $shift;
        }

        $shift = $this->shifts->currentOpenShift($user);
        if (! $shift) {
            throw ValidationException::withMessages([
                'shift' => 'يجب فتح شيفت قبل البيع.',
            ]);
        }

        return $shift;
    }
}
