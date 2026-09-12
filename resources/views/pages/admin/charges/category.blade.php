@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/charges/category.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Charges Category</h1>

        <form class="d-flex gap-2" id="charge_form">
            @csrf

            <button type="button" class="btn btn-info" id="create_charge_category_btn">
                <i class="fa-solid fa-plus"></i> Create Category
            </button>
        </form>

        <hr>

        <div class="table-responsive">
            <table class="table table-bordered align-middle caption-top" id="charges_category_table">
                <caption>Charges Category</caption>
                <thead class="table-info">
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
@endsection
