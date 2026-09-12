$(function () {
    loadProfile();

    let addresses = [];

    function loadAddressData() {
        $.ajax({
            url: "/api/fetch_address_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.data != null) {
                    addresses = response.data;
                    addresses.regions.forEach(e => {
                        $("#comp_region").append(`<option value="${e.REGION_CODE}">${e.REGION_DESC}</option>`);
                    });
                }
            }
        });
    }

    function loadProfile() {
        // loadAddressData();

        $.ajax({
            url: "/api/load_company_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success) {
                    const profile = response.profile;
                    $("#comp_name").val(profile.HOSP_NAME);
                    // $("#comp_region").val(profile.company_region).trigger("change");
                    // setTimeout(() => {
                    //     $("#comp_prov").val(profile.company_prov).trigger("change");
                    //     setTimeout(() => {
                    //         $("#comp_mun").val(profile.company_mun).trigger("change");
                    //         setTimeout(() => {
                    //             $("#comp_brgy").val(profile.company_brgy);
                    //             $("#comp_zipcode").val(profile.company_zipcode);
                    //         }, 50);
                    //     }, 50);
                    // }, 50);
                    // $("#comp_contact").val(profile.company_mobilenumber);
                    $("#comp_tel").val(profile.TEL_NO);
                    $("#comp_email").val(profile.EMAIL_ADD);
                    $("#ph_username").val(response.company.userid);
                    $("#ph_password").val(response.company.passwd);
                    $("#ph_accreno").val(response.company.hciaccreno);
                }
            }
        });
    }

    $("#comp_region").on("change", function () {
        const value = $(this).val();
        let region = addresses.regions.find(e => e.REGION_CODE == value);
        if (!region) return;

        let regionCode = region.PRO_CODE;
        if (parseInt(regionCode) < 10) regionCode = regionCode.padStart(2, "0");

        const provinces = addresses.provinces.filter(e => e.PROCODE == regionCode);
        $("#comp_prov").empty();
        provinces.forEach(e => $("#comp_prov").append(`<option value="${e.PROVINCE}">${e.PROV_NAME}</option>`));
    });

    $("#comp_prov").on("change", function () {
        const value = $(this).val();
        const province = addresses.provinces.find(e => e.PROVINCE === value);
        const municipalities = addresses.municipalities.filter(
            e => e.PROCODE === province.PROCODE && e.PROVINCE === province.PROVINCE
        );
        $("#comp_mun").empty();
        municipalities.forEach(e => $("#comp_mun").append(`<option value="${e.MUNICIPALITY}">${e.MUN_NAME}</option>`));
    });

    $("#comp_mun").on("change", function () {
        const province = $("#comp_prov").val();
        const value = $("#comp_mun").val();
        const municipality = addresses.municipalities.find(e => e.MUNICIPALITY === value && e.PROVINCE === province);
        const barangay = addresses.barangay.filter(e => e.MUNICIPALITY === municipality.MUNICIPALITY && e.PROVINCE === province);
        $("#comp_brgy").empty();
        barangay.forEach(e => $("#comp_brgy").append(`<option value="${e.BARANGAY}">${e.BRGY_NAME}</option>`));
    });

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

        $.ajax({
            url: "/api/update_profile",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: new FormData(form),
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
});
