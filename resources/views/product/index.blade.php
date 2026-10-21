<link rel="stylesheet"
      href="https://code.jquery.com/ui/1.14.1/themes/base/jquery-ui.css">

<style>
.ui-autocomplete{
    z-index:99999 !important;
    max-height:250px;
    overflow-y:auto;
    overflow-x:hidden;
    font-size:14px;
}

.ui-menu-item-wrapper{
    padding:8px;
}

.ui-state-active{
    background:#0d6efd !important;
    border:#0d6efd !important;
    color:#fff !important;
}
</style>

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Product List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <div class="row">
                    <div class="col-md-8">    
                        <a href="{{ route('product.productform') }}" class="btn btn-primary d-inline-block mb-3">Add New Product</a>
                    </div>
                    <div class="col-md-4">
                        {{-- <form method="GET" action="{{ route('product.index') }}" class="mb-3" id="searchForm">
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="search" name="search" class="form-control" placeholder="Search product / code" value="{{ request('search') }}">
                                <button class="btn btn-primary">Search</button>
                            </div>
                        </form> --}}
                        <form method="GET"
                            action="{{ route('product.index') }}"
                            id="searchForm">

                            <div class="input-group">

                                <input
                                    type="text"
                                    id="search"
                                    name="search"
                                    class="form-control"
                                    autocomplete="off"
                                    placeholder="Search Product Name / Code"
                                    value="{{ request('search') }}">

                                <button class="btn btn-primary">
                                    <i class="fa fa-search"></i> Search
                                </button>

                            </div>

                        </form>
                    </div>
                </div>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr class="text-center">
                                <th>SL NO.</th>
                                <th scope="col">Image</th>
                                <th scope="col">PRODUCT NAME</th>
                                 <th scope="col">PURCHASES</th>
                                 <th scope="col">WHOLESALE</th>
                                 <th scope="col">SALL</th>
                                  <th scope="col">STOCK</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($products as $product)
                            <tr>
                                <th scope="row">{{ $products->firstItem() + $loop->index }}</th> <!-- পেজিনেশন SL ফিক্স -->
                                <td>
                                @if($product->image)
                                <img src="{{ asset('uploads/products/'.$product->image) }}"
                                    width="60"
                                    height="60"
                                    style="object-fit:cover; cursor:pointer;"
                                    data-bs-toggle="modal"
                                    data-bs-target="#imageModal"
                                    onclick="showImage('{{ asset('uploads/products/'.$product->image) }}')">
                                @else
                                No Image
                                @endif

                                </td>
                                <td>{{$product->product_name}}</td>
                                <td class="text-right">{{ number_format($product->purchase_price ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($product->wholesale_price ?? 0, 2) }}</td>
                                <td class="text-right">{{ number_format($product->selling_price ?? 0, 2) }}</td>
                                <td class="text-center">{{ number_format($product->stock ?? 0, 0) }}</td>
                                <td class="text-center">   
                                    <a href="{{route('product.edit',$product)}}" class="btn btn-primary">Edit</a>
                                    <a href="{{route('product.show',$product)}}" class="btn btn-info">View</a>
                                    <form action="{{route('product.destroy',$product)}}" method="POST" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <!-- Image Preview Modal -->
                        <div class="modal fade" id="imageModal" tabindex="-1">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Product Image</h5>

                <button type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                </button>
            </div>

            <div class="modal-body d-flex justify-content-center align-items-center"
                 style="min-height: 500px;">

                <img id="modalImage"
                     src=""
                     class="img-fluid rounded"
                     style="max-height: 450px; object-fit: contain;">

            </div>

        </div>
    </div>
</div>
    <div class="mt-4">
        {{ $products->links() }}
        </div>
                    </div>
                    
                </div>
            </div>
            <br>
            <span> @include('layouts.footer')</span>
        </div>
    </div>


<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://code.jquery.com/ui/1.14.1/jquery-ui.min.js"></script>

<script>
function showImage(imageUrl){
    $("#modalImage").attr("src", imageUrl);
}

$(function(){

    $("#search").autocomplete({

        source: "{{ route('product.autocomplete') }}",

        minLength:1,

        delay:200,

        autoFocus:true,

        select:function(event,ui){

            $("#search").val(ui.item.value);

            $("#searchForm").submit();

        }

    });

});
</script>

</x-app-layout>
