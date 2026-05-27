<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ProductReplacement;
use App\Models\Sales;
use App\Models\Sale;
use App\Models\Product;
use App\Models\OldProduct;
use App\Models\Unit;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\SaleDetail; //


class ProductReplacementController extends Controller
{
    // List all replacements
    public function index() {
        $replacements = ProductReplacement::with(['sale', 'oldProduct', 'newProduct'])->latest()->paginate(10);
        return view('replacement.index', compact('replacements'));
    }
    // Show single replacement details
   public function view($id)
    {
        $replacement = ProductReplacement::with([
            'sale.customer', // Sale & Customer
            'newProduct',    // New Product
        ])->findOrFail($id);

        // Old product details from sale_details
        $replacement->oldSaleDetail = SaleDetail::with('product')
            ->where('sale_id', $replacement->sale_id)
            ->where('product_id', $replacement->old_product_id)
            ->first();

        return view('replacement.view', compact('replacement'));
    }
    // Show form
    public function create()
    {
        $sales = Sales::with('details.product','customer')->get();
        $units = Unit::all(); // Units load
        return view('replacement.create', compact('sales', 'units'));
    }

    // Store replacement
    public function store(Request $request)
    {
        $request->validate([

            'sale_id'        => 'required|exists:sales,id',
            'old_product_id' => 'required|exists:products,id',
            'new_product_id' => 'required|exists:products,id',
            'qty'            => 'required|integer|min:1',
            'price'          => 'required|numeric|min:0',
        ]);


        DB::transaction(function () use ($request) {

            // Previous active → inactive
            ProductReplacement::where('sale_id',$request->sale_id)
                ->update(['is_active'=>false]);


            // ✅ New replacement insert
            $replacement = ProductReplacement::create([

                'sale_id'        => $request->sale_id,
                'old_product_id' => $request->old_product_id,
                'new_product_id' => $request->new_product_id,
                'qty'            => $request->qty,
                'unit'            => $request->unit,
                'price'          => $request->price,
                'total'          => $request->qty * $request->price,
                'replaceMent_date'            => $request->replaceMent_date,
                'body_date'            => $request->body_date,
                'replaceMent_fee'            => $request->replaceMent_fee,
                'is_active'      => true,
            ]);


            // 🔁 Stock Adjust

            // old product back
            Product::where('id',$request->old_product_id)
                ->increment('stock',$request->qty);

            // new product out
            Product::where('id',$request->new_product_id)
                ->decrement('stock',$request->qty);
        });


        return redirect()
            ->route('replacement.index')
            ->with('success','Replacement Completed');
    }
    // Show edit form
    public function edit($id)
    {
        $replacement = ProductReplacement::findOrFail($id);
        $sales = Sales::all();
        $products = Product::all();
        $units = Unit::all(); // Units load
        return view('replacement.edit', compact('replacement', 'sales', 'products', 'units'));
    }
        // Update replacement
    public function update(Request $request, $id)
{
    $request->validate([
        'sale_id'        => 'required|exists:sales,id',
        'old_product_id' => 'required|exists:products,id',
        'new_product_id' => 'required|exists:products,id',
        'qty'            => 'required|integer|min:1',
        'price'          => 'required|numeric|min:0',
    ]);

    $replacement = ProductReplacement::findOrFail($id);

    DB::transaction(function () use ($request, $replacement) {

        // 🔁 Revert previous stock
        Product::where('id', $replacement->old_product_id)
            ->increment('stock', $replacement->qty);

        Product::where('id', $replacement->new_product_id)
            ->decrement('stock', $replacement->qty);

        // 🔄 Update replacement record
        $replacement->update([
            'sale_id'        => $request->sale_id,
            'old_product_id' => $request->old_product_id,
            'new_product_id' => $request->new_product_id,
            'qty'            => $request->qty,
            'unit'            => $request->unit,
            'price'          => $request->price,
            'total'          => $request->qty * $request->price,
            'replaceMent_date'=> $request->replaceMent_date,
            'body_date'       => $request->body_date,
            'replaceMent_fee' => $request->replaceMent_fee,
            'is_active'      => true,
        ]);

        // 🔁 Adjust stock for new values
        Product::where('id', $request->old_product_id)
            ->decrement('stock', $request->qty);

        Product::where('id', $request->new_product_id)
            ->increment('stock', $request->qty);
    });

    return redirect()
        ->route('replacement.index')
        ->with('success', 'Replacement updated successfully!');
}

    // Delete replacement
    public function destroy($id) {
        $replacement = ProductReplacement::findOrFail($id);
        DB::transaction(function() use($replacement) {
            // revert stock
            $replacement->oldProduct->stock += $replacement->qty;
            $replacement->oldProduct->save();
            $replacement->newProduct->stock -= $replacement->qty;
            $replacement->newProduct->save();
            $replacement->delete();
        });
        return redirect()->route('replacement.index')->with('success', 'Replacement deleted successfully!');
    }

    public function saleProducts(Sales $sale)
    {
        $products = $sale->details()->with('product')->get();
        $result = $products->map(function($detail) {
            return [
                'id' => $detail->product->id,
                'name' => $detail->product->product_name,
                'stock' => $detail->product->stock,
            ];
        });
        return response()->json($result);
    }

    public function getOldProducts($sale_id)
    {
        $sale = Sales::with('details.product')->find($sale_id);
        if (!$sale) {
            return response()->json([]);
        }
        $products = $sale->details->map(function ($d) {
            return [
                'id'    => $d->product->id,
                'name'  => $d->product->product_name,
                'qty'   => $d->qty,
                'stock' => $d->product->stock,
                'price' => $d->price,
            ];
        });
        return response()->json($products);
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term');
        $sales = Sales::with('customer')
            ->where('invoice_no', 'LIKE', "%{$term}%")
            ->orWhereHas('customer', function ($q) use ($term) {
                $q->where('customer_name', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get();
        $results = $sales->map(function ($sale) {
            return [
                'id'    => $sale->id,
                'label' => 'Invoice: '.$sale->invoice_no
                        .' | Date: '.$sale->sale_date
                        .' | Customer: '.($sale->customer->customer_name ?? ''),
                'value' => $sale->invoice_no
            ];
        });
        return response()->json($results);
    }

    public function invoicePdf($id)
    {
        $replacement = ProductReplacement::with([
            'sale.customer',
            'newProduct',
        ])->findOrFail($id);

        // ✅ EXACTLY same logic as view()
        $oldSaleDetail = SaleDetail::with('product')
            ->where('sale_id', $replacement->sale_id)
            ->where('product_id', $replacement->old_product_id)
            ->first();

        $pdf = Pdf::loadView(
            'replacement.invoice_pdf',
            compact('replacement', 'oldSaleDetail')
        );

        $pdf->setPaper('A4', 'portrait');

        return $pdf->download('replacement_invoice_'.$replacement->id.'.pdf');
    }
}

 