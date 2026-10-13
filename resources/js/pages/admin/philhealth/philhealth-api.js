$(function () {
    function showError(error) {
        return Swal.fire({
            title: "PhilHealth API Notice",
            text: typeof error === "string" ? error : JSON.stringify(error),
            icon: "warning"
        });
    }

    $("#download-masterlist-btn").on("click", function () {
        Swal.showLoading();
        $.ajax({
            url: "/api/fetch_registrations",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        title: "Registration List",
                        text: "Registration list fetched successfully.",
                        icon: "success"
                    });
                } else {
                    showError(response.message || "Failed to fetch registrations.");
                }
            },
            error: function (xhr) {
                Swal.close();
                const err = xhr.responseJSON?.message || "Failed to connect to PhilHealth service.";
                showError(err);
            }
        });
    });

    $("#btn-check-md").on("click", function (e) {
        e.preventDefault();
        const pin = $("#check-pin").val();
        const type = $("#check-type").val();

        if (!pin) {
            return Swal.fire({ title: "PIN Required", text: "Please enter the patient PIN.", icon: "info" });
        }

        Swal.showLoading();
        $.ajax({
            url: "/api/check_registration",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { pin: pin, type: type },
            success: function (response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        title: "Registration Status",
                        text: JSON.stringify(response.data),
                        icon: "info"
                    });
                } else {
                    showError(response.message || "Check failed.");
                }
            },
            error: function (xhr) {
                Swal.close();
                const err = xhr.responseJSON?.message || "Registration verification failed.";
                showError(err);
            }
        });
    });

    $("#btn-validate-atc").on("click", function (e) {
        e.preventDefault();
        const pin = $("#atc-pin").val();
        const atc = $("#atc").val();
        const date = $("#atc-date").val();

        if (!pin || !atc) {
            return Swal.fire({ title: "Fields Required", text: "Please enter PIN and ATC code.", icon: "info" });
        }

        Swal.showLoading();
        $.ajax({
            url: "/api/validate_atc",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                pin: pin,
                atc: atc,
                effectivity_date: date
            },
            success: function (response) {
                Swal.close();
                if (response.success) {
                    Swal.fire({
                        title: "ATC Validated",
                        text: JSON.stringify(response.data),
                        icon: "success"
                    });
                } else {
                    showError(response.message || "ATC validation failed.");
                }
            },
            error: function (xhr) {
                Swal.close();
                const err = xhr.responseJSON?.message || "ATC validation failed.";
                showError(err);
            }
        });
    });
});
