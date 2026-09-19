<ul class="nav nav-pills bg-body-secondary flex-column p-2 gap-1 d-flex" style="width: 20rem; max-width: 20rem;"
    id="admin_sidebar">
    {{-- Admin --}}
    @if (auth()->guard('admin')->check())
    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
            href="{{ route('admin.dashboard') }}">
            <i class="fa-solid fa-dashboard"></i> Dashboard
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('doctor.patients') ? 'active' : '' }}"
            href="{{ route('doctor.patients') }}">
            <i class="fa-solid fa-users"></i> Patients Masterlist
        </a>
    </li>

    <li class="nav-item">
        <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
            data-bs-toggle="collapse" data-bs-target="#myConsulBtn" type="button" aria-controls="myConsulBtn"
            aria-expanded="{{ request()->routeIs('admin.consultations.*') ? 'true' : 'false' }}">
            <i class="fa-solid fa-staff-snake"></i>
            My Consultation
            <i class="fa-solid fa-caret-down ms-auto"></i>
        </button>
        <div class="collapse {{ request()->routeIs('admin.consultations.*') ? 'show' : '' }}" id="myConsulBtn">
            <div class="card card-body bg-transparent border-0 p-0 gap-1 flex-column">
                <!-- <a class="nav-link text-dark {{ request()->routeIs('admin.consultations.requests') ? 'active' : '' }}"
                        style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.consultations.requests') }}">
                        Diagnostic Requests
                    </a> -->
                <a class="nav-link text-dark {{ request()->routeIs('admin.consultations.billing') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.consultations.billing') }}">
                    Billing
                </a>
                <a class="nav-link text-dark {{ request()->routeIs('admin.consultations.settlements') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;"
                    href="{{ route('admin.consultations.settlements') }}">
                    Payment & Settlements
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('admin.secretary') ? 'active' : '' }}"
            href="{{ route('admin.secretary') }}">
            <i class="fa-solid fa-user-pen"></i> Secretary Console
        </a>
    </li>

    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('admin.hmo') ? 'active' : '' }}"
            href="{{ route('admin.hmo') }}">
            <i class="fa-solid fa-building"></i> HMO
        </a>
    </li>

    <li class="nav-item">
        <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
            data-bs-toggle="collapse" data-bs-target="#stockSrvBtn" type="button" aria-controls="stockSrvBtn"
            aria-expanded="{{ request()->routeIs('admin.stocks.*') ? 'true' : 'false' }}">
            <i class="fa-solid fa-table-list"></i>
            Stocks & Services
            <i class="fa-solid fa-caret-down ms-auto"></i>
        </button>
        <div class="collapse {{ request()->routeIs('admin.stocks.*') ? 'show' : '' }}" id="stockSrvBtn">
            <div class="card card-body bg-transparent border-0 p-0 gap-1 flex-column">
                <a class="nav-link text-dark {{ request()->routeIs('admin.stocks.management') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.stocks.management') }}">
                    Management
                </a>
                <a class="nav-link text-dark {{ request()->routeIs('admin.stocks.ledger') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.stocks.ledger') }}">
                    Stocks Ledger
                </a>
                <a class="nav-link text-dark {{ request()->routeIs('admin.stocks.inventory') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.stocks.inventory') }}">
                    Inventory
                </a>
            </div>
        </div>
    </li>



    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('admin.profile') ? 'active' : '' }}"
            href="{{ route('admin.profile') }}">
            <i class="fa-solid fa-building"></i> Profile
        </a>
    </li>

    <li class="nav-item">
        <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
            data-bs-toggle="collapse" data-bs-target="#utilitiesBtn" type="button" aria-controls="utilitiesBtn"
            aria-expanded="{{ request()->routeIs('admin.utilities.*') ? 'true' : 'false' }}">
            <i class="fa-solid fa-gear"></i>
            Utilities
            <i class="fa-solid fa-caret-down ms-auto"></i>
        </button>
        <div class="collapse {{ request()->routeIs('admin.utilities.*') ? 'show' : '' }}" id="utilitiesBtn">
            <div class="card card-body bg-transparent border-0 p-0 gap-1 flex-column">
                <a class="nav-link text-dark {{ request()->routeIs('admin.utilities.chargecat') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.utilities.chargecat') }}">
                    Charges Category
                </a>
                <a class="nav-link text-dark {{ request()->routeIs('admin.utilities.diagcat') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.utilities.diagcat') }}">
                    Diagnostic Category
                </a>
            </div>
        </div>
    </li>

    <li class="nav-item">
        <button class="d-flex align-items-center gap-2 nav-link w-100 text-start text-dark"
            data-bs-toggle="collapse" data-bs-target="#usersBtn" type="button" aria-controls="usersBtn"
            aria-expanded="{{ request()->routeIs('admin.users.*') ? 'true' : 'false' }}">
            <i class="fa-solid fa-user-gear"></i>
            Users Management
            <i class="fa-solid fa-caret-down ms-auto"></i>
        </button>
        <div class="collapse {{ request()->routeIs('admin.users.*') ? 'show' : '' }}" id="usersBtn">
            <div class="card card-body bg-transparent border-0 p-0 gap-1 flex-column">
                <!-- Detailed Comment: Sub-menu item rebranded to Secretaries/Admin Users referencing new route -->
                <a class="nav-link text-dark {{ (request()->routeIs('admin.users.secretaries_admin') || request()->routeIs('admin.users.secretaries')) ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.users.secretaries_admin') }}">
                    Secretaries/Admin Users
                </a>
                <a class="nav-link text-dark {{ request()->routeIs('admin.users.doctors') ? 'active' : '' }}"
                    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.users.doctors') }}">
                    Doctors
                </a>
            </div>
        </div>
    </li>
    @endif

    @if (auth()->guard('secretary')->check())
    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('secretary.queue') ? 'active' : '' }}"
            href="{{ route('secretary.queue') }}">
            <i class="fa-solid fa-list-ul"></i> Patient Queue
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#secretary_profile_modal">
            <i class="fa-solid fa-user-nurse"></i> My Profile
        </a>
    </li>
    @endif

    @if (auth()->guard('doctor')->check())
    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('doctor.dashboard') ? 'active' : '' }}"
            href="{{ route('doctor.dashboard') }}">
            <i class="fa-solid fa-gauge"></i> Dashboard
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('doctor.consultation') ? 'active' : '' }}"
            href="{{ route('doctor.consultation') }}">
            <i class="fa-solid fa-stethoscope"></i> Consultation
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark {{ request()->routeIs('doctor.patients') ? 'active' : '' }}"
            href="{{ route('doctor.patients') }}">
            <i class="fa-solid fa-users"></i> Patients Masterlist
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link text-dark" href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#doctor_profile_modal">
            <i class="fa-solid fa-user-doctor"></i> My Profile
        </a>
    </li>
    @endif
</ul>
