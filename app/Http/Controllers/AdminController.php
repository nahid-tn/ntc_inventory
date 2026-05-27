<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\Customer;
use App\Models\Supplier;


class AdminController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::with(['category'])
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;

                $query->where(function ($q) use ($search) {
                    $q->where('categories.category_name', 'like', "%{$search}%");
                });
            })
            ->orderBy('categories.category_name', 'asc')
            ->select('categories.*')
            ->paginate(15)
            ->withQueryString();

        return view('admin.index', compact('categories'));
    }

    public function CategoryForm()
    {
        return view("admin.category");
    }
    public function Create(Request $request)
    {
        Category::create([
        'category_name' => $request->category_name
            ]);

            return redirect()->route('admin.index')
                ->with('success', 'Category created successfully');
    }

    public function Edit($id)
    {
        $category = Category::findorfail($id);
        return View('admin.editcategory', compact('category'));
    }
    public function update(Request $request, $id)
    {
        $request->validate([
            'category_name' => 'required|string|max:255'
        ]);

        $category = Category::findOrFail($id);
        $category->update([
            'category_name' => $request->category_name
        ]);

        return redirect()->route('admin.index')
            ->with('success', 'Category updated successfully');
    }

    public function delete($id)
    {
        $category = Category::findorfail($id);
        $category->delete();
        return redirect()->back();
    }

    public function autocomplete(Request $request)
    {
        $term = $request->term;

        $categories = Category::where('category_name', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json(
            $categories->map(function ($c) {
                return [
                    'label' => $c->category_name,
                    'value' => $c->category_name,
                    'id'    => $c->id,
                ];
            })
        );
    }
    public function search(Request $request)
    {
        $term = $request->term;

        $categories = Category::where('product_name', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get();

        $result = [];
        foreach ($categories as $categorie) {
            $result[] = [
                'id' => $categorie->id,
                'value' => $categorie->product_name ?? '',
            ];
        }

        return response()->json($result);
    }
        
}
