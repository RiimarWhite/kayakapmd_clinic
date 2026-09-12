@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/diagnostics/requests.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Billing</h1>
        <hr>
    </div>
@endsection
