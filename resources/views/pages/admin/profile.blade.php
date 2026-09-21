@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/profile.js')
@endpush

@section('content')
    <div class="card p-3" id="admin_page">
        <h1 class="m-0">Profile</h1>
        <hr>

        <form class="d-flex flex-column gap-3" id="profile_form">
            @csrf
            <div class="d-flex w-100 gap-4">
                <div class="d-flex flex-column gap-3 w-75">
                    <div class="w-auto">
                        <label class="form-label fw-bold" for="comp_name">Company Name<span
                                class="text-danger">*</span></label>
                        <input class="form-control form-control-sm" type="text" name="comp_name" id="comp_name" required>
                        <div class="form-text">Enter business or company name.</div>
                    </div>

                    <div class="d-flex w-auto gap-2">
                        <div class="w-auto">
                            <label class="form-label fw-bold" for="comp_tel">Contact Number</label>
                            <input class="form-control form-control-sm" type="tel" name="comp_tel" id="comp_tel">
                        </div>

                        <div class="w-auto">
                            <label class="form-label fw-bold" for="comp_email">Email Address</label>
                            <input class="form-control form-control-sm" type="email" name="comp_email" id="comp_email">
                        </div>
                    </div>

                    <!-- Detailed Comment: Company Address with PSGC Reference Tables Cascading Integration -->
                    <div class="d-flex flex-column gap-2">
                        <label class="form-label fw-bold mb-0">Company Address</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1" for="phregion">Region</label>
                                <select class="form-select form-select-sm" name="phregion" id="phregion">
                                    <option value="" selected disabled>-- Select Region --</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small text-muted mb-1" for="phprov">Province</label>
                                <select class="form-select form-select-sm" name="phprov" id="phprov" disabled>
                                    <option value="" selected disabled>-- Select Province --</option>
                                </select>
                            </div>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-5">
                                <label class="form-label small text-muted mb-1" for="phmun">City / Municipality</label>
                                <select class="form-select form-select-sm" name="phmun" id="phmun" disabled>
                                    <option value="" selected disabled>-- Select Municipality --</option>
                                </select>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label small text-muted mb-1" for="phbrgy">Barangay</label>
                                <select class="form-select form-select-sm" name="phbrgy" id="phbrgy" disabled>
                                    <option value="" selected disabled>-- Select Barangay --</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label small text-muted mb-1" for="phzipcode">ZIP Code</label>
                                <input class="form-control form-control-sm" type="text" name="phzipcode" id="phzipcode"
                                    placeholder="ZIP Code">
                            </div>
                        </div>
                    </div>
                </div>


                <div class="d-flex flex-column">
                    <div>
                        <label class="form-label fw-bold" for="comp_logo">Company Logo</label>
                        <input class="d-none" type="file" name="comp_logo" id="comp_logo" accept=".png,.jpg,.jpeg">
                        <div class="d-flex flex-column gap-2 align-items-center">
                            <img class="rounded-circle"
                                style="width: 10rem; max-width: 10rem; height: 10rem; max-height: 10rem;"
                                src="{{ asset('images/company_logo.png') }}" alt="company_logo" id="company_logo_display">
                            <button type="button" class="btn btn-sm btn-secondary" id="upload_logo_btn">
                                <i class="fa-solid fa-arrow-up-from-bracket"></i> Upload Logo
                            </button>
                            <div class="form-text">Image file size must be 2mb or lower.</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_updates">Update Company
                    Profile</button>
            </div>
        </form>

        <h1 class="m-0">PhilHealth Account</h1>
        <hr>

        <form action="" id="philhealth_account_form">
            @csrf
            <div class="d-flex flex-column w-50 gap-4">
                <div class="w-auto">
                    <label class="form-label fw-bold" for="ph_username">HCI User ID</label>
                    <div class="input-group">
                        <input class="form-control form-control-sm" type="text" name="ph_username" id="ph_username">
                        <input class="form-control form-control-sm" type="password" name="ph_password" id="ph_password"
                            placeholder="Password">
                        <button type="button" class="btn btn-sm btn-secondary" id="view_password">
                            <i class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                    <div class="form-text">ID provided by PhilHealth</div>
                </div>

                <div class="w-50">
                    <label class="form-label fw-bold" for="ph_accreno">HCI Accreditation No.</label>
                    <input class="form-control form-control-sm" type="text" name="ph_accreno" id="ph_accreno">
                </div>

                <div>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_comp_credentials">Save
                        Credentials</button>
                </div>
            </div>
        </form>

        <!-- Detailed Comment: Administrator Account section allowing logged in admin to manage credentials and username from adminrights -->
        <div class="d-flex justify-content-between align-items-center mt-5">
            <h1 class="m-0">Administrator Account</h1>
        </div>
        <hr>

        @php
            $adminUser = auth()->guard('admin')->user();
        @endphp
        <form id="admin_account_form" class="d-flex flex-column w-50 gap-3 mb-4">
            @csrf
            <input type="hidden" name="id" value="{{ $adminUser ? $adminUser->id : '' }}">
            <input type="hidden" name="adminrefno" value="{{ $adminUser ? $adminUser->adminrefno : '' }}">

            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <label class="form-label fw-bold" for="adm_fname">First Name <span class="text-danger">*</span></label>
                    <input class="form-control form-control-sm" type="text" name="adminfname" id="adm_fname"
                        value="{{ $adminUser ? $adminUser->adminfname : '' }}" required>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label fw-bold" for="adm_mname">Middle Name</label>
                    <input class="form-control form-control-sm" type="text" name="adminmname" id="adm_mname"
                        value="{{ $adminUser ? $adminUser->adminmname : '' }}">
                </div>
                <div class="flex-grow-1">
                    <label class="form-label fw-bold" for="adm_lname">Last Name <span class="text-danger">*</span></label>
                    <input class="form-control form-control-sm" type="text" name="adminlname" id="adm_lname"
                        value="{{ $adminUser ? $adminUser->adminlname : '' }}" required>
                </div>
            </div>

            <div class="d-flex gap-2">
                <div class="flex-grow-1">
                    <label class="form-label fw-bold" for="adm_username">Username <span class="text-danger">*</span></label>
                    <input class="form-control form-control-sm" type="text" name="username" id="adm_username"
                        value="{{ $adminUser ? $adminUser->username : '' }}" required>
                </div>
                <div class="flex-grow-1">
                    <label class="form-label fw-bold" for="adm_contact">Contact #</label>
                    <input class="form-control form-control-sm" type="tel" name="admincontactno" id="adm_contact"
                        value="{{ $adminUser ? $adminUser->admincontactno : '' }}">
                </div>
            </div>

            <div>
                <label class="form-label fw-bold" for="adm_email">Email Address</label>
                <input class="form-control form-control-sm" type="email" name="adminemail" id="adm_email"
                    value="{{ $adminUser ? ($adminUser->adminemail ?: $adminUser->useremail) : '' }}">
            </div>

            <div>
                <label class="form-label fw-bold" for="adm_password">New Password <span
                        class="text-secondary fw-normal">(Leave blank to keep current)</span></label>
                <input class="form-control form-control-sm" type="password" name="password" id="adm_password"
                    placeholder="Leave blank to keep unchanged">
            </div>

            <div>
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_admin_account">Update Administrator
                    Account</button>
            </div>
        </form>
    </div>
@endsection