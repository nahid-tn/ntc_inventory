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
// use Barryvdh\DomPDF\Facade\Pdf;
use Mpdf\Mpdf;
use Mpdf\Output\Destination;

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
    public function update(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->update([
            'supplier_id' => $request->supplier_id,
            'order_date' => $request->order_date,
            'expected_date' => $request->expected_date,
            'subtotal' => $request->subtotal ?? 0,
            'discount' => $request->discount ?? 0,
            'shipping' => $request->shipping ?? 0,
            'tax' => $request->tax ?? 0,
            'total_amount' => $request->total_amount ?? 0,
            'payment_method' => $request->payment_method,
            'note' => $request->note,
        ]);

        return redirect()
            ->route('purchase-orders.index')
            ->with(
                'success',
                'Purchase Order Updated Successfully'
            );
    }

    /*
    =====================================
    Delete
    =====================================
    */
    public function destroy($id)
    {
        $purchaseOrder = PurchaseOrder::findOrFail($id);
        $purchaseOrder->delete();
        return back()->with(
            'success',
            'Purchase Order Deleted Successfully'
        );
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
        $purchaseOrder = PurchaseOrder::with([
            'supplier',
            'details.product'
        ])->findOrFail($id);

        $pdf = Pdf::loadView(
            'purchase_orders.pdf',
            compact('purchaseOrder')
        );

        return $pdf->download($purchaseOrder->po_number . '.pdf');
    }
    
}