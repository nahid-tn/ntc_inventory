<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;
use App\Models\CompanyInfo;
use Illuminate\Support\Facades\Validator;


class PurchaseOrderController extends Controller
{
    /*
    =====================================
    Purchase Order List
    =====================================
    */
    public function index()
    {
        $purchaseOrders = PurchaseOrder::with('supplier')
            ->latest()
            ->paginate(50);

        return view(
            'purchase_orders.index',
            compact('purchaseOrders')
        );
    }

    /*
    =====================================
    Create Page
    =====================================
    */
    public function create()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $products  = Product::orderBy('product_name')->get();
        $units     = Unit::orderBy('ShortName')->get();

        return view(
            'purchase_orders.create',
            compact(
                'suppliers',
                'products',
                'units'
            )
        );
    }

    /*
    =====================================
    Store Purchase Order
    =====================================
    */
    public function store(Request $request)
    {
        $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'products' => 'required|array',
        ]);

        DB::beginTransaction();

        try {

            /*
            =====================================
            Auto PO Number
            =====================================
            */
            $lastId = PurchaseOrder::max('id') + 1;
            $poNumber = 'PO-' . date('Y') . '-' . str_pad($lastId, 5, '0', STR_PAD_LEFT);

            /*
            =====================================
            Create Purchase Order
            =====================================
            */
            $purchaseOrder = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'subtotal' => $request->subtotal ?? 0,
                'discount' => $request->discount ?? 0,
                'shipping' => $request->shipping ?? 0,
                'tax' => $request->tax ?? 0,
                'total_amount' => $request->total_amount ?? 0,
                'status' => 'pending',
                'payment_method' => $request->payment_method,
                'note' => $request->note,
            ]);

            /*
            =====================================
            Save Details
            =====================================
            */
            foreach ($request->products as $item) {

                if (empty($item['product_id'])) {
                    continue;
                }
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $item['product_id'],
                    'qty' => $item['qty'] ?? 0,
                    'unit' => $item['unit'] ?? null,
                    'price' => $item['price'] ?? 0,
                    'total' => ($item['qty'] ?? 0) * ($item['price'] ?? 0),
                    'remarks' => $item['remarks'] ?? null,
                ]);
            }
            DB::commit();
            return redirect()
                ->route('purchase-orders.index')
                ->with(
                    'success',
                    'Purchase Order Created Successfully'
                );
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with(
                    'error',
                    $e->getMessage()
                );
        }
    }

    /*
    =====================================
    View Page
    =====================================
    */
    public function show($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'details.product'
        ])->findOrFail($id);

        return view(
            'purchase_orders.show',
            compact('purchaseOrder')
        );
    }

    /*
    =====================================
    Edit Page
    =====================================
    */
    public function edit($id)
    {
        $purchaseOrder = PurchaseOrder::with('details')
            ->findOrFail($id);
        $suppliers = Supplier::orderBy('supplier_name')->get();
        $products  = Product::orderBy('product_name')->get();
        $units     = Unit::orderBy('ShortName')->get();
        $purchaseOrder->load(['supplier', 'details.product']);

        return view(
            'purchase_orders.edit', compact(
            'purchaseOrder',
            'suppliers',
            'products',
            'units'
            )
        );
    }

    /*
    =====================================
    Update
    =====================================
    */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        // Validate the request
        $validator = Validator::make($request->all(), [
            'supplier_id' => 'required|exists:suppliers,id',
            'order_date' => 'required|date',
            'expected_date' => 'nullable|date|after_or_equal:order_date',
            'tax' => 'nullable|numeric|min:0',
            'shipping' => 'nullable|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'total_amount' => 'required|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id',
            'products.*.qty' => 'required|numeric|min:0.01',
            'products.*.unit' => 'required|string|max:10',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Update Purchase Order
            $purchaseOrder->update([
                'supplier_id' => $request->supplier_id,
                'order_date' => $request->order_date,
                'expected_date' => $request->expected_date,
                'tax' => $request->tax ?? 0,
                'shipping' => $request->shipping ?? 0,
                'discount' => $request->discount ?? 0,
                'total_amount' => $request->total_amount,
                'updated_by' => auth()->id(),
            ]);

            // Delete existing details
            $purchaseOrder->details()->delete();

            // Create new details
            foreach ($request->products as $productData) {
                PurchaseOrderDetail::create([
                    'purchase_order_id' => $purchaseOrder->id,
                    'product_id' => $productData['product_id'],
                    'qty' => $productData['qty'],
                    'unit' => $productData['unit'],
                    'price' => $productData['price'],
                    'total' => $productData['qty'] * $productData['price'],
                    'remarks' => $productData['remarks'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchase-orders.index')
                ->with('success', 'Purchase Order updated successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to update Purchase Order: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            DB::beginTransaction();

            // Delete details first
            $purchaseOrder->details()->delete();
            
            // Delete the purchase order
            $purchaseOrder->delete();

            DB::commit();

            return redirect()
                ->route('purchase-orders.index')
                ->with('success', 'Purchase Order deleted successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            
            return redirect()
                ->back()
                ->with('error', 'Failed to delete Purchase Order: ' . $e->getMessage());
        }
    }

    /**
     * Update purchase order status
     */
    public function updateStatus(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,approved,shipped,delivered,cancelled',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 422);
        }

        try {
            $purchaseOrder->update([
                'status' => $request->status,
                'updated_by' => auth()->id(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully!',
                'status' => $request->status
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Failed to update status: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get product details for autocomplete
     */
    public function productAutocomplete(Request $request)
    {
        $search = $request->get('term');
        
        $products = Product::where('product_name', 'LIKE', "%{$search}%")
            ->orWhere('barcode', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get();
        
        $results = [];
        
        foreach ($products as $product) {
            $results[] = [
                'id' => $product->id,
                'label' => $product->product_name . ' (Stock: ' . ($product->stock ?? 0) . ')',
                'value' => $product->product_name,
                'price' => $product->purchase_price ?? $product->selling_price ?? 0,
                'stock' => $product->stock ?? 0,
                'unit' => $product->unit ?? 'Pcs',
            ];
        }
        
        return response()->json($results);
    }

    /**
     * Get supplier details
     */
    public function supplierAutocomplete(Request $request)
    {
        $search = $request->get('term');
        
        $suppliers = Supplier::where('supplier_name', 'LIKE', "%{$search}%")
            ->orWhere('mobile', 'LIKE', "%{$search}%")
            ->limit(10)
            ->get();
        
        $results = [];
        
        foreach ($suppliers as $supplier) {
            $results[] = [
                'id' => $supplier->id,
                'label' => $supplier->supplier_name . ' (' . $supplier->mobile . ')',
                'value' => $supplier->supplier_name,
                'mobile' => $supplier->mobile,
                'address' => $supplier->address,
                'email' => $supplier->email ?? '',
            ];
        }
        
        return response()->json($results);
    }

    /*
    =====================================
    Convert To Final Purchase
    =====================================
    */
    public function convertToPurchase($id)
    {
        $purchaseOrder = PurchaseOrder::with('details')
            ->findOrFail($id);
        return back()->with(
            'success',
            'Convert To Purchase Logic Ready'
        );
    }

    
    public function pdf($id)
    {
        try {

            ini_set('memory_limit', '1024M');
            set_time_limit(0);

            // Purchase Order
            $purchaseOrder = PurchaseOrder::with([
                'supplier',
                'details.product'
            ])->findOrFail($id);

            // Company Information
            $company = \App\Models\CompanyInfo::first();

            // Logo
            $logoBase64 = null;
            $logoPath = public_path('images/logo.png');

            if (file_exists($logoPath)) {
                $logoBase64 = base64_encode(file_get_contents($logoPath));
            }

            // Blade Render
            $html = view('purchase_orders.pdf', compact(
                'purchaseOrder',
                'company',
                'logoBase64'
            ))->render();

            // mPDF
            $mpdf = new \Mpdf\Mpdf([
                'mode' => 'utf-8',
                'format' => 'A4',
                'margin_left'   => 8,
                'margin_right'  => 8,
                'margin_top'    => 12,
                'margin_bottom' => 12,
                'tempDir'       => storage_path('app/mpdf'),

                // যদি SolaimanLipi Font Configure করা থাকে
                'default_font' => 'solaimanlipi',
            ]);

            $mpdf->SetTitle('Purchase Order');
            $mpdf->SetAuthor(config('app.name'));
            $mpdf->SetDisplayMode('fullpage');

            // বাংলা সাপোর্ট
            $mpdf->autoScriptToLang = true;
            $mpdf->autoLangToFont = true;

            // HTML লিখুন
            $mpdf->WriteHTML($html);

            // Browser এ Open হবে
            return response(
                $mpdf->Output($purchaseOrder->po_number . '.pdf', 'S'),
                200,
                [
                    'Content-Type' => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="'.$purchaseOrder->po_number.'.pdf"',
                ]
            );

        } catch (\Exception $e) {

            \Log::error('Purchase Order PDF Error: '.$e->getMessage());
            return back()->with('error', $e->getMessage());
        }
    }
    
}