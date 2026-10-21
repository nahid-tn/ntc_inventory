<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Supplier;
use App\Models\Purchase;
use App\Models\SupplierPayment;
use App\Models\PurchaseReturn;
use Carbon\Carbon;
use App\Models\CompanyInfo;
use Mpdf\Mpdf;
use Mpdf\Config\ConfigVariables;
use Mpdf\Config\FontVariables;



class SupplierController extends Controller
{
   public function index(Request $request)
    {
        $query = Supplier::query();

        // 🔍 Search
        if ($request->search) {
            $query->where('supplier_name', 'like', '%'.$request->search.'%');
        }

        $suppliers = $query
            ->orderBy('id','desc')
            ->paginate(10)
            ->withQueryString();

        return view('supplier.index', compact('suppliers'));
    }

    public function SupplierForm()
    {
        return view('supplier.creat');
    }

    public function create(Request $request)
    {
        $request->validate([
            'supplier_name'  => 'required|string|max:255',
            'mobile'         => 'required|digits:11',
            'BankName'       => 'nullable|string|max:255',
            'AccountNumber'  => 'nullable|digits:11',
            'address'        => 'required|string|max:500',
        ]);

        $supplier = new Supplier();
        $supplier->supplier_name  = $request->supplier_name;
        $supplier->mobile         = $request->mobile;
        $supplier->BankName       = $request->BankName;          
        $supplier->AccountNumber  = $request->AccountNumber; 
        $supplier->address        = $request->address;
        $supplier->save();

        return redirect()
            ->route('supplier.index')
            ->with('success', 'Supplier added successfully!');
    }
    public function Edit($id)
    {
        $suppliers = Supplier::findorfail($id);
        return View('supplier.edit', compact('suppliers'));
    }

    public function update(Request $request, $id)
    {
        $suppliers = Supplier::findorfail($id);
        $suppliers->supplier_name=$request->supplier_name;
        $suppliers->BankName = $request->BankName;          
        $suppliers->AccountNumber = $request->AccountNumber; 
        $suppliers->mobile=$request->mobile;
        $suppliers->address=$request->address;
        $suppliers->save();
        $suppliers = Supplier::all();
        return View('supplier.index', compact('suppliers'));
    }

    public function Destry($id)
    {
        $suppliers = Supplier::findOrFail($id);
        $suppliers->delete();
        return redirect()->back()->with('success', 'Supplier deleted successfully!');
    }

    public function autocomplete(Request $request)
    {
        $term = $request->get('term');
        $suppliers = Supplier::where('supplier_name', 'LIKE', "%{$term}%")
            ->orWhere('mobile', 'LIKE', "%{$term}%")
            ->limit(10)
            ->get();

        $result = [];
        foreach ($suppliers as $supplier) {
            $result[] = [
                'id'    => $supplier->id,
                'value' => $supplier->supplier_name,
                'mobile'=> $supplier->mobile,
            ];
        }

        return response()->json($result);
    }

    public function info($id)
    {
        $supplier = Supplier::findOrFail($id);
        $dues = Purchase::where('supplier_id',$id)->where('due_amount','>',0)->get();

        return response()->json([
            'name' => $supplier->supplier_name,
            'mobile' => $supplier->mobile,
            'address' => $supplier->address,
            'total_due' => $dues->sum('due_amount'),
            'dues' => $dues->map(function($p){
                return [
                    'Invoice_no' => $p->Invoice_no,
                    'due_amount' => $p->due_amount,
                ];
            }),
        ]);
    }


    public function ledgerPdf(Request $request, $supplierId)
    {
        // =========================
        // DATE FIX (MOST IMPORTANT)
        // =========================
        $from = $request->from ? Carbon::parse($request->from)->startOfDay() : null;
        $to   = $request->to ? Carbon::parse($request->to)->endOfDay() : null;

        $supplier = Supplier::findOrFail($supplierId);
        $company  = CompanyInfo::first();
        $transactions = collect();

        // =========================
        // PURCHASE
        // =========================
        $transactions = $transactions->merge(
            Purchase::where('supplier_id', $supplierId)
                ->when($to, fn($q) => $q->where('date', '<=', $to))
                ->get()
                ->map(function ($p) {
                    return [
                        'date'    => Carbon::parse($p->date),
                        'type'    => 'Purchase',
                        'voucher' => $p->Invoice_no,
                        'debit'   => 0,
                        'credit'  => (float) $p->subtotal,
                        'items'   => $p->details->map(function ($i) {
                            return [
                                'name' => $i->product->product_name ?? '',
                                'qty'  => $i->qty,
                                'rate' => $i->price
                            ];
                        }),
                    ];
                })
        );

        // =========================
        // RETURNS
        // =========================
        $transactions = $transactions->merge(
            PurchaseReturn::where('supplier_id', $supplierId)
                ->when($to, fn($q) => $q->where('return_date', '<=', $to))
                ->get()
                ->map(function ($r) {
                    return [
                        'date'    => Carbon::parse($r->return_date),
                        'type'    => 'Return',
                        'voucher' => $r->invoice_no,
                        'debit'   => (float) $r->total_amount,
                        'credit'  => 0,
                        'items'   => $r->items->map(function ($i) {
                            return [
                                'name' => $i->product->product_name ?? '',
                                'qty'  => $i->qty,
                                'rate' => $i->price
                            ];
                        }),
                    ];
                })
        );

        // =========================
        // PAYMENTS
        // =========================
        // $transactions = $transactions->merge(
        //     SupplierPayment::where('supplier_id', $supplierId)
        //         ->when($to, fn($q) => $q->where('payment_date', '<=', $to))
        //         ->get()
        //         ->map(function ($p) {
        //             return [
        //                 'date'    => Carbon::parse($p->payment_date),
        //                 'type'    => 'Payment',
        //                 'voucher' => $p->transaction_number,
        //                 'debit'   => (float) $p->amount,
        //                 'credit'  => 0,
        //                 'items'   => collect(),
        //             ];
        //         })
        // );
$transactions = $transactions->merge(
    SupplierPayment::where('supplier_id', $supplierId)
        ->when($to, fn($q) => $q->where('payment_date', '<=', $to))
        ->get()
        ->map(function ($p) {
            // items কে অ্যারে অফ অ্যারে আকারে সংরক্ষণ
            $items = collect();
            
            if (!empty($p->note)) {
                $items->push([
                    'name' => $p->note,
                    'qty' => 1,
                    'rate' => 0,
                ]);
            }
            
            return [
                'date'    => Carbon::parse($p->payment_date),
                'type'    => 'Payment',
                'voucher' => $p->transaction_number,
                'debit'   => (float) $p->amount,
                'credit'  => 0,
                'items'   => $items,
            ];
        })
);
        // =========================
        // SORT ALL TRANSACTIONS
        // =========================
        $transactions = $transactions->sortBy('date')->values();

        // =========================
        // OPENING BALANCE (FIXED)
        // =========================
        $openingBalance = 0;

        if ($from) {
            $openingBalance = $transactions
                ->filter(fn($t) => $t['date']->lt($from))
                ->sum(fn($t) => ($t['credit'] ?? 0) - ($t['debit'] ?? 0));
        }

        // =========================
        // LEDGER RANGE (FIXED FILTER)
        // =========================
        $ledger = $transactions
            ->filter(function ($t) use ($from, $to) {

                if ($from && $t['date']->lt($from)) {
                    return false;
                }

                if ($to && $t['date']->gt($to)) {
                    return false;
                }

                return true;
            })
            ->values()
            ->toArray();

        // =========================
        // RUNNING BALANCE
        // =========================
        $balance = $openingBalance;
        $totalDebit = 0;
        $totalCredit = 0;

        foreach ($ledger as $k => $row) {
            $debit  = (float) ($row['debit'] ?? 0);
            $credit = (float) ($row['credit'] ?? 0);
            $totalDebit  += $debit;
            $totalCredit += $credit;
            // Supplier formula
            $balance += ($credit - $debit);
            $ledger[$k]['running_balance'] = $balance;
        }

        // =========================
        // PDF VIEW
        // =========================
        $html = view('supplier.ledger_pdf', compact(
            'supplier',
            'ledger',
            'from',
            'to',
            'openingBalance',
            'company',
            'totalDebit',
            'totalCredit'
        ))->render();

        // =========================
        // MPDF
        // =========================
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'utf-8',
            'format' => 'A4',
            'autoScriptToLang' => true,
            'autoLangToFont' => true,
            'default_font' => 'solaimanlipi'
        ]);

        $mpdf->SetAutoPageBreak(true, 15);
        $mpdf->WriteHTML($html);

        $fileName = $supplier->id . '_' . $supplier->supplier_name . '_' . now()->format('Y-m-d_H-i-s') . '_ledger.pdf';

        return $mpdf->Output($fileName, 'D');
    }

    public function paymentList($id)
    {
        $supplier = Supplier::findOrFail($id);
        $payments = SupplierPayment::where('supplier_id', $id)
                        ->orderBy('payment_date', 'asc')
                        ->get();
        $purchases = Purchase::with('supplier')
                        ->where('supplier_id', $id)
                        ->orderBy('date', 'asc')
                        ->get();

        return view('supplier.payment_list', compact('supplier', 'payments', 'purchases'));
    }
}
