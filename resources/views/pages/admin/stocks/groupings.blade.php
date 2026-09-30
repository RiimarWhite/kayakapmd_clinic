@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/admin/stocks/groupings.js')
@endpush

@section('content')
    <div class="card h-100 p-4">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="{{ route('admin.stocks.management') }}">Stocks & Services</a></li>
                        <li class="breadcrumb-item active" aria-current="page">Grouping Management</li>
                    </ol>
                </nav>
                <h1 class="m-0 fs-3">Category Grouping Management</h1>
            </div>
            <div>
                <a href="{{ route('admin.stocks.management') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="fa-solid fa-arrow-left"></i> Back to Stocks Management
                </a>
            </div>
        </div>
        <hr>

        <!-- Category filter buttons & Add button -->
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div class="btn-group btn-group-sm flex-wrap" role="group" id="grouping_category_filter">
                <button type="button" class="btn btn-outline-primary active" data-category="ALL">All Categories</button>
                <button type="button" class="btn btn-outline-primary" data-category="DRUGS AND MEDS">Drugs & Meds</button>
                <button type="button" class="btn btn-outline-primary" data-category="IMAGING">Imaging</button>
                <button type="button" class="btn btn-outline-primary" data-category="SUPPLIES">Supplies</button>
                <button type="button" class="btn btn-outline-primary" data-category="PROCEDURES">Procedures</button>
                <button type="button" class="btn btn-outline-primary" data-category="DIAGNOSTIC">Diagnostic</button>
                <button type="button" class="btn btn-outline-primary" data-category="PROFESSIONAL FEE">Professional Fee</button>
            </div>

            <button class="btn btn-sm btn-success" data-bs-toggle="modal" data-bs-target="#add_grouping_modal" id="btn_open_add_grouping">
                <i class="fa-solid fa-plus"></i> Add Grouping
            </button>
        </div>

        <!-- Groupings Data Table -->
        <div class="table-responsive">
            <table class="table table-sm table-bordered table-hover align-middle caption-top w-100" id="groupings_table">
                <caption>Masterlist of Category-Specific Groupings</caption>
                <thead class="table-light">
                    <tr>
                        <th scope="col" style="width: 100px;" class="text-center">Actions</th>
                        <th scope="col">Category</th>
                        <th scope="col">Group Name</th>
                        <th scope="col">Group Code</th>
                        <th scope="col">Description</th>
                        <th scope="col" style="width: 90px;" class="text-center">Status</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
@endsection

@push('modals')
    <!-- Detailed Comment: Add Grouping Modal -->
    <div class="modal fade" id="add_grouping_modal" tabindex="-1" aria-labelledby="addGroupingModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addGroupingModalLabel"><i class="fa-solid fa-folder-plus text-success me-1"></i> Add Category Grouping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="add_grouping_form">
                    @csrf
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label fw-bold" for="new_group_category">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" id="new_group_category" required>
                                <option value="" disabled selected>-- Select Category --</option>
                                <option value="DRUGS AND MEDS">Drugs & Medicines</option>
                                <option value="IMAGING">Imaging</option>
                                <option value="SUPPLIES">Supplies</option>
                                <option value="PROCEDURES">Procedures</option>
                                <option value="DIAGNOSTIC">Diagnostic</option>
                                <option value="PROFESSIONAL FEE">Professional Fee</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-bold" for="new_group_name">Group Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="group_name" id="new_group_name" placeholder="e.g. xray, mri, ct scan" required>
                            <small class="text-muted">Descriptive grouping name used in item modal dropdowns.</small>
                        </div>
                        <div>
                            <label class="form-label" for="new_group_description">Description</label>
                            <textarea class="form-control" name="description" id="new_group_description" rows="2" placeholder="Optional description or guidelines"></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-bold" for="new_group_status">Status</label>
                            <select class="form-select" name="status" id="new_group_status">
                                <option value="ACTIVE" selected>Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="btn_save_grouping"><i class="fa-solid fa-floppy-disk me-1"></i> Save Grouping</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Detailed Comment: Edit Grouping Modal -->
    <div class="modal fade" id="edit_grouping_modal" tabindex="-1" aria-labelledby="editGroupingModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editGroupingModalLabel"><i class="fa-solid fa-pen-to-square text-primary me-1"></i> Edit Category Grouping</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="edit_grouping_form">
                    @csrf
                    <input type="hidden" name="id" id="edit_group_id">
                    <input type="hidden" name="group_code" id="edit_group_code">
                    <div class="modal-body d-flex flex-column gap-3">
                        <div>
                            <label class="form-label fw-bold" for="edit_group_category">Category <span class="text-danger">*</span></label>
                            <select class="form-select" name="category" id="edit_group_category" required>
                                <option value="DRUGS AND MEDS">Drugs & Medicines</option>
                                <option value="IMAGING">Imaging</option>
                                <option value="SUPPLIES">Supplies</option>
                                <option value="PROCEDURES">Procedures</option>
                                <option value="DIAGNOSTIC">Diagnostic</option>
                                <option value="PROFESSIONAL FEE">Professional Fee</option>
                            </select>
                        </div>
                        <div>
                            <label class="form-label fw-bold" for="edit_group_name">Group Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="group_name" id="edit_group_name" required>
                        </div>
                        <div>
                            <label class="form-label" for="edit_group_description">Description</label>
                            <textarea class="form-control" name="description" id="edit_group_description" rows="2"></textarea>
                        </div>
                        <div>
                            <label class="form-label fw-bold" for="edit_group_status">Status</label>
                            <select class="form-select" name="status" id="edit_group_status">
                                <option value="ACTIVE">Active</option>
                                <option value="INACTIVE">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="btn_update_grouping"><i class="fa-solid fa-floppy-disk me-1"></i> Update Grouping</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endpush
