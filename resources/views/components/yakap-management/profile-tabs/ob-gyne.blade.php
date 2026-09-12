<div class="container-fluid p-0">
    <div class="alert alert-warning fw-bold" id="ob-gyne-warning-message" role="alert">
        Patient is not female
    </div>
    <div class="card mb-4">
        <form id="mensHistoryForm">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dMenarchePeriod" class="form-label">Menarche</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dMenarchePeriod" name="dMenarchePeriod"
                                maxlength="3">
                            <span class="input-group-text">yrs. old</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dOnsetSexIc" class="form-label">Onset of sexual intercourse</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dOnsetSexIc" name="dOnsetSexIc"
                                maxlength="3">
                            <span class="input-group-text">yrs. old</span>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Menopause?</label>
                        <div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsMenopause" id="dIsMenopauseY"
                                    value="Y">
                                <label class="form-check-label" for="dIsMenopauseY">Yes</label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsMenopause" id="dIsMenopauseN"
                                    value="N">
                                <label class="form-check-label" for="dIsMenopauseN">No</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label for="dMenopauseAge" class="form-label">If yes, what age?</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dMenopauseAge" name="dMenopauseAge"
                                maxlength="3">
                            <span class="input-group-text">yrs. old</span>
                        </div>
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-4">
                        <label for="dLastMensPeriod" class="form-label">Last menstrual period</label>
                        <input type="date" class="form-control" id="dLastMensPeriod" name="dLastMensPeriod">
                    </div>
                    <div class="col-md-4">
                        <label for="dBirthCtrlMethod" class="form-label">Birth control method</label>
                        <input type="text" class="form-control" id="dBirthCtrlMethod" name="dBirthCtrlMethod">
                    </div>
                    <div class="col-md-4">
                        <label for="dPadsPerDay" class="form-label">No. of pads/day during menstruation</label>
                        <input type="text" class="form-control" id="dPadsPerDay" name="dPadsPerDay">
                    </div>
                </div>
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label for="dPeriodDuration" class="form-label">Period duration</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dPeriodDuration" name="dPeriodDuration">
                            <span class="input-group-text">days</span>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label for="dMensInterval" class="form-label">Interval cycle</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="dMensInterval" name="dMensInterval">
                            <span class="input-group-text">days</span>
                        </div>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <form id="pregHistoryForm">
        <div class="card mb-4">
            <div class="card-body">
                <div class="mb-3">
                    <label class="form-label">With access to family planning counseling?</label>
                    <div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dWFamPlan" id="dWFamPlanY"
                                value="Y">
                            <label class="form-check-label" for="dWFamPlanY">Yes</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="dWFamPlan" id="dWFamPlanN"
                                value="N">
                            <label class="form-check-label" for="dWFamPlanN">No</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="card">
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-3">
                        <label for="dPregCnt" class="form-label">Gravidity (no. of pregnancy)</label>
                        <input type="text" class="form-control" id="dPregCnt" name="dPregCnt" maxlength="2">
                    </div>
                    <div class="col-md-3">
                        <label for="dDeliveryCnt" class="form-label">Parity (no. of delivery)</label>
                        <input type="text" class="form-control" id="dDeliveryCnt" name="dDeliveryCnt"
                            maxlength="2">
                    </div>
                    <div class="col-md-3">
                        <label for="dDeliveryTyp" class="form-label">Type of delivery</label>
                        <select class="form-select" id="dDeliveryTyp" name="dDeliveryTyp">
                            <option value="">Select delivery type</option>
                            <option value="N">Normal (NSD)</option>
                            <option value="O">Operative (CSD)</option>
                            <option value="B">Both Normal & Operative (NSD & CSD)</option>
                            <option value="X">Not Applicable</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="form-check mt-4">
                            <input class="form-check-input" type="checkbox" id="dWPregIndhyp" name="dWPregIndhyp"
                                value="Y">
                            <label class="form-check-label" for="dWPregIndhyp">
                                Pregnancy-induced hypertension (Pre-eclampsia)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-3">
                        <label for="dFullTermCnt" class="form-label">No. of full term</label>
                        <input type="text" class="form-control" id="dFullTermCnt" name="dFullTermCnt"
                            maxlength="3">
                    </div>
                    <div class="col-md-3">
                        <label for="dPrematureCnt" class="form-label">No. of premature</label>
                        <input type="text" class="form-control" id="dPrematureCnt" name="dPrematureCnt"
                            maxlength="3">
                    </div>
                    <div class="col-md-3">
                        <label for="dAbortionCnt" class="form-label">No. of abortion</label>
                        <input type="text" class="form-control" id="dAbortionCnt" name="dAbortionCnt"
                            maxlength="3">
                    </div>
                    <div class="col-md-3">
                        <label for="dLivChildrenCnt" class="form-label">No. of living children</label>
                        <input type="text" class="form-control" id="dLivChildrenCnt" name="dLivChildrenCnt"
                            maxlength="3">
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
