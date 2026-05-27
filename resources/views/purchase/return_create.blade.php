<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            Purchase Return Form | Invoice: {{ $purchase->Invoice_no }}
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white shadow-sm rounded p-4">
                
                <h4>Invoice: {{ $purchase->Invoice_no }}</h4>
                <h4>Reference No: {{ $purchase->reference_no }}</h4>
                <h4>Supplier: {{ $purchase->supplier->supplier_name }}</h4>
                <h4>Date: {{ \Carbon\Carbon::parse($purchase->date)->format('d-M-Y') }}</h4>
                <hr class="my-4">
                <form method="POST" action="{{ route('purchase.return.store', $purchase->id) }}">
                    @csrf
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th>Purchased Qty</th>
                                <th>Return Qty</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($purchase->details as $detail)
                                <tr>
                                    <td>
                                        {{ $detail->product?->product_name ?? 'N/A' }}
                                        <input type="hidden" 
                                               name="items[{{ $detail->id }}][product_id]" 
                                               value="{{ $detail->product_id }}">
                                    </td>
                                    <td>
                                        {{ $detail->qty }}
                                        <input type="hidden" 
                                               name="items[{{ $detail->id }}][purchased_qty]" 
                                               value="{{ $detail->qty }}">
                                    </td>
                                    <td>
                                        <input type="number"
                                               name="items[{{ $detail->id }}][return_qty]"
                                               class="form-control"
                                               min="0"
                                               max="{{ $detail->qty }}"
                                               placeholder="0">
                                    </td>
                                    <td>
                                        <input type="text"
                                               name="items[{{ $detail->id }}][reason]"
                                               class="form-control"
                                               placeholder="Reason (optional)">
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mb-3 col-md-4">
                        <label>Return Date <span class="required">*</span></label>
                        <input type="date" name="return_date"
                            value="{{ date('Y-m-d') }}"
                            class="form-control">
                    </div>
                    <div class="mt-4 text-end">
                        <button class="btn btn-danger">
                            Submit Return
                        </button>
                        <a href="{{ route('purchase.index') }}" class="btn btn-secondary">
                            Cancel
                        </a>
                    </div>
                </form>
                 <hr class="my-4">
                <button type="button" class="btn btn-secondary" onclick="history.back()">
                    ← Back
                </button>
            </div>
        </div>
    </div>
    <style>
    .required {
    color: red;
    font-weight: bold;
}
</style>

</x-app-layout>
