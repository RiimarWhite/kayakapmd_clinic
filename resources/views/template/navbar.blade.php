<nav class="navbar" style="background-color: beige;">
    <div class="container-fluid m-0">
        <div class="navbar-brand d-flex gap-3 align-items-center">
            <img style="height: 7.5rem;" src="{{ asset('images/logo.png') }}" alt="company_logo">
        </div>

        <div class="d-flex gap-3">
            @if (auth()->guard('doctor')->check())
                <button class="btn btn-light" data-bs-toggle="modal" data-bs-target="#doctor_profile_modal"><i class="fa-solid fa-user-doctor"></i> Profile</button>
            @endif
            {{-- Detailed Comment: Hidden POST form for native, environment-agnostic logout --}}
            <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                @csrf
            </form>
            <button class="btn btn-light" id="logout" type="button"><span class="fa fa-arrow-right-from-bracket"></span> Sign out</button>
        </div>
    </div>
</nav>