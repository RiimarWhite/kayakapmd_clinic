@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/doctor/patients.js')
@endpush

@php $bodyId = 'doctorPage'; @endphp

@section('content')
    @if(auth()->guard('admin')->check())
        <div class="card p-3">
            <h1 class="m-0">Patient Masterlist</h1>
            <hr>

            <button class="btn btn-sm btn-success" style="width: fit-content;" id="add_patient_modal_btn"><i class="fa-solid fa-plus"></i> Add Patient</button>
    @endif

    <div class="d-flex gap-4 w-100">
        <div class="table-responsive w-100">
            <table class="table table-sm align-middle table-bordered" id="masterlist_table">
                <thead class="table-success">
                    <tr>
                        <th scope="col">Actions</th>
                        <th scope="col">Patient Name <span class="text-secondary">(Last, First, Middle, Suffix)</span></th>
                        <th scope="col">Consultation Date</th>
                    </tr>
                </thead>

                <tbody></tbody>
            </table>
        </div>
    </div>

    @if(auth()->guard('admin')->check())
        </div>
    @endif
@endsection

@push('modals')
    @include('modals.view_patient')
    @include('modals.doctor_info')
    @include('modals.add_patient')
@endpush
