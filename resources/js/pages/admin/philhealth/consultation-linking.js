/**
 * Consultation Linking Modal Module
 *
 * Handles the functionality for linking a pxwalkinconsultation record to the SOAP form.
 * Stores selected consultationrefno to window.currentConsultationRefNo for later saving.
 *
 * Exports: initLinkConsultationModal()
 */
let consultationSearchTimeout = null;

export function initLinkConsultationModal() {
    const $modal = $("#linkConsultationModal");
    const $searchInput = $("#consultation_search_input");
    const $resultsTableContainer = $("#consultation_results_table_container");
    const $resultsTable = $("#consultation_search_results_table tbody");
    const $noSearchMessage = $("#consultation_no_search_message");
    const $noResultsMessage = $("#consultation_no_results_message");
    const $searchLoading = $("#consultation_search_loading");
    const $linkErrorMessage = $("#consultation_link_error_message");
    const $paginationLinks = $("#consultation_pagination_links");

    if (!$modal.length) return;

    // When opened, auto-search using current patient name (best-effort)
    $modal.on("show.bs.modal", function () {
        const enlistmentData = window.enlistmentData;
        if (enlistmentData && enlistmentData.dPatientLname) {
            $searchInput.val(enlistmentData.dPatientLname);
            // Trigger search automatically
            setTimeout(() => {
                performConsultationSearch(enlistmentData.dPatientLname);
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

    // Debounced search
    $searchInput.on("input", function () {
        clearTimeout(consultationSearchTimeout);
        const searchTerm = $(this).val().trim();

        $noSearchMessage.addClass("d-none");
        $noResultsMessage.addClass("d-none");
        $resultsTableContainer.addClass("d-none");
        $linkErrorMessage.addClass("d-none");

        if (searchTerm.length < 2) {
            $noSearchMessage.removeClass("d-none");
            return;
        }

        consultationSearchTimeout = setTimeout(() => {
            performConsultationSearch(searchTerm);
        }, 500);
    });

    function performConsultationSearch(searchTerm, page = 1) {
        $searchLoading.removeClass("d-none");
        $noSearchMessage.addClass("d-none");
        $noResultsMessage.addClass("d-none");
        $resultsTableContainer.addClass("d-none");
        $linkErrorMessage.addClass("d-none");

        $.ajax({
            url: "/api/walkin-consultations/search",
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
                console.error("Consultation search error:", xhr);
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

        $.each(data.data, function (_, consult) {
            const fullName =
                [
                    consult.pxfirstname || "",
                    consult.pxmidname || "",
                    consult.pxlastname || "",
                    consult.pxsuffix || "",
                ]
                    .filter(Boolean)
                    .join(" ")
                    .trim() ||
                consult.patientname ||
                "N/A";

            const consultationRefNo = consult.consultationrefno || "";
            const consultationDate = consult.consultation_date || "";

            const row = `
                <tr>
                    <td>${fullName}</td>
                    <td class="fw-bold text-primary">${consultationRefNo}</td>
                    <td>${consultationDate}</td>
                    <td>
                        <button type="button"
                                class="btn btn-sm btn-primary link-consultation-action-btn"
                                data-consultationrefno="${consultationRefNo}"
                                data-patientname="${fullName}">
                            <i class="fa-solid fa-link"></i> Link
                        </button>
                    </td>
                </tr>
            `;
            $resultsTable.append(row);
        });

        renderPagination(data);

        $(".link-consultation-action-btn")
            .off("click")
            .on("click", handleLinkConsultation);
    }

    function renderPagination(data) {
        $paginationLinks.empty();

        if (!data.links || data.links.length <= 3) return;

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
                           class="btn btn-sm ${isActive} mx-1 consultation-pagination-link"
                           data-page="${page}"
                           ${link.active ? "disabled" : ""}>
                        ${link.label}
                    </button>`;
            })
            .join("");

        $paginationLinks.html(paginationHtml);

        $(".consultation-pagination-link")
            .off("click")
            .on("click", function () {
                const page = $(this).data("page");
                const searchTerm = $searchInput.val().trim();
                performConsultationSearch(searchTerm, page);
            });
    }

    function handleLinkConsultation() {
        const $btn = $(this);
        const consultationRefNo = $btn.data("consultationrefno");
        const patientName = $btn.data("patientname");

        if (!consultationRefNo) {
            $linkErrorMessage
                .text("Missing required information. Please try again.")
                .removeClass("d-none");
            return;
        }

        // Store globally for later save work
        window.currentConsultationRefNo = consultationRefNo;

        const confirmMessage = `Are you sure you want to link this consultation?`;

        Swal.fire({
            title: "Confirm Link",
            text: confirmMessage,
            icon: "question",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Yes, link consultation",
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
                url: "/api/soap/link-consultation",
                type: "POST",
                data: {
                    soapTransNo: window.currentSoapTransNo,
                    enlistmentCaseNo: window.selectedEnlistmentCaseNo,
                    consultCode: consultationRefNo,
                },
                headers: {
                    "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr(
                        "content",
                    ),
                },
                success: function (response) {
                    if (response.success) {
                        // Close the modal first using Bootstrap 5 API
                        const modalElement = document.getElementById(
                            "linkConsultationModal",
                        );
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
                            window.currentSoapTransNo = response.soapTransNo;
                            viewSoapDetails(window.currentSoapTransNo);
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

        // // Reflect on UI immediately
        // $("#consultCode").html(
        //     `<span class="fw-bold text-primary">${consultationRefNo}</span>`,
        // );
        // $('#soapClientProfileForm input[name="consultCode"]').val(
        //     consultationRefNo,
        // );

        // // Close search modal
        // const linkModalEl = document.getElementById("linkConsultationModal");
        // const linkModalInstance = bootstrap.Modal.getInstance(linkModalEl);
        // if (linkModalInstance) linkModalInstance.hide();

        // // Ensure SOAP modal is visible again (in case Bootstrap hid/focused it)
        // const soapModalEl = document.getElementById("soap-details-modal");
        // if (soapModalEl) {
        //     const soapModalInstance =
        //         bootstrap.Modal.getInstance(soapModalEl) ||
        //         new bootstrap.Modal(soapModalEl);
        //     soapModalInstance.show();
        // }
    }
}
