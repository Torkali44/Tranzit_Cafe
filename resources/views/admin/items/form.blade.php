@extends('layouts.admin')
@section('title', $item->exists ? 'تعديل منتج' : 'منتج جديد')

@section('content')
<div class="mx-auto max-w-2xl card p-5 sm:p-6" x-data="{
    addons: @js(old('addons', $addonRows)),
    addRow(){ this.addons.push({name:'', price:''}) },
    removeRow(i){ this.addons.splice(i,1); if(!this.addons.length) this.addRow() }
}">
    <form method="POST" action="{{ $item->exists ? route('admin.items.update', $item) : route('admin.items.store') }}" class="space-y-4">
        @csrf
        @if($item->exists) @method('PUT') @endif

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="sm:col-span-2">
                <label class="label">اسم المنتج</label>
                <input name="name" class="input" value="{{ old('name', $item->name) }}" required>
            </div>
            <div>
                <label class="label">القسم</label>
                <select name="category_id" class="input" required>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}" @selected(old('category_id', $item->category_id) == $cat->id)>{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">السعر</label>
                <input type="number" step="0.01" min="0" name="price" class="input" value="{{ old('price', $item->price) }}" required>
            </div>
            <div>
                <label class="label">الترتيب</label>
                <input type="number" name="sort_order" class="input" value="{{ old('sort_order', $item->sort_order ?? 0) }}">
            </div>
            <div class="flex items-end">
                <label class="flex items-center gap-2 pb-3 text-sm font-semibold">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $item->is_active ?? true) ? 'checked' : '' }}>
                    مفعّل
                </label>
            </div>
        </div>

        <div class="rounded-2xl border border-slate-200 p-4">
            <div class="mb-3 flex items-center justify-between">
                <h3 class="font-extrabold text-brand-900">الإضافات (اختياري)</h3>
                <button type="button" class="btn-outline text-xs" @click="addRow()">+ إضافة</button>
            </div>
            <template x-for="(addon, index) in addons" :key="index">
                <div class="mb-2 grid grid-cols-[1fr_120px_auto] gap-2">
                    <input class="input" :name="`addons[${index}][name]`" x-model="addon.name" placeholder="اسم الإضافة">
                    <input class="input" type="number" step="0.01" min="0" :name="`addons[${index}][price]`" x-model="addon.price" placeholder="السعر">
                    <button type="button" class="btn-ghost text-red-600" @click="removeRow(index)">حذف</button>
                </div>
            </template>
        </div>

        <div class="flex flex-wrap gap-2 pt-2">
            <button class="btn-primary" type="submit">حفظ</button>
            <a href="{{ route('admin.items.index') }}" class="btn-outline">رجوع</a>
        </div>
    </form>
</div>
@endsection
