<div class="modal fade" data-bs-backdrop="static" id="profile-details-modal" tabindex="-1">
    <div class="modal-dialog modal-dialog-scrollable modal-huge">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">
                    <span class="fa fa-solid fa-user-doctor"></span>
                </h2>
                <div class="mx-3">
                    <h4>Health Screening and Assessment</h4>
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

                /* Add hover effect for tab buttons in profile details modal */

                #profile_tabs {
                    position: sticky;
                    top: 0;
                    background-color: #fff;
                    z-index: 10;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
                    gap: 0.5rem;
                }

                #profile_tabs .nav-item {
                    flex: 1 1 0;
                }

                #profile_tabs .nav-item .btn {
                    transition: all 0.2s ease;
                }

                #profile_tabs .nav-item .btn:hover {
                    background-color: rgba(13, 110, 253, 0.1);
                    /* bootstraps primary tint */
                    color: #0d6efd;
                }

                #profile_tabs .nav-item .btn.active,
                #profile_tabs .nav-item .btn:focus,
                #profile_tabs .nav-item .btn:active {
                    background-color: #0d6efd;
                    color: #fff;
                }
            </style>

            <div class="modal-body p-0">
                <!-- Sidebar -->
                <ul class="nav nav-pills p-2 gap-2 d-flex justify-content-around" style="min-width: 220px;"
                    id="profile_tabs" role="tablist">
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center active text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#client_profile" role="tab"
                            aria-controls="client_profile" aria-selected="true">
                            1 <br>
                            Client Profile
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#medical_history" role="tab"
                            aria-controls="medical_history" aria-selected="false">
                            2 <br> Surgical History
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2"
                            style="height: 80px; font-size: small;" data-bs-toggle="pill"
                            data-bs-target="#family_history" role="tab" aria-controls="family_history"
                            aria-selected="false">
                            3 <br>
                            Family & Personal History
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#immunizations" role="tab"
                            aria-controls="immunizations" aria-selected="false">
                            4 <br>
                            Immunizations
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#ob_gyne_history" role="tab"
                            aria-controls="ob_gyne_history" aria-selected="false">
                            5 <br>
                            OB-Gyne History
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#pertinent_physical" role="tab"
                            aria-controls="pertinent_physical" aria-selected="false">
                            6 <br>
                            Pertinent PE <br> Findings
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="w-100 last-tab tab-btn text-center text-dark btn gap-2" style="height: 80px"
                            data-bs-toggle="pill" data-bs-target="#ncd_high_risk" role="tab"
                            aria-controls="ncd_high_risk" aria-selected="false">
                            7 <br>
                            NCD High-Risk <br> Assessment
                        </button>
                    </li>
                </ul>

                <!-- Tab Content -->
                <div class="tab-content flex-grow-1 p-3" id="admin_sidebarContent">
                    <div class="tab-pane fade show active" id="client_profile" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Client Profile</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.client-profile')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="medical_history" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Medical & Surgical History</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.medical-history')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="family_history" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Family & Personal History</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.family-history')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="immunizations" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Immunizations</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.immunizations')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ob_gyne_history" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">OB-Gyne History</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.ob-gyne')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="pertinent_physical" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">Pertinent Physical Examination Findings</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.pertinent-physical-examination-findings')
                            </div>
                        </div>
                    </div>
                    <div class="tab-pane fade" id="ncd_high_risk" role="tabpanel">
                        <div class="card">
                            <div class="card-header bg-success text-white">
                                <h5 class="mb-0">NCD High-Risk Assessment</h5>
                            </div>
                            <div class="card-body border border-success">
                                @include('components.yakap-management.profile-tabs.ncd-highrisk-assessment')
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer p-1">
                <button type="button" class="btn btn-primary" id="yakap_next_btn">Next</button>
                <button type="button" class="btn btn-success" id="yakap_save_profile_btn">Save
                    Profile</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
            </div>
        </div>
    </div>
</div>
