// Track active validation errors for real-time clearing
// Map<fieldKey, { target, tabSelector, safeKey }>
export const activeValidationErrors = new Map();

const MUST_BE_ARRAY_MESSAGE_REGEX = /must be an array/i;

/**
 * Laravel errors for array-typed inputs (e.g. "must be an array") and internal keys
 * like `enlistmentCaseNo` are not something the user can fix directly.
 */
export function shouldIgnoreProfileValidationError(fieldKey, message) {
    if (fieldKey === "enlistmentCaseNo") {
        return true;
    }

    if (
        typeof message === "string" &&
        MUST_BE_ARRAY_MESSAGE_REGEX.test(message)
    ) {
        return true;
    }

    return false;
}

export function getProfileValidationTarget(fieldKey) {
    // clientProfile
    if (fieldKey === "clientProfile.dATC") {
        return { type: "text", selector: "#dATC" };
    }

    if (fieldKey === "clientProfile.dProfDate") {
        return { type: "text", selector: "#dProfDate" };
    }

    if (fieldKey === "clientProfile.dIsWalkedIn") {
        return { type: "radioGroup", selector: 'input[name="dIsWalkedIn"]' };
    }

    // personalSocialHistory
    if (fieldKey === "personalSocialHistory.dNoCigpk") {
        return { type: "text", selector: "#dNoCigpk" };
    }

    if (fieldKey === "personalSocialHistory.dNoBottles") {
        return { type: "text", selector: "#dNoBottles" };
    }

    if (fieldKey === "personalSocialHistory.dIsSmoker") {
        return { type: "radioGroup", selector: 'input[name="dIsSmoker"]' };
    }

    if (fieldKey === "personalSocialHistory.dIsADrinker") {
        return { type: "radioGroup", selector: 'input[name="dIsADrinker"]' };
    }

    if (fieldKey === "personalSocialHistory.dIllDrugUser") {
        return { type: "radioGroup", selector: 'input[name="dIllDrugUser"]' };
    }

    if (fieldKey === "personalSocialHistory.dIsSexuallyActive") {
        return {
            type: "radioGroup",
            selector: 'input[name="dIsSexuallyActive"]',
        };
    }

    // pepert (pertinent physical exam vitals)
    const pepertMatch = fieldKey.match(
        /^pepert\.(dSystolic|dDiastolic|dHr|dRr|dTemp|dHeight|dWeight|dBMI|dLeftVision|dRightVision|dLength|dHeadCirc|dSkinfoldThickness|dMidUpperArmCirc|dWaist|dHip|dLimbs)$/,
    );
    if (pepertMatch) {
        const fieldName = pepertMatch[1];
        return { type: "text", selector: `#${fieldName}` };
    }

    // familyHistory
    if (fieldKey === "familyHistory.chkFamHistDiseases") {
        return {
            type: "checkboxGroup",
            selector: "#familyHistoryForm .fam-disease-checkbox",
            feedbackAfterSelector: "#familyHistoryForm .alert",
        };
    }

    const familyDiseaseSelectedMatch = fieldKey.match(
        /^familyHistory\.chkFamHistDiseases\.([^.]+)\.selected$/,
    );
    if (familyDiseaseSelectedMatch) {
        const diseaseCode = familyDiseaseSelectedMatch[1];
        return {
            type: "checkbox",
            selector: `input[name="chkFamHistDiseases[${diseaseCode}][selected]"]`,
        };
    }

    const familyDiseaseSpecifyMatch = fieldKey.match(
        /^familyHistory\.chkFamHistDiseases\.([^.]+)\.specify$/,
    );
    if (familyDiseaseSpecifyMatch) {
        const diseaseCode = familyDiseaseSpecifyMatch[1];
        return { type: "text", selector: `#specificFamCode${diseaseCode}` };
    }

    // medicalHistory
    if (fieldKey === "medicalHistory.chkMedHistDiseases") {
        return {
            type: "checkboxGroup",
            selector: "#medicalHistoryForm .disease-checkbox",
            feedbackAfterSelector: "#medicalHistoryForm .alert",
        };
    }

    const medicalDiseaseSelectedMatch = fieldKey.match(
        /^medicalHistory\.chkMedHistDiseases\.([^.]+)\.selected$/,
    );
    if (medicalDiseaseSelectedMatch) {
        const diseaseCode = medicalDiseaseSelectedMatch[1];
        return {
            type: "checkbox",
            selector: `input[name="chkMedHistDiseases[${diseaseCode}][selected]"]`,
        };
    }

    const medicalDiseaseSpecifyMatch = fieldKey.match(
        /^medicalHistory\.chkMedHistDiseases\.([^.]+)\.specify$/,
    );
    if (medicalDiseaseSpecifyMatch) {
        const diseaseCode = medicalDiseaseSpecifyMatch[1];
        return { type: "text", selector: `#specificCode${diseaseCode}` };
    }

    // // surgicalHistory
    // if (fieldKey === 'surgicalHistory.surgicalHistory') {
    //     return {
    //         type: 'checkboxGroup',
    //         selector: '#surgicalHistoryForm input[type="text"], #surgicalHistoryForm input[type="date"]',
    //         feedbackAfterSelector: '#surgicalHistoryForm .alert',
    //     };
    // }

    // const surgicalMatch = fieldKey.match(/^surgicalHistory\.surgicalHistory\.([^.]+)\.(operation|date)$/);
    // if (surgicalMatch) {
    //     const idx = surgicalMatch[1];
    //     const field = surgicalMatch[2];
    //     return {
    //         type: 'text',
    //         selector: `#surgicalHistoryForm input[name="surgicalHistory[${idx}][${field}]"]`,
    //     };
    // }

    return null;
}

export function clearSingleFieldValidationError(fieldKey) {
    const $modal = $("#profile-details-modal");
    if ($modal.length === 0) {
        return null;
    }

    const errorInfo = activeValidationErrors.get(fieldKey);
    if (!errorInfo) {
        return null;
    }

    const { target, tabSelector, safeKey } = errorInfo;

    // Remove the error message
    $modal.find(`[data-profile-validation-key="${safeKey}"]`).remove();

    // Remove .is-invalid class based on target type
    if (target.type === "text") {
        const $input = $(target.selector, $modal).first();
        $input.removeClass("is-invalid");
        // Remove the event listener
        $input.off(".realtimeValidation");
    } else if (target.type === "radioGroup") {
        const $radios = $(target.selector, $modal);
        $radios.removeClass("is-invalid");
        $radios.off(".realtimeValidation");
    } else if (target.type === "checkbox") {
        const $checkbox = $(target.selector, $modal).first();
        $checkbox.removeClass("is-invalid");
        $checkbox.off(".realtimeValidation");
    } else if (target.type === "checkboxGroup") {
        const $inputs = $(target.selector, $modal);
        $inputs.removeClass("is-invalid");
        $inputs.off(".realtimeValidation");
    }

    // Remove from tracking
    activeValidationErrors.delete(fieldKey);

    return tabSelector;
}

export function updateTabErrorBadge(tabButtonSelector) {
    if (!tabButtonSelector) {
        return;
    }

    const $modal = $("#profile-details-modal");
    if ($modal.length === 0) {
        return;
    }

    // Check if any errors still exist for this tab
    let hasErrors = false;
    for (const [fieldKey, errorInfo] of activeValidationErrors) {
        if (errorInfo.tabSelector === tabButtonSelector) {
            hasErrors = true;
            break;
        }
    }

    const $tabBtn = $modal.find(tabButtonSelector).first();
    if ($tabBtn.length === 0) {
        return;
    }

    if (!hasErrors) {
        // Remove error styling from tab
        $tabBtn.removeClass("border border-danger");
        $tabBtn.find(".validation-error-badge").remove();
        $tabBtn.removeAttr("data-has-validation-error");
    }
}

export function attachRealtimeValidationListeners() {
    const $modal = $("#profile-details-modal");
    if ($modal.length === 0) {
        return;
    }

    activeValidationErrors.forEach((errorInfo, fieldKey) => {
        const { target } = errorInfo;

        if (target.type === "text") {
            const $input = $(target.selector, $modal).first();
            if ($input.length === 0) {
                return;
            }

            // Use 'input' event for real-time feedback as user types
            $input.on("input.realtimeValidation", function () {
                const tabSelector = clearSingleFieldValidationError(fieldKey);
                updateTabErrorBadge(tabSelector);
            });
        } else if (target.type === "radioGroup") {
            const $radios = $(target.selector, $modal);
            if ($radios.length === 0) {
                return;
            }

            // Use 'change' event for radio buttons
            $radios.on("change.realtimeValidation", function () {
                const tabSelector = clearSingleFieldValidationError(fieldKey);
                updateTabErrorBadge(tabSelector);
            });
        } else if (target.type === "checkbox") {
            const $checkbox = $(target.selector, $modal).first();
            if ($checkbox.length === 0) {
                return;
            }

            // Use 'change' event for checkboxes
            $checkbox.on("change.realtimeValidation", function () {
                const tabSelector = clearSingleFieldValidationError(fieldKey);
                updateTabErrorBadge(tabSelector);
            });
        } else if (target.type === "checkboxGroup") {
            const $inputs = $(target.selector, $modal);
            if ($inputs.length === 0) {
                return;
            }

            // Use 'change' event for checkbox groups
            // Clear error when any checkbox in the group is changed
            $inputs.on("change.realtimeValidation", function () {
                const tabSelector = clearSingleFieldValidationError(fieldKey);
                updateTabErrorBadge(tabSelector);
            });
        }
    });
}
