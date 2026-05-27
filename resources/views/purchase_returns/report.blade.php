<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Purchase Return') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">

                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    
                    @foreach($returns as $r)
                    <h5>{{ $r->return_no }} | {{ $r->purchase->Invoice_no }}</h5>
                    <ul>
                    @foreach($r->details as $d)
                    <li>{{ $d->product->product_name }} – {{ $d->qty }} pcs</li>
                    @endforeach
                    </ul>
                    @endforeach
                    
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
    {{-- JS --}}
    <script>
        
    </script>

