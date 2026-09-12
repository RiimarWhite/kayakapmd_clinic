$(function () {
    loadDiagnosticsTable();

    function loadDiagnosticsTable() {
        $("#diagnostic_table").DataTable().destroy().clear();
        $("#diagnostic_table").DataTable({
            ajax: {
                url: "/api/fetch_diagnostics",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: 'results'
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-danger remove_diagnostic" value="${data.diagnosticrefno}"><i class="fa-solid fa-trash"></i> Remove</button>`; } },
                { data: 'diagnostic_name' },
                { data: 'category.category_name' }
            ],
            columnDefs: [{ target: 0, width: '1%', orderable: false, className: 'text-nowrap text-center' }],
            info: false,
            lengthChange: false,
            initComplete: function (settings, json) {
                let catg = $("#diagnostic_catg");
                catg.empty();
                json.categories.forEach(element => {
                    catg.append(`<option value="${element.category_refno}">${element.category_name}</option>`);
                });
            }
        });
    }

    $("#create_diagnostic_btn").on("click", function () {
        $.ajax({
            url: "/api/create_diagnostic",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { name: $("#diagnostic_name").val(), category: $("#diagnostic_catg").val() },
            success: function (response) {
                if (response.success) {
                    Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Record created!', showConfirmButton: false, timer: 1500 });
                    $("#diagnostic_table").DataTable().ajax.reload();
                }
            }
        });
    });

    $(document).on("click", ".remove_diagnostic", function () {
        Swal.fire({ title: "Confirmation", text: "Do you want to delete this record?", icon: "warning", confirmButtonText: "Confirm", showCancelButton: true })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/delete_diagnostic",
                        type: "POST",
                        data: { refno: $(this).val() },
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Record deleted!', showConfirmButton: false, timer: 1500 });
                                $("#diagnostic_table").DataTable().ajax.reload();
                            }
                        }
                    });
                }
            });
    });
});
