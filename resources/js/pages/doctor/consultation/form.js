$(function () {
    $("#consultation_modal").on("shown.bs.modal", function () {
        loadMedicalHistory();
    });

    $("#close-consultation-modal").on("click", function () {
        Swal.fire({
            title: "Close Form?",
            text: "Make sure patient data are saved, the patient will still be marked as PENDING if not yet COMPLETED.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Close"
        }).then((result) => {
            const modal = bootstrap.Modal.getInstance("#consultation_modal");

            if (result.isConfirmed) {
                modal.hide();
            }
        });
    });

    $("#medhistory_btn").on("click", function () {
        loadMedicalHistory();
    });

    function loadMedicalHistory() {
        if ($.fn.DataTable.isDataTable("#medhistory_table")) {
            $("#medhistory_table").DataTable().destroy().clear();
        }

        $("#medhistory_table").DataTable({
            ajax: {
                url: "/api/fetch_patient_history",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() }
            },
            columns: [
                { data: 'photo_path' },
                { data: 'consultation_date' }
            ],
            columnDefs: [
                { target: 0, wdith: "1%", className: "text-center text-nowrap align-middle" },
                { target: 1, width: "1%", className: "text-center text-nowrap align-middle" }
            ],
            select: { style: "single" },
            language: { emptyTable: "No previous consultations yet." },
            info: false,
            paging: true,
            ordering: false,
            responsive: true
        });
    }

    // Save chief complaints, impressions, and diagnosis
    $("#save_impressions_diagnosis").on("click", function () {
        $.ajax({
            url: "/api/save_impressions_diagnosis",
            type: "POST",
            data: {
                consultationrefno: $("#consultationrefno").val(),
                reasonforconsultation: $("#reasonforconsultation").val(),
                impressions: $("#impressions").val(),
                diagnosis: $("#diagnosis").val()
            },
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Details saved',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    });

    // Generate RX modal
    $("#mymed").select2({
        width: '100%',
        dropdownParent: $("#rx_modal"),
        ajax: {
            url: "/api/fetch_medicines",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: function (params) { return { term: params.term }; },
            processResults: function (response) {
                return {
                    results: $.map(response.rx || response, function (i) {
                        return {
                            id: i.prodcode,
                            text: i.prod_itemdscr
                        }
                    })
                }
            }
        },
        placeholder: "Search for medicine...",
        // minimumInputLength: 1
    });

    $("#add_rx").on("click", function () {
        $.ajax({
            url: "/api/add_medicine",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val(), prodcode: $("#mymed").val(), qty: $("#myquantity").val() },
            success: function (response) {
                if (response.success) {
                    loadRx();
                }
            }
        });
    });

    $(document).on("click", ".delete_rx", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Delete this from the list of patient RX?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_rx",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        prodcode: $(this).val(),
                        consultationrefno: $("#consultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Medicine Deleted',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $("#rx_table").DataTable().ajax.reload();
                        }
                    }
                });
            }
        });
    })

    function loadRx() {
        $("#rx_table").DataTable().destroy().clear();
        $("#rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: 'rx'
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger delete_rx" value="${data.prodcode}"><i class="fa-solid fa-trash"></i></button>`;
                    }
                },
                { data: 'item_dscr' },
                { data: 'qty' },
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    className: 'text-nowrap text-center items-align-center'
                },
                {
                    targets: [0, 1],
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap align-middle'
                },
                {
                    target: 2,
                    width: '1%',
                    className: 'text-center text-nowrap'
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            pageLength: 5,
            info: false,
            lengthChange: false,
            paging: true,
            searching: true,
            ordering: false,
            responsive: true
        });
        $("#myrx_form")[0].reset();
    }

    $("#rx_sidebar_btn").on("click", function () {
        loadDashboardRx();
    });

    function loadDashboardRx() {
        $("#dashboard_rx_table").DataTable().destroy().clear();
        $("#dashboard_rx_table").DataTable({
            ajax: {
                url: "/api/fetch_medicine_rx",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: "rx"
            },
            columns: [
                { data: 'item_dscr' },
                { data: 'qty' },
                { data: 'dispensed_status' },
            ],
            columnDefs: [
                {
                    targets: [0, 1, 2],
                    width: '10%',
                    orderable: false,
                    searchable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            language: {
                emptyTable: "No records yet."
            },
            pageLength: 5,
            lengthChange: false,
            info: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
            initComplete: function (settings, json) {
                $("#pxinstructions").text(json.instructions ? json.instructions[0] : '');
            }
        });
    }

    $("#save_rx_btn").on("click", function () {
        $.ajax({
            url: "/api/save_rx",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                pxinstructions: $("#pxinstructions").val()
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Successfully saved Rx.",
                        icon: "success"
                    });

                    $("#patient_instructions").val(response.instructions);
                }
            }
        });
    });

    // Diagnostic requests
    $("#dReqsTabBtn").on("click", function () {
        $("#diagnostics_table").DataTable().destroy().clear();
        $("#diagnostics_table").DataTable({
            ajax: {
                url: "/api/get_diagnostics",
                type: "POST",
                headers: {
                    "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                },
                data: {
                    consultationrefno: $("#consultationrefno").val()
                },
                dataSrc: "requested"
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `<button class="btn btn-sm btn-danger remove_request" value="${data.diagnostic_id}"><i class="fa-solid fa-trash"></i> Remove</button>`;
                    }
                },
                { data: 'prod_itemdscr' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: "1%",
                    orderable: false,
                    className: "text-center text-nowrap"
                },
                {
                    target: 1,
                    className: 'text-nowrap align-middle'
                }
            ],
            info: true,
            lengthChange: false,
            searching: false,
            order: [[1, 'asc']]
        });

        $("#print_diagnostics").attr("href", `print_diagnostics?consultationrefno=${$("#consultationrefno").val()}`);
    });

    $("#diag_to_consul").on("click", function () { $("#diagnostics_table").DataTable().ajax.reload(); });

    let diagnostics = [];
    $("#diagnostic_btn, #diagnostic_btn_2").on("click", function () {
        $.ajax({
            url: "/api/fetch_diagnostics_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                let optionsTab = $("#diagnostic_options");
                let requestedRef = response.requested.map(r => r.prodcode);
                let allDiagnosticOptions = [...response.available, ...response.requested];

                optionsTab.empty();

                allDiagnosticOptions.forEach(e => {
                    let isRequested = requestedRef.includes(e.prodcode);

                    const entry = `
                        <div class="form-check">
                            <input class="form-check-input diagnostic_check"
                                type="checkbox"
                                value="${e.prodcode}"
                                id="diag_${e.prodcode}"
                                name="${e.prodcode}"
                                ${isRequested ? 'checked disabled' : ''}
                            >
                            <label class="form-check-label" for="${e.prodcode}">${e.prod_itemdscr}</label>
                        </div>
                    `;

                    optionsTab.append(entry);
                });

                var table = $("#selected_table").DataTable();
                table.clear();

                response.requested.forEach(e => {
                    table.row.add([
                        `<button class="btn btn-sm btn-secondary" disabled><i class="fa-solid fa-floppy-disk"></i> Saved</button>`,
                        e.prod_itemdscr
                    ]);
                });

                table.draw();
            }
        });

        if ($.fn.DataTable.isDataTable("#selected_table")) {
            $("#selected_table").DataTable().clear().destroy();
        }

        $("#selected_table").DataTable({
            columnDefs: [
                { target: 0, width: "1%", orderable: false, className: "text-nowrap text-center" },
                { target: 1, className: "text-nowrap align-middle" },
            ],
            order: [[1, 'asc']],
            pageLength: 10,
            lengthChange: false,
            searching: false,
            info: false
        });
    });

    $(document).on("change", ".diagnostic_check", function () {
        var table = $("#selected_table").DataTable();

        if ($(this).is(":checked")) {
            let value = $(this).attr("name");

            table.row.add([
                `<button class="btn btn-sm btn-danger remove-request-btn" data-id="${value}"><i class="fa-solid fa-trash"></i> Remove</button>`,
                $(`label[for="${value}"]`).text().trim()
            ]).draw();

            diagnostics.push(value);
            $(this).prop("disabled", true);
        }
    });

    $(document).on("click", ".remove-request-btn", function () {
        var table = $("#selected_table").DataTable();
        let diagId = $(this).data("id");

        table.row($(this).closest("tr")).remove().draw();
        diagnostics = diagnostics.filter(ref => ref != diagId);
        $(`.diagnostic_check[name='${diagId}']`).prop("checked", false).prop("disabled", false);
    });

    $("#save_requests").on("click", function () {
        if (diagnostics.length === 0) return Swal.fire({title: "No updated changes", text: "No changes were made.", icon: "success"});

        $.ajax({
            url: "/api/save_diagnostics",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                diagnostics: diagnostics
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Diagnostics saved successfully.",
                        icon: "success"
                    });
                }
            }
        });
    });

    $(document).on("click", ".remove_request", function () {
        Swal.fire({
            title: "Confirmation",
            text: "Do you want to remove this request?",
            icon: "warning"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_diagnostic",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        requestrefno: $(this).val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Status updated',
                                showConfirmButton: false,
                                timer: 2000
                            }).then(() => {
                                $("#diagnostics_table").DataTable().ajax.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    // Radiology and Laboratory Upload
    $("#radLabTabBtn").on("click", function () {
        loadMedicalFiles();
    });

    function loadMedicalFiles() {
        $("#radiology_result").val("");
        $("#radiology_hasfile").removeClass("d-inline-block").addClass("d-none");
        $("#preview_radiology").prop("disabled", true);

        $("#laboratory_result").val("");
        $("#laboratory_hasfile").removeClass("d-inline-block").addClass("d-none");
        $("#preview_laboratory").prop("disabled", true);

        $.ajax({
            url: "/api/fetch_radlab_files",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: {
                consultationrefno: $("#consultationrefno").val()
            },
            success: function (response) {
                if (response.files.radiologypath) {
                    $("#radiology_hasfile").removeClass("d-none").addClass("d-inline-block");

                    $("#preview_radiology").prop("disabled", false);
                    $("#preview_radiology").attr("data-filepath", response.files.radiologypath);
                }

                if (response.files.laboratorypath) {
                    $("#laboratory_hasfile").removeClass("d-none").addClass("d-inline-block");

                    $("#preview_laboratory").prop("disabled", false);
                    $("#preview_laboratory").attr("data-filepath", response.files.laboratorypath);
                }
            }
        });
    }

    $("#update_files_btn").on("click", function () {
        let formData = new FormData($("#rad_lab_form")[0]);
        formData.append("consultationrefno", $("#consultationrefno").val());

        $.ajax({
            url: "/api/upload_consultation_files",
            type: "POST",
            headers: {
                "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
            },
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: 'Status updated',
                        showConfirmButton: false,
                        timer: 2000
                    });
                }
            }
        });
    });

    $("#radiology_result").on("change", function () {
        $("#preview_radiology").prop("disabled", false);
    });

    $("#laboratory_result").on("change", function () {
        $("#preview_laboratory").prop("disabled", false);
    });

    $("#preview_modal").on("show.bs.modal", function () {
        const src = this.dataset.src;
        document.getElementById("docPreview").src = src;
        document.getElementById("docPreview").style.display = "block";
    });

    $("#preview_modal").on("hidden.bs.modal", function () {
        document.getElementById("docPreview").src = "";
        document.getElementById("docPreview").style.display = "none";

        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.show();
    });

    $("[id^=preview_]").on("click", function () {
        const type = this.id.replace("preview_", "");
        const fileInput = document.getElementById(type + "_result");
        const storedPath = this.dataset.filepath;

        let fileURL = "";

        if (fileInput && fileInput.files.length) {
            fileURL = URL.createObjectURL(fileInput.files[0]);
        } else if (storedPath && storedPath.trim() !== "") {
            fileURL = "/preview-file/" + storedPath;
        }

        if (!fileURL) return;

        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.hide();

        const previewModalEl = document.getElementById("preview_modal");
        previewModalEl.dataset.src = fileURL;
        const previewModal = bootstrap.Modal.getOrCreateInstance(previewModalEl);
        previewModal.show();
    });

    // Load patient charges modal
    $("#search_charge").select2({
        width: '50%',
        dropdownParent: $("#append_charge_modal"),
        ajax: {
            url: "/api/fetch_all_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: function (params) { return { term: params.term, category: $("#search_filter").val() }; },
            processResults: function (response) {
                return {
                    results: $.map(response.charges, function (i) {
                        return {
                            id: i.prodcode,
                            text:i.prod_itemdscr,
                            price:i.price_regular
                        }
                    })
                }
            }
        },
        placeholder: "Search charges..."
    });

    $("#search_charge").on("select2:select", function () {
        const prodcode = this.value;
        const charge_amt = $("#charge_amount");

        $.ajax({
            url: "/api/get_hmo_price",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { prodcode: prodcode, consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                if (response.success) {
                    charge_amt.val(response.price);
                    return;
                }

                charge_amt.val("");
            }
        });
    });

    $("#append_charge_modal").on("shown.bs.modal", function () {
        $("#charge_amount").val(0);
    });

    $("#append_charge_btn, #append_charge_btn_2").on("click", function () {
        $("#search_charge").val("");
        $("#appended_charges_table tbody").empty();

        $.ajax({
            url: "/api/fetch_patient_charges",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            data: { consultationrefno: $("#consultationrefno").val() },
            success: function (response) {
                if (response.charges.length > 0) {
                    response.charges.forEach(c => {
                        appendedCharges.push({
                            prodcode: c.prodcode,
                            quantity: c.qty,
                            amount: c.totalamt
                        });

                        const newRow = `
                            <tr data-refno="${c.prodcode}">
                                <td class="align-middle text-center text-nowrap">
                                    <button type="button"
                                        class="btn btn-sm btn-secondary"
                                        data-refno="${c.prodcode}"
                                        title="Appended charges can only be removed from the patient charges tab."
                                    >
                                        <i class="fa-solid fa-floppy-disk"></i>
                                    </button>
                                </td>

                                <td class="align-middle text-nowrap">${c.item_dscr}</td>
                                <td class="align-middle text-nowrap">${c.qty}</td>
                                <td class="align-middle text-nowrap">${c.totalamt}</td>
                            </tr>
                        `;

                        $("#appended_charges_table tbody").append(newRow);
                    });
                } else {
                    $("#appended_charges_table tbody").append(`
                        <tr>
                            <td class="align-middle text-center text-nowrap" colspan="4">No charges yet.</td>
                        </tr>
                    `);
                }
            }
        });
    });

    $("#charge_category").on("change", function () { cache = null; });

    let appendedCharges = [];
    $("#append_to_charges_btn").on("click", function () {
        const form = document.getElementById("appended_charges_form");

        if (!form.checkValidity()) return form.reportValidity();

        const search = $("#search_charge");
        const description = $("#search_charge option:selected").text();
        const amount = $("#charge_amount").val();
        const quantity = $("#charge_qty").val();

        if (search.val() == "" || search.val() == null) {
            return Swal.fire({
                title: "Reminder!",
                text: "Please select a charge from the list.",
                icon: "error"
            });
        }

        if (quantity == 0) {
            return Swal.fire({
                title: "Reminder!",
                text: "Quantity must be higher than 0.",
                icon: "error"
            });
        }

        if (appendedCharges.some(item => item && item.prodcode === search.val())) {
            return Swal.fire({
                title: "Reminder!",
                text: "This charge has already been appended.",
                icon: "error"
            });
        }

        let table = $("#appended_charges_table tbody");
        if (appendedCharges.length == 0) {
            table.empty();
        }

        appendedCharges.push({
            prodcode: search.val(),
            quantity: quantity,
            amount: amount
        });

        const newRow = `
            <tr data-refno="${search.val()}">
                <td class="align-middle text-center text-nowrap">
                    <button type="button" class="btn btn-sm btn-danger charge_entry" data-refno="${search.val()}">
                        <i class="fa-solid fa-trash"></i>
                    </button>
                </td>
                <td class="align-middle">${description}</td>
                <td>${quantity}</td>
                <td>${amount}</td>
            </tr>
        `;

        table.append(newRow);
        search.val(null).trigger("change");

        $("#charge_amount").val(0);
        $("#charge_qty").val(0);
    });

    $(document).on("click", ".charge_entry", function () {
        const prodCode = $(this).data("refno");

        appendedCharges = appendedCharges.filter(item => item && item.prodcode !== prodCode);

        $(this).closest("tr").remove();

        if (appendedCharges.length === 0) {
            $("#appended_charges_table tbody").append(`
                <tr>
                    <td class="align-middle text-center text-nowrap" colspan="4">No charges yet.</td>
                </tr>
            `);
        }
    });

    $("#save_charges_btn").on("click", function () {
        if (appendedCharges.length === 0) {
            return Swal.fire({
                title: "Error",
                text: "Please append at least one charge before saving.",
                icon: "error"
            });
        }

        $.ajax({
            url: "/api/save_patient_charges",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: {
                consultationrefno: $("#consultationrefno").val(),
                chargerefnos: appendedCharges
            },
            success: function (response) {
                if (response.success) {
                    Swal.fire({
                        title: "Success",
                        text: "Charges successfully saved.",
                        icon: "success"
                    });
                    appendedCharges = [];
                    $("#appended_charges_list").empty();
                } else {
                    Swal.fire({
                        title: "Error",
                        html: response.message,
                        icon: "error"
                    });
                }
            }
        });

        $("#charges_table").DataTable().ajax.reload();
    });

    $("#patient_charge_tab_btn").on("click", function () {
        $("#charges_table").DataTable().destroy().clear();
        $("#charges_table").DataTable({
            ajax: {
                url: "/api/fetch_patient_charges",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { consultationrefno: $("#consultationrefno").val() },
                dataSrc: function (response) {
                    let total = 0;

                    response.charges.forEach(charge => {
                        total += parseFloat(charge.totalamt);
                    });

                    $("#charges_total").text('₱' + total.toFixed(2));

                    return response.charges;
                }
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        return `
                            <div class="d-flex gap-1">
                                <button class="btn btn-sm btn-danger remove_charge_btn" value="${data.id}"><i class="fa-solid fa-trash"></i></button>
                                <button class="btn btn-sm btn-primary edit_charge_btn" value="${data.id}"><i class="fa-solid fa-pen-to-square"></i></button>
                            </div>
                            `;
                    }
                },
                { data: 'item_dscr' },
                { data: 'qty' },
                { data: 'cost_ave' },
                { data: 'totalamt' }
            ],
            columnDefs: [
                {
                    target: 0,
                    width: '1%',
                    orderable: false,
                    className: 'text-nowrap text-center align-middle'
                }
            ],
            layout: {
                bottomStart: 'paging',
                bottomEnd: {
                    div: {
                        html: `<h4 class="fw-bold">Total:<span class="fw-normal ms-2" id="charges_total"></span></h4>`
                    }
                }
            },
            lengthChange: false,
            pageLength: 10,
            info: false,
            paging: true,
            searching: false,
            ordering: false,
            responsive: true,
        });
    });

    $(document).on("click", ".remove_charge_btn", function () {
        Swal.fire({
            title: "Remove Charge?",
            text: "Are you sure you want to remove this charge from the patient's consultation?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Confirm"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/delete_patient_charge",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        chargeid: $(this).val(),
                        consultationrefno: $("#consultationrefno").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Success",
                                text: "Charge successfully removed.",
                                icon: "success"
                            }).then(() => {
                                $("#charges_table").DataTable().ajax.reload();
                            });
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".edit_charge_btn", function () {
        const consulModal = bootstrap.Modal.getInstance(document.getElementById("consultation_modal"));
        consulModal.hide();

        Swal.fire({
            title: "Edit Charge",
            html: `
                <div class="d-flex gap-2">
                    <div>
                        <label class="form-label" for="charge_qty">Quantity</label>
                        <input class="form-control" type="number" name="charge_qty" id="charge_qty">
                    </div>

                    <div>
                        <label class="form-label" for="charge_input_sw">Charge Fee</label>
                        <input class="form-control" type="number" name="charge_input_sw" id="charge_input_sw">
                    </div>

                    <div>
                        <label class="form-label" for="charge_input_sw">Charge Discount</label>
                        <input class="form-control" type="number" name="discount_input_sw" id="discount_input_sw">
                    </div>
                </div>
            `,
            confirmButtonText: "Update",
            showCancelButton: true,
            preConfirm: () => {
                const charge = document.getElementById("charge_input_sw").value;
                // const discount = document.getElementById("discount_input_sw").value;

                if (!charge) {
                    Swal.showValidationMessage("Input field is empty.");
                    return false;
                }

                return true;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                consulModal.show();

                $.ajax({
                    url: "update_charge",
                    type: "POST",
                    headers: {
                        "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content")
                    },
                    data: {
                        consultationrefno: $("#consultationrefno").val(),
                        pxchargerefno: $(this).val(),
                        charge_fee: $("#charge_input_sw").val(),
                        discount: $("#discount_input_sw").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'Charge updated',
                                showConfirmButton: false,
                                timer: 2000
                            });

                            $("#charges_table").DataTable().ajax.reload();
                        }
                    }
                });
            } else {
                consulModal.show();
            }
        });
    });
});
