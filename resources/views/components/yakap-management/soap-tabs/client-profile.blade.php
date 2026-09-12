<div class="container-fluid">
    <form id="soapClientProfileForm">
        <div class="row mb-3">
            <div class="col-12 p-0">
                <div class="card">
                    <div class="card-body">
                        <input type="hidden" name="consultCode">
                        <h6 class="mb-0">Consult Code: <span id="consultCode"></span></h6>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mb-3">
            <div class="col-12 p-0">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsWalkedIn"
                                    id="walkedInCheckerSoap_true" value="N">
                                <label class="form-check-label" for="walkedInCheckerSoap_true">
                                    Walk-in clients with Authorization Transaction Code (ATC)
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsWalkedIn"
                                    id="walkedInCheckerSoap_false" value="Y">
                                <label class="form-check-label" for="walkedInCheckerSoap_false">
                                    Walk-in clients without ATC
                                </label>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dATC" class="form-label">Authorization Transaction Code <span
                                        class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="dATC" name="dATC" minlength="4"
                                    maxlength="10" placeholder="Authorization Transaction Code">
                            </div>
                        </div>
                        <small class="text-muted">Note: ATC should be used within the Screening & Assessment
                            Date.</small>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-3">
            <div class="col-12 p-0">
                <div class="card">
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="dCoPay" class="form-label">Co-payment <span
                                        class="text-danger">*</span></label>
                                <span>(Php)</span><input type="number" class="form-control" id="dCoPay"
                                    name="dCoPay" placeholder="Co-payment">
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="dSoapDate" class="form-label">Consultation Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" class="form-control" id="dSoapDate" name="dSoapDate"
                                    placeholder="mm/dd/yyyy">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
