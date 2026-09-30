<div class="modal fade" data-bs-backdrop="static" id="add_item_modal" data-bs-keyboard="false"  tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title"><span class="fa fa-solid fa-plus"></span> Add Item/Service</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form class="modal-body d-flex flex-column gap-4 p-4" id="add_item_form">
                @csrf

                <div class="d-flex justify-content-between">
                    <div class="d-flex align-items-center gap-5">
                        <div>
                            <label class="form-label" for="item_group">Category</label>
                            <select class="form-select" name="item_group" id="item_group">
                                <option value="" selected disabled>-- Select --</option>
                                <option value="DRUGS AND MEDS">Drugs & Medicines</option>
                                <option value="SUPPLIES">Supplies</option>
                                <option value="PROCEDURES">Procedures</option>
                                <option value="DIAGNOSTIC">Diagnostic</option>
                                <option value="IMAGING">Imaging</option>
                                <option value="PROFESSIONAL FEE">Professional Fee</option>
                            </select>
                        </div>
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" name="is_inventory" id="is_inventory">
                            <label class="form-check-label" for="is_inventory">Is Inventory Item</label>
                        </div>
                    </div>
                    <div class="d-none" id="quantity-entry">
                        <label class="form-label" for="item-quantity">Quantity</label>
                        <input class="form-control" type="number" name="item_quantity" id="item_quantity" value="1">
                    </div>
                </div>

                <!-- Detailed Comment: Drug-specific fields placed ABOVE Item Name & PhilHealth Code when Category is Drugs & Medicine -->
                <div class="d-none flex-column gap-3" id="drug_fields">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold" for="drug_generic">Generic Name <span class="text-danger">*</span></label>
                            <select class="form-select" name="drug_generic" id="drug_generic" style="width: 100%;">
                                <option value="">-- Search Generic Name --</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="drug_brand">Brand Name</label>
                            <input class="form-control" type="text" name="drug_brand" id="drug_brand" placeholder="e.g. Biogesic">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-bold" for="drug_dosage">Dosage</label>
                            <input class="form-control" type="text" name="drug_dosage" id="drug_dosage" placeholder="e.g. 500mg">
                        </div>
                    </div>

                    <!-- Detailed Comment: PhilHealth Gamot Essential (PGE) checkbox and Dosage Form dropdown with Custom option -->
                    <div class="row g-2 align-items-center">
                        <div class="col-md-4">
                            <label class="form-label fw-bold" for="dosage_form">Dosage Form</label>
                            <select class="form-select" name="dosage_form" id="dosage_form">
                                <option value="N/A" selected>N/A</option>
                                <option value="Capsule">Capsule</option>
                                <option value="IV">IV</option>
                                <option value="Tablet">Tablet</option>
                                <option value="Custom">Custom Field</option>
                            </select>
                            <input type="text" class="form-control mt-2 d-none" name="custom_dosage_form" id="custom_dosage_form" placeholder="Enter custom dosage form">
                        </div>

                        <div class="col-md-4 pt-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="philhealth_gamot_essential" id="philhealth_gamot_essential" value="1">
                                <label class="form-check-label fw-bold" for="philhealth_gamot_essential">
                                    <i class="fa-solid fa-shield-halved text-success me-1"></i> PhilHealth Gamot Essential
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Comment: Category Group container dynamically populated from stocks_groupings table -->
                <div class="d-none row g-2" id="group_container">
                    <div class="col-md-6">
                        <label class="form-label fw-bold" for="drug_group">Group <span class="text-muted small fw-normal">(from Grouping Management)</span></label>
                        <select class="form-select" name="drug_group" id="drug_group">
                            <option value="" disabled selected>-- Select Group --</option>
                        </select>
                    </div>
                </div>

                <!-- Detailed Comment: Item Name and PhilHealth Reference Code positioned below drug fields -->
                <div class="d-flex gap-2" id="name_and_refcode_row">
                    <div class="w-100" id="name_container">
                        <label class="form-label fw-bold" for="item_dscr">Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="item_dscr" id="item_dscr" placeholder="Item or Service Name">
                    </div>

                    <div class="d-none w-50" id="refcode">
                        <label class="form-label fw-bold" for="ref_code">PhilHealth Reference Code</label>
                        <input class="form-control" type="text" name="ref_code" id="ref_code" placeholder="PHIC Code">
                    </div>
                </div>

                <div class="d-none" id="additional_fields">
                    <div class="flex flex-fill">
                        <label class="form-label" for="item_add">Additional Description</label>
                        <input class="form-control" type="text" name="item_add" id="item_add">
                    </div>
                </div>

                <div class="d-flex gap-2" id="price_fields">
                    <div class="flex-fill">
                        <label class="form-label" for="price_regular">Regular Price</label>
                        <input class="form-control" type="number" name="price_regular" id="price_regular" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="price_phic">PHIC Price</label>
                        <input class="form-control" type="number" name="price_phic" id="price_phic" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="price_hmo">HMO Price</label>
                        <input class="form-control" type="number" name="price_hmo" id="price_hmo" min="0.00" placeholder="0.00">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="price_others">Price <span class="text-secondary">(Others)</span></label>
                        <input class="form-control" type="number" name="price_others" id="price_others" min="0.00" placeholder="0.00">
                    </div>
                </div>
            </form>

            <div class="modal-footer">
                <button type="button" class="btn btn-success" id="save_item">Add Item</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
