<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')->latest('id');

        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('name', 'like', "%{$s}%")
                  ->orWhere('code', 'like', "%{$s}%")
                  ->orWhere('barcode', 'like', "%{$s}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        if ($request->filled('stock_status')) {
            if ($request->stock_status === 'low') {
                $query->whereColumn('stock_quantity', '<=', 'min_stock_alert');
            } elseif ($request->stock_status === 'out') {
                $query->where('stock_quantity', '<=', 0);
            }
        }

        $products = $query->paginate(15)->withQueryString();
        $categories = Category::where('is_active', true)->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create()
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'code' => 'nullable|string|max:100|unique:products,code',
            'barcode' => 'nullable|string|max:100|unique:products,barcode',
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'customs_cost_per_unit' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $purchasePrice = (float)$validated['purchase_price'];
        $customsCost = (float)($validated['customs_cost_per_unit'] ?? 0);
        $actualCost = $purchasePrice + $customsCost;

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('products', 'public');
        }

        $product = Product::create([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'code' => $validated['code'] ?? ('PRD-' . rand(1000, 9999)),
            'barcode' => $validated['barcode'] ?? null,
            'unit' => $validated['unit'],
            'purchase_price' => $purchasePrice,
            'customs_cost_per_unit' => $customsCost,
            'actual_cost' => $actualCost,
            'selling_price' => (float)$validated['selling_price'],
            'min_selling_price' => $validated['min_selling_price'] ? (float)$validated['min_selling_price'] : null,
            'stock_quantity' => (float)$validated['stock_quantity'],
            'min_stock_alert' => (int)($validated['min_stock_alert'] ?? 5),
            'description' => $validated['description'] ?? null,
            'image_path' => $imagePath,
            'is_active' => true,
        ]);

        if ($product->stock_quantity > 0) {
            InventoryLog::create([
                'product_id' => $product->id,
                'type' => 'purchase',
                'quantity_change' => $product->stock_quantity,
                'quantity_before' => 0,
                'quantity_after' => $product->stock_quantity,
                'reference_type' => 'رصيد افتتاحي',
                'notes' => 'إضافة صنف جديد مع رصيد افتتاحي',
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'تمت إضافة المنتج بنجاح وحساب التكلفة الفعلية.');
    }

    public function edit(Product $product)
    {
        $categories = Category::where('is_active', true)->get();
        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'code' => 'nullable|string|max:100|unique:products,code,' . $product->id,
            'barcode' => 'nullable|string|max:100|unique:products,barcode,' . $product->id,
            'unit' => 'required|string|max:50',
            'purchase_price' => 'required|numeric|min:0',
            'customs_cost_per_unit' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'min_selling_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'min_stock_alert' => 'nullable|integer|min:0',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'is_active' => 'nullable|boolean',
        ]);

        $purchasePrice = (float)$validated['purchase_price'];
        $customsCost = (float)($validated['customs_cost_per_unit'] ?? 0);
        $actualCost = $purchasePrice + $customsCost;

        $qtyBefore = (float)$product->stock_quantity;
        $qtyAfter = (float)$validated['stock_quantity'];

        if ($request->hasFile('image')) {
            if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
                Storage::disk('public')->delete($product->image_path);
            }
            $product->image_path = $request->file('image')->store('products', 'public');
        }

        $product->update([
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'code' => $validated['code'] ?? $product->code,
            'barcode' => $validated['barcode'] ?? null,
            'unit' => $validated['unit'],
            'purchase_price' => $purchasePrice,
            'customs_cost_per_unit' => $customsCost,
            'actual_cost' => $actualCost,
            'selling_price' => (float)$validated['selling_price'],
            'min_selling_price' => $validated['min_selling_price'] ? (float)$validated['min_selling_price'] : null,
            'stock_quantity' => $qtyAfter,
            'min_stock_alert' => (int)($validated['min_stock_alert'] ?? 5),
            'description' => $validated['description'] ?? null,
            'is_active' => $request->has('is_active') ? (bool)$request->is_active : true,
        ]);

        if ($qtyAfter != $qtyBefore) {
            InventoryLog::create([
                'product_id' => $product->id,
                'type' => $qtyAfter > $qtyBefore ? 'adjustment' : 'damage',
                'quantity_change' => $qtyAfter - $qtyBefore,
                'quantity_before' => $qtyBefore,
                'quantity_after' => $qtyAfter,
                'reference_type' => 'تعديل مباشر من صفحة المنتج',
                'notes' => 'تحديث رصيد المستودع من صفحة التعديل',
                'user_id' => Auth::id(),
            ]);
        }

        return redirect()->route('products.index')->with('success', 'تم تعديل بيانات المنتج ورصيد المخزون بنجاح.');
    }

    public function adjustStock(Request $request, Product $product)
    {
        $validated = $request->validate([
            'adjustment_type' => 'required|in:add,subtract,set',
            'quantity' => 'required|numeric|min:0.01',
            'notes' => 'required|string|max:255',
        ]);

        $qtyBefore = (float)$product->stock_quantity;
        $amount = (float)$validated['quantity'];

        if ($validated['adjustment_type'] === 'add') {
            $qtyChange = $amount;
            $qtyAfter = $qtyBefore + $amount;
            $logType = 'adjustment';
        } elseif ($validated['adjustment_type'] === 'subtract') {
            $qtyChange = -$amount;
            $qtyAfter = max(0, $qtyBefore - $amount);
            $logType = 'damage';
        } else {
            $qtyChange = $amount - $qtyBefore;
            $qtyAfter = $amount;
            $logType = 'adjustment';
        }

        $product->update(['stock_quantity' => $qtyAfter]);

        InventoryLog::create([
            'product_id' => $product->id,
            'type' => $logType,
            'quantity_change' => $qtyChange,
            'quantity_before' => $qtyBefore,
            'quantity_after' => $qtyAfter,
            'reference_type' => 'تسوية يدوية',
            'notes' => $validated['notes'],
            'user_id' => Auth::id(),
        ]);

        return back()->with('success', "تم تعديل رصيد المخزون للمنتج ({$product->name}) إلى {$qtyAfter} {$product->unit}");
    }

    public function destroy(Product $product)
    {
        if ($product->invoiceItems()->exists()) {
            return back()->with('error', 'لا يمكن حذف هذا المنتج لأنه مرتبط بفواتير بيع سابقة. يمكنك إلغاء تفعيله بدلاً من حذفه.');
        }

        if ($product->image_path && Storage::disk('public')->exists($product->image_path)) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();
        return redirect()->route('products.index')->with('success', 'تم حذف المنتج بنجاح.');
    }
}
