// Helper function to format dates for display
function formatDateForDisplay(dateValue) {
    if (!dateValue) return "";

    // If already in MM/DD/YYYY format, return as is
    if (/^\d{2}\/\d{2}\/\d{4}$/.test(dateValue)) {
        return dateValue;
    }

    // If in YYYY-MM-DD format, convert to MM/DD/YYYY
    if (/^\d{4}-\d{2}-\d{2}$/.test(dateValue)) {
        var parts = dateValue.split("-");
        return parts[1] + "/" + parts[2] + "/" + parts[0];
    }

    return dateValue;
}

// Helper function to populate form fields with data
export function populateFormFields(data) {
    $.each(data, function (key, value) {
        var $element = $(`#${key}`);

        if ($element.length === 0) return; // Skip if element doesn't exist

        var elementType = $element.attr("type");
        var tagName = $element.prop("tagName").toLowerCase();

        // Handle checkboxes
        if (elementType === "checkbox") {
            $element.prop(
                "checked",
                value === "Y" || value === "1" || value === 1 || value === true,
            );
        }
        // Handle select elements
        else if (tagName === "select") {
            $element.val(value);
        }
        // Handle date inputs - format if necessary
        else if (elementType === "text" && $element.hasClass("datepicker")) {
            if (value) {
                // Format date from YYYY-MM-DD to MM/DD/YYYY if needed
                var formattedDate = formatDateForDisplay(value);
                $element.val(formattedDate);
            }
        }
        // Handle regular text inputs, textareas, etc.
        else {
            $element.val(value);
        }
    });
}

// Helper function to populate profiles table
export function populateProfilesTable(profiles) {
    profiles.sort((a, b) => new Date(a.dTransDate) - new Date(b.dTransDate));
    var tbody = $("#profiles-table tbody");
    var addBtnContainer = $("#add-profile-btn-container");
    addBtnContainer.empty();
    tbody.empty();

    if (profiles.length === 0) {
        tbody.append(
            '<tr><td colspan="5" class="text-center">No profiles found.</td></tr>',
        );
        $("#add-profile-btn-container").append(
            '<button class="btn btn-sm btn-primary" onClick="addNewProfileRecord()">Add New Record</button>',
        );
        return;
    }

    $.each(profiles, function (_, profile) {
        tbody.append(`
            <tr>
                <td><button type="button" class="btn btn-link p-0 m-0 align-baseline border-0 shadow-none" onclick="viewProfileDetails('${profile.dTransNo || ""}')" data-transno="${profile.dTransNo || ""}">${profile.dTransNo || ""}</button></td>
                <td>${profile.dTransDate ? formatDateForDisplay(profile.dTransDate) : ""}</td>
                <td>${profile.px_pin || ""}</td>
            </tr>
        `);
    });
}

// Helper function to populate soaps table
export function populateSoapsTable(soaps) {
    soaps.sort((a, b) => new Date(a.dTransDate) - new Date(b.dTransDate));
    var tbody = $("#consultation-table tbody");
    tbody.empty();

    if (soaps.length === 0) {
        tbody.append(
            '<tr><td colspan="5" class="text-center">No consultations found.</td></tr>',
        );
        return;
    }

    $.each(soaps, function (_, soap) {
        tbody.append(`
            <tr>
                <td><button type="button" class="btn btn-link p-0 m-0 align-baseline border-0 shadow-none" onclick="viewSoapDetails('${soap.pHciTransNo || ""}')" data-transno="${soap.pHciTransNo || ""}">${soap.pHciTransNo || ""}</button></td>
                <td>${soap.dTransDate ? formatDateForDisplay(soap.dTransDate) : ""}</td>
                <td>${soap.px_pin || ""}</td>
                <td>
                    <button class="btn btn-sm btn-primary me-2" onClick="generateEkas('${soap.pHciTransNo || ""}')">eKAS</button>
                    <button class="btn btn-sm btn-primary me-2" onClick="generateEpress('${soap.pHciTransNo || ""}')">ePresS</button>
                </td>
            </tr>
        `);
    });
}

export function hideModal(modalId) {
    const modal = bootstrap.Modal.getInstance(document.getElementById(modalId));
    if (modal) {
        modal.hide();
    }
}

export function showModal(modalId) {
    const el = document.getElementById(modalId);
    const existing = bootstrap.Modal.getInstance(el);
    (existing ?? new bootstrap.Modal(el)).show();
}

export function getAgeFromBirthdate(birthdate) {
    if (!birthdate) return null;

    const birth = new Date(birthdate);
    const today = new Date();
    let age = today.getFullYear() - birth.getFullYear();
    const m = today.getMonth() - birth.getMonth();

    if (m < 0 || (m === 0 && today.getDate() < birth.getDate())) {
        age--;
    }

    return age;
}
