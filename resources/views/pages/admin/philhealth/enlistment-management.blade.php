@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/philhealth/enlistment.js')
    @vite('resources/js/pages/admin/philhealth/philhealth-api.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <h1 class="m-0">Enlistment</h1>

        <hr>

        <div class="d-flex justify-content-end mb-3">
            <button class="btn btn-primary" id="download-masterlist-btn">Download Masterlist</button>
        </div>

        <table class="table table-bordered align-middle caption-top" id="enlistment_uploads_table">
            <caption>XML Enlistment Uploads</caption>
            <thead class="table-primary">
                <tr>
                    <th scope="col">Upload ID</th>
                    <th scope="col">Date Uploaded</th>
                    <th scope="col">Range Date</th>
                    <th scope="col">Status</th>
                    <th scope="col">Imported At</th>
                    <th scope="col">Actions</th>
                </tr>
            </thead>
            <tbody id="uploads_tbody">
                <tr>
                    <td colspan="5" class="text-center text-muted">Loading...</td>
                </tr>
            </tbody>
        </table>

        <div id="pagination_container" class="mt-3"></div>
    </div>
@endsection

@push('modals')
    @include('modals.enlistment.parsed-data')
    @include('modals.enlistment.masterlist-details')
@endpush
