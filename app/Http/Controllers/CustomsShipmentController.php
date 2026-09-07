<?php

namespace App\Http\Controllers;

use App\Models\CustomsShipment;
use App\Models\Product;
use App\Services\LandedCostService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomsShipmentController extends Controller
{
    public function index()
    {
        $shipments = CustomsShipment::withCount('items')->latest('shipment_date')->paginate(15);
        return view('customs.index', compact('shipments'));
    }

    public function create()
    {
        $products = Product::where('is_active', true)->get();
        return view('customs.create', compact('products'));
    }

    public function store(Request $request, LandedCostService $service)
    {
        $validated = $request->validate([
            'shipment_number' => 'required|string|max:100|unique:customs_shipments,shipment_number',
            'supplier_name' => 'required|string|max:255',
            'shipment_date' => 'required|date',
            'origin_country' => 'nullable|string|max:100',
            'customs_fees' => 'required|numeric|min:0',
            'shipping_fees' => 'required|numeric|min:0',
            'clearance_fees' => 'required|numeric|min:0',
            'other_fees' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.unit_purchase_price' => 'required|numeric|min:0',
            'apply_inventory' => 'nullable|boolean',
        ]);

        $shipment = CustomsShipment::create([
            'shipment_number' => $validated['shipment_number'],
            'supplier_name' => $validated['supplier_name'],
            'shipment_date' => $validated['shipment_date'],
            'origin_country' => $validated['origin_country'] ?? 'مستورد',
            'customs_fees' => (float)$validated['customs_fees'],
            'shipping_fees' => (float)$validated['shipping_fees'],
            'clearance_fees' => (float)$validated['clearance_fees'],
            'other_fees' => (float)($validated['other_fees'] ?? 0),
            'notes' => $validated['notes'] ?? null,
            'status' => 'draft',
        ]);

        $apply = $request->boolean('apply_inventory');
        $service->processLandedCost($shipment, $validated['items'], $apply, Auth::id());

        $msg = $apply 
            ? 'تم حفظ الشحنة، واحتساب كلفة الجمارك والشحن، وتحديث أرصدة وتكاليف المنتجات في المستودع بنجاح!'
            : 'تم حفظ مسودة الشحنة وحساب كلفة الجمارك بنجاح.';

        return redirect()->route('customs.show', $shipment)->with('success', $msg);
    }

    public function show(CustomsShipment $customs)
    {
        $customs->load(['items.product']);
        return view('customs.show', ['shipment' => $customs]);
    }

    public function applyToInventory(CustomsShipment $customs, LandedCostService $service)
    {
        if ($customs->status === 'applied') {
            return back()->with('info', 'هذه الشحنة تم تطبيقها وإضافتها للمخزون مسبقاً.');
        }

        $itemsData = $customs->items->map(function ($item) {
            return [
                'product_id' => $item->product_id,
                'quantity' => $item->quantity,
                'unit_purchase_price' => $item->unit_purchase_price,
            ];
        })->toArray();

        $service->processLandedCost($customs, $itemsData, true, Auth::id());

        return back()->with('success', 'تم ترحيل الشحنة للمخزون بنجاح وتحديث أسعار التكلفة الفعلية بعد الجمارك لجميع الأصناف.');
    }
}
