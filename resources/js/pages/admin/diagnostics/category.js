$(function () {
    loadCategoryTable();

    function loadCategoryTable() {
        $("#diagnostic_category_table").DataTable().destroy().clear();
        $("#diagnostic_category_table").DataTable({
            ajax: {
                url: "/api/fetch_diagnostic_category",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: 'categories'
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-danger remove_dia_catg" value="${data.category_refno}"><i class="fa-solid fa-trash"></i> Remove</button>`; } },
                { data: 'category_name' }
            ],
            columnDefs: [{ target: 0, width: '1%', orderable: false, className: 'text-nowrap text-center' }],
            searching: false, info: false, pageLength: 20, lengthChange: false,
        });
    }

    $("#create_diagnostic_category_btn").on("click", function () {
        Swal.fire({
            title: "Create Diagnostic Category",
            html: `<input id="diagnostic_category_input" class="form-control" placeholder="Enter category name">`,
            showCancelButton: true, confirmButtonText: "Create",
            preConfirm: () => {
                const value = document.getElementById("diagnostic_category_input").value;
                if (!value) Swal.showValidationMessage("Please enter a category name.");
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/create_diagnostic_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { categoryname: result.value },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Category created!', showConfirmButton: false, timer: 1500 });
                            loadCategoryTable();
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".remove_dia_catg", function () {
        Swal.fire({ title: "Confirmation", text: "Delete this category?", icon: "warning", confirmButtonText: "Confirm", showCancelButton: true })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/delete_diagnostic_category",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { refno: $(this).val() },
                        success: function (response) {
                            if (response.success) {
                                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Category deleted!', showConfirmButton: false, timer: 1500 });
                                loadCategoryTable();
                            }
                        }
                    });
                }
            });
    });
});
