/**
 * Detailed Comment: Groupings Management JavaScript controller for standalone page /admin/stocks/groupings.
 * Handles category-specific grouping DataTables list, category switching, modal creation, editing,
 * and deletion of groups (e.g. Imaging: xray, mri, ct scan, ultrasound, ob ultrasound, 2d echo;
 * Drugs: DRUGS AND MEDS, MEDICAL SUPPLIES).
 */
$(function () {
    let groupingsTable = null;
    let activeCategory = 'ALL';

    // Initialize DataTable for Groupings
    initGroupingsTable();

    function initGroupingsTable() {
        if (groupingsTable) {
            groupingsTable.destroy();
        }

        groupingsTable = $("#groupings_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/stocks/fetch_groupings",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: function (d) {
                    d.category = activeCategory;
                }
            },
            columns: [
                {
                    data: null,
                    className: 'text-center text-nowrap align-middle',
                    orderable: false,
                    searchable: false,
                    render: function (data) {
                        return `
                            <div class="btn-group btn-group-sm">
                                <button type="button" class="btn btn-primary edit_grouping" 
                                    data-id="${data.id}" 
                                    data-category="${data.category}" 
                                    data-group-name="${data.group_name}" 
                                    data-group-code="${data.group_code || ''}" 
                                    data-description="${data.description || ''}" 
                                    data-status="${data.status || 'ACTIVE'}" 
                                    title="Edit Grouping">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button type="button" class="btn btn-danger delete_grouping" 
                                    data-id="${data.id}" 
                                    data-group-name="${data.group_name}" 
                                    title="Delete Grouping">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    data: 'category',
                    className: 'align-middle',
                    render: function (data) {
                        let badgeColor = 'bg-secondary';
                        if (data === 'DRUGS AND MEDS') badgeColor = 'bg-success';
                        else if (data === 'IMAGING') badgeColor = 'bg-info text-dark';
                        else if (data === 'DIAGNOSTIC') badgeColor = 'bg-warning text-dark';
                        else if (data === 'SUPPLIES') badgeColor = 'bg-primary';
                        else if (data === 'PROCEDURES') badgeColor = 'bg-danger';

                        return `<span class="badge ${badgeColor} px-2 py-1">${data}</span>`;
                    }
                },
                {
                    data: 'group_name',
                    className: 'align-middle fw-semibold',
                    render: function (data) {
                        return `<span class="text-capitalize">${data}</span>`;
                    }
                },
                {
                    data: 'group_code',
                    className: 'align-middle font-monospace text-muted small',
                    render: function (data) {
                        return data ? `<code>${data}</code>` : '-';
                    }
                },
                {
                    data: 'description',
                    className: 'align-middle text-muted',
                    render: function (data) {
                        return data || '<span class="fst-italic text-secondary">None</span>';
                    }
                },
                {
                    data: 'status',
                    className: 'text-center align-middle',
                    render: function (data) {
                        return data === 'ACTIVE' 
                            ? `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">Active</span>`
                            : `<span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1">Inactive</span>`;
                    }
                }
            ],
            language: {
                emptyTable: "No category groupings found.",
                search: "Search groupings:"
            },
            order: [[1, 'asc'], [2, 'asc']],
            pageLength: 15,
            lengthChange: true
        });
    }

    // Category Filter Button Handler
    $("#grouping_category_filter button").on("click", function () {
        $("#grouping_category_filter button").removeClass("active");
        $(this).addClass("active");
        activeCategory = $(this).data("category");
        groupingsTable.ajax.reload();
    });

    // Reset Add Form on Open
    $("#btn_open_add_grouping").on("click", function () {
        $("#add_grouping_form")[0].reset();
        if (activeCategory !== 'ALL') {
            $("#new_group_category").val(activeCategory);
        }
    });

    // Save Grouping AJAX Submission
    $("#add_grouping_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_save_grouping");
        const origHtml = $btn.html();
        $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Saving...');

        $.ajax({
            url: "/api/stocks/save_grouping",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function (res) {
                $btn.prop("disabled", false).html(origHtml);
                if (res.success) {
                    Swal.fire({
                        title: "Created!",
                        text: res.message || "Grouping added successfully.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById("add_grouping_modal"));
                        if (modal) modal.hide();
                        groupingsTable.ajax.reload();
                    });
                } else {
                    Swal.fire("Error", res.message || "Failed to create grouping.", "error");
                }
            },
            error: function (xhr) {
                $btn.prop("disabled", false).html(origHtml);
                const msg = xhr.responseJSON?.message || "Server error occurred while creating grouping.";
                Swal.fire("Error", msg, "error");
            }
        });
    });

    // Populate Edit Grouping Modal
    $(document).on("click", ".edit_grouping", function () {
        const btn = $(this);
        $("#edit_group_id").val(btn.data("id"));
        $("#edit_group_code").val(btn.data("group-code"));
        $("#edit_group_category").val(btn.data("category"));
        $("#edit_group_name").val(btn.data("group-name"));
        $("#edit_group_description").val(btn.data("description"));
        $("#edit_group_status").val(btn.data("status"));

        const modal = new bootstrap.Modal(document.getElementById("edit_grouping_modal"));
        modal.show();
    });

    // Update Grouping AJAX Submission
    $("#edit_grouping_form").on("submit", function (e) {
        e.preventDefault();
        const $btn = $("#btn_update_grouping");
        const origHtml = $btn.html();
        $btn.prop("disabled", true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: "/api/stocks/update_grouping",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $(this).serialize(),
            success: function (res) {
                $btn.prop("disabled", false).html(origHtml);
                if (res.success) {
                    Swal.fire({
                        title: "Updated!",
                        text: res.message || "Grouping updated successfully.",
                        icon: "success",
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        const modal = bootstrap.Modal.getInstance(document.getElementById("edit_grouping_modal"));
                        if (modal) modal.hide();
                        groupingsTable.ajax.reload();
                    });
                } else {
                    Swal.fire("Error", res.message || "Failed to update grouping.", "error");
                }
            },
            error: function (xhr) {
                $btn.prop("disabled", false).html(origHtml);
                const msg = xhr.responseJSON?.message || "Server error occurred while updating grouping.";
                Swal.fire("Error", msg, "error");
            }
        });
    });

    // Delete Grouping
    $(document).on("click", ".delete_grouping", function () {
        const id = $(this).data("id");
        const groupName = $(this).data("group-name");

        Swal.fire({
            title: "Delete Grouping?",
            text: `Are you sure you want to delete grouping "${groupName}"?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#d33",
            cancelButtonColor: "#6c757d",
            confirmButtonText: "Yes, delete it"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/stocks/delete_grouping",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { id: id },
                    success: function (res) {
                        if (res.success) {
                            Swal.fire({
                                title: "Deleted!",
                                text: res.message || "Grouping deleted successfully.",
                                icon: "success",
                                timer: 1500,
                                showConfirmButton: false
                            });
                            groupingsTable.ajax.reload();
                        } else {
                            Swal.fire("Error", res.message || "Failed to delete grouping.", "error");
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || "Server error occurred while deleting grouping.";
                        Swal.fire("Error", msg, "error");
                    }
                });
            }
        });
    });
});
