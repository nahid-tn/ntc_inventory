<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Purchase Return List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    {{-- Success Message --}}
                    @if(session('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    {{-- Top Section --}}
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <a href="{{ route('purchase-return.create') }}" class="btn btn-primary">
                                Return Create
                            </a>
                        </div>

                        <div class="col-md-8">
                            <form method="GET" class="d-flex justify-content-end gap-2 flex-wrap">
                                {{-- Search --}}
                                <input type="text" name="search" placeholder="Search Supplier or Invoice" value="{{ request('search') }}" class="form-control" style="max-width:200px;">
                                {{-- From Date --}}
                                <input type="date" name="from_date" value="{{ request('from_date') }}" class="form-control" style="max-width:150px;">
                                {{-- To Date --}}
                                <input type="date" name="to_date" value="{{ request('to_date') }}" class="form-control" style="max-width:150px;">
                                <button class="btn btn-primary">Filter</button>
                                <a href="{{ route('purchase-return.index') }}" class="btn btn-secondary"> Reset</a>
                            </form>
                        </div>
                    </div>

                    {{-- Table --}}
                    <table class="table table-bordered">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Invoice No</th>
                                <th>Return Date</th>
                                <th>Supplier</th>
                                <th>Total Amount</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($returns as $return)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $return->invoice_no }}</td>
                                <td>{{ \Carbon\Carbon::parse($return->return_date)->format('Y-m-d') }}</td>
                                <td>{{ $return->supplier->supplier_name ?? '-' }}</td>
                                <td>{{ number_format($return->total_amount, 2) }}</td>
                                
                                <td>
                                    <a href="{{ route('purchase-return.show', $return->id) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('purchase-return.edit', $return->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('purchase-return.delete', $return->id) }}" method="POST" style="display:inline-block;">
                                        @csrf
                                        @method('DELETE')
                                        <button class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">
                                            Delete
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center">No Purchase Returns found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>

                    {{-- Pagination --}}
                    <div class="mt-4">
                        {{ $returns->links() }}
                    </div>
                </div>
            </div>
        </div>
        <br>
        <span>@include('layouts.footer')</span>
    </div>

</x-app-layout>