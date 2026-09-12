@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/users/secretaries.js')
@endpush

@section('content')
    <div class="card h-100 p-3">
        <div id="add-secretary">
            <h1 class="m-0">Secretary</h1>
            <hr>
            <form class="d-flex flex-column gap-3 w-100" action="{{ route('admin.add_secretary') }}" method="post"
                id="add_secretary_form">
                @csrf
                <div class="d-flex gap-2">
                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secfname">First Name</label>
                        <input class="form-control" type="text" name="secfname" id="secfname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secfname">Middle Name</label>
                        <input class="form-control" type="text" name="secmname" id="secmname">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secfname">Last Name</label>
                        <input class="form-control" type="text" name="seclname" id="seclname" required>
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secsuffix">Suffix</label>
                        <input class="form-control" type="text" name="secsuffix" id="secsuffix" maxlength="10">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secgender">Sex</label>
                        <select class="form-select" name="secgender" id="secgender">
                            <option value="male">MALE</option>
                            <option value="female">FEMALE</option>
                        </select>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <div class="">
                        <label class="form-label fw-bold" for="secbday">Birthdate</label>
                        <input class="form-control" type="date" name="secbday" id="secbday">
                    </div>

                    <div class="">
                        <label class="form-label fw-bold" for="seccontactno">Contact #</label>
                        <input class="form-control" type="tel" name="seccontactno" id="seccontactno"
                            placeholder="(+63)">
                    </div>

                    <div class="">
                        <label class="form-label fw-bold" for="secemail">Email Address</label>
                        <input class="form-control" type="email" name="secemail" id="secemail">
                    </div>

                    <div class=" d-flex flex-column flex-grow-1">
                        <label class="form-label fw-bold" for="secadrs">Address</label>
                        <input class="form-control" type="text" name="secadrs" id="secadrs">
                    </div>
                </div>

                <div class="d-flex justify-content-end gap-2">
                    <div class="">
                        <label class="form-label fw-bold" for="secpassword">Default Password</label>
                        <input class="form-control" type="text" name="secpassword" id="secpassword" required>
                    </div>

                    <div class="d-flex align-items-end">
                        <button class="btn btn-success" type="button" id="add_secretary_btn"><i
                                class="fa-solid fa-plus"></i> Add Secretary</button>
                    </div>
                </div>
            </form>

            <hr class="my-4">

            <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle caption-top" id="secretary_table">
                    <caption>List of Secretaries</caption>
                    <thead class="table-success">
                        <tr>
                            <th scope="col">Actions</th>
                            <th scope="col">Full Name <span class="text-secondary">(Last, First, Middle,
                                    Suffix)</span></th>
                            <th scope="col">Contact #</th>
                            <th scope="col">Email Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.assigned_doctors')
@endpush
