/**
 * Patient Linking Modal Module
 *
 * Handles the functionality for linking patients from PatientMasterlist to Enlistments
 * Exports: initLinkPatientModal()
 */

let searchTimeout = null;

/**
 * Initialize the patient linking modal
 * Accesses window.enlistmentData globally
 */
export function initLinkPatientModal() {
    const $modal = $("#linkPatientModal");
    const $searchInput = $("#patient_search_input");
    const $resultsTableContainer = $("#results_table_container");
    const $resultsTable = $("#patient_search_results_table tbody");
    const $noSearchMessage = $("#no_search_message");
    const $noResultsMessage = $("#no_results_message");
    const $searchLoading = $("#search_loading");
    const $linkErrorMessage = $("#link_error_message");
    const $paginationLinks = $("#pagination_links");
    const $linkPatientBtn = $("#link_patient_btn");

    if (!$modal.length) return;

    // When the modal is opened, auto-populate search with patient's last name
    $modal.on("show.bs.modal", function () {
        const enlistmentData = window.enlistmentData;
        if (enlistmentData && enlistmentData.dPatientLname) {
            $searchInput.val(enlistmentData.dPatientLname);
            // Trigger search automatically
            setTimeout(() => {
                performPatientSearch(enlistmentData.dPatientLname);
            }, 300);
        }
    });

    // Reset modal when closed
    $modal.on("hidden.bs.modal", function () {
        $searchInput.val("");
        $resultsTable.empty();
        $resultsTableContainer.addClass("d-none");
        $noSearchMessage.removeClass("d-none");
        $noResultsMessage.addClass("d-none");
        $searchLoading.addClass("d-none");
        $linkErrorMessage.addClass("d-none");
        $paginationLinks.empty();
    });

    // Debounced search input handler
    $searchInput.on("input", function () {
        clearTimeout(searchTimeout);
        const searchTerm = $(this).val().trim();

        // Hide all messages initially
        $noSearchMessage.addClass("d-none");
        $noResultsMessage.addClass("d-none");
        $resultsTableContainer.addClass("d-none");
        $linkErrorMessage.addClass("d-none");

        if (searchTerm.length < 2) {
            $noSearchMessage.removeClass("d-none");
            return;
        }

        searchTimeout = setTimeout(() => {
            performPatientSearch(searchTerm);
        }, 500); // 500ms debounce
    });

    function performPatientSearch(searchTerm, page = 1) {
        $searchLoading.removeClass("d-none");
        $noSearchMessage.addClass("d-none");
        $noResultsMessage.addClass("d-none");
        $resultsTableContainer.addClass("d-none");
        $linkErrorMessage.addClass("d-none");

        $.ajax({
            url: "/api/patient-masterlist/search",
            type: "GET",
            data: { name: searchTerm, page: page },
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success: function (response) {
                $searchLoading.addClass("d-none");

                if (
                    response.success &&
                    response.data &&
                    response.data.data &&
                    response.data.data.length > 0
                ) {
                    populateSearchResults(response.data);
                    $resultsTableContainer.removeClass("d-none");
                } else {
                    $noResultsMessage.removeClass("d-none");
                }
            },
            error: function (xhr) {
                $searchLoading.addClass("d-none");
                console.error("Search error:", xhr);
                $linkErrorMessage
                    .text(
                        "An error occurred while searching. Please try again.",
                    )
                    .removeClass("d-none");
            },
        });
    }

    function populateSearchResults(data) {
        $resultsTable.empty();

        $.each(data.data, function (index, patient) {
            const fullName =
                [
                    patient.pxfirstname || "",
                    patient.pxmidname || "",
                    patient.pxlastname || "",
                    patient.pxsuffix || "",
                ]
                    .filter(Boolean)
                    .join(" ")
                    .trim() ||
                patient.patientname ||
                "N/A";

            const mobile = patient.mobilenumber || "N/A";
            const pincode = patient.pincode || "";

            const row = `
                <tr>
                    <td>${fullName}</td>
                    <td>${mobile}</td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-primary link-patient-action-btn"
                                data-pincode="${pincode}"
                                data-patientname="${fullName}">
                            <i class="fa-solid fa-link"></i> Link
                        </button>
                    </td>
                </tr>
            `;
            $resultsTable.append(row);
        });

        // Handle pagination
        renderPagination(data);

        // Attach event handlers to link buttons
        $(".link-patient-action-btn")
            .off("click")
            .on("click", handleLinkPatient);
    }

    function renderPagination(data) {
        $paginationLinks.empty();

        if (!data.links || data.links.length <= 3) return; // No pagination needed

        const paginationHtml = data.links
            .map((link) => {
                if (link.url === null) {
                    return `<span class="btn btn-sm btn-secondary disabled mx-1">${link.label}</span>`;
                }

                const isActive = link.active
                    ? "btn-primary"
                    : "btn-outline-primary";
                const page = new URL(link.url).searchParams.get("page");

                return `<button type="button"
                           class="btn btn-sm ${isActive} mx-1 pagination-link"
                           data-page="${page}"
                           ${link.active ? "disabled" : ""}>
                        ${link.label}
                    </button>`;
            })
            .join("");

        $paginationLinks.html(paginationHtml);

        // Attach event handlers to pagination links
        $(".pagination-link")
            .off("click")
            .on("click", function () {
                const page = $(this).data("page");
                const searchTerm = $searchInput.val().trim();
                performPatientSearch(searchTerm, page);
            });
    }

    function handleLinkPatient() {
        const $btn = $(this);
        const pincode = $btn.data("pincode");
        const patientName = $btn.data("patientname");

        // Use global enlistmentData
        const enlistmentData = window.enlistmentData;
        if (!enlistmentData || !enlistmentData.dCaseNo || !pincode) {
            $linkErrorMessage
                .text("Missing required information. Please try again.")
                .removeClass("d-none");
            return;
        }

        const caseNo = enlistmentData.dCaseNo;

        // Show confirmation dialog
        const confirmMessage = `Are you sure you want to link "${patientName}" (PIN: ${pincode}) to this enlistment?`;

        Swal.fire({
            title: "Confirm Link",
            text: confirmMessage,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, link patient",
            cancelButtonText: "Cancel",
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }

            // Disable button and show loading state
            const originalText = $btn.html();
            $btn.prop("disabled", true).html(
                '<i class="fa-solid fa-spinner fa-spin"></i> Linking...',
            );
            $linkErrorMessage.addClass("d-none");

            // Send link request to backend
            $.ajax({
                url: "/api/link-patient-pin",
                type: "POST",
                data: {
                    en_caseno: caseNo,
                    px_pin: pincode,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                    "Content-Type": "application/json",
                    Accept: "application/json",
                },
                contentType: "application/json",
                data: JSON.stringify({
                    en_caseno: caseNo,
                    px_pin: pincode,
                }),
                success: function (response) {
                    if (response.success) {
                        // Close the modal first using Bootstrap 5 API
                        const modalElement =
                            document.getElementById("linkPatientModal");
                        const modalInstance =
                            bootstrap.Modal.getInstance(modalElement);
                        if (modalInstance) {
                            modalInstance.hide();
                        }

                        // Show success message with SweetAlert2
                        Swal.fire({
                            position: "center",
                            icon: "success",
                            title: "Patient successfully linked!",
                            showConfirmButton: false,
                            timer: 1500,
                        }).then(() => {
                            // Update the UI after SweetAlert closes
                            $("#px_pin").text(pincode).removeClass("d-none");
                            $("#link_patient_btn").addClass("d-none");

                            // Reload enlistment details to refresh data
                            if (
                                typeof window.fetchEnlistmentDetails ===
                                    "function" &&
                                window.enlistmentData
                            ) {
                                window.fetchEnlistmentDetails(
                                    window.enlistmentData.dCaseNo,
                                );
                            }
                        });
                    } else {
                        $linkErrorMessage
                            .text(response.message || "Failed to link patient.")
                            .removeClass("d-none");
                        $btn.prop("disabled", false).html(originalText);
                    }
                },
                error: function (xhr) {
                    console.error("Link error:", xhr);
                    let errorMessage =
                        "An error occurred while linking the patient. Please try again.";

                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    }

                    // Show error in modal and reset button
                    $linkErrorMessage.text(errorMessage).removeClass("d-none");
                    $btn.prop("disabled", false).html(originalText);

                    // Also show SweetAlert for critical errors
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: errorMessage,
                    });
                },
            });
        });
    }
}
