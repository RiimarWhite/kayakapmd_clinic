@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/diagnostics/category.js')
@endpush

@section('content')
    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
        <div class="card h-100 p-4">
            <h1 class="m-0">Diagnostics Category</h1>

            <form class="d-flex gap-2" id="charge_form">
                @csrf

                <button type="button" class="btn btn-warning" id="create_diagnostic_category_btn">
                    <i class="fa-solid fa-plus"></i> Create Category
                </button>
            </form>

            <hr>

            <div class="table-responsive">
                <table class="table table-bordered align-middle caption-top" id="diagnostic_category_table">
                    <caption>Diagnostic Categories</caption>
                    <thead class="table-warning">
                        <tr>
                            <th scope="col">Actions</th>
                            <th scope="col"> Name</th>
                        </tr>
                    </thead>

                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
