<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\CafeTable;
use App\Models\Category;
use App\Models\Item;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::setValue('service_percent', '12');
        Setting::setValue('cafe_name', 'Tranzit');
        Setting::setValue('cafe_name_ar', 'ترانزيت');

        DB::table('users')->delete();

        // أدمن واحد فقط + كاشير
        User::create([
            'name' => 'صاحب ترانزيت',
            'username' => 'admin',
            'email' => 'admin@tranzit.test',
            'password' => Hash::make('Admin@Tranzit1'),
            'role' => UserRole::Admin,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'كاشير ترانزيت',
            'username' => 'cashier',
            'email' => 'cashier@tranzit.test',
            'password' => Hash::make('Cashier@Tranzit1'),
            'role' => UserRole::Cashier,
            'is_active' => true,
        ]);

        $catalog = [
            'المشروبات الساخنة' => [
                ['قهوة تركي', 25, [['حجم كبير', 10], ['حليب لوز', 8], ['شوت إسبريسو إضافي', 10]]],
                ['إسبريسو', 20, [['شوت إضافي', 10]]],
                ['كابتشينو', 35, [['حجم كبير', 10], ['حليب لوز', 8]]],
                ['موكا', 40, [['حجم كبير', 10], ['كريمة', 5]]],
                ['شاي', 15, [['ليمون', 3]]],
                ['نسكافيه', 22, [['حجم كبير', 8]]],
            ],
            'المشروبات الباردة' => [
                ['آيس موكا', 45, [['حجم كبير', 10], ['شوت إسبريسو إضافي', 10], ['حليب لوز', 8]]],
                ['آيس لاتيه', 42, [['حجم كبير', 10], ['حليب لوز', 8]]],
                ['آيس تي', 28, []],
                ['موهيتو', 40, [['نعناع إضافي', 5]]],
            ],
            'المشروبات الغازية' => [
                ['كولا', 20, []],
                ['سبرايت', 20, []],
                ['فينو', 18, []],
            ],
            'العصائر الطبيعية' => [
                ['ليمون بالنعناع', 30, [['حجم كبير', 10]]],
                ['فراولة', 35, []],
                ['كوكتيل فواكه', 45, []],
                ['مانجو', 35, []],
                ['برتقال', 28, []],
            ],
            'الحلويات' => [
                ['براوني شوكولاتة', 45, []],
                ['تشيز كيك', 50, []],
                ['تيراميسو', 55, []],
            ],
            'المأكولات الخفيفة' => [
                ['ساندوتش تونة', 40, []],
                ['كرواسون جبن', 30, []],
                ['توست', 25, []],
            ],
        ];

        Category::query()->delete();

        $sort = 1;
        foreach ($catalog as $catName => $items) {
            $category = Category::create([
                'name' => $catName,
                'sort_order' => $sort++,
                'is_active' => true,
            ]);

            $itemSort = 1;
            foreach ($items as [$name, $price, $addons]) {
                $item = Item::create([
                    'category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'sort_order' => $itemSort++,
                    'is_active' => true,
                ]);

                foreach ($addons as [$addonName, $addonPrice]) {
                    $item->addons()->create([
                        'name' => $addonName,
                        'price' => $addonPrice,
                        'is_active' => true,
                    ]);
                }
            }
        }

        CafeTable::query()->delete();

        $tables = [
            ['ترابيزة 1', '1', 4, 'داخلي'],
            ['ترابيزة 2', '2', 4, 'داخلي'],
            ['ترابيزة 3', '3', 2, 'داخلي'],
            ['ترابيزة 4', '4', 4, 'داخلي'],
            ['ترابيزة 5', '5', 6, 'داخلي'],
            ['ترابيزة 6', '6', 4, 'داخلي'],
            ['ترابيزة 7', '7', 2, 'داخلي'],
            ['ترابيزة 8', '8', 4, 'داخلي'],
            ['شرفة 9', '9', 4, 'شرفة'],
            ['شرفة 10', '10', 4, 'شرفة'],
            ['شرفة 11', '11', 6, 'شرفة'],
            ['شرفة 12', '12', 4, 'شرفة'],
            ['شرفة 13', '13', 2, 'شرفة'],
            ['شرفة 14', '14', 4, 'شرفة'],
        ];

        foreach ($tables as $i => [$name, $number, $capacity, $area]) {
            CafeTable::create([
                'name' => $name,
                'number' => $number,
                'capacity' => $capacity,
                'area' => $area,
                'status' => 'available',
                'sort_order' => $i + 1,
                'is_active' => true,
            ]);
        }
    }
}
