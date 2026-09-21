@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/users/secretaries.js')
@endpush

@section('content')
    <div class="card p-3">
        <div id="users-management">
            <!-- Detailed Comment: Rebranded header from Secretaries to Secretaries/Admin Users -->
            <div class="d-flex justify-content-between align-items-center mb-2">
                <h1 class="m-0">Secretaries/Admin Users</h1>
            </div>
            <hr>

            <!-- Nav tabs for switching between Add Secretary and Add Admin forms -->
            <ul class="nav nav-pills mb-3" id="userFormTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="tab-add-secretary" data-bs-toggle="pill"
                        data-bs-target="#pane-add-secretary" type="button" role="tab" aria-selected="true">
                        <i class="fa-solid fa-user-nurse me-1"></i> Add Secretary
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="tab-add-admin" data-bs-toggle="pill"
                        data-bs-target="#pane-add-admin" type="button" role="tab" aria-selected="false">
                        <i class="fa-solid fa-user-shield me-1"></i> Add Admin User
                    </button>
                </li>
            </ul>

            <div class="tab-content border rounded p-3 bg-light bg-opacity-25" id="userFormTabsContent">
                <!-- Add Secretary Form Pane -->
                <div class="tab-pane fade show active" id="pane-add-secretary" role="tabpanel">
                    <form class="d-flex flex-column gap-3 w-100" action="{{ route('admin.add_secretary') }}" method="post"
                        id="add_secretary_form">
                        @csrf
                        <div class="d-flex gap-2">
                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secfname">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="secfname" id="secfname" required>
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secmname">Middle Name</label>
                                <input class="form-control" type="text" name="secmname" id="secmname">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="seclname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="seclname" id="seclname" required>
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secsuffix">Suffix</label>
                                <input class="form-control" type="text" name="secsuffix" id="secsuffix" maxlength="10">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secgender">Sex <span
                                        class="text-danger">*</span></label>
                                <select class="form-select" name="secgender" id="secgender">
                                    <option value="male">MALE</option>
                                    <option value="female">FEMALE</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secusername">Username <span
                                        class="text-secondary fw-normal">(Defaults to Last Name)</span></label>
                                <input class="form-control" type="text" name="username" id="secusername"
                                    placeholder="Defaults to lowercase last name">
                            </div>

                            <div class="d-flex flex-column">
                                <label class="form-label fw-bold" for="secbday">Birthdate</label>
                                <input class="form-control" type="date" name="secbday" id="secbday">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="seccontactno">Contact #</label>
                                <input class="form-control" type="tel" name="seccontactno" id="seccontactno"
                                    placeholder="(+63)">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="secemail">Email Address</label>
                                <input class="form-control" type="email" name="secemail" id="secemail">
                            </div>
                        </div>

                        <!-- Detailed Comment: Integrated PSGC Geographic Address Cascade (Region, Province, Municipality, Barangay, Zipcode) -->
                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="sec_region">Region</label>
                                <select class="form-select" id="sec_region" name="sec_region">
                                    <option value="" selected disabled>-- Select Region --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="sec_prov">Province</label>
                                <select class="form-select" id="sec_prov" name="sec_prov" disabled>
                                    <option value="" selected disabled>-- Select Province --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="sec_mun">City / Municipality</label>
                                <select class="form-select" id="sec_mun" name="sec_mun" disabled>
                                    <option value="" selected disabled>-- Select Municipality --</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="sec_brgy">Barangay</label>
                                <select class="form-select" id="sec_brgy" name="sec_brgy" disabled>
                                    <option value="" selected disabled>-- Select Barangay --</option>
                                </select>
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="sec_street">Street / House No.</label>
                                <input class="form-control" type="text" id="sec_street" name="sec_street"
                                    placeholder="e.g. 123 Rizal St.">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label fw-bold" for="sec_zipcode">Zip Code</label>
                                <input class="form-control" type="text" id="sec_zipcode" name="sec_zipcode"
                                    placeholder="Zip Code" disabled>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold" for="secadrs">Compiled Address</label>
                                <input class="form-control" type="text" name="secadrs" id="secadrs"
                                    placeholder="Full address will be compiled here">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold" for="secpassword">Default Password <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="secpassword" id="secpassword" value="12345"
                                    required>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end">
                            <button class="btn btn-success fw-bold" type="button" id="add_secretary_btn">
                                <i class="fa-solid fa-plus"></i> Add Secretary
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Add Admin User Form Pane -->
                <div class="tab-pane fade" id="pane-add-admin" role="tabpanel">
                    <form class="d-flex flex-column gap-3 w-100" action="{{ route('admin.add_admin') }}" method="post"
                        id="add_admin_form">
                        @csrf
                        <div class="d-flex gap-2">
                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="adminfname">First Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="adminfname" id="adminfname" required>
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="adminmname">Middle Name</label>
                                <input class="form-control" type="text" name="adminmname" id="adminmname">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="adminlname">Last Name <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="adminlname" id="adminlname" required>
                            </div>
                        </div>

                        <div class="d-flex gap-2">
                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="adminusername">Username <span
                                        class="text-secondary fw-normal">(Defaults to Last Name)</span></label>
                                <input class="form-control" type="text" name="username" id="adminusername"
                                    placeholder="Defaults to lowercase last name">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="admincontactno">Contact #</label>
                                <input class="form-control" type="tel" name="admincontactno" id="admincontactno"
                                    placeholder="(+63)">
                            </div>

                            <div class="d-flex flex-column flex-grow-1">
                                <label class="form-label fw-bold" for="adminemail">Email Address</label>
                                <input class="form-control" type="email" name="adminemail" id="adminemail">
                            </div>

                            <div class="d-flex flex-column" style="min-width: 250px;">
                                <label class="form-label fw-bold" for="adminpassword">Default Password <span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="password" id="adminpassword" value="12345"
                                    required>
                            </div>

                            <div class="d-flex align-items-end">
                                <button class="btn btn-primary fw-bold" type="button" id="add_admin_btn">
                                    <i class="fa-solid fa-plus"></i> Add Admin User
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <hr class="my-4">

            <!-- Unified Table of Secretaries and Admin Users with Account Type filter dropdown in column header -->
            <div class="table-responsive">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h5 class="m-0 fw-bold text-secondary"><i class="fa-solid fa-users-gear me-1"></i> Users Masterlist</h5>
                </div>

                <table class="table table-sm table-bordered align-middle caption-top w-100" id="secretary_table">
                    <caption>List of Secretaries and Admin Users</caption>
                    <thead class="table-success">
                        <tr>
                            <!-- Detailed Comment: Expanded Actions column width to comfortably fit labeled buttons (Edit, Assigned Doctors, Delete) -->
                            <th scope="col" style="min-width: 290px; width: 300px;" class="align-middle text-center">Actions</th>
                            <th scope="col" id="th_sec_account_type" style="min-width: 170px;" class="align-middle">Account Type</th>
                            <th scope="col" id="th_sec_source_table" style="min-width: 150px;" class="align-middle">Source Table</th>
                            <th scope="col" id="th_sec_username" style="min-width: 140px;" class="align-middle">Username</th>
                            <th scope="col" id="th_sec_fullname" style="min-width: 220px;" class="align-middle">Full Name <span class="text-secondary small">(Last, First, Middle, Suffix)</span></th>
                            <th scope="col" id="th_sec_contact" style="min-width: 140px;" class="align-middle">Contact #</th>
                            <th scope="col" id="th_sec_email" style="min-width: 180px;" class="align-middle">Email Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
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
    @include('modals.assigned_doctors')
    @include('modals.edit_secretary')
    @include('modals.edit_admin')
@endpush