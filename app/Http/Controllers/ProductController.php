<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Brand;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\ProductRequest;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with(['brand', 'category'])
            ->where('is_deleted', 1) // শুধু Active Product দেখাবে
            ->when($request->search, function ($query, $search) {
                // SQL injection ফিক্স
                $search = addcslashes($search, '%_');
                $query->where(function ($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                    ->orWhere('model_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('product_code', 'desc')
            ->paginate(20)
            ->withQueryString();
        // dd($products);
        return view('product.index', compact('products'));
    }

    public function ProductForm()
    {
        $brands = Brand::orderBy('brand_name')->get();
        $categories = Category::orderBy('category_name')->get();
        
        // Race Condition ফিক্স: Transaction + Lock
        $productCode = DB::transaction(function () {
            $prefix = 'PR - ';
            $lastProduct = Product::where('product_code', 'like', $prefix.'%')
                ->lockForUpdate() // DB লক করো
                ->latest('id')
                ->first();

            $nextNumber = $lastProduct 
                ? (int) substr($lastProduct->product_code, strlen($prefix)) + 1 
                : 1;
            return $prefix . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
        });

        return view('product.create', compact('brands', 'categories', 'productCode'));
    }
    public function store(Request $request)
    {
        $request->validate([
            'product_name' => 'required',
            'category_id' => 'required',
            'purchase_price' => 'required',
            'wholesale_price' => 'required',
            'selling_price' => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);
        
        $imageName = null;

        if ($request->hasFile('image')) {
            $imageName = time().'_'.$request->image->getClientOriginalName();
            $request->image->move(
                public_path('uploads/products'),
                $imageName
            );
        }
        Product::create([
            'product_name' => $request->product_name,
            'product_name_bn' => $request->product_name_bn,
            'product_code' => $request->product_code,
            'model_name' => $request->model_name,
            'category_id' => $request->category_id,
            'purchase_price' => $request->purchase_price,
            'wholesale_price' => $request->wholesale_price,
            'selling_price' => $request->selling_price,
            'description' => $request->description,
            'image' => $imageName,
            'stock' => 0,
            'status' => 1,
        ]);

        return redirect()->route('product.index')
            ->with('success', 'Product created successfully!');
    }
    public function show($id)
    {
        $product = Product::findOrFail($id);
        return view('product.show', compact('product'));
    }

    public function edit($id)
    {
        $product = Product::findOrFail($id);
        $brands = Brand::all();
        $categories = Category::all();

        return view(
            'product.edit',
            compact(
                'product',
                'brands',
                'categories'
            )
        );
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $request->validate([
            'product_name'   => 'required',
            'category_id'    => 'required',
            'purchase_price' => 'required',
            'wholesale_price' => 'required',
            'selling_price'  => 'required',
            'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $imageName = $product->image;

        if ($request->hasFile('image')) {
            // পুরাতন ছবি ডিলিট
            if (
                !empty($product->image) &&
                file_exists(
                    public_path(
                        'uploads/products/' .
                        $product->image
                    )
                )
            ) {
                unlink(
                    public_path(
                        'uploads/products/' .
                        $product->image
                    )
                );
            }

            // নতুন ছবি আপলোড
        if ($request->hasFile('image')) {
            // Old image delete
            if ($product->image &&
                file_exists(public_path('uploads/products/'.$product->image))) {
                unlink(public_path('uploads/products/'.$product->image));
            }

            // New image upload
            $image = $request->file('image');
            $imageName = time().'.'.$image->getClientOriginalExtension();
            $image->move(
                public_path('uploads/products'),
                $imageName
            );
        }
        }

        $product->update([
            'product_name'   => $request->product_name,
            'product_code'   => $request->product_code,
            'model_name'     => $request->model_name,
            'category_id'    => $request->category_id,
            'purchase_price' => $request->purchase_price,
            'wholesale_price' => $request->wholesale_price,
            'selling_price'  => $request->selling_price,
            'description'    => $request->description,
            'image'          => $imageName,
            'stock'          => $request->stock ?? 0,
            'status'         =>
                (($request->stock ?? 0) <= 5)
                    ? 0
                    : 1,
        ]);

        return redirect()
            ->route('product.index')
            ->with(
                'success',
                'Product Updated Successfully!'
            );

    }

   public function destroy(Product $product)
    {
        $product->update([
            'is_deleted' => 0
        ]);
        // dd($product);
        return redirect()
            ->back()
            ->with('success', 'Product Deactivated Successfully!');
    }

    public function stockReport()
    {
        $products = Product::orderBy('product_name')->paginate(50);
        return view('report.stock', compact('products'));
    }
    
    public function autocomplete(Request $request)
    {
        $term = addcslashes($request->term, '%_');
        $products = Product::with('lastPurchaseDetail')
            ->where('product_name', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json($products->map(fn($product) => [
            'label' => $product->product_name,
            'value' => $product->product_name,
            'id'    => $product->id,
            'stock' => $product->stock ?? 0,
            'purchase_price' => $product->lastPurchaseDetail->price ?? 0,
            'wholesale_price' => $product->wholesale_price ?? 0,
            'selling_price'  => $product->selling_price ?? 0,
        ]));
    }
    
}