{{-- Detailed Comment: Top navigation header with dynamic background, logo, and accent strip driven by active clinic theme --}}
<nav class="navbar p-0 gap-1" style="background-color: var(--header-bg, #f4c79f);">
    <div class="container-fluid m-0">
        <div class="navbar-brand d-flex gap-1 align-items-center">
            <button class="btn btn-lg" id="hide-sidebar" style="color: inherit;">
                <i class="fa-solid fa-bars"></i>
            </button>
            <img class="antialiased" style="height: 80px; max-height: 80px; object-fit: contain;" src="{{ asset($activeTheme->logo_path ?? 'images/logo.png') }}" alt="company_logo" id="app_navbar_logo">
        </div>

        <div class="d-flex gap-2">
            {{-- Detailed Comment: Global Header Theme Switcher dropdown supporting Dark, Light, and System modes with accessible controls --}}
            <div class="dropdown" id="theme_switcher_dropdown">
                <button class="btn btn-light dropdown-toggle d-flex align-items-center gap-1" id="bd-theme" type="button" aria-expanded="false" data-bs-toggle="dropdown" aria-label="Toggle theme">
                    <i class="fa-solid fa-circle-half-stroke me-1" id="theme-active-icon"></i>
                    <span class="d-none d-md-inline" id="theme-active-text">System</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="bd-theme" id="theme_options_menu">
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="light" id="theme_option_light">
                            <span><i class="fa-solid fa-sun me-2 text-warning"></i> Light</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="dark" id="theme_option_dark">
                            <span><i class="fa-solid fa-moon me-2 text-info"></i> Dark</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                    <li>
                        <button type="button" class="dropdown-item d-flex align-items-center justify-content-between" data-bs-theme-value="system" id="theme_option_system">
                            <span><i class="fa-solid fa-circle-half-stroke me-2 text-secondary"></i> System</span>
                            <i class="fa-solid fa-check text-primary theme-check-icon d-none"></i>
                        </button>
                    </li>
                </ul>
            </div>

            @if (auth()->guard('doctor')->check())
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#doctor_profile_modal">
                    <i class="fa-solid fa-user-doctor"></i> Profile
                </button>
            @elseif (auth()->guard('secretary')->check())
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#secretary_profile_modal">
                    <i class="fa-solid fa-user-nurse"></i> Profile
                </button>
            @elseif (auth()->guard('admin')->check())
                <a class="btn btn-light" href="{{ route('admin.profile') }}">
                    <i class="fa-solid fa-user-shield"></i> Profile
                </a>
            @endif

            {{-- Detailed Comment: Hidden POST form for native, environment-agnostic logout --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            <button class="btn btn-light" id="logout" type="button">
                <span class="fa fa-arrow-right-from-bracket"></span> Sign out
            </button>
        </div>
    </div>

    <span class="navbar-accent-line" style="background-color: var(--header-accent, orange); height: 0.25rem; width: 100%;"></span>
</nav>
