$(function () {
    loadHmoTable();

    function loadHmoTable() {
        if ($.fn.DataTable.isDataTable("#hmo-table")) {
            $("#hmo-table").DataTable().clear().destroy();
        }

        $("#hmo-table").DataTable({
            ajax: {
                url: "/api/fetch_all_hmo",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") }
            },
            columns: [
                { data: 'hmocode', render: function (data) {
                    return `
                        <button class='btn btn-sm btn-primary edit-hmo' value="${data}"><i class="fa-solid fa-pen-to-square"></i></button>
                        <button class='btn btn-sm btn-danger delete-hmo' value="${data}"><i class="fa-solid fa-trash"></i></button>
                    `;
                }},
                { data: 'hmoname' },
                { data: 'hmotype' }
            ],
            columnDefs: [
                { target: 0, width: "1%", orderable: false, className: 'text-nowrap' },
                { target: '_all', className: 'align-middle' }
            ],
            language: {
                emptyTable: "No records yet."
            },
            order: [[1, 'asc']]
        });
    }

    $("#btn-add-hmo").on("click", function () {
        $.ajax({
            url: "/api/add_hmo",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: $("#hmo-form").serialize(),
            success: function (response) {
                Swal.fire({
                    toast: true,
                    position: 'top-end',
                    icon: 'success',
                    title: 'HMO added',
                    showConfirmButton: false,
                    timer: 1500
                });

                $("#hmo-table").DataTable().ajax.reload();
            }
        });
    });

    $(document).on("click", ".edit-hmo", function () {
        const id = $(this).val();

        Swal.fire({
            title: "Edit HMO Details",
            html: `
                <form id="ehmo_form" class="d-flex flex-column text-start gap-3">
                    <div>
                        <label class="form-label">HMO Name</label>
                        <input class="form-control" type="text" name="hmo_ename" id="hmo_ename" placeholder="Enter HMO Name">
                    </div>

                    <div>
                        <label class="form-label">Type</label>
                        <select class="form-select" name="hmo_etype" id="hmo_etype">
                            <option value="HMO">HMO</option>
                            <option value="GOVERNMENT">Government</option>
                            <option value="COMPANY">Company</option>
                        </select>
                    </div>
                </form>
            `,
            showCancelButton: true,
            confirmButtonText: "Save Changes",
            preConfirm: () => {
                const name = Swal.getPopup().querySelector("#hmo_ename").value;
                const type = Swal.getPopup().querySelector("#hmo_etype").value;

                if (!name) {
                    Swal.showValidationMessage("HMO name must not be empty.");
                    return false;
                }

                return { name: name, type: type };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                let form = $("#ehmo_form").serialize();
                form += '&code=' + encodeURIComponent(id);

                $.ajax({
                    url: "/api/edit_hmo",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: form,
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'HMO added!',
                                showConfirmButton: false,
                                timer: 1500
                            });

                            $("#hmo-table").DataTable().ajax.reload();
                        } else {
                            return Swal.fire({
                                title: "Error!",
                                text: "An error occurred.",
                                icon: "error"
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".delete-hmo", function () {
        Swal.fire({
            title: "Confirm Deletion",
            text: "Do you want to delete this HMO entry?",
            icon: "warning",
            showCancelButton: true,
            conirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_hmo",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: { code: $(this).val() },
                    success: function (response) {
                        return Swal.fire({
                            toast: true,
                            position: 'top-end',
                            icon: 'success',
                            title: 'HMO deleted',
                            showConfirmButton: false,
                            timer: 1500
                        });
                    }
                });

                $("#hmo-table").DataTable().ajax.reload();
            }
        })
    });
});
