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
use App\Models\DoctorsProfileModel;
use App\Models\MedicineModel;
use App\Models\ScheduleModel;
use App\Models\DoctorModel;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
    public function index()
    {
        return redirect()->route('doctor.dashboard');
    }

    public function dashboardPage()
    {
        $doctor = auth()->guard('doctor')->user();

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

    public function fetchDoctorUser()
    {
        $user = DoctorsProfileModel::where('docrefno', auth()->guard('doctor')->user()->docrefno)->first();

        return response()->json(['success' => true, 'user' => $user]);
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

        $baseQuery = DB::table('pxmasterlist')
            ->join('pxwalkinconsultation as c', 'pxmasterlist.pxrefno', '=', 'c.pxrefno');

        // TOTAL (no filter)
        $recordsTotal = $baseQuery->count();

        // APPLY SEARCH
        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('pxmasterlist.patientname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxfirstname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxmidname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxlastname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pincode', 'like', "%{$search}%");
            });
        }

        // FILTERED COUNT
        $recordsFiltered = $baseQuery->count();

        // DATA
        $data = $baseQuery
            ->select([
                'pxmasterlist.pxrefno',
                'pxmasterlist.pincode',
                'pxmasterlist.patientname',
                'pxmasterlist.pxfirstname',
                'pxmasterlist.pxmidname',
                'pxmasterlist.pxlastname',
                'pxmasterlist.pxsuffix',
                'c.consultationrefno',
                'c.consultation_date',
                'c.docrefno',
                'c.photo_path'
            ])
            // ->where(['docrefno' => auth()->guard('doctor')->user()->docrefno ?? $request->docrefno])
            ->when(auth()->guard('doctor')->check(), function ($q) {
                $q->where(['docrefno' => auth()->guard('doctor')->user()->docrefno]);
            })
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
            ->select([
                'consultations.docrefno',
                'consultations.doccoaOPD',
                'consultations.doccode',
                'consultations.pincode',
                'consultations.casecode',
                'consultations.pinno',
                'consultations.caseno',
                'consultations.isMember',
                'consultations.memPin',
                'consultations.patientname',
                'consultations.pxmidname',
                'consultations.pxlastname',
                'consultations.pxsuffix',
                'consultations.gender',
                'consultations.birthday',
                'consultations.age',
                'consultations.mobilenumber',
                'consultations.emailaddress',
                'consultations.address',
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

    public function fetchDoctorSchedules()
    {
        $schedules = ScheduleModel::where(['docrefno' => auth()->guard('doctor')->user()->docrefno])->orderBy('day', 'ASC')->get();

        return response()->json(['schedules' => $schedules]);
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

    public function addMedicine(Request $request)
    {
        $consultation = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        $medicine = StocksListingModel::where(['item_grouping' => 'DRUGS AND MEDS', 'prodcode' => $request->prodcode])->first();

        $record = StocksLedgerModel::create([
            'px_pin' => $consultation->pxrefno,
            'px_consultcode_cn' => $request->consultationrefno,
            'patient_name' => $consultation->patientname,
            'prodcode' => $request->prodcode,
            'phic_reference_code' => $medicine->phic_reference_code,
            'item_dscr' => $medicine->prod_itemdscr,
            'qty' => $request->qty,
            'item_grouping' => 'DRUGS AND MEDS'
        ]);

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteMedicine(Request $request)
    {
        $med = StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno, 'prodcode' => $request->prodcode])->first();
        $med->delete();

        return response()->json(['success' => true]);
    }

    public function saveRx(Request $request)
    {
        $result = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->update([
            'instructions' => $request->pxinstructions
        ]);

        return response()->json(['success' => true, 'instructions' => $request->pxinstructions]);
    }

    public function printPDF(Request $request)
    {
        $doctor = DoctorsProfileModel::where('docrefno', auth()->guard('doctor')->user()->docrefno)->first();
        $patient = ConsultationModel::where('consultationrefno', $request->consultationrefno)->first();
        $medicines = DoctorMedicinesModel::where('consultationrefno', $request->consultationrefno)->get();
        $profile = KayakapProfileModel::first();
        $type = $request->type;

        return Pdf::loadView('printables.rx_print', compact('doctor', 'type', 'patient', 'profile', 'medicines'))->setPaper('A4', 'portrait')->stream('test.pdf');
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

    public function saveDiagnosticRequest(Request $request)
    {
        $patient = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();

        foreach ($request->diagnostics as $diags) {
            $item = StocksListingModel::where(['prodcode' => $diags])->first();

            StocksLedgerModel::create([
                'px_consultcode_cn' => $request->consultationrefno,
                'patient_name' => $patient->patient_name,
                'prodcode' => $diags,
                'phic_reference_code' => $item->phic_reference_code,
                'item_dscr' => $item->prod_itemdscr,
                'qty' => 1
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

        if ($req) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function printDiagnostics(Request $request)
    {
        $doctor = DoctorsProfileModel::where('docrefno', auth()->guard('doctor')->user()->docrefno)->first();
        $patient = ConsultationModel::where('consultationrefno', $request->consultationrefno)->first();
        $pxreq = DocRequestsModel::where(['consultationrefno' => $request->consultationrefno])->get();
        $requests = DiagnosticsMasterlistModel::whereIn('diagnosticrefno', $pxreq->pluck('requestrefno')->toArray())->get();
        $profile = KayakapProfileModel::first();
        $type = "diagnostics";

        return Pdf::loadView('printables.rx_print', compact('doctor', 'type', 'patient', 'profile', 'requests'))->setPaper('A4', 'portrait')->stream('test.pdf');
    }

    // Charges
    public function fetchPatientCharges(Request $request)
    {
        // $charges = DocChargesModel::where(['consultationrefno' => $request->consultationrefno])->get();
        $charges = StocksLedgerModel::select(['id', 'prodcode', 'item_dscr', 'qty', 'cost_ave', 'totalamt'])->where(['px_consultcode_cn' => $request->consultationrefno])->get();

        return response()->json(['charges' => $charges]);
    }

    public function saveAppendedCharges(Request $request)
    {
        $consultation = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first();
        $doctor = DoctorsProfileModel::where(['docrefno' => $consultation->docrefno])->first();

        foreach ($request->chargerefnos as $chargeData) {
            // $chargeData is now an object with refno, discount, amount
            $chargeRefno = $chargeData['prodcode'] ?? null;
            $quantity = $chargeData['quantity'] ?? 0;

            $charge = StocksListingModel::select(['prod_itemdscr', 'item_grouping', 'qty'])->where(['prodcode' => $chargeRefno])->first();

            if (!$charge)
                continue;

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
                    $charge->decrement('qty', $quantity);
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => "Insufficient stocks for <strong>{$charge->prod_itemdscr}</strong>.<br>Requested: {$quantity} | Stock: {$charge->qty}"
                    ]);
                }
            }

            StocksLedgerModel::create([
                'patient_name' => $consultation->patientname,
                'prodcode' => $chargeRefno,
                'px_consultcode_cn' => $request->consultationrefno,
                'item_dscr' => $charge->prod_itemdscr,
                'qty' => $quantity,
                'item_grouping' => $charge->item_grouping
            ]);
        }

        return response()->json(['success' => true]);
    }

    public function deleteCharge(Request $request)
    {
        $deleted = StocksLedgerModel::where([
            'px_consultcode_cn' => $request->consultationrefno,
            'id' => $request->chargeid
        ])->delete();

        if ($deleted) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
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

        $record->status = "COMPLETED";
        $record->save();

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function saveImpressionsDiagnosis(Request $request)
    {
        $record = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])
            ->update([
                'reasonforconsultation' => $request->reasonforconsultation,
                'impression' => $request->impressions,
                'finadiagnosis' => $request->diagnosis
            ]);

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function getHmoPrice(Request $request)
    {
        $patient = ConsultationModel::where(['consultationrefno' => $request->consultationrefno])->first()->value('hmocode');
        $price_type = ($patient != null) ? 'price_regular' : 'price_hmo';

        $price = StocksListingModel::where(['prodcode' => $request->prodcode])
            ->value($price_type);

        if ($price != null) {
            return response()->json(['success' => true, 'price' => $price]);
        }

        return response()->json(['success' => false]);
    }
}
