@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/stocks/stock_management.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Management</h1>
        <hr>

        <div class="mb-3">
            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#add_item_modal"><i class="fa-solid fa-plus"></i> Add Item</button>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="stocks_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">Item Description</th>
                        <th scope="col">Category</th>
                        <th scope="col">PhilHealth Reference Code</th>
                        <th scope="col">Price <span class="text-secondary">(Regular)</span></th>
                        <th scope="col">Price <span class="text-secondary">(PHIC)</span></th>
                        <th scope="col">Price <span class="text-secondary">(HMO)</span></th>
                        <th scope="col">Price <span class="text-secondary">(Others)</span></th>
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
