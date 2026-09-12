<div class="container-fluid p-0">
    <form id="objectivePhysicalExaminationForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    OBJECTIVE/PHYSICAL EXAMINATION
                </div>

                {{-- Vital Signs --}}
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dSystolicSoap" class="form-label">Blood Pressure (Systolic) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dSystolicSoap" name="dSystolicSoap"
                                maxlength="4" placeholder="Systolic" autocomplete="off">
                            <span class="input-group-text">mmHg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dDiastolicSoap" class="form-label">Blood Pressure (Diastolic) <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dDiastolicSoap" name="dDiastolicSoap"
                                maxlength="4" placeholder="Diastolic" autocomplete="off">
                            <span class="input-group-text">mmHg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dHrSoap" class="form-label">Heart Rate <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHrSoap" name="dHrSoap" maxlength="6"
                                placeholder="Heart Rate" autocomplete="off">
                            <span class="input-group-text">/min</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dRrSoap" class="form-label">Respiratory Rate <span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dRrSoap" name="dRrSoap" maxlength="6"
                                placeholder="Respiratory Rate" autocomplete="off">
                            <span class="input-group-text">/min</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dTempSoap" class="form-label">Temperature <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dTempSoap" name="dTempSoap" maxlength="6"
                                placeholder="Temperature" autocomplete="off">
                            <span class="input-group-text">°C</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dLeftVisionSoap" class="form-label">Visual Acuity (Left Eye)</label>
                        <input type="text" class="form-control" id="dLeftVisionSoap" name="dLeftVisionSoap"
                            maxlength="12" placeholder="Left Eye" autocomplete="off">
                    </div>
                    <div class="col-md-3">
                        <label for="dRightVisionSoap" class="form-label">Visual Acuity (Right Eye)</label>
                        <input type="text" class="form-control" id="dRightVisionSoap" name="dRightVisionSoap"
                            maxlength="12" placeholder="Right Eye" autocomplete="off">
                    </div>
                </div>
            </div>
        </div>

        {{-- Anthropometric Measurements --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Anthropometric Measurements</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dHeightSoap" class="form-label">Height <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHeightSoap" name="dHeightSoap"
                                maxlength="6" placeholder="Height" autocomplete="off">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dWeightSoap" class="form-label">Weight <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dWeightSoap" name="dWeightSoap"
                                maxlength="6" placeholder="Weight" autocomplete="off">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dBMISoap" class="form-label">BMI <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dBMISoap" name="dBMISoap"
                                maxlength="6" placeholder="BMI" readonly>
                        </div>
                        <div id="bmiDescription" class="form-text mt-1"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Pediatric Measurements --}}
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pediatric Client aged 0-24 months</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dLengthSoap" class="form-label">Length</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dLengthSoap" name="dLengthSoap"
                                maxlength="6" placeholder="Length">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dHeadCircSoap" class="form-label">Head Circumference</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHeadCircSoap" name="dHeadCircSoap"
                                maxlength="6" placeholder="Head Circumference">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dSkinfoldThicknessSoap" class="form-label">Skinfold Thickness</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dSkinfoldThicknessSoap"
                                name="dSkinfoldThicknessSoap" maxlength="6" placeholder="Skinfold Thickness">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-12">
                        <label class="form-label fw-semibold">Body Circumference</label>
                    </div>
                    <div class="col-md-4">
                        <label for="dWaistSoap" class="form-label">Waist</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dWaistSoap" name="dWaistSoap"
                                maxlength="6" placeholder="Waist">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="dHipSoap" class="form-label">Hip</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHipSoap" name="dHipSoap"
                                maxlength="6" placeholder="Hip">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="dLimbsSoap" class="form-label">Limbs</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dLimbsSoap" name="dLimbsSoap"
                                maxlength="6" placeholder="Limbs">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-5">
                        <label for="dMidUpperArmCircSoap" class="form-label">Middle/Upper Arm Circumference</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dMidUpperArmCircSoap"
                                name="dMidUpperArmCircSoap" maxlength="6" placeholder="Mid/Upper Arm">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    PERTINENT FINDINGS PER SYSTEM
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>A. HEENT</u></h6>
                        <div class="mb-3">
                            @foreach ($heentLibrary as $key => $heentOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="heent[{{ $key }}]"
                                        id="soap_heent_{{ $heentOption->heent_id }}"
                                        value="{{ $heentOption->heent_id }}">
                                    <label class="form-check-label"
                                        for="soap_heent_{{ $heentOption->heent_id }}">{{ $heentOption->heent_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dHeentRem" id="dHeentRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold">
                            <u>B. Chest/Breast/Lungs</u>
                        </h6>
                        <div class="mb-3">
                            @foreach ($chestLibrary as $key => $chestOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chest[{{ $key }}]"
                                        id="soap_chest_{{ $chestOption->chest_id }}"
                                        value="{{ $chestOption->chest_id }}">
                                    <label class="form-check-label"
                                        for="soap_chest_{{ $chestOption->chest_id }}">{{ $chestOption->chest_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dChestRem" id="dChestRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>C. Heart</u></h6>
                        <div class="mb-3">
                            @foreach ($heartLibrary as $key => $heartOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="heart[{{ $key }}]"
                                        id="soap_heart_{{ $heartOption->heart_id }}"
                                        value="{{ $heartOption->heart_id }}">
                                    <label class="form-check-label"
                                        for="soap_heart_{{ $heartOption->heart_id }}">{{ $heartOption->heart_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dHeartRem" id="dHeartRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>D. Abdomen</u></h6>
                        <div class="mb-3">
                            @foreach ($abdomenLibrary as $key => $abdomenOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="abdomen[{{ $key }}]"
                                        id="soap_abdomen_{{ $abdomenOption->abdomen_id }}"
                                        value="{{ $abdomenOption->abdomen_id }}">
                                    <label class="form-check-label"
                                        for="soap_abdomen_{{ $abdomenOption->abdomen_id }}">{{ $abdomenOption->abdomen_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dAbdomenRem" id="dAbdomenRem" rows="2"
                                placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>E. Genitourinary</u></h6>
                        <div class="mb-3">
                            @foreach ($genitourinaryLibrary as $key => $guOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="dGuId[{{ $key }}]" id="soap_gu_{{ $guOption->gu_id }}"
                                        value="{{ $guOption->gu_id }}">
                                    <label class="form-check-label"
                                        for="soap_gu_{{ $guOption->gu_id }}">{{ $guOption->gu_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dGuRem" id="dGuRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>F. Digital Rectal Examination</u></h6>
                        <div class="mb-3">
                            @foreach ($digitalRectalLibrary as $key => $rectalOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="rectal[{{ $key }}]"
                                        id="soap_rectal_{{ $rectalOption->rectal_id }}"
                                        value="{{ $rectalOption->rectal_id }}">
                                    <label class="form-check-label"
                                        for="soap_rectal_{{ $rectalOption->rectal_id }}">{{ $rectalOption->rectal_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dRectalRem" id="dRectalRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>G. Skin/Extremities</u></h6>
                        <div class="mb-3">
                            @foreach ($skinExtremitiesLibrary as $key => $skinOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="skinExtremities[{{ $key }}]"
                                        id="soap_skin_{{ $skinOption->skin_id }}"
                                        value="{{ $skinOption->skin_id }}">
                                    <label class="form-check-label"
                                        for="soap_skin_{{ $skinOption->skin_id }}">{{ $skinOption->skin_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dSkinRem" id="dSkinRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6 class="fw-bold"><u>H. Neurological Examination</u></h6>
                        <div class="mb-3">
                            @foreach ($neuroLibrary as $key => $neuroOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="neuro[{{ $key }}]"
                                        id="soap_neuro_{{ $neuroOption->neuro_id }}"
                                        value="{{ $neuroOption->neuro_id }}">
                                    <label class="form-check-label"
                                        for="soap_neuro_{{ $neuroOption->neuro_id }}">{{ $neuroOption->neuro_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dNeuroRem" id="soap_dNeuroRem" rows="2"
                                placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
