@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/philhealth/yakap-management.js')
    @vite('resources/js/pages/admin/philhealth/profile-validation-codes.js')
@endpush

@section('content')
    <div class="card p-3">
        <h1 class="m-0">Yakap Management</h1>

        <hr>

        <div>
            <div class="d-none" id="advance-search">
                <div class="row align-items-end mb-3">
                    <div class="col-2">
                        <label class="form-label" for="pin">Philhealth ID No.</label>
                        <input class="form-control" type="text" name="pin" id="pin">
                    </div>
                </div>
                <div class="row align-items-end mb-3">
                    <div class="col-2">
                        <label class="form-label" for="last_name">Last Name</label>
                        <input class="form-control" type="text" name="last_name" id="last_name">
                    </div>

                    <div class="col-2">
                        <label class="form-label" for="first_name">First Name</label>
                        <input class="form-control" type="text" name="first_name" id="first_name">
                    </div>

                    <div class="col-2">
                        <label class="form-label" for="middle_name">Middle Name</label>
                        <input class="form-control" type="text" name="middle_name" id="middle_name">
                    </div>

                    <div class="col-1">
                        <label class="form-label" for="extension">Extension</label>
                        <input class="form-control" type="text" name="extension" id="extension">
                    </div>

                    <div class="col-2">
                        <label class="form-label" for="dob">Date of birth</label>
                        <input class="form-control" type="text" name="dob" id="dob">
                    </div>
                </div>
            </div>

            <div class="row align-items-end mb-3">
                <div class="col-4">
                    <label class="form-label" for="name">Name</label>
                    <input class="form-control" style="text-transform: uppercase;" type="text" name="name"
                        id="name">
                </div>

                <div class="col-2">
                    <label for="effective-year">Effective Year</label>
                    <select class="form-control" id="effective-year" name="effective-year">
                        <option value="" selected>All</option>
                        @foreach ($effectiveYears as $year)
                            <option value="{{ $year }}">{{ $year }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-2">
                    <button type="button" class="btn btn-primary" id="enlistment_search">Search</button>
                    <button type="button" class="btn btn-primary" id="advanced_search_btn"><i
                            class="fas fa-gear"></i></button>
                </div>
            </div>

            <div>
                <div class="table-responsive">
                    <table class="table table-bordered" id="enlistment_table">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">PIN</th>
                                <th scope="col">Last Name</th>
                                <th scope="col">First Name</th>
                                <th scope="col">Middle Name</th>
                                <th scope="col">Extension</th>
                                <th scope="col">Client Type</th>
                                <th scope="col">Date of Birth</th>
                                <th scope="col">Effectivity Year</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td colspan="8" class="text-center">Search to show items</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="pagination_container"></div>

            <hr>

            <div class="d-none" id="client_record_accordion">
                @include('components.yakap-management.patient-details')

                <h5 class="bg-success text-white p-2">List of Health Screening & Assessment</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="profiles-table">
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col">Transaction No.</th>
                                <th scope="col">Transaction Date</th>
                                <th scope="col">PIN</th>
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


                <div class="d-flex justify-content-center align-items-center" id="add-profile-btn-container">
                </div>

                <hr>

                <h5 class="bg-success text-white p-2">Consultation</h5>
                <div class="table-responsive">
                    <table class="table table-bordered" id="consultation-table">
                        <thead class="table-secondary">
                            <tr>
                                <th scope="col">Transaction No.</th>
                                <th scope="col">Consultation Date</th>
                                <th scope="col">PIN</th>
                                <th scope="col">Reports</th>
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


                <div class="d-flex justify-content-center align-items-center">
                    <button class="btn btn-sm btn-primary" id="addNewConsultation">Add New Consultation</button>
                </div>
            </div>
        </div>

    </div>
@endsection

@push('modals')
    @include('modals.yakap-management.profile-details')
    @include('modals.yakap-management.soap-details')
    @include('modals.link-patient-modal')
    @include('modals.link-consultation-modal')
@endpush
