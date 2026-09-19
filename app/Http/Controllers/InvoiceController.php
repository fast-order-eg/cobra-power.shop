<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Product;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Setting;
use App\Models\InventoryLog;
use App\Services\JordanTaxService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['customer', 'creator'])->latest('id');

        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->invoice_type);
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('from_date')) {
            $query->whereDate('invoice_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('invoice_date', '<=', $request->to_date);
        }

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('invoice_number', 'like', "%{$s}%")
                  ->orWhere('customer_name', 'like', "%{$s}%")
                  ->orWhereHas('customer', fn($cq) => $cq->where('name', 'like', "%{$s}%")->orWhere('phone', 'like', "%{$s}%"));
            });
        }

        $invoices = $query->paginate(15)->withQueryString();

        // Statistics for current filtered view
        $filteredQuery = clone $query;
        $totalSum = $filteredQuery->sum('total_amount');
        $taxSum = $filteredQuery->sum('tax_amount');
        $profitSum = $filteredQuery->sum('profit_margin');

        return view('invoices.index', compact('invoices', 'totalSum', 'taxSum', 'profitSum'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->with(['products' => function ($q) {
            $q->where('is_active', true);
        }])->get();

        $products = Product::where('is_active', true)->where('stock_quantity', '>', 0)->get();
        $customers = Customer::orderBy('name')->get();
        $setting = Setting::instance();
        $nextInvoiceNumber = 'INV-' . date('Y') . '-' . str_pad((Invoice::max('id') + 1), 4, '0', STR_PAD_LEFT);

        return view('invoices.pos', compact('categories', 'products', 'customers', 'setting', 'nextInvoiceNumber'));
    }

    public function store(Request $request, JordanTaxService $taxService)
    {
        $validated = $request->validate([
            'invoice_number' => 'required|string|unique:invoices,invoice_number',
            'invoice_type' => 'required|in:tax,non_tax',
            'customer_id' => 'nullable|exists:customers,id',
            'customer_name' => 'nullable|string|max:255',
            'invoice_date' => 'required|date',
            'payment_method' => 'required|in:cash,card,credit,bank',
            'discount_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:0.1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $invoice = DB::transaction(function () use ($validated, $request, $taxService) {
            $setting = Setting::instance();
            $isTax = $validated['invoice_type'] === 'tax';
            $taxRate = $isTax ? (float)$setting->tax_rate : 0.00;

            $subtotal = 0;
            $totalCost = 0;
            $itemsToCreate = [];

            foreach ($validated['items'] as $itemData) {
                $product = Product::findOrFail($itemData['product_id']);
                $qty = (float)$itemData['quantity'];
                $unitPrice = (float)$itemData['unit_price'];
                $itemTotal = $qty * $unitPrice;
                $unitCost = (float)$product->actual_cost;
                $itemCost = $qty * $unitCost;

                $subtotal += $itemTotal;
                $totalCost += $itemCost;

                $itemsToCreate[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'quantity' => $qty,
                    'unit_cost' => $unitCost,
                    'unit_price' => $unitPrice,
                    'discount' => 0,
                    'tax_amount' => 0,
                    'total_price' => $itemTotal,
                    'total_cost' => $itemCost,
                ];

                // Deduct stock
                $qtyBefore = (float)$product->stock_quantity;
                $qtyAfter = max(0, $qtyBefore - $qty);
                $product->update(['stock_quantity' => $qtyAfter]);

                // Inventory Log
                InventoryLog::create([
                    'product_id' => $product->id,
                    'type' => 'sale',
                    'quantity_change' => -$qty,
                    'quantity_before' => $qtyBefore,
                    'quantity_after' => $qtyAfter,
                    'reference_type' => 'فاتورة مبيعات',
                    'notes' => "خصم مبيعات فاتورة رقم {$validated['invoice_number']}",
                    'user_id' => Auth::id(),
                ]);
            }

            $discount = (float)($validated['discount_amount'] ?? 0);
            $taxableAmount = max(0, $subtotal - $discount);
            $taxAmount = $isTax ? ($taxableAmount * ($taxRate / 100)) : 0.000;
            $totalAmount = $taxableAmount + $taxAmount;
            $paidAmount = (isset($validated['paid_amount']) && $validated['paid_amount'] !== null) ? (float)$validated['paid_amount'] : $totalAmount;
            $remainingAmount = max(0, $totalAmount - $paidAmount);
            $profitMargin = $taxableAmount - $totalCost;

            $customerName = $validated['customer_name'] ?? null;
            if (!empty($validated['customer_id'])) {
                $cust = Customer::find($validated['customer_id']);
                if ($cust) {
                    $customerName = $cust->name;
                    if ($remainingAmount > 0) {
                        $cust->increment('current_balance', $remainingAmount);
                    }
                }
            }

            $invoice = Invoice::create([
                'invoice_number' => $validated['invoice_number'],
                'customer_id' => $validated['customer_id'] ?? null,
                'customer_name' => $customerName ?? 'زبون نقدي',
                'invoice_type' => $validated['invoice_type'],
                'invoice_date' => $validated['invoice_date'],
                'payment_method' => $validated['payment_method'],
                'subtotal' => $subtotal,
                'discount_amount' => $discount,
                'tax_rate' => $taxRate,
                'tax_amount' => $taxAmount,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'remaining_amount' => $remainingAmount,
                'total_cost' => $totalCost,
                'profit_margin' => $profitMargin,
                'notes' => $validated['notes'] ?? null,
                'created_by' => Auth::id(),
            ]);

            foreach ($itemsToCreate as $item) {
                $invoice->items()->create($item);
            }

            if ($isTax) {
                $invoice->qr_payload = $taxService->generateQrCode($invoice);
                $invoice->save();
            }

            return $invoice;
        });

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'invoice_id' => $invoice->id,
                'invoice_number' => $invoice->invoice_number,
                'message' => 'تم حفظ الفاتورة بنجاح',
            ]);
        }

        return redirect()->route('invoices.show', $invoice)->with('success', 'تم إصدار الفاتورة وتحديث رصيد المستودع بنجاح.');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'creator']);
        $setting = Setting::instance();
        return view('invoices.show', compact('invoice', 'setting'));
    }

    public function printThermal(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'creator']);
        $setting = Setting::instance();
        return view('invoices.print_thermal', compact('invoice', 'setting'));
    }

    public function printA4(Invoice $invoice)
    {
        $invoice->load(['items.product', 'customer', 'creator']);
        $setting = Setting::instance();
        return view('invoices.print_a4', compact('invoice', 'setting'));
    }

    public function syncTax(Invoice $invoice, JordanTaxService $taxService)
    {
        $result = $taxService->syncInvoiceToJordanTax($invoice);
        if ($result['success']) {
            return back()->with('success', $result['message']);
        }
        return back()->with('error', $result['message']);
    }

    public function destroy(Invoice $invoice)
    {
        DB::transaction(function () use ($invoice) {
            $invoice->load(['items']);

            // 1. Restore stock quantity for all items
            foreach ($invoice->items as $item) {
                if ($item->product_id) {
                    $product = Product::find($item->product_id);
                    if ($product) {
                        $qtyBefore = (float)$product->stock_quantity;
                        $qtyAfter = $qtyBefore + (float)$item->quantity;
                        $product->update(['stock_quantity' => $qtyAfter]);

                        InventoryLog::create([
                            'product_id' => $product->id,
                            'type' => 'purchase',
                            'quantity_change' => (float)$item->quantity,
                            'quantity_before' => $qtyBefore,
                            'quantity_after' => $qtyAfter,
                            'reference_type' => 'إلغاء/حذف فاتورة مبيعات',
                            'notes' => "إعادة الكمية للمستودع بسبب حذف الفاتورة رقم {$invoice->invoice_number}",
                            'user_id' => Auth::id(),
                        ]);
                    }
                }
            }

            // 2. Adjust customer balance if remaining amount > 0
            if ($invoice->customer_id && $invoice->remaining_amount > 0) {
                $customer = Customer::find($invoice->customer_id);
                if ($customer) {
                    $customer->decrement('current_balance', (float)$invoice->remaining_amount);
                }
            }

            // 3. Delete items and invoice
            $invoice->items()->delete();
            $invoice->delete();
        });

        return redirect()->route('invoices.index')->with('success', 'تم حذف الفاتورة وإعادة الكميات المباعة للمستودع وتعديل حساب العميل بنجاح.');
    }
}
