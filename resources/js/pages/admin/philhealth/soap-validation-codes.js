// Track active validation errors for real-time clearing
// Map<fieldKey, { target, tabSelector, safeKey }>
export const activeValidationErrors = new Map();

// ─── Cached selectors ────────────────────────────────────────────────────────
const $soapModal = $('#soap-details-modal');

// ─── Validation target map ────────────────────────────────────────────────────

/**
 * Map of validation error key → { type, selector, tabId }
 * Covers all 14 SOAP validation fields across three tabs.
 */
export const SOAP_VALIDATION_TARGETS = {
    // Client Profile tab
    'clientProfile.dSoapDate': {
        type: 'text',
        selector: '#dSoapDate',
        tabId: '#soap-client-profile',
    },
    'clientProfile.dATC': {
        type: 'text',
        selector: '#dATC',
        tabId: '#soap-client-profile',
    },
    'clientProfile.dIsWalkedIn': {
        type: 'radioGroup',
        selector: 'input[name="dIsWalkedIn"]',
        tabId: '#soap-client-profile',
    },
    'clientProfile.dCoPay': {
        type: 'text',
        selector: '#dCoPay',
        tabId: '#soap-client-profile',
    },

    // Subjective / History of Illness tab
    'subjectiveHistory.dPainSite': {
        type: 'text',
        selector: '#dPainSite',
        tabId: '#subjective-history-of-illness',
    },
    'subjectiveHistory.dOtherComplaint': {
        type: 'text',
        selector: '#dOtherComplaint',
        tabId: '#subjective-history-of-illness',
    },
    'subjectiveHistory.dIllnessHistory': {
        type: 'text',
        selector: '#dIllnessHistory',
        tabId: '#subjective-history-of-illness',
    },

    // Objective / Physical Examination tab
    'objectivePhysicalExamination.dSystolicSoap': {
        type: 'text',
        selector: '#dSystolicSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dDiastolicSoap': {
        type: 'text',
        selector: '#dDiastolicSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dHrSoap': {
        type: 'text',
        selector: '#dHrSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dRrSoap': {
        type: 'text',
        selector: '#dRrSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dTempSoap': {
        type: 'text',
        selector: '#dTempSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dHeightSoap': {
        type: 'text',
        selector: '#dHeightSoap',
        tabId: '#objective-physical-examination',
    },
    'objectivePhysicalExamination.dWeightSoap': {
        type: 'text',
        selector: '#dWeightSoap',
        tabId: '#objective-physical-examination',
    },
};

// ─── Public helpers ───────────────────────────────────────────────────────────

/**
 * Returns the { type, selector } descriptor for a given error key, or null if unknown.
 */
export function getSoapValidationTarget(fieldKey) {
    const entry = SOAP_VALIDATION_TARGETS[fieldKey];
    return entry ? { type: entry.type, selector: entry.selector } : null;
}

/**
 * Returns the tab button selector for a given error key, or null if unknown.
 */
export function getSoapTabButtonSelector(fieldKey) {
    const entry = SOAP_VALIDATION_TARGETS[fieldKey];
    return entry ? `#soap_tabs .tab-btn[data-bs-target="${entry.tabId}"]` : null;
}

/**
 * Clears the inline validation error for a single field key.
 *
 * @returns {string|null} The tab button selector for the cleared field, or null.
 */
export function clearSingleSoapFieldValidationError(fieldKey) {
    if (!$soapModal.length) return null;

    const errorInfo = activeValidationErrors.get(fieldKey);
    if (!errorInfo) return null;

    const { target, tabSelector, safeKey } = errorInfo;

    $soapModal.find(`[data-soap-validation-key="${safeKey}"]`).remove();

    if (target.type === 'text') {
        const $input = $soapModal.find(target.selector).first();
        $input.removeClass('is-invalid').off('.realtimeValidation');
    } else if (target.type === 'radioGroup') {
        $soapModal.find(target.selector).removeClass('is-invalid').off('.realtimeValidation');
    } else if (target.type === 'checkbox') {
        $soapModal.find(target.selector).first().removeClass('is-invalid').off('.realtimeValidation');
    } else if (target.type === 'checkboxGroup') {
        $soapModal.find(target.selector).removeClass('is-invalid').off('.realtimeValidation');
    }

    activeValidationErrors.delete(fieldKey);

    return tabSelector;
}

/**
 * Updates the error badge on a tab button based on whether any errors remain for that tab.
 */
export function updateSoapTabErrorBadge(tabButtonSelector) {
    if (!tabButtonSelector || !$soapModal.length) return;

    let hasErrors = false;
    for (const [, errorInfo] of activeValidationErrors) {
        if (errorInfo.tabSelector === tabButtonSelector) {
            hasErrors = true;
            break;
        }
    }

    const $tabBtn = $soapModal.find(tabButtonSelector).first();
    if (!$tabBtn.length) return;

    if (!hasErrors) {
        $tabBtn
            .removeClass('border border-danger')
            .find('.validation-error-badge').remove();
        $tabBtn.removeAttr('data-has-validation-error');
    }
}

/**
 * Attaches real-time validation listeners to all currently tracked error fields.
 */
export function attachSoapRealtimeValidationListeners() {
    if (!$soapModal.length) return;

    activeValidationErrors.forEach((errorInfo, fieldKey) => {
        const { target } = errorInfo;

        const onClear = function () {
            const tabSelector = clearSingleSoapFieldValidationError(fieldKey);
            updateSoapTabErrorBadge(tabSelector);
        };

        if (target.type === 'text') {
            const $input = $soapModal.find(target.selector).first();
            if ($input.length) $input.on('input.realtimeValidation', onClear);
        } else if (target.type === 'radioGroup') {
            const $radios = $soapModal.find(target.selector);
            if ($radios.length) $radios.on('change.realtimeValidation', onClear);
        } else if (target.type === 'checkbox') {
            const $checkbox = $soapModal.find(target.selector).first();
            if ($checkbox.length) $checkbox.on('change.realtimeValidation', onClear);
        } else if (target.type === 'checkboxGroup') {
            const $inputs = $soapModal.find(target.selector);
            if ($inputs.length) $inputs.on('change.realtimeValidation', onClear);
        }
    });
}
