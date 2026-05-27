<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Brand') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    @if(session('success'))
                    <p style="color:green">{{ session('success') }}</p>
                @endif

                @if ($errors->any())
                    <div style="color:red">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="brandForm" action="{{ route('brand.create') }}" method="post">
                    @csrf
                    <input type="text" name="brand_name" id="brand_name" placeholder="Brand Name" required autocomplete="off">
                    <input type="submit" value="Add Brand" style="background-color:green;color:white;padding:8px"> | 
                    <button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>
                    <!-- <div id="brandList" style="border:1px solid #ccc; max-height:150px; overflow:auto;"></div> -->
                </form>

                </div>

            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
    <script>
        $(document).ready(function(){

            // 🔹 Uppercase live
            $('#brand_name').on('input', function(){
                $(this).val($(this).val().toUpperCase());

                let query = $(this).val();
                if(query.length > 0){
                    $.ajax({
                        url: "{{ route('brand.autocomplete') }}",
                        type: "GET",
                        data: { term: query },
                        success: function(data){
                            let html = '';
                            data.forEach(function(item){
                                html += `<div class="brand-item" style="padding:5px; cursor:pointer;">${item}</div>`;
                            });
                            $('#brandList').html(html).show();
                        }
                    });
                } else {
                    $('#brandList').hide();
                }
            });

            // 🔹 Click on autocomplete item
            $(document).on('click', '.brand-item', function(){
                $('#brand_name').val($(this).text());
                $('#brandList').hide();
            });

            // 🔹 Hide list when clicking outside
            $(document).click(function(e){
                if(!$(e.target).closest('#brand_name, #brandList').length){
                    $('#brandList').hide();
                }
            });

        });
    </script>
</x-app-layout>