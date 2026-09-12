import {
    getSoapValidationTarget,
    getSoapTabButtonSelector,
    activeValidationErrors,
    clearSingleSoapFieldValidationError,
    updateSoapTabErrorBadge,
    attachSoapRealtimeValidationListeners,
} from "./soap-validation-codes.js";
import { setConsultCode, resetMedsList, appendMedToList } from "./soap-form.js";

// ─── Cached selectors ────────────────────────────────────────────────────────
// Static elements that exist for the lifetime of the page
const $soapModal = $("#soap-details-modal");
const $diagOthRemarks = $("#diagnostic_oth_remarks1");
const $mgmtOthRemarks = $("#management_oth_remarks1");
const $diagnosisTableBody = $("#diagnosisTable tbody");
const $consultCode = $("#consultCode");

// ─── Helpers ─────────────────────────────────────────────────────────────────

/**
 * Returns a jQuery object scoped inside #soap-details-modal.
 * Use this for elements that may be re-rendered (dynamic content).
 *
 * @param {string} selector
 * @returns {jQuery}
 */
function $modal(selector) {
    return $soapModal.find(selector);
}

// ─── Tab population ───────────────────────────────────────────────────────────

/**
 * Populates the Client Profile tab fields inside #soap-details-modal.
 *
 * @param {Object} data - Top-level SOAP record with dIsWalkedIn, dATC, dCoPay, dSoapDate
 */
function populateClientProfileTab(data) {
    $modal('input[name="dIsWalkedIn"]').each(function () {
        $(this).prop("checked", $(this).val() === data.dIsWalkedIn);
    });

    $modal("#dATC").val(data.dATC ?? "");
    $modal("#dCoPay").val(data.dCoPay ?? "");
    $modal("#dSoapDate").val(data.dSoapDate ?? "");
}

/**
 * Populates the Subjective tab fields inside #soap-details-modal.
 *
 * @param {Array} subjective - Array of subjective records; uses subjective[0]
 */
function populateSubjectiveTab(subjective) {
    const record = subjective[0];
    if (!record) return;

    // Uncheck all signs-and-symptoms checkboxes first
    $modal('input[type="checkbox"][id^="signsSymptom"]').prop("checked", false);

    // Parse dSignsSymptoms and check matching checkboxes
    const symptomsStr = record.dSignsSymptoms ?? "";
    if (symptomsStr) {
        symptomsStr.split(";").forEach((id) => {
            const trimmed = id.trim();
            if (trimmed) {
                $modal(`#signsSymptom${trimmed}`).prop("checked", true);
            }
        });
    }

    $modal("#dPainSite").val(record.dPainSite ?? "");
    $modal("#dOtherComplaint").val(record.dOtherComplaint ?? "");
    $modal("#dIllnessHistory").val(record.dIllnessHistory ?? "");
}

/**
 * Populates the Objective tab fields inside #soap-details-modal.
 *
 * @param {Array} pepert     - Array of PE pertinent records; uses pepert[0]
 * @param {Array} peMisc     - Array of PE miscellaneous records
 * @param {Array} peSpecific - Array of PE specific records; uses peSpecific[0]
 */
function populateObjectiveTab(pepert, peMisc, peSpecific) {
    // --- Vital signs and anthropometrics from pepert[0] ---
    const pert = pepert && pepert[0] ? pepert[0] : {};

    const vitalMap = {
        "#dSystolicSoap": "dSystolic",
        "#dDiastolicSoap": "dDiastolic",
        "#dHrSoap": "dHr",
        "#dRrSoap": "dRr",
        "#dTempSoap": "dTemp",
        "#dLeftVisionSoap": "dLeftVision",
        "#dRightVisionSoap": "dRightVision",
        "#dHeightSoap": "dHeight",
        "#dWeightSoap": "dWeight",
        "#dBMISoap": "dBMI",
        "#dLengthSoap": "dLength",
        "#dHeadCircSoap": "dHeadCirc",
        "#dSkinfoldThicknessSoap": "dSkinfoldThickness",
        "#dWaistSoap": "dWaist",
        "#dHipSoap": "dHip",
        "#dLimbsSoap": "dLimbs",
        "#dMidUpperArmCircSoap": "dMidUpperArmCirc",
    };

    Object.entries(vitalMap).forEach(([selector, field]) => {
        $modal(selector).val(pert[field] ?? "");
    });

    // --- peMisc checkboxes: uncheck all PE groups first ---
    const peGroups = [
        "heent",
        "chest",
        "heart",
        "abdomen",
        "gu",
        "rectal",
        "skin",
        "neuro",
    ];
    peGroups.forEach((group) => {
        $modal(`[id^="soap_${group}_"]`).prop("checked", false);
    });

    // Map peMisc field names to checkbox prefix
    const miscFieldMap = {
        dHeentId: "soap_heent",
        dChestId: "soap_chest",
        dHeartId: "soap_heart",
        dAbdomenId: "soap_abdomen",
        dGuId: "soap_gu",
        dRectalId: "soap_rectal",
        dSkinId: "soap_skin",
        dNeuroId: "soap_neuro",
    };

    if (peMisc && peMisc.length) {
        peMisc.forEach((record) => {
            Object.entries(miscFieldMap).forEach(([field, prefix]) => {
                const val = record[field];
                if (val !== null && val !== undefined && val !== "") {
                    $modal(`#${prefix}_${val}`).prop("checked", true);
                }
            });
        });
    }

    // --- Remarks textareas from peSpecific[0] ---
    const specific = peSpecific && peSpecific[0] ? peSpecific[0] : {};

    const remarksMap = {
        "#dHeentRem": "dHeentRem",
        "#dChestRem": "dChestRem",
        "#dHeartRem": "dHeartRem",
        "#dAbdomenRem": "dAbdomenRem",
        "#dGuRem": "dGuRem",
        "#dRectalRem": "dRectalRem",
        "#dSkinRem": "dSkinRem",
        "#soap_dNeuroRem": "dNeuroRem",
    };

    Object.entries(remarksMap).forEach(([selector, field]) => {
        $modal(selector).val(specific[field] ?? "");
    });
}

/**
 * Populates the Assessment tab (#diagnosisTable) inside #soap-details-modal.
 * Clears existing rows first to prevent duplicates.
 *
 * @param {Array} icd - Array of ICD records with dIcdCode and icd_desc fields
 */
function populateAssessmentTab(icd) {
    // $diagnosisTableBody is a cached static selector — tbody itself never changes
    $diagnosisTableBody.empty();

    if (!icd || !icd.length) return;

    icd.forEach((record, index) => {
        const rowNum = index + 1;
        const code = record.dIcdCode ?? "";
        const desc = record.icd_desc ?? "";
        const displayText = desc ? `${code} - ${desc}` : code;

        $diagnosisTableBody.append(`
            <tr>
                <td>${rowNum}</td>
                <td data-refcode="${code}">${displayText}<input type="hidden" name="diagnoses[${code}][]" value="${code}"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onClick="removeDiagnosis(this)">Remove</button></td>
            </tr>
        `);
    });
}

/**
 * Populates the Plan/Management tab fields inside #soap-details-modal.
 *
 * @param {Array} diagnostics - Array of diagnostic records with dDiagnosticId, dIsPhysicianRecommend, dPatientRemarks
 * @param {Array} management  - Array of management records with pManagementId
 * @param {Array} advice      - Array of advice records; uses advice[0].dRemarks
 */
function populatePlanManagementTab(
    diagnostics,
    management,
    advice,
    labResults,
    diagnosisFromStocksLedger,
) {
    $diagOthRemarks.val("").prop("disabled", true);

    resetRadioButton();
    if (diagnostics && diagnostics.length) {
        diagnostics.forEach((record) => {
            const id = record.dDiagnosticId;

            if (id == "99") {
                $diagOthRemarks.val(record.dOthRemarks).prop("disabled", false);
            }

            if (id !== "0") {
                if (
                    typeof labResults[id] != "undefined" &&
                    record.dPatientRemarks == "RQ"
                ) {
                    $(`#status_${id}`).text(
                        labResults[id][`diagnostic_${id}_status`],
                    );
                } else {
                    if (record.dPatientRemarks == "RQ") {
                        $(`#status_${id}`).text("N");
                    } else {
                        $(`#status_${id}`).text("");
                    }
                }

                if (typeof labResults[id] != "undefined") {
                    if (labResults[id][`diagnostic_${id}_status`] == "V") {
                        $(`#status_${id}`).text(
                            labResults[id][`diagnostic_${id}_status`],
                        );
                    }

                    if (labResults[id][`diagnostic_${id}_status`] == "D") {
                        $(`input[name="diagnostic_doctor_reco[${id}]"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_doctorYes"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_doctorNo"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_doctorUnselect"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });

                        $(`input[name="diagnostic_patient[${id}]"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_patientRQ"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_patientRF"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                        $(`label[for="diagnostic_${id}_patientUnselect"`).css({
                            "pointer-events": "none",
                            opacity: "0.5",
                        });
                    }
                }
            }

            // Doctor recommendation radio — dynamic name attribute, use $modal()
            const $doctorRecoGroup = $modal(
                `input[name="diagnostic_doctor_reco[${id}]"]`,
            );
            $doctorRecoGroup.prop("checked", false);
            $modal(
                `input[name="diagnostic_doctor_reco[${id}]"][value="${record.dIsPhysicianRecommend}"]`,
            ).prop("checked", true);

            // Patient remarks radio — dynamic name attribute, use $modal()
            const $patientGroup = $modal(
                `input[name="diagnostic_patient[${id}]"]`,
            );
            $patientGroup.prop("checked", false);
            $modal(
                `input[name="diagnostic_patient[${id}]"][value="${record.dPatientRemarks}"]`,
            ).prop("checked", true);

            const match = diagnosisFromStocksLedger.find(
                (item) => item.phic_reference_code === id,
            );

            if (match) {
                $modal(
                    `input[name="diagnostic_doctor_reco[${id}]"][value="Y"]`,
                ).prop("checked", true);

                $modal(
                    `input[name="diagnostic_patient[${id}]"][value="RQ"]`,
                ).prop("checked", true);

                $modal(`input[name="diagnostic_patient[${id}]"][value="RQ"]`)
                    .closest("tr")
                    .addClass("table-warning");
            }
        });
    }

    // Uncheck all management checkboxes first
    $modal('[id^="management_"]').prop("checked", false);

    $mgmtOthRemarks.val("").prop("disabled", true);

    if (management && management.length) {
        management.forEach((record) => {
            if (record.pManagementId == "X") {
                $mgmtOthRemarks.val(record.pOthRemarks).prop("disabled", false);
            }
            // Dynamic id — use $modal()
            $modal(`#management_${record.pManagementId}`).prop("checked", true);
        });
    }

    if (advice && advice.length) {
        $modal("#dRemarks").val(advice[0].dRemarks ?? "");
    }
}

function resetRadioButton() {
    const ids = [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 13, 14, 15, 16, 17, 18, 19, 99];

    ids.forEach((id) => {
        $(`input[name="diagnostic_doctor_reco[${id}]"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_doctorYes"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_doctorNo"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_doctorUnselect"`).css({
            "pointer-events": "",
            opacity: "",
        });

        $(`input[name="diagnostic_patient[${id}]"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_patientRQ"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_patientRF"`).css({
            "pointer-events": "",
            opacity: "",
        });
        $(`label[for="diagnostic_${id}_patientUnselect"`).css({
            "pointer-events": "",
            opacity: "",
        });
    });
}

function populateMedicineTab(medicines) {
    resetMedsList();

    if (!medicines || !medicines.length) return;

    medicines.forEach((med) => {
        appendMedToList({
            drugDesc: med.generic_name ?? "",
            drugCode: med.drug_code,
            prodCode: med.prod_code ?? "",
            genCode: med.gen_code ?? "",
            saltCode: med.salt_code ?? "",
            strengthCode: med.strength_code ?? "",
            formCode: med.form_code ?? "",
            unitCode: med.unit_code ?? "",
            packageCode: med.package_code ?? "",
            genericName: med.generic_name ?? "",
            otherMedicine: med.dOtherMedicine ?? "",
            otherMedicineGrouping: med.dOthMedDrugGrouping ?? "",
            quantity: med.qty,
            actualPrice: med.actual_price,
            totalPrice: med.total_price,
            instructionQuantity: med.prescribed_quantity,
            instructionStrength: med.ins_strength,
            instructionFrequency: med.ins_frequency,
            physician: med.doc_name,
            isDispensed: med.is_dispensed,
            dispenseDate: med.dispensed_date,
            dispensingPersonnel: med.dispensedby,
        });
    });
}

/**
 * Shows each laboratory exam card only when Plan/Management has doctor reco "Yes" for that diagnostic id.
 */
export function syncLaboratoryPanelsVisibility() {
    if (!$soapModal.length) return;

    $soapModal.find(".lab-exam-panel").each(function () {
        const id = $(this).data("philhealth-diagnostic-id");
        if (id === undefined || id === null) return;

        const $yes = $soapModal.find(
            `input[name="diagnostic_patient[${id}]"][value="RQ"]`,
        );
        const show = $yes.length > 0 && $yes.is(":checked");
        $(this).toggle(!!show);
        $(this)
            .toggleClass("requested", !!show)
            .toggleClass("not-requested", !show);
    });
}

/**
 * @param {Record<string, Record<string, string>>|null|undefined} labResults
 */
export function populateLaboratoryTab(labResults, diagnosisFromStocksLedger) {
    if (!labResults || typeof labResults !== "object") return;

    Object.entries(labResults).forEach(([key, fields]) => {
        if (!fields || typeof fields !== "object") return;

        Object.entries(fields).forEach(([name, value]) => {
            const $fields = $modal(`[name="${name}"]`);
            if (!$fields.length) return;

            const $first = $fields.first();
            const type = ($first.attr("type") || "").toLowerCase();

            if (type === "radio") {
                $fields.prop("checked", false);
                const valStr =
                    value === null || value === undefined ? "" : String(value);
                $fields
                    .filter(function () {
                        return $(this).val() === valStr;
                    })
                    .prop("checked", true);
            } else if ($first.is("textarea")) {
                $first.val(value ?? "");
            } else {
                $first.val(value ?? "");
            }

            const match = name.match(/^diagnostic_(\d+)_lab_exam$/);

            if (match) {
                const n = match[1]; // extracted N
                const $textInput = $(`[name="diagnostic_${n}_accre_diag_fac"]`);
                if (value == "1") {
                    $textInput.prop("disabled", true);
                } else {
                    $textInput.prop("disabled", false);
                }
            }
        });
    });

    Object.entries(diagnosisFromStocksLedger).forEach(([key, value]) => {
        $modal(`[name="diagnostic_${value.phic_reference_code}_lab_fee"]`).val(
            value.retails,
        );
    });
}

// ─── Public API ───────────────────────────────────────────────────────────────

/**
 * Fetches GET /api/soap/{transNo}/details and populates all tabs of #soap-details-modal.
 *
 * @param {string} transNo
 * @returns {Promise<void>}
 */
export async function fetchAndPopulateSoapForm(transNo) {
    return new Promise((resolve) => {
        $.ajax({
            url: `/api/soap/${transNo}/details`,
            method: "GET",
            success(data) {
                const nextConsultCode = data.px_consultcode_cn ?? null;
                setConsultCode(nextConsultCode);
                if (nextConsultCode) {
                    $consultCode.html(
                        `<span class="fw-bold text-primary">${nextConsultCode}</span>`,
                    );
                    $('input[name="consultCode"]').val(nextConsultCode);
                } else {
                    $('input[name="consultCode"]').val("");
                    $consultCode.html(`
                        <a href="#" id="link_consultation_btn" class="" data-bs-toggle="modal" data-bs-target="#linkConsultationModal">
                            <i class="fa-solid fa-link"></i> Link consultation
                        </a>
                    `);
                }
                populateClientProfileTab(data);
                populateSubjectiveTab(data.subjective ?? []);
                populateObjectiveTab(
                    data.pepert ?? [],
                    data.pe_misc ?? [],
                    data.pe_specific ?? [],
                );
                populateAssessmentTab(data.icd ?? []);
                populatePlanManagementTab(
                    data.diagnostics ?? [],
                    data.management ?? [],
                    data.advice ?? [],
                    data.labResults ?? [],
                    data.diagnosisFromStocksLedger,
                );
                populateMedicineTab(data.phic_charges ?? []);
                populateLaboratoryTab(
                    data.labResults ?? {},
                    data.diagnosisFromStocksLedger,
                );
                syncLaboratoryPanelsVisibility();
                $("#soap_save_profile_btn").prop("disabled", false);
                resolve();
            },
            error() {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: "Failed to load SOAP details.",
                });
                $("#soap_save_profile_btn").prop("disabled", false);
                resolve();
            },
        });
    });
}

/**
 * Clears all is-invalid classes, .invalid-feedback elements, and tab error
 * badges from #soap-details-modal.
 */
export function clearSoapValidationUI() {
    if (!$soapModal.length) return;

    $modal(".is-invalid").removeClass("is-invalid");
    $modal("[data-soap-validation-key]").remove();
    $modal(".validation-error-badge").remove();
    $modal("#soap_tabs .tab-btn").removeClass("border border-danger");

    activeValidationErrors.clear();
}

/**
 * Parses a Laravel 422 errors object and applies inline field errors + tab badges.
 *
 * @param {Record<string, string[]>} errors
 * @returns {boolean} true if any known errors were applied, false otherwise
 */
export function applySoapValidationErrors(errors) {
    let anyApplied = false;

    for (const key of Object.keys(errors)) {
        const target = getSoapValidationTarget(key);
        if (!target) continue;

        const message = Array.isArray(errors[key])
            ? errors[key][0]
            : errors[key];
        const safeKey = key.replace(/\./g, "_");
        const tabSelector = getSoapTabButtonSelector(key);

        // Apply is-invalid class
        if (target.type === "radioGroup") {
            $modal(target.selector).addClass("is-invalid");
        } else {
            $modal(target.selector).first().addClass("is-invalid");
        }

        // Insert .invalid-feedback adjacent to the input (or its .input-group wrapper)
        const $input = $modal(target.selector).first();
        const $inputGroup = $input.closest(".input-group");
        const $insertAfter = $inputGroup.length ? $inputGroup : $input;

        $modal(`[data-soap-validation-key="${safeKey}"]`).remove();
        $insertAfter.after(
            `<div class="invalid-feedback d-block" data-soap-validation-key="${safeKey}">${message}</div>`,
        );

        // Tab button error badge
        if (tabSelector) {
            const $tabBtn = $modal(tabSelector).first();
            if ($tabBtn.length) {
                $tabBtn.addClass("border border-danger");
                if ($tabBtn.find(".validation-error-badge").length === 0) {
                    $tabBtn.append(
                        '<span class="badge bg-danger ms-2 validation-error-badge" aria-hidden="true">!</span>',
                    );
                }
            }
        }

        activeValidationErrors.set(key, { target, tabSelector, safeKey });
        anyApplied = true;
    }

    attachSoapRealtimeValidationListeners();

    return anyApplied;
}
