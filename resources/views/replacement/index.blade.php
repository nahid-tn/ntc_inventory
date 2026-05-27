<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Sales Replacement List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-4">
                <a href="{{ route('replacement.create') }}" class="btn btn-primary d-inline-block mb-3">New Replacement</a>
                <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Invoice</th>
                    <th>Old Product</th>
                    <th>New Product</th>
                    <th>Qty</th>
                    <th>Price</th>
                    <th>Total</th>
                    <th>Replacement Fee</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($replacements as $r)
                <tr>
                    <td>{{ $r->sale->invoice_no }}</td>
                    <td>{{ $r->oldProduct->product_name }}</td>
                    <td>{{ $r->newProduct->product_name }}</td>
                    <td>{{ $r->qty }}</td>
                    <td>{{ $r->price }}</td>
                    <td>{{ $r->total }}</td>
                    <td>{{ $r->replaceMent_fee }}</td>
                    <td>
                        <a href="{{ route('replacement.view', $r->id) }}" class="btn btn-info btn-sm">View</a>
                        <a href="{{ route('replacement.edit', $r->id) }}" class="btn btn-warning btn-sm">Edit</a>
                        <form action="{{ route('replacement.destroy', $r->id) }}" method="POST" style="display:inline;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm"
                                onclick="return confirm('Are you sure?')">Delete</button>
                        </form>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
                    <div class="mt-4">
                        {{ $replacements->links() }}
                    </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>