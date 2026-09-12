<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ config('app.name', 'Laravel') }} | Register</title>
    <link rel="icon" type="image/png" href="favicon.ico">
    @vite(['resources/js/app.js'])
</head>

<body class="d-flex flex-column h-100" id="registration">
    <!-- Navbar -->
    <nav class="navbar bg-primary d-flex">
        <div class="navbar-brand d-flex align-items-center px-2 gap-2">
            <img style="height: 50px;" src="{{ asset('images/logo.png') }}" alt="company_logo">
            <div class="d-flex flex-column">
                <h3 class="fw-bold text-white m-0">E-Consultation</h3>
                <p class="text-white m-0" style="font-size: 12px;">Company Name</p>
            </div>
        </div>
    </nav>

    <!-- Form -->
    <section class="d-flex flex-column p-2 h-100">
        <div class="row d-flex justify-content-center m-0">
            <div class="col-sm-12 col-lg-8 p-0">
                <form class="card d-flex flex-column p-4 gap-2" id="register_form">
                    @csrf

                    <h1 class="m-0">Register</h1>

                    <hr class="mt-0">

                    <div class="d-flex mb-3 gap-4">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pMem" id="isMem" checked>
                            <label class="form-check-label" for="pMem">Member</label>
                        </div>

                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="pMem" id="isDep">
                            <label class="form-check-label" for="pMem">Dependent</label>
                        </div>
                    </div>

                    <div class="d-none flex-column" id="ifMem">
                        <h5 class="fw-bold">Member</h5>

                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-1">
                            <div class="">
                                <label class="form-label m-0" for="pMemFname">First Name<span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pMemFname" id="pMemFname" required>
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pMemMname">Middle Name</label>
                                <input class="form-control" type="text" name="pMemMname" id="pMemMname">
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pMemLname">Last Name<span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pMemLname" id="pMemLname" required>
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pExtname">Suffix</label>
                                <input class="form-control" type="text" name="pExtname" id="pExtname">
                            </div>
                        </div>

                        <hr>
                    </div>

                    <div class="d-flex flex-column" id="ifDep">
                        <h5 class="fw-bold">Patient Information</h5>

                        <div class="d-flex flex-column flex-lg-row justify-content-between gap-1">
                            <div class="">
                                <label class="form-label m-0" for="pFname">First Name<span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pFname" id="pFname" required>
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pMname">Middle Name</label>
                                <input class="form-control" type="text" name="pMname" id="pMname" required>
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pLname">Last Name<span
                                        class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="pLname" id="pLname">
                            </div>

                            <div class="">
                                <label class="form-label m-0" for="pExtname">Suffix</label>
                                <input class="form-control" type="text" name="pExtname" id="pExtname">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center gap-1">
                        <div class=" col-6">
                            <label class="form-label m-0" for="pSex">Sex<span class="text-danger">*</span></label>
                            <select class="form-select" name="pSex" id="pSex" required>
                                <option disabled selected>Select</option>
                                <option value="MALE">Male</option>
                                <option value="FEMALE">Female</option>
                            </select>
                        </div>

                        <div class=" col-6">
                            <label class="form-label m-0" for="pDob">Date of Birth<span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="date" name="pDob" id="pDob" required>
                        </div>
                    </div>

                    <hr>

                    <div class="d-flex gap-1">
                        <div class=" col-6">
                            <label class="form-label" for="pMobileNo">Mobile Number<span
                                    class="text-danger">*</span></label>
                            <input class="form-control" type="tel" name="pMobileNo" id="pMobileNo" required>
                        </div>

                        <div class=" col-6">
                            <label class="form-label" for="pEmail">Email Address</label>
                            <input class="form-control" type="email" name="pEmail" id="pEmail">
                        </div>
                    </div>

                    <div class=" col-12">
                        <label class="form-label" for="pAddress">Address<span class="text-danger">*</span></label>
                        <input class="form-control" type="email" name="pAddress" id="pAddress">
                    </div>

                    <hr>

                    <div class="d-flex justify-content-lg-end">
                        <button class="btn btn-primary col-12 col-lg-3" type="button" id="register_btn">Submit
                            Form</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="w-100 d-flex bg-primary justify-content-center p-2">
        <p class="text-white m-0" style="font-size: 12px;">DrainWiz Computer Systems</p>
    </footer>
</body>