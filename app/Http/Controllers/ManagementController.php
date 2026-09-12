<?php

namespace App\Http\Controllers;

use App\Models\ConsultationModel;
use App\Models\EnlistmentModel;
use App\Models\HMOModel;
use App\Models\PatientMasterlist;
use App\Models\PCBModel;
use App\Models\PhilHealthDiagModel;
use App\Models\PhilHealthMedsModel;
use App\Models\SOAPModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use App\Models\StocksLedger;
use App\Models\XmlTransModel;
use App\Services\Admin\AdminService;
use Date;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\ChargesCategoryModel;
use App\Models\ChargesModel;
use App\Models\DiagnosticsCategoryModel;
use App\Models\DiagnosticsMasterlistModel;
use App\Models\DocChargesModel;
use App\Models\DoctorModel;
use App\Models\DoctorsProfileModel;
use App\Models\MedicineMasterlistModel;
use App\Models\MedicineModel;
use App\Models\KayakapProfileModel;
use App\Models\SecretaryDoctorsModel;
use App\Models\SecretaryModel;
use App\Models\SettlementsModel;
use Log;

class ManagementController extends Controller
{
    protected $service;

    public function __construct(AdminService $service)
    {
        $this->service = $service;
    }

    public function index()
    {
        return redirect()->route('admin.dashboard');
    }

    // --- Page-serving methods ---

    public function dashboardPage()
    {
        $data = $this->getDashboardData();
        return view('pages.admin.dashboard', ['data' => $data]);
    }

    public function profilePage()
    {
        return view('pages.admin.profile');
    }

    public function secretariesPage()
    {
        return view('pages.admin.users.secretaries');
    }

    public function doctorsPage()
    {
        return view('pages.admin.users.doctors');
    }

    public function secretaryPanelPage()
    {
        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])
            ->get();

        $secretaries = SecretaryModel::select(['secrefno', 'seclname'])
            ->get();

        return view('pages.secretary.queue', [
            'doctors' => $doctors,
            'secretaries' => $secretaries
        ]);
    }

    public function hmoPanelPage()
    {
        return view('pages.admin.hmo');
    }

    public function diagnosticRequestsPage()
    {
        return view('pages.admin.consultations.requests');
    }

    public function billingsPage()
    {
        return view('pages.admin.consultations.billing');
    }

    public function settlementsPage()
    {
        return view('pages.admin.consultations.settlements');
    }

    public function stocksManagementPage()
    {
        return view('pages.admin.stocks.management');
    }

    public function stocksLedgerPage()
    {
        return view('pages.admin.stocks.ledger');
    }

    public function inventoryPage()
    {
        return view('pages.admin.stocks.inventory');
    }




    // public function diagnosticCategoryPage()
    // {
    //     return view('pages.admin.diagnostics.category');
    // }

    // public function medicinesPage()
    // {
    //     return view('pages.admin.medicines');
    // }

    // public function chargesMasterlistPage()
    // {
    //     return view('pages.admin.charges.masterlist');
    // }

    // public function chargesCategoryPage()
    // {
    //     return view('pages.admin.charges.category');
    // }

    public function philhealthOverviewPage()
    {
        return view('pages.admin.philhealth.overview');
    }

    public function philhealthManagementPage()
    {
        return view('pages.admin.philhealth.management');
    }


    public function philhealthUploadingPage()
    {
        return view('pages.admin.philhealth.uploading');
    }

    public function philhealthToolsPage()
    {
        return view('pages.admin.philhealth.tools');
    }

    public function philhealthConsultationsPage()
    {
        return view('pages.admin.philhealth.consultations');
    }

    public function philhealthMasterlistPage()
    {
        return view('pages.admin.philhealth.masterlist');
    }

    public function chargeCatgPage()
    {
        return view('pages.admin.utilities.chargecat');
    }

    public function diagCatgPage()
    {
        return view('pages.admin.utilities.diagcat');
    }

    public function reportsPage()
    {
        return view('pages.admin.philhealth.reports');
    }

    public function getDashboardData(Request $request = null)
    {
        $data = [];
        $data['patient_records'] = PatientMasterlist::all()->count();
        $data['consultations'] = ConsultationModel::all()->count();
        $data['today_consultations'] = ConsultationModel::whereDate('consultation_date', now())->count();

        return $data;
    }

    // Stocks related
    public function fetchStocks(Request $request)
    {
        $stocks = $this->service->getStockList($request->all());

        return response()->json($stocks);
    }

    public function fetchDrugRef(Request $request)
    {
        $data = $this->service->getDrugRefs($request->input('term'));

        return response()->json($data);
    }

    public function fetchDiagRef(Request $request)
    {
        $data = $this->service->getDiagsRefs($request->input('term'));

        return response()->json($data);
    }

    public function fetchStocksLedger(Request $request)
    {
        $query = StocksLedgerModel::query();

        $start = $request->input('start');
        $limit = $request->input('length');
        $search = $request->input('search.value');

        if ($search) {
            $query->select(['patient_name', 'item_dscr', 'dispensed', 'updated', 'dispensed_status'])
                ->where(function ($q) use ($search) {
                    $q->where('patient_name', 'LIKE', "%{$search}%")
                        ->orWhere('item_dscr', 'LIKE', "%{$search}%")
                        ->orWhere('dispensed_status', 'LIKE', "%{$search}%")
                        ->orWhere('updated', 'LIKE', "%{$search}%");
                });
        }

        $recordsFiltered = $query->count();
        $recordsTotal = StocksLedgerModel::count();

        $records = $query->offset($start)->limit($limit)->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function fetchStockItem(Request $request)
    {
        $item = StocksListingModel::where(['prodcode' => $request->prodcode])->first();

        if ($item) {
            return response()->json(['success' => true, 'item' => $item]);
        }

        return response()->json(['success' => false]);
    }

    public function saveStockItem(Request $request)
    {
        $item = [
            'item_grouping' => $request->item_group,
            'prodcode' => 'PROD' . now()->format('mdYHis'),
            'prod_itemdscr' => $request->item_dscr,
            'price_regular' => $request->price_regular,
            'price_phic' => $request->price_phic,
            'price_hmo' => $request->price_hmo,
            'price_others' => $request->price_others,
            'is_inventory' => $request->is_inventory == "on" ? true : false,
            'qty' => $request->item_quantity
        ];

        $add_array = [];

        if ($request->item_group == "DRUGS AND MEDS") {
            $add_array = [
                'phic_reference_code' => $request->ref_code,
                'drug_generic' => $request->drug_generic,
                'drug_brand' => $request->drug_brand,
                'drug_dosage' => $request->drug_dosage,
                'drug_group' => $request->drug_group
            ];
        } else if ($request->item_group == "DIAGNOSTIC") {
            $add_array = [
                'phic_reference_code' => $request->ref_code
            ];
        }

        if (!empty($add_array)) {
            $item = array_merge($item, $add_array);
        }

        $result = StocksListingModel::create($item);

        if ($result) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function editStockItem(Request $request)
    {
        $data = [
            'prod_itemdscr' => $request->eitem_dscr,
            'item_grouping' => $request->eitem_group,
            'price_regular' => $request->eprice_regular,
            'price_phic' => $request->eprice_phic,
            'price_hmo' => $request->eprice_hmo,
            'price_others' => $request->eprice_others
        ];

        $add_array = [];

        if ($request->eitem_group == "DRUGS AND MEDS") {
            $add_array = [
                'phic_reference_code' => $request->eref_code,
                'drug_generic' => $request->edrug_generic,
                'drug_brand' => $request->edrug_brand,
                'drug_dosage' => $request->edrug_dosage,
                'drug_grouping' => $request->edrug_group
            ];
        } else if ($request->eitem_group == "DIAGNOSTIC") {
            $add_array = [
                'phic_reference_code' => $request->eref_code
            ];
        }

        if (!empty($add_array)) {
            $data = array_merge($data, $add_array);
        }

        $item = StocksListingModel::where(['prodcode' => $request->prodcode])->update($data);

        if ($item) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteStockItem(Request $request)
    {
        $item = StocksListingModel::where(['prodcode' => $request->prodcode])->delete();

        if ($item) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function fetchInventory(Request $request)
    {
        $query = StocksListingModel::query();

        $start = $request->input('start');
        $length = $request->input('length');
        $search = $request->input('search.value');

        if (!empty($search)) {
            $query->where('prod_itemdscr', 'LIKE', "%{$search}%");
        }

        $recordsTotal = StocksListingModel::count();

        $data = $query->select(['prodcode', 'prod_itemdscr', 'qty', 'item_grouping', 'is_inventory'])
            ->where(['is_inventory' => true])
            ->when($request->filter !== 'ALL', function ($q) use ($request) {
                return $q->where('item_grouping', $request->filter);
            })
            ->offset($start)
            ->limit($length)
            ->get();

        $recordsFiltered = $query->count();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsFiltered' => $recordsFiltered,
            'recordsTotal' => $recordsTotal,
            'data' => $data
        ]);
    }

    public function fetchDrugDetails(Request $request)
    {
        $generic = $request->input('generic');
        $refcode = $request->input('phic_reference_code');

        $query = DB::table('dw_lib_medicine as m')
            ->leftJoin('dw_lib_meds_generic as g', 'm.GEN_CODE', '=', 'g.gen_code')
            ->leftJoin('dw_lib_meds_strength as st', 'm.STRENGTH_CODE', '=', 'st.strength_code')
            ->select(
                'm.drug_code as drug_code',
                'm.drug_desc as drug_description',
                'g.gen_desc as generic_description',
                'st.strength_desc as strength_description'
            );

        if ($generic) {
            $result = $query
                ->where('g.gen_desc', 'LIKE', "%{$generic}%")
                ->first();
        } else if ($refcode) {
            $result = $query
                ->where('m.drug_code', $refcode)
                ->first();
        }

        if ($result) {
            return response()->json(['success' => true, 'drug' => $result]);
        }

        return response()->json(['success' => false]);
    }

    public function getAssigned(Request $request)
    {
        $assigned_doctors = SecretaryDoctorsModel::where([
            'secrefno' => $request->secrefno
        ])->pluck('docrefno');

        $doctors = DoctorsProfileModel::select(['docrefno', 'docname'])
            ->whereIn('docrefno', $assigned_doctors)
            ->get();

        return response()->json($doctors);
    }

    public function fetchDoctors()
    {
        $doctors = DoctorsProfileModel::all();

        return response()->json(['doctors' => $doctors]);
    }

    public function fetchDoctor(Request $request)
    {
        $doctor = DoctorsProfileModel::where('docrefno', $request->docrefno)->first();

        return response()->json(['success' => true, 'doctor' => $doctor]);
    }

    public function addDoctor(Request $request)
    {
        $request->validate([
            'pass' => 'required|string|min:5',
        ]);

        $docrefno = Date::now()->format('mdYHis') . 'MD';
        $profile = DoctorsProfileModel::create([
            'doccode' => 'PFMD',
            'docrefno' => $docrefno,
            'docfname' => $request->docfname,
            'docmname' => $request->docmname,
            'doclname' => $request->doclname,
            'suffix' => $request->suffix,
            'titlename' => $request->titlename,
            'docname' => trim($request->docfname . ' ' . ($request->docmname ?? '') . ' ' . $request->doclname . ' ' . ($request->suffix ?? '')),
            'emailadd' => $request->emailadd,
            'cellno' => $request->cellno,
            'adrs' => $request->adrs,
            'proftype' => $request->proftype,
            'expertise' => $request->expertise,
            'tin' => $request->tin,
            'Licno' => $request->licno,
            'licnoexpiry' => $request->licnoexpiry,
            'phicno' => $request->phicno,
            'phicexpiry' => $request->phicexpiry,
            'status' => ($request->status == 'ACTIVE' ? true : false),
            'statusreason' => $request->statusreason,
        ]);

        DoctorModel::create([
            'docrefno' => $docrefno,
            'docfname' => $request->docfname,
            'docmname' => $request->docmname,
            'doclname' => $request->doclname,
            'suffix' => $request->suffix,
            'titlename' => $request->titlename,
            'username' => $request->doclname,
            'pass' => bcrypt($request->pass),
            'eadd' => $request->emailadd,
            'tin' => $request->tin,
            'address' => $request->adrs,
            'slcode' => 'SLCODE',
            'taxpercent' => $request->tax,
            'bankacct' => 'BANKACCT',
            'status' => $request->status,
            'expertise' => $request->expertise,
            'proftype' => $request->proftype,
            'doctype' => 0,
            'docmgmt' => 0,
            'consultationfee' => 0
        ]);

        $profile->where('docrefno', $docrefno)->update(['doccode' => 'PFMD' . str_pad($profile->id, 3, '0', STR_PAD_LEFT)]);

        return response()->json(['success' => true]);
    }

    public function editDoctor(Request $request)
    {
        $doctorprofile = DoctorsProfileModel::where('docrefno', $request->docrefno)
            ->update([
                'docfname' => $request->edocfname,
                'docmname' => $request->edocmname,
                'doclname' => $request->edoclname,
                'suffix' => $request->esuffix,
                'titlename' => $request->etitlename,
                'docname' => trim($request->edocfname . ' ' . ($request->edocmname ?? '') . ' ' . $request->edoclname . ' ' . ($request->esuffix ?? '')),
                'emailadd' => $request->eemailadd,
                'cellno' => $request->ecellno,
                'adrs' => $request->eadrs,
                'proftype' => $request->eproftype,
                'expertise' => $request->eexpertise,
                'tin' => $request->etin,
                'Licno' => $request->elicno,
                'licnoexpiry' => $request->elicnoexpiry,
                'phicno' => $request->ephicno,
                'phicexpiry' => $request->ephicexpiry,
                'status' => ($request->estatus == 'ACTIVE' ? true : false),
                'statusreason' => $request->estatusreason,
            ]);

        $existingPass = DoctorModel::where('docrefno', $request->edocrefno)->value('pass');
        $doctor = DoctorModel::where(['docrefno' => $request->docrefno])
            ->update([
                'docfname' => $request->edocfname,
                'docmname' => $request->edocmname,
                'doclname' => $request->edoclname,
                'suffix' => $request->esuffix,
                'titlename' => $request->etitlename,
                'username' => $request->edoclname,
                'pass' => $request->epass != '' ? bcrypt($request->epass) : $existingPass,
                'eadd' => $request->eemailadd,
                'tin' => $request->etin,
                'address' => $request->eadrs,
                'slcode' => 'SLCODE',
                'taxpercent' => $request->etax,
                'bankacct' => 'BANKACCT',
                'status' => $request->estatus,
                'expertise' => $request->eexpertise,
                'proftype' => $request->eproftype,
                'doctype' => 0,
                'docmgmt' => 0,
                'consultationfee' => 0
            ]);

        if ($doctor && $doctorprofile)
            return response()->json(['success' => true]);

        return response()->json(['success' => false]);
    }

    public function deleteDoctor(Request $request)
    {
        $request->validate([
            'docrefno' => 'required|string'
        ]);

        DoctorsProfileModel::where('docrefno', $request->docrefno)->delete();
        DoctorModel::where('docrefno', $request->docrefno)->delete();

        return response()->json(['success' => true]);
    }

    public function fetchSecretaries()
    {
        $secretaries = SecretaryModel::all();

        return response()->json(['secretaries' => $secretaries]);
    }

    public function addSecretary(Request $request)
    {
        $request->validate([
            'secfname' => 'required|string|max:255',
            'secmname' => 'nullable|string|max:255',
            'seclname' => 'required|string|max:255',
            'secsuffix' => 'nullable|string|max:10',
            'secgender' => 'required|in:male,female',
            'secpassword' => 'required|string|min:5',
            'secbday' => 'nullable|date',
            'seccontactno' => 'nullable|string|max:11',
            'secemail' => 'nullable|email|unique:secretaryrights,secemail',
            'secadrs' => 'nullable|string|max:255'
        ]);

        SecretaryModel::create([
            'secrefno' => Date::now()->format('mdYHis') . 'TASK',
            'secidno' => Date::now()->format('Y') . "-" . (SecretaryModel::count() + 1),
            'secfname' => $request->secfname,
            'secmname' => $request->secmname,
            'seclname' => $request->seclname,
            'secsuffix' => $request->secsuffix,
            'secgender' => strtoupper($request->secgender),
            'secpassword' => bcrypt($request->secpassword),
            'secbday' => $request->secbday,
            'seccontactno' => $request->seccontactno,
            'secemail' => $request->secemail,
            'secadrs' => $request->secadrs
        ]);

        return response()->json(['success' => true]);
    }

    public function editSecretary(Request $request)
    {
        $user = SecretaryModel::where('secrefno', auth()->guard('secretary')->user()->secrefno)
            ->update([
                'seccontactno' => $request->sec_contact,
                'secemail' => $request->sec_email
            ]);

        if ($user) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteSecretary(Request $request)
    {
        $request->validate([
            'secrefno' => 'required|string|exists:secretaryrights,secrefno'
        ]);

        $secretary = SecretaryModel::where('secrefno', $request->secrefno)->first();
        if ($secretary) {
            $secretary->delete();

            return response()->json([
                'success' => true
            ]);
        }
    }

    public function fetchSecretaryDoctors(Request $request)
    {
        $secretary = SecretaryModel::where('secrefno', $request->secrefno)->first();

        $assigned = SecretaryDoctorsModel::where('secrefno', $request->secrefno)->get();
        $assigned_doctors = [];
        foreach ($assigned as $assign) {
            $assigned_doctors[] = DoctorsProfileModel::select(['docrefno', 'docname'])->where('docrefno', $assign->docrefno)->first();
        }

        $available = SecretaryDoctorsModel::where('secrefno', $request->secrefno)->pluck('docrefno');
        $avail_doctors = DoctorsProfileModel::whereNotIn('docrefno', $available)->get();

        return response()->json([
            'success' => true,
            'name' => ($secretary->seclname . ', ' . $secretary->secfname . ' ' . $secretary->secmname . ' ' . $secretary->secsuffix),
            'assigned_doctors' => $assigned_doctors,
            'avail_doctors' => $avail_doctors
        ]);
    }

    public function saveAppendedDoctors(Request $request)
    {
        foreach ($request->doctors as $doctor) {
            if (SecretaryDoctorsModel::where(['docrefno' => $doctor, 'secrefno' => $request->secrefno])->first() == null) {
                SecretaryDoctorsModel::create([
                    'docrefno' => $doctor,
                    'secrefno' => $request->secrefno,
                    'recordedby' => '',
                    'recordeddate' => Date::now(),
                    'active' => true
                ]);
            }
        }

        return response()->json(['success' => true, 'doctors' => $request->doctors]);
    }

    public function fetchProfile(Request $request)
    {
        $profile = KayakapProfileModel::firstOrFail();

        return response()->json(['profile' => $profile]);
    }

    public function fetchAddressData(Request $request)
    {
        $region = DB::table('lib_region')
            ->select(['REGION_CODE', 'REGION_DESC', 'PRO_CODE'])
            ->get();

        $province = DB::table('lib_province')
            ->select(['PROCODE', 'PROV_NAME', 'PROVINCE'])
            ->orderBy('PROV_NAME', 'ASC')
            ->get();

        $municipalities = DB::table('lib_municipality')
            ->select(['PROCODE', 'PROVINCE', 'MUNICIPALITY', 'MUN_NAME'])
            ->orderBy('MUN_NAME', 'ASC')
            ->get();

        $barangay = DB::table('lugar_barangay')
            ->select(['MUNICIPALITY', 'PROVINCE', 'BARANGAY', 'BRGY_NAME'])
            ->orderBy("BRGY_NAME", 'ASC')
            ->get();

        $data['regions'] = $region;
        $data['provinces'] = $province;
        $data['municipalities'] = $municipalities;
        $data['barangay'] = $barangay;

        return response()->json(['data' => $data]);
    }

    public function loadCompanyProfile()
    {
        $profile = KayakapProfileModel::select([
            'HOSP_NAME',
            'HOSP_ADDBRGY',
            'EMAIL_ADD',
            'TEL_NO'
        ])->first();

        $company = PCBModel::select([
            'userid',
            'passwd',
            'hciaccreno'
        ])
            ->first();

        if ($profile && $company) {
            return response()->json(['success' => true, 'profile' => $profile, 'company' => $company]);
        }

        return response()->json(['success' => false]);
    }

    public function updateProfile(Request $request)
    {
        $data = [
            'HOSP_NAME' => $request->comp_name,
            'TEL_NO' => $request->comp_tel,
            'EMAIL_ADD' => $request->comp_email
            // 'company_name' => $request->comp_name,
            // 'company_brgy' => $request->comp_brgy,
            // 'company_mun' => $request->comp_mun,
            // 'company_prov' => $request->comp_prov,
            // 'company_region' => $request->comp_region,
            // 'company_zipcode' => $request->comp_zipcode,
            // 'company_email' => $request->comp_email,
            // 'company_mobilenumber' => $request->comp_contact,
            // 'company_telephone' => $request->comp_tel
        ];

        if ($request->hasFile('comp_logo')) {
            $file = $request->file('comp_logo');

            $request->validate([
                'comp_logo' => 'image|mimes:jpg,jpeg,png|max:2048'
            ]);

            $file->move(public_path('images'), 'company_logo.png');
        }

        $profile = KayakapProfileModel::first();
        if (!$profile) {
            return response()->json(['success' => false]);
        }

        $profile->fill($data)->save();

        return response()->json(['success' => true]);
    }

    public function savePhilHealthCreds(Request $request)
    {
        $profile = KayakapProfileModel::first()->update([
            'hci_username' => $request->ph_username,
            'hci_password' => $request->ph_password,
            'hci_accre_no' => $request->ph_accreno
        ]);

        $pcb = PCBModel::first();
        $pcb->update([
            'userid' => $request->ph_username,
            'passwd' => $request->ph_password,
            'hciaccreno' => $request->ph_accreno
        ]);

        if ($profile && $pcb) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // Medicine-related
    public function fetchMedicineMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);

        $medicines = MedicineMasterlistModel::orderBy('medicine_name', 'ASC')
            ->offset($start)
            ->limit($length)
            ->get();

        $recordsTotal = $medicines->count();
        $recordsFiltered = $medicines->count();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'medicines' => $medicines
        ]);
    }

    public function fetchMedicineReference(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value');

        $query = MedicineModel::query();
        $recordsTotal = $query->count();

        if (!empty($search)) {
            $query->where('drug_dscr', 'like', "%{$search}%")
                ->orWhere('drug_code', 'like', "%{$search}%");
        }

        $recordsFiltered = $query->count();

        $medicines = $query->orderBy('drug_dscr')
            ->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $medicines
        ]);
    }

    public function addMedicine(Request $request)
    {
        $record = MedicineMasterlistModel::create([
            'medicine_refno' => 'MED' . Date::now()->format('mdYHis'),
            'medicine_name' => $request->med_name,
            'philhealth_refno' => $request->ph_id
        ]);

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteMedicine(Request $request)
    {
        $record = MedicineMasterlistModel::where([
            'medicine_refno' => $request->medicinerefno
        ])->delete();

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // Diagnostics-related
    public function fetchDiagnosticCategory()
    {
        $categories = DiagnosticsCategoryModel::all();

        return response()->json(['categories' => $categories]);
    }

    public function saveDiagnosticCategory(Request $request)
    {
        $catg = DiagnosticsCategoryModel::create([
            'category_refno' => Date::now()->format('mdYHis') . 'CCATG',
            'category_name' => $request->categoryname
        ]);

        if ($catg) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteDiagnosticCategory(Request $request)
    {
        $catg = DiagnosticsCategoryModel::where(['category_refno' => $request->refno])->delete();

        if ($catg) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function fetchDiagnostic(Request $request)
    {
        $result = DiagnosticsMasterlistModel::with('category')->get();
        $categories = DiagnosticsCategoryModel::all();

        return response()->json(['results' => $result, 'categories' => $categories]);
    }

    public function createDiagnostic(Request $request)
    {
        $result = DiagnosticsMasterlistModel::create([
            'diagnosticrefno' => Date::now()->format('mdYHis') . 'DIAG',
            'diagnostic_name' => $request->name,
            'diagnostic_catg' => $request->category
        ]);

        if ($result) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function editMedicine(Request $request)
    {
        $record = MedicineMasterlistModel::where(['medicine_refno' => $request->medrefno])
            ->update([
                'medicine_name' => $request->med_name,
                'philhealth_refno' => $request->ph_id
            ]);

        if ($record) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteDiagnostic(Request $request)
    {
        $result = DiagnosticsMasterlistModel::where([
            'diagnosticrefno' => $request->refno
        ])->delete();

        if ($result) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    // Charges-related
    public function fetchChargeCategories(Request $request)
    {
        $categories = ChargesCategoryModel::all();

        return response()->json(['categories' => $categories]);
    }

    public function fetchChargeCategoriesSc(Request $request)
    {
        $categories = ChargesCategoryModel::all();
        $pxcharges = DocChargesModel::where(['consultationrefno' => $request->consultationrefno])->get();

        return response()->json(['categories' => $categories, 'pxcharges' => $pxcharges]);
    }

    public function saveChargeCategory(Request $request)
    {
        $category = ChargesCategoryModel::create([
            'categoryrefno' => now()->format('mdYHis') . 'CHGCAT',
            'categoryname' => $request->categoryname
        ]);

        if ($category) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function editChargeCategory(Request $request)
    {
        $category = ChargesCategoryModel::where('categoryrefno', $request->ecategoryrefno)->first();
        if ($category) {
            $category->update([
                'categoryname' => $request->categoryname
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteChargeCategory(Request $request)
    {
        $category = ChargesCategoryModel::where('categoryrefno', $request->categoryrefno)->first();
        if ($category) {
            $category->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function fetchSpecificCharges(Request $request)
    {
        $charges = ChargesModel::where('chargerefno', $request->chargerefno)->get();

        return response()->json(['charges' => $charges]);
    }

    public function fetchAllCharges(Request $request)
    {
        $appendedCharges = StocksLedgerModel::where(['px_consultcode_cn' => $request->consultationrefno])->pluck('prodcode')->toArray();
        $charges = StocksListingModel::when($request->category, function ($q) use ($request) {
            if ($request->category == 'ALL') return;

            $q->select(['prod_itemdscr', 'prodcode', 'item_grouping', 'price_regular'])->where(['item_grouping' => $request->category]);
        })->whereNotIn('prodcode', $appendedCharges)->get();

        // // already-appended charge refnos
        // $appendedCharges = DocChargesModel::where('consultationrefno', $request->consultationrefno)
        //     ->pluck('servicerefno')
        //     ->toArray(); // important

        // $charges = ChargesModel::when($request->category, function ($q) use ($request) {
        //     $q->where('charge_category', $request->category);
        // })
        //     ->whereNotIn('chargerefno', $appendedCharges)
        //     ->get();

        return response()->json(['charges' => $charges]);
    }

    public function fetchChargePrice(Request $request)
    {
        $prices = StocksListingModel::select(['price_regular', 'price_phic', 'price_hmo', 'price_others'])
            ->where(['prodcode' => $request->prodcode])
            ->first();

        return response()->json([
            'prices' => $prices
        ]);
    }

    public function fetchCharges()
    {
        $charges = ChargesModel::join('charges_category', 'charges_masterlist.charge_category', '=', 'charges_category.categoryrefno')
            ->select('charges_masterlist.*', 'charges_category.categoryname')
            ->get();

        return response()->json(['charges' => $charges]);
    }

    public function saveCharge(Request $request)
    {
        $charge = ChargesModel::create([
            'chargerefno' => now()->format('mdYHis') . 'CHRG',
            'charge_name' => $request->charge_name,
            'charge_category' => $request->charge_catg,
            'charge_amount' => $request->charge_amount,
        ]);

        if ($charge) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function editCharge(Request $request)
    {
        $charge = ChargesModel::where('chargerefno', $request->chargerefno)->first();
        if ($charge) {
            $charge->update([
                'charge_name' => $request->charge_name,
                'charge_category' => $request->charge_category,
                'charge_amount' => $request->charge_amount,
            ]);

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function deleteCharge(Request $request)
    {
        $charge = ChargesModel::where('chargerefno', $request->chargerefno)->first();
        if ($charge) {
            $charge->delete();

            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false]);
    }

    public function phDiagnosticLib(Request $request)
    {
        $totalCount = PhilHealthDiagModel::count();
        $totalFiltered = $totalCount;

        $limit = $request->input('length');
        $start = $request->input('start');
        $search = $request->input('search.value');

        $query = PhilHealthDiagModel::select(['diagnostic_id', 'diagnostic_desc']);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('diagnostic_id', 'LIKE', "%{$search}%")
                    ->orWhere('diagnostic_desc', 'LIKE', "%{$search}%");
            });

            $totalFiltered = $query->count();
        }

        $data = $query
            ->offset($start)
            ->limit($limit)
            ->orderByRaw('CAST(diagnostic_id AS UNSIGNED) ASC')
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    public function phMedicineLib(Request $request)
    {
        $columns = [
            'm.DRUG_CODE',
            'm.DRUG_DESC',
            'g.gen_desc',
            's.salt_desc',
            'f.form_desc',
            'st.strength_desc',
            'u.unit_desc'
        ];

        $totalCount = PhilHealthMedsModel::from('dw_lib_medicine as m')->count();
        $totalFiltered = $totalCount;

        $limit = $request->input('length');
        $start = $request->input('start');
        $search = $request->input('search.value');

        $query = PhilHealthMedsModel::from('dw_lib_medicine as m')
            ->leftJoin('dw_lib_meds_generic as g', 'm.GEN_CODE', '=', 'g.gen_code')
            ->leftJoin('dw_lib_meds_salt as s', 'm.SALT_CODE', '=', 's.salt_code')
            ->leftJoin('dw_lib_meds_form as f', 'm.FORM_CODE', '=', 'f.form_code')
            ->leftJoin('dw_lib_meds_strength as st', 'm.STRENGTH_CODE', '=', 'st.strength_code')
            ->leftJoin('dw_lib_meds_unit as u', 'm.UNIT_CODE', '=', 'u.unit_code')
            ->select($columns);

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('m.DRUG_CODE', 'LIKE', "%{$search}%")
                    ->orWhere('m.DRUG_DESC', 'LIKE', "%{$search}%")
                    ->orWhere('g.gen_desc', 'LIKE', "%{$search}%")
                    ->orWhere('s.salt_desc', 'LIKE', "%{$search}%")
                    ->orWhere('f.form_desc', 'LIKE', "%{$search}%")
                    ->orWhere('st.strength_desc', 'LIKE', "%{$search}%")
                    ->orWhere('u.unit_desc', 'LIKE', "%{$search}%");
            });

            $totalFiltered = $query->count();
        }

        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir');

        $orderableColumns = [
            'm.DRUG_CODE',
            'm.DRUG_DESC',
            'g.gen_desc',
            's.salt_desc',
            'f.form_desc',
            'st.strength_desc',
            'u.unit_desc'
        ];

        if (isset($orderColumnIndex)) {
            $query->orderBy($orderableColumns[$orderColumnIndex], $orderDir);
        }

        $data = $query
            ->offset($start)
            ->limit($limit)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalCount,
            'recordsFiltered' => $totalFiltered,
            'data' => $data,
        ]);
    }

    // PhilHealth-related
    public function fetchPatientsConsultation(Request $request)
    {
        $query = ConsultationModel::query();

        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('patientname', 'LIKE', "%{$search}%")
                    ->orWhere('pxmidname', 'LIKE', "%{$search}%")
                    ->orWhere('pxlastname', 'LIKE', "%{$search}%")
                    ->orWhere('pincode', 'LIKE', "%{$search}%")
                    ->orWhere('casecode', 'LIKE', "%{$search}%");
            });
        }

        if ($request->filled('filter_start') && $request->filled('filter_end')) {
            $query->whereBetween(
                'consultation_date',
                [
                    Carbon::parse($request->filter_start)->startOfDay(),
                    Carbon::parse($request->filter_end)->endOfDay()
                ]
            );
        }

        $recordsFiltered = $query->count();
        $recordsTotal = ConsultationModel::count();

        $records = $query->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function exportToMasterlist(Request $request)
    {
        $consultations = ConsultationModel::all();
        $masterlist = new PatientMasterlist();

        $count = 0;
        foreach ($consultations as $record) {
            if ($masterlist->where(['pincode' => $record->pincode])->first()) {
                continue;
            }

            $masterlist->create([
                'pxrefno' => $record->pxrefno,
                'pincode' => $record->pincode,
                'casecode' => $record->casecode,
                'patientname' => trim($record->patientname . ' ' . $record->pxmidname . ' ' . $record->pxlastname . ' ' . $record->pxsuffix),
                'pxlastname' => $record->pxlastname,
                'pxfirstname' => $record->patientname,
                'pxmidname' => $record->pxmidname,
                'pxsuffix' => $record->pxsuffix,
                'gender' => $record->gender,
                'birthday' => $record->birthday,
                'age' => $record->age,
                'mobilenumber' => $record->mobilenumber,
                'emailaddress' => $record->emailaddress,
                'address' => $record->address,
                'last_consultation' => $record->consultationdate,
                'last_docrefno' => $record->docrefno,
                'status' => $record->status,
                'recordedby' => $record->recordedby,
                'recordeddate' => $record->recordeddate,
                'updatedby' => $record->updatedby,
                'updated' => $record->updated,
            ]);

            $count++;
        }

        return response()->json(['success' => true, 'count' => $count]);
    }


    public function fetchPatientMasterlist(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value');

        $query = ConsultationModel::query();

        if (!empty($search)) {
            $query->where('patientname', 'like', "%{$search}%");
        }

        $recordsFiltered = $query->count();
        $recordsTotal = ConsultationModel::count();

        $records = $query->select(['pincode', 'patientname', 'consultation_date'])
            ->offset($start)
            ->limit($length)
            ->orderBy('consultation_date', 'desc')
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function fetchSoapData(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $search = $request->input('search.value');

        $query = SOAPModel::with(['enlistment' => function ($q) {
            $q->select('dCaseNo', 'dPatientFname', 'dPatientMname', 'dPatientLname');
        }])
            ->select('en_CaseNo', 'px_pin', 'dSoapDate', 'report_code', 'report_code2')
            ->where('status', 'CONSULTED');

        $recordsTotal = SOAPModel::where('status', 'CONSULTED')->count();

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->whereHas('enlistment', function ($q) use ($search) {
                    $q->where('dPatientFname', 'LIKE', "%{$search}%")
                        ->orWhere('dPatientMname', 'LIKE', "%{$search}%")
                        ->orWhere('dPatientLname', 'LIKE', "%{$search}%");
                });
            });
        }

        $recordsFiltered = $query->count();

        $orderMap = [
            '2' => 'dPatientLname',
            '3' => 'dSoapDate'
        ];

        $columnIndex = $request->input('order.0.column');
        $direction = $request->input('order.0.dir', 'asc');

        if (isset($orderMap[$columnIndex])) {
            $column = $orderMap[$columnIndex];

            if ($columnIndex == '2') {
                $query->orderBy(
                    EnlistmentModel::select('dPatientLname')
                        ->whereColumn('dCaseNo', 'en_CaseNo')
                        ->limit(1),
                    $direction
                );
            } else {
                $query->orderBy($column, $direction);
            }
        }

        $records = $query->offset($start)
            ->limit($length)
            ->get();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }

    public function generatePatientCodes()
    {
        return response()->json(['success' => true]);
    }

    // Reports
    public function fetchTransactions(Request $request)
    {
        $query = SettlementsModel::query();

        if ($request->filled('filter_start') && $request->filled('filter_end')) {
            $query->whereBetween('created_at', [
                Carbon::parse($request->filter_start)->startOfDay(),
                Carbon::parse($request->filter_end)->endOfDay()
            ]);
        }

        $recordsFiltered = $query->count();
        $start = $request->input('start', 0);
        $length = $request->input('length', 25);
        $records = $query
            ->orderBy('id', 'ASC')
            ->offset($start)
            ->limit($length)
            ->get();

        $recordsTotal = SettlementsModel::count();

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $records
        ]);
    }
}
