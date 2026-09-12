@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/stocks/stock_ledger.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Stocks Ledger</h1>
        <hr>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="stocks_ledger_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col">Last Updated</th>
                        <th scope="col">Patient Name</th>
                        <th scope="col">Item</th>
                        <th scope="col">Dispensed Status</th>
                        <th scope="col">Dispensed Date</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
