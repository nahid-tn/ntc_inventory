<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Brand;

class BrandController extends Controller
{
    public function index(Request $request)
    {
        $query = Brand::query();

        // 🔍 Search by Brand Name
        if ($request->search) {
            $query->where('brand_name', 'like', '%'.$request->search.'%');
        }

        $brands = $query
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('brand.index', compact('brands'));
    }
    
    public function BrandForm()
    {
        return view("brand.create");
    }

    public function Create(Request $request)
    {
        $request->validate([
            'brand_name' => 'required|string|max:255',
        ]);

        Brand::create([
            'brand_name' => $request->brand_name,
        ]);

        return redirect()->route('brand.index')
                         ->with('success', 'Brand added successfully');
    }

    public function edit($id)
        {
            $brand = Brand::findOrFail($id);
            return view('brand.edit', compact('brand'));
        }

    public function update(Request $request, $id)
    {
        $brand = Brand::findOrFail($id);
        $brand->update([
            'brand_name' => $request->brand_name,
        ]);

        return redirect()->route('brand.index')
            ->with('success', 'Brand updated successfully');
    }

    public function Destry($id)
    {
        $brand = Brand::findorfail($id);
        $brand->delete();
        return redirect()->back();
    }

    public function autocomplete(Request $request)
    {
        $term = $request->term;
        $brands = Brand::where('brand_name', 'like', "%{$term}%")
            ->limit(10)
            ->get();

        return response()->json(
            $brands->map(function ($b) {
                return [
                    'label' => $b->brand_name,
                    'value' => $b->brand_name,
                    'id'    => $b->id,
                ];
            })
        );
    }
    
}
