<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Customer List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- ✅ SUCCESS MESSAGE --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-7">
                            <a href="{{ route('customer.customerform') }}" class="btn btn-primary d-inline-block mb-3">
                                Add New Customer
                            </a>
                        </div>

                        <div class="col-md-5">
                            <form method="GET" action="{{ route('customer.index') }}" class="mb-3">
                                <div style="display:flex; gap:10px; max-width:400px;">
                                    <input type="text" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           class="form-control" 
                                           placeholder="Search customer...">
                                    <button type="submit" class="btn btn-primary">Search</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- TABLE --}}
                    <table class="table mt-4 table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>Sl No.</th>
                                <th>Customer ID</th>
                                <th>Customer Name</th>
                                <th>Mobile No.</th>
                                <th>Address</th>
                                <th>Action</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($customers as $index => $customer)
                            <tr>
                                {{-- ✅ Pagination Serial Fix --}}
                                <td>
                                    {{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}
                                </td>

                                <td>{{ $customer->id }}</td>
                                <td>{{ $customer->customer_name }}</td>
                                <td>{{ $customer->mobile }}</td>
                                <td>{{ $customer->address }}</td>

                                <td>
                                    <a href="{{ route('customerReport.customer_single', $customer->id) }}" class="btn btn-primary">
                                        Ledger
                                    </a>

                                    <a href="{{ route('customer.edit', $customer->id) }}" class="btn btn-primary">
                                        Edit
                                    </a>

                                    <a href="{{ route('customer.delete', $customer->id) }}" 
                                       class="btn btn-danger"
                                       onclick="return confirm('Are you sure?')">
                                        Delete
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No Customer Found</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- ✅ Pagination Safe --}}
                    <div class="mt-4">
                        @if(method_exists($customers, 'links'))
                            {{ $customers->links('pagination::bootstrap-5') }}
                        @endif
                    </div>

                </div>
            </div>
        </div>

        <br>
        <span>@include('layouts.footer')</span>
    </div>
</x-app-layout>