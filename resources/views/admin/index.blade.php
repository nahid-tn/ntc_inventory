<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Category List') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="row">
                    <div class="col-md-6">    
                        <a href="{{route('admin.categoryform')}}" class="btn btn-primary d-inline-block mb-3">Add Category</a>
                    </div>
                    <div class="col-md-6">
                        <form method="GET" action="{{ route('admin.index') }}" class="mb-3" id="searchForm">
                            <div style="display:flex; gap:10px;">
                                <input type="text" id="search" name="search" class="form-control" placeholder="Search category" value="{{ request('search') }}">
                                <button class="btn btn-primary">Search</button>
                            </div>
                        </form>
                    </div>
                </div>
                    <table class="table table-bordered table-striped">
                        <thead class="thead-light text-center">
                            <tr>
                                <th scope="col" >SL No.</th>
                                <!-- <th scope="col" >CATEGORY ID</th> -->
                                <th scope="col">CATEGORY NAME</th>
                                <th scope="col">ACTION</th>
                            </tr>
                        </thead>
                        <tbody class="text-center">
                            @foreach($categories as $index => $category)
                            <tr>
                                <th>{{ $index + 1 }}</th>
                                <!-- <th>{{ $category->id }}</th> -->
                                <td>{{ $category->category_name }}</td>
                                <td>
                                    <a href="{{ route('admin.edit', $category->id) }}" class="btn btn-primary">Edit</a>
                                    <a href="{{ route('admin.delete', $category->id) }}"
                                    class="btn btn-danger"
                                    onclick="return confirm('Are you sure?')">
                                    Delete
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                            </tbody>
                    </table>
                    <div class="mb-4">
                {{ $categories->links() }}    
                </div>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function () {
        if (new URLSearchParams(window.location.search).has('search')) {
            document.getElementById('search').value = '';
        }
    });
</script>
</x-app-layout>
