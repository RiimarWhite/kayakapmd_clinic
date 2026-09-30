<div class="modal fade" data-bs-backdrop="static" id="edit_item_modal" data-bs-keyboard="false"  tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-pen-to-square"></span> Edit Item/Service</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-4 p-4" id="edit_item_form">
                @csrf

                <input type="hidden" name="prodcode" id="prodcode">

                <div class="w-25">
                    <label class="form-label" for="eitem_group">Category</label>
                    <select class="form-select" name="eitem_group" id="eitem_group">
                        <option value="" selected disabled>-- Select --</option>
                        <option value="DRUGS AND MEDS">Drugs & Medicines</option>
                        <option value="SUPPLIES">Supplies</option>
                        <option value="PROCEDURES">Procedures</option>
                        <option value="DIAGNOSTIC">Diagnostic</option>
                        <option value="IMAGING">Imaging</option>
                        <option value="PROFESSIONAL FEE">Professional Fee</option>
                    </select>
                </div>

                <!-- Detailed Comment: Drug-specific fields placed ABOVE Item Name & PhilHealth Code when Category is Drugs & Medicine -->
                <div class="d-none flex-column gap-3" id="edrug_fields">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="edrug_generic">Generic Name <span class="text-danger">*</span></label>
                            <select class="form-select" name="edrug_generic" id="edrug_generic" style="width: 100%;">
                                <option value="">-- Search Generic Name --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="edrug_brand">Brand Name</label>
                            <input class="form-control" type="text" name="edrug_brand" id="edrug_brand" placeholder="e.g. Biogesic">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="edrug_dosage">Dosage</label>
                            <input class="form-control" type="text" name="edrug_dosage" id="edrug_dosage" placeholder="e.g. 500mg">
                        </div>
                    </div>

                    <!-- Detailed Comment: PhilHealth Gamot Essential (PGE) checkbox and Dosage Form dropdown with Custom option -->
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="edosage_form">Dosage Form</label>
                            <select class="form-select" name="edosage_form" id="edosage_form">
                                <option value="N/A" selected>N/A</option>
                                <option value="Capsule">Capsule</option>
                                <option value="IV">IV</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Custom">Custom Field</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" name="ecustom_dosage_form" id="ecustom_dosage_form" placeholder="Enter custom dosage form">
                        </div>

                        <div class="col-md-4 pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="ephilhealth_gamot_essential" id="ephilhealth_gamot_essential" value="1">
                                <label class="form-check-label fw-bold" for="ephilhealth_gamot_essential">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> PhilHealth Gamot Essential
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Comment: Category Group container dynamically populated from stocks_groupings table -->
                <div class="d-none row g-2" id="egroup_container">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="edrug_group">Group <span class="text-muted small fw-normal">(from Grouping Management)</span></label>
                        <select class="form-select" name="edrug_group" id="edrug_group">
                            <option value="" disabled selected>-- Select Group --</option>
                        </select>
                    </div>
                </div>

                <!-- Detailed Comment: Item Name and PhilHealth Reference Code positioned below drug fields -->
                <div class="d-flex gap-2" id="ename_and_refcode_row">
                    <div class="w-100" id="ename_container">
                        <label class="form-label fw-bold" for="eitem_dscr">Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="eitem_dscr" id="eitem_dscr" placeholder="Item or Service Name">
                    </div>

                    <div class="d-none w-50" id="erefcode">
                        <label class="form-label fw-bold" for="eref_code">PhilHealth Reference Code</label>
                        <input class="form-control" type="text" name="eref_code" id="eref_code" placeholder="PHIC Code">
                    </div>
                </div>

                <div class="d-flex gap-2" id="price_fields">
                    <div class="flex-fill">
                        <label class="form-label" for="eprice_regular">Regular Price</label>
                        <input class="form-control" type="number" name="eprice_regular" id="eprice_regular" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="eprice_phic">PHIC Price</label>
                        <input class="form-control" type="number" name="eprice_phic" id="eprice_phic" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="eprice_hmo">HMO Price</label>
                        <input class="form-control" type="number" name="eprice_hmo" id="eprice_hmo" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="eprice_others">Price <span class="text-secondary">(Others)</span></label>
                        <input class="form-control" type="number" name="eprice_others" id="eprice_others" min="0.00" placeholder="0.00">
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="edit_item">Save Updates</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
