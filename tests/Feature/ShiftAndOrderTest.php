<?php

namespace Tests\Feature;

use App\Enums\ShiftStatus;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use App\Services\OrderService;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShiftAndOrderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::setValue('service_percent', '12');
    }

    public function test_cashier_must_open_shift_before_selling(): void
    {
        $cashier = User::factory()->cashier()->create();
        $category = Category::create(['name' => 'مشروبات', 'sort_order' => 1, 'is_active' => true]);
        Item::create(['category_id' => $category->id, 'name' => 'شاي', 'price' => 15, 'is_active' => true]);

        $this->actingAs($cashier)
            ->postJson('/pos/orders', ['type' => 'takeaway'])
            ->assertStatus(422);
    }

    public function test_cashier_can_sell_during_open_shift_and_free_table_on_pay(): void
    {
        $cashier = User::factory()->cashier()->create();
        $shifts = app(ShiftService::class);
        $orders = app(OrderService::class);

        $shift = $shifts->start($cashier, 100);

        $category = Category::create(['name' => 'مشروبات', 'sort_order' => 1, 'is_active' => true]);
        $item = Item::create(['category_id' => $category->id, 'name' => 'شاي', 'price' => 15, 'is_active' => true]);
        $table = CafeTable::create([
            'name' => 'ترابيزة 1',
            'number' => '1',
            'capacity' => 4,
            'area' => 'داخلي',
            'status' => 'available',
            'is_active' => true,
        ]);

        $order = $orders->create($cashier, 'dine_in', $table->id);
        // Table stays available until first item is added
        $this->assertEquals('available', $table->fresh()->status);

        $orders->addItem($order, $item->id, 2);
        $this->assertEquals('occupied', $table->fresh()->status);

        $invoice = $orders->pay($order->fresh(['items', 'cafeTable']), $cashier, 50);

        $this->assertNotNull($invoice->id);
        $this->assertEquals('available', $table->fresh()->status);
        $this->assertEquals('paid', $order->fresh()->status);

        $shift = $shifts->refreshTotals($shift);
        $this->assertEquals(1, $shift->orders_count);
        $this->assertEquals(100 + (float) $invoice->total, (float) $shift->expected_cash);

        $closed = $shifts->close($shift, (float) $shift->expected_cash);
        $this->assertEquals(ShiftStatus::Closed, $closed->status);
        $this->assertEquals(0.0, (float) $closed->variance);
    }

    public function test_free_table_makes_it_available(): void
    {
        $cashier = User::factory()->cashier()->create();
        app(ShiftService::class)->start($cashier, 0);
        $orders = app(OrderService::class);

        $category = Category::create(['name' => 'مشروبات', 'sort_order' => 1, 'is_active' => true]);
        $item = Item::create(['category_id' => $category->id, 'name' => 'شاي', 'price' => 15, 'is_active' => true]);

        $table = CafeTable::create([
            'name' => 'ترابيزة 2',
            'number' => '2',
            'capacity' => 2,
            'area' => 'داخلي',
            'status' => 'available',
            'is_active' => true,
        ]);

        $order = $orders->create($cashier, 'dine_in', $table->id);
        $orders->addItem($order, $item->id, 1);
        $this->assertEquals('occupied', $table->fresh()->status);

        $this->actingAs($cashier)
            ->post(route('pos.tables.free', $table), ['cancel_orders' => 1])
            ->assertRedirect();

        $this->assertEquals('available', $table->fresh()->status);
    }

    public function test_service_percent_comes_from_settings(): void
    {
        Setting::setValue('service_percent', '10');
        $this->assertEquals(10.0, (float) tranzit('service_percent'));
    }

    public function test_cannot_save_order_without_table(): void
    {
        $cashier = User::factory()->cashier()->create();
        app(ShiftService::class)->start($cashier, 0);
        $orders = app(OrderService::class);

        $category = Category::create(['name' => 'مشروبات', 'sort_order' => 1, 'is_active' => true]);
        $item = Item::create(['category_id' => $category->id, 'name' => 'ليمون', 'price' => 30, 'is_active' => true]);

        $order = $orders->create($cashier, 'takeaway');
        $orders->addItem($order, $item->id, 1);

        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $orders->save($order->fresh());
    }

    public function test_save_order_on_table_then_reopen_via_table(): void
    {
        $cashier = User::factory()->cashier()->create();
        app(ShiftService::class)->start($cashier, 0);
        $orders = app(OrderService::class);

        $category = Category::create(['name' => 'مشروبات', 'sort_order' => 1, 'is_active' => true]);
        $item = Item::create(['category_id' => $category->id, 'name' => 'ليمون', 'price' => 30, 'is_active' => true]);
        $table = CafeTable::create([
            'name' => 'ترابيزة 4',
            'number' => '4',
            'capacity' => 4,
            'area' => 'داخلي',
            'status' => 'available',
            'is_active' => true,
        ]);

        $order = $orders->create($cashier, 'dine_in', $table->id);
        $orders->addItem($order, $item->id, 1);
        $saved = $orders->save($order->fresh(['items']));

        $this->assertEquals('saved', $saved->status);
        $this->assertEquals('occupied', $table->fresh()->status);
        $this->assertNotNull($table->fresh()->currentOrder);
        $this->assertEquals($saved->id, $table->fresh()->currentOrder->id);
    }
}
