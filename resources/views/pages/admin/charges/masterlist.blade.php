@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/charges/masterlist.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Charges Masterlist</h1>

        <form class="d-flex flex-column gap-2" id="charge_form">
            @csrf

            <div class="d-flex gap-2 w-100 align-items-end">
                <div class=" w-auto flex-col flex-grow-1">
                    <label class="form-label fw-bold" for="charge_name">Name</label>
                    <input class="form-control" type="text" name="charge_name" id="charge_name" required>
                </div>

                <div class=" w-25">
                    <label class="form-label fw-bold" for="charge_catg">Category</label>
                    <select class="form-select" name="charge_catg" id="charge_catg" required></select>
                </div>

                <div class=" w-25">
                    <label class="form-label fw-bold" for="charge_amount">Amount</label>
                    <input class="form-control" type="number" name="charge_amount" id="charge_amount" placeholder="0.00"
                        step="0.01" required>
                </div>
            </div>

            <div class="d-flex w-100 justify-content-end gap-2">
                <button type="button" class="btn btn-info w-25" id="create_charge_btn">
                    <i class="fa-solid fa-plus"></i> Create Charge
                </button>
            </div>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered align-middle caption-top" id="charges_table">
                <caption>List of Charges</caption>
                <thead class="table-primary">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
                        <th scope="col">Amount</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
                        <td></td>
                        <td></td>
                        <td></td>
                        <td></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.edit_charges')
@endpush
