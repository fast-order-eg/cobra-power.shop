<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::withCount('invoices')->latest('id');

        if ($request->filled('search')) {
            $s = str_replace(' ', '', $request->search);
            $query->where(function ($q) use ($request, $s) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$s}%")
                  ->orWhere('tax_number', 'like', "%{$s}%")
                  ->orWhere('national_id', 'like', "%{$s}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();
        $totalCustomers = Customer::count();

        return view('customers.index', compact('customers', 'totalCustomers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name',
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'customer_type' => 'nullable|in:individual,company',
        ], [
            'name.unique' => 'يوجد عميل مسجل بهذا الاسم بالفعل.',
            'name.required' => 'يرجى إدخال اسم العميل أو الشركة.',
        ]);

        $phone = !empty($validated['phone']) ? str_replace(' ', '', $validated['phone']) : null;
        $taxNumber = !empty($validated['tax_number']) ? str_replace(' ', '', $validated['tax_number']) : null;
        $nationalId = !empty($validated['national_id']) ? str_replace(' ', '', $validated['national_id']) : null;

        $customer = Customer::create([
            'name' => trim($validated['name']),
            'phone' => $phone,
            'tax_number' => $taxNumber,
            'national_id' => $nationalId,
            'address' => $validated['address'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 'individual',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => "تم إضافة العميل ({$customer->name}) بنجاح.",
                'customer' => [
                    'id' => $customer->id,
                    'name' => $customer->name,
                    'phone' => $customer->phone,
                    'tax_number' => $customer->tax_number,
                ]
            ]);
        }

        return redirect()->back()->with('success', "تم إضافة العميل ({$customer->name}) بنجاح.");
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:customers,name,' . $customer->id,
            'phone' => 'nullable|string|max:50',
            'tax_number' => 'nullable|string|max:100',
            'national_id' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'customer_type' => 'nullable|in:individual,company',
        ], [
            'name.unique' => 'يوجد عميل آخر مسجل بهذا الاسم بالفعل.',
        ]);

        $phone = !empty($validated['phone']) ? str_replace(' ', '', $validated['phone']) : null;
        $taxNumber = !empty($validated['tax_number']) ? str_replace(' ', '', $validated['tax_number']) : null;
        $nationalId = !empty($validated['national_id']) ? str_replace(' ', '', $validated['national_id']) : null;

        $customer->update([
            'name' => trim($validated['name']),
            'phone' => $phone,
            'tax_number' => $taxNumber,
            'national_id' => $nationalId,
            'address' => $validated['address'] ?? null,
            'customer_type' => $validated['customer_type'] ?? 'individual',
        ]);

        return redirect()->back()->with('success', "تم تحديث بيانات العميل ({$customer->name}) بنجاح.");
    }

    public function destroy(Customer $customer)
    {
        $name = $customer->name;

        // Disassociate invoices safely without deleting any invoice data
        $customer->invoices()->update(['customer_id' => null]);

        $customer->delete();

        return redirect()->back()->with('success', "تم حذف العميل ({$name}) بنجاح مع الاحتفاظ بكافة فواتيره وسجلاته السابقة.");
    }
}
