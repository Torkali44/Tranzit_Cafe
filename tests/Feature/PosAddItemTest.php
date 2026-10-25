<?php

namespace Tests\Feature;

use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Item;
use App\Models\ItemAddon;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\ShiftService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PosAddItemTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Setting::setValue('service_percent', '10');
    }

    public function test_pos_page_renders_products_and_categories(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create(['name' => 'المشروبات الساخنة', 'sort_order' => 1, 'is_active' => true]);
        Item::create([
            'category_id' => $category->id,
            'name' => 'قهوة تركي',
            'price' => 25,
            'is_active' => true,
        ]);

        $this->actingAs($admin)
            ->get('/pos')
            ->assertOk()
            ->assertSee('pos-config', false)
            ->assertSee('قهوة تركي', false)
            ->assertSee('المشروبات الساخنة', false);
    }

    public function test_can_add_item_with_addons_and_save_on_table(): void
    {
        $cashier = User::factory()->cashier()->create();
        app(ShiftService::class)->start($cashier, 0);

        $category = Category::create(['name' => 'بارد', 'sort_order' => 1, 'is_active' => true]);
        $item = Item::create([
            'category_id' => $category->id,
            'name' => 'ليمون بالنعناع',
            'price' => 30,
            'is_active' => true,
        ]);
        ItemAddon::create([
            'item_id' => $item->id,
            'name' => 'حجم كبير',
            'price' => 10,
            'is_active' => true,
        ]);
        $table = CafeTable::create([
            'name' => 'ترابيزة 4',
            'number' => '4',
            'capacity' => 4,
            'area' => 'داخلي',
            'status' => 'available',
            'is_active' => true,
        ]);

        $create = $this->actingAs($cashier)->postJson('/pos/orders', [
            'type' => 'dine_in',
            'cafe_table_id' => $table->id,
        ])->assertOk();

        $orderId = $create->json('order.id');

        $this->actingAs($cashier)->postJson("/pos/orders/{$orderId}/items", [
            'item_id' => $item->id,
            'quantity' => 1,
            'addons' => [
                ['name' => 'حجم كبير', 'price' => 10],
            ],
        ])->assertOk()
            ->assertJsonPath('order.items.0.item_name', 'ليمون بالنعناع')
            ->assertJsonPath('order.items.0.line_total', 40);

        $this->actingAs($cashier)
            ->postJson("/pos/orders/{$orderId}/save")
            ->assertOk()
            ->assertJsonPath('order.status', 'saved')
            ->assertJsonPath('order.table.name', 'ترابيزة 4');

        $order = Order::find($orderId);
        $this->assertEquals('saved', $order->status);
        $this->assertEquals(40.0, (float) $order->subtotal);
        $this->assertEquals('occupied', $table->fresh()->status);

        $this->actingAs($cashier)
            ->get('/pos?order='.$orderId)
            ->assertOk()
            ->assertSee('ليمون بالنعناع', false);
    }
}
