<div class="container-fluid p-0">
    <form id="laboratoryResults">
        <div class="alert alert-success fw-bold mb-3" role="alert">LABORATORY RESULTS</div>
        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="1">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>CBC w/ platelet count</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_1_status" id="diagnostic_1_done"
                            value="D">
                        <label class="form-check-label" for="diagnostic_1_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_1_status"
                            id="diagnostic_1_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_1_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_1_status"
                            id="diagnostic_1_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_1_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_1_status"
                            id="diagnostic_1_waived" value="W">
                        <label class="form-check-label" for="diagnostic_1_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_1_status" id="diagnostic_1_void"
                            value="V">
                        <label class="form-check-label" for="diagnostic_1_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="1">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_1_lab_exam"
                                    id="diagnostic_1_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_1_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_1_lab_exam"
                                    id="diagnostic_1_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_1_lab_exam_out">Partner Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_1_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_1_accre_diag_fac" id="diagnostic_1_accre_diag_fac"
                                class="form-control text-uppercase" autocomplete="off"
                                placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_1_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_1_lab_exam_date" id="diagnostic_1_lab_exam_date"
                                class="form-control text-uppercase" autocomplete="off" placeholder="mm/dd/yyyy"
                                maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_1_lab_fee">Laboratory/Imaging
                                Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_1_lab_fee" id="diagnostic_1_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle">
                        <tbody>
                            <tr>
                                <td class="w-25"><label class="form-label mb-0"
                                        for="diagnostic_1_hematocrit">Hematocrit</label></td>
                                <td>
                                    <input type="text" name="diagnostic_1_hematocrit" id="diagnostic_1_hematocrit"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">
                                    <span class="small">%</span>
                                    <span id="diagnostic_1_normalHct" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_1_aboveHct" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_1_belowHct" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0"
                                        for="diagnostic_1_hemoglobin_gdL">Hemoglobin</label></td>
                                <td>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-auto">
                                            <input type="text" name="diagnostic_1_hemoglobin_gdL"
                                                id="diagnostic_1_hemoglobin_gdL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15">
                                            <span class="small">g/dL</span>
                                            <span id="diagnostic_1_normalHgb"
                                                class="text-danger small d-none">Normal</span>
                                            <span id="diagnostic_1_aboveHgb" class="text-danger small d-none">Above
                                                Normal</span>
                                            <span id="diagnostic_1_belowHgb" class="text-danger small d-none">Below
                                                Normal</span>
                                        </div>
                                        <div class="col-auto">
                                            <input type="text" name="diagnostic_1_hemoglobin_mmolL"
                                                id="diagnostic_1_hemoglobin_mmolL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15">
                                            <span class="small">mmol/L</span>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_mhc_pgcell">MCH</label></td>
                                <td>
                                    <div class="row g-2">
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mhc_pgcell"
                                                id="diagnostic_1_mhc_pgcell"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> pg/cell</span></div>
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mhc_fmolcell"
                                                id="diagnostic_1_mhc_fmolcell"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> fmol/cell</span></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_mchc_gHbdL">MCHC</label></td>
                                <td>
                                    <div class="row g-2">
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mchc_gHbdL"
                                                id="diagnostic_1_mchc_gHbdL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> g Hb/dL</span></div>
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mchc_mmolHbL"
                                                id="diagnostic_1_mchc_mmolHbL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> mmol Hb/L</span></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_mcv_um">MCV</label></td>
                                <td>
                                    <div class="row g-2">
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mcv_um"
                                                id="diagnostic_1_mcv_um"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> um^3</span></div>
                                        <div class="col-auto"><input type="text" name="diagnostic_1_mcv_fL"
                                                id="diagnostic_1_mcv_fL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> fL</span></div>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_wbc_cellsmmuL">WBC</label></td>
                                <td>
                                    <div class="row g-2">
                                        <div class="col-auto"><input type="text" name="diagnostic_1_wbc_cellsmmuL"
                                                id="diagnostic_1_wbc_cellsmmuL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 9rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> x1,000 cells/mm^3uL</span></div>
                                        <div class="col-auto"><input type="text" name="diagnostic_1_wbc_cellsL"
                                                id="diagnostic_1_wbc_cellsL"
                                                class="form-control form-control-sm text-uppercase"
                                                style="max-width: 9rem;" autocomplete="off" maxlength="15"><span
                                                class="small"> x10^9 cells/L</span></div>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <p class="fw-bold mt-2 mb-1">Leukocyte differential</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle">
                        <tbody>
                            <tr>
                                <td class="w-25"><label class="form-label mb-0"
                                        for="diagnostic_1_myelocyte">Myelocyte</label></td>
                                <td><input type="text" name="diagnostic_1_myelocyte" id="diagnostic_1_myelocyte"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span></td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_neutrophils_bands">Neutrophils
                                        (bands)</label></td>
                                <td><input type="text" name="diagnostic_1_neutrophils_bands"
                                        id="diagnostic_1_neutrophils_bands"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span></td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0"
                                        for="diagnostic_1_neutrophils_segmenters">Neutrophils (segmenters)</label></td>
                                <td><input type="text" name="diagnostic_1_neutrophils_segmenters"
                                        id="diagnostic_1_neutrophils_segmenters"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span></td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_lymphocytes">Lymphocytes</label>
                                </td>
                                <td>
                                    <input type="text" name="diagnostic_1_lymphocytes"
                                        id="diagnostic_1_lymphocytes"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span>
                                    <span id="diagnostic_1_normalLymp" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_1_aboveLymp" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_1_belowLymp" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_monocytes">Monocytes</label></td>
                                <td>
                                    <input type="text" name="diagnostic_1_monocytes" id="diagnostic_1_monocytes"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span>
                                    <span id="diagnostic_1_normalMono" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_1_aboveMono" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_1_belowMono" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_eosinophils">Eosinophils</label>
                                </td>
                                <td>
                                    <input type="text" name="diagnostic_1_eosinophils"
                                        id="diagnostic_1_eosinophils"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span>
                                    <span id="diagnostic_1_normalEosi" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_1_aboveEosi" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_1_belowEosi" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_basophils">Basophils</label></td>
                                <td><input type="text" name="diagnostic_1_basophils" id="diagnostic_1_basophils"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> %</span></td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_1_platelet">Platelet</label></td>
                                <td><input type="text" name="diagnostic_1_platelet" id="diagnostic_1_platelet"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15"><span
                                        class="small"> Platelets/mcL</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="2">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Urinalysis</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_2_status"
                            id="diagnostic_2_done" value="D">
                        <label class="form-check-label" for="diagnostic_2_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_2_status"
                            id="diagnostic_2_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_2_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_2_status"
                            id="diagnostic_2_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_2_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_2_status"
                            id="diagnostic_2_waived" value="W">
                        <label class="form-check-label" for="diagnostic_2_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_2_status"
                            id="diagnostic_2_void" value="V">
                        <label class="form-check-label" for="diagnostic_2_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="2">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_2_lab_exam"
                                    id="diagnostic_2_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_2_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_2_lab_exam"
                                    id="diagnostic_2_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_2_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_2_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_2_accre_diag_fac" id="diagnostic_2_accre_diag_fac"
                                class="form-control text-uppercase" autocomplete="off"
                                placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_2_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_2_lab_exam_date" id="diagnostic_2_lab_exam_date"
                                class="form-control text-uppercase" autocomplete="off" placeholder="mm/dd/yyyy"
                                maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_2_lab_fee">Laboratory/Imaging
                                Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_2_lab_fee" id="diagnostic_2_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle">
                        <tbody>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_sg">Specific
                                        gravity</label></td>
                                <td><input type="text" name="diagnostic_2_sg" id="diagnostic_2_sg"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3"
                                        for="diagnostic_2_crystals">Crystals</label></td>
                                <td><input type="text" name="diagnostic_2_crystals" id="diagnostic_2_crystals"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small"
                                        for="diagnostic_2_appearance">Appearance</label></td>
                                <td><input type="text" name="diagnostic_2_appearance" id="diagnostic_2_appearance"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_bladder_cells">Bladder
                                        cells</label></td>
                                <td><input type="text" name="diagnostic_2_bladder_cells"
                                        id="diagnostic_2_bladder_cells"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_color">Color</label></td>
                                <td><input type="text" name="diagnostic_2_color" id="diagnostic_2_color"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3"
                                        for="diagnostic_2_squamous_cells">Squamous cells</label></td>
                                <td><input type="text" name="diagnostic_2_squamous_cells"
                                        id="diagnostic_2_squamous_cells"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_glucose">Glucose</label>
                                </td>
                                <td><input type="text" name="diagnostic_2_glucose" id="diagnostic_2_glucose"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_tubular_cells">Tubular
                                        cells</label></td>
                                <td><input type="text" name="diagnostic_2_tubular_cells"
                                        id="diagnostic_2_tubular_cells"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off">/hpf
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_proteins">Proteins</label>
                                </td>
                                <td><input type="text" name="diagnostic_2_proteins" id="diagnostic_2_proteins"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_broad_casts">Broad
                                        casts</label></td>
                                <td><input type="text" name="diagnostic_2_broad_casts"
                                        id="diagnostic_2_broad_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_ketones">Ketones</label>
                                </td>
                                <td><input type="text" name="diagnostic_2_ketones" id="diagnostic_2_ketones"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3"
                                        for="diagnostic_2_epithelial_cell_casts">Epithelial cell casts</label></td>
                                <td><input type="text" name="diagnostic_2_epithelial_cell_casts"
                                        id="diagnostic_2_epithelial_cell_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_pH">pH</label></td>
                                <td><input type="text" name="diagnostic_2_pH" id="diagnostic_2_pH"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50"></td>
                                <td><label class="form-label mb-0 small ms-3"
                                        for="diagnostic_2_granular_casts">Granular casts</label></td>
                                <td><input type="text" name="diagnostic_2_granular_casts"
                                        id="diagnostic_2_granular_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_pus">Pus cells</label></td>
                                <td>
                                    <input type="text" name="diagnostic_2_pus" id="diagnostic_2_pus"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">
                                    <span id="diagnostic_2_normalUrinePus"
                                        class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_2_aboveUrinePus" class="text-danger small d-none">Above
                                        Normal</span>
                                </td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_hyaline_casts">Hyaline
                                        casts</label></td>
                                <td><input type="text" name="diagnostic_2_hyaline_casts"
                                        id="diagnostic_2_hyaline_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_alb">Albumin</label></td>
                                <td>
                                    <input type="text" name="diagnostic_2_alb" id="diagnostic_2_alb"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">mg/dl
                                    <span id="diagnostic_2_normalUrineAlb"
                                        class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_2_aboveUrineAlb" class="text-danger small d-none">Above
                                        Normal</span>
                                </td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_rbc_casts">Red blood
                                        cell casts</label></td>
                                <td><input type="text" name="diagnostic_2_rbc_casts" id="diagnostic_2_rbc_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_rbc">Red blood
                                        cells</label></td>
                                <td>
                                    <input type="text" name="diagnostic_2_rbc" id="diagnostic_2_rbc"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf
                                    <span id="diagnostic_2_normalUrineRbc"
                                        class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_2_aboveUrineRbc" class="text-danger small d-none">Above
                                        Normal</span>
                                </td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_waxy_casts">Waxy
                                        casts</label></td>
                                <td><input type="text" name="diagnostic_2_waxy_casts" id="diagnostic_2_waxy_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_wbc">White blood
                                        cells</label></td>
                                <td><input type="text" name="diagnostic_2_wbc" id="diagnostic_2_wbc"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                                <td><label class="form-label mb-0 small ms-3" for="diagnostic_2_wc_casts">White cell
                                        casts</label></td>
                                <td><input type="text" name="diagnostic_2_wc_casts" id="diagnostic_2_wc_casts"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off"
                                        maxlength="50">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0 small" for="diagnostic_2_bacteria">Bacteria</label>
                                </td>
                                <td colspan="3"><input type="text" name="diagnostic_2_bacteria"
                                        id="diagnostic_2_bacteria" class="form-control form-control-sm text-uppercase"
                                        autocomplete="off" maxlength="50">/hpf</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="3">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Fecalysis</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_3_status"
                            id="diagnostic_3_done" value="D">
                        <label class="form-check-label" for="diagnostic_3_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_3_status"
                            id="diagnostic_3_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_3_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_3_status"
                            id="diagnostic_3_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_3_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_3_status"
                            id="diagnostic_3_waived" value="W">
                        <label class="form-check-label" for="diagnostic_3_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_3_status"
                            id="diagnostic_3_void" value="V">
                        <label class="form-check-label" for="diagnostic_3_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="3">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_3_lab_exam"
                                    id="diagnostic_3_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_3_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_3_lab_exam"
                                    id="diagnostic_3_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_3_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_3_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_3_accre_diag_fac" id="diagnostic_3_accre_diag_fac"
                                class="form-control text-uppercase" autocomplete="off"
                                placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_3_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_3_lab_exam_date" id="diagnostic_3_lab_exam_date"
                                class="form-control text-uppercase" autocomplete="off" placeholder="mm/dd/yyyy"
                                maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_3_lab_fee">Laboratory/Imaging
                                Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_3_lab_fee" id="diagnostic_3_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <p class="fw-bold">Appearance:</p>
                <div class="row g-3 mb-2">
                    <div class="col-md-4">
                        <label class="form-label mb-0" for="diagnostic_3_color">Color</label>
                        <select name="diagnostic_3_color" id="diagnostic_3_color" class="form-select form-select-sm"
                            style="max-width: 12rem;">
                            <option value="1">BROWN</option>
                            <option value="2">BLACK</option>
                            <option value="3">RED</option>
                            <option value="4">WHITE/GREY</option>
                            <option value="5">YELLOW</option>
                            <option value="6">GREEN</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-0" for="diagnostic_3_consistency">Consistency</label>
                        <select name="diagnostic_3_consistency" id="diagnostic_3_consistency"
                            class="form-select form-select-sm" style="max-width: 12rem;">
                            <option value="1">SOFT</option>
                            <option value="2">WELL-FORMED</option>
                            <option value="3">SEMI-FORMED</option>
                            <option value="4">WATERY</option>
                            <option value="5">MUCOID</option>
                            <option value="6">HARD</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label mb-0" for="diagnostic_3_pus">Pus Cells</label>
                        <input type="text" name="diagnostic_3_pus" id="diagnostic_3_pus"
                            class="form-control form-control-sm text-uppercase" style="max-width: 10rem;"
                            autocomplete="off" maxlength="4">
                    </div>
                </div>
                <p class="fw-bold">Microscopic:</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle">
                        <tbody>
                            <tr>
                                <td class="w-25"><label class="form-label mb-0" for="diagnostic_3_rbc">RBC</label>
                                </td>
                                <td><input type="text" name="diagnostic_3_rbc" id="diagnostic_3_rbc"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_3_wbc">WBC</label></td>
                                <td><input type="text" name="diagnostic_3_wbc" id="diagnostic_3_wbc"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off">/hpf</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_3_ova">Ova</label></td>
                                <td><input type="text" name="diagnostic_3_ova" id="diagnostic_3_ova"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off">=/-</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_3_parasite">Parasite</label></td>
                                <td><input type="text" name="diagnostic_3_parasite" id="diagnostic_3_parasite"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off">=/-</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_3_blood">Blood</label></td>
                                <td>
                                    <select name="diagnostic_3_blood" id="diagnostic_3_blood"
                                        class="form-select form-select-sm" style="max-width: 8rem;">
                                        <option value="P">PRESENT</option>
                                        <option value="A">ABSENT</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_3_occult_blood">Occult
                                        Blood</label></td>
                                <td><input type="text" name="diagnostic_3_occult_blood"
                                        id="diagnostic_3_occult_blood"
                                        class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                                        autocomplete="off"></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="4">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Chest X-Ray</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_4_status"
                            id="diagnostic_4_done" value="D">
                        <label class="form-check-label" for="diagnostic_4_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_4_status"
                            id="diagnostic_4_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_4_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_4_status"
                            id="diagnostic_4_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_4_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_4_status"
                            id="diagnostic_4_waived" value="W">
                        <label class="form-check-label" for="diagnostic_4_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_4_status"
                            id="diagnostic_4_void" value="V">
                        <label class="form-check-label" for="diagnostic_4_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="4">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_4_lab_exam"
                                    id="diagnostic_4_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_4_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_4_lab_exam"
                                    id="diagnostic_4_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_4_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_4_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_4_accre_diag_fac" id="diagnostic_4_accre_diag_fac"
                                class="form-control text-uppercase" autocomplete="off"
                                placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_4_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_4_lab_exam_date" id="diagnostic_4_lab_exam_date"
                                class="form-control text-uppercase" autocomplete="off" placeholder="mm/dd/yyyy"
                                maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_4_lab_fee">Laboratory/Imaging
                                Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_4_lab_fee" id="diagnostic_4_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <label class="form-label fw-normal">Results</label>
                <div class="table-responsive mb-3">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th class="text-center small" style="width:45%">Observation</th>
                                <th class="text-center small" style="width:55%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="diagnostic_4_chest_observe" id="diagnostic_4_chest_observe"
                                        class="form-select form-select-sm">
                                        <option value="" selected disabled></option>
                                        <option value="1">INFILTRATES</option>
                                        <option value="10">THICKENING</option>
                                        <option value="11">HYPERAERATION/ EMPHYSEMATOUS CHANGES</option>
                                        <option value="12">MASS</option>
                                        <option value="2">CALCIFICATION</option>
                                        <option value="3">CONSOLIDATION</option>
                                        <option value="4">CAVITY</option>
                                        <option value="5">DENSITIES</option>
                                        <option value="6">PLEURAL EFFUSION</option>
                                        <option value="7">PNEUMOTHORAX</option>
                                        <option value="8">BLEB</option>
                                        <option value="9">CARDIOMEGALY</option>
                                        <option value="99">OTHERS</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="diagnostic_4_chest_observe_remarks"
                                        id="diagnostic_4_chest_observe_remarks"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered">
                        <thead>
                            <tr>
                                <th class="small" style="width:45%"><label class="mb-0"
                                        for="diagnostic_4_chest_findings">Findings</label></th>
                                <th class="small" style="width:55%">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <select name="diagnostic_4_chest_findings" id="diagnostic_4_chest_findings"
                                        class="form-select form-select-sm">
                                        <option value="" selected disabled></option>
                                        <option value="1">NORMAL</option>
                                        <option value="10">BRONCHIOLITIS</option>
                                        <option value="11">CHRONIC OBSTRUCTIVE PULMONARY DISEASE</option>
                                        <option value="12">PULMONARY MASS</option>
                                        <option value="2">PNEUMONIA</option>
                                        <option value="3">PTB/KOCHS</option>
                                        <option value="4">PLEURAL EFFUSION</option>
                                        <option value="5">PNEUMOTHORAX</option>
                                        <option value="6">EMPHYSEMA</option>
                                        <option value="7">CHRONIC BRONCHITIS</option>
                                        <option value="8">BRONCHIECTASIS</option>
                                        <option value="9">ACUTE BRONCHITIS</option>
                                        <option value="99">OTHERS</option>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="diagnostic_4_chest_findings_remarks"
                                        id="diagnostic_4_chest_findings_remarks"
                                        class="form-control form-control-sm text-uppercase" autocomplete="off">
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="5">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Sputum Microscopy</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_5_status"
                            id="diagnostic_5_done" value="D">
                        <label class="form-check-label" for="diagnostic_5_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_5_status"
                            id="diagnostic_5_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_5_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_5_status"
                            id="diagnostic_5_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_5_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_5_status"
                            id="diagnostic_5_waived" value="W">
                        <label class="form-check-label" for="diagnostic_5_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_5_status"
                            id="diagnostic_5_void" value="V">
                        <label class="form-check-label" for="diagnostic_5_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="5">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_5_lab_exam"
                                    id="diagnostic_5_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_5_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_5_lab_exam"
                                    id="diagnostic_5_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_5_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_5_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_5_accre_diag_fac"
                                id="diagnostic_5_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_5_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_5_lab_exam_date"
                                id="diagnostic_5_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_5_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_5_lab_fee" id="diagnostic_5_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mb-3">
                    <label class="form-label fst-italic">Lab Results</label>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="diagnostic_5_sputum"
                                id="diagnostic_5_sputum_no" value="1">
                            <label class="form-check-label" for="diagnostic_5_sputum_no">Essentially Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="diagnostic_5_sputum"
                                id="diagnostic_5_sputum_yes" value="2">
                            <label class="form-check-label" for="diagnostic_5_sputum_yes">With Findings</label>
                        </div>
                        <input type="text" name="diagnostic_5_sputum_remarks" id="diagnostic_5_sputum_remarks"
                            class="form-control form-control-sm text-uppercase d-inline-block"
                            style="max-width: 14rem;" autocomplete="off">
                    </div>
                </div>
                <div class="mb-1">
                    <label class="form-label small mb-0" for="diagnostic_5_plusses">Number of Plusses</label>
                    <input type="text" name="diagnostic_5_plusses" id="diagnostic_5_plusses"
                        class="form-control form-control-sm" style="max-width: 8rem;">
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="6">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Lipid Profile</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_6_status"
                            id="diagnostic_6_done" value="D">
                        <label class="form-check-label" for="diagnostic_6_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_6_status"
                            id="diagnostic_6_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_6_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_6_status"
                            id="diagnostic_6_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_6_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_6_status"
                            id="diagnostic_6_waived" value="W">
                        <label class="form-check-label" for="diagnostic_6_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_6_status"
                            id="diagnostic_6_void" value="V">
                        <label class="form-check-label" for="diagnostic_6_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="6">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_6_lab_exam"
                                    id="diagnostic_6_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_6_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_6_lab_exam"
                                    id="diagnostic_6_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_6_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_6_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_6_accre_diag_fac"
                                id="diagnostic_6_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_6_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_6_lab_exam_date"
                                id="diagnostic_6_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_6_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_6_lab_fee" id="diagnostic_6_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle">
                        <tbody>
                            <tr>
                                <td class="w-25"><label class="form-label mb-0" for="diagnostic_6_ldl">LDL
                                        Cholesterol</label></td>
                                <td>
                                    <input type="text" name="diagnostic_6_ldl" id="diagnostic_6_ldl"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="50">mg/dL
                                    <span id="diagnostic_6_normalLdl" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_6_aboveLdl" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_6_belowLdl" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_6_hdl">HDL Cholesterol</label>
                                </td>
                                <td>
                                    <input type="text" name="diagnostic_6_hdl" id="diagnostic_6_hdl"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="50">mg/dL
                                    <span id="diagnostic_6_normalHdl" class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_6_aboveHdl" class="text-danger small d-none">Above
                                        Normal</span>
                                    <span id="diagnostic_6_belowHdl" class="text-danger small d-none">Below
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_6_cholesterol">Total
                                        Cholesterol</label></td>
                                <td>
                                    <input type="text" name="diagnostic_6_cholesterol"
                                        id="diagnostic_6_cholesterol"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="50">mg/dL
                                    <span id="diagnostic_6_normalChol"
                                        class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_6_aboveChol" class="text-danger small d-none">Above
                                        Normal</span>
                                </td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0"
                                        for="diagnostic_6_triglycerides">Triglycerides</label></td>
                                <td>
                                    <input type="text" name="diagnostic_6_triglycerides"
                                        id="diagnostic_6_triglycerides"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="50">mg/dL
                                    <span id="diagnostic_6_normalTrigly"
                                        class="text-danger small d-none">Normal</span>
                                    <span id="diagnostic_6_aboveTrigly" class="text-danger small d-none">Above
                                        Normal</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="7">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Fasting Blood Sugar</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_7_status"
                            id="diagnostic_7_done" value="D">
                        <label class="form-check-label" for="diagnostic_7_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_7_status"
                            id="diagnostic_7_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_7_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_7_status"
                            id="diagnostic_7_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_7_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_7_status"
                            id="diagnostic_7_waived" value="W">
                        <label class="form-check-label" for="diagnostic_7_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_7_status"
                            id="diagnostic_7_void" value="V">
                        <label class="form-check-label" for="diagnostic_7_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="7">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_7_lab_exam"
                                    id="diagnostic_7_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_7_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_7_lab_exam"
                                    id="diagnostic_7_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_7_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_7_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_7_accre_diag_fac"
                                id="diagnostic_7_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_7_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_7_lab_exam_date"
                                id="diagnostic_7_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_7_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_7_lab_fee" id="diagnostic_7_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="row g-3 align-items-center">
                    <div class="col-auto"><label class="form-label mb-0"
                            for="diagnostic_7_glucose_mgdL">Glucose</label></div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_7_glucose_mgdL" id="diagnostic_7_glucose_mgdL"
                            class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                            autocomplete="off" maxlength="15">mg/dL
                        <span id="diagnostic_7_normalGlucose" class="text-danger small d-none">Normal</span>
                        <span id="diagnostic_7_aboveGlucose" class="text-danger small d-none">Above Normal</span>
                        <span id="diagnostic_7_belowGlucose" class="text-danger small d-none">Below Normal</span>
                    </div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_7_glucose_mmolL" id="diagnostic_7_glucose_mmolL"
                            class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                            autocomplete="off" maxlength="15">mmol/L
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="8">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Creatinine</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_8_status"
                            id="diagnostic_8_done" value="D">
                        <label class="form-check-label" for="diagnostic_8_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_8_status"
                            id="diagnostic_8_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_8_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_8_status"
                            id="diagnostic_8_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_8_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_8_status"
                            id="diagnostic_8_waived" value="W">
                        <label class="form-check-label" for="diagnostic_8_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_8_status"
                            id="diagnostic_8_void" value="V">
                        <label class="form-check-label" for="diagnostic_8_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="8">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_8_lab_exam"
                                    id="diagnostic_8_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_8_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_8_lab_exam"
                                    id="diagnostic_8_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_8_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_8_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_8_accre_diag_fac"
                                id="diagnostic_8_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_8_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_8_lab_exam_date"
                                id="diagnostic_8_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_8_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_8_lab_fee" id="diagnostic_8_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="row g-2 align-items-center">
                    <div class="col-auto"><label class="form-label mb-0"
                            for="diagnostic_8_creatinine_mgdl">Result</label></div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_8_creatinine_mgdl"
                            id="diagnostic_8_creatinine_mgdl" class="form-control form-control-sm text-uppercase"
                            style="max-width: 7rem;" autocomplete="off" maxlength="5">mg/dL
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="9">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Electrocardiogram (ECG)</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_9_status"
                            id="diagnostic_9_done" value="D">
                        <label class="form-check-label" for="diagnostic_9_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_9_status"
                            id="diagnostic_9_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_9_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_9_status"
                            id="diagnostic_9_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_9_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_9_status"
                            id="diagnostic_9_waived" value="W">
                        <label class="form-check-label" for="diagnostic_9_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_9_status"
                            id="diagnostic_9_void" value="V">
                        <label class="form-check-label" for="diagnostic_9_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="9">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_9_lab_exam"
                                    id="diagnostic_9_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_9_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_9_lab_exam"
                                    id="diagnostic_9_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_9_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_9_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_9_accre_diag_fac"
                                id="diagnostic_9_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_9_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_9_lab_exam_date"
                                id="diagnostic_9_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_9_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_9_lab_fee" id="diagnostic_9_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mb-2">
                    <label class="form-label fst-italic">Lab Results</label>
                    <div class="d-flex flex-wrap align-items-center gap-3">
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="diagnostic_9_ecg"
                                id="diagnostic_9_ecg_no" value="1">
                            <label class="form-check-label" for="diagnostic_9_ecg_no">Essentially Normal</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="diagnostic_9_ecg"
                                id="diagnostic_9_ecg_yes" value="2">
                            <label class="form-check-label" for="diagnostic_9_ecg_yes">With Findings</label>
                        </div>
                        <input type="text" name="diagnostic_9_ecg_remarks" id="diagnostic_9_ecg_remarks"
                            class="form-control form-control-sm text-uppercase d-inline-block"
                            style="max-width: 16rem;" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="13">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Pap Smear</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_13_status"
                            id="diagnostic_13_done" value="D">
                        <label class="form-check-label" for="diagnostic_13_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_13_status"
                            id="diagnostic_13_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_13_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_13_status"
                            id="diagnostic_13_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_13_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_13_status"
                            id="diagnostic_13_waived" value="W">
                        <label class="form-check-label" for="diagnostic_13_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_13_status"
                            id="diagnostic_13_void" value="V">
                        <label class="form-check-label" for="diagnostic_13_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="13">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_13_lab_exam"
                                    id="diagnostic_13_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_13_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_13_lab_exam"
                                    id="diagnostic_13_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_13_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_13_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_13_accre_diag_fac"
                                id="diagnostic_13_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_13_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_13_lab_exam_date"
                                id="diagnostic_13_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_13_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_13_lab_fee" id="diagnostic_13_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="mb-3">
                    <label class="form-label mb-1" for="diagnostic_13_papsSmearFindings">Findings:</label>
                    <textarea name="diagnostic_13_papsSmearFindings" id="diagnostic_13_papsSmearFindings"
                        class="form-control text-uppercase" rows="3" autocomplete="off" style="resize: none;"></textarea>
                </div>
                <div>
                    <label class="form-label mb-1" for="diagnostic_13_papsSmearImpression">Impression:</label>
                    <textarea name="diagnostic_13_papsSmearImpression" id="diagnostic_13_papsSmearImpression"
                        class="form-control text-uppercase" rows="3" autocomplete="off" style="resize: none;"></textarea>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="14">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Oral Glucose Tolerance Test</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_14_status"
                            id="diagnostic_14_done" value="D">
                        <label class="form-check-label" for="diagnostic_14_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_14_status"
                            id="diagnostic_14_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_14_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_14_status"
                            id="diagnostic_14_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_14_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_14_status"
                            id="diagnostic_14_waived" value="W">
                        <label class="form-check-label" for="diagnostic_14_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_14_status"
                            id="diagnostic_14_void" value="V">
                        <label class="form-check-label" for="diagnostic_14_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="14">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_14_lab_exam"
                                    id="diagnostic_14_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_14_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_14_lab_exam"
                                    id="diagnostic_14_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_14_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_14_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_14_accre_diag_fac"
                                id="diagnostic_14_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_14_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_14_lab_exam_date"
                                id="diagnostic_14_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_14_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_14_lab_fee" id="diagnostic_14_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <p class="fw-bold mb-2">Examination</p>
                <div class="table-responsive">
                    <table class="table table-sm table-borderless align-middle">
                        <tbody>
                            <tr>
                                <td class="w-25"><label class="form-label mb-0"
                                        for="diagnostic_14_fasting_mg">Fasting</label></td>
                                <td><input type="text" name="diagnostic_14_fasting_mg"
                                        id="diagnostic_14_fasting_mg"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mg/dL</td>
                                <td><input type="text" name="diagnostic_14_fasting_mmol"
                                        id="diagnostic_14_fasting_mmol"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mmol/L</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_14_oneHr_mg">OGTT (1
                                        Hour)</label></td>
                                <td><input type="text" name="diagnostic_14_oneHr_mg"
                                        id="diagnostic_14_oneHr_mg"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mg/dL</td>
                                <td><input type="text" name="diagnostic_14_oneHr_mmol"
                                        id="diagnostic_14_oneHr_mmol"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mmol/L</td>
                            </tr>
                            <tr>
                                <td><label class="form-label mb-0" for="diagnostic_14_twoHr_mg">OGTT (2
                                        Hours)</label></td>
                                <td><input type="text" name="diagnostic_14_twoHr_mg"
                                        id="diagnostic_14_twoHr_mg"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mg/dL</td>
                                <td><input type="text" name="diagnostic_14_twoHr_mmol"
                                        id="diagnostic_14_twoHr_mmol"
                                        class="form-control form-control-sm d-inline-block text-uppercase"
                                        style="max-width: 7rem;" autocomplete="off" maxlength="15">mmol/L</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="15">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Fecal Occult Blood</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_15_status"
                            id="diagnostic_15_done" value="D">
                        <label class="form-check-label" for="diagnostic_15_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_15_status"
                            id="diagnostic_15_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_15_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_15_status"
                            id="diagnostic_15_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_15_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_15_status"
                            id="diagnostic_15_waived" value="W">
                        <label class="form-check-label" for="diagnostic_15_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_15_status"
                            id="diagnostic_15_void" value="V">
                        <label class="form-check-label" for="diagnostic_15_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="15">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_15_lab_exam"
                                    id="diagnostic_15_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_15_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_15_lab_exam"
                                    id="diagnostic_15_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_15_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_15_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_15_accre_diag_fac"
                                id="diagnostic_15_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_15_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_15_lab_exam_date"
                                id="diagnostic_15_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_15_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_15_lab_fee" id="diagnostic_15_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="form-label mb-0">Result</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="diagnostic_15_fobt"
                            id="diagnostic_15_fobt_positive" value="P">
                        <label class="form-check-label" for="diagnostic_15_fobt_positive">Positive</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="diagnostic_15_fobt"
                            id="diagnostic_15_fobt_negative" value="N">
                        <label class="form-check-label" for="diagnostic_15_fobt_negative">Negative</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="17">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>PPD Test (Tuberculosis)</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_17_status"
                            id="diagnostic_17_done" value="D">
                        <label class="form-check-label" for="diagnostic_17_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_17_status"
                            id="diagnostic_17_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_17_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_17_status"
                            id="diagnostic_17_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_17_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_17_status"
                            id="diagnostic_17_waived" value="W">
                        <label class="form-check-label" for="diagnostic_17_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_17_status"
                            id="diagnostic_17_void" value="V">
                        <label class="form-check-label" for="diagnostic_17_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="17">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_17_lab_exam"
                                    id="diagnostic_17_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_17_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_17_lab_exam"
                                    id="diagnostic_17_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_17_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_17_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_17_accre_diag_fac"
                                id="diagnostic_17_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_17_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_17_lab_exam_date"
                                id="diagnostic_17_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_17_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_17_lab_fee" id="diagnostic_17_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="d-flex flex-wrap align-items-center gap-3">
                    <span class="form-label mb-0">Result</span>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="diagnostic_17_ppdt"
                            id="diagnostic_17_ppdt_positive" value="P">
                        <label class="form-check-label" for="diagnostic_17_ppdt_positive">Positive</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="diagnostic_17_ppdt"
                            id="diagnostic_17_ppdt_negative" value="N">
                        <label class="form-check-label" for="diagnostic_17_ppdt_negative">Negative</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="18">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>HbA1c</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_18_status"
                            id="diagnostic_18_done" value="D">
                        <label class="form-check-label" for="diagnostic_18_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_18_status"
                            id="diagnostic_18_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_18_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_18_status"
                            id="diagnostic_18_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_18_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_18_status"
                            id="diagnostic_18_waived" value="W">
                        <label class="form-check-label" for="diagnostic_18_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_18_status"
                            id="diagnostic_18_void" value="V">
                        <label class="form-check-label" for="diagnostic_18_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="18">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_18_lab_exam"
                                    id="diagnostic_18_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_18_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_18_lab_exam"
                                    id="diagnostic_18_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_18_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_18_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_18_accre_diag_fac"
                                id="diagnostic_18_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_18_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_18_lab_exam_date"
                                id="diagnostic_18_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_18_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_18_lab_fee" id="diagnostic_18_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="row g-2 align-items-center">
                    <div class="col-auto"><label class="form-label mb-0"
                            for="diagnostic_18_hba1c_mmol">Result</label></div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_18_hba1c_mmol" id="diagnostic_18_hba1c_mmol"
                            class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                            autocomplete="off" maxlength="5">mmol/mol
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="19">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Random Blood Sugar</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_19_status"
                            id="diagnostic_19_done" value="D">
                        <label class="form-check-label" for="diagnostic_19_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_19_status"
                            id="diagnostic_19_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_19_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_19_status"
                            id="diagnostic_19_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_19_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_19_status"
                            id="diagnostic_19_waived" value="W">
                        <label class="form-check-label" for="diagnostic_19_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_19_status"
                            id="diagnostic_19_void" value="V">
                        <label class="form-check-label" for="diagnostic_19_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="19">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_19_lab_exam"
                                    id="diagnostic_19_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_19_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_19_lab_exam"
                                    id="diagnostic_19_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_19_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_19_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_19_accre_diag_fac"
                                id="diagnostic_19_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_19_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_19_lab_exam_date"
                                id="diagnostic_19_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_19_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_19_lab_fee" id="diagnostic_19_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <div class="row g-3 align-items-center">
                    <div class="col-auto"><label class="form-label mb-0"
                            for="diagnostic_19_glucose_mgdL">Glucose</label></div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_19_glucose_mgdL" id="diagnostic_19_glucose_mgdL"
                            class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                            autocomplete="off" maxlength="15">mg/dL
                        <span id="diagnostic_19_normalGlucose" class="text-danger small d-none">Normal</span>
                        <span id="diagnostic_19_aboveGlucose" class="text-danger small d-none">Above Normal</span>
                        <span id="diagnostic_19_belowGlucose" class="text-danger small d-none">Below Normal</span>
                    </div>
                    <div class="col-auto">
                        <input type="text" name="diagnostic_19_glucose_mmolL" id="diagnostic_19_glucose_mmolL"
                            class="form-control form-control-sm text-uppercase" style="max-width: 7rem;"
                            autocomplete="off" maxlength="15">mmol/L
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3 lab-exam-panel" data-philhealth-diagnostic-id="99">
            <div
                class="card-header d-flex flex-wrap justify-content-between align-items-center gap-2 bg-success bg-opacity-10 py-2">
                <span class="fw-bold"><span class="text-danger">*</span>Others</span>
                <div class="d-flex flex-wrap align-items-center gap-2 small">
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_99_status"
                            id="diagnostic_99_done" value="D">
                        <label class="form-check-label" for="diagnostic_99_done">Done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_99_status"
                            id="diagnostic_99_notYetDone" value="N" checked>
                        <label class="form-check-label" for="diagnostic_99_notYetDone">Not yet done</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_99_status"
                            id="diagnostic_99_deferred" value="X">
                        <label class="form-check-label" for="diagnostic_99_deferred">Deferred</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_99_status"
                            id="diagnostic_99_waived" value="W">
                        <label class="form-check-label" for="diagnostic_99_waived">Waived</label>
                    </div>
                    <div class="form-check form-check-inline mb-0">
                        <input class="form-check-input" type="radio" name="diagnostic_99_status"
                            id="diagnostic_99_void" value="V">
                        <label class="form-check-label" for="diagnostic_99_void">Void</label>
                    </div>
                    <div>
                        <button class="btn btn-sm btn-success laboratoryResultsSaveBtn" type="button"
                            data-id="99">Save</button>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <fieldset class="border rounded p-3 mb-3">
                    <legend class="float-none w-auto fs-6 fw-bold fst-italic">Laboratory/Image Done</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-12">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_99_lab_exam"
                                    id="diagnostic_99_lab_exam_in" value="1" checked>
                                <label class="form-check-label" for="diagnostic_99_lab_exam_in">within the
                                    facility</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="diagnostic_99_lab_exam"
                                    id="diagnostic_99_lab_exam_out" value="0">
                                <label class="form-check-label" for="diagnostic_99_lab_exam_out">Partner
                                    Facility</label>
                            </div>
                        </div>
                        <div class="col-12 col-md-8">
                            <label class="form-label small mb-0" for="diagnostic_99_accre_diag_fac">Partner facility
                                name</label>
                            <input type="text" name="diagnostic_99_accre_diag_fac"
                                id="diagnostic_99_accre_diag_fac" class="form-control text-uppercase"
                                autocomplete="off" placeholder="NAME OF HEALTH CARE INSTITUTION">
                        </div>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold" for="diagnostic_99_lab_exam_date">Date of
                                Lab/Image Exam</label>
                            <input type="text" name="diagnostic_99_lab_exam_date"
                                id="diagnostic_99_lab_exam_date" class="form-control text-uppercase"
                                autocomplete="off" placeholder="mm/dd/yyyy" maxlength="10">
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label fst-italic fw-bold"
                                for="diagnostic_99_lab_fee">Laboratory/Imaging Fee</label>
                            <div class="input-group">
                                <span class="input-group-text">Php</span>
                                <input type="text" name="diagnostic_99_lab_fee" id="diagnostic_99_lab_fee"
                                    class="form-control text-uppercase" autocomplete="off">
                            </div>
                        </div>
                    </div>
                </fieldset>

                <p class="fw-bold mb-2">Examination</p>
                <div class="row g-2">
                    <div class="col-12">
                        <label class="form-label mb-0" for="diagnostic_99_oth1">Result</label>
                        <input type="text" name="diagnostic_99_oth1" id="diagnostic_99_oth1"
                            class="form-control text-uppercase" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

    </form>
</div>
