<!DOCTYPE html>
<html class="vh-100">

    <head>
        <link rel="icon" type="image/png" href="favicon.ico">
        <title>{{ config('app.name', 'Laravel') }} | Admin</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <style>
            @font-face {
                font-family: 'MyFont';
                src: url({{ asset('baru_sans/TTF/BaruSansDemo-Light.ttf') }});
                font-style: normal;
            }

            #admin_sidebar.nav .active {
                background-color: white !important;
                font-weight: bold;
            }

            #admin_sidebar.nav button:hover {
                background-color: white !important;
            }

            #management_sidebar.nav .active {
                background-color: #e9ecef !important;
                font-weight: bold;
            }

            #patients_queue_table::-webkit-scrollbar {
                width: 6px;
            }

            #patients_queue_table::-webkit-scrollbar-thumb {
                background-color: #cbd5e0;
                border-radius: 10px;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>

    <body class="d-flex flex-column h-100 overflow-hidden" id="admin_page" style="font-family: MyFont;">

        @include('template.navbar')

        <div class="d-flex h-100">
            <ul class="nav nav-pills bg-body-secondary flex-column w-25 p-2 gap-2" id="admin_sidebar">
                <li class="nav-item">
                    <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                        data-bs-target="#company_management" role="tab" aria-controls="company_management"
                        aria-selected="true">
                        <i class="fa-solid fa-building"></i>
                        Dashboard
                    </button>
                </li>

                <li class="nav-item">
                    <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
                        data-bs-toggle="collapse" data-bs-target="#diagnosticsBtn" type="button"
                        aria-controls="diagnosticsBtn" aria-expanded="false">
                        <i class="fa-solid fa-x-ray"></i>
                        Diagnostics Management
                        <i class="fa-solid fa-caret-down ms-auto"></i>
                    </button>

                    <div class="collapse" id="diagnosticsBtn">
                        <div class="card card-body bg-transparent border-0 p-0 flex-column">
                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#diagnostics_masterlist" role="tab"
                                aria-controls="diagnostics_masterlist" aria-selected="true"
                                id="diagnostics_masterlist_tab">
                                Diagnostic Requests
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#diagnostics_category" role="tab"
                                aria-controls="diagnostics_category" aria-selected="true" id="diagnostics_category_tab">
                                Diagnostics Category
                            </button>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab"
                        data-bs-target="#medicine_masterlist" role="tab" aria-controls="medicine_masterlist"
                        aria-selected="true" id="medicine_masterlist_btn">
                        <i class="fa-solid fa-prescription-bottle"></i>
                        Medicine Masterlist
                    </button>
                </li>

                <li class="nav-item">
                    <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
                        data-bs-toggle="collapse" data-bs-target="#chargesBtn" type="button" aria-controls="chargesBtn"
                        aria-expanded="false">
                        <i class="fa-solid fa-coins"></i>
                        Charges Management
                        <i class="fa-solid fa-caret-down ms-auto"></i>
                    </button>

                    <div class="collapse" id="chargesBtn">
                        <div class="card card-body bg-transparent border-0 p-0 flex-column">
                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#charges_masterlist" role="tab"
                                aria-controls="charges_masterlist" aria-selected="true" id="charges_masterlist_tab">
                                Charges Masterlist
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#charges_category" role="tab"
                                aria-controls="charges_category" aria-selected="true" id="charges_category_tab">
                                Charges Category
                            </button>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
                        data-bs-toggle="collapse" data-bs-target="#philHealthBtn" type="button"
                        aria-controls="philHealthBtn" aria-expanded="false">
                        <i class="fa-solid fa-stethoscope"></i>
                        PhilHealth Yakap
                        <i class="fa-solid fa-caret-down ms-auto"></i>
                    </button>

                    <div class="collapse" id="philHealthBtn">
                        <div class="card card-body bg-transparent border-0 p-0 flex-column">
                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#philhealth_overview" role="tab"
                                aria-controls="philhealth_overview" aria-selected="true"
                                id="philhealth_overview_btn">
                                Dashboard
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#philhealth_overview" role="tab"
                                aria-controls="philhealth_overview" aria-selected="true"
                                id="philhealth_overview_btn">
                                Management
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#reports" role="tab"
                                aria-controls="philhealth_overview" aria-selected="true"
                                id="philhealth_overview_btn">
                                Reports
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#philhealth_overview" role="tab"
                                aria-controls="philhealth_overview" aria-selected="true"
                                id="philhealth_overview_btn">
                                Uploading
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#philhealth_overview" role="tab"
                                aria-controls="philhealth_overview" aria-selected="true"
                                id="philhealth_overview_btn">
                                Tools & Utility
                            </button>
                        </div>
                    </div>
                </li>

                <li class="nav-item">
                    <button class="nav-link active w-100 text-start text-dark" data-bs-toggle="tab"
                        data-bs-target="#company_management" role="tab" aria-controls="company_management"
                        aria-selected="true">
                        <i class="fa-solid fa-building"></i>
                        Profile
                    </button>
                </li>

                <li class="nav-item">
                    <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
                        data-bs-toggle="collapse" data-bs-target="#usersBtn" type="button" aria-controls="usersBtn"
                        aria-expanded="false">
                        <i class="fa-solid fa-gear"></i>
                        Users Management
                        <i class="fa-solid fa-caret-down ms-auto"></i>
                    </button>

                    <div class="collapse" id="usersBtn">
                        <div class="card card-body bg-transparent border-0 p-0 flex-column">
                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#secretary_management" role="tab"
                                aria-controls="secretary_management" aria-selected="true" id="add_secretary_tab">
                                Secretaries
                            </button>

                            <button class="nav-link text-start text-dark" style="font-size: 14px; padding-left: 3rem;"
                                data-bs-toggle="tab" data-bs-target="#doctor_management" role="tab"
                                aria-controls="doctor_management" aria-selected="true" id="add_doctors_tab">
                                Doctors
                            </button>
                        </div>
                    </div>
                </li>

                <!-- <li class="nav-item">
                <button class="nav-link w-100 text-start text-dark" data-bs-toggle="tab" data-bs-target="#reports"
                    role="tab" aria-controls="reports" aria-selected="false" id="reports-tab">
                    <i class="fa-solid fa-chart-pie"></i> Reports
                </button>
            </li> -->
            </ul>

            <div class="tab-content d-flex flex-column w-100">
                <!-- Profile -->
                <div class="tab-pane active w-100 overflow-auto flex-grow-1" id="company_management" role="tabpanel"
                    aria-labelledby="company_management-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column flex-grow-1" style="max-height: 1rem;">
                        <div class="card p-3">
                            <h1 class="ms-2 mb-3">Profile</h1>

                            <hr class="my-4">

                            <form class="d-flex flex-column gap-3" id="profile_form">
                                @csrf

                                <div class="d-flex w-100 gap-4">
                                    <div class="d-flex flex-column gap-3 w-100">
                                        <div class=" w-50">
                                            <label class="form-label fw-bold" for="comp_name">Company Name<span
                                                    class="text-danger">*</span></label>
                                            <input class="form-control" type="text" name="comp_name"
                                                id="comp_name" required>
                                            <div class="form-text">Enter business or company name.</div>
                                        </div>

                                        <div class="w-100">
                                            <label class="form-label fw-bold" for="comp_address">Address</label>
                                            <div class="input-group">
                                                <select class="form-select" name="comp_region" id="comp_region">
                                                    <option value="" selected disabled>-- Select Region --
                                                    </option>
                                                </select>
                                                <select class="form-select" name="comp_prov" id="comp_prov"></select>
                                                <select class="form-select" name="comp_mun" id="comp_mun"></select>
                                                <select class="form-select" name="comp_brgy" id="comp_brgy"></select>
                                            </div>
                                        </div>

                                        <div class="w-25">
                                            <input class="form-control" type="text" name="comp_zipcode"
                                                id="comp_zipcode" placeholder="ZIP Code">
                                        </div>

                                        <div class="d-flex gap-2">
                                            <div>
                                                <label class="form-label fw-bold" for="comp_contact">Contact
                                                    Number</label>
                                                <input class="form-control" type="tel" name="comp_contact"
                                                    id="comp_contact">
                                            </div>

                                            <div>
                                                <label class="form-label fw-bold" for="comp_tel">Telephone
                                                    Number</label>
                                                <input class="form-control" type="tel" name="comp_tel"
                                                    id="comp_tel">
                                            </div>
                                        </div>

                                        <div class="w-50">
                                            <label class="form-label fw-bold" for="comp_email">Email Address</label>
                                            <input class="form-control" type="email" name="comp_email"
                                                id="comp_email">
                                        </div>
                                    </div>

                                    <div class="d-flex flex-column">
                                        <div class="">
                                            <label class="form-label fw-bold" for="comp_logo">Company Logo</label>
                                            <input class="d-none" type="file" name="comp_logo" id="comp_logo"
                                                accept=".png,.jpg,.jpeg">
                                            <div class="d-flex flex-column gap-2 align-items-center">
                                                <img class="rounded-circle" style="max-width: 10rem;"
                                                    src="{{ asset('images/company_logo.png') }}" alt="company_logo"
                                                    id="company_logo_display">
                                                <button type="button" class="btn btn-sm btn-secondary"
                                                    id="upload_logo_btn">
                                                    <i class="fa-solid fa-arrow-up-from-bracket"></i>
                                                    Upload Logo
                                                </button>
                                                <div class="form-text">Image file size must be 2mb or lower.</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <button type="button" class="btn btn-primary fw-bold" id="save_updates">Update
                                        Company Profile</button>
                                </div>
                            </form>

                            <h1 class="mt-5 mb-3">PhilHealth Account</h1>
                            <hr>

                            <form action="" id="philhealth_account_form">
                                @csrf

                                <div class="d-flex flex-column w-75 gap-4">
                                    <div class="w-auto">
                                        <label class="form-label fw-bold" for="ph_username">HCI User ID</label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="ph_username"
                                                id="ph_username">
                                            <input class="form-control" type="password" name="ph_password"
                                                id="ph_password" placeholder="Password">
                                            <button type="button" class="btn btn-secondary" id="view_password">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>
                                        <div class="form-text">ID provided by PhilHealth</div>
                                    </div>

                                    <div class="w-50">
                                        <label class="form-label fw-bold" for="ph_accreno">HCI Accreditation
                                            No.</label>
                                        <input class="form-control" type="text" name="ph_accreno"
                                            id="ph_accreno">
                                    </div>

                                    <div>
                                        <button type="button" class="btn btn-primary fw-bold"
                                            id="save_comp_credentials">Save Credentials</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <!-- User Management -->
                <div class="tab-pane w-100 h-100" id="secretary_management" role="tabpanel"
                    aria-labelledby="secretary_management-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-3">
                            <div id="add-secretary">
                                <h1 class="m-0">Secretary</h1>
                                <hr>
                                <form class="d-flex flex-column gap-3 w-100"
                                    action="{{ route('admin.add_secretary') }}" method="post"
                                    id="add_secretary_form">
                                    @csrf
                                    <div class="d-flex gap-2">
                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secfname">First Name</label>
                                            <input class="form-control" type="text" name="secfname"
                                                id="secfname" required>
                                        </div>

                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secfname">Middle Name</label>
                                            <input class="form-control" type="text" name="secmname"
                                                id="secmname">
                                        </div>

                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secfname">Last Name</label>
                                            <input class="form-control" type="text" name="seclname"
                                                id="seclname" required>
                                        </div>

                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secsuffix">Suffix</label>
                                            <input class="form-control" type="text" name="secsuffix"
                                                id="secsuffix" maxlength="10">
                                        </div>

                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secgender">Sex</label>
                                            <select class="form-select" name="secgender" id="secgender">
                                                <option value="male">MALE</option>
                                                <option value="female">FEMALE</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <div class="">
                                            <label class="form-label fw-bold" for="secbday">Birthdate</label>
                                            <input class="form-control" type="date" name="secbday"
                                                id="secbday">
                                        </div>

                                        <div class="">
                                            <label class="form-label fw-bold" for="seccontactno">Contact #</label>
                                            <input class="form-control" type="tel" name="seccontactno"
                                                id="seccontactno" placeholder="(+63)">
                                        </div>

                                        <div class="">
                                            <label class="form-label fw-bold" for="secemail">Email Address</label>
                                            <input class="form-control" type="email" name="secemail"
                                                id="secemail">
                                        </div>

                                        <div class=" d-flex flex-column flex-grow-1">
                                            <label class="form-label fw-bold" for="secadrs">Address</label>
                                            <input class="form-control" type="text" name="secadrs"
                                                id="secadrs">
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <div class="">
                                            <label class="form-label fw-bold" for="secpassword">Default
                                                Password</label>
                                            <input class="form-control" type="text" name="secpassword"
                                                id="secpassword" required>
                                        </div>

                                        <div class="d-flex align-items-end">
                                            <button class="btn btn-success" type="button" id="add_secretary_btn"><i
                                                    class="fa-solid fa-plus"></i> Add Secretary</button>
                                        </div>
                                    </div>
                                </form>

                                <hr class="my-4">

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle caption-top"
                                        id="secretary_table">
                                        <caption>List of Secretaries</caption>
                                        <thead class="table-success">
                                            <tr>
                                                <th scope="col">Actions</th>
                                                <th scope="col">Full Name <span class="text-secondary">(Last,
                                                        First, Middle,
                                                        Suffix)</span></th>
                                                <th scope="col">Contact #</th>
                                                <th scope="col">Email Address</th>
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
                    </div>
                </div>

                <div class="tab-pane w-100 h-100" id="doctor_management" role="tabpanel"
                    aria-labelledby="doctor_management-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-3">
                            <div id="add-doctor">
                                <h1 class="m-0">Doctors</h1>
                                <hr>

                                <button class="btn btn-sm btn-primary" id="add_doctor_btn"><i
                                        class="fa-solid fa-plus"></i> Add
                                    Doctor</button>

                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered align-middle" id="doctor_table">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Actions</th>
                                                <th>Fullname</th>
                                                <th>Specialization</th>
                                                <th>Status</th>
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
                    </div>
                </div>

                <!-- Diagnostics -->
                <div class="tab-pane w-100 h-100" id="diagnostics_masterlist" role="tabpanel"
                    aria-labelledby="diagnostics_masterlist-tab" tabindex="0">
                    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Diagnostics Requests</h1>

                            <form class="d-flex flex-column gap-2" id="diagnostic_form">
                                @csrf

                                <div class="d-flex gap-2 w-100 align-items-end">
                                    <div class=" w-50 flex-col flex-grow-1">
                                        <label class="form-label fw-bold" for="diagnostic_name">Name</label>
                                        <input class="form-control" type="text" name="diagnostic_name"
                                            id="diagnostic_name" required>
                                    </div>

                                    <div class=" w-50">
                                        <label class="form-label fw-bold" for="diagnostic_catg">Category</label>
                                        <select class="form-select" name="diagnostic_catg" id="diagnostic_catg"
                                            required></select>
                                    </div>

                                    <button type="button" class="btn btn-warning w-50" id="create_diagnostic_btn">
                                        <i class="fa-solid fa-plus"></i> Create Diagnostic Request
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle caption-top" id="diagnostic_table">
                                    <caption>List of Diagnostic Requests</caption>
                                    <thead class="table-warning">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Category</th>
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

                <div class="tab-pane w-100 h-100" id="diagnostics_category" role="tabpanel"
                    aria-labelledby="diagnostics_category-tab" tabindex="0">
                    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Diagnostics Category</h1>

                            <form class="d-flex gap-2" id="charge_form">
                                @csrf

                                <button type="button" class="btn btn-warning" id="create_diagnostic_category_btn">
                                    <i class="fa-solid fa-plus"></i> Create Category
                                </button>
                            </form>

                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle caption-top"
                                    id="diagnostic_category_table">
                                    <caption>Diagnostic Categories</caption>
                                    <thead class="table-warning">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col"> Name</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Medicines -->
                <div class="tab-pane w-100 h-100" id="medicine_masterlist" role="tabpanel"
                    aria-labelledby="medicine_masterlist-tab" tabindex="0">
                    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Medicine Masterlist</h1>

                            <div class="d-flex flex-column gap-3">
                                <form class="d-flex gap-2" id="drug_form">
                                    @csrf

                                    <div class=" w-50">
                                        <label class="form-label fw-bold" for="med_name">Medicine Name</label>
                                        <input class="form-control" type="text" name="med_name" id="med_name"
                                            required>
                                    </div>

                                    <div class=" w-25">
                                        <label class="form-label fw-bold" for="ph_id">PhilHealth ID
                                            Reference</label>
                                        <div class="input-group">
                                            <input class="form-control" type="text" name="ph_id"
                                                id="ph_id">
                                            <button class="btn btn-danger" id="clear_reference"><i
                                                    class="fa-solid fa-circle-xmark"></i></button>
                                        </div>
                                    </div>

                                    <div class="align-content-end w-25">
                                        <button class="btn btn-success" type="button" id="add_med_btn"><i
                                                class="fa-solid fa-plus"></i> Add Medicine</button>
                                    </div>
                                </form>
                            </div>

                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered" id="medicine_table">
                                    <thead class="table-success">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Medicine Name</th>
                                            <th scope="col">ID Reference</th>
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

                <!-- Charges -->
                <div class="tab-pane w-100 h-100" id="charges_masterlist" role="tabpanel"
                    aria-labelledby="charges_masterlist-tab" tabindex="0">
                    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Charges Masterlist</h1>

                            <form class="d-flex flex-column gap-2" id="charge_form">
                                @csrf

                                <div class="d-flex gap-2 w-100 align-items-end">
                                    <div class=" w-auto flex-col flex-grow-1">
                                        <label class="form-label fw-bold" for="charge_name">Name</label>
                                        <input class="form-control" type="text" name="charge_name"
                                            id="charge_name" required>
                                    </div>

                                    <div class=" w-25">
                                        <label class="form-label fw-bold" for="charge_catg">Category</label>
                                        <select class="form-select" name="charge_catg" id="charge_catg"
                                            required></select>
                                    </div>

                                    <div class=" w-25">
                                        <label class="form-label fw-bold" for="charge_amount">Amount</label>
                                        <input class="form-control" type="number" name="charge_amount"
                                            id="charge_amount" placeholder="0.00" step="0.01" required>
                                    </div>
                                </div>

                                <div class="d-flex w-100 justify-content-end gap-2">
                                    <button type="button" class="btn btn-info w-25" id="create_charge_btn">
                                        <i class="fa-solid fa-plus"></i> Create Charge
                                    </button>
                                </div>
                            </form>

                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle caption-top" id="charges_table">
                                    <caption>List of Charges</caption>
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Category</th>
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
                        </div>
                    </div>
                </div>

                <div class="tab-pane w-100 h-100" id="charges_category" role="tabpanel"
                    aria-labelledby="charges_category-tab" tabindex="0">
                    <div class="p-4 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Charges Category</h1>

                            <form class="d-flex gap-2" id="charge_form">
                                @csrf

                                <button type="button" class="btn btn-info" id="create_charge_category_btn">
                                    <i class="fa-solid fa-plus"></i> Create Category
                                </button>
                            </form>

                            <hr class="my-4">

                            <div class="table-responsive">
                                <table class="table table-bordered align-middle caption-top"
                                    id="charges_category_table">
                                    <caption>Charges Category</caption>
                                    <thead class="table-info">
                                        <tr>
                                            <th scope="col">Actions</th>
                                            <th scope="col"> Name</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        <tr>
                                            <td></td>
                                            <td></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PhilHealth Management -->
                <div class="tab-pane w-100 overflow-auto flex-grow-1" id="philhealth_overview" role="tabpanel"
                    aria-labelledby="philhealth_overview-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column flex-grow-1" style="max-height: 1rem;">
                        <div class="card p-3">
                            <h1 class="m-0">Yakap Management</h1>

                            <hr>

                            @include('pages.yakap-management.index')
                        </div>
                    </div>
                </div>

                <div class="tab-pane w-100 h-100" id="philhealth_data" role="tabpanel"
                    aria-labelledby="philhealth_data-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-3">
                            <h1 class="m-0">Patient Consultations Data</h1>
                            <hr>
                            <div class="d-flex justify-content-between gap-2">
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-secondary fw-bold text-nowrap"
                                        title="Export patient data from consultation to patient masterlist"
                                        id="export_to_masterlist"><i class="fa-solid fa-arrow-right-from-bracket"></i>
                                        Export Patient(s) to Masterlist</button>
                                </div>

                                <div class="input-group input-group-sm w-50">
                                    <span class="input-group-text fw-bold">Start Date</span>
                                    <input class="form-control form-control-sm" type="date" name="px_filter_start"
                                        id="px_filter_start">
                                    <span class="input-group-text fw-bold">End Date</span>
                                    <input class="form-control form-control-sm" type="date" name="px_filter_end"
                                        id="px_filter_end">
                                    <button type="button" class="btn btn-sm btn-danger fw-bold"
                                        title="Clear date filters" id="clear_date_filter"><i
                                            class="fa-solid fa-xmark"></i></button>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped" id="phpatients_table">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col"><input type="checkbox" class="form-check-input"
                                                    id="ph-selectall"></th>
                                            <th scope="col">Patient Name <span class="text-secondary">(Last, First
                                                    Middle Suffix)</span></th>
                                            <th scope="col">PIN #</th>
                                            <th scope="col">Case #</th>
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
                </div>

                <div class="tab-pane w-100 h-100" id="patient_masterlist" role="tabpanel"
                    aria-labelledby="patient_masterlist-tab" tabindex="0">
                    <div class="p-2 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-3">
                            <h1 class="m-0">Patient Masterlist</h1>

                            <hr>

                            <div class="table-responsive mt-2">
                                <table class="table table-sm table-bordered" id="patient_masterlist_table">
                                    <thead class="table-info">
                                        <tr>
                                            <th scope="col">Patient Name (Last, First Middle, Suffix)</th>
                                            <th scope="col">PIN No.</th>
                                            <th scope="col">Case No.</th>
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

                <!-- Reports -->
                <div class="tab-pane w-100 h-100" id="reports" role="tabpanel" aria-labelledby="reports-tab"
                    tabindex="0">
                    <div class="p-2 d-flex flex-column h-100 flex-grow-1">
                        <div class="card h-100 p-4">
                            <h1 class="mb-3">Reports</h1>

                            <hr class="my-4">

                            <div class="d-flex gap-4">
                                <button type="button" class="card shadow-sm w-25" data-bs-target="#generateXMLT1"
                                    data-bs-toggle="modal">
                                    <img class="card-img-top" src="{{ asset('images/blank_photo.png') }}"
                                        alt="">
                                    <div class="card-body">
                                        <h4 class="card-title">Generate First Tranche</h4>
                                        <p class="card-text"></p>
                                    </div>
                                </button>

                                <button type="button" class="card shadow-sm w-25" data-bs-target="#generateXMLT1"
                                    data-bs-toggle="modal">
                                    <img class="card-img-top" src="{{ asset('images/blank_photo.png') }}"
                                        alt="">
                                    <div class="card-body">
                                        <h4 class="card-title">Generate Second Tranche</h4>
                                        <p class="card-text"></p>
                                    </div>
                                </button>
                            </div>

                            <!-- <form class="input-group">
                            <select class="form-select w-25" name="docrefno" id="docrefno">
                                <option value="">None</option>
                                @foreach ($doctors as $doctor)
<option value="{{ $doctor->docrefno }}">Dr. {{ $doctor->docname }}</option>
@endforeach
                            </select>

                            <input class="form-control" type="date" name="filter_start" id="filter_start">
                            <input class="form-control" type="date" name="filter_end" id="filter_end">

                            <button type="button" class="btn btn-primary fw-bold" id="print_transactions">
                                <i class="fa-solid fa-print"></i> Print Transactions
                            </button>
                        </form>

                        <div class="table-responsive mt-2">
                            <table class="table table-sm table-bordered" id="transactions_table">
                                <thead class="table-primary">
                                    <tr>
                                        <th scope="col">Patient Name</th>
                                        <th scope="col">Transaction Date</th>
                                        <th scope="col" class="text-center">Cash</th>
                                        <th scope="col" class="text-center">CTA</th>
                                        <th scope="col" class="text-center">--/--</th>
                                        <th scope="col" class="text-center">HMO</th>
                                        <th scope="col" class="text-center">Total</th>
                                    </tr>
                                </thead>

                                <tbody></tbody>
                            </table>
                        </div> -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @include('modals.add_doctor')
        @include('modals.manage_doctor')
        @include('modals.edit_doctor')
        @include('modals.edit_charges')
        @include('modals.assigned_doctors')
        @include('modals.reference_modal')

        @include('modals.generate_xml')

    </body>

</html>
