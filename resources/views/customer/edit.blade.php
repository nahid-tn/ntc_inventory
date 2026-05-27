<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Update Customer') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <form action="{{route('customer.update', $customers->id)}}" method="post">
                        @csrf
                        <div class="form-group">
                            <label for="customer_name">Customer Name</label>
                            <input type="text" class="form-control" name="customer_name" value="{{$customers->customer_name}}">
                        </div>
                        <div class="form-group">
                            <label>Customer's Mobile</label>
                            <input type="text" class="form-control" name="mobile" value="{{$customers->mobile}}">
                        </div>
                        <div class="form-group">
                            <label for="category_name">Customer's Address</label>
                            <input type="text" class="form-control" name="address" value="{{$customers->address}}">
                        </div>
                        <button type="submit" class="btn btn-primary">Update Customer</button> | <button type="button" class="btn btn-secondary" onclick="history.back()">
    ← Back
</button>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
