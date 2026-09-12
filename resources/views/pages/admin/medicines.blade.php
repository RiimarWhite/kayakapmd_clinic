@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/medicines.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Medicine Masterlist</h1>

        <div class="d-flex flex-column gap-3">
            <form class="d-flex gap-2" id="drug_form">
                @csrf

                <div class=" w-50">
                    <label class="form-label fw-bold" for="med_name">Medicine Name</label>
                    <input class="form-control" type="text" name="med_name" id="med_name" required>
                </div>

                <div class=" w-25">
                    <label class="form-label fw-bold" for="ph_id">PhilHealth ID Reference</label>
                    <div class="input-group">
                        <input class="form-control" type="text" name="ph_id" id="ph_id">
                        <button class="btn btn-danger" id="clear_reference"><i
                                class="fa-solid fa-circle-xmark"></i></button>
                    </div>
                </div>

                <div class="align-content-end w-25">
                    <button class="btn btn-success" type="button" id="add_med_btn"><i class="fa-solid fa-plus"></i> Add
                        Medicine</button>
                </div>
            </form>
        </div>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered" id="medicine_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">Medicine Name</th>
                        <th scope="col">ID Reference</th>
                    </tr>
                </thead>

                <tbody>
                    <tr>
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
    @include('modals.reference_modal')
@endpush
