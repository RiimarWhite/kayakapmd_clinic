<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} | Home</title>
    <link rel="icon" type="image/png" href="favicon.ico">

    <style>
        body {
            scroll-behavior: smooth;
        }
    </style>

    @vite(['resources/js/app.js'])
</head>

<body class="overflow-y-scroll vh-100">
    <div class="d-flex h-75 justify-content-center align-items-center">
        <a href="#about-us"
            class="text-decoration-none d-flex justify-content-center align-items-center gap-4 shadow p-5 rounded-5 bg-primary"
            style="--bs-bg-opacity: .25; z-index: 10;">
            <img style="height: 200px;" src="{{ asset('images/logo.png') }}" alt="company_logo">
            <div class="d-flex text-center flex-column gap-0">
                <h1 class="fw-bold text-white mb-0" style="font-size: 80px;">{{ $profile->COMPANYNAME }}</h1>
                <p class="fs-4 text-white">{{ $profile->COMPANYADDRESS }}</p>
            </div>
        </a>
    </div>

    <div class="w-100 h-100 justify-content-center align-items-center d-flex">
        <div class="d-flex flex-column bg-white h-100 w-75 shadow-lg rounded-top-5 p-5 position-relative"
            style="z-index: 10;">
            <h1 id="about-us">About Us</h1>
            <hr>
        </div>
    </div>

    <p class="position-fixed bottom-0 end-0 text-white pe-3 pb-2 fw-bold" style="z-index: 7;">© DrainWiz Computer
        Systems</p>
    <div class="position-fixed top-0 start-0 w-100 bg-primary vh-100" style="--bs-bg-opacity: .25; z-index: 1;"></div>
    <div class="position-fixed top-0 start-0 w-100 h-100"
        style="background-image: url('{{ asset('images/background/hero.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; z-index: -1;">
    </div>
</body>

</html>