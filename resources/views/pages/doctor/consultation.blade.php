@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/doctor/consultation.js')
    @vite('resources/js/pages/doctor/consultation/form.js')
@endpush

@php $bodyId = 'doctorPage'; @endphp

@section('content')
    <div class="d-flex flex-column h-100">
        <h3>Consultation List</h3>
        <hr>
        <div class="d-flex flex-grow-1 gap-4">
            <div class="d-flex flex-grow-1 flex-column w-75 gap-2">
                <div class="d-flex align-items-end justify-content-end">
                    <div class="input-group w-auto">
                        <button class="btn btn-primary" id="previous">
                            <span class="fa-solid fa-angle-left"></span>
                        </button>
                        <input class="form-control" style="min-width: 15rem;" type="date" name="consul_date" id="consul_date">
                        <button class="btn btn-primary" id="next">
                            <span class="fa-solid fa-angle-right"></span>
                        </button>
                    </div>
                </div>

                <div class="table-responsive w-100">
                    <table class="table table-hover align-middle table-bordered m-0" id="consultation_table">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Patient Name</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="rounded w-50 d-flex flex-column card">
                <div class="d-flex text-white bg-primary px-3 py-2 rounded-top">
                    <h5 class="m-0 fw-bold">Consultation Details</h5>
                </div>

                <div class="bg-white rounded-bottom p-3 d-flex flex-column flex-grow-1">
                    <div class="w-100 d-flex justify-content-center mb-3">
                        <img src="{{ asset('images/blank_photo.png') }}" style="max-height: 15rem; max-width: 15rem;" alt="patient_photo" id="px_photo_preview">
                    </div>
                    <p class="m-0">Patient Name: <strong id="pxname"></strong></p>
                    <p class="m-0">Sex: <strong id="pxsex"></strong></p>
                    <div class="d-flex">
                        <p class="m-0 w-50">Birthdate: <strong id="pxbday"></strong></p>
                        <p class="m-0 w-50">Age: <strong id="pxage"></strong></p>
                    </div>
                    <div class="d-flex">
                        <p class="m-0 w-50">Contact #: <strong id="pxcellno"></strong></p>
                        <p class="m-0 w-50">Email: <strong id="pxemail"></strong></p>
                    </div>
                    <p class="m-0">Address: <strong id="pxaddress"></strong></p>

                    <hr>

                    <div class="d-flex justify-content-between">
                        <p class="m-0 w-50">Weight: <strong id="pxweight"></strong></p>
                        <p class="m-0 w-50">Height: <strong id="pxheight"></strong></p>
                        <p class="m-0 w-50">Temperature: <strong id="pxtemp"></strong></p>
                    </div>

                    <div class="d-flex justify-content-between">
                        <p class="m-0 w-50">Respiratory Rate: <strong id="pxresprate"></strong></p>
                        <p class="m-0 w-50">Pulse Rate: <strong id="pxpulserate"></strong></p>
                        <p class="m-0 w-50">Blood Pressure: <strong id="pxbp"></strong></p>
                    </div>

                    <hr>

                    <div class="">
                        <label class="form-label" for="rfc">Chief Complaints:</label>
                        <textarea class="form-control" name="rfc" id="rfc" disabled></textarea>
                    </div>

                    <hr class="mt-auto">

                    <div class="d-flex justify-content-center">
                        <button class="btn btn-primary" id="consult">Proceed Consultation</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.consultation_modal')
    @include('modals.doctor_info')
    @include('modals.view_patient')
@endpush
