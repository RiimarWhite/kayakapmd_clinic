@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/stocks/stock_listing.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Inventory</h1>
        <hr>

        <div class="d-flex mb-4">
            <div>
                <label class="form-label" for="filter-category">Category</label>
                <select class="form-select" name="filter-category" id="filter-category">
                    <option value="ALL" selected>ALL</option>
                    <option value="DRUGS AND MEDS">DRUGS AND MEDS</option>
                    <option value="SUPPLIES">SUPPLIES</option>
                    <option value="PROCEDURES">PROCEDURES</option>
                    <option value="DIAGNOSTIC">DIAGNOSTIC</option>
                    <option value="IMAGING">IMAGING</option>
                    <option value="PROFESSIONAL FEE">PROFESSIONAL FEE</option>
                </select>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="stocks_listing_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col">Item Description</th>
                        <th scope="col">Quantity</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
