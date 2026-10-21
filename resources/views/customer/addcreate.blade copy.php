<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Create Customer New') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <form action="{{route('customer.store')}}" method="post">
                        @csrf
                        <div class="row">
                            <div class="form-group col-md-6">
                                <label for="customerName">Customer Name</label>
                                <input type="text" class="form-control" name="customer_name" aria-describedby="emailHelp" placeholder="Enter Customer Name:" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label>Mobile Number</label>
                                <input type="text" class="form-control" name="mobile" placeholder="Enter Customer's Mobile Number" required pattern="[0-9]{11}" minlength="11" maxlength="11">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>Address</label>
                            <textarea class="form-control" name="address" placeholder="Enter Customer's Address" rows="2" oninput="autoResize(this)" style="resize: none; overflow: hidden;" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button> | 
                        <button type="button" class="btn btn-secondary" onclick="history.back()"> ← Back </button>
                    </form>
                </div>
            </div>
        </div>
        <br>
        <span> @include('layouts.footer')</span>
    </div>
    </div>
</x-app-layout>
<script>
function autoResize(textarea) {
    textarea.style.height = "auto";
    textarea.style.height = textarea.scrollHeight + "px";
}
</script>