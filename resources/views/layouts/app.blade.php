<!DOCTYPE html>
<html class="vh-100">

    <head>
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <style>
            #admin_sidebar.nav .active {
                background-color: white !important;
                font-weight: bold;
            }

            #admin_sidebar.nav a:hover {
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

            .select2-container .select2-selection--single {
                height: 38px !important;
                display: flex;
                align-items: center;
            }

            .select2-container--default .select2-selection--single .select2-selection__arrow {
                height: 36px;
            }
        </style>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @stack('styles')
    </head>

    <body class="d-flex flex-column vh-100 overflow-hidden" id="{{ $bodyId ?? '' }}">

        <x-navbar />

        <div class="d-flex flex-grow-1 overflow-hidden">
            @include('components.sidebar')
            <main class="flex-grow-1 overflow-auto p-2">
                @yield('content')
            </main>
        </div>

        @stack('modals')
        @stack('scripts')

    </body>

</html>
