@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/doctor/dashboard.js')
@endpush

@php $bodyId = 'doctorPage'; @endphp

@section('content')
    <div class="d-flex flex-column h-100">
        <h3>{{ now()->hour < 12 ? 'Good Morning' : (now()->hour < 18 ? 'Good Afternoon' : 'Good Evening') }},
            Dr. {{ $doctor->doclname }}!</h3>
        <hr>
        <div class="d-flex flex-grow-1 gap-2">
            <div class="card shadow-sm w-50">
                <div class="card-header text-bg-primary d-flex justify-content-between">
                    <h5 class="card-title m-0">Today's Patients</h5><span id="patient_count">1/30</span>
                </div>

                <div class="card-body py-0">
                    <table class="table table-sm table-bordered" id="todays_patients_table">
                        <thead class="table-primary">
                            <tr>
                                <th scope="col">Queue</th>
                                <th scope="col">Patient Name</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>

                        <tbody>
                            <tr>
                                <td></td>
                                <td></td>
                                <td></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card shadow-sm w-50">
                <div class="card-header text-bg-success">
                    <h5 class="card-title m-0">Schedules</h5>
                </div>

                <div class="card-body">
                    <table class="table table-sm table-bordered" id="schedules_calendar">
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('modals')
    @include('modals.doctor_info')
@endpush
