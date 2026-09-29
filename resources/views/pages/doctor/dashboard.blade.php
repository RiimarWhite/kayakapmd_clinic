@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/doctor/dashboard.js')
@endpush

@php $bodyId = 'doctorPage'; @endphp

@section('content')
    <div class="d-flex flex-column h-100">
        <h3>{{ now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening') }},
            Dr. {{ $doctor->doclname }}!</h3>
        <hr>
        <div class="d-flex flex-grow-1 gap-2">
            <div class="card shadow-sm w-50">
                <div class="card-header text-bg-primary d-flex justify-content-between">
                    <h5 class="card-title m-0">Today's Patients</h5><span id="patient_count">1/30</span>
                </div>

                <div class="card-body py-0">
                    <table class="table table-sm table-bordered" id="todays_patients_table">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Queue</th>
                                <th scope="col">Patient Name</th>
                                <th scope="col">Status</th>
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

            <div class="card shadow-sm w-50">
                <!-- Detailed Comment: Doctor schedules card header with Add Schedule trigger -->
                <div class="card-header text-bg-success d-flex justify-content-between align-items-center py-2">
                    <h5 class="card-title m-0 fw-bold"><i class="fa-solid fa-calendar-days me-1"></i> Clinic Schedules</h5>
                    <button type="button" class="btn btn-sm btn-light text-success fw-bold" id="add_doctor_schedule_btn">
                        <i class="fa-solid fa-plus"></i> Add Schedule
                    </button>
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered mb-0" id="schedules_calendar">
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Detailed Comment: Default Professional/Consultation Fee Configuration widget per user requirements -->
        <div class="row g-3 mt-1">
            <div class="col-md-6">
                <div class="card shadow-sm h-100">
                    <div class="card-header text-bg-warning text-dark d-flex justify-content-between align-items-center py-2">
                        <h5 class="card-title m-0 fw-bold"><i class="fa-solid fa-coins me-1"></i> Default Consultation Fee</h5>
                        <span class="badge bg-dark text-white">Auto-charge</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Configure your default professional/consultation fee rate. This fee is automatically populated to the patient charges during consultation.</p>
                        <form id="doctor_fee_form" class="d-flex align-items-end gap-3">
                            <div class="flex-grow-1">
                                <label class="form-label fw-bold mb-1" for="doctor_pfrate">Professional Fee Rate (PHP)</label>
                                <div class="input-group">
                                    <span class="input-group-text fw-bold">PHP</span>
                                    <input type="number" step="0.01" min="0" class="form-control form-control-lg fw-bold text-success" id="doctor_pfrate" name="pfrate" value="{{ number_format((float)($doctor->pfrate ?? 0), 2, '.', '') }}">
                                </div>
                            </div>
                            <button type="button" class="btn btn-warning fw-bold btn-lg text-nowrap" id="save_doctor_fee_btn">
                                <i class="fa-solid fa-floppy-disk me-1"></i> Update Fee
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.doctor_info')
    @include('modals.doctor_schedule')
@endpush
