/**
 * Detailed Comment: Reusable Client-Side Admin Elevation Helper.
 * Handles checking active session elevation and prompting the administrator verification
 * modal with configurable elevation durations (1 hour, 2 hours, 1 day) saved to user session data.
 */
import Swal from 'sweetalert2';

let pendingActionCallback = null;
let isElevatedLocally = false;
let localElevationExpiresAt = null;

/**
 * Detailed Comment: Initialize modal event listeners once on DOM ready
 */
$(function () {
    // Detailed Comment: Toggle access duration selector based on remember checkbox state
    $(document).on('change', '#admin_verify_remember', function () {
        if ($(this).is(':checked')) {
            $('#admin_verify_duration_container').slideDown(150);
        } else {
            $('#admin_verify_duration_container').slideUp(150);
        }
    });

    // Detailed Comment: Submit handler for admin verification form
    $(document).on('click', '#admin_verify_submit_btn', function (e) {
        e.preventDefault();
        submitAdminVerification();
    });

    $(document).on('submit', '#admin_verification_form', function (e) {
        e.preventDefault();
        submitAdminVerification();
    });

    // Reset pending action on modal close if not authenticated
    $(document).on('hidden.bs.modal', '#admin_verification_modal', function () {
        $('#admin_verify_password').val('');
        $('#admin_verify_alert').addClass('d-none').text('');
    });
});

/**
 * Detailed Comment: Submits entered administrator credentials to the backend verification endpoint
 */
function submitAdminVerification() {
    const username = $('#admin_verify_username').val().trim();
    const password = $('#admin_verify_password').val();
    const rememberAccess = $('#admin_verify_remember').is(':checked') ? 1 : 0;
    const duration = $('#admin_verify_duration').val() || '1_hour';
    const $alert = $('#admin_verify_alert');
    const $submitBtn = $('#admin_verify_submit_btn');

    if (!username || !password) {
        $alert.removeClass('d-none').text('Please enter both administrator username and password.');
        return;
    }

    $alert.addClass('d-none').text('');
    const origHtml = $submitBtn.html();
    $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1" role="status"></span> Verifying...');

    $.ajax({
        url: '/api/verify_admin_credentials',
        type: 'POST',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        data: {
            username: username,
            password: password,
            remember_access: rememberAccess,
            duration: duration
        },
        success: function (response) {
            $submitBtn.prop('disabled', false).html(origHtml);

            if (response.success) {
                isElevatedLocally = true;
                localElevationExpiresAt = response.expires_at || null;

                const modalEl = document.getElementById('admin_verification_modal');
                if (modalEl) {
                    const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                    modalInstance.hide();
                }

                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'Administrator authorized',
                    showConfirmButton: false,
                    timer: 1800
                });

                // Detailed Comment: Execute the pending protected action callback
                if (typeof pendingActionCallback === 'function') {
                    const callback = pendingActionCallback;
                    pendingActionCallback = null;
                    callback();
                }
            } else {
                $alert.removeClass('d-none').text(response.message || 'Authorization failed. Please try again.');
            }
        },
        error: function (xhr) {
            $submitBtn.prop('disabled', false).html(origHtml);
            const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Invalid administrator credentials.';
            $alert.removeClass('d-none').text(msg);
        }
    });
}

/**
 * Detailed Comment: Protects an administrative action by verifying session elevation first.
 * If elevated, invokes callback immediately. Otherwise, presents the verification modal.
 *
 * @param {Function} callback Action to execute once admin privileges are satisfied
 */
export function requireAdminAuth(callback) {
    const nowTimestamp = Math.floor(Date.now() / 1000);

    // Fast-path: local cache valid and unexpired
    if (isElevatedLocally && localElevationExpiresAt && localElevationExpiresAt > nowTimestamp) {
        return callback();
    }

    // Detailed Comment: Query backend for active session elevation
    $.ajax({
        url: '/api/check_admin_elevation',
        type: 'GET',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            'Accept': 'application/json'
        },
        success: function (res) {
            if (res.elevated) {
                isElevatedLocally = true;
                localElevationExpiresAt = res.expires_at || null;
                return callback();
            }

            // Not elevated: show modal
            pendingActionCallback = callback;
            const modalEl = document.getElementById('admin_verification_modal');
            if (modalEl) {
                $('#admin_verify_password').val('');
                $('#admin_verify_alert').addClass('d-none').text('');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            } else {
                Swal.fire({
                    title: 'Admin Verification Required',
                    text: 'Admin authorization modal is not loaded on this page.',
                    icon: 'warning'
                });
            }
        },
        error: function () {
            // On check error, fallback to displaying the modal
            pendingActionCallback = callback;
            const modalEl = document.getElementById('admin_verification_modal');
            if (modalEl) {
                $('#admin_verify_password').val('');
                $('#admin_verify_alert').addClass('d-none').text('');
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    });
}
