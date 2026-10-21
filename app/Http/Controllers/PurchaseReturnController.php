<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PurchaseReturn;
use App\Models\PurchaseReturnItem;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use App\Models\Purchase;
use App\Models\PurchaseDetail;
use App\Models\Supplier;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;
use Carbon\Carbon;
use App\Models\CompanyInfo;
use Picqer\Barcode\BarcodeGeneratorPNG;


class PurchaseReturnController extends Controller
{
    /**
     * Return List
     */
    public function index(Request $request)
    {
        $query = PurchaseReturn::with(['items.product', 'supplier', 'supplier.companyInfo']);
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
    /**
     * Create Form
     */
    public function create()
    {
        $products = Product::orderBy('product_name', 'asc')->get();
        return view('purchase_returns.create', compact('products'));
    }

    /**
     * Store Return + Reduce Stock
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'invoice_no' => 'required|string|max:255',
    //         'return_date' => 'required|date',
    //         'supplier_id' => 'nullable|integer|exists:suppliers,id',
    //         'note' => 'nullable|string',
    //         'products' => 'required|array|min:1',
    //         'products.*.product_id' => 'required|integer|exists:products,id',
    //         'products.*.qty'   => 'required|decimal:0,2|min:1',
    //         'products.*.unit' => 'nullable|string|max:50',
    //         'products.*.price' => 'required|decimal:0,2|min:0',
    //         'products.*.reason' => 'nullable|string',
    //     ]);

    //     DB::beginTransaction();

    //     try {
    //         $grandTotal = 0;
    //         foreach ($request->products as $item) {
    //             $grandTotal += ($item['qty'] * $item['price']);
    //         }
    //         /**
    //          * Main Return Entry
    //          */
    //         $purchaseReturn = PurchaseReturn::create([
    //             'invoice_no' => $request->invoice_no,
    //             'return_date' => $request->return_date,
    //             'total_amount' => $grandTotal,
    //             'note' => $request->note,
    //             'supplier_id' => $request->supplier_id,
    //         ]);

    //         /**
    //          * Return Items + Stock Minus
    //          */
    //         foreach ($request->products as $item) {
    //             $product = Product::findOrFail($item['product_id']);
    //             /**
    //              * Safety Check
    //              * বেশি return দিলে error দিবে
    //              */
    //             if ($item['qty'] > $product->stock) {
    //                 throw new \Exception(
    //                     "Insufficient stock for product: " . $product->product_name
    //                 );
    //             }
    //             $total = $item['qty'] * $item['price'];
    //             PurchaseReturnItem::create([
    //                 'purchase_return_id' => $purchaseReturn->id,
    //                 'product_id' => $item['product_id'],
    //                 'qty' => $item['qty'],
    //                 'unit' => $item['unit'] ?? null,
    //                 'price' => $item['price'],
    //                 'total' => $total,
    //                 'reason' => $item['reason'] ?? null,
    //             ]);

    //             /**
    //              * IMPORTANT:
    //              * Return হলে stock কমবে
    //              */
    //             $product->decrement('stock', $item['qty']);
    //             /**
    //              * Auto status update
    //              */
    //             $product->status = $product->stock <= 5
    //                 ? 'Low Stock'
    //                 : 'In Stock';
    //             $product->save();
    //         }

    //         DB::commit();

    //         return redirect()
    //             ->route('purchase-return.index')
    //             ->with('success', 'Purchase Return Created Successfully & Stock Reduced!');
    //     } catch (\Exception $e) {

    //         DB::rollBack();

    //         return redirect()
    //             ->back()
    //             ->withInput()
    //             ->withErrors($e->getMessage());
    //     }
    // }
    public function store(Request $request)
    {
        $request->validate([
            'invoice_no' => 'required|string|max:255',
            'return_date' => 'required|date',
            'transport_cost' => 'nullable|numeric|min:0',

            // ✅ FIXED: Image + PDF both allowed
            'transport_receive' => 'nullable|file|mimes:jpg,jpeg,png,webp,pdf|max:5120',

            'supplier_id' => 'nullable|integer|exists:suppliers,id',
            'note' => 'nullable|string',

            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|integer|exists:products,id',
            'products.*.qty' => 'required|numeric|min:1',
            'products.*.unit' => 'nullable|string|max:50',
            'products.*.price' => 'required|numeric|min:0',
            'products.*.reason' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {

            $grandTotal = collect($request->products)
                ->sum(fn($item) => $item['qty'] * $item['price']);

            /**
             * =========================
             * FILE UPLOAD (SAFE VERSION)
             * =========================
             */
            $filePath = null;

            if ($request->hasFile('transport_receive')) {

                $file = $request->file('transport_receive');

                // folder ensure
                $folder = public_path('uploads/transport_receive');
                if (!file_exists($folder)) {
                    mkdir($folder, 0777, true);
                }

                $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

                $file->move($folder, $fileName);

                $filePath = 'uploads/transport_receive/' . $fileName;
            }

            /**
             * =========================
             * MAIN RETURN CREATE
             * =========================
             */
            $purchaseReturn = PurchaseReturn::create([
                'invoice_no'        => $request->invoice_no,
                'return_date'       => $request->return_date,
                'total_amount'      => $grandTotal,
                'note'              => $request->note,
                'supplier_id'       => $request->supplier_id,
                'transport_cost'    => $request->transport_cost,
                'transport_receive' => $filePath,
            ]);

            /**
             * =========================
             * ITEMS + STOCK UPDATE
             * =========================
             */
            foreach ($request->products as $item) {

                $product = Product::findOrFail($item['product_id']);

                if ($item['qty'] > $product->stock) {
                    throw new \Exception("Insufficient stock: " . $product->product_name);
                }

                $total = $item['qty'] * $item['price'];

                PurchaseReturnItem::create([
                    'purchase_return_id' => $purchaseReturn->id,
                    'product_id'         => $item['product_id'],
                    'qty'                => $item['qty'],
                    'unit'               => $item['unit'] ?? null,
                    'price'              => $item['price'],
                    'total'              => $total,
                    'reason'             => $item['reason'] ?? null,
                ]);

                $product->decrement('stock', $item['qty']);

                $product->update([
                    'status' => $product->stock <= 5 ? 'Low Stock' : 'In Stock'
                ]);
            }

            DB::commit();

            return redirect()
                ->route('purchase-return.index')
                ->with('success', 'Purchase Return Created Successfully');

        } catch (\Exception $e) {

            DB::rollBack();

            return back()
                ->withInput()
                ->withErrors($e->getMessage());
        }
    }


    public function show($id)
    {
        $purchaseReturn = PurchaseReturn::with('items.product')
            ->findOrFail($id);

        return view('purchase_returns.show', compact('purchaseReturn'));
    }


    public function edit($id)
    {
        $purchaseReturn = PurchaseReturn::with('items.product')
            ->findOrFail($id);
        $products = Product::orderBy('product_name', 'asc')->get();
        return view('purchase_returns.edit', compact(
            'purchaseReturn',
            'products'
        ));
    }

    public function update(Request $request, $id)
{
    $request->validate([
        'invoice_no' => 'required|string|max:255',
        'return_date' => 'required|date',
        'supplier_id' => 'nullable|integer|exists:suppliers,id',
        'note' => 'nullable|string',
        'transport_cost' => 'nullable|numeric|min:0',

        'transport_receive' => 'nullable|file|mimetypes:image/jpeg,image/png,image/webp,application/pdf|max:5120',

        'items' => 'required|array|min:1',
        'items.*.product_id' => 'required|integer|exists:products,id',
        'items.*.qty' => 'required|numeric|min:1',
        'items.*.unit' => 'nullable|string|max:50',
        'items.*.price' => 'required|numeric|min:0',
        'items.*.reason' => 'nullable|string',
    ]);

    DB::transaction(function () use ($request, $id) {

        $return = PurchaseReturn::findOrFail($id);

        // ================= FILE UPLOAD ONLY ONCE =================
        $filePath = $return->transport_receive;

        if ($request->hasFile('transport_receive')) {

            $file = $request->file('transport_receive');

            $folder = public_path('uploads/transport_receive');

            if (!file_exists($folder)) {
                mkdir($folder, 0777, true);
            }

            $fileName = time().'_'.uniqid().'.'.$file->getClientOriginalExtension();

            $file->move($folder, $fileName);

            $filePath = 'uploads/transport_receive/'.$fileName;
        }

        // ================= UPDATE MAIN =================
        $return->update([
            'invoice_no' => $request->invoice_no,
            'return_date' => $request->return_date,
            'supplier_id' => $request->supplier_id,
            'note' => $request->note,
            'transport_cost' => $request->transport_cost ?? 0,
            'transport_receive' => $filePath,
        ]);

        // ================= DELETE OLD ITEMS =================
        $return->items()->delete();

        $totalAmount = 0;

        foreach ($request->items as $item) {

            $total = $item['qty'] * $item['price'];
            $totalAmount += $total;

            $return->items()->create([
                'product_id' => $item['product_id'],
                'qty' => $item['qty'],
                'unit' => $item['unit'] ?? null,
                'price' => $item['price'],
                'total' => $total,
                'reason' => $item['reason'] ?? null,
            ]);
        }

        // ================= FINAL TOTAL =================
        $return->update([
            'total_amount' => $totalAmount
        ]);
    });

    return redirect()->route('purchase-return.index')
        ->with('success', 'Purchase return updated successfully!');
}


    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $purchaseReturn = PurchaseReturn::with('items.product')
                ->findOrFail($id);
            foreach ($purchaseReturn->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('stock', $item->qty);
                    $product->status = $product->stock <= 5
                        ? 'Low Stock'
                        : 'In Stock';
                    $product->save();
                }
            }

            $purchaseReturn->items()->delete();
            $purchaseReturn->delete();
            DB::commit();
            return redirect()
                ->route('purchase-returns.index')
                ->with('success', 'Return Deleted & Stock Restored Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()
                ->back()
                ->withErrors($e->getMessage());
        }
    }


    public function reportForm(Request $request)
    {
        $suppliers = Supplier::all();
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

    public function report(){
        $returns = PurchaseReturn::with(['items.product','supplier'])->get();
        return view('purchase_returns.report',compact('returns'));
    }

    
    public function exportPdf($id)
    {
        $return = PurchaseReturn::with([
            'items.product',
            'supplier',
        ])->findOrFail($id);

        $companyInfo = CompanyInfo::first();
        $date = Carbon::parse($return->return_date)->format('d-m-Y');
        $invoiceNo = $return->return_no ?? 'NoInv';
        $supplierName = str_replace(' ', '_', $return->supplier->supplier_name ?? 'NoSupplier');
        $filename = $date . '-' . $invoiceNo . '-' . $supplierName . '.pdf';

        $returns = collect([$return]);
        $overallTotal = $return->items->sum('total') ?? 0;

        $supplier = $return->supplier ?? null;
        $from_date = $date;
        $to_date = $date;
        $generator = new BarcodeGeneratorPNG();

        $companyName = $companyInfo->company_name_bangla ?? 'COMPANY';
        $supplierNameRaw = $return->supplier->supplier_name ?? 'SUPPLIER';
        $invoiceRaw = $return->return_no ?? 'INV';
        $barcodeText = $companyName . '|' . $supplierNameRaw . '|' . $invoiceRaw;

        $barcode = base64_encode(
            $generator->getBarcode($barcodeText, $generator::TYPE_CODE_128)
        );

        $html = view('purchase_returns.report_pdf', compact(
            'return',
            'returns',
            'overallTotal',
            'supplier',
            'from_date',
            'to_date',
            'companyInfo',
            'barcode'
        ))->render();

        $defaultConfig = (new ConfigVariables())->getDefaults();
        $fontDirs = $defaultConfig['fontDir'];
        $defaultFontConfig = (new FontVariables())->getDefaults();
        $fontData = $defaultFontConfig['fontdata'];

        $mpdf = new Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'fontDir' => array_merge($fontDirs, [
                public_path('fonts')
            ]),

            'fontdata' => $fontData + [
                'solaimanlipi' => [
                    'R' => 'SolaimanLipi.ttf',
                ]
            ],

            'default_font' => 'solaimanlipi',
            'margin_left' => 10,
            'margin_right' => 10,
            'margin_top' => 10,
            'margin_bottom' => 10,
        ]);

        $mpdf->SetAutoPageBreak(true, 15);
        $mpdf->WriteHTML($html);
        return $mpdf->Output($filename, 'D');
    }
    public function generate(Request $request)
    {
        $request->validate([
            'from_date' => 'required|date',
            'to_date'   => 'required|date',
        ]);

        $query = PurchaseReturn::with(['items.product', 'supplier'])
            ->whereBetween('return_date', [
                $request->from_date,
                $request->to_date
            ]);

        if ($request->supplier_id) {
            $query->where('supplier_id', $request->supplier_id);
        }

        if ($request->product_id) {
            $query->whereHas('items', function ($q) use ($request) {
                $q->where('product_id', $request->product_id);
            });
        }

        $returns = $query->get();

        $overallTotal = 0;
        foreach ($returns as $r) {
            $overallTotal += $r->items->sum('total');
        }

        $supplier_name = 'All';
        if ($request->supplier_id) {
            $supplier = Supplier::find($request->supplier_id);
            $supplier_name = $supplier->supplier_name ?? 'All';
        }

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
}