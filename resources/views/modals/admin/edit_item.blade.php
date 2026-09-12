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

                <div class="d-flex gap-2">
                    <div class="w-100">
                        <label class="form-label" for="eitem_dscr">Name <span class="text-danger">*</span></label>
                        <input class="form-control" type="text" name="eitem_dscr" id="eitem_dscr">
                    </div>

                    <div class="d-none w-50" id="erefcode">
                        <label class="form-label" for="eref_code">PhilHealth Reference Code</label>
                        <input class="form-control" type="text" name="eref_code" id="eref_code">
                    </div>
                </div>

                <div class="d-none gap-2" id="edrug_fields">
                    <div class="flex-fill">
                        <label class="form-label" for="edrug_generic">Generic Name</label>
                        <input class="form-control" type="text" name="edrug_generic" id="edrug_generic">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="edrug_brand">Brand</label>
                        <input class="form-control" type="text" name="edrug_brand" id="edrug_brand">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="edrug_dosage">Dosage</label>
                        <input class="form-control" type="text" name="edrug_dosage" id="edrug_dosage">
                    </div>

                    <div class="flex-fill">
                        <label class="form-label" for="edrug_group">Group</label>
                        <select class="form-select" name="edrug_group" id="edrug_group">
                            <option value="" disabled selected>-- Select --</option>
                            <option value="DRUGS AND MEDS">Drus & Medicine</option>
                            <option value="MEDICAL SUPPLIES">Medical Supplies</option>
                        </select>
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
