$(function () {
    loadCategoryTable();

    function loadCategoryTable() {
        $("#charge_category_table").DataTable().clear().destroy();
        $("#charge_category_table").DataTable({
            ajax: {
                url: "/api/fetch_charge_categories",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                dataSrc: 'categories'
            },
            columns: [
                { data: null, render: function (data) { return `<button class="btn btn-sm btn-primary edit-category" value="${data.categoryrefno}"><i class="fa-solid fa-pen-to-square"></i> Edit</button> <button class="btn btn-sm btn-danger delete-category" value="${data.categoryrefno}"><i class="fa-solid fa-trash-can"></i> Delete</button>`; } },
                { data: 'categoryname' }
            ],
            columnDefs: [{ target: 0, width: '1%', className: 'text-nowrap text-truncate text-center align-middle' }],
            ordering: false, paging: true, pageLength: 20, lengthChange: false, searching: true, responsive: true,
        });
    }

    $("#create_charge_category_btn").on("click", function () {
        Swal.fire({
            title: "Create Charge Category",
            html: `<input id="charge_category_input" class="form-control" placeholder="Enter category name">`,
            showCancelButton: true, confirmButtonText: "Create",
            preConfirm: () => {
                const value = document.getElementById("charge_category_input").value;
                if (!value) Swal.showValidationMessage("Please enter a category name.");
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/create_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { categoryname: result.value },
                    success: function () {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Category created!', showConfirmButton: false, timer: 1500 });
                        loadCategoryTable();
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit-category", function () {
        const refno = $(this).val();
        Swal.fire({
            title: "Edit category",
            html: `<input id="edit_category_input" class="form-control" placeholder="Enter new category name">`,
            showCancelButton: true, confirmButtonText: "Update",
            preConfirm: () => {
                const value = document.getElementById("edit_category_input").value;
                if (!value) Swal.showValidationMessage("Please enter a category name.");
                return value;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/edit_charge_category",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { categoryname: result.value, ecategoryrefno: refno },
                    success: function () {
                        Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Category updated!', showConfirmButton: false, timer: 1500 });
                        loadCategoryTable();
                    }
                });
            }
        });
    });

    $(document).on("click", ".delete-category", function () {
        Swal.fire({ title: "Confirmation", text: "Are you sure you want to delete this category?", icon: "warning", showCancelButton: true, confirmButtonText: "Confirm" })
            .then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "/api/delete_charge_category",
                        type: "POST",
                        headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                        data: { categoryrefno: $(this).val() },
                        success: function () {
                            Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Category deleted!', showConfirmButton: false, timer: 1500 });
                            loadCategoryTable();
                        }
                    });
                }
            });
    });
});
