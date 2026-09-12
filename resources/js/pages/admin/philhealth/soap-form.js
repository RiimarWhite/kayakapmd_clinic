import { showModal, hideModal } from "../../../helper.js";
import {
    fetchAndPopulateSoapForm,
    clearSoapValidationUI,
    applySoapValidationErrors,
    syncLaboratoryPanelsVisibility,
} from "./soap-edit.js";

export let medsList = [];
export let consultCode = null;

export function setConsultCode(value) {
    consultCode = value ?? null;
}

// ─── Cached selectors ────────────────────────────────────────────────────────
// Static elements that exist for the lifetime of the page
const $soapModal = $("#soap-details-modal");

function getNotRequestedDiagnosticIds() {
    const excluded = new Set();
    $soapModal.find(".lab-exam-panel.not-requested").each(function () {
        const id = $(this).data("philhealth-diagnostic-id");
        if (id !== undefined && id !== null) excluded.add(String(id));
    });
    return excluded;
}

const $medsTableBody = $("#tblResultsMeds tbody");
const $diagnosisTableBody = $("#diagnosisTable tbody");
const $soapTabs = $("#soap_tabs");
const $soapNextBtn = $("#soap_next_btn");
const $soapSaveBtn = $("#soap_save_profile_btn");
const $dDiagnosis = $("#dDiagnosis");
const $diagnosisError = $("#diagnosisError");
const $dMedicine = $("#dMedicine");
const $dHeightSoap = $("#dHeightSoap");
const $dWeightSoap = $("#dWeightSoap");
const $dBMISoap = $("#dBMISoap");
const $dOtherMedicine = $("#dOtherMedicine");
const $dInstructionPhysician = $("#dInstructionPhysician");
const $dDispensingPersonnel = $("#dDispensingPersonnel");
const $dDateDispensed = $("#dDateDispensed");
const $genDesc = $("#gen_desc");
const $saltDesc = $("#salt_desc");
const $strengthDesc = $("#strength_desc");
const $formDesc = $("#form_desc");
const $unitDesc = $("#unit_desc");
const $packageDesc = $("#package_desc");
const $actualUnitPrice = $("#dActualUnitPrice");
const $btnAddMeds = $("#btnAddMeds");
const $importLedgerBtn = $("#import-medicine-from-ledger-btn");
const $addNewConsultation = $("#addNewConsultation");
const $addDiagnosisBtn = $("#addDiagnosisBtn");
const $managementX = $("#management_X");
const $managementOthRemarks = $("#management_oth_remarks1");
const $diagnosticOthRemarks = $("#diagnostic_oth_remarks1");
const $philhealthRadio = $("#philhealth");
const $consultCode = $("#consultCode");

// ─── Medicine list helpers ────────────────────────────────────────────────────

export function resetMedsList() {
    medsList.length = 0;
    $medsTableBody.empty();
}

export function appendMedToList(medData, rowClass = "") {
    medsList.push(medData);
    $medsTableBody.append(`
        <tr class="${rowClass}">
            <td>${medData.drugDesc ?? ""}</td>
            <td>${medData.otherMedicineGrouping ?? ""}</td>
            <td>${medData.quantity ?? ""}</td>
            <td>${medData.actualPrice ?? ""}</td>
            <td>${medData.totalPrice ?? ""}</td>
            <td>${medData.instructionQuantity ?? ""}</td>
            <td>${medData.instructionStrength ?? ""}</td>
            <td>${medData.instructionFrequency ?? ""}</td>
            <td>${medData.isDispensed === "Y" ? "Yes" : "No"}</td>
            <td>${medData.dispenseDate ?? ""}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-danger" onclick="removeMedFromList(this)">
                    <i class="fa-solid fa-trash"></i>
                </button>
            </td>
        </tr>
    `);
}

// ─── Helpers ─────────────────────────────────────────────────────────────────

function resetSoapModal() {
    $soapModal.find("form").each(function () {
        this.reset();
    });
    $diagnosisTableBody.empty();
    $dMedicine.val(null).trigger("change");
    resetMedsList();
    clearSoapValidationUI();
    $soapTabs.find(".tab-btn").first().trigger("click");
}

function renumberDiagnosisRows() {
    $diagnosisTableBody.find("tr").each(function (index) {
        $(this)
            .find("td:first")
            .text(index + 1);
    });
}

function calculateBMI() {
    const height = parseFloat($dHeightSoap.val());
    const weight = parseFloat($dWeightSoap.val());

    if (height && weight && height > 0 && weight > 0) {
        const heightInMeters = height / 100;
        const bmi = (weight / (heightInMeters * heightInMeters)).toFixed(2);
        $dBMISoap.val(bmi);
    } else {
        $dBMISoap.val("");
    }
}

function initMedicineSelect2(url) {
    if ($dMedicine.hasClass("select2-hidden-accessible")) {
        $dMedicine.select2("destroy");
    }
    $dMedicine.select2({
        ajax: {
            url,
            dataType: "json",
            delay: 300,
            data: (params) => ({ q: params.term || "" }),
            processResults: (data) => ({ results: data.results }),
            cache: true,
        },
        minimumInputLength: 0,
        dropdownParent: $soapModal,
        width: "100%",
        placeholder: "Select a medicine",
        allowClear: true,
    });
}

function getMedicineDetails(medicineId, source) {
    $.ajax({
        url: "/api/medicine/details",
        method: "GET",
        data: { medicine_code: medicineId, source: source },
        success(response) {
            if (response) {
                $genDesc.val(response.GEN_DESC);
                $saltDesc.val(response.SALT_DESC);
                $strengthDesc.val(response.STRENGTH_DESC);
                $formDesc.val(response.FORM_DESC);
                $unitDesc.val(response.UNIT_DESC);
                $packageDesc.val(response.PACKAGE_DESC);
                response.price
                    ? $actualUnitPrice.val(response.price)
                    : $actualUnitPrice.val("");
                $("#dQuantity").val("");
            }
        },
        error() {
            alert("An error occurred while fetching medicine details.");
        },
    });
}

function addMedsToList() {
    const selectedOption = $dMedicine.find("option:selected");
    const medsDesc = selectedOption.text();
    const drugCode = selectedOption.val();
    const otherMedicine = $dOtherMedicine.val();
    const drugOtherMedsGrouping = $('input[name="dOthMedDrugGrouping"]').text();
    const quantity = $('input[name="dQuantity"]').val();
    const actualPrice = $('input[name="dActualUnitPrice"]').val();
    const totalPrice = quantity * actualPrice;
    const instructionQuantity = $('input[name="dInstructionQuantity"]').val();
    const instructionStrength = $('input[name="dInstructionStrength"]').val();
    const instructionFrequency = $('input[name="dInstructionFrequency"]').val();
    const isDispensed = $('input[name="dIsDispensed"]:checked').val();
    const dispenseDate = $('input[name="dDateDispensed"]').val();
    const physician = $dInstructionPhysician.val();
    const dispensingPersonnel = $dDispensingPersonnel.val();
    const source = $('input[name="medSource"]:checked').val();

    appendMedToList({
        drugDesc: medsDesc,
        drugCode,
        prodCode: "",
        genCode: "",
        saltCode: "",
        strengthCode: "",
        formCode: "",
        unitCode: "",
        packageCode: "",
        genericName: medsDesc,
        otherMedicine,
        otherMedicineGrouping: drugOtherMedsGrouping,
        quantity,
        actualPrice,
        totalPrice,
        instructionQuantity,
        instructionStrength,
        instructionFrequency,
        physician,
        isDispensed,
        dispenseDate,
        dispensingPersonnel,
    });
}

function getImportConsultCode() {
    return consultCode || $('input[name="consultCode"]').val();
}

function populateMedicineTabFromLedger(importedMeds) {
    // Seed with every non-empty identifier already in medsList
    const existingKeys = new Set();
    medsList.forEach((m) => {
        if (m.drugCode) existingKeys.add(m.drugCode);
        if (m.prodCode) existingKeys.add(m.prodCode);
    });

    importedMeds.forEach((med) => {
        const drugCode = med.drug_code;
        const prodCode = med.prodcode;
        const phicCode = med.phic_reference_code;

        // Check across all available identifiers from the API response
        if (
            (drugCode && existingKeys.has(drugCode)) ||
            (prodCode && existingKeys.has(prodCode)) ||
            (phicCode && existingKeys.has(phicCode))
        )
            return;

        // Register new identifiers immediately so within-batch duplicates are caught
        if (drugCode) existingKeys.add(drugCode);
        if (prodCode) existingKeys.add(prodCode);
        if (phicCode) existingKeys.add(phicCode);

        appendMedToList(
            {
                drugDesc:
                    med.drug_name || med.item_dscr || med.generic_name || "",
                drugCode: med.drug_code,
                prodCode: med.prodcode ?? "",
                genCode: med.gen_code ?? "",
                saltCode: med.salt_code ?? "",
                strengthCode: med.strength_code ?? "",
                formCode: med.form_code ?? "",
                unitCode: med.unit_code ?? "",
                packageCode: med.package_code ?? "",
                genericName: med.generic_name ?? med.drug_name ?? "",
                otherMedicine: "",
                otherMedicineGrouping: med.dOthMedDrugGrouping || "",
                quantity: med.qty ?? "",
                actualPrice: med.retails || med.cost_ave || "",
                totalPrice:
                    med.total_price ||
                    ((med.qty ?? 0) && (med.retails || med.cost_ave || 0)
                        ? (med.qty ?? 0) * (med.retails || med.cost_ave || 0)
                        : ""),
                instructionQuantity: med.prescribed_quantity || "",
                instructionStrength: med.ins_strength || "",
                instructionFrequency: med.ins_frequency || "",
                physician: med.doc_name || "",
                isDispensed: med.dispensed_status === "Y" ? "Y" : "N",
                dispenseDate: med.dispensed_date || "",
                dispensingPersonnel: med.dispensedby || "",
            },
            "table-warning",
        );
    });
}

function validateMedsForm() {
    const errors = [];

    if ($dInstructionPhysician.val() === "")
        errors.push("dInstructionPhysician");

    if (!$('input[name="dIsDispensed"]:checked').length) {
        errors.push("dIsDispensed");
    } else if ($('input[name="dIsDispensed"]:checked').val() === "Y") {
        if ($dDispensingPersonnel.val() === "")
            errors.push("dDispensingPersonnel");
        if ($dDateDispensed.val() === "") errors.push("dDateDispensed");
    }

    if ($dMedicine.val() === "") errors.push("dMedicine");
    if ($('input[name="dQuantity"]').val() === "") errors.push("dQuantity");
    if ($('input[name="dActualUnitPrice"]').val() === "")
        errors.push("dActualUnitPrice");
    if ($('input[name="dInstructionQuantity"]').val() === "")
        errors.push("dInstructionQuantity");
    if ($('input[name="dInstructionStrength"]').val() === "")
        errors.push("dInstructionStrength");
    if ($('input[name="dInstructionFrequency"]').val() === "")
        errors.push("dInstructionFrequency");
    if ($('input[name="advice_remarks"]').val() === "")
        errors.push("advice_remarks");

    return errors;
}

// ─── DOM-ready ────────────────────────────────────────────────────────────────

$(function () {
    $medsTableBody.empty();

    $soapModal.on(
        "change",
        'input[name^="diagnostic_patient"]',
        syncLaboratoryPanelsVisibility,
    );

    $('[name^="diagnostic_"][name$="_lab_exam"]').on("change", function () {
        // Extract N from the radio button name
        const n = $(this)
            .attr("name")
            .match(/diagnostic_(\d+)_lab_exam/)[1];
        const $textInput = $(`[name="diagnostic_${n}_accre_diag_fac"]`);

        // Disable/enable based on selected value
        // Adjust the condition to match your actual radio values
        if ($(this).val() === "1") {
            $textInput.val("").prop("disabled", true);
        } else {
            $textInput.prop("disabled", false);
        }
    });

    // ── New consultation ──────────────────────────────────────────────────────
    $addNewConsultation.on("click", async function () {
        const firstConsultationTransNo = await checkIfSecondConsultation(
            selectedEnlistmentCaseNo,
        );
        if (firstConsultationTransNo != null) {
            $("#soap_save_profile_btn").prop("disabled", true);
            window.currentSoapTransNo = null;
            window.currentConsultationRefNo = null;
            resetSoapModal();
            showModal("soap-details-modal");
            fetchAndPopulateSoapForm(firstConsultationTransNo);
        } else {
            window.currentSoapTransNo = null;
            window.currentConsultationRefNo = null;
            consultCode = null;
            $('input[name="consultCode"]').val("");
            $consultCode.html(`
                    <a href="#" id="link_consultation_btn" class="" data-bs-toggle="modal" data-bs-target="#linkConsultationModal">
                        <i class="fa-solid fa-link"></i> Link consultation
                    </a>
                `);
            resetSoapModal();
            $("#dSoapDate").val(new Date().toISOString().split("T")[0]); // FORMAT yyyy-MM-dd
            showModal("soap-details-modal");
            syncLaboratoryPanelsVisibility();
        }
    });

    // ── View existing SOAP ────────────────────────────────────────────────────
    window.viewSoapDetails = function (transNo) {
        $("#soap_save_profile_btn").prop("disabled", true);
        window.currentSoapTransNo = transNo;
        window.currentConsultationRefNo = null;
        resetSoapModal();
        showModal("soap-details-modal");
        fetchAndPopulateSoapForm(transNo);
    };

    // ── Diagnosis select2 ─────────────────────────────────────────────────────
    $dDiagnosis.select2({
        placeholder: "Select a diagnosis",
        allowClear: true,
        width: "100%",
        dropdownParent: $soapModal,
        ajax: {
            url: "/api/icd-diagnosis/search",
            dataType: "json",
            delay: 300,
            data: (params) => ({ q: params.term || "" }),
            processResults: (data) => ({ results: data.results }),
            cache: true,
        },
        minimumInputLength: 0,
    });

    $dDiagnosis.on("change", function () {
        $diagnosisError.hide().text("");
    });

    // ── Medicine source toggle ────────────────────────────────────────────────
    $('input[name="medSource"]').on("change", function () {
        const value = $(this).val();
        if (value === "inhouse") {
            initMedicineSelect2("/api/medicine/search/inhouse");
        } else if (value === "philhealth") {
            initMedicineSelect2("/api/medicine/search/philhealth");
        }
        $dMedicine.val(null).trigger("change");
        $genDesc.val("");
        $saltDesc.val("");
        $strengthDesc.val("");
        $formDesc.val("");
        $unitDesc.val("");
        $packageDesc.val("");
        $actualUnitPrice.val("");
        $("#dQuantity").val("");
    });

    if ($philhealthRadio.is(":checked")) {
        initMedicineSelect2("/api/medicine/search/philhealth");
    } else {
        initMedicineSelect2("/api/medicine/search/inhouse");
    }

    // ── Tab navigation ────────────────────────────────────────────────────────
    // $soapTabs.find(".tab-btn").on("click", function () {
    //     if ($(this).hasClass("last-tab")) {
    //         $soapNextBtn.addClass("d-none");
    //         $soapSaveBtn.removeClass("d-none");
    //     } else {
    //         $soapNextBtn.removeClass("d-none");
    //         $soapSaveBtn.addClass("d-none");
    //     }
    // });

    $soapNextBtn.on("click", function () {
        const $activeTab = $soapTabs.find(".tab-btn.active");
        const $nextTab = $activeTab.parent().next().find(".tab-btn");
        if ($nextTab.length > 0) {
            $nextTab.trigger("click");
            // if ($nextTab.parent().is(":last-child")) {
            //     $soapNextBtn.addClass("d-none");
            //     $soapSaveBtn.removeClass("d-none");
            // }
        }
    });

    // ── Add diagnosis ─────────────────────────────────────────────────────────
    $addDiagnosisBtn.on("click", function () {
        const $selectedOption = $dDiagnosis.find("option:selected");
        const selectedValue = $selectedOption.val();
        const selectedText = $selectedOption.text();

        $diagnosisError.hide().text("");

        if (!selectedValue) {
            $diagnosisError.text("Please select a diagnosis first.").show();
            return;
        }

        if (
            $diagnosisTableBody.find(`td[data-refcode="${selectedValue}"]`)
                .length > 0
        ) {
            $diagnosisError
                .text("This diagnosis has already been added.")
                .show();
            return;
        }

        $diagnosisTableBody.append(`
            <tr>
                <td></td>
                <td data-refcode="${selectedValue}">${selectedText}<input type="hidden" name="diagnoses[${selectedValue}][]" value="${selectedValue}"></td>
                <td><button type="button" class="btn btn-danger btn-sm" onClick="removeDiagnosis(this)">Remove</button></td>
            </tr>
        `);

        renumberDiagnosisRows();
        $dDiagnosis.val(null).trigger("change");
    });

    window.removeDiagnosis = function (button) {
        $(button).closest("tr").remove();
        renumberDiagnosisRows();
    };

    window.removeMedFromList = function (button) {
        const $row = $(button).closest("tr");
        medsList.splice($row.index(), 1);
        $row.remove();
    };

    // ── Diagnostic "others" remarks toggle ────────────────────────────────────
    // Dynamic name attribute — delegate from modal
    $soapModal.on(
        "change",
        'input[name="diagnostic_doctor_reco[99]"]',
        function () {
            $diagnosticOthRemarks.prop("disabled", $(this).val() !== "Y");
        },
    );

    // ── Management "others" remarks toggle ────────────────────────────────────
    $managementX.on("change", function () {
        $managementOthRemarks.prop("disabled", !$(this).is(":checked"));
    });
});

// ─── Outside DOM-ready (delegated / global) ───────────────────────────────────

async function checkIfSecondConsultation(selectedEnlistmentCaseNo) {
    try {
        const response = await fetch(
            `/api/soap/check-first-consultation/${selectedEnlistmentCaseNo}`,
        );
        if (!response.ok) throw new Error();
        const data = await response.json();
        return data.soapTransNo;
    } catch {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Internal server error",
        });
        return null;
    }
}

// Dispensed toggle — delegated so it works even if radios are re-rendered
$(document).on("change", 'input[name="dIsDispensed"]', function () {
    const isY = $(this).val() === "Y";
    $dDispensingPersonnel.prop("disabled", !isY);
    $dDateDispensed.prop("disabled", !isY);
    if (!isY) {
        $dDispensingPersonnel.val("");
        $dDateDispensed.val("");
    }
});

$dMedicine.on("change", function () {
    const $selectedOption = $(this).find("option:selected");
    const medicineCode = $selectedOption.val();
    const source = $('input[name="medSource"]:checked').val();
    if (medicineCode !== "") {
        getMedicineDetails(medicineCode, source);
    }
});

$btnAddMeds.on("click", function () {
    if (validateMedsForm().length !== 0) {
        alert("Fill all required fields");
        return;
    }
    addMedsToList();
});

$importLedgerBtn.on("click", async function () {
    const consultationRefNo = getImportConsultCode();
    if (!consultationRefNo) {
        alert("Please link a consultation first before importing medicines.");
        return;
    }

    const originalHtml = $importLedgerBtn.html();
    $importLedgerBtn
        .prop("disabled", true)
        .html(
            "<span class='fa fa-solid fa-spinner fa-spin'></span> Importing...",
        );

    try {
        const response = await fetch(
            `/api/medicine/import-from-ledger?consultationrefno=${encodeURIComponent(consultationRefNo)}`,
        );

        if (!response.ok) {
            throw new Error("Failed to import medicines from ledger.");
        }

        const data = await response.json();

        if (!Array.isArray(data.medicines)) {
            throw new Error("Unexpected response from import endpoint.");
        }

        populateMedicineTabFromLedger(data.medicines);

        // Scroll modal to bottom to show imported medicines
        const $modalBody = $soapModal.find(".modal-body");
        $modalBody.animate({ scrollTop: $modalBody[0].scrollHeight }, 300);
    } catch (error) {
        console.error("❌ Import error:", error);
        alert("Unable to import medicines from ledger. Please try again.");
    } finally {
        $importLedgerBtn.prop("disabled", false).html(originalHtml);
    }
});

$dHeightSoap.on("input change", calculateBMI);
$dWeightSoap.on("input change", calculateBMI);

// ─── Save SOAP ────────────────────────────────────────────────────────────────

$soapSaveBtn.on("click", function () {
    const $saveButton = $(this);
    const originalText = $saveButton.text();
    $saveButton.prop("disabled", true).text("Saving...");

    const forms = {
        clientProfile: document.getElementById("soapClientProfileForm"),
        assessmentDiagnosis: document.getElementById("assessmentDiagnosisForm"),
        subjectiveHistory: document.getElementById("subjectiveHistoryForm"),
        objectivePhysicalExamination: document.getElementById(
            "objectivePhysicalExaminationForm",
        ),
        planManagement: document.getElementById("planManagementForm"),
        laboratoryResults: document.getElementById("laboratoryResults"),
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

    const notRequestedIds = getNotRequestedDiagnosticIds();

    Object.entries(forms).forEach(([formKey, formElement]) => {
        if (!formElement) {
            combined[formKey] = {};

            return;
        }
        const formData = new FormData(formElement);
        combined[formKey] = {};
        formData.forEach((value, key) => {
            if (formKey === "laboratoryResults") {
                const match = key.match(/^diagnostic_(\d+)_/);
                if (match && notRequestedIds.has(match[1])) return;
            }
            setNested(combined[formKey], key, value);
        });
    });

    combined.enlistmentCaseNo = window.selectedEnlistmentCaseNo;
    combined.soapTransNo = window.currentSoapTransNo ?? null;
    combined.medsList = medsList;

    $.ajax({
        url: "/api/soap/save",
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
                title: "Consultation saved successfully!",
                showConfirmButton: false,
                timer: 1000,
            });
            // hideModal("soap-details-modal");
            $saveButton.prop("disabled", false).text(originalText);
            fetchAndPopulateSoapForm(window.currentSoapTransNo);
            fetchEnlistmentDetails(window.selectedEnlistmentCaseNo);
        },
        error(xhr) {
            if (xhr.status === 422) {
                clearSoapValidationUI();
                const errors = xhr.responseJSON?.errors ?? {};
                const applied = applySoapValidationErrors(errors);
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

// ─── Save Individual Laboratory Result ────────────────────────────────────────

$(document).on("click", ".laboratoryResultsSaveBtn", function () {
    if (!window.currentSoapTransNo) {
        Swal.fire({
            icon: "info",
            title: "Save Consultation First",
            text: 'Please click "Save Consultation" to create the consultation record before saving individual laboratory results.',
        });
        return;
    }

    const $btn = $(this);
    const diagnosticId = String($btn.data("id"));
    const originalText = $btn.text();
    $btn.prop("disabled", true).text("Saving...");

    const formElement = document.getElementById("laboratoryResults");
    const formData = new FormData(formElement);
    const laboratoryResults = {};
    const notRequestedIds = getNotRequestedDiagnosticIds();
    formData.forEach((value, key) => {
        const match = key.match(/^diagnostic_(\d+)_/);
        if (match && notRequestedIds.has(match[1])) return;
        laboratoryResults[key] = value;
    });

    $.ajax({
        url: "/api/soap/save-lab-result",
        type: "POST",
        data: JSON.stringify({
            soapTransNo: window.currentSoapTransNo,
            enlistmentCaseNo: window.selectedEnlistmentCaseNo,
            diagnosticId,
            laboratoryResults,
        }),
        processData: false,
        contentType: "application/json",
        headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
        success() {
            Swal.fire({
                position: "center",
                icon: "success",
                title: "Laboratory result saved!",
                showConfirmButton: false,
                timer: 1000,
            });
            $btn.prop("disabled", false).text(originalText);
        },
        error() {
            Swal.fire({
                icon: "error",
                title: "Oops...",
                text: "Something went wrong!",
            });
            $btn.prop("disabled", false).text(originalText);
        },
    });
});
