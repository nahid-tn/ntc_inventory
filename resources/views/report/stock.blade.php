<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200">
            📦 Stock Report
        </h2>
    </x-slot>

    <div class="py-8">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 shadow rounded p-4">
                <table class="table table-bordered table-striped">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>#</th>
                            <th>Product Name</th>
                            <th>Purchase Price</th>
                            <th>Sale Price</th>
                            <th>Stock Qty</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($products as $index => $product)
                        <tr class="text-center">
                            <td>{{ $index + 1 }}</td>
                            <td class="text-start">
                                <a href="{{ route('product.show', $product->id) }}" class="text-decoration-none text-primary">
                                    {{ $product->product_name }}
                                </a>
                            </td>
                            <td>{{ number_format($product->purchase_price, 2) }}</td>
                            <td>{{ number_format($product->selling_price, 2) }}</td>
                            <td>
                                <strong>{{ intval($product->stock) }}</strong>
                            </td>
                            <td>
                                @if($product->stock <= 5)
                                    <span class="badge bg-danger">Low Stock</span>
                                @else
                                    <span class="badge bg-success">In Stock</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                No products found
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-4">
                        {{ $products->links() }}
                    </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>