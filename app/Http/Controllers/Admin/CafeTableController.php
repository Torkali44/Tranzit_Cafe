<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CafeTable;
use Illuminate\Http\Request;

class CafeTableController extends Controller
{
    public function index()
    {
        $tables = CafeTable::orderBy('sort_order')->orderBy('name')->get();

        return view('admin.tables.index', compact('tables'));
    }

    public function create()
    {
        return view('admin.tables.form', ['table' => new CafeTable([
            'capacity' => 4,
            'area' => 'داخلي',
            'status' => 'available',
            'is_active' => true,
        ])]);
    }

    public function store(Request $request)
    {
        CafeTable::create($this->validated($request));

        return redirect()->route('admin.tables.index')->with('success', 'تم إضافة الترابيزة.');
    }

    public function edit(CafeTable $table)
    {
        return view('admin.tables.form', compact('table'));
    }

    public function update(Request $request, CafeTable $table)
    {
        $table->update($this->validated($request));

        return redirect()->route('admin.tables.index')->with('success', 'تم تحديث الترابيزة.');
    }

    public function destroy(CafeTable $table)
    {
        $table->delete();

        return redirect()->route('admin.tables.index')->with('success', 'تم حذف الترابيزة.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'number' => ['nullable', 'string', 'max:40'],
            'capacity' => ['required', 'integer', 'min:1', 'max:50'],
            'area' => ['required', 'string', 'max:60'],
            'status' => ['required', 'in:available,occupied,awaiting_payment'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['is_active'] = $request->boolean('is_active', true);
        $data['sort_order'] = $data['sort_order'] ?? 0;

        return $data;
    }
}
