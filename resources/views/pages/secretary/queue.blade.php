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

    <div class="d-flex gap-1 h-100">
        <!-- Left Column -->
        <div class="h-100" style="min-width: 45rem;">
            <div class="m-0 pb-5 h-100 d-flex flex-column gap-2 overflow-auto">
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
                                        <th scope="col">#</th>
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

                <div class="card d-flex flex-column shadow-sm">
                    <div class="card-header fw-semibold bg-secondary text-white">
                        <i class="fa-solid fa-address-book"></i> New/Unscheduled Patients
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered table-hover" id="patients_unsched_table">
                                <thead class="table-secondary">
                                    <tr>
                                        <th scope="col">Actions</th>
                                        <th scope="col">Patient Name <span class="text-secondary">(Last, First, Middle,
                                                Suffix)</span></th>
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
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="h-100 d-flex flex-column p-0">
            <form class="h-100" id="consultation_form">
                @csrf

                <div class="card shadow-sm d-flex flex-column" style="height: 90vh;">
                    <div class="card-header">
                        <!-- Topbar Buttons -->
                        <div class="d-flex gap-1">
                            <button type="button" class="btn btn-sm btn-success fw-bold" id="add_new_patient_btn">
                                <i class="fa-solid fa-user-plus"></i> Add New Patient Record
                            </button>

                            <button type="button" class="btn btn-sm btn-secondary fw-bold" id="view_masterlist_btn">
                                <i class="fa-solid fa-bars"></i> Patient Masterlist
                            </button>

                            <button type="button" class="btn btn-sm btn-danger fw-bold text-nowrap ms-auto" id="clear_form">
                                <i class="fa-solid fa-trash-can"></i> Clear Consultation Form
                            </button>
                        </div>
                    </div>

                    <div class="card-body d-flex flex-column gap-2 flex-grow-1 overflow-auto">
                        <!-- Patient Information -->
                        <div class="d-flex gap-3">
                            <div class="d-flex flex-column gap-2 w-100">
                                <div class="d-flex flex-column gap-0 ms-1">
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
                                        Consultation Details
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button type="button" class="nav-link" data-bs-toggle="tab"
                                        data-bs-target="#payment_info" role="tab" aria-controls="payment_info"
                                        aria-selected="false" id="patient_charges_btn">
                                        Payment Details
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
                                                <input type="date" class="form-control form-control-sm" name="sched_date"
                                                    id="sched_date">
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

                                <div class="tab-pane" id="payment_info" role="tabpanel">
                                    <div class="d-flex flex-column gap-1">
                                        <div class="w-100 d-flex justify-content-end">
                                            <button type="button" class="btn btn-sm btn-warning text-white fw-bold"
                                                id="append_pxcharges_btn">
                                                <i class=""></i> Append Charges
                                            </button>
                                        </div>

                                        <div class="d-flex flex-column gap-2">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-bordered caption-top"
                                                    id="pxcharges_table">
                                                    <caption>List of charges</caption>
                                                    <thead class="table-warning">
                                                        <tr>
                                                            <th scope="col">Actions</th>
                                                            <th scope="col">Description</th>
                                                            <th scope="col">Quantity</th>
                                                            <th scope="col">Amount</th>
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

                                            <!-- Detailed Comment: Position charges total element cleanly below table beside Settlements button -->
                                            <div class="d-flex justify-content-between align-items-center mt-2">
                                                <h4 class="fw-bold m-0">Total: ₱<span class="fw-normal ms-1" id="charges_total">0.00</span></h4>
                                                <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                                    data-bs-target="#settlementModal" id="settlement_btn">
                                                    <i class="fa-solid fa-credit-card"></i> Settlements
                                                </button>
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