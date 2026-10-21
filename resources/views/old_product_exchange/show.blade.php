<x-app-layout>

    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Exchange Details') }}
        </h2>
    </x-slot>

    <div class="py-10">

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            {{-- =========================================================
                TOP BAR
            ========================================================== --}}
            <div class="flex justify-between items-center mb-3">
                <h4 class="text-lg font-semibold"> 🔄 Exchange Details </h4>
                <div class="d-flex gap-2">
                    <a href="{{ route('old_product_exchange.index') }}" class="btn btn-secondary"> <i class="fas fa-arrow-left"></i> Back </a>
                        @if($exchange->status !== 'cancelled')
                            <a href="{{ route('old_product_exchange.edit', $exchange->id) }}" class="btn btn-warning"> <i class="fas fa-edit"></i> Edit </a>
                        @endif
                    <a href="{{ route('old_product_exchange.invoice', $exchange->id) }}" target="_blank" class="btn btn-dark"> <i class="fas fa-print"></i> Print </a>
                </div>
            </div>


            {{-- =========================================================
                SUCCESS MESSAGE
            ========================================================== --}}
            @if(session('success'))

                <div class="alert alert-success alert-dismissible fade show"> <i class="fas fa-check-circle"></i>
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"> </button>
                </div>

            @endif


            {{-- =========================================================
                ERROR MESSAGE
            ========================================================== --}}
            @if(session('error'))

                <div class="alert alert-danger alert-dismissible fade show"> <i class="fas fa-exclamation-circle"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"> </button>
                </div>

            @endif


            {{-- =========================================================
                EXCHANGE HEADER
            ========================================================== --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white">
                    <div class="row align-items-center">
                        <div class="col-md-6">
                            <strong> <i class="fas fa-exchange-alt"></i> Old Product Exchange </strong>
                        </div>
                        <div class="col-md-6 text-md-end mt-2 mt-md-0">
                            <strong> Exchange No: </strong>
                            {{ $exchange->exchange_no }}
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        {{-- =============== DATE ===================== --}}
                        <div class="col-md-3">
                            <div class="border rounded p-3 h-100">
                                <small class="text-muted"> Exchange Date
                                </small>

                                <div class="fw-bold">

                                    @if($exchange->exchange_date)

                                        {{ $exchange->exchange_date->format('d-m-Y') }}

                                    @else

                                        —

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            INVOICE
                        ================================================== --}}
                        <div class="col-md-3">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted">
                                    Invoice No
                                </small>

                                <div class="fw-bold">

                                    {{ $exchange->invoice_no ?: '—' }}

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            STATUS
                        ================================================== --}}
                        <div class="col-md-3">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted">
                                    Status
                                </small>

                                <div>

                                    @if($exchange->status === 'completed')

                                        <span class="badge bg-success">
                                            Completed
                                        </span>

                                    @elseif($exchange->status === 'cancelled')

                                        <span class="badge bg-danger">
                                            Cancelled
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            {{ ucfirst($exchange->status ?? 'Unknown') }}
                                        </span>

                                    @endif

                                </div>

                            </div>

                        </div>


                        {{-- =================================================
                            CUSTOMER
                        ================================================== --}}
                        <div class="col-md-3">

                            <div class="border rounded p-3 h-100">

                                <small class="text-muted">
                                    Customer
                                </small>

                                @if($exchange->customer)

                                    <div class="fw-bold">

                                        {{ $exchange->customer->customer_name }}

                                    </div>

                                    <small class="text-muted">

                                        <i class="fas fa-phone"></i>

                                        {{ $exchange->customer->mobile }}

                                    </small>

                                @else

                                    <div class="text-danger fw-bold">

                                        Customer Not Found

                                    </div>

                                    @if($exchange->customer_id)

                                        <small class="text-muted">

                                            Customer ID:
                                            {{ $exchange->customer_id }}

                                        </small>

                                    @endif

                                @endif

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                CUSTOMER INFORMATION
            ========================================================== --}}
            @if($exchange->customer)

                <div class="card shadow-sm mb-4">

                    <div class="card-header bg-light">

                        <strong>
                            <i class="fas fa-user"></i>
                            Customer Information
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-4">

                                <label class="text-muted">
                                    Customer Name
                                </label>

                                <div class="fw-bold">
                                    {{ $exchange->customer->customer_name }}
                                </div>

                            </div>

                            <div class="col-md-4">

                                <label class="text-muted">
                                    Mobile
                                </label>

                                <div class="fw-bold">
                                    {{ $exchange->customer->mobile }}
                                </div>

                            </div>

                            <div class="col-md-4">

                                <label class="text-muted">
                                    Address
                                </label>

                                <div class="fw-bold">
                                    {{ $exchange->customer->address ?: '—' }}
                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                PRODUCTS
            ========================================================== --}}
            <div class="row g-4">

                {{-- =====================================================
                    CUSTOMER GIVES
                ====================================================== --}}
                <div class="col-md-6">

                    <div class="card border-danger shadow-sm h-100">

                        <div class="card-header bg-danger text-white">

                            <strong>
                                🔴 Customer Gives
                            </strong>

                        </div>

                        <div class="card-body">

                            @if($exchange->oldProduct)

                                <h5 class="fw-bold">

                                    {{ $exchange->oldProduct->product_name }}

                                </h5>

                            @else

                                <h5 class="fw-bold">

                                    {{ $exchange->old_product_name }}

                                </h5>

                            @endif

                            <hr>


                            <div class="row g-3">

                                {{-- Quantity --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Quantity
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->old_qty, 2) }}

                                        {{ $exchange->old_unit }}

                                    </div>

                                </div>


                                {{-- Price --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Price
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->old_price, 2) }}
                                        টাকা

                                    </div>

                                </div>


                                {{-- Weight --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Weight
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->old_weight_kg, 2) }}
                                        KG

                                    </div>

                                </div>


                                {{-- Total --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Total Value
                                    </small>

                                    <div class="fw-bold text-danger">

                                        {{ number_format((float)$exchange->old_total, 2) }}
                                        টাকা

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {{-- =====================================================
                    CUSTOMER TAKES
                ====================================================== --}}
                <div class="col-md-6">

                    <div class="card border-success shadow-sm h-100">

                        <div class="card-header bg-success text-white">

                            <strong>
                                🟢 Customer Takes
                            </strong>

                        </div>

                        <div class="card-body">

                            @if($exchange->exchangeProduct)

                                <h5 class="fw-bold">

                                    {{ $exchange->exchangeProduct->product_name }}

                                </h5>

                            @else

                                <h5 class="fw-bold">

                                    {{ $exchange->exchange_product_name }}

                                </h5>

                            @endif

                            <hr>


                            <div class="row g-3">

                                {{-- Quantity --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Quantity
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->exchange_qty, 2) }}

                                        {{ $exchange->exchange_unit }}

                                    </div>

                                </div>


                                {{-- Price --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Price
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->exchange_price, 2) }}
                                        টাকা

                                    </div>

                                </div>


                                {{-- Weight --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Weight
                                    </small>

                                    <div class="fw-bold">

                                        {{ number_format((float)$exchange->exchange_weight_kg, 2) }}
                                        KG

                                    </div>

                                </div>


                                {{-- Total --}}
                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Total Value
                                    </small>

                                    <div class="fw-bold text-success">

                                        {{ number_format((float)$exchange->exchange_total, 2) }}
                                        টাকা

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                WEIGHT CALCULATION
            ========================================================== --}}
            <div class="card shadow-sm mt-4">

                <div class="card-header bg-warning">

                    <strong>
                        ⚖️ Weight & Exchange Calculation
                    </strong>

                </div>

                <div class="card-body">

                    <div class="table-responsive">

                        <table class="table table-bordered mb-0">

                            <tbody>

                                {{-- Customer Given --}}
                                <tr>

                                    <th style="width:60%;">
                                        Customer Given Value
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->old_total, 2) }}
                                        টাকা

                                    </td>

                                </tr>


                                {{-- Customer Taken --}}
                                <tr>

                                    <th>
                                        Customer Taken Value
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->exchange_total, 2) }}
                                        টাকা

                                    </td>

                                </tr>


                                {{-- Value Difference --}}
                                <tr>

                                    <th>
                                        Value Difference
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->value_difference, 2) }}
                                        টাকা

                                    </td>

                                </tr>


                                {{-- Old Weight --}}
                                <tr>

                                    <th>
                                        Customer Given Weight
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->old_weight_kg, 2) }}
                                        KG

                                    </td>

                                </tr>


                                {{-- Exchange Weight --}}
                                <tr>

                                    <th>
                                        Customer Taken Weight
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->exchange_weight_kg, 2) }}
                                        KG

                                    </td>

                                </tr>


                                {{-- Weight Difference --}}
                                <tr>

                                    <th>
                                        Weight Difference
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->weight_difference_kg, 2) }}
                                        KG

                                    </td>

                                </tr>


                                {{-- Weight Rate --}}
                                <tr>

                                    <th>
                                        Weight Rate / KG
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->weight_rate, 2) }}
                                        টাকা

                                    </td>

                                </tr>


                                {{-- Weight Adjustment --}}
                                <tr>

                                    <th>
                                        Weight Adjustment
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->weight_adjustment, 2) }}
                                        টাকা

                                    </td>

                                </tr>


                                {{-- Exchange Fee --}}
                                <tr>

                                    <th>
                                        Exchange Fee
                                    </th>

                                    <td class="text-end">

                                        {{ number_format((float)$exchange->exchange_fee, 2) }}
                                        টাকা

                                    </td>

                                </tr>

                            </tbody>

                        </table>

                    </div>

                </div>

            </div>


            {{-- =========================================================
                FINAL SETTLEMENT
            ========================================================== --}}
            <div class="card shadow-sm mt-4">

                <div class="card-header bg-dark text-white">

                    <strong>
                        💰 Final Settlement
                    </strong>

                </div>

                <div class="card-body text-center">

                    @if($exchange->settlement_type === 'customer_pay')

                        <div class="alert alert-danger mb-0">

                            <div class="fs-5">
                                Customer Pay
                            </div>

                            <div class="display-6 fw-bold">

                                {{ number_format(abs((float)$exchange->settlement_amount), 2) }}

                                টাকা

                            </div>

                        </div>


                    @elseif($exchange->settlement_type === 'customer_receive')

                        <div class="alert alert-success mb-0">

                            <div class="fs-5">
                                Customer Receive
                            </div>

                            <div class="display-6 fw-bold">

                                {{ number_format(abs((float)$exchange->settlement_amount), 2) }}

                                টাকা

                            </div>

                        </div>


                    @else

                        <div class="alert alert-secondary mb-0">

                            <div class="fs-5">
                                No Adjustment
                            </div>

                            <div class="display-6 fw-bold">
                                0.00 টাকা
                            </div>

                        </div>

                    @endif

                </div>

            </div>


            {{-- =========================================================
                NOTES
            ========================================================== --}}
            @if($exchange->notes)

                <div class="card shadow-sm mt-4">

                    <div class="card-header">

                        <strong>
                            📝 Notes
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="alert alert-secondary mb-0">

                            {!! nl2br(e($exchange->notes)) !!}

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                CANCELLED INFORMATION
            ========================================================== --}}
            @if($exchange->status === 'cancelled')

                <div class="card border-danger shadow-sm mt-4">

                    <div class="card-header bg-danger text-white">

                        <strong>
                            ⚠️ Cancellation Information
                        </strong>

                    </div>

                    <div class="card-body">

                        <div class="row g-3">

                            <div class="col-md-4">

                                <small class="text-muted">
                                    Cancelled At
                                </small>

                                <div class="fw-bold">

                                    @if($exchange->cancelled_at)

                                        {{ $exchange->cancelled_at->format('d-m-Y h:i A') }}

                                    @else

                                        —

                                    @endif

                                </div>

                            </div>


                            <div class="col-md-8">

                                <small class="text-muted">
                                    Cancel Reason
                                </small>

                                <div class="fw-bold">

                                    {{ $exchange->cancel_reason ?: '—' }}

                                </div>

                            </div>

                        </div>

                    </div>

                </div>

            @endif


            {{-- =========================================================
                BOTTOM ACTION
            ========================================================== --}}
            <div class="text-end mt-4 mb-3">

                <a href="{{ route('old_product_exchange.index') }}" class="btn btn-secondary"> <i class="fas fa-arrow-left"></i> Back to Exchange List </a>
                @if($exchange->status !== 'cancelled')
                    <a href="{{ route('old_product_exchange.edit', $exchange->id) }}" class="btn btn-warning"> <i class="fas fa-edit"></i> Edit Exchange </a>
                @endif

                <a href="{{ route('old_product_exchange.invoice', $exchange->id) }}"
                   target="_blank"
                   class="btn btn-dark">

                    <i class="fas fa-print"></i>
                    Print Invoice

                </a>

            </div>

        </div>


        <br>

        <span class="flex justify-center">

            @include('layouts.footer')

        </span>

    </div>

</x-app-layout>