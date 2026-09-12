<div class="container-fluid p-0">
    <form id="medicineForm">
        <div class="card">
            <div class="card-body">
                <div class="alert alert-success fw-bold">
                    DRUG PRESCRIPTION
                    {{-- <div style="float:right;">
                        <span style="display:inline-block;">
                            <span>
                                <input type="radio" name="medsStatus" id="medsStatusYes" style="cursor: pointer; float: left;"
                                    value="Y" checked="checked" onclick="enableMedicine()">
                                <label for="medsStatusYes"
                                    style="margin: 4px 0px 0px 2px; font-weight: normal; cursor: pointer;font-weight: bold;">With
                                    prescribe drug/medicine</label>
                            </span>
                        </span>
                    </div> --}}
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dInstructionPhysician" class="form-label fw-bold" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Prescribing
                            Physician:</label>
                        <input type="text" name="dInstructionPhysician" id="dInstructionPhysician"
                            class="form-control text-uppercase" autocomplete="off" maxlength="100"
                            placeholder="NAME OF PRESCRIBING PHYSICIAN">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold" style="font-size: 0.85rem;"><span class="text-danger">*</span>
                            Is Drug/Medicine
                            dispensed?:</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dIsDispensed" id="dIsDispensedY"
                                    value="Y" checked>
                                <label class="form-check-label" for="dIsDispensedY">Yes</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="dIsDispensed" id="dIsDispensedN"
                                    value="N">
                                <label class="form-check-label" for="dIsDispensedN">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dDispensingPersonnel" class="form-label fw-bold" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Dispensing Personnel:</label>
                        <input type="text" name="dDispensingPersonnel" id="dDispensingPersonnel"
                            class="form-control text-uppercase" autocomplete="off" maxlength="100"
                            placeholder="NAME OF DISPENSING PERSONNEL">
                    </div>
                    <div class="col-md-3">
                        <label for="dDateDispensed" class="form-label fw-bold" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Dispense
                            Date:</label>
                        <input type="date" name="dDateDispensed" id="dDateDispensed"
                            class="datepicker form-control text-uppercase" autocomplete="off" maxlength="100"
                            placeholder="MM/DD/YYYY">
                    </div>
                </div>

                <hr>

                {{-- Drug/Medicine Section --}}
                <div class="row mb-2">
                    <div class="col-md-3">
                        <u class="fw-bold">DRUG/MEDICINE</u>
                    </div>
                    <div class="col-6 gap-3">
                        <input type="radio" class="form-check-input" id="philhealth" name="medSource"
                            value="philhealth" checked>
                        <label for="philhealth" class="form-check-label">PhilHealth</label>
                        <input type="radio" class="form-check-input" id="inhouse" name="medSource" value="inhouse">
                        <label for="inhouse" class="form-check-label" style="margin-right: 10px">In-House</label>
                    </div>
                    <div class="col-3">
                        <button class="btn btn-warning btn-sm" type="button" id="import-medicine-from-ledger-btn"><span
                                class="fa fa-solid fa-download"></span>Import from ledger</button>
                    </div>
                </div>

                <div class="row mb-3">
                    <div class="col-6">
                        <label for="pDrugCode" class="form-label fw-bold" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Drug/Medicine
                            [Complete Details]</label>
                        {{-- <select name="pDrugCode" id="pDrugCode" class="form-select" style="max-width: 450px;">
                            <option value="" selected disabled>Select Drug/Medicine</option>
                        </select> --}}
                        <select class="form-select select2-diagnosis" id="dMedicine" name="dMedicine"
                            style="width: 100%;">
                            <option value="">Select a medicine</option>
                        </select>
                    </div>
                </div>

                {{-- Drug Detail Selects --}}
                <div class="row mb-3 g-2">
                    <div class="col-md-3">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Generic Name</label>
                        <input type="text" name="gen_desc" id="gen_desc" class="form-control" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Salt</label>
                        <input type="text" name="salt_desc" id="salt_desc" class="form-control" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Strength</label>
                        <input type="text" name="strength_desc" id="strength_desc" class="form-control" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Form</label>
                        <input type="text" name="form_desc" id="form_desc" class="form-control" disabled>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Unit</label>
                        <input type="text" name="unit_desc" id="unit_desc" class="form-control" disabled>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fst-italic" style="font-size: 0.85rem;">Package</label>
                        <input type="text" name="package_desc" id="package_desc" class="form-control" disabled>
                    </div>
                </div>

                {{-- Other Medicine --}}
                <div class="row mb-2 d-none">
                    <div class="col-md-6">
                        <div class="form-check mb-1">
                            <input class="form-check-input" type="checkbox" name="chkOthMeds" id="chkOthMeds"
                                value="Y">
                            <label class="form-check-label fw-bold" for="chkOthMeds" style="font-size: 0.85rem;">
                                Other Drug/Medicine [If not available in the list of library]
                            </label>
                        </div>
                        <input type="text" name="dOtherMedicine" id="dOtherMedicine"
                            class="form-control text-uppercase" autocomplete="off" maxlength="500"
                            placeholder="Generic Name/ Salt/ Strength/ Form/ Unit/ Package" disabled>
                    </div>
                    <div class="col-md-3">
                        <label for="dOthMedDrugGrouping" class="form-label" style="font-size: 0.85rem;">Drug
                            Grouping</label>
                        <select name="dOthMedDrugGrouping" id="dOthMedDrugGrouping" class="form-select" disabled>
                            <option value="" selected disabled>SELECT DRUG GROUPING</option>
                            <option value="NCD">NCD</option>
                            <option value="ANTIBIOTIC">ANTIBIOTIC</option>
                            <option value="OTHERS">OTHERS</option>
                        </select>
                    </div>
                </div>

                {{-- Quantity & Price --}}
                <div class="row mb-3 g-2">
                    <div class="col-md-2">
                        <label for="dQuantity" class="form-label" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Quantity</label>
                        <input type="text" name="dQuantity" id="dQuantity" class="form-control"
                            autocomplete="off" maxlength="5">
                    </div>
                    <div class="col-md-3">
                        <label for="dActualUnitPrice" class="form-label" style="font-size: 0.85rem;"><span
                                class="text-danger">*</span> Actual Unit
                            Price</label>
                        <div class="input-group">
                            <span class="input-group-text">Php</span>
                            <input type="text" name="dActualUnitPrice" id="dActualUnitPrice" class="form-control"
                                autocomplete="off" maxlength="6">
                        </div>
                    </div>
                </div>

                <hr>

                {{-- Advise / Medicine Instruction --}}
                <div class="row mb-2">
                    <div class="col">
                        <span class="fw-bold">Advise</span>
                    </div>
                </div>

                <div class="table-responsive mb-3">
                    <table class="table table-bordered table-sm align-middle" style="font-size: 0.875rem;">
                        <thead class="table-light">
                            <tr>
                                <th colspan="3">Medicine Instruction</th>
                            </tr>
                            <tr>
                                <th>Quantity<span class="text-danger">*</span></th>
                                <th>Strength<span class="text-danger">*</span></th>
                                <th>Frequency<span class="text-danger">*</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <input type="text" name="dInstructionQuantity" id="dInstructionQuantity"
                                        class="form-control text-uppercase" autocomplete="off" maxlength="50">
                                </td>
                                <td>
                                    <input type="text" name="dInstructionStrength" id="dInstructionStrength"
                                        class="form-control text-uppercase" autocomplete="off" maxlength="100">
                                </td>
                                <td>
                                    <input type="text" name="dInstructionFrequency" id="dInstructionFrequency"
                                        class="form-control text-uppercase" autocomplete="off" maxlength="50">
                                </td>
                            </tr>
                            <tr>
                                <td colspan="3">
                                    <label for="advice_remarks" class="form-label mb-1">Remarks<span
                                            class="text-danger">*</span>:</label>
                                    <textarea name="advice_remarks" id="advice_remarks" class="form-control text-uppercase" autocomplete="off"
                                        rows="2" style="resize: none;"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                {{-- Add Medicine Button --}}
                <div class="mb-2">
                    <button type="button" name="btnAddMeds" id="btnAddMeds" class="btn btn-warning text-dark">
                        Add Medicine
                    </button>
                </div>

                <p class="fst-italic text-danger mb-2" style="font-size: 0.8rem;">
                    Click 'Add Medicine' button to add drug/medicine on the list.
                </p>

                {{-- Results Table --}}
                <div class="table-responsive">
                    <table id="tblResultsMeds" class="table table-bordered table-hover table-sm"
                        style="font-size: 0.8rem;">
                        <thead class="table-light text-center">
                            <tr>
                                <th colspan="5">List of Drug/Medicine</th>
                                <th colspan="3">Instruction</th>
                                <th colspan="2">Dispensing Section</th>
                                <th rowspan="2" style="vertical-align: middle;">Action</th>
                            </tr>
                            <tr>
                                <th style="vertical-align: middle;">Medicine<br>Strength/ Form/ Volume</th>
                                <th style="vertical-align: middle;">Drug Grouping<br>(For Other Medicine)</th>
                                <th style="vertical-align: middle;">Quantity</th>
                                <th style="vertical-align: middle;">Actual Unit Price</th>
                                <th style="vertical-align: middle;">Total Amount Price</th>
                                <th style="vertical-align: middle;">Quantity</th>
                                <th style="vertical-align: middle;">Strength</th>
                                <th style="vertical-align: middle;">Frequency</th>
                                <th style="vertical-align: middle;">Is Drug/Medicine dispensed?</th>
                                <th style="vertical-align: middle;">Dispensed Date</th>
                            </tr>
                        </thead>
                        <tbody id="tblBodyMeds">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </form>
</div>
