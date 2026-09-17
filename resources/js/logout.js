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

    // Detailed Comment: Sign out of session with universal environment support (XAMPP, Docker Apache, and Artisan serve)
    $(document).on("click", "#logout", function (e) {
        e.preventDefault();
        Swal.fire({
            title: "Logout?",
            text: "Are you sure you want to logout?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Logout"
        }).then((result) => {
            if (result.isConfirmed) {
                const $form = $('#logout-form');
                // Detailed Comment: If native logout form is present, submit it directly to let the browser natively follow the redirect
                if ($form.length) {
                    $form.submit();
                } else {
                    // Detailed Comment: Fallback to dynamic AJAX endpoint if form is unavailable
                    const logoutUrl = $('meta[name="logout-url"]').attr('content') || 'logout';
                    const loginUrl = $('meta[name="login-url"]').attr('content') || 'login';

                    $.ajax({
                        url: logoutUrl,
                        type: "POST",
                        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                        success: function (response) {
                            window.location.href = (response && response.redirect) ? response.redirect : loginUrl;
                        },
                        error: function () {
                            window.location.href = loginUrl;
                        }
                    });
                }
            }
        });
    });
});