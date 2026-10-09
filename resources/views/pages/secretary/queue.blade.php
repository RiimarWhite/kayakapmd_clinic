@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/secretary/queue.js')
@endpush

@php $bodyId = 'secretaryPage'; @endphp

@section('content')
    @if(auth()->guard('admin')->check())
        <script>
            const secretaries = @json($secretaries);

            document.addEventListener("DOMContentLoaded", function () {
                const opt = secretaries.map(sec =>
                    `<option value="${sec.secrefno}">${sec.seclname}</option>`
                ).join("");

                Swal.fire({
                    title: "Choose Secretary",
                    html: `
                                <select class="form-select" id="secretary_select">
                                    <option value="" disabled selected>-- Select --</option>
                                    ${opt}
                                </select>
                            `,
                    confirmButtonText: "Confirm",
                    allowOutsideClick: false,
                    preConfirm: () => {
                        const value = document.getElementById('secretary_select').value;

                        if (!value) { Swal.showValidationMessage('Please select a secretary'); }

                        return value;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: "/api/get_assigned_doctors",
                            type: "POST",
                            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
                            data: { secrefno: result.value },
                            success: function (data) {
                                let opts = `<option value="" selected>None</option>`;

                                data.forEach(e => {
                                    opts += `<option value="${e.docrefno}">DR. ${e.docname}</option>`
                                });

                                $("#doctor_id").html(opts);
                                $("#doctor_for_consult").html(opts);
                            }
                        });
                    }
                });
            });
        </script>
    @endif

    <div class="row g-3">
        <!-- 1. Patient Queue Column (Left on Desktop, Top on Tablet/Mobile) -->
        <div class="col-12 col-xl-5 col-xxl-5 d-flex flex-column gap-3">
            <div class="card d-flex flex-column shadow-sm">
                <div class="card-header fw-semibold bg-primary text-white">
                    <i class="fa-solid fa-list-ul"></i> Patient Queue
                </div>
                <div class="card-body d-flex flex-column flex-grow-1 gap-2 p-3">
                    <div class="d-flex flex-column gap-2 w-100">
                        <div class="w-100">
                            <select class="form-select form-select-sm" name="doctor_id" id="doctor_id" required>
                                <option value="" selected>None</option>
                                @foreach ($doctors as $doctor)
                                    <option value="{{ $doctor->docrefno }}">
                                        DR. {{ strtoupper($doctor->docname) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Select doctor to view doctor's patient queue.</div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="w-50">
                                <div class="d-flex input-group">
                                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="sprevious">
                                        <i class="fa-solid fa-angle-left"></i>
                                    </button>

                                    <input class="form-control form-control-sm" type="date" name="queuedate"
                                        id="queuedate" value="{{ now()->toDateString() }}">

                                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="snext">
                                        <i class="fa-solid fa-angle-right"></i>
                                    </button>
                                </div>
                                <div class="form-text">Enter consultation date.</div>
                            </div>

                            <div class=" w-50">
                                <div class="input-group">
                                    <select class="form-select form-select-sm" name="stime" id="stime"></select>
                                </div>
                                <div class="form-text">Enter consultation time.</div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive mt-2">
                        <table class="table table-sm table-bordered table-hover" id="patients_queue_table">
                            <thead class="table-success">
                                <tr>
                                    <!-- Detailed Comment: Draggable queue column header per user requirement -->
                                    <th scope="col" style="width: 55px;" class="text-center" title="Drag row to reorder queue"><i class="fa-solid fa-grip-vertical me-1 text-muted"></i>#</th>
                                    <th scope="col">Actions</th>
                                    <th scope="col">Patient Name <span class="text-secondary">(First, Middle, Last,
                                            Suffix)</span></th>
                                    <th scope="col">Status</th>
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

            {{-- Detailed Comment: Queue Financial Report & Daily Income Summary card placed at the bottom of the Patient Queue card --}}
            <div class="card d-flex flex-column shadow-sm border-primary" id="financial_summary_card">
                <div class="card-header fw-semibold bg-light text-primary d-flex justify-content-between align-items-center py-2">
                    <span><i class="fa-solid fa-chart-line me-1"></i> Daily Income &amp; Financial Summary</span>
                    <div class="d-flex gap-1">
                        <button type="button" class="btn btn-sm btn-outline-primary" id="refresh_financial_summary_btn" title="Refresh Income Summary">
                            <i class="fa-solid fa-arrows-rotate"></i>
                        </button>
                        <a href="#" target="_blank" class="btn btn-sm btn-primary fw-bold" id="print_financial_report_btn" title="Print Consolidated Financial Report">
                            <i class="fa-solid fa-print me-1"></i> Print Report
                        </a>
                    </div>
                </div>
                <div class="card-body p-2">
                    <!-- KPI metrics pills -->
                    <div class="row g-2 text-center mb-2">
                        <div class="col-4">
                            <div class="border rounded p-1 bg-light">
                                <div class="text-muted small" style="font-size: 10px;">Total Gross</div>
                                <div class="fw-bold text-dark fs-6" id="fin_sum_gross">₱0.00</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-1 bg-light">
                                <div class="text-muted small" style="font-size: 10px;">Net Billing</div>
                                <div class="fw-bold text-primary fs-6" id="fin_sum_net">₱0.00</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="border rounded p-1 bg-light">
                                <div class="text-muted small" style="font-size: 10px;">Collected</div>
                                <div class="fw-bold text-success fs-6" id="fin_sum_paid">₱0.00</div>
                            </div>
                        </div>
                    </div>

                    <!-- Collections & Deductions breakdown table -->
                    <table class="table table-sm table-borderless m-0 small" style="font-size: 11px;">
                        <tbody>
                            <tr>
                                <td class="text-muted py-0">Less PHIC Deductions:</td>
                                <td class="text-end text-success py-0 fw-semibold" id="fin_sum_phic">-₱0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-0">Less HMO Deductions:</td>
                                <td class="text-end text-primary py-0 fw-semibold" id="fin_sum_hmo">-₱0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-0">Less Senior/PWD:</td>
                                <td class="text-end text-warning py-0 fw-semibold" id="fin_sum_senior">-₱0.00</td>
                            </tr>
                            <tr class="border-top">
                                <td class="text-muted py-1">Cash Collected:</td>
                                <td class="text-end py-1 fw-semibold" id="fin_sum_cash">₱0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-0">Card / CTA:</td>
                                <td class="text-end py-0 fw-semibold" id="fin_sum_card">₱0.00</td>
                            </tr>
                            <tr>
                                <td class="text-muted py-0">PhilHealth Yakap Co-Pay:</td>
                                <td class="text-end text-info py-0 fw-semibold" id="fin_sum_copay">₱0.00</td>
                            </tr>
                            <tr class="border-top fw-bold">
                                <td class="text-danger py-1">Remaining Balance:</td>
                                <td class="text-end text-danger py-1" id="fin_sum_balance">₱0.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 2. Patient Consultation Details Column (Right on Desktop, Middle on Tablet/Mobile) -->
        <div class="col-12 col-xl-7 col-xxl-7">
            <form id="consultation_form">
                @csrf

                <div class="card shadow-sm d-flex flex-column">
                    <div class="card-header">
                        <!-- Topbar Buttons -->
                        <div class="d-flex gap-1 align-items-center">
                            {{-- Detailed Comment: 1-Click Document Printing dropdown matching Step 3 & 6 of OPD Consultation Workflow Plan --}}
                            <div class="dropdown">
                                <button class="btn btn-sm btn-info text-white fw-bold dropdown-toggle" type="button" id="printDocsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    <i class="fa-solid fa-print"></i> Print Documents
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="printDocsDropdown">
                                    <li><a class="dropdown-item" id="sec_print_rx_btn" target="_blank" href="#"><i class="fa-solid fa-prescription text-info me-2"></i> Print Rx (Prescription)</a></li>
                                    <li><a class="dropdown-item" id="sec_print_diag_btn" target="_blank" href="#"><i class="fa-solid fa-envelope-open-text text-success me-2"></i> Print Diagnostic Requests</a></li>
                                    <li><a class="dropdown-item" id="sec_print_admit_btn" target="_blank" href="#"><i class="fa-solid fa-hospital-user text-danger me-2"></i> Print Admission / Kin Orders</a></li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><a class="dropdown-item fw-bold" id="sec_print_soa_btn" target="_blank" href="#"><i class="fa-solid fa-file-invoice-dollar text-primary me-2"></i> Print Statement of Account (SOA)</a></li>
                                </ul>
                            </div>

                            <button type="button" class="btn btn-sm btn-danger fw-bold text-nowrap ms-auto" id="clear_form">
                                <i class="fa-solid fa-trash-can"></i> Clear Consultation Form
                            </button>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column gap-2 flex-grow-1 overflow-auto">
                        <!-- Patient Information -->
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-column gap-2 w-100">
                                {{-- Detailed Comment: Prominent Secretary Allergy Alert Banner --}}
                                <div class="alert alert-danger py-1 px-3 mb-1 d-none align-items-center gap-2 border-danger" id="sec_allergy_alert_bar" role="alert">
                                    <i class="fa-solid fa-triangle-exclamation text-danger fs-5"></i>
                                    <div class="small">
                                        <strong class="text-danger">KNOWN ALLERGIES:</strong> <span id="sec_allergy_alert_text" class="fw-bold"></span>
                                    </div>
                                </div>

                                <div class="d-flex flex-column gap-0 ms-1">
                                    <input type="hidden" name="pincode" id="pincode">
                                    <input type="hidden" name="consultationrefno" id="hidden_consultationrefno">
                                    <p class="text-secondary m-0"><span class="fw-bold">Consultation Reference #:</span>
                                        <span id="pxconsultationrefno"></span>
                                    </p>
                                    <p class="text-secondary m-0"><span class="fw-bold">Patient ID:</span> <span
                                            id="pxidno"></span></p>
                                </div>

                                <fieldset class="gap-1" readonly>
                                    <div class="d-flex">
                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxfname">First Name</label>
                                            <input type="text" class="form-control form-control-sm" name="pxfname"
                                                id="pxfname">
                                        </div>

                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxmname">Middle Name</label>
                                            <input type="text" class="form-control form-control-sm" name="pxmname"
                                                id="pxmname">
                                        </div>

                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxlname">Last Name</label>
                                            <input type="text" class="form-control form-control-sm" name="pxlname"
                                                id="pxlname">
                                        </div>

                                        <div class="p-1" style="max-width: 5rem;">
                                            <label class="form-label fw-bold m-0" for="pxsuffix">Suffix</label>
                                            <input type="text" class="form-control form-control-sm" name="pxsuffix"
                                                id="pxsuffix">
                                        </div>
                                    </div>

                                    <div class="d-flex">
                                        <div class="p-1" style="max-width: 7.5rem;">
                                            <label class="form-label fw-bold m-0" for="pxsex">Sex</label>
                                            <select class="form-select form-select-sm" name="pxsex" id="pxsex">
                                                <option value="M">M</option>
                                                <option value="F">F</option>
                                            </select>
                                            <!-- <input type="text" class="form-control form-control-sm" name="pxsex" id="pxsex"> -->
                                        </div>

                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxbday">Birthdate</label>
                                            <input type="date" class="form-control form-control-sm" name="pxbday"
                                                id="pxbday">
                                        </div>

                                        <div class="p-1" style="max-width: 5rem;">
                                            <label class="form-label fw-bold m-0" for="pxage">Age</label>
                                            <input type="text" class="form-control form-control-sm" name="pxage" id="pxage">
                                        </div>

                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxcellnumber">Mobile No.</label>
                                            <input type="tel" class="form-control form-control-sm" name="pxcellnumber"
                                                id="pxcellnumber">
                                        </div>

                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxlandlinenumber">Landline
                                                No.</label>
                                            <input type="tel" class="form-control form-control-sm" name="pxlandlinenumber"
                                                id="pxlandlinenumber">
                                        </div>
                                    </div>

                                    <div class="d-flex">
                                        <div class="p-1">
                                            <label class="form-label fw-bold m-0" for="pxemail">Email Address</label>
                                            <input type="email" class="form-control form-control-sm" name="pxemail"
                                                id="pxemail">
                                        </div>

                                        <div class="p-1 w-100">
                                            <label class="form-label fw-bold m-0" for="pxaddress">Address</label>
                                            <input type="text" class="form-control form-control-sm" name="pxaddress"
                                                id="pxaddress">
                                        </div>
                                    </div>
                                </fieldset>
                            </div>

                            <div class="d-flex flex-column gap-1 mb-1 mt-auto">
                                <img class="border border-secondary"
                                    style="max-height: 12rem; min-height: 12rem; max-width: 12rem; min-width: 12rem;"
                                    src="{{ asset('images/blank_photo.png') }}" alt="patient-image"
                                    id="patient_picture_preview">
                                <div class="input-group justify-content-center">
                                    <input type="hidden" name="photo_path" id="photo_path">
                                    <input type="hidden" name="photo_base64" id="photo_base64">
                                    <button type="button" class="btn btn-sm btn-primary text-nowrap fw-bold"
                                        id="upload_patient_image">
                                        <i class="fa-solid fa-upload"></i> Upload Image
                                    </button>

                                    <button type="button" class="btn btn-sm btn-secondary" id="take_photo">
                                        <i class="fa-solid fa-camera"></i>
                                    </button>
                                </div>

                                <input class="d-none" type="file" accept="image/*" name="patient_image" id="patient_image">
                            </div>
                        </div>

                        <div>
                            <ul class="nav nav-tabs nav-fill" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link active" data-bs-toggle="tab"
                                        data-bs-target="#consul_info" role="tab" aria-controls="consul_info"
                                        aria-selected="true">
                                        <i class="fa-solid fa-notes-medical me-1"></i> Consultation Details
                                    </button>
                                </li>

                                <!-- Detailed Comment: Consultation History tab renamed per user request -->
                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#medhistory_info" role="tab" aria-controls="medhistory_info"
                                        aria-selected="false" id="patient_medhistory_tab_btn">
                                        <i class="fa-solid fa-clock-rotate-left me-1"></i> Consultation History
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#payment_info" role="tab" aria-controls="payment_info"
                                        aria-selected="false" id="patient_charges_btn">
                                        <i class="fa-solid fa-credit-card me-1"></i> Payment Details
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#permanent_medhistory_info" role="tab" aria-controls="permanent_medhistory_info"
                                        aria-selected="false" id="patient_permanent_medhistory_tab_btn">
                                        <i class="fa-solid fa-file-waveform me-1"></i> Medical History
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content border border-top-0 rounded-bottom p-3 d-flex flex-column flex-grow-1">
                                <div class="tab-pane active" id="consul_info" role="tabpanel">
                                    <div class="d-flex align-items-end gap-1">
                                        <div>
                                            <label class="form-label fw-bold m-0" for="doctor_for_consult">Assign
                                                Doctor</label>
                                            <select class="form-select form-select-sm" name="doctor_for_consult"
                                                id="doctor_for_consult" required>
                                                @foreach ($doctors as $doctor)
                                                    <option value="{{ $doctor->docrefno }}">DR. {{ $doctor->docname }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="form-text">Assign doctor for this consultation.</div>
                                        </div>

                                        <div class="d-flex flex-column ms-auto">
                                            <div class="input-group">
                                                {{-- Detailed Comment: Default sched_date to current date per secretary/admin queue requirements --}}
                                                <input type="date" class="form-control form-control-sm" name="sched_date"
                                                    id="sched_date" value="{{ now()->toDateString() }}">
                                                <select class="form-select form-select-sm" name="sched_time"
                                                    id="sched_time"></select>
                                            </div>
                                            <div class="form-text">Update or assign new consultation date.</div>
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column gap-2 mt-3">
                                        <div>
                                            <label class="form-label fw-bold m-0" for="questions">Questions:</label>
                                            <div class="d-flex flex-column" id="questions_container"></div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <div class="w-100">
                                                <label class="form-label fw-bold m-0" for="reason_for_consultation">Chief
                                                    Complaints</label>
                                                <textarea class="form-control form-control-sm" rows="4"
                                                    name="reason_for_consultation" id="pxreasonforconsultation"></textarea>
                                            </div>

                                            <div class="d-flex flex-column gap-2" style="min-width: 12.5rem;">
                                                <div>
                                                    <label class="form-label fw-bold m-0" for="weight">Weight</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control form-control-sm"
                                                            style="max-width: 7.5rem;" min="0.00" value="0.00" name="weight"
                                                            id="pxweight">
                                                        <select class="form-select form-select-sm" style="max-width: 5rem;"
                                                            name="w_unit" id="pxweight_unit">
                                                            <option value="kg">kg</option>
                                                            <option value="lbs">lbs</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                <div>
                                                    <label class="form-label fw-bold m-0" for="height">Height</label>
                                                    <div class="input-group">
                                                        <input type="number" class="form-control form-control-sm"
                                                            style="max-width: 7.5rem;" min="0.00" value="0.00" name="height"
                                                            id="pxheight">
                                                        <select class="form-select form-select-sm" style="max-width: 5rem;"
                                                            name="h_unit" id="pxheight_unit">
                                                            <option value="cm">cm</option>
                                                            <option value="m">m</option>
                                                            <option value="ft">ft</option>
                                                        </select>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <div>
                                                <label class="form-label fw-bold m-0" for="temperature">Temperature</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control form-control-sm"
                                                        style="max-width: 7.5rem;" min="0.00" value="0.00"
                                                        name="temperature" id="pxtemp">
                                                    <select class="form-select form-select-sm" style="max-width: 5rem;"
                                                        name="t_unit" id="pxtemp_unit">
                                                        <option value="C">°C</option>
                                                        <option value="F">°F</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div>
                                                <label class="form-label fw-bold m-0" for="respiratory_rate">Respiratory
                                                    Rate</label>
                                                <input type="number" class="form-control form-control-sm" min="0.00"
                                                    value="0.00" name="respiratory_rate" id="pxrespiratory">
                                            </div>

                                            <div>
                                                <label class="form-label fw-bold m-0" for="pulse_rate">Pulse Rate</label>
                                                <input type="number" class="form-control form-control-sm" min="0.00"
                                                    value="0.00" name="pulse_rate" id="pxpulse">
                                            </div>

                                            <div>
                                                <label class="form-label fw-bold m-0" for="pulse_rate">Blood
                                                    Pressure</label>
                                                <div class="input-group">
                                                    <input type="number" class="form-control form-control-sm" min="0.00"
                                                        placeholder="Systolic" name="bp_numerator" id="pxbpnumerator">
                                                    <input type="number" class="form-control form-control-sm" min="0.00"
                                                        placeholder="Diastolic" name="bp_denominator" id="pxbpdenominator">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-2">
                                            <div class="w-50">
                                                <label class="form-label fw-bold m-0" for="preview_radiology">Radiology
                                                    Result</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control form-control-sm"
                                                        name="radiologypath" id="radiologypath">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        id="preview_radiology">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            <div class="w-50">
                                                <label class="form-label fw-bold m-0" for="preview_laboratory">Laboratory
                                                    Result</label>
                                                <div class="input-group">
                                                    <input type="file" class="form-control form-control-sm"
                                                        name="laboratorypath" id="laboratorypath">
                                                    <button type="button" class="btn btn-sm btn-primary"
                                                        id="preview_laboratory">
                                                        <i class="fa-solid fa-eye"></i>
                                                    </button>
                                                </div>
                                            </div>
                                        </div>

                                        <div>
                                            <label class="form-label fw-bold" for="patient_type">Patient Type</label>
                                            <div class="d-flex gap-2">
                                                <select class="form-select" name="patient_type" id="patient_type">
                                                    <option value="regular" selected>REGULAR</option>
                                                    <option value="hmo">HMO</option>
                                                    <option value="phic">PHIC</option>
                                                    <option value="others">OTHERS</option>
                                                </select>
                                                <select class="form-select" name="hmo_input" id="hmo_input"></select>
                                            </div>
                                        </div>

                                        <!-- Detailed Comment: Consultation action buttons for saving a new record or updating the current consultation details. Redundant Mark as Complete button removed per requirements since queue status is managed in the queue table. -->
                                        <div class="d-flex gap-2">
                                            <button type="button"
                                                class="btn btn-sm btn-success fw-bold save_consultation_btn">
                                                <i class="fa-solid fa-square-check"></i> Save as New Record
                                            </button>

                                            <button type="button"
                                                class="btn btn-sm btn-warning fw-bold text-white update_consultation_btn">
                                                <i class="fa-solid fa-clipboard"></i> Update Consultation Details
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <!-- Detailed Comment: Consultation History tab pane rendering patient's historical consultations with dedicated details modal access -->
                                <div class="tab-pane" id="medhistory_info" role="tabpanel">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <h5 class="fw-bold m-0"><i class="fa-solid fa-clock-rotate-left text-primary me-2"></i> Patient Consultation History</h5>
                                        <span class="badge bg-secondary" id="sec_medhistory_count">0 records</span>
                                    </div>
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="sec_medhistory_table">
                                            <thead class="table-info">
                                                <tr>
                                                    <th scope="col" class="text-center" style="width: 80px;">Action</th>
                                                    <th scope="col" class="text-center" style="width: 50px;">Photo</th>
                                                    <th scope="col" style="width: 120px;">Consultation Date</th>
                                                    <th scope="col">Reason for Consultation</th>
                                                    <th scope="col" style="width: 100px;">Status</th>
                                                    <th scope="col" style="width: 150px;">Recorded By</th>
                                                    <th scope="col" style="width: 120px;">Recorded On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td colspan="7" class="text-center text-muted py-3">No patient consultation history loaded yet. Import or select a patient to view consultation history.</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>

                                <div class="tab-pane" id="payment_info" role="tabpanel">
                                    <!-- Detailed Comment: Current Consultation Payment & Charges Section with Patient Type Reference -->
                                    <div class="card border-0 shadow-sm mb-3">
                                        <div class="card-header bg-light d-flex flex-wrap justify-content-between align-items-center py-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="fw-bold text-dark"><i class="fa-solid fa-receipt text-primary me-1"></i> Current Consultation Charges</span>
                                                <span class="badge bg-primary" id="pay_tab_consultdate_badge">Date: --</span>
                                                <span class="badge bg-secondary" id="pay_tab_consultref_badge">Ref: --</span>
                                                <span class="badge bg-info text-dark" id="pay_tab_settlement_status">Status: Pending</span>
                                                <span class="badge bg-warning text-dark" id="pay_tab_patient_type_badge">Type: REGULAR</span>
                                            </div>
                                            <div>
                                                <button type="button" class="btn btn-sm btn-warning text-white fw-bold shadow-sm"
                                                    id="append_pxcharges_btn">
                                                    <i class="fa-solid fa-plus me-1"></i> Append Charges
                                                </button>
                                            </div>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered caption-top w-100"
                                                    id="pxcharges_table">
                                                    <caption>Charges for current consultation</caption>
                                                    <thead class="table-warning">
                                                        <tr>
                                                            <th scope="col" style="width: 10%;" class="text-center">Actions</th>
                                                            <th scope="col">Description</th>
                                                            <th scope="col" style="width: 10%;" class="text-center">Quantity</th>
                                                            <th scope="col" style="width: 15%;" class="text-end">Unit Price</th>
                                                            <th scope="col" style="width: 15%;" class="text-end">Amount</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>

                                            <div class="d-flex flex-wrap justify-content-between align-items-center mt-3 pt-2 border-top">
                                                <div>
                                                    <h5 class="fw-bold m-0 text-primary">Total: PHP <span class="fw-normal" id="charges_total">0.00</span></h5>
                                                    <small class="text-muted" id="pay_tab_soaref_wrap">SOA / Trans No: <span id="pay_tab_soaref" class="fw-semibold">--</span></small>
                                                </div>
                                                <div class="d-flex gap-2">
                                                    {{-- Detailed Comment: Target updated modal ID settlement_modal --}}
                                                    <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                        data-bs-target="#settlement_modal" id="settlement_btn">
                                                        <i class="fa-solid fa-credit-card me-1"></i> Settlements
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Detailed Comment: Previous Payments History Section based on patient consultation records -->
                                    <div class="card border-0 shadow-sm mt-3">
                                        <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                            <span class="fw-bold text-dark">
                                                <i class="fa-solid fa-clock-rotate-left text-secondary me-1"></i> Previous Payments (Consultation History)
                                            </span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary" id="refresh_payment_history_btn" title="Refresh Payment History">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                                        </div>
                                        <div class="card-body p-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-striped table-hover table-bordered w-100" id="px_previous_payments_table">
                                                    <thead class="table-secondary">
                                                        <tr>
                                                            <th>Consultation Date</th>
                                                            <th>Consultation Ref</th>
                                                            <th>Doctor</th>
                                                            <th class="text-end">Total Bill</th>
                                                            <th class="text-end">Amount Paid</th>
                                                            <th>Channel</th>
                                                            <th class="text-center">Status</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td colspan="7" class="text-center text-muted">Select a patient consultation to view past payment history.</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Detailed Comment: Dedicated Permanent Patient Medical History tab pane --}}
                                <div class="tab-pane" id="permanent_medhistory_info" role="tabpanel">
                                    <div class="card border-0">
                                        <div class="card-body p-1">
                                            <div class="d-flex justify-content-between align-items-center mb-2">
                                                <span class="fw-bold text-dark"><i class="fa-solid fa-notes-medical text-primary me-1"></i> Permanent Patient Medical History</span>
                                                <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_sec_medhistory_btn">
                                                    <i class="fa-solid fa-floppy-disk me-1"></i> Save Medical History
                                                </button>
                                            </div>

                                            <div class="row g-2">
                                                <div class="col-12 col-md-6">
                                                    <div class="p-2 border rounded bg-light mb-2">
                                                        <label class="form-label fw-bold text-danger small mb-1" for="sec_allergies">
                                                            <i class="fa-solid fa-triangle-exclamation me-1"></i> Allergies &amp; Drug Reactions:
                                                        </label>
                                                        <textarea class="form-control form-control-sm border-danger" id="sec_allergies" rows="2" placeholder="e.g. Seafood, Penicillin, Aspirin, Latex"></textarea>
                                                    </div>

                                                    <div class="p-2 border rounded bg-light mb-2">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_injections">
                                                            <i class="fa-solid fa-syringe me-1 text-primary"></i> Injections &amp; Immunizations:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_injections" rows="2" placeholder="e.g. Flu Vaccine (2025), Pneumococcal, Tetanus Toxoid"></textarea>
                                                    </div>

                                                    <div class="p-2 border rounded bg-light">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_maintenance_meds">
                                                            <i class="fa-solid fa-pills me-1 text-info"></i> Maintenance Medications:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_maintenance_meds" rows="2" placeholder="e.g. Amlodipine 5mg OD, Metformin 500mg BID"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-12 col-md-6">
                                                    <div class="p-2 border rounded bg-light mb-2">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_past_medical_history">
                                                            <i class="fa-solid fa-heart-pulse me-1 text-secondary"></i> Past Medical Conditions:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_past_medical_history" rows="2" placeholder="e.g. Hypertension (5 yrs), Type 2 Diabetes, Asthma"></textarea>
                                                    </div>

                                                    <div class="p-2 border rounded bg-light mb-2">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_surgical_history">
                                                            <i class="fa-solid fa-bandage me-1 text-warning"></i> Surgical History:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_surgical_history" rows="2" placeholder="e.g. Appendectomy (2018), C-Section (2021)"></textarea>
                                                    </div>

                                                    <div class="p-2 border rounded bg-light">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_family_history">
                                                            <i class="fa-solid fa-users me-1 text-success"></i> Family Medical History:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_family_history" rows="2" placeholder="e.g. Maternal DM, Paternal CVD / Stroke"></textarea>
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <div class="p-2 border rounded bg-light">
                                                        <label class="form-label fw-bold text-dark small mb-1" for="sec_clinical_notes">
                                                            <i class="fa-solid fa-comment-medical me-1 text-secondary"></i> Special Clinical Notes &amp; Warnings:
                                                        </label>
                                                        <textarea class="form-control form-control-sm" id="sec_clinical_notes" rows="2" placeholder="Special patient precautions, dietary restrictions, or notes"></textarea>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <div class="my-1">
                            <button type="button" class="btn btn-sm btn-primary fw-bold" id="mark_as_complete_btn">
                                <i class="fa-solid fa-square-check"></i> Mark as Complete
                            </button>
                            <div class="form-text">This will mark this patient's consultation as COMPLETED.</div>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        <!-- 3. Patient Masterlist Column (Positioned below queue and consultation per requested layout) -->
        <div class="col-12 mt-3">
            <div class="card d-flex flex-column shadow-sm">
                <div class="card-header fw-semibold bg-secondary text-white d-flex justify-content-between align-items-center">
                    <span><i class="fa-solid fa-address-book me-1"></i> Patient Masterlist</span>
                    <button type="button" class="btn btn-sm btn-success fw-bold" id="btn_add_patient_masterlist" data-bs-toggle="modal" data-bs-target="#add_patient_modal">
                        <i class="fa-solid fa-user-plus me-1"></i> Add Patient
                    </button>
                </div>
                <div class="card-body p-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover w-100" id="patient_masterlist_queue_table">
                            <thead class="table-secondary">
                                <tr>
                                    <th scope="col" style="width: 140px;" class="text-center">Action</th>
                                    <th scope="col">Patient Name <span class="text-secondary">(Last, First, Middle, Suffix)</span></th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.secretary_profile')
    @include('modals.secretary_management')
    @include('modals.reschedule_modal')
    @include('modals.add_patient')
    @include('modals.patient_masterlist')
    @include('modals.edit_group_management')
    @include('modals.edit_services_management')
    @include('modals.view_patient')
    @include('modals.edit_patient')
    @include('modals.append_charges')
    @include('modals.take_photo')
    @include('modals.settlement_modal')
    @include('modals.advance_details')
    <!-- Detailed Comment: Dedicated Consultation Details Viewer Modal with vertical tabs per user requirement -->
    @include('modals.view_consultation_details')
    <!-- Detailed Comment: Reusable Administrator Verification Modal for protected actions (queue deletion, masterlist deletion, fee editing/deletion) -->
    @include('modals.admin_verification_modal')
@endpush

@if (session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            Swal.fire({
                icon: 'success',
                title: 'Success',
                text: "{{ session('success') }}",
                confirmButtonColor: '#3085d6'
            });
        });
    </script>
@endif