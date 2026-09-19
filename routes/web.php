<?php

use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\EnlistmentController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\ManagementController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\SecretaryController;
use App\Http\Controllers\YakapManagementController;
use App\Models\KayakapProfileModel;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('home', function () {
    $profile = KayakapProfileModel::firstOrFail();

    return view('home', compact('profile'));
})->name('home');

Route::get('register', function () {
    return view('patient');
});

Route::post('register', [PatientController::class, 'register'])->name('register');

// Login Routes
Route::get('login', [LoginController::class, 'index'])->name('login');
Route::post('login', [LoginController::class, 'authenticate'])->name('login.authenticate');
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

// Secretary Routes — page views only
Route::middleware('auth:secretary,admin')->group(function () {
    Route::get('secretary', [SecretaryController::class, 'index'])->name('secretary');
    Route::get('secretary/queue', [SecretaryController::class, 'queuePage'])->name('secretary.queue');
    Route::get('generate_new_codes', [SecretaryController::class, 'generateNewCodes'])->name('secretary.generate_new_codes');
});

// Doctor Routes — page views only
Route::middleware('auth:doctor,admin')->group(function () {
    Route::get('doctor', [DoctorController::class, 'index'])->name('doctor');
    Route::get('doctor/dashboard', [DoctorController::class, 'dashboardPage'])->name('doctor.dashboard');
    Route::get('doctor/consultation', [DoctorController::class, 'consultationPage'])->name('doctor.consultation');
    Route::get('doctor/patients', [DoctorController::class, 'patientsPage'])->name('doctor.patients');
});

// Utility & Print routes — non-JSON / document streams accessible across roles
Route::middleware('auth:secretary,doctor,admin')->group(function () {
    Route::get('/patient/photo/{filename}', [ConsultationController::class, 'fetchPatientPhoto']);
    // Detailed Comment: Prescription, instructions, and diagnostics print views accessible across doctor, secretary, and admin roles
    Route::get('print_pdf', [DoctorController::class, 'printPDF'])->name('print.pdf');
    Route::get('doctor/print_diagnostics', [DoctorController::class, 'printDiagnostics'])->name('doctor.print_diagnostics');
    Route::get('print_diagnostics', [DoctorController::class, 'printDiagnostics'])->name('print_diagnostics');
});

// Admin Routes — page views only
Route::middleware('auth:admin')->group(function () {
    Route::get('admin', [ManagementController::class, 'index'])->name('admin');
    Route::get('admin/dashboard', [ManagementController::class, 'dashboardPage'])->name('admin.dashboard');
    Route::get('admin/profile', [ManagementController::class, 'profilePage'])->name('admin.profile');
    Route::get('admin/secretary', [ManagementController::class, 'secretaryPanelPage'])->name('admin.secretary');
    Route::get('admin/hmo', [ManagementController::class, 'hmoPanelPage'])->name('admin.hmo');

    Route::get('admin/consultations/requests', [ManagementController::class, 'diagnosticRequestsPage'])->name('admin.consultations.requests');
    Route::get('admin/consultations/billing', [ManagementController::class, 'billingsPage'])->name('admin.consultations.billing');
    Route::get('admin/consultations/settlements', [ManagementController::class, 'settlementsPage'])->name('admin.consultations.settlements');

    // Detailed Comment: Staff management page updated to Secretaries/Admin Users with alias
    Route::get('admin/users/secretaries-admins', [ManagementController::class, 'secretariesAdminsPage'])->name('admin.users.secretaries_admin');
    Route::get('admin/users/secretaries', [ManagementController::class, 'secretariesAdminsPage'])->name('admin.users.secretaries');
    Route::get('admin/users/doctors', [ManagementController::class, 'doctorsPage'])->name('admin.users.doctors');

    // Detailed Comment: Route aliases for doctor schedule operations initiated from admin doctor management modals
    Route::post('admin/users/create_doctor_schedules', [SecretaryController::class, 'createSchedule'])->name('admin.users.create_doctor_schedules');
    Route::post('admin/users/fetch_doctor_schedules', [SecretaryController::class, 'fetchSchedules'])->name('admin.users.fetch_doctor_schedules');
    Route::post('admin/users/delete_schedule', [SecretaryController::class, 'deleteSchedule'])->name('admin.users.delete_schedule');
    Route::post('admin/users/edit_schedule', [SecretaryController::class, 'editSchedule'])->name('admin.users.edit_schedule');
    Route::post('admin/users/fetch_schedule_refno', [SecretaryController::class, 'fetchScheduleByRef'])->name('admin.users.fetch_schedule_refno');

    Route::get('admin/stocks/management', [ManagementController::class, 'stocksManagementPage'])->name('admin.stocks.management');
    Route::get('admin/stocks/ledger', [ManagementController::class, 'stocksLedgerPage'])->name('admin.stocks.ledger');
    Route::get('admin/stocks/inventory', [ManagementController::class, 'inventoryPage'])->name('admin.stocks.inventory');


    Route::get('admin/philhealth/overview', [ManagementController::class, 'philhealthOverviewPage'])->name('admin.philhealth.overview');
    Route::get('admin/philhealth/management', [ManagementController::class, 'philhealthManagementPage'])->name('admin.philhealth.management');
    Route::get('admin/philhealth/uploading', [ManagementController::class, 'philhealthUploadingPage'])->name('admin.philhealth.uploading');
    Route::get('admin/philhealth/tools', [ManagementController::class, 'philhealthToolsPage'])->name('admin.philhealth.tools');
    Route::get('admin/philhealth/consultations', [ManagementController::class, 'philhealthConsultationsPage'])->name('admin.philhealth.consultations');
    Route::get('admin/philhealth/masterlist', [ManagementController::class, 'philhealthMasterlistPage'])->name('admin.philhealth.masterlist');

    Route::get('admin/utilities/chargecat', [ManagementController::class, 'chargeCatgPage'])->name('admin.utilities.chargecat');
    Route::get('admin/utilities/diagcat', [ManagementController::class, 'diagCatgPage'])->name('admin.utilities.diagcat');

    Route::get('print_transactions', [ManagementController::class, 'printTransactions'])->name('admin.print_transaction');
});

// Fetch secured files
Route::get('/preview-file/{path}', function ($path) {
    return response()->file(storage_path('app/private/' . $path));
})->where('path', '.*')->middleware('auth:doctor');
