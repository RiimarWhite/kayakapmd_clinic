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

                <div class="d-flex gap-2">
                    <div class="w-100">
                        <label class="form-label" for="item_dscr">Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="item_dscr" id="item_dscr">
                    </div>

                    <div class="d-none w-50" id="refcode">
                        <label class="form-label" for="ref_code">PhilHealth Reference Code</label>
                        <input class="form-control" type="text" name="ref_code" id="ref_code">
                    </div>
                </div>

                <div class="d-none" id="additional_fields">
                    <div class="flex flex-fill">
                        <label class="form-label" for="item_add">Additional Description</label>
                        <input class="form-control" type="text" name="item_add" id="item_add">
                    </div>
                </div>

                <div class="d-none gap-2" id="drug_fields">
                    <div class="flex-fill">
                        <label class="form-label" for="drug_generic">Generic Name</label>
                        <input class="form-control" type="text" name="drug_generic" id="drug_generic">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="drug_brand">Brand</label>
                        <input class="form-control" type="text" name="drug_brand" id="drug_brand">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="drug_dosage">Dosage</label>
                        <input class="form-control" type="text" name="drug_dosage" id="drug_dosage">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="drug_group">Group</label>
                        <select class="form-select" name="drug_group" id="drug_group">
                            <option value="" disabled selected>-- Select --</option>
                            <option value="DRUGS AND MEDS">Drus & Medicine</option>
                            <option value="MEDICAL SUPPLIES">Medical Supplies</option>
                        </select>
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
