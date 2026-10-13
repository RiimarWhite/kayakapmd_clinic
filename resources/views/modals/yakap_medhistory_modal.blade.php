<!-- Detailed Comment: Dedicated PhilHealth Yakap Medical History & Profiling Modal for Secretary & Admin Consoles.
     Extracts past medical conditions from dw_lib_mdiseases and dynamic surgical operations, persisting to
     dd_profile_medhist, dd_profile_mhspecific, dd_profile_surghist, and dd_profile_1. -->
<div class="modal fade" data-bs-backdrop="static" id="yakap_medhistory_modal" tabindex="-1" aria-labelledby="yakapMedHistoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-file-medical fa-lg"></i>
                    <div>
                        <h5 class="modal-title fw-bold m-0" id="yakapMedHistoryModalLabel">PhilHealth Yakap Medical History &amp; Profiling</h5>
                        <small class="text-white-50">Health Screening &amp; Past Clinical Assessment</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Patient Header Summary Strip -->
            <div class="bg-light border-bottom p-3">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-4">
                        <small class="text-muted d-block">Patient Name</small>
                        <span class="fw-bold text-primary" id="yakap_modal_pxname">--</span>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted d-block">PhilHealth / PIN</small>
                        <span class="fw-semibold text-dark" id="yakap_modal_pin">--</span>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted d-block">Patient Ref No</small>
                        <span class="fw-semibold text-dark" id="yakap_modal_pxrefno">--</span>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted d-block">Consultation Ref</small>
                        <span class="badge bg-secondary" id="yakap_modal_consultref">--</span>
                    </div>
                    <div class="col-6 col-md-2">
                        <small class="text-muted d-block">Profile Trans No</small>
                        <span class="badge bg-info text-dark" id="yakap_modal_transno">--</span>
                    </div>
                </div>
            </div>

            <div class="modal-body p-3">
                <div class="row g-3">
                    <!-- Past Medical History Conditions Form -->
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-header bg-danger text-white py-2 fw-bold">
                                <i class="fa-solid fa-heart-pulse me-1"></i> PAST MEDICAL CONDITIONS
                            </div>
                            <div class="card-body p-3">
                                <form id="yakap_medhist_form">
                                    <p class="text-muted small mb-2">
                                        Check all conditions diagnosed or reported. If a condition is checked, provide specifics if available.
                                    </p>
                                    <div class="disease-list-container" style="max-height: 440px; overflow-y: auto; padding-right: 5px;">
                                        @foreach (($mDiseaseLibrary ?? []) as $disease)
                                            @if ($disease->mdisease_code !== '999')
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input yakap-disease-checkbox" type="checkbox"
                                                        id="yakapDiseaseCode{{ $disease->mdisease_code }}"
                                                        name="chkMedHistDiseases[{{ $disease->mdisease_code }}][selected]"
                                                        value="{{ $disease->mdisease_code }}">
                                                    <label class="form-check-label fw-semibold text-dark" for="yakapDiseaseCode{{ $disease->mdisease_code }}">
                                                        {{ $disease->mdisease_desc }}
                                                    </label>
                                                </div>
                                                <div class="mb-2 ps-3" id="divYakapDiseaseCode{{ $disease->mdisease_code }}" style="display: none;">
                                                    <label for="yakapSpecificCode{{ $disease->mdisease_code }}" class="form-label small text-muted m-0">
                                                        Specify details for {{ $disease->mdisease_desc }}:
                                                    </label>
                                                    <input type="text" class="form-control form-control-sm"
                                                        id="yakapSpecificCode{{ $disease->mdisease_code }}"
                                                        name="chkMedHistDiseases[{{ $disease->mdisease_code }}][specify]"
                                                        placeholder="Specify {{ $disease->mdisease_desc }} details..."
                                                        maxlength="2000">
                                                </div>
                                            @endif
                                        @endforeach

                                        <!-- None Option (Code 999) -->
                                        <hr class="my-2">
                                        <div class="form-check">
                                            <input class="form-check-input yakap-disease-checkbox" type="checkbox"
                                                id="yakapChkMedHistNone"
                                                name="chkMedHistDiseases[999][selected]"
                                                value="999">
                                            <label class="form-check-label fw-bold text-success" for="yakapChkMedHistNone">
                                                <i class="fa-solid fa-circle-check me-1"></i> None (No Known Medical Conditions)
                                            </label>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Past Surgical History Form -->
                    <div class="col-12 col-lg-6">
                        <div class="card h-100 border shadow-sm">
                            <div class="card-header bg-success text-white py-2 fw-bold">
                                <i class="fa-solid fa-scissors me-1"></i> PAST SURGICAL OPERATIONS
                            </div>
                            <div class="card-body p-3">
                                <form id="yakap_surghist_form">
                                    <p class="text-muted small mb-2">
                                        Enter historical surgical operations and operation dates below. Click Add to append rows.
                                    </p>
                                    <div class="table-responsive" style="max-height: 440px; overflow-y: auto;">
                                        <table class="table table-sm table-bordered align-middle" id="tblYakapMedHistOpHist">
                                            <thead class="table-light">
                                                <tr>
                                                    <th scope="col" style="min-width: 180px;">Operation / Surgical Procedure</th>
                                                    <th scope="col" style="width: 130px;">Date</th>
                                                    <th scope="col" class="text-center" style="width: 75px;">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr data-empty="1">
                                                    <td>
                                                        <input type="text" class="form-control form-control-sm"
                                                            name="surgicalHistory[0][operation]"
                                                            placeholder="e.g. Appendectomy, C-Section..."
                                                            maxlength="2000">
                                                    </td>
                                                    <td>
                                                        <input type="date" class="form-control form-control-sm"
                                                            name="surgicalHistory[0][date]">
                                                    </td>
                                                    <td class="text-center">
                                                        <button type="button" class="btn btn-sm btn-success w-100"
                                                            onclick="addYakapOperationHist(this)" title="Add operation row">
                                                            <i class="fa-solid fa-plus"></i> Add
                                                        </button>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light p-2 d-flex justify-content-between align-items-center">
                <span class="text-muted small">
                    <i class="fa-solid fa-circle-info text-primary me-1"></i> Changes will synchronize with PhilHealth Yakap profiling records.
                </span>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary fw-bold" id="btn_save_yakap_medhistory">
                        <i class="fa-solid fa-floppy-disk me-1"></i> Save Yakap History
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
