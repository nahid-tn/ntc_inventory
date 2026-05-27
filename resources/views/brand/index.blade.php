<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Brand List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                <div class="row">
                    <div class="col-md-6">    
                <a href="{{route('brand.brandform')}}" class="btn btn-primary d-inline-block mb-3">Add Brand</a>
                    </div>
                    <div class="col-md-6">
                            <form method="GET" action="{{ route('brand.index') }}" class="mb-3">
                                <div style="display:flex; gap:10px; max-width:400px;">
                                    <input
                                        type="text"
                                        name="search"
                                        value="{{ request('search') }}"
                                        class="form-control"
                                        placeholder="Search brand..."
                                    >

                                    <button type="submit" class="btn btn-primary">
                                        Search
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light">
                            <tr>
                                <th scope="col">BRAND ID</th>
                                <th scope="col">BRAND NAME</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($brands as $brand)
                            <tr>
                                <th scope="row">{{$brand->id}}</th>
                                <td>{{$brand->brand_name}}</td>
                                 <td>   
                                        <a href="{{route('brand.edit',$brand->id)}}" class="btn btn-primary">Edit</a>
                                        <a href="{{route('brand.delete',$brand->id)}}" class="btn btn-danger" onclick="return confirm('Are Your sure?')">Delete</a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class="mt-4">
                    {{ $brands->links() }}
                    </div>
                </div>
                
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
