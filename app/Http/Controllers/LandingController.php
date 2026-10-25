<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Invoice;
use App\Models\Item;
use Carbon\Carbon;

class LandingController extends Controller
{
    public function __invoke()
    {
        $todayInvoices = Invoice::whereDate('paid_at', Carbon::today());
        $ordersToday = (clone $todayInvoices)->count();
        $salesToday = (float) (clone $todayInvoices)->sum('total');
        $avgToday = $ordersToday > 0 ? $salesToday / $ordersToday : 0;

        $categories = Category::query()
            ->where('is_active', true)
            ->withCount(['activeItems as items_count'])
            ->orderBy('sort_order')
            ->limit(11)
            ->get();

        $featuredItems = Item::query()
            ->where('is_active', true)
            ->with('category')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->limit(11)
            ->get()
            ->map(fn (Item $item) => [
                'name' => $item->name,
                'price' => (float) $item->price,
                'image' => $this->imageFor($item->name),
            ]);

        // لو مفيش منتجات، نعرض الأقسام
        $tiles = $featuredItems->isNotEmpty()
            ? $featuredItems
            : $categories->map(fn (Category $c) => [
                'name' => $c->name,
                'price' => null,
                'image' => $this->imageFor($c->name),
            ]);

        return view('landing', [
            'ordersToday' => $ordersToday,
            'salesToday' => $salesToday,
            'avgToday' => $avgToday,
            'tiles' => $tiles,
            'cafeName' => tranzit('name'),
            'cafeNameAr' => tranzit('name_ar'),
        ]);
    }

    private function imageFor(string $name): string
    {
        $n = mb_strtolower($name);

        return match (true) {
            str_contains($n, 'كابتشينو') || str_contains($n, 'cappuccino') => 'https://images.unsplash.com/photo-1572442388796-11668a67e53d?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'لاتيه') || str_contains($n, 'latte') => 'https://images.unsplash.com/photo-1561882468-9110e03e0f78?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'إسبريسو') || str_contains($n, 'اسبرسو') || str_contains($n, 'espresso') => 'https://images.unsplash.com/photo-1510590337019-5ef8d3d32116?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'موكا') || str_contains($n, 'mocha') => 'https://images.unsplash.com/photo-1578374173705-969cbe6f2d4b?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'تركي') || str_contains($n, 'turkish') => 'https://images.unsplash.com/photo-1610889556528-9a770e32642f?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'آيس') || str_contains($n, 'بارد') || str_contains($n, 'cold') => 'https://images.unsplash.com/photo-1517701604599-bb29b565090c?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'تي') || str_contains($n, 'شاي') || str_contains($n, 'tea') => 'https://images.unsplash.com/photo-1556679343-c7306c1976bc?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'فراولة') || str_contains($n, 'strawberry') => 'https://images.unsplash.com/photo-1464454709131-ffd692591ee5?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'عصير') || str_contains($n, 'ليمون') || str_contains($n, 'مانجو') || str_contains($n, 'برتقال') => 'https://images.unsplash.com/photo-1622597467836-f3285f2131b8?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'ساند') || str_contains($n, 'توست') || str_contains($n, 'كرواسون') || str_contains($n, 'مأكول') => 'https://images.unsplash.com/photo-1528735602780-2552fd46c7af?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'حلويات') || str_contains($n, 'كيك') || str_contains($n, 'براوني') || str_contains($n, 'تيراميسو') => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=600&q=80',
            str_contains($n, 'غاز') || str_contains($n, 'كولا') => 'https://images.unsplash.com/photo-1622483767028-3f66f32aef97?auto=format&fit=crop&w=600&q=80',
            default => 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=600&q=80',
        };
    }
}
