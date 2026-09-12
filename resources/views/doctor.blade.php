<!DOCTYPE html>

<head>
    <title>{{ config('app.name', 'Laravel') }} | Doctor</title>
    <link rel="icon" type="image/png" href="favicon.ico">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        .ui-autocomplete {
            z-index: 2000 !important;
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.2);
            max-height: 200px;
            overflow-y: auto;
            width: 500px;
            color: black;
        }

        .ui-autocomplete li {
            list-style: none;
            padding: 5px 10px;
            cursor: pointer;
        }

        .ui-helper-hidden-accessible {
            border: 0;
            clip: rect(0 0 0 0);
            height: 1px;
            margin: -1px;
            overflow: hidden;
            padding: 0;
            position: absolute;
            width: 1px;
        }

        #consul_sidebar .nav-link.active {
            background-color: whitesmoke;
        }
    </style>

    @vite(['resources/js/app.js'])
</head>

<body class="container-fluid h-100 d-flex flex-column m-0 p-0" id="doctorPage">
    @include("template.navbar")

    <div class="p-2 d-flex flex-column flex-grow-1">
        <ul class="nav nav-tabs nav-fill" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#dashboard" role="tab"
                    aria-controls="dashboard" aria-selected="true">Dashboard</button>
            </li>

            <li class="nav-item" role="presentation">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#consultation" role="tab"
                    aria-controls="consultation" aria-selected="false" id="consult_tab">Consultation</button>
            </li>

            <li class="nav-item" role="tab">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#patients" role="tab"
                    aria-controls="patients" aria-selected="false" id="masterlist_tab">Patients Masterlist</button>
            </li>
        </ul>

        <div class="tab-content border border-top-0 rounded-bottom p-4 d-flex flex-column flex-grow-1">
            <!-- Dashboard -->
            <div class="tab-pane active h-100" id="dashboard" role="tabpanel" aria-labelledby="dashboard-tab"
                tabindex="0">
                <div class="d-flex flex-column h-100">
                    <h3>{{ now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening') }},
                        Dr. {{ $doctor->doclname }}!</h3>
                    <hr>
                    <div class="d-flex flex-grow-1 gap-4">
                        <div class="rounded w-50 d-flex flex-grow-1 flex-column card">
                            <div class="d-flex justify-content-between text-white bg-primary px-3 py-2 rounded-top">
                                <h5 class="m-0 fw-bold">Today's Patients</h5>
                                <p class="m-0 fw-bold" id="patient_count">1/30</p>
                            </div>

                            <div
                                class="bg-white rounded-bottom border-top-0 table-responsive p-2 d-flex flex-column flex-grow-1">
                                <table class="table table-sm table-bordered m-0" id="todays_patients_table">
                                    <thead class="table-primary">
                                        <tr>
                                            <th>Queue</th>
                                            <th>Patient Name</th>
                                            <th>Status</th>
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

                        <div class="rounded w-50 d-flex flex-column flex-grow-1 card">
                            <div class="d-flex text-white bg-success px-3 py-2 rounded-top">
                                <h5 class="m-0 fw-bold">Schedules</h5>
                            </div>

                            <div class="bg-white rounded-bottom p-2 d-flex flex-column flex-grow-1">
                                <table class="table table-sm table-bordered" id="schedules_calendar">
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Consultations -->
            <div class="tab-pane h-100" id="consultation" role="tabpanel" aria-labelledby="consultation-tab"
                tabindex="0">
                <div class="d-flex flex-column h-100">
                    <h3>Consultation List</h3>
                    <hr>
                    <div class="d-flex flex-grow-1 gap-4">
                        <div class="d-flex flex-grow-1 flex-column w-75 gap-2">
                            <div class="d-flex justify-content-end">
                                <div class="input-group w-25">
                                    <button class="btn btn-primary" id="previous">
                                        <span class="fa-solid fa-angle-left"></span>
                                    </button>
                                    <input class="form-control w-25" type="date" name="consul_date"
                                        id="consul_date">
                                    <button class="btn btn-primary" id="next">
                                        <span class="fa-solid fa-angle-right"></span>
                                    </button>
                                </div>
                            </div>

                            <div class="table-responsive w-100">
                                <table class="table table-hover align-middle table-bordered m-0"
                                    id="consultation_table">
                                    <thead class="table-primary">
                                        <tr>
                                            <th scope="col">Patient Name</th>
                                            <th scope="col">Status</th>
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

                        <div class="rounded w-50 d-flex flex-column card">
                            <div class="d-flex text-white bg-primary px-3 py-2 rounded-top">
                                <h5 class="m-0 fw-bold">Consultation Details</h5>
                            </div>

                            <div class="bg-white rounded-bottom p-3 d-flex flex-column flex-grow-1">
                                <div class="w-100 d-flex justify-content-center mb-3">
                                    <img style="max-height: 15rem; max-width: 15rem;" alt="patient_photo"
                                        id="px_photo_preview">
                                </div>
                                <p class="m-0">Patient Name: <strong id="pxname"></strong></p>
                                <p class="m-0">Sex: <strong id="pxsex"></strong></p>
                                <div class="d-flex">
                                    <p class="m-0 w-50">Birthdate: <strong id="pxbday"></strong></p>
                                    <p class="m-0 w-50">Age: <strong id="pxage"></strong></p>
                                </div>
                                <div class="d-flex">
                                    <p class="m-0 w-50">Contact #: <strong id="pxcellno"></strong></p>
                                    <p class="m-0 w-50">Email: <strong id="pxemail"></strong></p>
                                </div>
                                <p class="m-0">Address: <strong id="pxaddress"></strong></p>

                                <hr>

                                <div class="d-flex justify-content-between">
                                    <p class="m-0 w-50">Weight: <strong id="pxweight"></strong></p>
                                    <p class="m-0 w-50">Height: <strong id="pxheight"></strong></p>
                                    <p class="m-0 w-50">Temperature: <strong id="pxtemp"></strong></p>
                                </div>

                                <div class="d-flex justify-content-between">
                                    <p class="m-0 w-50">Respiratory Rate: <strong id="pxresprate"></strong></p>
                                    <p class="m-0 w-50">Pulse Rate: <strong id="pxpulserate"></strong></p>
                                    <p class="m-0 w-50">Blood Pressure: <strong id="pxbp"></strong></p>
                                </div>

                                <hr>

                                <div class="">
                                    <label class="form-label" for="rfc">Reason for Consultation</label>
                                    <textarea class="form-control" name="rfc" id="rfc" disabled></textarea>
                                </div>

                                <hr class="mt-auto">

                                <div class="d-flex justify-content-center">
                                    <button class="btn btn-primary" id="consult">Proceed Consultation</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Masterlist -->
            <div class="tab-pane" id="patients" role="tabpanel" aria-labelledby="patients-tab" tabindex="0">
                <h3>Patient Masterlist</h3>
                <hr>
                <div class="d-flex gap-4 w-100">
                    <div class="table-responsive w-100">
                        <table class="table table-sm align-middle table-bordered" id="masterlist_table">
                            <thead class="table-success">
                                <tr>
                                    <th scope="col">Actions</th>
                                    <th scope="col">Photo</th>
                                    <th scope="col">Patient Name (Last, First, Middle, Suffix)</th>
                                    <!-- <th scope="col">Reason for Consultation</th> -->
                                    <th scope="col">Consultation Date</th>
                                    <!-- <th scope="col">Status</th> -->
                                </tr>
                            </thead>

                            <tbody>
                                <td></td>
                                <td></td>
                                <td></td>
                                <!-- <td></td> -->
                                <td></td>
                                <!-- <td></td> -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include("modals.consultation_modal")
    @include('modals.doctor_info')
    @include('modals.view_patient')

</body>