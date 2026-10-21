<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $customers = Customer::query()

            // 🔍 Search Filter (fixed)
            ->when($request->search, function ($query) use ($request) {
                $query->where(function ($q) use ($request) {
                    $q->where('customer_name', 'like', '%' . $request->search . '%')
                    ->orWhere('mobile', 'like', '%' . $request->search . '%');
                });
            })

            // 🔽 Latest first
            ->latest('id')

            // 📄 Pagination
            ->paginate(15)

            // 🔗 Keep search query in pagination
            ->withQueryString();

        return view('customer.index', compact('customers'));
    }

    public function CustomerForm()
    {
        return view('customer.create');
    }

    public function NewCustomerForm()
    {
        return view('customer.addcreate');
    }

    public function Create(Request $request)
    {
        $customers= new Customer();
        $customers->customer_name=$request->customer_name;
        $customers->mobile=$request->mobile;
        $customers->address=$request->address;
        $customers->save();
        return redirect()->route('customer.index')
                 ->with('success', 'Customer added successfully!');
    }

    public function store(Request $request)
    {
        $customer = new Customer();
        $customer->customer_name = $request->customer_name;
        $customer->mobile        = $request->mobile;
        $customer->address       = $request->address;
        $customer->save();

        return response()->make("
        <!DOCTYPE html>
        <html>
        <head>
            <title>Saved</title>
        </head>
        <body>

        <script>

        if(window.opener){

            // Parent Window-এর Customer Name
            window.opener.document.getElementById('customer_search').value = '".addslashes($customer->customer_name)."';

            // Hidden Customer ID
            window.opener.document.getElementById('customer_id').value = '".$customer->id."';

            // চাইলে Parent Page Refresh করতে পারেন
            // window.opener.location.reload();

        }

        alert('Customer Added Successfully.');

        window.close();

        </script>

        </body>
        </html>
        ");
    }


    public function Edit($id)
    {
        $customers = Customer::findorfail($id);
        return View('customer.edit', compact('customers'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'customer_name' => 'required|max:255',
            'mobile'        => 'required|max:20',
            'address'       => 'nullable|max:500',
        ]);

        $customer = Customer::findOrFail($id);

        $customer->update([
            'customer_name' => $request->customer_name,
            'mobile'        => $request->mobile,
            'address'       => $request->address,
        ]);

        return redirect()->route('customer.index')
            ->with('success', 'Customer updated successfully');
    }
    public function Destry($id)
    {
        $customers = Customer::findOrFail($id);
        $customers->delete();

        return redirect()->back()->with('success', 'Customer deleted successfully!');
    }
    
    public function autocomplete(Request $request)
    {
        $customers = Customer::where('customer_name', 'like', '%'.$request->term.'%')
            ->orWhere('mobile', 'like', '%' . $request->term . '%')
            ->limit(10)
            ->get();

        return response()->json(
            $customers->map(function ($c) {
                return [
                    'label' => $c->customer_name,
                    'value' => $c->customer_name,
                    'id'    => $c->id,
                    'mobile' => $c->mobile ?? '',
                    'address' => $c->address ?? '',
                ];
            })
        );
    }
}
