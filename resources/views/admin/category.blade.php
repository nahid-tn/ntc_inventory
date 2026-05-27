<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Category') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{route('admin.create')}}" method="post">
                        @csrf
                    <input type="text" name="category_name" class="uppercase" placeholder="Category Name" required>
                    <input style="background-color:green; color: white; padding: 8px " type="submit" name="submit" value="Add category"> | <button type="button" class="btn btn-secondary" onclick="history.back()">
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
    <script>

        document.addEventListener('input', function(e){
            if(e.target.classList.contains('uppercase')){
                e.target.value = e.target.value.toUpperCase();
            }
        });
</script>
</x-app-layout>
