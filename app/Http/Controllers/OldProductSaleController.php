<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\OldProductSale;
use App\Models\Sales;
use App\Models\SaleDetail;
use App\Models\Product;
use App\Models\Customer;
use Illuminate\Support\Facades\DB;
use App\Models\OldProduct;
use App\Models\Unit;
use App\Models\OldProductSaleDetail;
use App\Models\OldProductPurchaseDetail;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;

class OldProductSaleController extends Controller
{
     public function index()
    {
        $sales = OldProductSale::with(['customer', 'details.oldProduct'])
            ->orderBy('sale_date', 'desc')
            ->get();

        return view('oldsale.index', compact('sales'));
    }

    public function createForm()
    {
        $oldProducts = OldProduct::all();
        $units       = Unit::all();
        $sales = Sales::with('customer')->orderBy('sale_date', 'desc')->get();
        return view('oldsale.create', compact('oldProducts', 'sales','units'));
    }

    public function store(Request $request)
    {
        $request->merge([
            'old_products' => collect($request->old_products ?? [])
                ->filter(fn($i) => !empty($i['product_id']))
                ->values()
                ->toArray()
        ]);
        $request->validate([
            'invoice_no'   => 'required|unique:old_product_sales,invoice_no',
            'customer_id'  => 'required|exists:customers,id',
            'sale_date'    => 'required|date',
            'old_products' => 'required|array|min:1',
            'old_products.*.product_id' => 'required|exists:old_products,id',
            'old_products.*.qty' => 'required|numeric|min:0.01',
            'old_products.*.price'      => 'required|numeric|min:0.01',
            'old_products.*.unit'       => 'nullable|string|max:50',
        ]);
        try {
            DB::transaction(function() use ($request) {

                $subtotal = 0;
                foreach($request->old_products as $item) {
                    $subtotal += $item['qty'] * $item['price'];
                }
                $paid = $request->paid_amount ?? $subtotal;
                $due  = $subtotal - $paid;
                $sale = OldProductSale::create([
                    'invoice_no'    => $request->invoice_no,
                    'customer_id'   => $request->customer_id,
                    'sale_date'     => $request->sale_date,
                    'subtotal'      => $subtotal,
                    'total_amount'  => $subtotal,
                    'paid_amount'   => $paid,
                    'due_amount'    => $due,
                    'payment_method'=> $request->payment_method,
                ]);

                foreach ($request->old_products as $item) {
                    $qtyToSell = $item['qty'];
                    $product = OldProduct::findOrFail($item['product_id']);
                    if($qtyToSell > $product->qty){
                        throw new \Exception("Insufficient stock for product: " . $product->product_name);
                    }
                    $buyPrice = $product->price;
                    $profit = ($item['price'] - $buyPrice) * $qtyToSell;
                    OldProductSaleDetail::create([
                        'sale_id'        => $sale->id,
                        'old_product_id' => $product->id,
                        'qty'            => $qtyToSell,
                        'unit'           => $item['unit'],
                        'price'          => $item['price'],
                        'buy_price'      => $buyPrice,
                        'subtotal'       => $qtyToSell * $item['price'],
                        'profit'         => $profit,
                    ]);
                    $product->decrement('qty', $qtyToSell);
                }
            });
            return redirect()->route('oldsale.index')->with('success', 'Old Product Sale created successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    // Show edit form
    public function edit($id)
    {
        $sale = OldProductSale::with('details.product')->findOrFail($id);
        $products = OldProduct::all();
        $customers = Customer::all();
        $units = Unit::all();
        return view('oldsale.edit', compact('sale', 'products', 'customers', 'units'));
    }

    // Update sale
    public function update(Request $request, $id)
    {
        $sale = OldProductSale::findOrFail($id);

        $request->merge([
            'old_products' => collect($request->old_products ?? [])
                ->filter(fn($i) => !empty($i['product_id']))
                ->values()
                ->toArray()
        ]);
        $request->validate([
            'invoice_no'   => 'required|unique:old_product_sales,invoice_no,'.$sale->id,
            'customer_id'  => 'required|exists:customers,id',
            'sale_date'    => 'required|date',
            'old_products' => 'required|array|min:1',
            'old_products.*.product_id' => 'required|exists:old_products,id',
            'old_products.*.qty' => 'required|numeric|min:0.01',
            'old_products.*.price'      => 'required|numeric|min:0.01',
            'old_products.*.unit'       => 'nullable|string|min:0.01',
        ]);

        try {
            DB::transaction(function() use ($request, $sale) {
                // revert old stock
                foreach ($sale->details as $detail) {
                    $product = OldProduct::find($detail->old_product_id);
                    if($product) $product->increment('qty', $detail->qty);
                }

                // delete old details
                $sale->details()->delete();

                // recalc subtotal
                $subtotal = 0;
                foreach($request->old_products as $item) {
                    $subtotal += $item['qty'] * $item['price'];
                }
                $paid = $request->paid_amount ?? $subtotal;
                $due  = $subtotal - $paid;

                $sale->update([
                    'invoice_no'    => $request->invoice_no,
                    'customer_id'   => $request->customer_id,
                    'sale_date'     => $request->sale_date,
                    'subtotal'      => $subtotal,
                    'total_amount'  => $subtotal,
                    'paid_amount'   => $paid,
                    'due_amount'    => $due,
                    'payment_method'=> $request->payment_method,
                ]);

                foreach ($request->old_products as $item) {
                    $qtyToSell = $item['qty'];
                    $product = OldProduct::findOrFail($item['product_id']);
                    if($qtyToSell > $product->qty){
                        throw new \Exception("Insufficient stock for product: " . $product->product_name);
                    }
                    $buyPrice = $product->price;
                    $profit = ($item['price'] - $buyPrice) * $qtyToSell;

                    OldProductSaleDetail::create([
                        'sale_id'        => $sale->id,
                        'old_product_id' => $product->id,
                        'qty'            => $qtyToSell,
                        'unit'           => $item['unit'],
                        'price'          => $item['price'],
                        'buy_price'      => $buyPrice,
                        'subtotal'       => $qtyToSell * $item['price'],
                        'profit'         => $profit,
                    ]);

                    $product->decrement('qty', $qtyToSell);
                }
            });

            return redirect()->route('oldsale.index')->with('success', 'Old Product Sale updated successfully!');
        } catch (\Exception $e) {
            return redirect()->back()->withInput()->withErrors($e->getMessage());
        }
    }

    // View sale details
    public function show($id)
    {
        $sale = OldProductSale::with(['customer', 'details.product'])
                    ->findOrFail($id);

        return view('oldsale.view', compact('sale'));
    }

    

    public function invoicePdf($id)
{
    $sale = OldProductSale::with('customer', 'details.product')
        ->findOrFail($id);

    $companyInfo = DB::table('company_info')->first();
    $invoice = DB::table('invoice')->first();

    $html = view('oldsale.invoice_pdf', compact('sale', 'companyInfo','invoice'))->render();

    // Clean output buffer (important for mPDF)
    while (ob_get_level()) {
        ob_end_clean();
    }

    $mpdf = new Mpdf([

        // ================= BASIC =================
        'mode' => 'utf-8',
        'format' => 'A4',
        'orientation' => 'P',

        // ================= LANGUAGE SUPPORT =================
        'autoScriptToLang' => true,
        'autoLangToFont' => true,
        'useSubstitutions' => true,

        // ================= FONT (SAFE FIX) =================
        // কোনো external font লাগবে না → error বন্ধ
        'default_font' => 'dejavusans',

        // যদি future এ font use করতে চান তখন uncomment করবেন
        /*
        'fontDir' => [storage_path('fonts')],

        'fontdata' => [
            'notobengali' => [
                'R' => 'NotoSansBengali-Regular.ttf',
                'B' => 'NotoSansBengali-Regular.ttf', // safe fallback
            ],
        ],

        'default_font' => 'notobengali',
        */

        // ================= MARGINS =================
        'margin_top' => 10,
        'margin_bottom' => 10,
        'margin_left' => 10,
        'margin_right' => 10,

        'autoPageBreak' => true,
    ]);

    $mpdf->WriteHTML($html);

    $filename = 'old_product_sale_' . ($sale->invoice_no ?? $sale->id) . '.pdf';

    return response()->streamDownload(function () use ($mpdf) {
        echo $mpdf->Output('', \Mpdf\Output\Destination::STRING_RETURN);
    }, $filename);
}

    public function productsOldAutocomplete(Request $request)
    {
        $term = $request->term;
        $products = Product::where('product_name', 'like', "%$term%")
            ->select(
                'product_name',
                DB::raw('SUM(stock) as total_stock'),
                DB::raw('MIN(price) as price')
            )
            ->groupBy('product_name')
            ->get();
        $data = [];
        foreach ($products as $p) {
            $data[] = [
                'id'    => $p->product_name,
                'value' => $p->product_name,
                'stock' => $p->total_stock,
                'price' => $p->price,
            ];
        }
        return response()->json($data);
    }

    public function view($id)
    {
        $sale = OldProductSale::with(['customer', 'details.product'])->findOrFail($id);
        return view('oldsale.view', compact('sale'));
    }

    public function productOldAutocomplete(Request $request)
    {
        $term = $request->term;
        $products = ProductOld::where('name_en', 'LIKE', "%{$term}%")
            ->orWhere('name_bn', 'LIKE', "%{$term}%")
            ->select('id', 'name_en', 'stock', 'price')
            ->limit(10)
            ->get();
        return response()->json(
            $products->map(function ($p) {
                return [
                    'id'    => $p->id,
                    'value' => $p->name_en,
                    'stock' => $p->stock,
                    'price' => $p->price,
                ];
            })
        );
    }

    public function getOldProductStock($id)
    {
        $totalStock = OldProductPurchaseDetail::where('old_product_id', $id)
            ->sum('qty');
        return response()->json([
            'stock' => $totalStock
        ]);
    }

    public function profitReport()
    {
        $totalBuy = DB::table('old_product_purchase_details')
            ->selectRaw('SUM(qty * price) as total_buy')
            ->value('total_buy');
        $totalSale = DB::table('old_product_sale_details')
            ->selectRaw('SUM(subtotal) as total_sale')
            ->value('total_sale');
        $totalPurchaseQty = DB::table('old_product_purchase_details')
            ->sum('qty');
        $totalSaleQty = DB::table('old_product_sale_details')
            ->sum('qty');
        $currentStock = $totalPurchaseQty - $totalSaleQty;
        $totalProfit = ($totalSale ?? 0) - ($totalBuy ?? 0);
        return view('oldsale.profitlos_report', [
            'totalBuy'     => $totalBuy ?? 0,
            'totalSale'    => $totalSale ?? 0,
            'totalProfit'  => $totalProfit,
            'currentStock' => $currentStock
        ]);
    }

    public function oldProductIndexList()
    {
        $products = OldProduct::latest()->get();
        return view('oldsale.old_index', compact('products'));
    }

}
