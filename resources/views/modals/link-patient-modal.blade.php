<div class="modal fade" data-bs-backdrop="static" id="linkPatientModal" tabindex="-1"
    aria-labelledby="linkPatientModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><i class="fa-solid fa-link"></i> Link Patient</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <div class="mb-3">
                    <label for="patient_search_input" class="form-label fw-bold">Search Patient by Name</label>
                    <input type="text" class="form-control" id="patient_search_input"
                        placeholder="Enter patient name (at least 2 characters)..." autocomplete="off">
                    <small class="text-muted">Search by first name, last name, middle name, or full name</small>
                </div>

                <div id="search_results_container">
                    <div class="alert alert-info text-center" id="no_search_message">
                        <i class="fa-solid fa-magnifying-glass"></i> Enter a patient name to search
                    </div>

                    <div class="table-responsive d-none" id="results_table_container">
                        <table class="table table-sm table-bordered table-hover align-middle"
                            id="patient_search_results_table">
                            <thead class="table-light">
                                <tr>
                                    <th>Full Name</th>
                                    <th>Mobile Number</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Results will be populated here by JavaScript -->
                            </tbody>
                        </table>

                        <div id="pagination_links" class="d-flex justify-content-center mt-3">
                            <!-- Pagination links will be populated here by JavaScript -->
                        </div>
                    </div>

                    <div class="alert alert-warning text-center d-none" id="no_results_message">
                        <i class="fa-solid fa-circle-exclamation"></i> No patients found matching your search
                    </div>

                    <div class="text-center d-none" id="search_loading">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-2">Searching...</p>
                    </div>
                </div>

                <div class="alert alert-danger d-none mt-3" id="link_error_message" role="alert">
                    <!-- Error messages will be displayed here -->
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
