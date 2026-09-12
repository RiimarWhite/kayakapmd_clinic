$(function () {
    $("#management").on("click", function () {
        const managementModal = new bootstrap.Modal("#management_modal");

        $.ajax({
            url: "fetch_doctors_from_secretary",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            success: function (response) {
                if (response.success) {
                    var $select = $("#choose_doctor");
                    $select.empty();

                    response.doctors.forEach(element => {
                        $select.append(
                            $("<option>", {
                                value: element.docrefno,
                                text: "Dr. " + element.docname
                            })
                        );
                    });

                    managementModal.show();
                }
            }
        });
    });

    // Sign out of session
    $("#logout").on("click", function () {
        Swal.fire({
            title: "Logout?",
            text: "Are you sure you want to logout?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Logout"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/logout",
                    type: "POST",
                    headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                    success: function () {
                        window.location.href = "/login";
                    }
                });
            }
        });
    });
});