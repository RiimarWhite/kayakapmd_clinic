<?php

namespace App\Http\Controllers;

use App\Models\HMOModel;
use App\Models\PatientMasterlist;
use App\Models\PCBModel;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use App\Models\ConsultationAnswerModel;
use App\Models\DoctorsProfileModel;
use App\Models\ConsultationModel;

class ConsultationController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'consultation_refno' => 'required|unique:pxwalkinconsultation,consultationrefno',
            'patient_id_no' => 'required|unique:pxwalkinconsultation,pxrefno',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'sex' => 'required',
            'birthdate' => 'required|date',
            'cellphone_number' => 'required',
            'email' => 'required|email',
            'address' => 'required',
            'reason_for_consultation' => 'required',
        ]);

        ConsultationModel::create([
            'consultationrefno' => $request->consultation_refno,
            'pxrefno' => $request->patient_id_no,

            'patientname' => $request->first_name,
            'pxmidname' => $request->middle_name,
            'pxlastname' => $request->last_name,
            'suffix' => $request->suffix,

            'gender' => $request->sex,
            'birthday' => $request->birthdate,
            'age' => $request->age,

            'address' => $request->address,
            'emailaddress' => $request->email,
            'mobilenumber' => $request->cellphone_number,

            'reasonforconsultation' => $request->reason_for_consultation,

            'height' => $request->height,
            'hunit' => $request->h_unit,
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,

            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
        ]);

        return back()->with('success', 'Consultation successfully saved.');
    }

    public function fetchConsultationPatients(Request $request)
    {
        $data = ConsultationModel::where(['docrefno' => $request->docrefno])->get();

        return response()->json(['patients' => $data]);
    }

    public function fetchConsultationPatientsQueue(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);

        $query = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', $request->consuldate)
            ->whereTime('consultation_date', $request->consultime)
            ->select([
                'consultationrefno',
                'pxrefno',
                'patientname',
                'queueno',
                'status'
            ]);

        $totalRecords = $query->count();
        $filteredRecords = $query->count();

        $data = $query->orderBy('queueno')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function fetchConsultationPatientsUnsched(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        $baseQuery = ConsultationModel::where('status', 'UNSCHEDULED');

        $totalRecords = $baseQuery->count();

        $query = clone $baseQuery;

        if (!empty($search)) {
            $query->where('patientname', 'LIKE', "%{$search}%");
        }

        $filteredRecords = $query->count();

        $data = $query->offset($start)
            ->limit($length)
            ->get([
                'patientname',
                'pxrefno',
                'status'
            ]);

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    public function addPatientRecord(Request $request)
    {
        $patientCode = $this->generatePatientCode();

        // Create patient record into PatientMasterlist
        $record = PatientMasterlist::create([
            'pxrefno' => $patientCode,
            'pincode' => 'PIN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT),
            'patientname' => $request->pPatientLname . ', ' . $request->pPatientFname . ' ' . $request->pPatientMname . ' ' . $request->pPatientExtname,
            'pxfirstname' => $request->pPatientFname,
            'pxmidname' => $request->pPatientMname,
            'pxlastname' => $request->pPatientLname,
            'pxsuffix' => $request->pPatientExtname,
            'gender' => $request->pPatientSex,
            'birthday' => $request->pPatientDob,
            'age' => Carbon::parse($request->pPatientDob)->age,
            'mobilenumber' => $request->pPatientMobileNo,
            'emailaddress' => $request->email,
            'address' => $request->address
        ]);

        $consultation = ConsultationModel::create([
            'pxrefno' => $patientCode,
            'pincode' => 'PIN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT),
            'caseno' => $this->generateCaseCode(),
            'patientname' => $record->patientname,
            'pxfirstname' => $record->pxfirstname,
            'pxmidname' => $record->pxmidname,
            'pxlastname' => $record->pxlastname,
            'pxsuffix' => $record->pxsuffix,
            'gender' => $record->gender == 'MALE' ? 'M' : 'F',
            'birthday' => $record->birthday,
            'age' => $record->age,
            'mobilenumber' => $record->mobilenumber,
            'emailaddress' => $record->emailaddress,
            'status' => 'UNSCHEDULED'
        ]);

        if ($record && $consultation) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function updatePatientRecord(Request $request)
    {
        // 1. Get Doctor details
        $doctor = DoctorsProfileModel::where('doccode', $request->doccode)->first();

        if (!$doctor) {
            return response()->json(['success' => false, 'message' => 'Doctor profile not found'], 404);
        }

        // 2. Calculate Age
        $age = 0;
        if ($request->birthday) {
            $age = Carbon::parse($request->birthday)->age;
        }

        // 3. Find the LATEST record for this patient using ID instead of created_at
        $latestRecord = ConsultationModel::where('pincode', $request->pincode)
            ->orderBy('id', 'desc')
            ->first();

        // Data to be saved (shared between update and create)
        $data = [
            'docrefno' => $doctor->docrefno,
            'doccoaOPD' => $doctor->coaOPD,
            'doccode' => $request->doccode,
            'memPin' => $request->memPin,
            'patientname' => $request->patientname,
            'pxmidname' => $request->pxmidname,
            'pxlastname' => $request->pxlastname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->gender,
            'birthday' => $request->birthday,
            'age' => $age,
            'memFname' => $request->memFname,
            'memMname' => $request->memMname,
            'memLname' => $request->memLname,
            'memExtname' => $request->memExtname,
            'memDob' => $request->memDob,
            'mobilenumber' => $request->mobilenumber,
            'emailaddress' => $request->emailaddress,
            'address' => $request->address,
        ];

        // 4. Logic Check: Update if status is UNSCHEDULED, otherwise Create New
        if ($latestRecord && $latestRecord->status === 'UNSCHEDULED') {
            // Update existing latest record
            $latestRecord->update($data);
            $message = 'Existing unscheduled consultation updated successfully';
        } else {
            // Add fields specific to a new entry
            $data['pincode'] = $request->pincode;
            $data['casecode'] = $this->generateCaseCode();
            $data['status'] = 'UNSCHEDULED';

            ConsultationModel::create($data);
            $message = 'New consultation entry created successfully';
        }

        return response()->json(['success' => true, 'message' => $message]);
    }

    // private function generatePxReference()
    // {
    //     return 'PX' . Carbon::now()->format('mdYHis');
    // }

    public function fetchPxMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');

        $baseQuery = DB::table('pxmasterlist')
            ->join('pxwalkinconsultation as c', 'pxmasterlist.pxrefno', '=', 'c.pxrefno');

        $recordsTotal = $baseQuery->count();

        if (!empty($search)) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('pxmasterlist.patientname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxfirstname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxmidname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pxlastname', 'like', "%{$search}%")
                ->orWhere('pxmasterlist.pincode', 'like', "%{$search}%");
            });
        }

        $recordsFiltered = $baseQuery->count();

        $data = $baseQuery
            ->select([
                'pxmasterlist.pxrefno',
                'pxmasterlist.pincode',
                'pxmasterlist.patientname',
                'pxmasterlist.pxfirstname',
                'pxmasterlist.pxmidname',
                'pxmasterlist.pxlastname',
                'pxmasterlist.pxsuffix',
                'c.status',
                'pxmasterlist.mobilenumber',
                'pxmasterlist.emailaddress',
                'c.consultationrefno',
                'c.consultation_date',
                'c.docrefno',
                'c.photo_path'
            ])
            ->where([
                'docrefno' => $request->docrefno
            ])
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

    public function fetchConsultation(Request $request)
    {
        // Find the most recent record for this patient by sorting by ID descending
        $consultation = ConsultationModel::where(['pxrefno' => $request->pxrefno])
            ->orderBy('id', 'desc')
            ->first();

        if (!$consultation) {
            return response()->json(['success' => false, 'message' => 'No record found'], 404);
        }

        if ($consultation->photo_path) {
            $filename = basename($consultation->photo_path);
            $consultation->photo_path = url('/patient/photo/' . $filename);
        }

        $answers = ConsultationAnswerModel::where(['pxconsultationrefno' => $consultation->consultationrefno])->get();

        if ($answers) {
            return response()->json([
                'success' => true,
                'patient' => $consultation,
                'answers' => $answers
            ]);
        }

        return response()->json([
            'success' => false,
            'patient' => null,
            'answers' => null
        ]);
    }

    public function fetchPatientPhoto($filename)
    {
        if ($filename === "blank_photo.png")
            return response()->file(public_path('images/blank_photo.png'));

        $path = storage_path('app/private/patient_photo/' . $filename);

        if (!file_exists($path)) {
            abort(404);
        }

        return response()->file($path);
    }

    public function saveConsultation(Request $request)
    {
        $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first(); // Get assigned doctor
        $secretary = auth()->guard('secretary')->user(); // Get authenticated secretary

        $queueCount = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', Carbon::parse($request->sched_date))
            ->whereTime('consultation_date', Carbon::parse($request->sched_time))
            ->count();

        $queueno = str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

        $fullName = array_filter([
            $request->pxfname,
            $request->pxmname,
            $request->pxlname,
            $request->pxsuffix
        ]);

        $patientName = implode(' ', $fullName);

        // Prepare base data
        // Merge the array below with the $memberData to be passed as one
        $consultationData = [
            'pxrefno' => $request->pxrefno ?? $this->generatePatientCode(),
            'docrefno' => $request->docrefno,
            'pincode' => $request->pincode,
            'casecode' => $this->generateCaseCode(),
            'doccoaOPD' => $doctor->coaOPD ?? '',
            'doccode' => $doctor->doccode ?? '',
            'patientname' => $patientName,
            'pxfirstname' => $request->pxfname,
            'pxmidname' => $request->pxmname,
            'pxlastname' => $request->pxlname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->pxsex,
            'birthday' => $request->pxbday,
            'age' => $request->pxage,
            'mobilenumber' => $request->pxcellnumber,
            'emailaddress' => $request->pxemail,
            'address' => $request->pxaddress,

            'reasonforconsultation' => $request->reason_for_consultation,
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'height' => $request->height,
            'hunit' => $request->h_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,

            'hmocode' => $request->hmo_input,
            'hmoname' => $request->hmo_name,

            'secrefno' => $secretary->secrefno ?? auth()->user()->adminrefno,
            'consultation_date' => Carbon::parse($request->sched_date . ' ' . $request->sched_time),
            'recordedby' => $secretary->secusername ?? auth()->user()->username,
            'recordeddate' => now(),

            'photo_path' => $request->hasFile('patient_photo') ? $request->file('patient_photo')->store('patient_photo', 'private') : public_path('images/blank_photo.png'),
            'radiologypath' => $request->hasFile('radiology_file') ? $request->file('radiology_file')->store('radiology_results', 'private') : null,
            'laboratorypath' => $request->hasFile('laboratory_file') ? $request->file('laboratory_file')->store('laboratory_results', 'private') : null,

            'status' => 'PENDING',
            'queueno' => $queueno
        ];

        $consultation = ConsultationModel::create($consultationData);

        $answers = $request->input('answer', []);
        foreach ($answers as $questionRef => $answer) {
            ConsultationAnswerModel::create([
                'pxconsultationrefno' => $consultation->consultationrefno,
                'questionrefno' => $questionRef,
                'answer' => $answer,
                'transactedby' => $secretary->secusername ?? auth()->user()->adminrefno,
                'transacteddate' => now(),
                'walkinconsuanswerrefno' => now()->format('mdYHis') . 'QA'
            ]);
        }

        if ($consultation) {
            return response()->json(['success' => true]);
        } else {
            return response()->json(['success' => false]);
        }
    }

    // Update consultation details
    public function updateConsultation(Request $request)
    {
        $doctor = DoctorsProfileModel::where(['docrefno' => $request->docrefno])->first();
        $secretary = auth()->guard('secretary')->user();

        $record = ConsultationModel::where(['consultationrefno' => $request->pxconsultationrefno])->first();

        $queueCount = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', Carbon::parse($request->sched_date)->startOfDay())
            ->whereTime('consultation_date', Carbon::parse($request->sched_time)->endOfDay())
            ->count();
        $queueno = str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT);

        $fullName = array_filter([
            $request->pxfname,
            $request->pxmname,
            $request->pxlname,
            $request->pxsuffix
        ]);

        $patientName = implode(' ', $fullName);

        $consultationData = [
            'docrefno' => $request->docrefno,
            'pincode' => $request->pincode,
            'casecode' => $this->generateCaseCode(),
            'doccoaOPD' => $doctor->coaOPD ?? '',
            'doccode' => $doctor->doccode ?? '',
            'patientname' => $patientName,
            'pxmidname' => $request->pxmname,
            'pxlastname' => $request->pxlname,
            'pxsuffix' => $request->pxsuffix,
            'gender' => $request->pxsex,
            'birthday' => $request->pxbday,
            'age' => $request->pxage,
            'mobilenumber' => $request->pxcellnumber,
            'emailaddress' => $request->pxemail,
            'address' => $request->pxaddress,

            'reasonforconsultation' => $request->reason_for_consultation ?? '',
            'weight' => $request->weight,
            'wunit' => $request->w_unit,
            'height' => $request->height,
            'hunit' => $request->h_unit,
            'temp' => $request->temperature,
            'tempunit' => $request->t_unit,
            'respiratoryrate' => $request->respiratory_rate,
            'pulserate' => $request->pulse_rate,
            'bpnumerator' => $request->bp_numerator,
            'bpdenominator' => $request->bp_denominator,

            'hmocode' => $request->hmo_input,
            'hmoname' => $request->hmo_name,

            'secrefno' => $secretary->secrefno ?? auth()->user()->adminrefno,
            'consultation_date' => Carbon::parse($request->sched_date . ' ' . $request->sched_time),
            'recordedby' => $secretary->secusername ?? auth()->user()->username,
            'recordeddate' => now(),

            'photo_path' => $request->hasFile('patient_photo') ? $request->file('patient_photo')->store('patient_photo', 'private') : $record->photo_path,
            'radiologypath' => $request->hasFile('radiologypath') ? $request->file('radiologypath')->store('radiology_results', 'private') : $record->radiologypath,
            'laboratorypath' => $request->hasFile('laboratorypath') ? $request->file('laboratorypath')->store('laboratory_results', 'private') : $record->laboratorypath,

            'status' => 'PENDING',
            'queueno' => $queueno
        ];

        $update = $record->update($consultationData); // Update record

        $answers = $request->input('answer', []);
        foreach ($answers as $questionRef => $answer) {
            $answerModel = ConsultationAnswerModel::updateOrCreate([
                'pxconsultationrefno' => $record->consultationrefno,
                'questionrefno' => $questionRef
            ], [
                'answer' => $answer,
                'transactedby' => $secretary->secusername ?? auth()->user()->username,
                'transacteddate' => now(),
            ]);

            if ($answerModel->wasRecentlyCreated) {
                $answerModel->update([
                    'walkinconsuanswerrefno' => now()->format('mdYHis') . 'QA'
                ]);
            }
        }

        if ($update) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function updateQueueStatus(Request $request)
    {
        $request->validate([
            'casecode' => 'required',
            'status' => 'required|in:WAITING,IN_CONSULTATION,COMPLETED,CANCELLED,NO_SHOW,ON_HOLD',
        ]);

        $updated = ConsultationModel::where('casecode', $request->casecode)->update([
            'status' => $request->status,
        ]);

        if ($updated) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Record not found.'], 404);
    }

    public function fetchLatestPatientDetails(Request $request)
    {
        $data = ConsultationModel::where('pincode', $request->pincode)->orderBy('id', 'DESC')->first();

        if ($data) {
            return response()->json(['success' => true, 'data' => $data]);
        }

        return response()->json(['success' => false, 'message' => 'Not found'], 404);
    }

    private function generateReferenceCode()
    {
        // Set timezone to Philippines
        $now = Carbon::now('Asia/Manila');

        // Format: CRNW + MMDDYYYY + HHMMSS
        $refCode = 'CRNW' . $now->format('mdYHis');

        return $refCode;
    }

    private function generatePatientCode()
    {
        // Set timezone to Philippines
        $now = Carbon::now('Asia/Manila');

        // Format: PTNW + MMDDYYYY + HHMMSS
        $patientCode = 'PTN' . $now->format('YHis');

        return $patientCode;
    }

    // PhilHealth Profile
    public function fetchHCIProfile()
    {
        $profile = PCBModel::first();
        if ($profile) {
            return $profile;
        }

        return false;
    }

    public function insertHCIProfile(Request $request)
    {
        $profile = $this->fetchHCIProfile();
        if (!$profile) {
            return response()->json(['success' => false]);
        }

        $profile->update([
            'pUsername' => $request->pusername,
            'pPassword' => $request->ppassword,
        ]);
    }

    private function generatePinCode($prevPincode = null)
    {
        // If a previous pincode exists, reuse it; otherwise, generate a new one
        if ($prevPincode) {
            return $prevPincode;
        }

        $profile = $this->fetchHCIProfile();
        $accre = '-';
        if ($profile) {
            $accre = $profile->hciaccreno;
        }

        $now = Carbon::now('Asia/Manila')->format('Ym');

        // Format: T YYYYMM (e.g., T202603)
        return 'T' . $accre . $now;
    }

    private function generateCaseCode()
    {
        // $profile = $this->fetchHCIProfile();
        // $accre = '-';
        // if ($profile) {
        //     $accre = $profile->hciaccreno;
        // }

        // $now = Carbon::now('Asia/Manila')->format('Ym');

        // Format: E YYYYMM (ex. E202603)
        return 'CN' . Carbon::now()->year . '-' . str_pad(PatientMasterlist::count(), 5, '0', STR_PAD_LEFT);
    }

    public function reschedulePatient(Request $request)
    {
        $queueCount = ConsultationModel::where(['docrefno' => $request->docrefno])
            ->whereDate('consultation_date', $request->date)
            ->whereTime('consultation_date', $request->time)
            ->count();

        // dd($queueCount);

        $resched = ConsultationModel::where(['casecode' => $request->consultationrefno])
            ->update([
                'consultation_date' => Carbon::parse($request->date . ' ' . $request->time),
                'queueno' => str_pad($queueCount + 1, 3, '0', STR_PAD_LEFT)
            ]);

        if ($resched) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function refreshQueue(Request $request)
    {
        $consultations = ConsultationModel::whereDate('consultation_date', $request->date)
            ->whereTime('consultation_date', $request->time)
            ->orderBy('queueno', 'ASC')
            ->get();

        $queueno = 1;

        foreach ($consultations as $consultation) {
            ConsultationModel::where('id', $consultation->id)->update([
                'queueno' => str_pad($queueno, 3, '0', STR_PAD_LEFT)
            ]);
            $queueno++;
        }

        return response()->json(['success' => true]);
    }

    public function fetchHMO()
    {
        $orgs = HMOModel::where(['dw_clientcode' => session()->get('clientcode')])->get();

        if ($orgs) {
            return response()->json(['success' => true, 'hmo' => $orgs]);
        }

        return response()->json(['success' => false, 'hmo' => $orgs]);
    }
}
