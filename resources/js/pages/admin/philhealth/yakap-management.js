import {
    populateFormFields,
    populateProfilesTable,
    populateSoapsTable,
    hideModal,
    getAgeFromBirthdate,
} from "./../../../helper.js";
import { PaginationComponent } from "./../../../components/pagination.js";
import {
    shouldIgnoreProfileValidationError,
    getProfileValidationTarget,
    activeValidationErrors,
    clearSingleFieldValidationError,
    updateTabErrorBadge,
    attachRealtimeValidationListeners,
} from "./profile-validation-codes.js";
import "./soap-form.js";
import { initLinkPatientModal } from "./patient-linking.js";
import { initLinkConsultationModal } from "./consultation-linking.js";

// ─── State ────────────────────────────────────────────────────────────────────
let paginationComponent;
let enlistmentData = null;
window.enlistmentData = null; // Expose globally for patient-linking module
let selectedEnlistmentCaseNo = null;
window.selectedEnlistmentCaseNo = null;
let selectedProfileTransNo = null;

// ─── Cached selectors ────────────────────────────────────────────────────────
// Static elements that exist for the lifetime of the page
const $profileModal = $("#profile-details-modal");
const $profileTabs = $("#profile_tabs");
const $yakapNextBtn = $("#yakap_next_btn");
const $yakapSaveBtn = $("#yakap_save_profile_btn");
const $addProfileRecordBtn = $("#add-profile-record-btn");
const $advancedSearchBtn = $("#advanced_search_btn");
const $advanceSearch = $("#advance-search");
const $enlistmentSearch = $("#enlistment_search");
const $enlistmentTable = $("#enlistment_table");
const $clientRecordAccordion = $("#client_record_accordion");
const $paginationContainer = $("#pagination_container");
const $dHeight = $("#dHeight");
const $dWeight = $("#dWeight");
const $dBMI = $("#dBMI");
const $obGyneWarning = $("#ob-gyne-warning-message");
const $tblMedHistOpHist = $("#tblMedHistOpHist tbody");

// Search fields
const $pinField = $("#pin");
const $lastNameField = $("#last_name");
const $firstNameField = $("#first_name");
const $middleNameField = $("#middle_name");
const $extensionField = $("#extension");
const $dobField = $("#dob");
const $nameField = $("#name");
const $effectiveYearField = $("#effective-year");

// Profile display elements
const $profileName = $(".profileName");
const $profileSex = $(".profileSex");
const $profileAge = $(".profileAge");
const $profilePIN = $(".profilePIN");
const $profileEffYr = $(".profileEffYr");
const $pxPin = $("#px_pin");

// ─── Profile validation UI ────────────────────────────────────────────────────

function clearProfileValidationUI() {
    if (!$profileModal.length) return;

    $profileModal.find("input, select, textarea").off(".realtimeValidation");
    activeValidationErrors.clear();

    $profileModal.find(".is-invalid").removeClass("is-invalid");
    $profileModal.find("[data-profile-validation-key]").remove();
    $profileModal.find(".validation-error-badge").remove();
    $profileModal
        .find(".tab-btn.border.border-danger")
        .removeClass("border border-danger");
    $profileModal
        .find('.tab-btn[data-has-validation-error="true"]')
        .removeAttr("data-has-validation-error");
}

function getProfileTabButtonSelectorByFieldKey(fieldKey) {
    if (!fieldKey || typeof fieldKey !== "string") return null;

    if (fieldKey.startsWith("clientProfile."))
        return '#profile_tabs .tab-btn[data-bs-target="#client_profile"]';

    if (
        fieldKey.startsWith("medicalHistory.") ||
        fieldKey.startsWith("surgicalHistory.")
    )
        return '#profile_tabs .tab-btn[data-bs-target="#medical_history"]';

    if (
        fieldKey.startsWith("familyHistory.") ||
        fieldKey.startsWith("personalSocialHistory.")
    )
        return '#profile_tabs .tab-btn[data-bs-target="#family_history"]';

    if (fieldKey.startsWith("immunizations."))
        return '#profile_tabs .tab-btn[data-bs-target="#immunizations"]';

    if (
        fieldKey.startsWith("mensHistory.") ||
        fieldKey.startsWith("pregHistory.")
    )
        return '#profile_tabs .tab-btn[data-bs-target="#ob_gyne_history"]';

    if (fieldKey.startsWith("pepert.") || fieldKey.startsWith("bloodType."))
        return '#profile_tabs .tab-btn[data-bs-target="#pertinent_physical"]';

    if (fieldKey.startsWith("ncdHighRisk"))
        return '#profile_tabs .tab-btn[data-bs-target="#ncd_high_risk"]';

    return null;
}

function markProfileTabWithError(tabButtonSelector) {
    if (!$profileModal.length) return;

    const $tabBtn = $profileModal.find(tabButtonSelector).first();
    if (!$tabBtn.length) return;

    $tabBtn.addClass("border border-danger");
    if ($tabBtn.find(".validation-error-badge").length === 0) {
        $tabBtn.append(
            '<span class="badge bg-danger ms-2 validation-error-badge" aria-hidden="true">!</span>',
        );
    }
    $tabBtn.attr("data-has-validation-error", "true");
}

function applyProfileValidationErrors(errors) {
    if (!$profileModal.length || !errors || typeof errors !== "object")
        return false;

    let appliedAny = false;

    Object.entries(errors).forEach(([fieldKey, messages]) => {
        const message = Array.isArray(messages) ? messages[0] : messages;
        if (!message) return;

        if (shouldIgnoreProfileValidationError(fieldKey, message)) return;

        const target = getProfileValidationTarget(fieldKey);
        if (!target) return;

        appliedAny = true;
        const tabButtonSelector =
            getProfileTabButtonSelectorByFieldKey(fieldKey);
        if (tabButtonSelector) markProfileTabWithError(tabButtonSelector);

        const safeKey = String(fieldKey).replace(/[^a-zA-Z0-9_-]/g, "_");
        const feedbackId = `profile-invalid-feedback-${safeKey}`;

        $profileModal
            .find(`[data-profile-validation-key="${safeKey}"]`)
            .remove();

        const $feedbackEl = $("<div/>", {
            class: "invalid-feedback d-block",
            id: feedbackId,
            "data-profile-validation-key": safeKey,
        }).text(message);

        if (target.type === "text") {
            const $input = $(target.selector, $profileModal).first();
            if (!$input.length) return;
            $input.addClass("is-invalid");
            const $insertAfter = $input.closest(".input-group").length
                ? $input.closest(".input-group")
                : $input;
            $insertAfter.after($feedbackEl);
        } else if (target.type === "radioGroup") {
            const $radios = $(target.selector, $profileModal);
            if (!$radios.length) return;
            $radios.addClass("is-invalid");
            const $container = $radios.first().closest(".mb-3");
            const $insertAfter = $container.length
                ? $container
                : $radios.first().closest(".form-check-inline");
            $insertAfter.after($feedbackEl);
        } else if (target.type === "checkbox") {
            const $checkbox = $(target.selector, $profileModal).first();
            if (!$checkbox.length) return;
            $checkbox.addClass("is-invalid");
            const $container = $checkbox.closest(".form-check");
            const $insertAfter = $container.length ? $container : $checkbox;
            $insertAfter.after($feedbackEl);
        } else if (target.type === "checkboxGroup") {
            const $inputs = $(target.selector, $profileModal);
            if ($inputs.length) $inputs.addClass("is-invalid");
            const $feedbackAfter = $(
                target.feedbackAfterSelector,
                $profileModal,
            ).first();
            if (!$feedbackAfter.length) return;
            $feedbackAfter.after($feedbackEl);
        }

        activeValidationErrors.set(fieldKey, {
            target,
            tabSelector: tabButtonSelector,
            safeKey,
        });
    });

    attachRealtimeValidationListeners();
    return appliedAny;
}

// ─── Helpers ──────────────────────────────────────────────────────────────────

function calculateBMI() {
    const height = parseFloat($dHeight.val());
    const weight = parseFloat($dWeight.val());

    if (height && weight && height > 0 && weight > 0) {
        const heightInMeters = height / 100;
        $dBMI.val((weight / (heightInMeters * heightInMeters)).toFixed(2));
    } else {
        $dBMI.val("");
    }
}

function resetConditionalInputsVisibility() {
    $('[id^="divfamDiseaseCode"]').hide();
    $('[id^="divsocDiseaseCode"]').hide();
    $('[id^="divdiseaseCode"]').hide();
}

function disableFormInputs(formId) {
    $(`#${formId} input, #${formId} select, #${formId} textarea`).prop(
        "disabled",
        true,
    );
    $(`#${formId} .form-check-input`).prop("disabled", true);
    $(`#${formId} .form-check-label`).addClass("text-muted");
}

function enableFormInputs(formId) {
    $(`#${formId} input, #${formId} select, #${formId} textarea`).prop(
        "disabled",
        false,
    );
    $(`#${formId} .form-check-input`).prop("disabled", false);
    $(`#${formId} .form-check-label`).removeClass("text-muted");
}

function enableOrDisableOBGyneAndNCDFields(age, sex) {
    if (sex === "M") {
        $obGyneWarning.removeClass("d-none");
        disableFormInputs("pregHistoryForm");
        disableFormInputs("mensHistoryForm");
    } else {
        $obGyneWarning.addClass("d-none");
        enableFormInputs("pregHistoryForm");
        enableFormInputs("mensHistoryForm");
    }

    if (age < 25) {
        disableFormInputs("ncdHighRiskForm");
    } else {
        enableFormInputs("ncdHighRiskForm");
    }
}

// ─── DOM-ready ────────────────────────────────────────────────────────────────

$(function () {
    paginationComponent = new PaginationComponent({
        container: "#pagination_container",
        onPageChange: (page) => enlistmentSearchAction(page),
    });

    $advancedSearchBtn.on("click", function () {
        $advanceSearch.toggleClass("d-none");
        $pinField.val().trim();
        $lastNameField.val("");
        $firstNameField.val("");
        $middleNameField.val("");
        $extensionField.val("");
        $dobField.val("");
    });

    $enlistmentSearch.on("click", function () {
        $clientRecordAccordion.addClass("d-none");
        enlistmentSearchAction();
    });

    $enlistmentTable.on("click", ".enlistment-pin-link", function (e) {
        e.preventDefault();
        $clientRecordAccordion.addClass("d-none");
        const caseNo = $(this).data("caseno");
        selectedEnlistmentCaseNo = caseNo;
        window.selectedEnlistmentCaseNo = caseNo;
        fetchEnlistmentDetails(caseNo);
    });

    // Tab navigation
    // $profileTabs.find(".tab-btn").on("click", function () {
    //     if ($(this).hasClass("last-tab")) {
    //         $yakapNextBtn.addClass("d-none");
    //         $yakapSaveBtn.removeClass("d-none");
    //     } else {
    //         $yakapNextBtn.removeClass("d-none");
    //         $yakapSaveBtn.addClass("d-none");
    //     }
    // });

    $yakapNextBtn.on("click", function () {
        const $activeTab = $profileTabs.find(".tab-btn.active");
        const $nextTab = $activeTab.parent().next().find(".tab-btn");
        if ($nextTab.length > 0) {
            $nextTab.trigger("click");
            // if ($nextTab.parent().is(":last-child")) {
            //     $yakapNextBtn.addClass("d-none");
            //     $yakapSaveBtn.removeClass("d-none");
            // }
        }
    });

    $dHeight.on("input change", calculateBMI);
    $dWeight.on("input change", calculateBMI);

    // ── View profile ──────────────────────────────────────────────────────────
    window.viewProfileDetails = function (transNo) {
        selectedProfileTransNo = transNo;
        $profileModal.find("form").each(function () {
            this.reset();
        });
        clearProfileValidationUI();
        resetConditionalInputsVisibility();
        $profileTabs.find(".tab-btn").first().trigger("click");
        new bootstrap.Modal(
            document.getElementById("profile-details-modal"),
        ).show();
        fetchProfileDetails(transNo);
    };

    // ── Add new profile ───────────────────────────────────────────────────────
    window.addNewProfileRecord = function () {
        selectedProfileTransNo = null;
        $profileTabs.find(".tab-btn").first().trigger("click");
        $profileModal.find("form").each(function () {
            this.reset();
        });
        clearProfileValidationUI();
        resetConditionalInputsVisibility();
        enableFormInputs("ncdHighRiskForm");
        $("#dProfDate").val(new Date().toISOString().split("T")[0]);
        new bootstrap.Modal(
            document.getElementById("profile-details-modal"),
        ).show();
    };

    $addProfileRecordBtn.on("click", function () {
        selectedProfileTransNo = null;
        $profileModal.find("form").each(function () {
            this.reset();
        });
        clearProfileValidationUI();
        resetConditionalInputsVisibility();
        $profileTabs.find(".tab-btn").first().trigger("click");
        new bootstrap.Modal(
            document.getElementById("profile-details-modal"),
        ).show();
    });

    // ── Save profile ──────────────────────────────────────────────────────────
    $yakapSaveBtn.on("click", function () {
        const $saveButton = $(this);
        const originalText = $saveButton.text();
        $saveButton.prop("disabled", true).text("Saving...");

        const forms = {
            clientProfile: document.getElementById("clientProfileForm"),
            familyHistory: document.getElementById("familyHistoryForm"),
            personalSocialHistory: document.getElementById(
                "personalSocialHistoryForm",
            ),
            immunizations: document.getElementById("immunizationsForm"),
            medicalHistory: document.getElementById("medicalHistoryForm"),
            surgicalHistory: document.getElementById("surgicalHistoryForm"),
            mensHistory: document.getElementById("mensHistoryForm"),
            pregHistory: document.getElementById("pregHistoryForm"),
            pepert: document.getElementById("pepertForm"),
            bloodType: document.getElementById("bloodTypeForm"),
            generalSurvey: document.getElementById("generalSurveyForm"),
            pertinentFindings: document.getElementById("pertinentFindingsForm"),
            ncdHighRisk: document.getElementById("ncdHighRiskForm"),
        };

        const combined = {};

        function setNested(obj, key, value) {
            const keys = key.split(/\[|\]/).filter((k) => k !== "");
            let current = obj;
            for (let i = 0; i < keys.length - 1; i++) {
                if (!current[keys[i]]) current[keys[i]] = {};
                current = current[keys[i]];
            }
            current[keys[keys.length - 1]] = value;
        }

        Object.entries(forms).forEach(([formKey, formElement]) => {
            const formData = new FormData(formElement);
            combined[formKey] = {};
            formData.forEach((value, key) =>
                setNested(combined[formKey], key, value),
            );
        });

        combined.enlistmentCaseNo = selectedEnlistmentCaseNo;
        combined.profileTransNo = selectedProfileTransNo;

        $.ajax({
            url: "/api/saveProfileData",
            type: "POST",
            data: JSON.stringify(combined),
            processData: false,
            contentType: "application/json",
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
            success() {
                Swal.fire({
                    position: "center",
                    icon: "success",
                    title: "Profile saved successfully!",
                    showConfirmButton: false,
                    timer: 1000,
                });
                $saveButton.prop("disabled", false).text(originalText);
                fetchEnlistmentDetails(selectedEnlistmentCaseNo);
            },
            error(xhr) {
                if (xhr.status === 422) {
                    clearProfileValidationUI();
                    const errors = xhr.responseJSON?.errors ?? {};
                    const applied = applyProfileValidationErrors(errors);
                    if (!applied) {
                        Swal.fire({
                            icon: "error",
                            title: "Oops...",
                            text: "Something went wrong!",
                        });
                    }
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Oops...",
                        text: "Something went wrong!",
                    });
                }
                $saveButton.prop("disabled", false).text(originalText);
            },
        });
    });
});

// ── PDF Generation ──────────────────────────────────────────────────────────
window.generateEkas = function (transNo) {
    if (!transNo) {
        alert("No consultation record selected");
        return;
    }
    window.open(`/api/pdf/ekas/${transNo}`, "_blank");
};

window.generateEpress = function (transNo) {
    if (!transNo) {
        alert("No consultation record selected");
        return;
    }
    window.open(`/api/pdf/epress/${transNo}`, "_blank");
};

// ─── Data fetching ────────────────────────────────────────────────────────────

function fetchProfileDetails(transNo) {
    $.ajax({
        url: `/api/profile/${transNo}`,
        type: "GET",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success(response) {
            populateModalFormFields(response);
        },
    });
}

window.fetchEnlistmentDetails = function (caseNo) {
    $.ajax({
        url: `/api/enlistment_details/${caseNo}`,
        type: "GET",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success(response) {
            enlistmentData = response;
            window.enlistmentData = response; // Update global reference

            // Toggle visibility of px_pin display vs link button
            const $pxPinSpan = $("#px_pin");
            const $linkPatientBtn = $("#link_patient_btn");

            if (response.px_pin) {
                // Has px_pin: show the PIN, hide the link button
                $pxPinSpan.text(response.px_pin).removeClass("d-none");
                $linkPatientBtn.addClass("d-none");
            } else {
                // No px_pin: hide the PIN display, show the link button
                $pxPinSpan.text("").addClass("d-none");
                $linkPatientBtn.removeClass("d-none");
            }

            populateFormFields(response);
            populateProfilesTable(response.profiles || []);
            populateSoapsTable(response.soaps || []);

            const age = getAgeFromBirthdate(response.dPatientDob);

            if (response.patientname) $profileName.text(response.patientname);
            if (response.dPatientSex)
                $profileSex.text(
                    response.dPatientSex === "M" ? "Male" : "Female",
                );
            if (response.dPatientDob) $profileAge.text(age + " years old");
            if (response.dPatientPin) $profilePIN.text(response.dPatientPin);
            if (response.dEffyear) $profileEffYr.text(response.dEffyear);

            enableOrDisableOBGyneAndNCDFields(age, response.dPatientSex);
            $clientRecordAccordion.removeClass("d-none");
        },
        error(xhr) {
            console.error("Failed to fetch details:", xhr.responseText);
        },
    });
};

function enlistmentSearchAction(page = 1) {
    const fields = {
        pin: $pinField.val().trim(),
        lastName: $lastNameField.val().trim().toUpperCase(),
        firstName: $firstNameField.val().trim().toUpperCase(),
        middleName: $middleNameField.val().trim().toUpperCase(),
        extension: $extensionField.val().trim(),
        date: $dobField.val().trim(),
        name: $nameField.val().trim().toUpperCase(),
        "effective-year": $effectiveYearField.val().trim(),
        page,
    };

    const hasValue = Object.values(fields).some((v) => v !== "" && v !== 1);
    if (!hasValue) {
        alert("Please fill in at least one search field.");
        return;
    }

    $.ajax({
        url: "/api/enlistment_search_action",
        type: "GET",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        data: fields,
        success(response) {
            const $tbody = $enlistmentTable.find("tbody");
            $tbody.empty();

            if (response.data.length === 0) {
                $tbody.append(
                    '<tr><td colspan="8" class="text-center">No records found.</td></tr>',
                );
                paginationComponent.render(response);
                return;
            }

            $.each(response.data, function (_, row) {
                $tbody.append(`
                    <tr>
                        <td><a href="#" class="enlistment-pin-link" data-caseno="${row.dCaseNo ?? ""}">${row.dCaseNo ?? ""}</a></td>
                        <td>${row.dPatientLname ?? ""}</td>
                        <td>${row.dPatientFname ?? ""}</td>
                        <td>${row.dPatientMname ?? ""}</td>
                        <td>${row.dPatientExtname ?? ""}</td>
                        <td>${row.dPatientType ?? ""}</td>
                        <td>${row.dPatientDob ?? ""}</td>
                        <td>${row.dEffyear ?? ""}</td>
                    </tr>
                `);
            });

            paginationComponent.render(response);
        },
        error(xhr) {
            console.error("Search failed:", xhr.responseText);
        },
    });
}

// ─── Modal form population ────────────────────────────────────────────────────

function populateModalFormFields(data) {
    populateClientDetails(data);
    populateMedicalAndSurgicalHistory(
        data.med_hist || [],
        data.mh_specific || [],
        data.surg_hist || [],
    );
    populateFamilyAndPersonalHistory(
        data.fam_hist || [],
        data.fh_specific || [],
        data.soc_hist || [],
    );
    populateImmunizations(data.immunizations || []);
    populateOBGyneHistory(data.preg_hist || [], data.mens_hist || []);
    populatePertinentPEFindings(
        data.pepert || [],
        data.blood_types || [],
        data.pe_gen_survey || [],
        data.pe_misc || [],
        data.pe_specific || [],
    );
    populateNCDHighRiskAssessment(data.ncd_qans || []);
}

function populateClientDetails(data) {
    if (!data) return;
    if (data.dIsWalkedIn)
        $(`input[name="dIsWalkedIn"][value="${data.dIsWalkedIn}"]`).prop(
            "checked",
            true,
        );
    if (data.dATC) $("#dATC").val(data.dATC);
    if (data.dProfDate) $("#dProfDate").val(data.dProfDate);
}

function populateNCDHighRiskAssessment(ncdQans) {
    if (!ncdQans || ncdQans.length === 0) return;

    const ncdData = ncdQans[0];

    Object.keys(ncdData).forEach(function (key) {
        const value = ncdData[key];
        if (!value || value === "0000-00-00") return;

        const $input = $(`input[name="${key}"]`);
        if (!$input.length) return;

        if ($input.attr("type") === "radio") {
            $(`input[name="${key}"][value="${value}"]`).prop("checked", true);
        } else {
            $input.val(value);
        }
    });
}

function populatePertinentPEFindings(
    pepert,
    bloodTypes,
    peGenSurvey,
    peMisc,
    peSpecific,
) {
    const pertFields = [
        "dSystolic",
        "dDiastolic",
        "dHr",
        "dRr",
        "dTemp",
        "dHeight",
        "dWeight",
        "dBMI",
        "dLeftVision",
        "dRightVision",
        "dLength",
        "dHeadCirc",
        "dSkinfoldThickness",
        "dMidUpperArmCirc",
        "dWaist",
        "dHip",
        "dLimbs",
    ];

    pepert.forEach(function (item) {
        pertFields.forEach(function (field) {
            if (item[field]) {
                const $el = $(`#${field}`);
                if ($el.length) $el.val(item[field]);
            }
        });
    });

    bloodTypes.forEach(function (item) {
        $(`[id="dBloodType${item.dBloodType}"]`).prop("checked", true);
    });

    peGenSurvey.forEach(function (item) {
        if (item.dGenSurveyId) {
            $(`#dGenSurveyId_${item.dGenSurveyId}`).prop("checked", true);
            if (item.dGenSurveyRem) $("#dGenSurveyRem").val(item.dGenSurveyRem);
        }
    });

    peMisc.forEach(function (item) {
        if (item.dHeentId) $(`#heent_${item.dHeentId}`).prop("checked", true);
        if (item.dChestId) $(`#chest_${item.dChestId}`).prop("checked", true);
        if (item.dHeartId) $(`#heart_${item.dHeartId}`).prop("checked", true);
        if (item.dAbdomenId)
            $(`#abdomen_${item.dAbdomenId}`).prop("checked", true);
        if (item.dGuId) $(`#gu_${item.dGuId}`).prop("checked", true);
        if (item.dRectalId)
            $(`#rectal_${item.dRectalId}`).prop("checked", true);
        if (item.dSkinId) $(`#skin_${item.dSkinId}`).prop("checked", true);
        if (item.dNeuroId) $(`#neuro_${item.dNeuroId}`).prop("checked", true);
    });

    peSpecific.forEach(function (item) {
        if (item.dHeentRem) $("#dHeentRem").val(item.dHeentRem);
        if (item.dChestRem) $("#dChestRem").val(item.dChestRem);
        if (item.dHeartRem) $("#dHeartRem").val(item.dHeartRem);
        if (item.dAbdomenRem) $("#dAbdomenRem").val(item.dAbdomenRem);
        if (item.dGuRem) $("#dGuRem").val(item.dGuRem);
        if (item.dRectalRem) $("#dRectalRem").val(item.dRectalRem);
        if (item.dSkinRem) $("#dSkinRem").val(item.dSkinRem);
        if (item.dNeuroRem) $("#dNeuroRem").val(item.dNeuroRem);
    });
}

function populateOBGyneHistory(pregHistory, mensHistory) {
    pregHistory.forEach(function (item) {
        if (item.dWFamPlan)
            $(`input[name="dWFamPlan"][value="${item.dWFamPlan}"]`).prop(
                "checked",
                true,
            );
        if (item.dPregCnt) $("#dPregCnt").val(item.dPregCnt);
        if (item.dDeliveryCnt) $("#dDeliveryCnt").val(item.dDeliveryCnt);
        if (item.dDeliveryTyp) $("#dDeliveryTyp").val(item.dDeliveryTyp);
        if (item.dWPregIndhyp === "Y") $("#dWPregIndhyp").prop("checked", true);
        if (item.dFullTermCnt) $("#dFullTermCnt").val(item.dFullTermCnt);
        if (item.dPrematureCnt) $("#dPrematureCnt").val(item.dPrematureCnt);
        if (item.dAbortionCnt) $("#dAbortionCnt").val(item.dAbortionCnt);
        if (item.dLivChildrenCnt)
            $("#dLivChildrenCnt").val(item.dLivChildrenCnt);
    });

    mensHistory.forEach(function (item) {
        if (item.dMenarchePeriod)
            $("#dMenarchePeriod").val(item.dMenarchePeriod);
        if (item.dOnsetSexIc) $("#dOnsetSexIc").val(item.dOnsetSexIc);
        if (item.dMenopauseAge) $("#dMenopauseAge").val(item.dMenopauseAge);
        if (item.dIsMenopause)
            $(`input[name="dIsMenopause"][value="${item.dIsMenopause}"]`).prop(
                "checked",
                true,
            );
        if (item.dLastMensPeriod)
            $("#dLastMensPeriod").val(item.dLastMensPeriod);
        if (item.dBirthCtrlMethod)
            $("#dBirthCtrlMethod").val(item.dBirthCtrlMethod);
        if (item.dPadsPerDay) $("#dPadsPerDay").val(item.dPadsPerDay);
        if (item.dPeriodDuration)
            $("#dPeriodDuration").val(item.dPeriodDuration);
        if (item.dMensInterval) $("#dMensInterval").val(item.dMensInterval);
    });
}

function populateImmunizations(immunizations) {
    immunizations.forEach(function (item) {
        $(`#immchild${item.dChildImmcode}`).prop(
            "checked",
            item.dChildImmcode !== "",
        );
        $(`#immelderly${item.dElderlyImmcode}`).prop(
            "checked",
            item.dElderlyImmcode !== "",
        );
        $(`#immpregw${item.dPregwImmcode}`).prop(
            "checked",
            item.dPregwImmcode !== "",
        );
        $(`#immyoungw${item.dYoungwImmcode}`).prop(
            "checked",
            item.dYoungwImmcode !== "",
        );
        $("#dOtherImm").val(item.dOtherImm !== "" ? item.dOtherImm : "");
    });
}

function populateFamilyAndPersonalHistory(
    familyHistory,
    fhSpecific,
    socialHistory,
) {
    familyHistory.forEach(function (item) {
        $(`#famDiseaseCode${item.dMdiseaseCode}`).prop("checked", true);
        $(`#divfamDiseaseCode${item.dMdiseaseCode}`).show();
    });

    fhSpecific.forEach(function (item) {
        $(`#specificFamCode${item.dMdiseaseCode}`).val(item.dSpecificDesc);
    });

    socialHistory.forEach(function (item) {
        $("#dNoBottles").val(item.dNoBottles !== 0 ? item.dNoBottles : "");
        $("#dNoCigpk").val(item.dNoCigpk !== 0 ? item.dNoCigpk : "");

        const radioMap = {
            dIllDrugUser: ["Y", "N"],
            dIsADrinker: ["Y", "N", "X"],
            dIsSexuallyActive: ["Y", "N"],
            dIsSmoker: ["Y", "N", "X"],
        };

        Object.entries(radioMap).forEach(([field, values]) => {
            if (values.includes(item[field])) {
                $(`#${field}${item[field]}`).prop("checked", true);
            }
        });

        $(`#socDiseaseCode${item.dMdiseaseCode}`).prop("checked", true);
        $(`#divsocDiseaseCode${item.dMdiseaseCode}`).show();
    });
}

function populateMedicalAndSurgicalHistory(
    medicalHistory,
    mhSpecific,
    surgicalHistory,
) {
    medicalHistory.forEach(function (item) {
        $(`#diseaseCode${item.dMdiseaseCode}`).prop("checked", true);
        $(`#divdiseaseCode${item.dMdiseaseCode}`).show();
    });

    mhSpecific.forEach(function (item) {
        $(`#specificCode${item.dMdiseaseCode}`).val(item.dSpecificDesc);
    });

    $tblMedHistOpHist.empty();

    surgicalHistory.forEach(function (item) {
        $tblMedHistOpHist.append(
            createSurgicalHistoryRow(
                item.dSurgDesc || "",
                item.dSurgDate || "",
                false,
            ),
        );
    });

    $tblMedHistOpHist.append(createSurgicalHistoryRow("", "", true));
}

// ─── Surgical history rows ────────────────────────────────────────────────────

var surgicalHistoryIndex = 0;

function createSurgicalHistoryRow(operation, date, isEmptyRow) {
    const escapeHtml = (value) => {
        if (value === null || value === undefined) return "";
        return String(value)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    };

    const escapedOperation = escapeHtml(operation);
    const escapedDate = escapeHtml(date);

    const actionButton = isEmptyRow
        ? '<button type="button" class="btn btn-success btn-sm" onclick="addOperationHist(this)">Add</button>'
        : '<button type="button" class="btn btn-danger btn-sm" onclick="removeOperationHist(this)">Remove</button>';

    const operationInputName = isEmptyRow
        ? ""
        : ` name="surgicalHistory[${surgicalHistoryIndex}][operation]"`;
    const dateInputName = isEmptyRow
        ? ""
        : ` name="surgicalHistory[${surgicalHistoryIndex}][date]"`;

    if (!isEmptyRow) surgicalHistoryIndex++;

    return `<tr data-empty="${isEmptyRow ? "1" : "0"}">
        <td><input type="text" class="form-control"${operationInputName} value="${escapedOperation}" maxlength="2000"></td>
        <td><input type="date" class="form-control"${dateInputName} value="${escapedDate}"></td>
        <td>${actionButton}</td>
    </tr>`;
}

window.removeOperationHist = function (button) {
    const $row = $(button).closest("tr");
    $row.remove();
    if ($tblMedHistOpHist.find('tr[data-empty="1"]').length === 0) {
        $tblMedHistOpHist.append(createSurgicalHistoryRow("", "", true));
    }
};

window.addOperationHist = function (button) {
    const $row = $(button).closest("tr");
    const $opInput = $row.find('input[type="text"]');
    const $dateInput = $row.find('input[type="date"]');
    const operation = $opInput.val().trim();
    const date = $dateInput.val().trim();

    if (!operation && !date) return;

    $row.before($(createSurgicalHistoryRow(operation, date, false)));
    $opInput.val("");
    $dateInput.val("");
};

// ─── Patient details form ─────────────────────────────────────────────────────

function initPatientDetailsForm() {
    const form = document.getElementById("patientDetailsForm");
    if (!form) return;

    // Cache form field selectors (scoped to the form element)
    const $form = $(form);
    const $submitBtn = $form.find('button[type="submit"]');
    const $dCaseNo = $("#dCaseNo");
    const $dWithConsent = $("#dWithConsent");

    form.addEventListener("submit", function (e) {
        e.preventDefault();

        const csrfToken = $('input[name="_token"]').val();
        const caseNo = $dCaseNo.val();

        if (!caseNo) {
            alert(
                "Error: Case number not set. Please select a patient record first.",
            );
            return;
        }

        const formData = {
            dCaseNo: caseNo,
            dEnlistDate: $("#dEnlistDate").val(),
            dPackageType: $("#dPackageType").val(),
            dWithConsent: $dWithConsent.is(":checked") ? "Y" : "N",
            dPatientType: $("#dPatientType").val(),
            px_pin: $("#px_pin").val(),
            dPatientLname: $("#dPatientLname").val().toUpperCase(),
            dPatientFname: $("#dPatientFname").val().toUpperCase(),
            dPatientMname: $("#dPatientMname").val().toUpperCase(),
            dPatientExtname: $("#dPatientExtname").val().toUpperCase(),
            dPatientDob: $("#dPatientDob").val(),
            dPatientSex: $("#dPatientSex").val(),
            dPatientMobileNo: $("#dPatientMobileNo").val(),
            dPatientLandlineNo: $("#dPatientLandlineNo").val(),
            dMemPin: $("#dMemPin").val(),
            dMemLname: $("#dMemLname").val().toUpperCase(),
            dMemFname: $("#dMemFname").val().toUpperCase(),
            dMemMname: $("#dMemMname").val().toUpperCase(),
            dMemExtname: $("#dMemExtname").val().toUpperCase(),
            dMemDob: $("#dMemDob").val(),
            dMemberSex: $("#dMemberSex").val(),
        };

        const originalText = $submitBtn.text();
        $submitBtn.prop("disabled", true).text("Saving...");

        fetch("/api/save_patient_details", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": csrfToken,
                Accept: "application/json",
            },
            body: JSON.stringify(formData),
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    alert("Patient details updated successfully!");
                } else if (data.errors) {
                    alert(
                        "Validation Error:\n" +
                            Object.values(data.errors).flat().join("\n"),
                    );
                } else {
                    alert(
                        "Error: " +
                            (data.message ||
                                "Failed to update patient details"),
                    );
                }
            })
            .catch((error) => {
                console.error("Error:", error);
                alert(
                    "An error occurred while saving patient details. Please try again.",
                );
            })
            .finally(() => {
                $submitBtn.prop("disabled", false).text(originalText);
            });
    });
}

if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", () => {
        initPatientDetailsForm();
        initLinkPatientModal();
        initLinkConsultationModal();
    });
} else {
    initPatientDetailsForm();
    initLinkPatientModal();
    initLinkConsultationModal();
}
