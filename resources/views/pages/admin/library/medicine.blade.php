@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/library/medicines.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Medicine Reference Library</h1>
        <hr>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="med_lib_table">
                <thead class="table-warning">
                    <tr>
                        <th scope="col">Drug Code</th>
                        <th scope="col">Description</th>
                        <th scope="col">Generic Name</th>
                        <th scope="col">Salt</th>
                        <th scope="col">Form</th>
                        <th scope="col">Strength</th>
                        <th scope="col">Unit</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection
