<x-app-layout>
<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Expense Report') }}
    </h2>
</x-slot>

<div class="py-12">
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
            
            <a href="{{ route('expense.create') }}" class="btn btn-primary mb-4">➕ Add Expense</a>

            <form action="{{ route('expense.viewreport') }}" method="GET" class="row g-3">
                
                <div class="col-md-4">
                    <label class="form-label">Report Type</label>
                    <select name="type" class="form-control" required>
                        <option value="day" {{ request('type') == 'day' ? 'selected' : '' }}>Daily</option>
                        <option value="month" {{ request('type') == 'month' ? 'selected' : '' }}>Monthly</option>
                        <option value="year" {{ request('type') == 'year' ? 'selected' : '' }}>Yearly</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label">Select Date</label>
                    <input type="date" name="date" value="{{ request('date') }}" class="form-control" required>
                </div>

                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary">View Report</button>
                    <button type="submit" formaction="{{ route('expense.reportpdf') }}" formtarget="_blank" class="btn btn-danger">
                        📄 Download PDF
                    </button>
                </div>

            </form>
        </div>
    </div>

    <br>
    <span>@include('layouts.footer')</span>
</div>
</x-app-layout>