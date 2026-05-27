<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Update Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{route('supplier.update', $suppliers->id)}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                            <label for="supplier_name">Supplier Name</label>
                            <input type="text" class="form-control" name="supplier_name" value="{{$suppliers->supplier_name}}">
                            </div>
                            <div class="form-group col-md-6">
                                <label for="category_name">Supplier's Mobile</label>
                                <input type="text" class="form-control" name="mobile" value="{{$suppliers->mobile}}">
                            </div>
                        <div>
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="BankName">Bank Name</label>
                                <input type="text" name="BankName" id="BankName"  class="form-control" list="bankList" value="{{$suppliers->BankName}}">
                                <datalist id="bankList"></datalist>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="AccountNumber">Account Number</label>
                                <input type="text" name="AccountNumber" class="form-control" value="{{$suppliers->AccountNumber}}" pattern="[0-9]{15}">
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="category_name">Supplier's Address</label>
                            <input type="text" class="form-control" name="address" value="{{$suppliers->address}}">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Supplier</button> | <button type="button" class="btn btn-secondary" onclick="history.back()">
    ← Back
</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
<script>
    document.getElementById('BankName').addEventListener('input', function () {
        let query = this.value;
        if(query.length < 1) return;

        fetch("/get-banks?q=" + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                let datalist = document.getElementById('bankList');
                datalist.innerHTML = '';
                data.forEach(bank => {
                    let option = document.createElement('option');
                    option.value = bank;
                    datalist.appendChild(option);
                });
            })
            .catch(err => console.error(err));
    });
</script>