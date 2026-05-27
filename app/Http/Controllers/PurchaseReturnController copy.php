<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use Illuminate\Support\Facades\DB;
use PDF;

class PurchaseReturnController extends Controller
{
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['items.product', 'supplier']);

        // 🔍 Search (Supplier + Invoice)
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->whereHas('supplier', function($q2) use ($request) {
                    $q2->where('supplier_name', 'like', '%'.$request->search.'%');
                })
                ->orWhere('invoice_no', 'like', '%'.$request->search.'%');
            });
        }

        // 📅 Date Filter (Flexible)
        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->from_date);
        }

        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->to_date);
        }

        // 📄 Pagination
        $returns = $query->orderBy('return_date', 'desc')
                        ->paginate(15)
                        ->withQueryString();

        return view('purchase_returns.index', compact('returns'));
    }

   public function create()
    {
        return view('purchase_returns.create');
    }
    // Autocomplete for purchase invoice
    public function purchaseSearch(Request $request)
    {
        return Purchase::where('Invoice_no', 'LIKE', '%'.$request->term.'%')
            ->limit(10)
            ->get()
            ->map(fn($p) => [
                'id' => $p->id,
                'value' => $p->Invoice_no
            ]);
    }

    // Store returned product

    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string|max:255',
            'return_date' => 'required|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.qty'   => 'required|decimal:0,2|min:1',
            'products.*.unit' => 'nullable|string|max:50',
            'products.*.price' => 'required|decimal:0,2|min:0',
            'products.*.reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request) {

            // 1️⃣ Create PurchaseReturn record
            $return = PurchaseReturn::create([
                'invoice_no' => $request->invoice_no,
                'return_date' => $request->return_date,
                'supplier_id' => $request->supplier_id,
                'note' => $request->note ?? null,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->products as $product) {
                $total = $product['qty'] * $product['price'];

                // 2️⃣ Create PurchaseReturnItem only
                $return->items()->create([
                    'product_id' => $product['product_id'],
                    'qty' => $product['qty'],
                    'unit' => $product['unit'] ?? null,
                    'price' => $product['price'],
                    'total' => $total,
                    'reason' => $product['reason'] ?? null,
                ]);

                $totalAmount += $total;
            }

            // 3️⃣ Update total_amount in PurchaseReturn
            $return->update(['total_amount' => $totalAmount]);
        });

        return redirect()->route('purchase-return.index')
            ->with('success', 'Purchase return created successfully! No effect on Product or Purchase tables.');
    }

    // Edit return
    public function edit($id)
    {
        $return = PurchaseReturn::with(['items.product', 'supplier'])
                    ->findOrFail($id);
        return view('purchase_returns.edit', compact('return'));
    }

    // Update return
    public function update(Request $request, $id)
    {
        $request->validate([
            'invoice_no' => 'required|string|max:255',
            'return_date' => 'required|date',
            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'note' => 'nullable|string',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.qty'   => 'required|decimal:0,2|min:1',
            'products.*.unit' => 'nullable|string|max:50',
            'products.*.price' => 'required|decimal:0,2|min:0',
            'products.*.reason' => 'nullable|string',
        ]);

        DB::transaction(function () use ($request, $id) {

            $return = PurchaseReturn::findOrFail($id);
            // 🔹 Update master table
            $return->update([
                'invoice_no'   => $request->invoice_no,
                'return_date'  => $request->return_date,
                'supplier_id'  => $request->supplier_id,
                'note'         => $request->note ?? null,
                'total_amount' => 0, // temp
            ]);

            // Old items delete (important)
            $return->items()->delete();
            $totalAmount = 0;
            // 🔹 Insert updated items
            foreach ($request->products as $product) {
                $total = $product['qty'] * $product['price'];
                $return->items()->create([
                    'product_id' => $product['product_id'],
                    'qty'        => $product['qty'],
                    'unit'        => $product['unit'],
                    'price'      => $product['price'],
                    'total'      => $total,
                    'reason'     => $product['reason'] ?? null,
                ]);
                $totalAmount += $total;
            }

            // 🔹 Update total
            $return->update([
                'total_amount' => $totalAmount
            ]);
        });
        return redirect()->route('purchase-return.index')->with('success', 'Purchase return updated successfully!');
    }


    // Delete return
    public function destroy(PurchaseReturn $purchaseReturn)
    {
        try {
            DB::transaction(function () use ($purchaseReturn) {
                foreach ($purchaseReturn->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->stock += $item->qty;
                        $product->save();
                    }
                }
                $purchaseReturn->items()->delete();
                $purchaseReturn->delete();
            });
            return redirect()
                ->route('purchase-return.index')
                ->with('success', 'Return deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
    }

    // 📊 REPORT
    public function report(){
        $returns = PurchaseReturn::with(['items.product','supplier'])->get();
        return view('purchase_returns.report',compact('returns'));
    }
    
    public function SuplierReport(Request $request)
    {
        $query = PurchaseReturn::with(['items.product', 'supplier']);
        if ($request->filled('supplier_id')) {
            $query->where('supplier_id', $request->supplier_id);
        }
        if ($request->filled('from_date')) {
            $query->whereDate('return_date', '>=', $request->from_date);
        }
        if ($request->filled('to_date')) {
            $query->whereDate('return_date', '<=', $request->to_date);
        }
        $returns = $query->get();
        $suppliers = Supplier::all();
        return view('purchase_returns.supplier_report', compact('returns','suppliers'));
    }
    
    public function purchaseInvoiceSearch(Request $request)
    {
        return Purchase::where('Invoice_no','LIKE','%'.$request->term.'%')
            ->limit(10)
            ->get()
            ->map(fn($p)=>[
                'id'=>$p->id,
                'value'=>$p->Invoice_no,
                'label'=>'Invoice: '.$p->Invoice_no
            ]);
    }
   // Load purchase products
    public function purchaseDetails($purchase_id)
    {
        $details = DB::table('purchase_details')
            ->join('products','products.id','=','purchase_details.product_id')
            ->where('purchase_details.purchase_id', $purchase_id)
            ->select('products.id', 'products.product_name', 'purchase_details.qty', 'purchase_details.price')
            ->get();
        return response()->json($details);
    }

    public function show($id)
    {
        $return = PurchaseReturn::with(['items.product', 'supplier'])->findOrFail($id);
        return view('purchase_returns.show', compact('return'));
    }

    public function exportPdf($id)
    {
        // Fetch Purchase Return with relationships
        $return = PurchaseReturn::with(['items.product', 'supplier'])
                    ->findOrFail($id);

        // Format values for file name
        $date = \Carbon\Carbon::parse($return->return_date)->format('d-m-Y');
        $invoiceNo = $return->return_no ?? 'NoInv';
        $supplierName = $return->supplier->supplier_name ?? 'NoSupplier';
        $supplierName = str_replace(' ', '_', $supplierName); // File safe

        // Final PDF file name
        $filename = $date . '-' . $invoiceNo . '-' . $supplierName . '.pdf';

        // Generate PDF
        $pdf = PDF::loadView('purchase_returns.pdf', compact('return'));
        $pdf->setPaper('A4', 'portrait');

        // Download
        return $pdf->download($filename);
    }

    public function reportPdf(Request $request)
    {
        $query = PurchaseReturn::with('items.product', 'supplier');
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
            $supplier = Supplier::find($request->supplier_id);
        } else {
            $supplier = null;
        }
        if ($request->from_date && $request->to_date) {
            $query->whereBetween('return_date', [$request->from_date, $request->to_date]);
        }
        $returns = $query->get();
        $pdf = PDF::loadView('purchase_returns.report_pdf', [
            'returns' => $returns,
            'supplier' => $supplier,
            'from_date' => $request->from_date,
            'to_date' => $request->to_date
        ]);
        return $pdf->download('purchase-return-report.pdf');
    }
    public function reportForm(Request $request)
    {
        $suppliers = Supplier::all();

        // ✅ শুধু যেসব product return এ আছে
        $products = Product::whereIn('id', function ($q) {
            $q->select('product_id')
            ->from('purchase_return_items'); // তোমার item table
        })->get();
        $returns = collect();
        if ($request->filled(['from_date','to_date'])) {
            $returns = PurchaseReturn::with([
        'supplier',
        'items' => function ($q) use ($request) {
            if ($request->product_id) {
                $q->where('product_id', $request->product_id);
            }
            $q->with('product');
        }
    ])
        ->when($request->supplier_id, function ($q) use ($request) {
        $q->where('supplier_id', $request->supplier_id);
        })

        ->when($request->product_id, function ($q) use ($request) {
        $q->whereHas('items', function ($qq) use ($request) {
            $qq->where('product_id', $request->product_id);
        });
    })
        ->whereBetween('return_date', [
        $request->from_date,
        $request->to_date
    ])
            ->orderBy('return_no','asc')
            ->get();
        }
        return view('purchase_returns.reportform', compact(
            'suppliers',
            'products',
            'returns'
        ));
    }

    public function generate(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
        ]);

        // Query with relationships
        $query = PurchaseReturn::with(['items.product', 'supplier'])
            ->whereBetween('return_date', [
                $request->from_date,
                $request->to_date
            ]);

        // Filter by supplier
        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        // Filter by product
        if ($request->product_id) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        // Get data
        $returns = $query->get();

        // Calculate overall total
        $overallTotal = 0;
        foreach ($returns as $r) {
            $overallTotal += $r->items->sum('total');
        }

        // Supplier name for header
        $supplier_name = 'All';
        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $supplier_name = $supplier->supplier_name ?? 'All';
        }

        // Generate file name based on first record
        if ($returns->count()) {
            $first = $returns->first();

            $date = \Carbon\Carbon::parse($first->return_date)->format('d-m-Y');
            $invoiceNo = $first->return_no ?? 'NoInv';
            $supplierName = $first->supplier->supplier_name ?? 'NoSupplier';
            $supplierName = str_replace(' ', '_', $supplierName); // File safe

            $filename = $date . '-' . $invoiceNo . '-' . $supplierName . '.pdf';
        } else {
            $filename = 'Purchase_Return_NoData.pdf';
        }

        // Generate PDF
        $pdf = \PDF::loadView('purchase_returns.report_pdf', [
            'returns'      => $returns,
            'from_date'    => $request->from_date,
            'to_date'      => $request->to_date,
            'overallTotal' => $overallTotal,
            'supplier'     => $request->supplier_id
                                ? Supplier::find($request->supplier_id)
                                : null,
        ]);

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download($filename);
    }

    public function itemWiseReportForm()
    {
        $suppliers = Supplier::orderBy('supplier_name')->get();
        return view('purchase_returns.item_wise_report', compact('suppliers'));
    }

    // Generate Item Wise Report
    public function itemWiseReport(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date'
        ]);

        $from_date   = $request->from_date;
        $to_date     = $request->to_date;
        $supplier_id = $request->supplier_id;

        // ---------------------------
        // Get Items
        // ---------------------------
        $items = PurchaseReturnItem::with(['product', 'purchaseReturn.supplier'])
            ->whereHas('purchaseReturn', function ($q) use ($from_date, $to_date, $supplier_id) {
                $q->whereBetween('return_date', [$from_date, $to_date]);
                if ($supplier_id) {
                    $q->where('supplier_id', $supplier_id);
                }
            })
            ->get();


        // ---------------------------
        // Group By Product
        // ---------------------------
        $rows = $items->groupBy('product_id')->map(function ($group) {

            return [

                'product' => $group->first()->product,
                'qty'     => $group->sum('qty'),
                'unit'    => $group->first()->unit,
                'total'   => $group->sum('total'),
                'returns' => $group->pluck('purchaseReturn'),
            ];
        });


        // ---------------------------
        // Supplier Info
        // ---------------------------
        $supplier = null;

        if ($supplier_id) {
            $supplier = Supplier::find($supplier_id);
        }


        // ---------------------------
        // PDF Download
        // ---------------------------
        if ($request->has('pdf')) {

            $pdf = PDF::loadView(
                'purchase_returns.item_wise_report_pdf',
                [
                    'rows'       => $rows,
                    'from_date'  => $from_date,
                    'to_date'    => $to_date,
                    'supplier'   => $supplier,
                ]
            );

            return $pdf->download('Purchase_Return_Item_Wise_Report.pdf');
        }


        // ---------------------------
        // Normal View
        // ---------------------------
        $suppliers = Supplier::orderBy('supplier_name')->get();

        return view(
            'purchase_returns.item_wise_report',
            compact(
                'rows',
                'suppliers',
                'from_date',
                'to_date',
                'supplier_id'
            )
        );
    }

// Product details
public function productReturnDetails($product_id, Request $request)
{
    $from_date = $request->from_date;
    $to_date   = $request->to_date;
    $supplier_id = $request->supplier_id;

    $details = PurchaseReturnItem::with(['product', 'purchaseReturn.supplier'])
        ->where('product_id', $product_id)
        ->whereHas('purchaseReturn', function($q) use($from_date, $to_date, $supplier_id){
            $q->whereBetween('return_date', [$from_date, $to_date]);
            if($supplier_id){
                $q->where('supplier_id', $supplier_id);
            }
        })
        ->get();

    return view('purchase_returns.product_details', compact('details'));
}
}