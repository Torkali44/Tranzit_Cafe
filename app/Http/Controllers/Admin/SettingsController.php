<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\SettingsService;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function __construct(private readonly SettingsService $settings) {}

    public function edit()
    {
        return view('admin.settings.edit', [
            'settings' => $this->settings->all(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'service_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'cafe_name' => ['required', 'string', 'max:80'],
            'cafe_name_ar' => ['required', 'string', 'max:80'],
        ]);

        $this->settings->update($data);

        return back()->with('success', 'تم حفظ الإعدادات.');
    }
}
