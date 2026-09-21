import { initAddressCascade } from '../../helpers/address-cascade.js';

/**
 * Detailed Comment: Admin Profile page controller handling Company Profile,
 * PSGC Reference Address cascading, PhilHealth integration credentials,
 * and Administrator account settings.
 */
$(function () {
    // Detailed Comment: Initialize PSGC address cascade for Company Profile
    const profileAddressCascade = initAddressCascade({
        regionSel: '#phregion',
        provSel: '#phprov',
        munSel: '#phmun',
        brgySel: '#phbrgy',
        zipInput: '#phzipcode'
    });

    loadProfile();

    function loadProfile() {
        $.ajax({
            url: "/api/load_company_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success) {
                    const profile = response.profile;
                    $("#comp_name").val(profile.HOSP_NAME || '');
                    $("#comp_tel").val(profile.TEL_NO || '');
                    $("#comp_email").val(profile.EMAIL_ADD || '');

                    // Detailed Comment: Populate PSGC address cascade fields
                    if (profile.HOSP_ADDREG) {
                        profileAddressCascade.setAddressValues({
                            region: profile.HOSP_ADDREG,
                            province: profile.HOSP_ADDPROV,
                            muncity: profile.HOSP_ADDMUN,
                            brgy: profile.HOSP_ADDBRGY,
                            zipcode: profile.HOSP_ADDZIPCODE
                        });
                    }

                    if (response.company) {
                        $("#ph_username").val(response.company.userid || '');
                        $("#ph_password").val(response.company.passwd || '');
                        $("#ph_accreno").val(response.company.hciaccreno || '');
                    }
                }
            }
        });
    }

    $("#upload_logo_btn").on("click", function () { $("#comp_logo").trigger("click"); });

    $("#comp_logo").on("change", function () {
        const file = this.files[0];
        if (!file) return;
        $("#company_logo_display").attr("src", URL.createObjectURL(file));
    });

    $("#view_password").on("click", function () {
        const input = document.getElementById("ph_password");
        input.type = input.type === "password" ? "text" : "password";
    });

    $("#save_updates").on("click", function () {
        const form = document.getElementById("profile_form");
        if (!form.checkValidity()) return form.reportValidity();

        // Enable all disabled selects inside profile form temporarily so FormData captures them
        $(form).find(':disabled').prop('disabled', false);
        const formData = new FormData(form);

        $.ajax({
            url: "/api/update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Profile updated', showConfirmButton: false, timer: 1500 });
                }
            }
        });
    });

    $("#save_comp_credentials").on("click", function () {
        $.ajax({
            url: "/api/save_philhealth_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#philhealth_account_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Credentials saved', showConfirmButton: false, timer: 1500 });
                }
            }
        });
    });

    // Detailed Comment: Handler to update logged in administrator account details and credentials
    $("#save_admin_account").on("click", function () {
        const form = document.getElementById("admin_account_form");
        if (!form.checkValidity()) return form.reportValidity();

        $.ajax({
            url: "/api/admin/update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#admin_account_form").serialize(),
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Administrator account updated successfully.",
                        icon: "success",
                        confirmButtonText: "Okay"
                    });
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update administrator account.";
                Swal.fire({ title: "Error", text: msg, icon: "error" });
            }
        });
    });
});
