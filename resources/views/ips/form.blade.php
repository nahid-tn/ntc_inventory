<x-app-layout>

<x-slot name="header">
    <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        IPS & Battery Calculator
    </h2>
</x-slot>

<div class="py-12">
<div class="max-w-6xl mx-auto sm:px-6 lg:px-8">

<div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg p-6">

<form method="POST" action="{{ route('ips.calculate') }}">
@csrf

{{-- ================= DEVICE TABLE ================= --}}
<h4 class="text-center mb-3">⚡ Device Load Calculator</h4>

<table class="table table-bordered" id="device_table">
<thead>
<tr>
<th>Device</th>
<th>Watt</th>
<th>Qty</th>
<th>Total</th>
<th>Action</th>
</tr>
</thead>

<tbody>
<tr>
<td>
<select class="form-control device_select">
<option value="">-- Select Device --</option>
<option value="75" data-name="Fan">Fan (75W)</option>
<option value="10" data-name="Light">Light (10W)</option>
<option value="100" data-name="LED TV">LED TV (100W)</option>
<option value="65" data-name="Laptop">Laptop (65W)</option>
<option value="300" data-name="Refrigerator">Refrigerator (300W)</option>
</select>
</td>

<td><input type="number" class="form-control watt" readonly></td>

<td><input type="number" class="form-control qty" value="1"></td>

<td><input type="text" class="form-control total_watt" readonly></td>

<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
</tr>
</tbody>
</table>

<button type="button" class="btn btn-success btn-sm mb-3" onclick="addRow()">+ Add Device</button>

{{-- ================= TOTAL LOAD ================= --}}
<div class="mb-3">
<label>Total Load (Watt)</label>
<input type="text" id="total_load" name="load" class="form-control" readonly required>
</div>

{{-- ================= DEVICE SUMMARY ================= --}}
<hr>
<h5 class="text-center">📋 Selected Device Summary</h5>

<table class="table table-bordered">
<thead>
<tr>
<th>Device</th>
<th>Total Qty</th>
<th>Total Watt</th>
</tr>
</thead>
<tbody id="device_summary_body"></tbody>
</table>

<hr>

{{-- ================= IPS INPUT ================= --}}
<div class="row">

<div class="col-md-6 mb-3">
<label>Backup Time (Hour)</label>
<input type="number" step="0.1" name="backup_time" class="form-control" required>
</div>

<div class="col-md-6 mb-3">
<label>Battery Voltage</label>
<select name="voltage" class="form-control">
<option value="12">12V</option>
<option value="24">24V</option>
</select>
</div>

</div>

<button class="btn btn-success">⚡ Calculate</button>
<button type="button" class="btn btn-secondary" onclick="history.back()">← Back</button>

</form>

{{-- ================= RESULT ================= --}}
@if(isset($ah))
<hr>

<h4 class="text-center mb-4">🔍 Result</h4>

<div class="row text-center">

<div class="col-md-4">
<div class="card border-success">
<div class="card-body">
<h6>🔋 Required Battery</h6>
<h4 class="text-success">{{ round($ah) }} Ah</h4>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card border-primary">
<div class="card-body">
<h6>✅ Recommended Battery</h6>
<h4 class="text-primary">{{ round($safe_ah) }} Ah</h4>
</div>
</div>
</div>

<div class="col-md-4">
<div class="card border-warning">
<div class="card-body">
<h6>⚡ IPS Capacity</h6>
<h4 class="text-warning">{{ round($va) }} VA</h4>
</div>
</div>
</div>

</div>
@endif

</div>
</div>
</div>

{{-- ================= JAVASCRIPT ================= --}}
<script>

function addRow() {
let row = document.querySelector('#device_table tbody').insertRow();
row.innerHTML = `
<td>
<select class="form-control device_select">
<option value="">-- Select Device --</option>
<option value="75" data-name="Fan">Fan (75W)</option>
<option value="10" data-name="Light">Light (10W)</option>
<option value="100" data-name="LED TV">LED TV (100W)</option>
<option value="65" data-name="Laptop">Laptop (65W)</option>
<option value="300" data-name="Refrigerator">Refrigerator (300W)</option>
</select>
</td>
<td><input type="number" class="form-control watt" readonly></td>
<td><input type="number" class="form-control qty" value="1"></td>
<td><input type="text" class="form-control total_watt" readonly></td>
<td><button type="button" class="btn btn-danger btn-sm" onclick="removeRow(this)">X</button></td>
`;
}

function removeRow(btn) {
btn.closest('tr').remove();
calculateTotal();
}

document.addEventListener('change', function(e) {

let row = e.target.closest('tr');

if (e.target.classList.contains('device_select')) {
row.querySelector('.watt').value = e.target.value;
}

if (e.target.classList.contains('device_select') || e.target.classList.contains('qty')) {

let watt = parseFloat(row.querySelector('.watt').value) || 0;
let qty = parseFloat(row.querySelector('.qty').value) || 0;

row.querySelector('.total_watt').value = watt * qty;

calculateTotal();
}

});

function calculateTotal() {

let total = 0;

document.querySelectorAll('.total_watt').forEach(el => {
total += parseFloat(el.value) || 0;
});

document.getElementById('total_load').value = total;

// 🔥 SUMMARY UPDATE
updateSummary();
}

function updateSummary() {

let summary = {};

document.querySelectorAll('#device_table tbody tr').forEach(row => {

let select = row.querySelector('.device_select');
let qty = parseFloat(row.querySelector('.qty').value) || 0;
let watt = parseFloat(row.querySelector('.watt').value) || 0;

let name = select.options[select.selectedIndex]?.dataset?.name;

if (!name) return;

if (!summary[name]) {
summary[name] = { qty: 0, watt: 0 };
}

summary[name].qty += qty;
summary[name].watt += watt * qty;

});

let tbody = document.getElementById('device_summary_body');
tbody.innerHTML = '';

for (let key in summary) {
tbody.innerHTML += `
<tr>
<td>${key}</td>
<td>${summary[key].qty}</td>
<td>${summary[key].watt} W</td>
</tr>
`;
}
}

</script>

</x-app-layout>