import $ from "jquery";
import { PaginationComponent } from "./../../../components/pagination.js";

let currentUploadId = null;
let pagination;

$(function () {
    // Initialize pagination component
    pagination = new PaginationComponent({
        container: "#pagination_container",
        onPageChange: function (page) {
            fetchEnlistmentUploads(page);
        },
        alignment: "justify-content-center",
    });

    // Fetch and populate table on page load
    fetchEnlistmentUploads(1);

    function fetchEnlistmentUploads(page = 1) {
        $.ajax({
            url: "/api/enlistment-uploads",
            type: "GET",
            data: {
                page: page,
            },
            success: function (response) {
                populateTable(response.data);
                pagination.render(response);
            },
            error: function (xhr) {
                const tbody = $("#uploads_tbody");
                tbody.html(
                    '<tr><td colspan="5" class="text-center text-danger">Failed to load data</td></tr>',
                );
                pagination.clear();
            },
        });
    }

    function populateTable(uploads) {
        console.log("Fetched uploads:", uploads);
        const tbody = $("#uploads_tbody");
        tbody.empty();

        if (uploads.length === 0) {
            tbody.html(
                '<tr><td colspan="5" class="text-center text-muted">No uploads found</td></tr>',
            );
            return;
        }

        uploads.forEach(function (upload) {
            const row = $("<tr></tr>");
            row.append(`<td>${escapeHtml(upload.UPLOAD_ID)}</td>`);
            row.append(`<td>${escapeHtml(upload.DATE_UPLOADED)}</td>`);
            row.append(`<td>${escapeHtml(upload.RANGE_DATE)}</td>`);
            row.append(`<td>${escapeHtml(upload.status)}</td>`);
            row.append(
                `<td>${upload.imported == "1901-01-01 00:00:00" ? "-" : escapeHtml(upload.imported)}</td>`,
            );
            row.append(`
                <td class="text-center">
                    <button class="btn btn-sm btn-primary import-btn" data-upload-id="${upload.UPLOAD_ID}">Import</button>
                </td>
            `);

            tbody.append(row);
        });
    }

    // Register import button listener once
    $(document).on("click", ".import-btn", function () {
        const uploadId = $(this).data("upload-id");
        const $btn = $(this);
        $btn.prop("disabled", true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>',
        );
        $("#resultMessage").html("");

        $.ajax({
            url: "/api/enlistment/parse",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                upload_id: uploadId,
            },
            success: function (response) {
                if (response.success) {
                    currentUploadId = uploadId;
                    displayParsedData(response);
                    $("#save-enlistment-btn").show();

                    const modalEl = document.getElementById("parsedXmlModal");
                    const existingModal = bootstrap.Modal.getInstance(modalEl);
                    if (existingModal) {
                        existingModal.dispose();
                    }
                    new bootstrap.Modal(modalEl).show();
                } else {
                    showError("Failed to parse XML: " + response.error);
                }
            },
            error: function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.error ||
                    "An error occurred while parsing the XML";
                showError(errorMsg);
            },
            complete: function () {
                $btn.prop("disabled", false).text("Import");
            },
        });
    });

    function displayParsedData(response) {
        const container = $("#parsedDataContainer");
        container.empty();

        // Add summary
        const summary = $('<div class="alert alert-info mb-3"></div>').text(
            `Total Assignments Found: ${response.count}`,
        );
        container.append(summary);

        if (response.count === 0) {
            container.append(
                '<p class="text-muted">No assignment records found in the XML.</p>',
            );
            return;
        }

        // Create table for assignments
        const table = $(
            '<table class="table table-sm table-bordered"></table>',
        );
        const thead = $('<thead class="table-light"></thead>');
        const headerRow = $(
            `<tr>
                <th>Date</th>
                <th>Birthday</th>
                <th>Ext.</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Middle Name</th>
                <th>PIN</th>
                <th>Sex</th>
                <th>Status</th>
                <th>Type</th>
                <th>Eff. Year</th>
                <th>Landline No.</th>
                <th>Member Category</th>
                <th>Description</th>
                <th>Mobile No.</th>
                <th>Package Type</th>
                <th>Primary Birthday</th>
                <th>Primary Ext. Name</th>
                <th>Primary First Name</th>
                <th>Primary Last Name</th>
                <th>Primary Middle Name</th>
                <th>Primary PIN</th>
                <th>Primary Sex</th>
            </tr>`,
        );

        // Get all unique column names from the data
        const allKeys = new Set();
        response.data.forEach((item) => {
            Object.keys(item).forEach((key) => allKeys.add(key));
        });
        const keys = Array.from(allKeys).sort();

        thead.append(headerRow);
        table.append(thead);

        // Add rows
        const tbody = $("<tbody></tbody>");
        response.data.forEach((assignment) => {
            const row = $("<tr></tr>");
            keys.forEach((key) => {
                const value = assignment[key] || "-";
                row.append(`<td>${escapeHtml(String(value))}</td>`);
            });
            tbody.append(row);
        });
        table.append(tbody);

        // Add scroll container for large tables
        const scrollContainer = $('<div class="table-responsive"></div>');
        scrollContainer.append(table);
        container.append(scrollContainer);
    }

    // Helper function to escape HTML
    function escapeHtml(text) {
        const map = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&quot;",
            "'": "&#039;",
        };
        return text.replace(/[&<>"']/g, (m) => map[m]);
    }

    // Show error message
    function showError(message) {
        const errorHtml = `
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Error!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $("#resultMessage").html(errorHtml);
    }

    // Show success message
    function showSuccess(message) {
        const successHtml = `
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <strong>Success!</strong> ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        `;
        $("#resultMessage").html(successHtml);
    }

    // Save button click handler
    $(document).on("click", "#save-enlistment-btn", function () {
        if (!currentUploadId) {
            showError("No upload selected");
            return;
        }

        const $btn = $(this);
        const originalText = $btn.text();
        $btn.prop("disabled", true).html(
            '<span class="spinner-border spinner-border-sm me-2"></span>Saving...',
        );

        $.ajax({
            url: "/api/enlistment/save",
            type: "POST",
            data: {
                _token: $('meta[name="csrf-token"]').attr("content"),
                upload_id: currentUploadId,
            },
            success: function (response) {
                if (response.success) {
                    const message = `Successfully saved ${response.saved_count} out of ${response.total_count} enlistment records.`;
                    showSuccess(message);
                    $btn.hide();
                    currentUploadId = null;
                } else {
                    showError("Failed to save enlistment: " + response.error);
                }
                fetchEnlistmentUploads(1);
            },
            error: function (xhr) {
                const errorMsg =
                    xhr.responseJSON?.error ||
                    "An error occurred while saving the enlistment";
                showError(errorMsg);
            },
            complete: function () {
                $btn.prop("disabled", false).text(originalText);
            },
        });
    });
});
