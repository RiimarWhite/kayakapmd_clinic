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
                    <label class="form-label fw-bold" for="comp_name">Company Name<span class="text-danger">*</span></label>
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

                <div class="d-flex flex-column">
                    <label class="form-label fw-bold" for="address">Company Address</label>
                    <div class="input-group">
                        <select class="form-select form-select-sm" name="phregion" id="phregion">
                            <option value="0" selected disabled>-- Select Region --</option>
                        </select>
                        <select class="form-select form-select-sm" name="phprov" id="phprov" disabled>
                            <option value="0" selected disabled>-- Select Province --</option>
                        </select>
                        <select class="form-select form-select-sm" name="phmun" id="phmun" disabled>
                            <option value="0" selected disabled>-- Select Municipality --</option>
                        </select>
                        <select class="form-select form-select-sm" name="phbrgy" id="phbrgy" disabled>
                            <option value="0" selected disabled>-- Select Barangay --</option>
                        </select>
                        <input class="form-control form-control-sm" type="text" name="phzipcode" id="phzipcode" placeholder="ZIP Code" disabled>
                    </div>
                </div>
            </div>


            <div class="d-flex flex-column">
                <div>
                    <label class="form-label fw-bold" for="comp_logo">Company Logo</label>
                    <input class="d-none" type="file" name="comp_logo" id="comp_logo" accept=".png,.jpg,.jpeg">
                    <div class="d-flex flex-column gap-2 align-items-center">
                        <img class="rounded-circle" style="width: 10rem; max-width: 10rem; height: 10rem; max-height: 10rem;"
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
            <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_updates">Update Company Profile</button>
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
                    <input class="form-control form-control-sm" type="password" name="ph_password" id="ph_password" placeholder="Password">
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
                <button type="button" class="btn btn-sm btn-primary fw-bold" id="save_comp_credentials">Save Credentials</button>
            </div>
        </div>
    </form>
</div>
@endsection
