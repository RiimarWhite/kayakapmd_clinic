$(function () {
    if ($("#registration").length) {
        $("#isMem").trigger("click");
    }

    $("#isMem").on("click", function () {
        $("#ifMem").removeClass("d-flex").addClass("d-none");
        $("#pMemFname, #pMemLname, #pMemDob").prop("required", false);
        $("#memPin").prop("required", true);
    });

    $("#isDep").on("click", function () {
        $("#ifMem").removeClass("d-none").addClass("d-flex");
        $("#pMemFname, #pMemLname, #pMemDob").prop("required", false);
        $("#memPin").prop("required", true);
    });

    $("#register_btn").on("click", function () {
        const form = document.getElementById("register_form");
        formData = form.serialize();

        $.ajax({
            url: "resgiter",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name=csrf-token']").attr("content") }

        })
    });
});