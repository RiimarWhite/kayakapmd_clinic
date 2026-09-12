$(function () {
    function showError(error) {
        return Swal.fire({
            title: "An error occurred.",
            text: {error},
            icon: "warning"
        });
    }

    $("#download-masterlist-btn").on("click", function () {
        $.ajax({
            url: "/api/fetch_registrations",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Download Masterlist",
                        text: "X new records were found.",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Save"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            console.log("Downloaded XML.");
                        }
                    });
                } else {
                    showError(response.message);
                }
            }
        });
    });

    $("#btn-check-md").on("click", function () {
        $.ajax({
            url: "/api/checkRegistration",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                "pin": $("#check-pin").val(),
                "type": $("#check-type").val()
            },
            success: function (response) {
                //
            }
        })
    });

    $("#btn-validate-atc").on("click", function () {
        $.ajax({
            url: "/api/checkATC",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                "pin": $("#atc-pin").val(),
                "atc": $("#atc").val(),
                "effectivity_date": $("#atc-date").val()
            },
            success: function (response) {
                //
            }
        });
    });
});
