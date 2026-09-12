@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/philhealth/reports.js')
    @vite('resources/js/pages/admin/philhealth/philhealth-api.js')
@endpush

@section('content')
    <div class="card h-100 p-3">
        <div class="d-flex justify-content-between align-middle align-items-center">
            <h1 class="m-0">Tools & Transmittal</h1>
            <button class="btn btn-outline-success">
                <i class="fa-solid fa-check"></i>
                Token Valid
            </button>

            <!-- <button class="btn btn-danger">
                <i class="fa-solid fa-xmark"></i>
                Token Expired
            </button> -->
        </div>
        <hr>

        <div class="d-flex gap-4">
            <div class="d-flex flex-fill card shadow-sm">
                <div class="card-header">
                    <h5 class="my-0">Check Member/Dependent</h5>
                </div>
                <div class="card-body">
                    <form class="input-group">
                        @csrf

                        <input class="form-control rounded-start" type="text" name="check-pin" id="check-pin" placeholder="PIN" required>
                        <select class="form-select" name="check-type" id="check-type" required>
                            <option selected disabled>-- Select Type --</option>
                            <option value="MM">Member</option>
                            <option value="DD">Dependent</option>
                        </select>
                    </form>
                    <div class="form-text">Check whether the member/dependent has already been registered.</div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button class="btn btn-sm btn-primary" id="btn-check-md">Submit</button>
                </div>
            </div>

            <div class="d-flex flex-fill card shadow-sm">
                <div class="card-header">
                    <h5 class="my-0">Check ATC Validity</h5>
                </div>
                <div class="card-body">
                    <form class="input-group">
                        @csrf

                        <input class="form-control rounded-start" type="text" name="atc-pin" id="atc-pin" placeholder="PIN" required>
                        <input class="form-control" type="text" name="atc" id="atc" placeholder="ATC" required>
                        <input class="form-control" type="date" name="atc-date" id="atc-date" required>
                    </form>
                    <div class="form-text">Validate ATC provided by the member/dependent.</div>
                </div>
                <div class="card-footer d-flex justify-content-end">
                    <button class="btn btn-sm btn-primary" id="btn-validate-atc">Submit</button>
                </div>
            </div>
        </div>

        <!-- <hr>

        <h3>XML Report Generation</h3>

        <div class="d-flex justify-content-between my-2">
            <div class="d-flex flex-column">
                <div class="input-group">
                    <span class="input-group-text">Date Covered:</span>
                    <input class="form-control" type="date" name="xml_start" id="xml_start">
                    <span class="input-group-text">To</span>
                    <input class="form-control" type="date" name="xml_end" id="xml_end">
                </div>
                <div class="form-text">The date range for which to base XML reports.</div>
            </div>

            <div class="d-flex gap-2">
                <div class="d-flex flex-column">
                    <div class="input-group">
                        <select class="form-select" name="xml_format" id="xml_format">
                            <option value="" disabled selected>-- Select --</option>
                            <option value="first">First Tranche</option>
                            <option value="second">Second Tranche</option>
                        </select>
                        <button class="btn btn-sm btn-primary" id="btn_generate_xml_report">
                            <i class="fa-solid fa-file-lines"></i> Generate XML Report
                        </button>
                    </div>
                    <div class="form-text">Select tranche type for XML generation.</div>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered caption-top" id="patients_report_table">
                <caption>List of patients marked as consulted</caption>
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">
                            <input class="form-check-input" type="checkbox" name="xml_select_all" id="xml_select_all" title="Select all from the list.">
                        </th>
                        <th scope="col"></th>
                        <th scope="col">Patient Name <span class="text-secondary">(Last, First Middle)</span></th>
                        <th scope="col">Consultation Date</th>
                        <th scope="col">Report <span class="text-secondary">(Tranche 1)</span></th>
                        <th scope="col">Report <span class="text-secondary">(Tranche 2)</span></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div> -->

        <hr>

        <h3>Reports Generation</h3>

        <div class="d-flex justify-content-between my-2">
            <div class="d-flex flex-column">
                <div class="input-group">
                    <span class="input-group-text">Date Covered</span>
                    <input class="form-control" type="date" name="xml-start" id="xml-start">
                    <span class="input-group-text">To</span>
                    <input class="form-control" type="date" name="xml-end" id="xml-end">
                </div>
                <div class="form-text">The date range basis for the consultations.</div>
            </div>
            <div class="d-flex flex-column">
                <div class="input-group">
                    <select class="form-select" name="xml-tranche" id="xml-tranche" required>
                        <option value="" selected disabled>-- Select --</option>
                        <option value="first">Tranche 1</option>
                        <option value="second">Tranche 2</option>
                    </select>
                    <button class="btn btn-primary" id="btn-generate-xml-report">
                        <i class="fa-solid fa-file-lines"></i> Generate XML Report
                    </button>
                </div>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-sm table-bordered" id="patients-report-table">
                <thead class="table-secondary">
                    <tr>
                        <th scope="col">
                            <input class="form-check-input" type="checkbox" name="xml-select-all" id="xml-select-all">
                        </th>
                        <th scope="col"></th>
                        <th scope="col">Patient Name <span class="text-secondary">(Last, First, Middle)</span></th>
                        <th scope="col">Consultation Date</th>
                        <th scope="col">Report <span class="text-secondary">(Tranche 1)</span></th>
                        <th scope="col">Report <span class="text-secondary">(Tranche 2)</span></th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.generate_xml')
@endpush
