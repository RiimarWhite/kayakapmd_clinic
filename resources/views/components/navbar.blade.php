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

    <span style="background-color: orange; height: 0.25rem; width: 100%;"></span>
</nav>
