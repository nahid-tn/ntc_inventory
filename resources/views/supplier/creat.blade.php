<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Supplier') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    <form action="{{ route('supplier.create') }}" method="post">
                        @csrf

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Supplier Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control"
                                       name="supplier_name"
                                       placeholder="Enter Supplier Name"
                                       required>
                            </div>

                            <div class="form-group col-md-6">
                                <label>Mobile Number <span class="text-danger">*</span></label>
                                <input type="text"
                                       class="form-control"
                                       name="mobile"
                                       placeholder="11 Digit Mobile Number"
                                       pattern="[0-9]{11}"
                                       minlength="11"
                                       maxlength="11"
                                       required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="form-group col-md-6">
                                <label>Bank Name</label>
                                <input type="text"
                                       name="BankName"
                                       id="BankName"
                                       class="form-control"
                                       list="bankList"
                                       placeholder="Optional">
                                <datalist id="bankList"></datalist>
                            </div>

                            <div class="form-group col-md-6 d-none" id="accountNumberWrapper">
                                <label>Account Number</label>
                                <input type="text"
                                       name="AccountNumber"
                                       id="AccountNumber"
                                       class="form-control"
                                       pattern="[0-9]{9,15}"
                                       placeholder="9–15 Digit Account Number">
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Address <span class="text-danger">*</span></label>
                            <input type="text"
                                   class="form-control"
                                   name="address"
                                   placeholder="Supplier Address"
                                   required>
                        </div>

                        <button type="submit" class="btn btn-primary">Add Supplier</button>
                        <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>

                    </form>

                </div>
            </div>
        </div>
    </div>

    <script>
$(document).ready(function () {

    /* ================= Bank Autocomplete ================= */
    $('#BankName').on('input', function () {
        let query = $(this).val().trim();
        let datalist = $('#bankList');

        datalist.empty();

        if (query.length < 1) return;

        fetch('/get-banks?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(data => {
                data.forEach(bank => {
                    datalist.append(`<option value="${bank}">`);
                });
            });
    });

    /* ================= Show / Hide Account Number ================= */
    $('#BankName').on('input', function () {
        let bankName = $(this).val().trim();

        if (bankName !== '') {
            $('#accountNumberWrapper').removeClass('d-none');
            $('#AccountNumber').prop('required', true);
        } else {
            $('#accountNumberWrapper').addClass('d-none');
            $('#AccountNumber').val('').prop('required', false);
        }
    });

});
</script>
</x-app-layout>