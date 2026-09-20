@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/doctor/patients.js')
@endpush

@php $bodyId = 'doctorPage'; @endphp

@section('content')
    <input type="hidden" id="auth_is_admin" value="{{ auth()->guard('admin')->check() ? '1' : '0' }}">

    <div class="card p-3 shadow-sm">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <h2 class="m-0 fw-bold"><i class="fa-solid fa-users text-success me-2"></i> Patient Masterlist</h2>
                <div class="text-muted small">Manage patient records, demographics, and consultation histories.</div>
            </div>
            @if(auth()->guard('admin')->check())
                <button type="button" class="btn btn-sm btn-success fw-bold" id="add_patient_modal_btn">
                    <i class="fa-solid fa-user-plus"></i> Add New Patient
                </button>
            @endif
        </div>
        <hr class="my-2">

        <div class="table-responsive w-100 mt-2">
            <table class="table table-sm align-middle table-bordered w-100" id="masterlist_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col" class="text-center" style="width: 1%;">Actions</th>
                        <th scope="col" class="text-center" style="width: 5%;">Photo</th>
                        <th scope="col">Patient Name <span class="text-secondary">(Last, First, Middle, Suffix)</span></th>
                        <th scope="col" style="width: 12%;">PIN / Reference</th>
                        <th scope="col" style="width: 12%;">Mobile No.</th>
                        <th scope="col" style="width: 15%;">Email Address</th>
                        <th scope="col" class="text-center" style="width: 12%;">Last Consultation</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    {{-- Detailed Comment: Modals required for viewing, editing, adding, and viewing consultation history --}}
    @include('modals.view_patient')
    @include('modals.edit_patient')
    @include('modals.doctor_info')
    @include('modals.add_patient')
    @include('modals.patient_masterlist')
@endpush