<?php

namespace App\Http\Controllers;

use DB;
use Date;
use Carbon\Carbon;
use App\Models\KayakapProfileModel;
use App\Models\PatientMasterlist;
use App\Models\Stocks\StocksListingModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\ChargesModel;
use App\Models\ConsultationModel;
use App\Models\DiagnosticsMasterlistModel;
use App\Models\DiagnosticsModel;
use App\Models\DocChargesModel;
use App\Models\DocRequestsModel;
use App\Models\DoctorMedicinesModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\MedicineModel;
use App\Models\ScheduleModel;
use App\Models\SettlementsModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class DoctorController extends Controller
{
    public function index()
    {
        return redirect()->route('doctor.dashboard');
    }

    public function dashboardPage()
    {
        $doctor = auth()->guard('doctor')->user();

        // Detailed Comment: Structured log when doctor dashboard view is accessed
        Log::info('Doctor dashboard rendered', [
            'doctor_id' => $doctor ? $doctor->id : null,
            'docrefno' => $doctor ? $doctor->docrefno : null,
            'username' => $doctor ? $doctor->username : null,
        ]);

        return view('pages.doctor.dashboard', [
            'doctor' => $doctor,
        ]);
    }

    public function consultationPage()
    {
        $doctor = auth()->guard('doctor')->user();

        return view('pages.doctor.consultation', [
            'doctor' => $doctor,
        ]);
    }

    public function patientsPage()
    {
        $doctor = auth()->guard('doctor')->user();

        return view('pages.doctor.patients', [
            'doctor' => $doctor,
        ]);
    }

    /**
     * Fetch authenticated doctor user profile including login username for profile modal.
     */
    public function fetchDoctorUser()
    {
        $doctorAuth = auth()->guard('doctor')->user();
        if (!$doctorAuth) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated doctor'], 401);
        }

        $user = DoctorsProfileModel::where('docrefno', $doctorAuth->docrefno)->first();
        if ($user) {
            $user->username = $doctorAuth->username ?: DoctorModel::where('docrefno', $doctorAuth->docrefno)->value('username');
            $user->source_table = 'doctors & doctorsrights';
        }

        return response()->json(['success' => true, 'user' => $user]);
    }

    /**
     * Detailed Comment: Self-service profile update for authenticated doctor.
     * Allows doctor to edit their own profile in 'doctors' and credentials in 'doctorsrights',
     * strictly bound to the authenticated doctor's docrefno to prevent cross-user tampering.
     */
    public function updateDoctorProfile(Request $request)
    {
        $doctorAuth = auth()->guard('doctor')->user();
        if (!$doctorAuth) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated doctor'], 401);
        }

        $request->validate([
            'docfname' => 'required|string|max:100',
            'doclname' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:doctorsrights,username,' . $doctorAuth->id,
            'new_password' => 'nullable|string|min:5',
            'emailadd' => 'nullable|email|max:100',
            'cellno' => 'nullable|string|max:20'
        ]);

        $docrefno = $doctorAuth->docrefno;

        // Detailed Comment: Update 'doctors' table with all clinical and profile fields
        DoctorsProfileModel::where('docrefno', $docrefno)->update([
            'docfname' => $request->docfname,
            'docmname' => $request->docmname,
            'doclname' => $request->doclname,
            'suffix' => $request->suffix,
            'titlename' => $request->titlename,
            'docfirst' => $request->docfirst ?? $request->docfname,
            'docname' => trim($request->docfname . ' ' . ($request->docmname ?? '') . ' ' . $request->doclname . ' ' . ($request->suffix ?? '')),
            'emailadd' => $request->emailadd,
            'cellno' => $request->cellno,
            'adrs' => $request->adrs,
            'proftype' => $request->proftype,
            'expertise' => $request->expertise,
            'department' => $request->department,
            'profgroup' => $request->profgroup,
            'catg' => $request->catg,
            'station' => $request->station,
            'groupname' => $request->groupname,
            'tin' => $request->tin,
            'Licno' => $request->licno,
            'licnoexpiry' => $request->licnoexpiry,
            'phicno' => $request->phicno,
            'phicexpiry' => $request->phicexpiry,
            'phicname' => $request->phicname,
            'phicenable' => $request->boolean('phicenable'),
            'phicrate' => $request->phicrate,
            'S2no' => $request->s2no,
            'PTR' => $request->ptr,
            'clinicroom' => $request->clinicroom,
            'clinichours' => $request->clinichours,
            'pfrate' => $request->pfrate ?? $request->consultationfee ?? 0,
            'rodrate' => $request->rodrate,
            'coacode' => $request->coacode,
            'accountno' => $request->accountno,
            'tax' => $request->tax,
            'vatable' => $request->boolean('vatable'),
            'vatrate' => $request->vatrate,
            'VAT' => $request->vatrate ?? $request->VAT,
            'autoAddVAT' => $request->boolean('autoAddVAT'),
            'issuehospOR' => $request->boolean('issuehospOR'),
            'quevisible' => $request->boolean('quevisible'),
            'allowtextresult' => $request->boolean('allowtextresult'),
            'allowdocsystem' => $request->boolean('allowdocsystem'),
            'disabletext' => $request->boolean('disabletext'),
            'otherinfo' => $request->otherinfo,
            'biodata' => $request->biodata
        ]);

        // Detailed Comment: Synchronize 'doctorsrights' table credentials and profile rights
        $rightsData = [
            'docfname' => $request->docfname,
            'docmname' => $request->docmname,
            'doclname' => $request->doclname,
            'suffix' => $request->suffix,
            'titlename' => $request->titlename,
            'username' => strtolower(trim($request->username)),
            'eadd' => $request->emailadd,
            'mnumber' => $request->cellno,
            'tin' => $request->tin,
            'address' => $request->adrs,
            'expertise' => $request->expertise,
            'proftype' => $request->proftype,
            'taxpercent' => $request->tax,
            'consultationfee' => $request->consultationfee ?? $request->pfrate ?? 0
        ];

        if ($request->filled('new_password')) {
            $rightsData['pass'] = Hash::make($request->new_password);
        }

        DoctorModel::where('docrefno', $docrefno)->update($rightsData);

        Log::info('Doctor self-service profile updated', [
            'docrefno' => $docrefno,
            'username' => $request->username
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    }

    public function fetchPatientHistory(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);

        $patient = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->value('pxrefno');
        $history = ConsultationModel::where(['pxrefno' => $patient])
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsFiltered' => $history->count(),
            'recordsTotal' => $history->count(),
            'data' => $history
        ]);
    }

    public function fetchAllPatients(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value');

        // Detailed Comment: Correlate each patient with their latest walk-in consultation without duplication.
        // Left join subquery ensures patients without consultations are also visible in the masterlist.
        $latestConsultation = DB::table('pxwalkinconsultation as c1')
            ->select('c1.pxrefno', 'c1.consultationrefno', 'c1.consultation_date', 'c1.photo_path', 'c1.docrefno')
            ->whereRaw('c1.id = (SELECT MAX(c2.id) FROM pxwalkinconsultation as c2 WHERE c2.pxrefno = c1.pxrefno)');

        $baseQuery = DB::table('pxmasterlist')
            ->leftJoinSub($latestConsultation, 'latest_c', function ($join) {
                $join->on('pxmasterlist.pxrefno', '=', 'latest_c.pxrefno');
            });

        // Doctor role scoping: view assigned / consulted patients or general clinic patients
        if (auth()->guard('doctor')->check()) {
            $docrefno = auth()->guard('doctor')->user()->docrefno;
            $baseQuery->where(function ($q) use ($docrefno) {
                $q->where('pxmasterlist.last_docrefno', $docrefno)
                    ->orWhere('latest_c.docrefno', $docrefno)
                    ->orWhereNull('pxmasterlist.last_docrefno')
                    ->orWhere('pxmasterlist.last_docrefno', '');
            });
        }

        // TOTAL (no filter)
        $recordsTotal = (clone $baseQuery)->count();

        // APPLY SEARCH across multiple patient identifiers and demographic fields
        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('pxmasterlist.patientname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxfirstname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxmidname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxlastname', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pincode', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.pxrefno', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.mobilenumber', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.emailaddress', 'like', "%{$search}%")
                    ->orWhere('pxmasterlist.phic_pin', 'like', "%{$search}%");
            });
        }

        // FILTERED COUNT
        $recordsFiltered = (clone $baseQuery)->count();

        // DATA: Select all pxmasterlist fields + latest consultation metadata
        $data = $baseQuery
            ->select([
                'pxmasterlist.id',
                'pxmasterlist.pxrefno',
                'pxmasterlist.pincode',
                'pxmasterlist.patientname',
                'pxmasterlist.pxfirstname',
                'pxmasterlist.pxmidname',
                'pxmasterlist.pxlastname',
                'pxmasterlist.pxsuffix',
                'pxmasterlist.gender',
                'pxmasterlist.birthday',
                'pxmasterlist.age',
                'pxmasterlist.religion',
                'pxmasterlist.nationality',
                'pxmasterlist.mobilenumber',
                'pxmasterlist.emailaddress',
                'pxmasterlist.address',
                'pxmasterlist.streetadrs',
                'pxmasterlist.brgy',
                'pxmasterlist.muncity',
                'pxmasterlist.province',
                'pxmasterlist.zipcode',
                'pxmasterlist.region',
                'pxmasterlist.country',
                'pxmasterlist.phic_pin',
                'pxmasterlist.ipd_pincode',
                'pxmasterlist.ispwd',
                'pxmasterlist.senior_idno',
                'pxmasterlist.last_consultation',
                'pxmasterlist.last_enlistcode',
                'pxmasterlist.last_docrefno',
                'pxmasterlist.last_docname',
                'pxmasterlist.classification',
                'pxmasterlist.followupdate',
                'pxmasterlist.followupcheckup',
                'pxmasterlist.recordedby',
                'pxmasterlist.recordeddate',
                'pxmasterlist.updatedby',
                'pxmasterlist.updated',
                'latest_c.consultationrefno',
                'latest_c.consultation_date',
                'latest_c.docrefno',
                'latest_c.photo_path'
            ])
            ->orderBy('pxmasterlist.id', 'desc')
            ->skip($start)
            ->take($length)
            ->get();

        $data->transform(function ($patient) {
            if ($patient->photo_path) {
                $filename = basename($patient->photo_path);
                $patient->photo_path = url('/patient/photo/' . $filename);
            }
            return $patient;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data
        ]);
    }

    public function fetchPatientMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        // Get one copy of patients under a doctor
        $latestRecords = ConsultationModel::selectRaw('pincode, MAX(recordeddate) as latest_date')
            ->where(['docrefno' => $request->docrefno])
            ->groupBy('pincode');

        $query = ConsultationModel::from('pxwalkinconsultation as consultations')
            ->joinSub($latestRecords, 'latest', function ($join) {
                $join->on('consultations.pincode', '=', 'latest.pincode')
                    ->on('consultations.recordeddate', '=', 'latest.latest_date');
            })
            ->where('consultations.docrefno', $request->docrefno)
            // Detailed Comment: Select attributes with aliases for nonexistent columns (doccode, casecode, pinno, isMember, memPin, address)
            ->select([
                'consultations.docrefno',
                'consultations.doccoaOPD',
                'consultations.docrefno as doccode',
                'consultations.pincode',
                DB::raw("COALESCE(NULLIF(consultations.caseno, ''), NULLIF(consultations.consultationrefno, ''), consultations.pxrefno) as casecode"),
                'consultations.phic_pin as pinno',
                'consultations.caseno',
                DB::raw("0 as isMember"),
                DB::raw("'' as memPin"),
                'consultations.patientname',
                'consultations.pxmidname',
                'consultations.pxlastname',
                'consultations.pxsuffix',
                'consultations.gender',
                'consultations.birthday',
                'consultations.age',
                'consultations.mobilenumber',
                'consultations.emailaddress',
                DB::raw("'' as address"),
                'consultations.photo_path',
                'consultations.recordeddate'
            ]);

        // Narrow down records by search if not empty
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('consultations.patientname', 'LIKE', "%{$search}%")
                    ->orWhere('consultations.pxlastname', 'LIKE', "%{$search}%");
            });
        }

        // Get unique record count
        $totalRecords = ConsultationModel::where(['docrefno' => $request->docrefno])->distinct('pincode')->count();
        $filteredRecords = $query->count();

        $patients = $query->orderBy('consultations.recordeddate', 'DESC')->offset($start)->limit($length)->get();
        $patients->transform(function ($patient) {
            // Get patient photo by GET
            if ($patient->photo_path) {
                $filename = basename($patient->photo_path);
                $patient->photo_path = url('/patient/photo/' . $filename);
            }

            return $patient;
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'patients' => $patients
        ]);
    }

    public function fetchConsultationPatients(Request $request)
    {
        $patients = ConsultationModel::select('consultationrefno', 'status', 'patientname', 'pxmidname', 'pxlastname', 'pxsuffix', 'reasonforconsultation')
            ->where(['docrefno' => auth()->guard('doctor')->user()->docrefno])
            ->whereDate('consultation_date', $request->date)
            ->orderBy('queueno', 'ASC')
            ->get();
        return response()->json(['patients' => $patients]);
    }

    public function fetchTodaysPatients(Request $request)
    {
        $patients = ConsultationModel::whereDate('consultation_date', Carbon::today())
            ->orderBy('queueno', 'ASC')
            ->get();

        return response()->json(['patients' => $patients, 'count' => $patients->count()]);
    }

    /**
     * Detailed Comment: Fetch clinic schedules for the authenticated doctor.
     * Orders schedules by day of week and start time for consistent calendar display.
     */
    public function fetchDoctorSchedules()
    {
        $doctor = auth()->guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $schedules = ScheduleModel::where('docrefno', $doctor->docrefno)
            ->orderBy('day', 'ASC')
            ->orderBy('start', 'ASC')
            ->get();

        return response()->json(['success' => true, 'schedules' => $schedules]);
    }

    /**
     * Detailed Comment: Create a new clinic schedule for the authenticated doctor.
     * Generates a unique schedrefno and strictly scopes persistence to the doctor's docrefno.
     */
    public function createSchedule(Request $request)
    {
        $doctor = auth()->guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $day = $request->input('day') ?: $request->input('schedule_day');
        $start = $request->input('start') ?: $request->input('stime') ?: $request->input('sched_from');
        $end = $request->input('end') ?: $request->input('etime') ?: $request->input('sched_to');

        $request->merge([
            'day' => $day,
            'start' => $start,
            'end' => $end,
        ]);

        $request->validate([
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start' => 'required',
            'end' => 'required',
        ]);

        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));
        $schedrefno = now()->format('mdYHis') . 'SCHED' . rand(10, 99);

        $schedule = ScheduleModel::create([
            'dw_clientcode' => $facilityClientCode,
            'schedrefno' => $schedrefno,
            'docrefno' => $doctor->docrefno,
            'day' => $day,
            'start' => $start,
            'end' => $end,
        ]);

        Log::info('Doctor created clinic schedule', [
            'docrefno' => $doctor->docrefno,
            'schedrefno' => $schedrefno,
            'day' => $day,
            'start' => $start,
            'end' => $end,
        ]);

        return response()->json(['success' => true, 'schedule' => $schedule]);
    }

    /**
     * Detailed Comment: Fetch a single schedule by reference number verifying ownership by the authenticated doctor.
     */
    public function fetchScheduleByRef(Request $request)
    {
        $doctor = auth()->guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $schedule = ScheduleModel::where('schedrefno', $request->schedrefno)
            ->where('docrefno', $doctor->docrefno)
            ->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Schedule not found or unauthorized'], 404);
        }

        return response()->json(['success' => true, 'sched' => $schedule]);
    }

    /**
     * Detailed Comment: Update an existing clinic schedule ensuring doctor can only edit their own schedule.
     */
    public function editSchedule(Request $request)
    {
        $doctor = auth()->guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $request->validate([
            'schedrefno' => 'required|string',
            'day' => 'required|in:Sunday,Monday,Tuesday,Wednesday,Thursday,Friday,Saturday',
            'start' => 'required',
            'end' => 'required',
        ]);

        $schedule = ScheduleModel::where('schedrefno', $request->schedrefno)
            ->where('docrefno', $doctor->docrefno)
            ->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Schedule not found or unauthorized'], 404);
        }

        $schedule->update([
            'day' => $request->day,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        Log::info('Doctor updated clinic schedule', [
            'docrefno' => $doctor->docrefno,
            'schedrefno' => $request->schedrefno,
            'day' => $request->day,
            'start' => $request->start,
            'end' => $request->end,
        ]);

        return response()->json(['success' => true, 'schedule' => $schedule]);
    }

    /**
     * Detailed Comment: Delete a clinic schedule ensuring doctor can only delete their own schedule.
     */
    public function deleteSchedule(Request $request)
    {
        $doctor = auth()->guard('doctor')->user();
        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $schedule = ScheduleModel::where('schedrefno', $request->schedrefno)
            ->where('docrefno', $doctor->docrefno)
            ->first();

        if (!$schedule) {
            return response()->json(['success' => false, 'message' => 'Schedule not found or unauthorized'], 404);
        }

        $schedule->delete();

        Log::info('Doctor deleted clinic schedule', [
            'docrefno' => $doctor->docrefno,
            'schedrefno' => $request->schedrefno,
        ]);

        return response()->json(['success' => true, 'message' => 'Schedule deleted successfully.']);
    }

    public function fetchPatientData(Request $request)
    {
        $patient = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        $patient->photo_path = $patient->photo_path != null ? url('/patient/photo/' . basename($patient->photo_path)) : null;

        return response()->json(['patient' => $patient]);
    }

    public function addQuestion(Request $request)
    {
        return response()->json(['success' => true]);
    }

    // Rx & Instructions
    public function fetchMedicines(Request $request)
    {
        $medicines = StocksListingModel::select(['prodcode', 'prod_itemdscr'])
            ->where(['item_grouping' => 'DRUGS AND MEDS'])
            ->where('prod_itemdscr', 'LIKE', "%{$request->term}%")
            ->get();

        return response()->json($medicines);
    }

    public function fetchMedicineRx(Request $request)
    {
        $rx = StocksLedgerModel::select(['item_dscr', 'qty', 'dispensed_status', 'prodcode'])->where(['px_consultcode_cn' => $request->consultationrefno, 'item_grouping' => 'DRUGS AND MEDS'])->get();
        $instructions = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->pluck('instructions');

        return response()->json(['rx' => $rx, 'instructions' => $instructions]);
    }

    /**
     * Detailed Comment: Adds prescription medicine into stocks_ledger.
     * Looks up price and PHIC reference from stocks_listing, computes unit price and total amount,
     * and sets transactiontype = 'CHARGES' so that prescription medicines seamlessly reflect
     * with valid prices and line totals in Patient Charges and Billing.
     */
    public function addMedicine(Request $request)
    {
        $consultation = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        if (!$consultation) {
            return response()->json(['success' => false, 'message' => 'Consultation not found.'], 404);
        }

        $medicine = StocksListingModel::where(['item_grouping' => 'DRUGS AND MEDS', 'prodcode' => $request->prodcode])->first();
        if (!$medicine) {
            return response()->json(['success' => false, 'message' => 'Medicine not found in inventory listing.'], 404);
        }

        $qty = (float)($request->qty ?: 1);
        $unitPrice = (float)($medicine->price_regular ?? $medicine->cost_ave ?? 0);
        $totalAmt = $unitPrice * $qty;

        $record = StocksLedgerModel::create([
            'transactiontype' => 'CHARGES',
            'px_pin' => $consultation->pxrefno,
            'px_consultcode_cn' => $request->consultationrefno,
            'patient_name' => $consultation->patientname,
            'prodcode' => $request->prodcode,
            'phic_reference_code' => $medicine->phic_reference_code ?? '',
            'item_dscr' => $medicine->prod_itemdscr ?? '',
            'qty' => $qty,
            'cost_ave' => $unitPrice,
            'retails' => $unitPrice,
            'totalamt' => $totalAmt,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Detailed Comment: Deletes prescribed medicine from stocks_ledger by consultationrefno and prodcode,
     * maintaining synchronization with patient charges.
     */
    public function deleteMedicine(Request $request)
    {
        $med = StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno, 'prodcode' => $request->prodcode])->first();
        if ($med) {
            $med->delete();
        }

        return response()->json(['success' => true]);
    }

    public function saveRx(Request $request)
    {
        $result = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->update([
            'instructions' => $request->pxinstructions
        ]);

        return response()->json(['success' => true, 'instructions' => $request->pxinstructions]);
    }

    /**
     * Detailed Comment: Streams PDF printable document for Rx (prescription) or Instructions.
     * Safely resolves patient by consultationrefno or caseno, populates missing address from patient masterlist,
     * resolves attending physician across doctor, secretary, and admin authentication guards, and retrieves
     * prescription medicines from both stocks_ledger (DRUGS AND MEDS) and pxrxdocuments.
     */
    public function printPDF(Request $request)
    {
        $refno = $request->consultationrefno ?: $request->query('consultationrefno');
        $patient = ConsultationModel::with('patient')
            ->where('consultationrefno', $refno)
            ->orWhere('caseno', $refno)
            ->first();

        // Detailed Comment: Fallback patient object to avoid null reference exceptions in Blade view
        if (!$patient) {
            $patient = (object)[
                'patientname' => 'Patient',
                'age' => '',
                'gender' => '',
                'address' => '',
                'instructions' => '',
                'docrefno' => null,
            ];
        } else {
            // Detailed Comment: Resolve address from related PatientMasterlist if not directly on consultation record
            if (empty($patient->address) && $patient->patient) {
                $pMaster = $patient->patient;
                $addrParts = array_filter([$pMaster->streetadrs, $pMaster->brgy, $pMaster->muncity, $pMaster->province]);
                $patient->address = !empty($addrParts) ? implode(', ', $addrParts) : ($pMaster->address ?? '');
            }
        }

        // Detailed Comment: Safely resolve attending physician across doctor, secretary, or admin guards
        $docrefno = null;
        if (auth()->guard('doctor')->check() && auth()->guard('doctor')->user()) {
            $docrefno = auth()->guard('doctor')->user()->docrefno;
        }
        if (!$docrefno && !empty($patient->docrefno)) {
            $docrefno = $patient->docrefno;
        }

        $doctor = $docrefno ? DoctorsProfileModel::where('docrefno', $docrefno)->first() : null;
        if (!$doctor) {
            $doctor = (object)[
                'docname' => $patient->docname ?? 'Attending Physician',
                'Licno' => '',
                'PTR' => '',
                'S2no' => '',
            ];
        }

        // Detailed Comment: Retrieve prescription medicines strictly matching 'DRUGS AND MEDS' grouping
        // to guarantee that diagnostic requests, medical supplies, and administrative charges never print on Rx prescriptions.
        $medicines = collect();
        if ($refno) {
            $ledgerMeds = StocksLedgerModel::where('px_consultcode_cn', $refno)
                ->where('item_grouping', 'DRUGS AND MEDS')
                ->get()
                ->map(function ($item) {
                    return [
                        'medicinename' => $item->item_dscr,
                        'medicinedosage' => '',
                        'medicineduration' => '',
                        'medicinequantity' => $item->qty ?: 1,
                    ];
                });

            $rxDocs = DoctorMedicinesModel::where('consultationrefno', $refno)
                ->get()
                ->map(function ($item) {
                    return [
                        'medicinename' => $item->medicinename,
                        'medicinedosage' => $item->medicinedosage ?? '',
                        'medicineduration' => $item->medicineduration ?? '',
                        'medicinequantity' => $item->medicinequantity ?? 1,
                    ];
                });

            $medicines = $ledgerMeds->concat($rxDocs);
        }

        // Detailed Comment: Retrieve patient charges, diagnostics, and billing settlement for SOA and admission documents
        $charges = collect();
        $settlement = null;
        $requests = collect();
        if ($refno) {
            $charges = StocksLedgerModel::where('px_consultcode_cn', $refno)->get();
            $settlement = SettlementsModel::where('consultationrefno', $refno)->first();

            $diagProdCodes = StocksListingModel::where('item_grouping', 'DIAGNOSTIC')->pluck('prodcode')->toArray();
            $ledgerDiags = StocksLedgerModel::where('px_consultcode_cn', $refno)
                ->where(function ($q) use ($diagProdCodes) {
                    $q->where('item_grouping', 'DIAGNOSTIC')
                      ->orWhereIn('prodcode', $diagProdCodes);
                })
                ->get()
                ->map(function ($item) {
                    return (object)[
                        'diagnostic_name' => $item->item_dscr,
                    ];
                });

            $pxreq = DocRequestsModel::where(['consultationrefno' => $refno])->get();
            $masterDiags = DiagnosticsMasterlistModel::whereIn('diagnosticrefno', $pxreq->pluck('requestrefno')->toArray())->get()
                ->map(function ($item) {
                    return (object)[
                        'diagnostic_name' => $item->diagnostic_name,
                    ];
                });

            $requests = $ledgerDiags->concat($masterDiags);
        }

        $profile = KayakapProfileModel::first() ?? (object)[
            'HOSP_NAME' => config('app.name', 'KayakapMD Clinic'),
            'HOSP_ADDBRGY' => ''
        ];

        $type = $request->type ?: $request->query('type', 'rx');

        $filenamePrefix = match($type) {
            'admission' => 'admission_orders_',
            'soa' => 'statement_of_account_',
            'instructions' => 'instructions_',
            'diagnostics' => 'diagnostics_',
            default => 'prescription_'
        };

        try {
            return Pdf::loadView('printables.rx_print', compact('doctor', 'type', 'patient', 'profile', 'medicines', 'charges', 'settlement', 'requests'))
                ->setPaper('A4', 'portrait')
                ->stream($filenamePrefix . ($refno ?: 'document') . '.pdf');
        } catch (\Throwable $e) {
            // Detailed Comment: Structured error logging if PDF rendering fails
            Log::error('Failed to generate printable PDF document', [
                'consultationrefno' => $refno,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response('Error generating PDF document: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    // Diagnostics
    public function initializeDiagnostics(Request $request)
    {
        $diagnostics = StocksListingModel::where(['item_grouping' => 'DIAGNOSTIC'])->get();
        $pxrequests = StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno, 'item_grouping' => 'DIAGNOSTIC'])->pluck('prodcode')->toArray();

        $requested = $diagnostics->whereIn('prodcode', $pxrequests)->values();
        $available = $diagnostics->whereNotIn('prodcode', $pxrequests)->values();

        return response()->json([
            'available' => $available,
            'requested' => $requested,
        ]);

        // $categories = DiagnosticsCategoryModel::all();
        // $diagnostics = DiagnosticsMasterlistModel::all();
        // $pxrequests = DocRequestsModel::where([
        //     'consultationrefno' => $request->consultationrefno
        // ])->pluck('requestrefno')->toArray();

        // $requested = $diagnostics->whereIn('diagnosticrefno', $pxrequests)->values();
        // $available = $diagnostics->whereNotIn('diagnosticrefno', $pxrequests)->values();

        // return response()->json([
        //     'categories' => $categories,
        //     'available' => $available,
        //     'requested' => $requested
        // ]);
    }

    public function fetchAllDiagnostics()
    {
        $procedures = DiagnosticsModel::all();

        return response()->json(['procedures' => $procedures]);
    }

    public function getDiagnosticRequests(Request $request)
    {
        $allDiagnostics = StocksListingModel::where(['item_grouping' => 'DIAGNOSTIC'])->get();
        $requested = StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno])->pluck('prodcode')->toArray();

        $requestedDiagnostics = $allDiagnostics->whereIn('prodcode', $requested)->values();
        $availableDiagnostics = $allDiagnostics->whereNotIn('prodcode', $requested)->values();

        return response()->json([
            'available' => $availableDiagnostics,
            'requested' => $requestedDiagnostics
        ]);

        // $allDiagnostics = DiagnosticsMasterlistModel::all();

        // // Get requested diagnostics for this consultation
        // $requested = DocRequestsModel::where('consultationrefno', $request->consultationrefno)
        //     ->pluck('requestrefno')  // Get only diagnostic IDs
        //     ->toArray();

        // // Separate diagnostics
        // $requestedDiagnostics = $allDiagnostics->whereIn('diagnosticrefno', $requested)->values();
        // $availableDiagnostics = $allDiagnostics->whereNotIn('diagnosticrefno', $requested)->values();

        // return response()->json([
        //     'available' => $availableDiagnostics,
        //     'requested' => $requestedDiagnostics
        // ]);
    }

    /**
     * Detailed Comment: Saves requested diagnostic procedures into stocks_ledger.
     * Explicitly sets item_grouping = 'DIAGNOSTIC' and transactiontype = 'CHARGES' so that
     * diagnostic requests are recognized in printables and patient billing without contaminating medicines.
     */
    public function saveDiagnosticRequest(Request $request)
    {
        $patient = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();

        $diagsList = $request->diagnostics;
        if (empty($diagsList) && $request->filled('requestrefno')) {
            $diagsList = [$request->requestrefno];
        }
        if (!is_array($diagsList)) {
            $diagsList = $diagsList ? [$diagsList] : [];
        }

        foreach ($diagsList as $diags) {
            $item = StocksListingModel::where(['prodcode' => $diags])->first();
            if (!$item) {
                continue;
            }

            // Sync with legacy doc_requests table
            DocRequestsModel::firstOrCreate([
                'consultationrefno' => $request->consultationrefno,
                'requestrefno' => $diags
            ]);

            // Detailed Comment: Prevent duplicate insertion of the same diagnostic code for this consultation
            if (StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno, 'prodcode' => $diags])->exists()) {
                continue;
            }

            $unitPrice = (float)($item->price_regular ?? $item->cost_ave ?? 0);

            StocksLedgerModel::create([
                'transactiontype' => 'CHARGES',
                'px_pin' => $patient->pxrefno ?? '',
                'px_consultcode_cn' => $request->consultationrefno,
                'patient_name' => $patient->patientname ?? $patient->patient_name ?? '',
                'prodcode' => $diags,
                'phic_reference_code' => $item->phic_reference_code ?? '',
                'item_dscr' => $item->prod_itemdscr ?? '',
                'qty' => 1,
                'cost_ave' => $unitPrice,
                'retails' => $unitPrice,
                'totalamt' => $unitPrice,
                'item_grouping' => 'DIAGNOSTIC'
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteDiagnostic(Request $request)
    {
        $req = DocRequestsModel::where([
            'consultationrefno' => $request->consultationrefno,
            'requestrefno' => $request->requestrefno
        ])->delete();

        // Detailed Comment: Also check and remove from stocks_ledger if recorded as diagnostic charge
        StocksLedgerModel::where([
            'px_consultcode_cn' => $request->consultationrefno,
            'prodcode' => $request->requestrefno
        ])->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Detailed Comment: Streams PDF printable document for Diagnostics Requests.
     * Resolves patient and doctor safely across guards, and retrieves diagnostic requests from both
     * stocks_ledger (active DIAGNOSTIC grouping or cross-referenced with stocks_listing) and legacy docrequests/diagnosticsmasterlist.
     */
    public function printDiagnostics(Request $request)
    {
        $refno = $request->consultationrefno ?: $request->query('consultationrefno');
        $patient = ConsultationModel::with('patient')
            ->where('consultationrefno', $refno)
            ->orWhere('caseno', $refno)
            ->first();

        if (!$patient) {
            $patient = (object)[
                'patientname' => 'Patient',
                'age' => '',
                'gender' => '',
                'address' => '',
                'docrefno' => null,
            ];
        } else {
            if (empty($patient->address) && $patient->patient) {
                $pMaster = $patient->patient;
                $addrParts = array_filter([$pMaster->streetadrs, $pMaster->brgy, $pMaster->muncity, $pMaster->province]);
                $patient->address = !empty($addrParts) ? implode(', ', $addrParts) : ($pMaster->address ?? '');
            }
        }

        $docrefno = null;
        if (auth()->guard('doctor')->check() && auth()->guard('doctor')->user()) {
            $docrefno = auth()->guard('doctor')->user()->docrefno;
        }
        if (!$docrefno && !empty($patient->docrefno)) {
            $docrefno = $patient->docrefno;
        }

        $doctor = $docrefno ? DoctorsProfileModel::where('docrefno', $docrefno)->first() : null;
        if (!$doctor) {
            $doctor = (object)[
                'docname' => $patient->docname ?? 'Attending Physician',
                'Licno' => '',
                'PTR' => '',
                'S2no' => '',
            ];
        }

        // Detailed Comment: Retrieve requested diagnostics from stocks_ledger and legacy docrequests
        $requests = collect();
        if ($refno) {
            $diagProdCodes = StocksListingModel::where('item_grouping', 'DIAGNOSTIC')->pluck('prodcode')->toArray();
            $ledgerDiags = StocksLedgerModel::where('px_consultcode_cn', $refno)
                ->where(function ($q) use ($diagProdCodes) {
                    $q->where('item_grouping', 'DIAGNOSTIC')
                      ->orWhereIn('prodcode', $diagProdCodes);
                })
                ->get()
                ->map(function ($item) {
                    return (object)[
                        'diagnostic_name' => $item->item_dscr,
                    ];
                });

            $pxreq = DocRequestsModel::where(['consultationrefno' => $refno])->get();
            $masterDiags = DiagnosticsMasterlistModel::whereIn('diagnosticrefno', $pxreq->pluck('requestrefno')->toArray())->get()
                ->map(function ($item) {
                    return (object)[
                        'diagnostic_name' => $item->diagnostic_name,
                    ];
                });

            $requests = $ledgerDiags->concat($masterDiags);
        }

        $profile = KayakapProfileModel::first() ?? (object)[
            'HOSP_NAME' => config('app.name', 'KayakapMD Clinic'),
            'HOSP_ADDBRGY' => ''
        ];

        $type = "diagnostics";

        try {
            return Pdf::loadView('printables.rx_print', compact('doctor', 'type', 'patient', 'profile', 'requests'))
                ->setPaper('A4', 'portrait')
                ->stream('diagnostics_' . ($refno ?: 'request') . '.pdf');
        } catch (\Throwable $e) {
            // Detailed Comment: Structured error logging if diagnostic PDF rendering fails
            Log::error('Failed to generate diagnostic PDF document', [
                'consultationrefno' => $refno,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            return response('Error generating diagnostic PDF document: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    // Charges
    /**
     * Detailed Comment: Fetches patient charges for the given consultation reference number.
     * Looks up existing records from stocks_ledger. If totalamt or cost_ave is missing/null,
     * looks up the current price from stocks_listing and formats the values to 2 decimal places,
     * ensuring no null or NaN appears in the client DataTable or total sums.
     */
    public function fetchPatientCharges(Request $request)
    {
        // Detailed Comment: Fetches all patient charges for this consultation, including
        // supplies, procedures, diagnostics, imaging, professional fees, and prescribed medicines (DRUGS AND MEDS).
        $charges = StocksLedgerModel::where('px_consultcode_cn', $request->consultationrefno)->get();

        $charges->transform(function ($item) {
            $qty = (float)($item->qty ?: 1);
            $total = ($item->totalamt !== null && $item->totalamt !== '') ? (float)$item->totalamt : null;
            $unitPrice = ($item->cost_ave !== null && $item->cost_ave !== '') ? (float)$item->cost_ave : null;

            // If unit price or total is missing or zero, defensively look up catalog price from stocks_listing
            if ($unitPrice === null || $total === null || $unitPrice <= 0 || $total <= 0) {
                $listing = StocksListingModel::where('prodcode', $item->prodcode)->first();
                if ($listing) {
                    $lookupPrice = (float)($listing->price_regular ?? $listing->cost_ave ?? 0);
                    if ($unitPrice === null || $unitPrice <= 0) {
                        $unitPrice = $lookupPrice;
                    }
                    if ($total === null || $total <= 0) {
                        $total = $unitPrice * $qty;
                    }
                } else {
                    $unitPrice = $unitPrice ?: 0;
                    $total = $total ?: 0;
                }
            }

            $item->cost_ave = number_format($unitPrice, 2, '.', '');
            $item->totalamt = number_format($total, 2, '.', '');

            return $item;
        });

        return response()->json(['charges' => $charges]);
    }

    /**
     * Detailed Comment: Saves appended patient charges to stocks_ledger.
     * Calculates the unit price and total amount from the supplied amount/quantity or stocks_listing,
     * populates px_pin and transactiontype, and persists cost_ave, retails, and totalamt.
     */
    public function saveAppendedCharges(Request $request)
    {
        $consultation = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        if (!$consultation) {
            return response()->json(['success' => false, 'message' => 'Consultation not found.'], 404);
        }

        if (empty($request->chargerefnos) || !is_array($request->chargerefnos)) {
            return response()->json(['success' => false, 'message' => 'No charges provided.'], 422);
        }

        foreach ($request->chargerefnos as $chargeData) {
            $chargeRefno = $chargeData['prodcode'] ?? null;
            $quantity = isset($chargeData['quantity']) && $chargeData['quantity'] !== '' ? (float)$chargeData['quantity'] : 1;
            $inputAmount = isset($chargeData['amount']) && $chargeData['amount'] !== '' ? (float)$chargeData['amount'] : null;

            $charge = StocksListingModel::where(['prodcode' => $chargeRefno])->first();
            if (!$charge) {
                continue;
            }

            if (
                StocksLedgerModel::where([
                    'px_consultcode_cn' => $request->consultationrefno,
                    'prodcode' => $chargeRefno,
                ])->exists()
            ) {
                continue;
            }

            if ($charge->is_inventory == true) {
                if ($charge->qty >= intval($quantity)) {
                    $charge->decrement('qty', (int)$quantity);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stocks for <strong>{$charge->prod_itemdscr}</strong>.<br>Requested: {$quantity} | Stock: {$charge->qty}"
                    ]);
                }
            }

            // Detailed Comment: Compute unit price and total amount defensively
            $unitPrice = ($inputAmount !== null && $inputAmount > 0)
                ? $inputAmount
                : (float)($charge->price_regular ?? $charge->cost_ave ?? 0);

            $totalAmt = $unitPrice * $quantity;

            StocksLedgerModel::create([
                'transactiontype' => 'CHARGES',
                'px_pin' => $consultation->pxrefno ?? '',
                'patient_name' => $consultation->patientname ?? '',
                'prodcode' => $chargeRefno,
                'px_consultcode_cn' => $request->consultationrefno,
                'item_dscr' => $charge->prod_itemdscr,
                'qty' => $quantity,
                'cost_ave' => $unitPrice,
                'retails' => $unitPrice,
                'totalamt' => $totalAmt,
                'item_grouping' => $charge->item_grouping
            ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Detailed Comment: Delete a consultation charge item from stocks_ledger defensively.
     * Supports identification by numeric ledger ID (chargeid), stock item code (prodcode),
     * or legacy charge reference (chargerefno). Sanitizes against string 'null' or empty identifiers
     * to prevent SQLSTATE[22007] integer truncation crashes.
     */
    public function deleteCharge(Request $request)
    {
        $request->validate([
            'consultationrefno' => 'required|string',
        ]);

        $query = StocksLedgerModel::where('px_consultcode_cn', $request->consultationrefno);

        $chargeId = $request->input('chargeid');
        $prodcode = $request->input('prodcode');
        $chargerefno = $request->input('chargerefno');

        // Defensive normalization: treat 'null', 'undefined', non-numeric strings as null for ID
        if ($chargeId === 'null' || $chargeId === 'undefined' || empty($chargeId) || !is_numeric($chargeId)) {
            $chargeId = null;
        }

        if ($chargeId) {
            $query->where('id', (int) $chargeId);
        } elseif (!empty($prodcode) && $prodcode !== 'null' && $prodcode !== 'undefined') {
            $query->where('prodcode', $prodcode);
        } elseif (!empty($chargerefno) && $chargerefno !== 'null' && $chargerefno !== 'undefined') {
            $query->where(function ($q) use ($chargerefno) {
                $q->where('prodcode', $chargerefno)
                  ->orWhere('phic_reference_code', $chargerefno);
            });
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No valid charge identifier provided for deletion.'
            ], 422);
        }

        $deleted = $query->delete();

        Log::info('Patient charge deletion attempted', [
            'consultationrefno' => $request->consultationrefno,
            'chargeid' => $chargeId,
            'prodcode' => $prodcode,
            'chargerefno' => $chargerefno,
            'deleted_count' => $deleted
        ]);

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Charge not found or already removed.'], 404);
    }

    public function editCharge(Request $request)
    {

    }

    public function updateCharge(Request $request)
    {
        $charges = DocChargesModel::where([
            'consultationrefno' => $request->consultationrefno,
            'pxchargerefno' => $request->pxchargerefno
        ])->update([
                    'total' => $request->charge_fee,
                    'discount' => $request->discount,
                    'net_total' => $request->charge_fee - $request->discount
                ]);

        if ($charges) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // Files
    public function fetchRadLabFiles(Request $request)
    {
        $files = ConsultationModel::select(['radiologypath', 'laboratorypath'])->where([
            'consultationrefno' => $request->consultationrefno
        ])->first();

        if ($files) {
            return response()->json(['files' => $files]);
        }

        return response()->json(['files' => null]);
    }

    public function uploadConsultationFiles(Request $request)
    {
        $consultation = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        $update = $consultation->update([
            'radiologypath' => $request->hasFile('radiology') ? $request->file('radiology')->store('radiology_results', 'private') : $consultation->radiologypath,
            'laboratorypath' => $request->hasFile('laboratory') ? $request->file('laboratory')->store('laboratory_results', 'private') : $consultation->laboratorypath
        ]);

        if ($update) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function completeConsultation(Request $request)
    {
        $record = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();

        if ($record) {
            // Detailed Comment: Transition consultation status to FOR_BILLING per approved OPD workflow plan
            // so that the patient is queued on the secretary side for billing, settlement, and document printing.
            $record->status = "FOR_BILLING";
            $record->save();

            // Detailed Comment: Log consultation status change
            Log::info('Consultation marked for billing and document handover', [
                'consultationrefno' => $request->consultationrefno,
                'status' => 'FOR_BILLING',
                'doctor' => auth()->guard('doctor')->check() ? auth()->guard('doctor')->user()->docrefno : null,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function saveImpressionsDiagnosis(Request $request)
    {
        $foradmit = $request->boolean('foradmit') || $request->input('foradmit') === '1' || $request->input('foradmit') === 1 ? 1 : 0;
        $record = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])
            ->update([
                'reasonforconsultation' => $request->reasonforconsultation,
                'impression' => $request->impressions ?: $request->impression,
                'finadiagnosis' => $request->diagnosis ?: $request->finadiagnosis,
                'foradmit' => $foradmit,
                'foradmit_instructions' => $request->foradmit_instructions ?? null,
            ]);

        if ($record) {
            // Detailed Comment: Log diagnostic notes and admission orders update
            Log::info('Consultation impressions, diagnosis, and admission orders updated', [
                'consultationrefno' => $request->consultationrefno,
                'foradmit' => $foradmit,
                'doctor' => auth()->guard('doctor')->check() ? auth()->guard('doctor')->user()->docrefno : null,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    /**
     * Detailed Comment: Retrieves HMO price or regular price for a product based on whether
     * the consultation record is linked to an active HMO code.
     */
    public function getHmoPrice(Request $request)
    {
        $consultation = ConsultationModel::where('consultationrefno', $request->consultationrefno)
            ->orWhere('caseno', $request->consultationrefno)
            ->first();

        $hasHmo = $consultation && !empty($consultation->hmocode);
        $priceType = $hasHmo ? 'price_hmo' : 'price_regular';

        $price = StocksListingModel::where('prodcode', $request->prodcode)->value($priceType);
        if ($price === null || $price === '') {
            $price = StocksListingModel::where('prodcode', $request->prodcode)->value('price_regular') ?? 0;
        }

        return response()->json(['success' => true, 'price' => number_format((float)$price, 2, '.', '')]);
    }
}
