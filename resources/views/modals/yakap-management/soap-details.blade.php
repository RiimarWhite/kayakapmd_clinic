<div class="modal fade" data-bs-backdrop="static" id="soap-details-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-huge">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <span class="fa fa-solid fa-user-doctor"></span>
                </h2>
                <div class="mx-3">
                    <h4>Consultation</h4>
                    <span class="profileName"></span> <span class="fw-bold"> | </span> <span class="profileSex"></span>
                    <span class="fw-bold"> |
                    </span> <span class="profileAge"></span> <span class="fw-bold"> | </span> <span
                        class="profilePIN"></span>
                    <span class="fw-bold"> | </span>
                    <span class="profileEffYr"></span>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <style>
                .modal-huge {
                    max-width: 80%;
                    /* Covers 95% of screen width */
                    margin: 1.75rem auto;
                    /* Maintains standard top/bottom spacing */
                }

                /* Sticky positioning for tab navigation */
                #soap_tabs {
                    position: sticky;
                    top: 0;
                    background-color: #fff;
                    z-index: 10;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    gap: 0.5rem;
                }

                /* Equal width for all tab navigation items */
                #soap_tabs .nav-item {
                    flex: 1 1 0;
                }

                /* Add hover effect for tab buttons in profile details modal */
                #soap_tabs .nav-item .btn {
                    transition: all 0.2s ease;
                }

                #soap_tabs .nav-item .btn:hover {
                    background-color: rgba(13, 110, 253, 0.1);
                    /* bootstraps primary tint */
                    color: #0d6efd;
                }

                #soap_tabs .nav-item .btn.active,
                #soap_tabs .nav-item .btn:focus,
                #soap_tabs .nav-item .btn:active {
                    background-color: #0d6efd;
                    color: #fff;
                }
            </style>

            <div class="modal-body p-0">
                <!-- Sidebar -->
                <ul class="nav nav-pills p-2 d-flex justify-content-around" style="min-width: 220px;" id="soap_tabs"
                    role="tablist">
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center active text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#soap-client-profile" role="tab"
                            aria-controls="soap-client-profile" aria-selected="true">
                            1 <br>
                            Client Profile
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#subjective-history-of-illness" role="tab"
                            aria-controls="subjective-history-of-illness" aria-selected="true">
                            2 <br>
                            Subjective/History <br> of Illness
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#objective-physical-examination" role="tab"
                            aria-controls="objective-physical-examination" aria-selected="false">
                            3 <br> Objective/Physical <br> Examination
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#assessment-diagnosis" role="tab"
                            aria-controls="assessment-diagnosis" aria-selected="false">
                            4 <br>
                            Assessment/Diagnosis
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#plan-management" role="tab"
                            aria-controls="plan-management" aria-selected="false">
                            5 <br>
                            Plan/Management
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#laboratory-results" role="tab"
                            aria-controls="laboratory-results" aria-selected="false">
                            6 <br>
                            Laboratory Results
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2 last-tab" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#medicine" role="tab" aria-controls="medicine"
                            aria-selected="false">
                            7 <br>
                            Medicine
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content flex-grow-1 p-3" id="soap_tab_content">
                    <div class="tab-pane fade show active" id="soap-client-profile" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Client Profile</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.client-profile')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="subjective-history-of-illness" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Subjective/History of Illness</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.subjective-history-illness')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="objective-physical-examination" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Objective/Physical Examination</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.objective-physical-examination')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="assessment-diagnosis" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Assessment/Diagnosis</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.assessment-diagnosis')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="plan-management" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Plan/Management</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.plan-management')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="laboratory-results" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Laboratory Results</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.laboratory-results')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="medicine" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Medicine</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.soap-tabs.medicine')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer p-1">
                <button type="button" class="btn btn-primary" id="soap_next_btn">Next</button>
                <button type="button" class="btn btn-success" id="soap_save_profile_btn">Save
                    Consultation</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
