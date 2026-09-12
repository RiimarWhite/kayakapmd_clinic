<nav class="navbar p-0 gap-1" style="background-color: #f4c79f;">
    <div class="container-fluid m-0">
        <div class="navbar-brand d-flex gap-1 align-items-center">
            <button class="btn btn-lg" id="hide-sidebar">
                <i class="fa-solid fa-bars"></i>
            </button>
            <img class="antialiased" style="height: 80px;" src="{{ asset('images/logo.png') }}" alt="company_logo">
        </div>

        <div class="d-flex gap-2">
            <button class="btn btn-light">
                <i class="fa-solid fa-sun"></i>
            </button>

            @if (auth()->guard('doctor')->check())
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#doctor_profile_modal">
                    <i class="fa-solid fa-user-doctor"></i> Profile
                </button>
            @endif

            <button class="btn btn-light" id="logout">
                <span class="fa fa-arrow-right-from-bracket"></span> Sign out
            </button>
        </div>
    </div>

    <span style="background-color: orange; height: 0.25rem; width: 100%;"></span>
</nav>
