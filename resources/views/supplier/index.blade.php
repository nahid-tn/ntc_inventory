<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Supplier List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <!-- Success Message -->
                    @if(session('success'))
                        <div class="alert alert-success mb-3">
                            {{ session('success') }}
                        </div>
                    @endif
                    <div class="row">
                        <div class="col-md-6">
                            <!-- New Supplier Button -->
                            <a href="{{ route('supplier.supplierform') }}" class="btn btn-primary d-inline-block mb-3"> ➕ New Supplier </a>
                        </div>
                        <div class="col-md-6">
                    <!-- Supplier Table -->
                     <form method="GET" action="{{ route('supplier.index') }}" class="mb-3">
                        <div style="display:flex; gap:10px; max-width:400px;">
                            <input type="text" name="search" value="{{ request('search') }}" class="form-control" placeholder="Search supplier...">
                            <button class="btn btn-primary">Search</button>
                        </div>
                    </form>
                    </div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col">Sl No.</th>
                                    <th scope="col">Supplier Name</th>
                                    <th scope="col">Bank Account Number</th>
                                    <th scope="col">Mobile No.</th>
                                    <th scope="col">Address</th>
                                    <th scope="col">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($suppliers as $index => $supplier)
                                    <tr>
                                        <td>{{ $index + 1 }}</td>
                                        <td>{{ $supplier->supplier_name }}</td>
                                        <td>{{ $supplier->BankName }}</td>
                                        <td>{{ $supplier->mobile }}</td>
                                        <td>{{ $supplier->address }}</td>
                                        <td class="text-nowrap">
                                            <!-- Ledger Button -->
                                            <a href="{{ route('supplier.ledger', $supplier->id) }}" class="btn btn-sm btn-info mb-1"> 📒 Ledger </a>
                                            <!-- Edit Button -->
                                            <a href="{{ route('supplier.edit', $supplier->id) }}" class="btn btn-sm btn-primary mb-1"> ✏ Edit </a>
                                            <!-- Delete Button -->
                                            <a href="{{ route('supplier.delete', $supplier->id) }}" class="btn btn-sm btn-danger mb-1" onclick="return confirm('Are you sure you want to delete this supplier?')"> 🗑 Delete </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-danger"> No Supplier Found </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-3">
                        {{ $suppliers->links() }}
                    </div>
                </div>
            </div>
        </div>

        <br>
        <span> @include('layouts.footer')</span>
    </div>
</x-app-layout>
