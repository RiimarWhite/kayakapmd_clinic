@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/stocks/stock_management.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Management</h1>
        <hr>

        <div class="mb-3 d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#add_item_modal"><i class="fa-solid fa-plus"></i> Add Item</button>
            <a href="{{ route('admin.stocks.groupings') }}" class="btn btn-sm btn-outline-primary"><i class="fa-solid fa-layer-group"></i> Grouping Management</a>
        </div>

        <!-- Detailed Comment: Stocks & Services Inventory Table with custom column filter and sorting headers -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="stocks_table">
                <caption>List of Clinic Stocks, Medications, and Service Offerings</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 110px;" class="text-center" id="th_stock_actions">Actions</th>
                        <th scope="col" id="th_stock_desc">Item Description</th>
                        <th scope="col" id="th_stock_group">Category</th>
                        <th scope="col" id="th_stock_subgroup">Group</th>
                        <th scope="col" id="th_stock_dosage_form">Dosage Form</th>
                        <th scope="col" id="th_stock_pge">PhilHealth PGE</th>
                        <th scope="col" id="th_stock_phic">PhilHealth Reference Code</th>
                        <th scope="col" id="th_stock_reg">Price (Regular)</th>
                        <th scope="col" id="th_stock_phic_price">Price (PHIC)</th>
                        <th scope="col" id="th_stock_hmo">Price (HMO)</th>
                        <th scope="col" id="th_stock_others">Price (Others)</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.admin.add_item')
    @include('modals.admin.edit_item')
@endpush
