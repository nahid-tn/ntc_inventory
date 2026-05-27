<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['brand', 'category'])
            ->when($request->search, function ($query) use ($request) {

                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%");
                });

            })
            ->orderBy('product_code', 'desc')
            ->paginate(115)
            ->withQueryString();

        return view('product.index', compact('products'));
    }

    public function ProductForm()
    {
        $brands = Brand::orderBy('brand_name', 'asc')->get();
        $categories = Category::orderBy('category_name', 'asc')->get();
        $products= Product::all();
        
        $prefix = 'PR - ';
        $lastCode = Product::where('product_code', 'like', $prefix.'%')
            ->orderBy('id', 'desc')
            ->value('product_code');

        if ($lastCode) {
            $number = (int) substr($lastCode, strlen($prefix));
            $nextNumber = $number + 1;
        } else {
            $nextNumber = 1;
        }
        $productCode = $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        return view('product.create', compact('brands','products','categories','productCode'));
    }
    
    public function Create(Request $request)
    {
        //  dd($request->all());
        $request->validate([
            'product_name' => 'required',
            'product_code' => 'required|unique:products,product_code',
            'purchase_price' => 'required|numeric|min:0',
            'salling_price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
        ]);

        Product::create([
            'product_name'   => $request->product_name,
            'product_code'   => $request->product_code,
            'model_name'     => $request->model_name,
            'category_id'    => $request->category_id,
            'purchase_price' => $request->purchase_price,
            'salling_price'  => $request->salling_price,
            'description'    => $request->description,
        ]);
        return redirect()->route('product.index')
            ->with('success', 'Product added successfully!');
    }

    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('product.show', compact('product'));
    }

    // public function search(Request $request)
    // {
    //     $term = $request->term;
    //     $products = Product::where('product_name', 'LIKE', "%{$term}%")
    //         ->orWhere('model_name', 'LIKE', "%{$term}%")
    //         ->limit(10)
    //         ->get();

    //     $result = [];
    //     foreach ($products as $product) {
    //         $result[] = [
    //             'id' => $product->id,
    //             'value' => $product->product_name,
    //             'model' => $product->model_name ?? '',
    //         ];
    //     }
    //     return response()->json($result);
    // }
    
    public function search(Request $request)
    {

        $products = Product::where(
            'product_name',
            'like',
            '%' . $request->term . '%'
        )->get();

        $data = [];

        foreach ($products as $product) {

            $data[] = [

                'label' => $product->product_name,
                'value' => $product->product_name,
                'id'    => $product->id,
                'price' => $product->purchase_price ?? 0,
                'stock' => $product->stock ?? 0,

            ];
        }

        return response()->json($data);
    }
    
    public function Edit($id)
    {
        $products = Product::findorfail($id);
        $brands = Brand::all();
        $categories = Category::all();
        return View('product.edit', compact('products','categories','brands'));
    }

    public function update(Request $request, $id)
    {
        // ✅ Validation
        $request->validate([
            'product_name'   => 'required|string|max:255',
            'product_code'   => 'required|string|max:100',
            'model_name'     => 'nullable|string|max:255',
            'category_id'    => 'required|exists:categories,id',
            'purchase_price' => 'required|numeric|min:0',
            'salling_price'  => 'required|numeric|min:0',
            'description'    => 'nullable|string',
            'stock'          => 'required|numeric|min:0',
        ]);

        $products = Product::findOrFail($id);

        // ✅ Assign Values
        $products->product_name   = $request->product_name;
        $products->product_code   = $request->product_code;
        $products->model_name     = $request->model_name;
        $products->category_id    = $request->category_id;
        $products->purchase_price = $request->purchase_price;
        $products->salling_price  = $request->salling_price;
        $products->description    = $request->description;
        $products->stock          = $request->stock;

        // 🔥 IMPORTANT: Auto Status Fix
        $products->status = $request->stock <= 5 ? 'Low Stock' : 'In Stock';

        $products->save();

        return redirect()->route('product.index')
            ->with('success', 'Product Updated Successfully!');
    }
    public function Destry($id)
    {
        $products = Product::findOrFail($id);
        $products->delete();
        return redirect()->back();
    }

    public function stockReport()
    {
        $products = Product::orderBy('product_name')->paginate(15);
            return view('report.stock', compact('products'));
    }

    public function autocomplete(Request $request)
    {
        $term = $request->term;

        $products = Product::with('lastPurchaseDetail')
            ->where('product_name', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        $result = $products->map(function ($product) {
            return [
                'label' => $product->product_name,
                'value' => $product->product_name,
                'id'    => $product->id,
                'stock' => $product->stock,

                // ✅ purchase price (optional)
                'purchase_price' => $product->lastPurchaseDetail->price ?? 0,

                // ✅ selling price (MAIN)
                'selling_price'  => $product->salling_price ?? 0,
            ];
        });

        return response()->json($result);
    }

    public function checkName(Request $request)
    {
        $name = $request->name;
        $products = Product::where('product_name', 'LIKE', "%{$name}%")
            ->limit(5)
            ->pluck('product_name');
        return response()->json($products);
    }

    public function saleAutocomplete(Request $request)
    {
        $term = $request->term;

        $sales = Sale::with('customer')
            ->where('invoice_no', 'LIKE', "%{$term}%")
            ->orWhereHas('customer', function ($q) use ($term) {
                $q->where('customer_name', 'LIKE', "%{$term}%");
            })
            ->limit(10)
            ->get();

        $data = $sales->map(function($sale){
            return [
                'id' => $sale->id,
                'label' => "Invoice: {$sale->invoice_no} | " . ($sale->customer->customer_name ?? 'No Customer'),
            ];
        });

        return response()->json($data);
    }

    public function lastPrice($id)
    {
        $price = \DB::table('purchase_details')
            ->where('product_id', $id)
            ->latest('id')
            ->value('price');

        return response()->json([
            'price' => $price ?? 0
        ]);
    }
}
