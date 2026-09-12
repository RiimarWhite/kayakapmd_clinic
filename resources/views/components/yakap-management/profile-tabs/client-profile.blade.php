<div class="container-fluid">
    <form id="clientProfileForm">
        <div class="row mb-3">
            <div class="col-12 p-0">
                <div class="card">
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsWalkedIn"
                                    id="walkedInChecker_true" value="N">
                                <label class="form-check-label" for="walkedInChecker_true">
                                    Walk-in clients with Authorization Transaction Code (ATC)
                                </label>
                            </div>
                            <div class="form-check form-check-inline">
                                <input class="form-check-input" type="radio" name="dIsWalkedIn"
                                    id="walkedInChecker_false" value="Y">
                                <label class="form-check-label" for="walkedInChecker_false">
                                    Walk-in clients without ATC
                                </label>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="dATC" class="form-label">Authorization Transaction Code <span
                                    class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="dATC" name="dATC" minlength="4"
                                maxlength="10" placeholder="Authorization Transaction Code">
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
                        <div class="mb-3">
                            <label for="dProfDate" class="form-label">Health Screening & Assessment Date <span
                                    class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="dProfDate" name="dProfDate"
                                placeholder="mm/dd/yyyy">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
