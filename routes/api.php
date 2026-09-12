<?php

use App\Http\Controllers\Api\EnlistmentApiController;
use App\Http\Controllers\Api\PhilHealthApiController;
use App\Http\Controllers\Api\SoapApiController;
use App\Http\Controllers\Api\YakapManagementApiController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\SecretaryController;
use Illuminate\Support\Facades\Route;

// Secretary API Routes
Route::middleware(['web', 'auth:secretary,admin'])->group(function () {
    Route::post('fetch_patient_history', [SecretaryController::class, 'fetchPatientMedhistory']);

    Route::post('fetchGroupManagement', [SecretaryController::class, 'fetchGroupManagement']);
    Route::post('createGroupManagement', [SecretaryController::class, 'createGroupManagement']);
    Route::post('editGroupManagement', [SecretaryController::class, 'editGroupManagement']);
    Route::post('deleteGroupManagement', [SecretaryController::class, 'deleteGroupManagement']);
    Route::post('updateGroupManagement', [SecretaryController::class, 'updateGroupManagement']);
    Route::post('fetchGroupManagementCategory', [SecretaryController::class, 'fetchGroupManagementCategory']);

    Route::post('createServicesManagement', [SecretaryController::class, 'createServicesManagement']);
    Route::get('fetchDoctorServices', [SecretaryController::class, 'fetchDoctorServices']);
    Route::post('editServiceManagement', [SecretaryController::class, 'editServiceManagement']);
    Route::post('deleteServiceManagement', [SecretaryController::class, 'deleteServiceManagement']);
    Route::post('updateServiceManagement', [SecretaryController::class, 'updateServiceManagement']);

    Route::post('consultation/store', [ConsultationController::class, 'store'])->name('consultation.store');

    Route::post('refresh_queue', [ConsultationController::class, 'refreshQueue'])->name('queue.refresh');
    Route::post('fetch_consultation', [ConsultationController::class, 'fetchConsultation'])->name('consultation.fetch');
    Route::post('save_patient_consultation', [ConsultationController::class, 'saveConsultation'])->name('consultation.save');
    Route::post('update_patient_consultation', [ConsultationController::class, 'updateConsultation'])->name('consultation.update');

    Route::post('fetch_secretary', [SecretaryController::class, 'fetchSecretary']);
    Route::post('edit_secretary_account', [ManagementController::class, 'editSecretary']);

    Route::post('fetch_doctor_info', [ManagementController::class, 'fetchDoctor']);

    Route::post('fetch_doctor_questions', [SecretaryController::class, 'fetchQuestions'])->name('secretary.fetch_questions');
    Route::post('create_question', [SecretaryController::class, 'createQuestion'])->name('secretary.create_question');

    Route::post('fetch_doctor_schedules_specific', [SecretaryController::class, 'fetchSchedulesSpecific'])->name('secretary.fetch_schedule_specific');
    Route::post('fetch_doctor_schedules', [SecretaryController::class, 'fetchSchedules'])->name('secretary.fetch_schedule');
    Route::post('create_doctor_schedules', [SecretaryController::class, 'createSchedule'])->name('secretary.create_schedule');
    Route::post('delete_schedule', [SecretaryController::class, 'deleteSchedule']);

    Route::post('fetch_schedule_refno', [SecretaryController::class, 'fetchScheduleByRef']);
    Route::post('edit_schedule', [SecretaryController::class, 'editSchedule']);

    Route::post('fetch_doctors_from_secretary', [SecretaryController::class, 'fetchDoctorsFromSecretary'])->name('secretary.fetch_doctors');

    Route::post('fetch_patients', [ConsultationController::class, 'fetchConsultationPatients'])->name('consultation.fetch_patients');
    Route::post('fetch_patients_queue', [ConsultationController::class, 'fetchConsultationPatientsQueue'])->name('consultation.fetch_patients_queue');
    Route::post('fetch_unscheduled_patients', [ConsultationController::class, 'fetchConsultationPatientsUnsched']);
    Route::post('add_patient', [ConsultationController::class, 'addPatientRecord'])->name('consultation.add_patient');
    Route::post('update_patient_record', [ConsultationController::class, 'updatePatientRecord'])->name('consultation.update_patient_record');
    Route::post('fetch_patient_masterlist_sec', [ConsultationController::class, 'fetchPxMasterlist'])->name('consultation.masterlist');
    Route::post('update_queue_status', [ConsultationController::class, 'updateQueueStatus'])->name('consultation.update_queue_status');
    Route::post('fetch_latest_patient_details', [ConsultationController::class, 'fetchLatestPatientDetails'])->name('consultation.fetch_latest_patient_details');
    Route::post('reschedule_patient', [ConsultationController::class, 'reschedulePatient']);

    Route::post('fetch_hmo', [ConsultationController::class, 'fetchHMO']);

    // Patient charges
    Route::post('fetch_pxcharges', [DoctorController::class, 'fetchPatientCharges']);

    // Patient settlements
    Route::post('save_settlements', [SecretaryController::class, 'saveSettlements']);
    Route::post('fetch_settlements', [SecretaryController::class, 'fetchSettlements']);
});

// Doctor API Routes
Route::middleware(['web', 'auth:doctor,admin'])->group(function () {
    Route::post('fetch_doctor_data', [DoctorController::class, 'fetchDoctorUser']);

    Route::post('fetch_patient_history', [DoctorController::class, 'fetchPatientHistory']);

    Route::post('fetch_doctor_consultations', [DoctorController::class, 'fetchConsultationPatients'])->name('consultations');
    Route::post('fetch_patient_data', [DoctorController::class, 'fetchPatientData'])->name('doctor.getdata');
    Route::post('fetch_consultation_masterlist', [DoctorController::class, 'fetchAllPatients'])->name('doctor.masterlist');

    Route::post('fetch_todays_patients', [DoctorController::class, 'fetchTodaysPatients'])->name('doctor.todays_patients');
    Route::post('fetch_doctor_schedules_dashboard', [DoctorController::class, 'fetchDoctorSchedules'])->name('doctor.schedules');

    Route::post('add_question', [DoctorController::class, 'addQuestion'])->name('doctor.add_question');

    Route::post('save_impressions_diagnosis', [DoctorController::class, 'saveImpressionsDiagnosis']);

    Route::post('fetch_medicines', [DoctorController::class, 'fetchMedicines'])->name('doctor.medicines');
    Route::post('add_medicine', [DoctorController::class, 'addMedicine'])->name('doctor.add_medicine');
    Route::post('delete_rx', [DoctorController::class, 'deleteMedicine'])->name('doctor.delete_medicine');
    Route::post('save_rx', [DoctorController::class, 'saveRx'])->name('doctor.save_rx');
    Route::post('fetch_medicine_rx', [DoctorController::class, 'fetchMedicineRx'])->name('doctor.medicineRx');

    Route::post('fetch_diagnostics_data', [DoctorController::class, 'initializeDiagnostics']);
    Route::post('fetch_all_diagnostics', [DoctorController::class, 'fetchAllDiagnostics']);
    Route::post('get_diagnostics', [DoctorController::class, 'getDiagnosticRequests']);
    Route::post('save_diagnostics', [DoctorController::class, 'saveDiagnosticRequest']);
    Route::post('delete_diagnostic', [DoctorController::class, 'deleteDiagnostic']);

    Route::post('fetch_radlab_files', [DoctorController::class, 'fetchRadLabFiles']);
    Route::post('upload_consultation_files', [DoctorController::class, 'uploadConsultationFiles']);

    Route::post('complete_consultation', [DoctorController::class, 'completeConsultation']);

    Route::post('get_hmo_price', [DoctorController::class, 'getHmoPrice']);
});

// Utility API Routes for both Secretary & Doctor
Route::middleware(['web', 'auth:secretary,doctor'])->group(function () {
    Route::post('fetch_charge_categories_dr', [ManagementController::class, 'fetchChargeCategories']);
    Route::post('fetch_charge_categories_sc', [ManagementController::class, 'fetchChargeCategoriesSc']);

    Route::post('fetch_all_charges', [ManagementController::class, 'fetchAllCharges']);
    Route::post('fetch_charge_payments', [ManagementController::class, 'fetchChargePrice']);

    Route::post('fetch_patient_charges', [DoctorController::class, 'fetchPatientCharges'])->name('doctor.fetch_patient_charges');
    Route::post('save_patient_charges', [DoctorController::class, 'saveAppendedCharges'])->name('doctor.save_appended_charges');

    Route::post('delete_patient_charge', [DoctorController::class, 'deleteCharge'])->name('doctor.delete_charge');
    Route::post('edit_charge', [DoctorController::class, 'editCharge'])->name('doctor.edit_charge');

    Route::post('update_charge', [DoctorController::class, 'updateCharge']);
});

// Admin API Routes
Route::middleware(['web', 'auth:admin'])->group(function () {
    Route::post('get_assigned_doctors', [ManagementController::class, 'getAssigned']);

    Route::post('fetch_hmo', [ConsultationController::class, 'fetchHMO']);

    // Stocks-related
    Route::post('fetch_stocks', [ManagementController::class, 'fetchStocks']);
    Route::post('fetch_drugref', [ManagementController::class, 'fetchDrugRef']);
    Route::post('fetch_diagnostic_reference', [ManagementController::class, 'fetchDiagRef']);
    Route::post('fetch_stocks_ledger', [ManagementController::class, 'fetchStocksLedger']);

    Route::post('fetch_inventory', [ManagementController::class, 'fetchInventory']);

    Route::post('fetch_stock_item', [ManagementController::class, 'fetchStockItem']);
    Route::post('save_stock_item', [ManagementController::class, 'saveStockItem']);
    Route::post('edit_stock_item', [ManagementController::class, 'editStockItem']);
    Route::post('delete_stock_item', [ManagementController::class, 'deleteStockItem']);

    // Route::post('fetch_drug_generic', [ManagementController::class, 'fetch'])
    Route::post('fetch_drug_details', [ManagementController::class, 'fetchDrugDetails']);

    Route::post('load_profile', [ManagementController::class, 'fetchProfile']);
    Route::post('update_profile', [ManagementController::class, 'updateProfile']);

    Route::post('fetch_address_data', [ManagementController::class, 'fetchAddressData']);
    Route::post('load_company_profile', [ManagementController::class, 'loadCompanyProfile']);

    Route::post('save_philhealth_profile', [ManagementController::class, 'savePhilHealthCreds']);

    Route::post('fetch_doctors', [ManagementController::class, 'fetchDoctors'])->name('fetch.doctors');
    Route::post('fetch_doctor_details', [ManagementController::class, 'fetchDoctor'])->name('fetch.doctors_details');
    Route::post('add_doctor', [ManagementController::class, 'addDoctor'])->name('admin.add_doctor');
    Route::post('edit_doctor', [ManagementController::class, 'editDoctor'])->name('admin.edit_doctor');
    Route::post('delete_doctor', [ManagementController::class, 'deleteDoctor'])->name('admin.delete_doctor');

    Route::post('fetch_secretaries', [ManagementController::class, 'fetchSecretaries'])->name('fetch.secretaries');
    Route::post('add_secretary', [ManagementController::class, 'addSecretary'])->name('admin.add_secretary');
    Route::post('delete_secretary', [ManagementController::class, 'deleteSecretary'])->name('admin.delete_secretary');
    Route::post('edit_secretary', [ManagementController::class, 'editSecretary']);

    Route::post('fetch_secretary_doctors', [ManagementController::class, 'fetchSecretaryDoctors'])->name('admin.fetch_secretary_doctors');
    Route::post('save_appended_doctors', [ManagementController::class, 'saveAppendedDoctors'])->name('admin.save_appended_doctors');

    Route::post('fetch_diagnostic_category', [ManagementController::class, 'fetchDiagnosticCategory']);
    Route::post('create_diagnostic_category', [ManagementController::class, 'saveDiagnosticCategory']);
    Route::post('delete_diagnostic_category', [ManagementController::class, 'deleteDiagnosticCategory']);

    Route::post('fetch_diagnostics', [ManagementController::class, 'fetchDiagnostic']);
    Route::post('create_diagnostic', [ManagementController::class, 'createDiagnostic']);
    Route::post('delete_diagnostic', [ManagementController::class, 'deleteDiagnostic']);

    Route::post('fetch_medicine', [ManagementController::class, 'fetchMedicineMasterlist']);
    Route::post('fetch_medicine_reference', [ManagementController::class, 'fetchMedicineReference']);
    Route::post('admin_add_medicine', [ManagementController::class, 'addMedicine']);
    Route::post('admin_edit_medicine', [ManagementController::class, 'editMedicine']);
    Route::post('admin_delete_medicine', [ManagementController::class, 'deleteMedicine']);

    Route::post('fetch_charge_categories', [ManagementController::class, 'fetchChargeCategories']);
    Route::post('create_charge_category', [ManagementController::class, 'saveChargeCategory']);
    Route::post('edit_charge_category', [ManagementController::class, 'editChargeCategory']);
    Route::post('delete_charge_category', [ManagementController::class, 'deleteChargeCategory']);

    Route::post('fetch_specific_charges', [ManagementController::class, 'fetchSpecificCharges']);
    Route::post('fetch_charges', [ManagementController::class, 'fetchCharges']);
    Route::post('create_charge', [ManagementController::class, 'saveCharge']);
    Route::post('edit_charge', [ManagementController::class, 'editCharge']);
    Route::post('delete_charge', [ManagementController::class, 'deleteCharge']);

    Route::post('fetch_philhealth_diagnostics', [ManagementController::class, 'phDiagnosticLib']);
    Route::post('fetch_philhealth_medicines', [ManagementController::class, 'phMedicineLib']);

    Route::post('fetch_all_patient_consultation', [ManagementController::class, 'fetchPatientsConsultation']);
    Route::post('export_consultation_to_masterlist', [ManagementController::class, 'exportToMasterlist']);
    Route::post('export_to_soap', [ManagementController::class, 'exportToSOAP']);

    Route::post('fetch_patient_masterlist', [ManagementController::class, 'fetchSoapData']);
    Route::post('generate_patient_codes', [ManagementController::class, 'generatePatientCodes']);
    Route::post('fetch_transactions', [ManagementController::class, 'fetchTransactions']);

    Route::post('generate_xml_first_tranche', [ManagementController::class, 'generateXML']);
    Route::post('save_xml_report', [ManagementController::class, 'saveXML']);
    Route::post('fetch_xml_data', [ManagementController::class, 'fetchXML']);
    // Route::post('encrypt_xml', [ManagementController::class, 'encryptXML']);
    Route::post('decrypt_xml', [ManagementController::class, 'decryptXML']);
    Route::post('get_xml_data', [ManagementController::class, 'getXmlData']);

    Route::get('enlistment_search_action', [YakapManagementApiController::class, 'enlistmentSearchAction'])->name('yakap.enlistment_search');
    Route::get('enlistment_details/{caseNo}', [YakapManagementApiController::class, 'enlistmentDetails'])->name('yakap.enlistment_details');
    Route::post('save_patient_details', [YakapManagementApiController::class, 'savePatientDetails'])->name('yakap.save_patient_details');
    Route::get('profile/{transNo}', [YakapManagementApiController::class, 'fetchPatientProfile'])->name('yakap.fetch_patient_profile');
    Route::post('saveProfileData', [YakapManagementApiController::class, 'saveProfileData'])->name('yakap.save_profile_data');
    Route::get('icd-diagnosis/search', [YakapManagementApiController::class, 'searchIcdDiagnosis'])->name('yakap.icd_diagnosis_search');
    Route::get('medicine/search/inhouse', [YakapManagementApiController::class, 'searchInhouseMedicine'])->name('yakap.medicine_search_inhouse');
    Route::get('medicine/search/philhealth', [YakapManagementApiController::class, 'searchPhilhealthMedicine'])->name('yakap.medicine_search_philhealth');
    Route::get('medicine/details', [YakapManagementApiController::class, 'getMedicineDetails'])->name('yakap.medicine_details');
    Route::get('patient-masterlist/search', [YakapManagementApiController::class, 'searchPatientMasterlist'])->name('yakap.search_patient_masterlist');
    Route::get('walkin-consultations/search', [YakapManagementApiController::class, 'searchWalkinConsultations'])->name('yakap.search_walkin_consultations');
    Route::post('link-patient-pin', [YakapManagementApiController::class, 'linkPatientPin'])->name('yakap.link_patient_pin');
    // Enlistment API Routes
    Route::get('enlistment-uploads', [EnlistmentApiController::class, 'fetchUploads'])->name('enlistment.uploads');
    Route::post('enlistment/parse', [EnlistmentApiController::class, 'parseXml'])->name('enlistment.parse');
    Route::post('enlistment/save', [EnlistmentApiController::class, 'saveEnlistment'])->name('enlistment.save');

    Route::get('soap/{transNo}/details', [SoapApiController::class, 'getSoapDetails'])->name('soap.details');
    Route::post('soap/save', [SoapApiController::class, 'saveSoapData'])->name('soap.save');
    Route::post('soap/save-lab-result', [SoapApiController::class, 'saveLabResult'])->name('soap.save-lab-result');
    Route::post('soap/link-consultation', [SoapApiController::class, 'linkConsultationCode'])->name('soap.link-consultation');
    Route::get('soap/check-first-consultation/{selectedEnlistmentCaseNo}', [SoapApiController::class, 'checkFirstConsultation'])->name('soap.check-first-consultation');
    Route::get('medicine/import-from-ledger', [YakapManagementApiController::class, 'importMedicineFromLedger'])->name('yakap.medicine_import_from_ledger');

    // PDF Reports
    Route::get('pdf/ekas/{transNo}', [App\Http\Controllers\ReportController::class, 'generateEkas'])->name('pdf.ekas');
    Route::get('pdf/epress/{transNo}', [App\Http\Controllers\ReportController::class, 'generateEpress'])->name('pdf.epress');

    // API
    Route::post('generate_token', [PhilHealthApiController::class, 'generateToken']);               // getToken
    Route::post('check_registration', [PhilHealthApiController::class, 'checkRegistration']);       // isMemberDependentRegistered
    Route::post('fetch_registrations', [PhilHealthApiController::class, 'fetchRegistrations']);     // extractRegitrationList
    Route::post('validate_atc', [PhilHealthApiController::class, 'checkATC']);                      // isATCValid
    Route::post('validate_xml', [PhilHealthApiController::class, 'validateXML']);                   // validateReport
    Route::post('submit_xml', [PhilHealthApiController::class, 'submitXML']);                       // submitXML

    // HMO
    Route::post('fetch_all_hmo', [ManagementController::class, 'fetchAllHmo']);
    Route::post('add_hmo', [ManagementController::class, 'addHmo']);
    Route::post('edit_hmo', [ManagementController::class, 'editHmo']);
    Route::post('delete_hmo', [ManagementController::class, 'deleteHmo']);
});
