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
                                <th scope="col" class="text-center" style="width: 100px;">Action</th>
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

        <!-- Detailed Comment: Default Professional/Consultation Fee & Billing Rates Configuration widget -->
        <div class="row g-3 mt-1">
            <div class="col-12">
                <div class="card shadow-sm">
                    <div class="card-header text-bg-warning text-dark d-flex justify-content-between align-items-center py-2">
                        <h5 class="card-title m-0 fw-bold"><i class="fa-solid fa-coins me-1"></i> Default Consultation Fee &amp; Billing Rates</h5>
                        <span class="badge bg-dark text-white">Auto-charge &amp; Settlement Default</span>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Configure your default consultation fee, rates, taxes, and billing preferences. These rates are automatically populated to patient charges and settlement during consultation.</p>
                        <form id="doctor_fee_form">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="doctor_pfrate">PF Rate / Consultation Fee (PHP)</label>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">PHP</span>
                                        <input type="number" step="0.01" min="0" class="form-control fw-bold text-success" id="doctor_pfrate" name="pfrate" value="{{ number_format((float)($doctor->pfrate ?? 0), 2, '.', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="doctor_rodrate">ROD Rate (PHP)</label>
                                    <div class="input-group">
                                        <span class="input-group-text fw-bold">PHP</span>
                                        <input type="number" step="0.01" min="0" class="form-control" id="doctor_rodrate" name="rodrate" value="{{ number_format((float)($doctor->rodrate ?? 0), 2, '.', '') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="doctor_tax">Withholding Tax %</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="doctor_tax" name="tax" placeholder="e.g. 10.00" value="{{ number_format((float)($doctor->tax ?? 0), 2, '.', '') }}">
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label fw-bold small" for="doctor_vatrate">VAT Rate %</label>
                                    <input type="number" step="0.01" min="0" class="form-control" id="doctor_vatrate" name="vatrate" placeholder="e.g. 12.00" value="{{ number_format((float)($doctor->vatrate ?? 0), 2, '.', '') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small" for="doctor_coacode">Chart of Accounts Code</label>
                                    <input type="text" class="form-control" id="doctor_coacode" name="coacode" value="{{ $doctor->coacode ?? '' }}">
                                </div>
                                <div class="col-md-5">
                                    <label class="form-label fw-bold small" for="doctor_accountno">Bank / Account No.</label>
                                    <input type="text" class="form-control" id="doctor_accountno" name="accountno" value="{{ $doctor->accountno ?? '' }}">
                                </div>

                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="vatable" id="doctor_vatable" value="1" {{ !empty($doctor->vatable) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="doctor_vatable">VATable Entity</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="autoAddVAT" id="doctor_autoAddVAT" value="1" {{ !empty($doctor->autoAddVAT) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="doctor_autoAddVAT">Auto Add VAT</label>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" name="issuehospOR" id="doctor_issuehospOR" value="1" {{ !empty($doctor->issuehospOR) ? 'checked' : '' }}>
                                        <label class="form-check-label fw-bold small" for="doctor_issuehospOR">Issue Hospital OR</label>
                                    </div>
                                </div>

                                <div class="col-12 text-end mt-3">
                                    <button type="button" class="btn btn-warning fw-bold px-4" id="save_doctor_fee_btn">
                                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Billing Rates &amp; Fee
                                    </button>
                                </div>
                            </div>
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
    {{-- Detailed Comment: Dedicated Consultation Details Viewer modal for viewing patient consultation details from dashboard --}}
    @include('modals.view_consultation_details')

    {{-- Detailed Comment: Dedicated Patient Consultations History Modal for Doctor Dashboard (Task R4) --}}
    <div class="modal fade m-0 p-0" data-bs-backdrop="static" id="patient_consultations_history_modal" tabindex="-1" aria-labelledby="patientConsultationsHistoryModalLabel" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-fullscreen modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h2 class="modal-title fs-5"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Patient Consultation History <span id="pch_patient_name" class="fs-6 text-muted fw-normal ms-2"></span></h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle w-100" id="patient_consultations_history_table">
                            <thead class="table-info">
                                <tr>
                                    <th scope="col" class="text-center" style="width: 80px;">Action</th>
                                    <th scope="col" class="text-center" style="width: 50px;">Photo</th>
                                    <th scope="col" style="width: 120px;">Consultation Date</th>
                                    <th scope="col">Reason for Consultation</th>
                                    <th scope="col">Diagnosis</th>
                                    <th scope="col" style="width: 110px;" class="text-center">Status</th>
                                    <th scope="col" style="width: 150px;">Doctor / Recorded By</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary fw-semibold" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Detailed Comment: Dedicated Preview Modal supporting image and PDF document preview for Radiology and Laboratory attachments (Task R4) --}}
    <div class="modal fade overflow-hidden" data-bs-backdrop="static" id="preview_modal" tabindex="-1" style="z-index: 1080;">
        <div class="modal-dialog modal-dialog-scrollable modal-xl">
            <div class="modal-content">
                <div class="modal-header" id="preview_title">
                    <h3 class="modal-title">Preview</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body text-center p-3">
                    <iframe id="docPreview" width="100%" height="600px" style="display: none; border: none;"></iframe>
                    <img id="imgPreview" class="img-fluid mx-auto d-none rounded shadow-sm" style="max-height: 600px; object-fit: contain;" src="" alt="Document Preview">
                </div>

                <div class="modal-footer">
                    <a id="preview_download_btn" href="#" target="_blank" download class="btn btn-outline-primary d-none"><i class="fa-solid fa-download me-1"></i> Download File</a>
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" id="preview_close_btn">Close</button>
                </div>
            </div>
        </div>
    </div>
@endpush
