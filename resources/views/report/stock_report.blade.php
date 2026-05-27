<x-app-layout>

<x-slot name="header">
<h2>Stock In / Out Report</h2>
</x-slot>

<div class="p-4">
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>#</th>
                <th>Product</th>
                <th>Stock In</th>
                <th>Stock Out</th>
                <th>Current Stock</th>
            </tr>
        </thead>
        <tbody>
        @foreach($stock as $key=>$s)
            <tr>
                <td>{{ $key+1 }}</td>
                <td>{{ $s->product_name }}</td>
                <td>{{ $s->total_in ?? 0 }}</td>
                <td>{{ $s->total_out ?? 0 }}</td>
                <td>{{ $s->current_stock }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>

</div>
</x-app-layout>
