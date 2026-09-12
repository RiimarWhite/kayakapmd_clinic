@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/users/doctors.js')
@endpush

@section('content')
    <div class="card h-100 p-3">
        <div id="add-doctor">
            <h1 class="m-0">Doctors</h1>
            <hr>

            <button class="btn btn-sm btn-primary" id="add_doctor_btn"><i class="fa-solid fa-plus"></i> Add
                Doctor</button>

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle" id="doctor_table">
                    <thead class="table-primary">
                        <tr>
                            <th>Actions</th>
                            <th>Fullname</th>
                            <th>Specialization</th>
                            <th>Status</th>
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
    </div>
@endsection

@push('modals')
    @include('modals.add_doctor')
    @include('modals.manage_doctor')
    @include('modals.edit_doctor')
@endpush
