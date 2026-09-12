@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/diagnostics/requests.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Diagnostics Requests</h1>

        <form class="d-flex flex-column gap-2" id="diagnostic_form">
            @csrf

            <div class="d-flex gap-2 w-100 align-items-end">
                <div class=" w-50 flex-col flex-grow-1">
                    <label class="form-label fw-bold" for="diagnostic_name">Name</label>
                    <input class="form-control" type="text" name="diagnostic_name" id="diagnostic_name" required>
                </div>

                <div class=" w-50">
                    <label class="form-label fw-bold" for="diagnostic_catg">Category</label>
                    <select class="form-select" name="diagnostic_catg" id="diagnostic_catg" required></select>
                </div>

                <button type="button" class="btn btn-warning w-50" id="create_diagnostic_btn">
                    <i class="fa-solid fa-plus"></i> Create Diagnostic Request
                </button>
            </div>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered align-middle caption-top" id="diagnostic_table">
                <caption>List of Diagnostic Requests</caption>
                <thead class="table-warning">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">Name</th>
                        <th scope="col">Category</th>
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
