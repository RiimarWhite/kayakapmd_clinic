$(function () {
    loadPatientsReportTable();

    function loadPatientsReportTable() {
        if ($.fn.DataTable.isDataTable("#patients-report-table")) {
            $("#patients-report-table").DataTable().clear().destroy();
        }

        $("#patients-report-table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_patient_masterlist",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") }
            },
            columns: [
                { data: null, render: function () { return ""; } },
                { data: "px_pin", visible: false },
                {
                    data: "enlistment.dPatientLname",
                    render: function (data, type, row) {
                        if (row.enlistment) {
                            const p = row.enlistment;
                            return `${p.dPatientLname}, ${p.dPatientFname} ${p.dPatientMname}`;
                        }

                        return "No Patient Data";
                    }
                },
                { data: 'dSoapDate', render: function (data) {
                    return new Date(data.replace(" ", "T"))
                        .toLocaleDateString("en-US", {
                            month: "long",
                            day: "numeric",
                            year: "numeric"
                        })
                }},
                { data: 'report_code', render: function (data) {
                    return data != "" ? `
                    <button class="btn btn-sm btn-secondary report1" value="${data}" title="XML report preview."><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-sm btn-success encrypt" value="${data}" title="Encrypt this XML report."><i class="fa-solid fa-file-arrow-up"></i></button>
                    <button class="btn btn-sm btn-primary clipboard" value="${data}" title="${data}"><i class="fa-solid fa-clipboard"></i></button>
                    ` : "N/A";
                }},
                { data: 'report_code2', render: function (data) {
                    return data != "" ? `
                    <button class="btn btn-sm btn-secondary report2" value="${data}" title="XML report preview."><i class="fa-solid fa-eye"></i></button>
                    <button class="btn btn-sm btn-success encrypt" value="${data}" title="Encrypt this XML report."><i class="fa-solid fa-file-arrow-up"></i></button>
                    <button class="btn btn-sm btn-primary clipboard" value="${data}" title="${data}"><i class="fa-solid fa-clipboard"></i></button>
                    ` : "N/A";
                }}
            ],
            columnDefs: [
                { target: 0, className: "text-center align-middle", orderable: false, width: '2rem' },
                { targets: [2, 3], className: "text-nowrap align-middle" },
                { targets: [4, 5], width: "1%", className: "text-nowrap text-center align-middle", orderable: false }
            ],
            order: [[2, 'asc']],
            language: { emptyTable: "No data." },
            select: { style: "multi" }
        });
    }

    $("#xml-start, #xml-end").on("change", function () {

    });

    $(document).on("change", "#xml-select-all", function () {
        const table = $("#patients_report_table").DataTable();

        if ($(this).is(":checked")) {
            table.rows().select();
        } else {
            table.rows().deselect();
        }
    });

    $("#btn-generate-xml-report").on("click", function () {
        if (!$("#xml-tranche").val()) return Swal.fire({ title: "Select Report Type", text: "Select type of report.", icon: "warning" });

        const table = $("#patients-report-table").DataTable();
        const selected = table.rows({ selected: true }).data();

        if (selected.count() == 0) return Swal.fire({ title: "No user selected", text: "Select a user from the table first.", icon: "warning" });

        Swal.fire({
            title: "Generate XML",
            text: `Would you like to generate ${table.rows({ selected: true }).count()} report(s) now?`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/generate_xml_first_tranche",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {
                        data: selected.toArray(),
                        type: $("#xml-tranche").val(),
                        start: $("#xml-start").val(),
                        end: $("#xml-end").val()
                    },
                    success: function (response) {
                        if (response.success) {
                            const modal = new bootstrap.Modal("#generate_xml");
                            const escapedXml = response.document.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

                            $("#btn_download_xml").data("xml-content", response.document);
                            $("#xml_preview_frame").attr("srcdoc", `
                                <html>
                                    <body style="margin:0; padding:10px; background:#f4f4f4; font-family:monospace;">
                                        <pre style="white-space: pre-wrap;">${escapedXml}</pre>
                                    </body>
                                </html>
                            `);

                            modal.show();
                        } else {
                            return Swal.fire({ title: "Unable to proceeed", text: response.message ?? "An error ocurred.", icon: "error" });
                        }

                        table.ajax.reload();
                    }
                });
            }
        });
    });

    $(document).on("click", ".encrypt", function () {
        const table = $("#patients_report_table").DataTable();
        const selected = table.rows({selected: true}).data();

        Swal.fire({
            title: "Confirmation",
            text: "Proceed with the encryption?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes",
            cancelButtonText: "No"
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "/api/encrypt_xml",
                    type: "POST",
                    headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                    data: {reference: $(this).val()},
                    success: function (response) {
                        if (response.success) {
                            return Swal.fire({
                                toast: true,
                                position: 'top-end',
                                icon: 'success',
                                title: 'XML encrypted',
                                showConfirmButton: false,
                                timer: 1500
                            });
                        } else {
                            return Swal.fire({title: "An error ocurred", text: response.message ?? "An error ocurred.", icon: "error"});
                        }
                    }
                });
            }
        });
    });

    $(document).on("click", ".report1, .report2", function () {
        const reference = $(this).val();
        const type = $(this).hasClass("report1") ? "report1" : "report2";

        $.ajax({
            url: "/api/fetch_xml_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            data: { reference, type },
            success: function (response) {
                if (response.success) {
                    const modal = new bootstrap.Modal("#generate_xml");
                    const escapedXml = response.document.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");

                    $("#btn_download_xml").data("xml-content", response.document);
                    $("#xml_preview_frame").attr("srcdoc", `
                        <html>
                            <body style="margin:0; padding:10px; background:#f4f4f4; font-family:monospace;">
                                <pre style="white-space: pre-wrap;">${escapedXml}</pre>
                            </body>
                        </html>
                    `);

                    modal.show();
                } else {
                    return Swal.fire({ title: "Error", text: "An error ocurred.", icon: "error" });
                }
            }
        });
    });

    // In-modal functions
    $("#btn_save_xml").on("click", function () {
        const table = $("#patients_report_table").DataTable();
        const selected = table.rows({ selected: true }).data();

        $.ajax({
            url: "/api/save_xml_report",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token']").attr("content") },
            data: { data: $("#xml_preview_frame"), type: $("#xml_format").val() },
            success: function (response) {
                if (response.success) {
                    return Swal.fire({
                        title: "XML Saved!",
                        text: "XML was saved successfully",
                        icon: "success"
                    });
                }
            }
        });
    });

    $("#btn_download_xml").on("click", function () {
        const xmlContent = $(this).data("xml-content");
        if (!xmlContent) return;

        const blob = new Blob([xmlContent], { type: 'text/xml' });
        const url = URL.createObjectURL(blob);

        const format = $("#xml_format").val();
        var tranche = format == "first" ? "1" : "2";
        var accreno = "";

        $.ajax({
            url: "/api/get_xml_data",
            type: "POST",
            headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
            success: function (response) {
                if (response.success) {
                    accreno = response.data;

                    const date = new Date();
                    const name = `${tranche}${accreno}_${date.toISOString().split('T')[0].replace(/-/g, '')}_transmittalNumber.xml.enc`;
                    const a = document.createElement('a');

                    a.href = url;
                    a.download = name;
                    a.click();

                    URL.revokeObjectURL(url);
                }
            }
        });
    });
});
