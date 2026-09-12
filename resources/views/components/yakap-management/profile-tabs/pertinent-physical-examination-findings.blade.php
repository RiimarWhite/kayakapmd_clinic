<div class="container-fluid p-0">
    <form id="pepertForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-danger fw-bold" role="alert">
                    PERTINENT PHYSICAL EXAMINATION FINDINGS
                </div>
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dSystolic" class="form-label">Blood Pressure (Systolic)<span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dSystolic" name="dSystolic" maxlength="3">
                            <span class="input-group-text">mmHg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dDiastolic" class="form-label">Blood Pressure (Diastolic)<span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dDiastolic" name="dDiastolic" maxlength="3">
                            <span class="input-group-text">mmHg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dHr" class="form-label">Heart Rate<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHr" name="dHr" maxlength="6">
                            <span class="input-group-text">bpm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dRr" class="form-label">Respiratory Rate<span
                                class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dRr" name="dRr" maxlength="6">
                            <span class="input-group-text">breaths/min</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="dTemp" class="form-label">Temperature<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dTemp" name="dTemp" maxlength="6">
                            <span class="input-group-text">°C</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Anthropometric Measurements</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dHeight" class="form-label">Height<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHeight" name="dHeight" maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dWeight" class="form-label">Weight<span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dWeight" name="dWeight" maxlength="6">
                            <span class="input-group-text">kg</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dBMI" class="form-label">BMI</label>
                        <input type="text" class="form-control" id="dBMI" name="dBMI" readonly>
                    </div>
                    {{-- <div class="col-md-3">
                    <label for="dWaist" class="form-label">Waist Circumference</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="dWaist"
                            name="dWaist">
                        <span class="input-group-text">cm</span>
                    </div>
                </div> --}}
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Visual Acuity</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dLeftVision" class="form-label">Left Eye</label>
                        <input type="text" class="form-control" id="dLeftVision" name="dLeftVision"
                            maxlength="12">
                    </div>
                    <div class="col-md-6">
                        <label for="dRightVision" class="form-label">Right Eye</label>
                        <input type="text" class="form-control" id="dRightVision" name="dRightVision"
                            maxlength="12">
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Pediatric Measurements (0-24 months)</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dLength" class="form-label">Length</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dLength" name="dLength"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dHeadCirc" class="form-label">Head Circumference</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHeadCirc" name="dHeadCirc"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dSkinfoldThickness" class="form-label">Skinfold Thickness</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dSkinfoldThickness"
                                name="dSkinfoldThickness" maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dMidUpperArmCirc" class="form-label">Middle/Upper Arm Circumference</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dMidUpperArmCirc" name="dMidUpperArmCirc"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4">
                        <label for="dWaist" class="form-label">Body Circumference - Waist</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dWaist" name="dWaist"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="dHip" class="form-label">Body Circumference - Hips</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dHip" name="dHip"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="dLimbs" class="form-label">Body Circumference - Limbs</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dLimbs" name="dLimbs"
                                maxlength="6">
                            <span class="input-group-text">cm</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="bloodTypeForm">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Blood Type</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-12">
                        <div class="mb-2">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeA+"
                                    value="A+">
                                <label class="form-check-label" for="dBloodTypeA+">A+</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeA-"
                                    value="A-">
                                <label class="form-check-label" for="dBloodTypeA-">A-</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeB+"
                                    value="B+">
                                <label class="form-check-label" for="dBloodTypeB+">B+</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeB-"
                                    value="B-">
                                <label class="form-check-label" for="dBloodTypeB-">B-</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeAB+"
                                    value="AB+">
                                <label class="form-check-label" for="dBloodTypeAB+">AB+</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeAB-"
                                    value="AB-">
                                <label class="form-check-label" for="dBloodTypeAB-">AB-</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeO+"
                                    value="O+">
                                <label class="form-check-label" for="dBloodTypeO+">O+</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dBloodType" id="dBloodTypeO-"
                                    value="O-">
                                <label class="form-check-label" for="dBloodTypeO-">O-</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="generalSurveyForm">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">General Survey</h5>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-6">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="dGenSurveyId" id="dGenSurveyId_1"
                                value="1">
                            <label class="form-check-label" for="dGenSurveyId_1">Awake and alert</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="dGenSurveyId" id="dGenSurveyId_2"
                                value="2">
                            <label class="form-check-label" for="dGenSurveyId_2">Altered Sensorium</label>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="dGenSurveyRem" class="form-label">Remarks (for Altered Sensorium)</label>
                        <input type="text" class="form-control" id="dGenSurveyRem" name="dGenSurveyRem"
                            placeholder="Altered Sensorium Remarks">
                    </div>
                </div>
            </div>
        </div>
    </form>

    <form id="pertinentFindingsForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="alert alert-success fw-bold" role="alert">
                    PERTINENT FINDINGS PER SYSTEM
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6><u>A. HEENT</u></h6>
                        <div class="mb-3">
                            @foreach ($heentLibrary as $key => $heentOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="heent[{{ $key }}]"
                                        id="heent_{{ $heentOption->heent_id }}" value="{{ $heentOption->heent_id }}">
                                    <label class="form-check-label"
                                        for="heent_{{ $heentOption->heent_id }}">{{ $heentOption->heent_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dHeentRem" id="dHeentRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><u>B. Chest/Breast/Lungs</u></h6>
                        <div class="mb-3">
                            @foreach ($chestLibrary as $key => $chestOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="chest[{{ $key }}]"
                                        id="chest_{{ $chestOption->chest_id }}" value="{{ $chestOption->chest_id }}">
                                    <label class="form-check-label"
                                        for="chest_{{ $chestOption->chest_id }}">{{ $chestOption->chest_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dChestRem" id="dChestRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6><u>C. Heart</u></h6>
                        <div class="mb-3">
                            @foreach ($heartLibrary as $key => $heartOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="heart[{{ $key }}]" id="heart_{{ $heartOption->heart_id }}"
                                        value="{{ $heartOption->heart_id }}">
                                    <label class="form-check-label"
                                        for="heart_{{ $heartOption->heart_id }}">{{ $heartOption->heart_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dHeartRem" id="dHeartRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><u>D. Abdomen</u></h6>
                        <div class="mb-3">
                            @foreach ($abdomenLibrary as $key => $abdomenOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="abdomen[{{ $key }}]"
                                        id="abdomen_{{ $abdomenOption->abdomen_id }}"
                                        value="{{ $abdomenOption->abdomen_id }}">
                                    <label class="form-check-label"
                                        for="abdomen_{{ $abdomenOption->abdomen_id }}">{{ $abdomenOption->abdomen_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dAbdomenRem" id="dAbdomenRem" rows="2"
                                placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6><u>E. Genitourinary</u></h6>
                        <div class="mb-3">
                            @foreach ($genitourinaryLibrary as $key => $guOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="dGuId[{{ $key }}]" id="gu_{{ $guOption->gu_id }}"
                                        value="{{ $guOption->gu_id }}">
                                    <label class="form-check-label"
                                        for="gu_{{ $guOption->gu_id }}">{{ $guOption->gu_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dGuRem" id="dGuRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><u>F. Digital Rectal Examination</u></h6>
                        <div class="mb-3">
                            @foreach ($digitalRectalLibrary as $key => $rectalOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="rectal[{{ $key }}]"
                                        id="rectal_{{ $rectalOption->rectal_id }}"
                                        value="{{ $rectalOption->rectal_id }}">
                                    <label class="form-check-label"
                                        for="rectal_{{ $rectalOption->rectal_id }}">{{ $rectalOption->rectal_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dRectalRem" id="dRectalRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <h6><u>G. Skin/Extremities</u></h6>
                        <div class="mb-3">
                            @foreach ($skinExtremitiesLibrary as $key => $skinOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="skinExtremities[{{ $key }}]"
                                        id="skin_{{ $skinOption->skin_id }}" value="{{ $skinOption->skin_id }}">
                                    <label class="form-check-label"
                                        for="skin_{{ $skinOption->skin_id }}">{{ $skinOption->skin_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dSkinRem" id="dSkinRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <h6><u>H. Neurological Examination</u></h6>
                        <div class="mb-3">
                            @foreach ($neuroLibrary as $key => $neuroOption)
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                        name="neuro[{{ $key }}]" id="neuro_{{ $neuroOption->neuro_id }}"
                                        value="{{ $neuroOption->neuro_id }}">
                                    <label class="form-check-label"
                                        for="neuro_{{ $neuroOption->neuro_id }}">{{ $neuroOption->neuro_desc }}</label>
                                </div>
                            @endforeach
                            <textarea class="form-control mt-2" name="dNeuroRem" id="dNeuroRem" rows="2" placeholder="Specify others"></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
