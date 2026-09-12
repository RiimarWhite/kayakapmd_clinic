$(function () {
    $("#hide-sidebar").on("click", function () {
        const sidebar = $("#admin_sidebar");

        if (sidebar.hasClass("d-flex")) {
            sidebar.removeClass("d-flex").addClass("d-none");
        } else {
            sidebar.removeClass("d-none").addClass("d-flex");
        }
    });
});