<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        $setting = Setting::instance();
        return view('settings.edit', compact('setting'));
    }

    public function update(Request $request)
    {
        $setting = Setting::instance();

        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'tax_number' => 'nullable|string|max:100',
            'commercial_registry' => 'nullable|string|max:100',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:255',
            'currency_name' => 'required|string|max:50',
            'currency_symbol' => 'required|string|max:20',
            'tax_rate' => 'required|numeric|min:0|max:100',
            'tax_enabled_default' => 'nullable|boolean',
            'invoice_footer_text' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($setting->logo_path && Storage::disk('public')->exists($setting->logo_path)) {
                Storage::disk('public')->delete($setting->logo_path);
            }
            $setting->logo_path = $request->file('logo')->store('settings', 'public');
        }

        $setting->update([
            'company_name' => $validated['company_name'],
            'tax_number' => isset($validated['tax_number']) ? str_replace(' ', '', $validated['tax_number']) : null,
            'commercial_registry' => isset($validated['commercial_registry']) ? str_replace(' ', '', $validated['commercial_registry']) : null,
            'phone' => isset($validated['phone']) ? str_replace(' ', '', $validated['phone']) : null,
            'email' => $validated['email'] ?? null,
            'address' => $validated['address'] ?? null,
            'currency_name' => $validated['currency_name'],
            'currency_symbol' => $validated['currency_symbol'],
            'tax_rate' => (float)$validated['tax_rate'],
            'tax_enabled_default' => $request->has('tax_enabled_default'),
            'invoice_footer_text' => $validated['invoice_footer_text'] ?? null,
        ]);

        return redirect()->route('settings.edit')->with('success', 'تم حفظ وتحديث إعدادات المؤسسة والبيانات الضريبية بنجاح.');
    }
}
