<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Item;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItemController extends Controller
{
    public function index(Request $request)
    {
        $items = Item::with('category')
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->q, fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $categories = Category::orderBy('sort_order')->get();

        return view('admin.items.index', compact('items', 'categories'));
    }

    public function create()
    {
        return view('admin.items.form', [
            'item' => new Item,
            'categories' => Category::where('is_active', true)->orderBy('sort_order')->get(),
            'addonRows' => [['name' => '', 'price' => '']],
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($data, $request) {
            $item = Item::create($data);
            $this->syncAddons($item, $request->input('addons', []));
        });

        return redirect()->route('admin.items.index')->with('success', 'تم إضافة المنتج بنجاح.');
    }

    public function edit(Item $item)
    {
        $item->load('addons');
        $addonRows = $item->addons->map(fn ($a) => [
            'name' => $a->name,
            'price' => $a->price,
        ])->values()->all();

        if (empty($addonRows)) {
            $addonRows = [['name' => '', 'price' => '']];
        }

        return view('admin.items.form', [
            'item' => $item,
            'categories' => Category::orderBy('sort_order')->get(),
            'addonRows' => $addonRows,
        ]);
    }

    public function update(Request $request, Item $item)
    {
        $data = $this->validated($request);

        DB::transaction(function () use ($item, $data, $request) {
            $item->update($data);
            $item->addons()->delete();
            $this->syncAddons($item, $request->input('addons', []));
        });

        return redirect()->route('admin.items.index')->with('success', 'تم تحديث المنتج.');
    }

    public function destroy(Item $item)
    {
        $item->delete();

        return redirect()->route('admin.items.index')->with('success', 'تم حذف المنتج.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'price' => ['required', 'numeric', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
            'addons' => ['nullable', 'array'],
            'addons.*.name' => ['nullable', 'string', 'max:120'],
            'addons.*.price' => ['nullable', 'numeric', 'min:0'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return collect($data)->except('addons')->all();
    }

    private function syncAddons(Item $item, array $addons): void
    {
        foreach ($addons as $addon) {
            if (empty($addon['name'])) {
                continue;
            }

            $item->addons()->create([
                'name' => $addon['name'],
                'price' => $addon['price'] ?? 0,
                'is_active' => true,
            ]);
        }
    }
}
