{{--
  Detailed Comment: Reusable Administrator Credential Verification Modal.
  Requires administrator username and password, with an optional duration dropdown
  (1 hour, 2 hours, 1 day) to elevate user session permissions for protected actions.
--}}
<div class="modal fade" id="admin_verification_modal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" aria-labelledby="adminVerificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content shadow border-0">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold m-0" id="adminVerificationModalLabel">
                    <i class="fa-solid fa-shield-halved me-2"></i> Administrator Authorization
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="admin_verification_form" class="modal-body p-4 d-flex flex-column gap-3">
                <div class="text-muted small">
                    <i class="fa-solid fa-lock text-danger me-1"></i>
                    This action is protected. Please enter administrator credentials to proceed.
                </div>

                <div class="alert alert-danger py-2 px-3 small d-none" id="admin_verify_alert" role="alert"></div>

                <div>
                    <label class="form-label fw-bold small" for="admin_verify_username">Admin Username <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-user-shield text-secondary"></i></span>
                        <input type="text" class="form-control" name="username" id="admin_verify_username" placeholder="Enter admin username" required autocomplete="off">
                    </div>
                </div>

                <div>
                    <label class="form-label fw-bold small" for="admin_verify_password">Admin Password <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <span class="input-group-text bg-light"><i class="fa-solid fa-key text-secondary"></i></span>
                        <input type="password" class="form-control" name="password" id="admin_verify_password" placeholder="Enter admin password" required autocomplete="current-password">
                    </div>
                </div>

                <div class="border rounded p-3 bg-light d-flex flex-column gap-2">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="remember_access" id="admin_verify_remember" checked value="1">
                        <label class="form-check-label fw-semibold small" for="admin_verify_remember">
                            Allow access for a duration
                        </label>
                    </div>

                    <div id="admin_verify_duration_container">
                        <label class="form-label fw-bold small text-muted mb-1" for="admin_verify_duration">Access Duration</label>
                        <select class="form-select form-select-sm" name="duration" id="admin_verify_duration">
                            <option value="1_hour" selected>1 Hour</option>
                            <option value="2_hours">2 Hours</option>
                            <option value="1_day">1 Day</option>
                        </select>
                        <div class="form-text small" style="font-size: 11px;">
                            Valid until you sign out or the duration expires. Saved to your session.
                        </div>
                    </div>
                </div>
            </form>

            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-secondary fw-semibold" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger fw-bold" id="admin_verify_submit_btn">
                    <i class="fa-solid fa-check-double me-1"></i> Verify &amp; Authorize
                </button>
            </div>
        </div>
    </div>
</div>
