<div class="container-fluid p-0">
    <form id="planManagementForm">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    PLAN/MANAGEMENT
                </div>

                {{-- A. Laboratory/Imaging Examination --}}
                <div class="row mb-3">
                    <div class="col">
                        <h6 class="fw-bold"><u><span class="text-danger">*</span>A. Laboratory/Imaging Examination</u>
                        </h6>
                    </div>
                </div>

                <p class="text-muted fst-italic mb-3" style="font-size: 0.8rem;">
                    Laboratory with <span class="badge bg-warning text-dark">orange</span> color is from the stocks
                    ledger.
                </p>

                <div class="table-responsive">
                    <table class="table table-bordered table-sm align-middle" style="font-size: 0.875rem;">
                        <colgroup>
                            <col style="width: 43%;">
                            <col style="width: 25%;">
                            <col style="width: 32%;">
                        </colgroup>
                        <thead class="table-light text-center">
                            <tr>
                                <th>Laboratory/Imaging</th>
                                <th>Doctor Recommendation</th>
                                <th>Client</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($diagnosisLibrary as $diagnosis)
                                <tr>
                                    <td>
                                        {{ $diagnosis->diagnostic_desc }}
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_doctor_reco[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorYes"
                                                    value="Y">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorYes">Yes</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_doctor_reco[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorNo"
                                                    value="N">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorNo">No</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_doctor_reco[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorUnselect"
                                                    value="X">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_doctorUnselect">Deselect</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-wrap gap-3">
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_patient[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_patientRQ"
                                                    value="RQ">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_patientRQ">Request</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_patient[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_patientRF"
                                                    value="RF">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_patientRF">Refuse</label>
                                            </div>
                                            <div class="form-check">
                                                <input class="form-check-input" type="radio"
                                                    name="diagnostic_patient[{{ $diagnosis->diagnostic_id }}]"
                                                    id="diagnostic_{{ $diagnosis->diagnostic_id }}_patientUnselect"
                                                    value="XX">
                                                <label class="form-check-label"
                                                    for="diagnostic_{{ $diagnosis->diagnostic_id }}_patientUnselect">Deselect</label>
                                            </div>
                                        </div>
                                    </td>
                                    <td id="status_{{ $diagnosis->diagnostic_id }}"></td>
                                </tr>
                            @endforeach

                            <tr>
                                <td colspan="3">
                                    <input type="text" name="diagnostic_oth_remarks" id="diagnostic_oth_remarks1"
                                        class="form-control text-uppercase" style="width: 250px;" autocomplete="off"
                                        disabled placeholder="Other Diagnostic Exam">
                                    <p class="text-muted fst-italic mt-3 mb-1" style="font-size: 0.8rem;">
                                        Asterisk (*) refers to the services recommended by the Guidelines
                                        (AO No. 2017-0012: Guidelines on the Adoptions of Baseline Primary Health Care
                                        Guarantees for All Filipinos)
                                    </p>
                                    <p class="text-muted fst-italic mb-0" style="font-size: 0.8rem;">
                                        "Deselect" option is added to unselect/uncheck the checked option in Doctor
                                        Recommendation and Client Request or Refuse to avoid reloading of the page
                                    </p>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <h6 class="fw-bold"><u><span class="text-danger">*</span>B. Management (check if done)</u></h6>
                        @foreach ($managementLibrary as $management)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                    name="management[{{ $management->management_id }}]"
                                    id="management_{{ $management->management_id }}"
                                    value="{{ $management->management_id }}">
                                <label class="form-check-label" for="management_{{ $management->management_id }}">
                                    {{ $management->manadement_desc }}
                                </label>
                            </div>
                        @endforeach
                        <input type="text" class="form-control" id="management_oth_remarks1"
                            name="management_oth_remarks1" placeholder="Other Management" disabled>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col">
                        <h6 class="fw-bold"><u><span class="text-danger">*</span>C. Doctor's Advice</u></h6>
                        <textarea class="form-control" id="dRemarks" name="dRemarks" rows="3"></textarea>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
