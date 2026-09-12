<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} | Login</title>
    <link rel="icon" type="image/png" href="favicon.ico">
    @vite(['resources/js/app.js', 'resources/css/app.css'])
</head>

<body class="overflow-hidden min-vh-100 font-sans">
    <div class="bg-primary vh-100" style="--bs-bg-opacity: .25;">
        <div class="d-flex align-items-center justify-content-center min-vh-100">
            <div class="bg-white w-25 d-flex align-items-center justify-content-center rounded-5 shadow">
                <div class="px-0 py-5 w-100 p-0 d-flex flex-column align-items-center gap-3">
                    <img class="d-flex" style="width: 20rem;" src="{{ asset('images/logo.png') }}" alt="company_logo">

                    <form class="d-flex flex-column gap-3 w-75" method="POST"
                        action="{{ route('login.authenticate') }}">
                        @csrf

                        <div class="">
                            @if ($errors->any())
                                <div class="alert alert-danger py-2" role="alert">
                                    {{ $errors->first() }}
                                </div>
                            @endif

                            <label class="form-label fw-bold m-0" for="username">Username</label>
                            <input class="form-control" type="text" name="username" id="username">
                        </div>

                        <div class="">
                            <label class="form-label fw-bold m-0" for="password">Password</label>
                            <input class="form-control" type="password" name="password" id="password">
                        </div>

                        <div class="d-flex flex-column gap-2">
                            <button type="submit" class="btn btn-primary fw-bold">Login</button>
                            <button type="button" class="btn btn-sm btn-link">Forgot Password?</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <p class="position-absolute bottom-0 end-0 text-white pe-3 pb-2">© DrainWiz Computer Systems</p>
    </div>

    <div class="position-fixed top-0 start-0 w-100 h-100"
        style="background-image: url('{{ asset('images/background/hero.jpg') }}'); background-size: cover; background-position: center; background-repeat: no-repeat; z-index: -1;">
    </div>
</body>

</html>