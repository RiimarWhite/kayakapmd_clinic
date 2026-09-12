@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/dashboard.js')
@endpush

@section('content')
<div class="card h-100 p-3" id="admin_page">
    <h1 class="m-0">Dashboard</h1>
    <hr>

    <div class="d-flex justify-content-between">
        <div class="d-flex gap-3">
            <!-- Total Patients -->
            <div class="d-flex rounded border border-1 shadow-sm p-4 gap-4">
                <h1 class="m-0">
                    <i class="fa-solid fa-users text-warning fa-2x"></i>
                </h1>

                <div class="d-flex flex-column justify-content-center text-center">
                    <h1 class="m-0">{{ $data['patient_records'] }}</h1>
                    <h4 class="m-0">Patient Records</h4>
                </div>
            </div>

            <!-- Total Consultations -->
            <div class="d-flex rounded border border-1 shadow-sm p-4 gap-4">
                <h1 class="m-0">
                    <i class="fa-solid fa-stethoscope text-primary fa-2x"></i>
                </h1>

                <div class="d-flex flex-column justify-content-center text-center">
                    <h1 class="m-0">{{ $data['consultations'] }}</h1>
                    <h4 class="m-0">Consultations</h4>
                </div>
            </div>
        </div>

        <div class="d-flex flex-column rounded border border-1 shadow-sm p-4 w-50">
            <!-- Today's Consultation -->
            <h2 class="m-0">Consultations Today: {{ $data['today_consultations'] }}</h2>
            <p class="text-secondary">{{ now()->format('M d, Y') }}</p>
            <hr class="my-2">
        </div>
    </div>
</div>
@endsection
