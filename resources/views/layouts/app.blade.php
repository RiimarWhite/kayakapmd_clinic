<!DOCTYPE html>
<html class="vh-100">

    <head>
        <link rel="icon" type="image/png" href="{{ asset('favicon.ico') }}">
        <title>{{ config('app.name', 'Laravel') }}</title>
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="logout-url" content="{{ route('logout') }}">
        <meta name="login-url" content="{{ route('login') }}">
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

            /* Detailed Comment: Ensure Select2 dropdown renders with full width and floats above all modal dialogs */
            .select2-container {
                width: 100% !important;
            }

            .select2-dropdown {
                z-index: 9999 !important;
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

        {{-- Detailed Comment: Dynamic CSS custom variables driven by the active clinic theme configuration --}}
        <style id="app-dynamic-theme-vars">
            :root {
                --app-bg: {{ $activeTheme->app_background ?? '#f8f9fa' }};
                --header-bg: {{ $activeTheme->header_bg ?? '#f4c79f' }};
                --header-text: {{ $activeTheme->header_text_color ?? '#212529' }};
                --header-accent: {{ $activeTheme->header_accent_color ?? '#ffa500' }};
                --footer-bg: {{ $activeTheme->footer_bg ?? '#f8f9fa' }};
                --footer-text: {{ $activeTheme->footer_text_color ?? '#6c757d' }};
                --sidebar-bg: {{ $activeTheme->sidebar_bg ?? '#e9ecef' }};
                --sidebar-text: {{ $activeTheme->sidebar_text_color ?? '#212529' }};
                --primary-btn-bg: {{ $activeTheme->primary_button_bg ?? '#0d6efd' }};
                --primary-btn-text: {{ $activeTheme->primary_button_text ?? '#ffffff' }};
                --secondary-btn-bg: {{ $activeTheme->secondary_button_bg ?? '#6c757d' }};
                --secondary-btn-text: {{ $activeTheme->secondary_button_text ?? '#ffffff' }};
                --body-text: {{ $activeTheme->text_color ?? '#212529' }};
            }

            body {
                background-color: var(--app-bg) !important;
                color: var(--body-text) !important;
            }

            .navbar {
                background-color: var(--header-bg) !important;
                color: var(--header-text) !important;
            }

            .navbar .navbar-brand,
            .navbar .nav-link,
            .navbar .btn-light {
                color: var(--header-text);
            }

            .navbar-accent-line {
                background-color: var(--header-accent) !important;
            }

            .app-footer {
                background-color: var(--footer-bg) !important;
                color: var(--footer-text) !important;
            }

            #admin_sidebar,
            #management_sidebar {
                background-color: var(--sidebar-bg) !important;
                color: var(--sidebar-text) !important;
            }

            .btn-primary {
                background-color: var(--primary-btn-bg) !important;
                border-color: var(--primary-btn-bg) !important;
                color: var(--primary-btn-text) !important;
            }

            .btn-secondary {
                background-color: var(--secondary-btn-bg) !important;
                border-color: var(--secondary-btn-bg) !important;
                color: var(--secondary-btn-text) !important;
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

        {{-- Detailed Comment: Application bottom footer displaying clinic facility branding, copyright, and system version --}}
        <footer class="app-footer border-top py-2 px-3 d-flex justify-content-between align-items-center small flex-shrink-0" style="background-color: var(--footer-bg, #f8f9fa); color: var(--footer-text, #6c757d); z-index: 100;">
            <div class="d-flex align-items-center gap-2">
                <span>&copy; {{ date('Y') }} {{ $activeProfile->HOSP_NAME ?? config('app.name', 'KayakAPMD') }}. All rights reserved.</span>
            </div>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted">KayakAPMD Clinic Management System</span>
                <span class="badge bg-secondary-subtle text-secondary border">v2.0</span>
            </div>
        </footer>

        @stack('modals')
        @stack('scripts')

    </body>

</html>
