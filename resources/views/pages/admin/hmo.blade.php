@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/secretary/hmo.js')
@endpush

@section('content')
    <div class="card h-100 p-4" id="admin_page">
        <h1 class="m-0">HMO</h1>
        <hr>

        <form class="d-flex mb-3" id="hmo-form">
            @csrf

            <div class="d-flex align-items-end gap-2">
                <div>
                    <label class="form-label" for="hmo-name">Name</label>
                    <input class="form-control" type="text" name="hmo_name" id="hmo_name">
                </div>

                <div>
                    <label class="form-label" for="hmo-type">Type</label>
                    <select class="form-select" name="hmo_type" id="hmo_type">
                        <option value="HMO">HMO</option>
                        <option value="COMPANY">Company</option>
                        <option value="GOVERNMENT">Government</option>
                    </select>
                </div>

                <button class="btn btn-primary" type="button" id="btn-add-hmo">
                    <i class="fa-solid fa-plus"></i> Add HMO
                </button>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="hmo-table">
                <thead class="table-warning">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">HMO Name</th>
                        <th scope="col">Type</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
