<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sales;
use App\Models\Customer;
use App\Models\Supplier;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function Dashboard()
    {
        if(Auth::check() && Auth::user()->type=="user")
        {
            return view('dashboard');
        }
       else if(Auth::check() && Auth::user()->type=="admin")
        {
        $totalProducts = Product::count(); // total products
        $totalStock = (int) DB::table('products')->sum('stock');
        $totalCustomers = Customer::count();
        $totalSuppliers = Supplier::count();
        $lowStock = Product::where('stock', '<=', 2)->count();
        $totalPurchaseAmount = Purchase::sum('total_amount');
        $totalSalesAmount    = Sales::sum('total_amount');
        return view('admin.dashboard', compact(
            'totalProducts',
            'totalStock',
            'totalCustomers',
            'totalSuppliers',
            'totalPurchaseAmount',
            'totalSalesAmount',
            'lowStock'
        ));
        } 
        else
        {
            return redirect('/');
        }
    }

}