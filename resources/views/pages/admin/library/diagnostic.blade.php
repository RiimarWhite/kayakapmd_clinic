@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/library/diagnostic.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Diagnostics Reference Library</h1>
        <hr>

        <div class="d-flex justify-content-between">
            <div class="table-responsive">
                <table class="table table-sm table-bordered" id="diag_lib_table">
                    <thead class="table-warning">
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Description</th>
                        </tr>
                    </thead>

                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
