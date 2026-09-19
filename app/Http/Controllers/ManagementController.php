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
use App\Models\AdminModel;
use Illuminate\Support\Facades\Hash;
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

    // Detailed Comment: Page-serving method for Secretaries/Admin Users management screen
    public function secretariesAdminsPage()
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

    public function getDashboardData(?Request $request = null)
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
        if ($doctor) {
            $rights = DoctorModel::where('docrefno', $request->docrefno)->first();
            if ($rights) {
                $doctor->username = $rights->username;
                $doctor->taxpercent = $rights->taxpercent;
                $doctor->bankacct = $rights->bankacct;
                $doctor->slcode = $rights->slcode;
            }
            $doctor->source_table = 'doctors & doctorsrights';
        }

        return response()->json(['success' => true, 'doctor' => $doctor]);
    }

    public function addDoctor(Request $request)
    {
        $request->validate([
            'docfname' => 'required|string',
            'doclname' => 'required|string',
            'pass' => 'required|string|min:5',
        ]);

        $docrefno = Date::now()->format('mdYHis') . 'MD';
        $username = $request->filled('username') ? strtolower(trim($request->username)) : strtolower(trim($request->doclname));

        // Detailed Comment: Create doctor profile in the primary 'doctors' table with all columns
        $profile = DoctorsProfileModel::create([
            'doccode' => 'PFMD',
            'docrefno' => $docrefno,
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
            'pfrate' => $request->pfrate,
            'rodrate' => $request->rodrate,
            'coacode' => $request->coacode,
            'accountno' => $request->accountno,
            'tax' => $request->tax,
            'vatable' => $request->boolean('vatable'),
            'vatrate' => $request->vatrate,
            'VAT' => $request->vatrate ?? $request->VAT,
            'autoAddVAT' => $request->boolean('autoAddVAT'),
            'issuehospOR' => $request->boolean('issuehospOR'),
            'quevisible' => $request->boolean('quevisible', true),
            'allowtextresult' => $request->boolean('allowtextresult'),
            'allowdocsystem' => $request->boolean('allowdocsystem'),
            'disabletext' => $request->boolean('disabletext'),
            'status' => ($request->status == 'ACTIVE' || $request->status == '1' ? true : false),
            'statusreason' => $request->statusreason,
            'otherinfo' => $request->otherinfo,
            'biodata' => $request->biodata
        ]);

        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));

        // Detailed Comment: Create credentials in 'doctorsrights' table with username defaulting to last name
        DoctorModel::create([
            'dw_clientcode' => $facilityClientCode,
            'docrefno' => $docrefno,
            'docfname' => $request->docfname,
            'docmname' => $request->docmname,
            'doclname' => $request->doclname,
            'suffix' => $request->suffix,
            'titlename' => $request->titlename,
            'username' => $username,
            'pass' => $request->pass, // Model casts 'pass' => 'hashed'
            'eadd' => $request->emailadd,
            'mnumber' => $request->cellno,
            'tin' => $request->tin,
            'address' => $request->adrs,
            'slcode' => $request->coacode ?? 'SLCODE',
            'taxpercent' => $request->tax,
            'bankacct' => $request->accountno ?? 'BANKACCT',
            'status' => $request->status,
            'expertise' => $request->expertise,
            'proftype' => $request->proftype,
            'doctype' => 0,
            'docmgmt' => 0,
            'consultationfee' => $request->pfrate ?? 0
        ]);

        $profile->where('docrefno', $docrefno)->update(['doccode' => 'PFMD' . str_pad($profile->id, 3, '0', STR_PAD_LEFT)]);

        // Detailed Comment: Structured logging for doctor account registration
        Log::info('Doctor account registered by admin', [
            'docrefno' => $docrefno,
            'username' => $username,
            'docname' => $request->docfname . ' ' . $request->doclname,
            'clientcode' => $facilityClientCode
        ]);

        return response()->json(['success' => true]);
    }

    public function editDoctor(Request $request)
    {
        $username = $request->filled('eusername') ? strtolower(trim($request->eusername)) : strtolower(trim($request->edoclname));

        // Detailed Comment: Update all columns on doctors table
        $doctorprofile = DoctorsProfileModel::where('docrefno', $request->docrefno)
            ->update([
                'docfname' => $request->edocfname,
                'docmname' => $request->edocmname,
                'doclname' => $request->edoclname,
                'suffix' => $request->esuffix,
                'titlename' => $request->etitlename,
                'docfirst' => $request->edocfirst ?? $request->edocfname,
                'docname' => trim($request->edocfname . ' ' . ($request->edocmname ?? '') . ' ' . $request->edoclname . ' ' . ($request->esuffix ?? '')),
                'emailadd' => $request->eemailadd,
                'cellno' => $request->ecellno,
                'adrs' => $request->eadrs,
                'proftype' => $request->eproftype,
                'expertise' => $request->eexpertise,
                'department' => $request->edepartment,
                'profgroup' => $request->eprofgroup,
                'catg' => $request->ecatg,
                'station' => $request->estation,
                'groupname' => $request->egroupname,
                'tin' => $request->etin,
                'Licno' => $request->elicno,
                'licnoexpiry' => $request->elicnoexpiry,
                'phicno' => $request->ephicno,
                'phicexpiry' => $request->ephicexpiry,
                'phicname' => $request->ephicname,
                'phicenable' => $request->boolean('ephicenable'),
                'phicrate' => $request->ephicrate,
                'S2no' => $request->es2no,
                'PTR' => $request->eptr,
                'clinicroom' => $request->eclinicroom,
                'clinichours' => $request->eclinichours,
                'pfrate' => $request->epfrate,
                'rodrate' => $request->erodrate,
                'coacode' => $request->ecoacode,
                'accountno' => $request->eaccountno,
                'tax' => $request->etax,
                'vatable' => $request->boolean('evatable'),
                'vatrate' => $request->evatrate,
                'VAT' => $request->evatrate ?? $request->eVAT,
                'autoAddVAT' => $request->boolean('eautoAddVAT'),
                'issuehospOR' => $request->boolean('eissuehospOR'),
                'quevisible' => $request->boolean('equevisible'),
                'allowtextresult' => $request->boolean('eallowtextresult'),
                'allowdocsystem' => $request->boolean('eallowdocsystem'),
                'disabletext' => $request->boolean('edisabletext'),
                'status' => ($request->estatus == 'ACTIVE' || $request->estatus == '1' ? true : false),
                'statusreason' => $request->estatusreason,
                'otherinfo' => $request->eotherinfo,
                'biodata' => $request->ebiodata
            ]);

        $existingPass = DoctorModel::where('docrefno', $request->edocrefno ?? $request->docrefno)->value('pass');
        $doctor = DoctorModel::where(['docrefno' => $request->docrefno])
            ->update([
                'docfname' => $request->edocfname,
                'docmname' => $request->edocmname,
                'doclname' => $request->edoclname,
                'suffix' => $request->esuffix,
                'titlename' => $request->etitlename,
                'username' => $username,
                'pass' => $request->filled('epass') ? bcrypt($request->epass) : $existingPass,
                'eadd' => $request->eemailadd,
                'mnumber' => $request->ecellno,
                'tin' => $request->etin,
                'address' => $request->eadrs,
                'slcode' => $request->ecoacode ?? 'SLCODE',
                'taxpercent' => $request->etax,
                'bankacct' => $request->eaccountno ?? 'BANKACCT',
                'status' => $request->estatus,
                'expertise' => $request->eexpertise,
                'proftype' => $request->eproftype,
                'doctype' => 0,
                'docmgmt' => 0,
                'consultationfee' => $request->epfrate ?? 0
            ]);

        Log::info('Doctor profile updated by admin', ['docrefno' => $request->docrefno, 'username' => $username]);

        if ($doctor !== false && $doctorprofile !== false) {
            return response()->json(['success' => true]);
        }

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

    /**
     * Fetch secretaries and administrators for the unified Secretaries/Admin Users management table.
     * Supports server-side filtering via 'account_type' or 'type' parameter ('Secretary', 'Admin', or '' / 'all').
     * Maps both account types with consistent attributes while preserving backward-compatible field names.
     */
    public function fetchSecretaries(Request $request)
    {
        $accountType = trim((string) $request->input('account_type', $request->input('type', '')));

        $secretaries = collect();
        $admins = collect();

        // Detailed Comment: Query secretaries if account_type is empty, 'all', or explicitly 'Secretary'
        if (empty($accountType) || strcasecmp($accountType, 'all') === 0 || strcasecmp($accountType, 'secretary') === 0) {
            $secretaries = SecretaryModel::all()->map(function ($sec) {
                return [
                    'id' => $sec->id,
                    'refno' => $sec->secrefno,
                    'secrefno' => $sec->secrefno,
                    'idno' => $sec->secidno,
                    'account_type' => 'Secretary',
                    'username' => $sec->username ?: strtolower($sec->seclname),
                    'secfname' => $sec->secfname,
                    'secmname' => $sec->secmname,
                    'seclname' => $sec->seclname,
                    'secsuffix' => $sec->secsuffix,
                    'secgender' => $sec->secgender,
                    'secbday' => $sec->secbday,
                    'seccontactno' => $sec->seccontactno,
                    'secemail' => $sec->secemail,
                    'secadrs' => $sec->secadrs,
                    'clientcode' => $sec->clientcode,
                    'verified' => $sec->verified,
                    'source_table' => 'secretaryrights',
                ];
            });
        }

        // Detailed Comment: Query administrators if account_type is empty, 'all', or explicitly 'Admin'
        if (empty($accountType) || strcasecmp($accountType, 'all') === 0 || strcasecmp($accountType, 'admin') === 0) {
            $admins = AdminModel::all()->map(function ($admin) {
                $fname = $admin->adminfname ?: 'Admin';
                $lname = $admin->adminlname ?: ($admin->username ?: 'User');
                $refno = $admin->adminrefno ?: (string)$admin->id;
                return [
                    'id' => $admin->id,
                    'refno' => $refno,
                    'secrefno' => $refno, // Fallback for table action button triggers
                    'adminrefno' => $refno,
                    'idno' => $admin->adminidno ?: ('ADM-' . $admin->id),
                    'account_type' => 'Admin',
                    'username' => $admin->username ?: strtolower($lname),
                    'secfname' => $fname,
                    'secmname' => $admin->adminmname ?: '',
                    'seclname' => $lname,
                    'secsuffix' => '',
                    'secgender' => 'N/A',
                    'secbday' => null,
                    'seccontactno' => $admin->admincontactno ?: '',
                    'secemail' => $admin->adminemail ?: $admin->useremail,
                    'secadrs' => '',
                    'clientcode' => $admin->clientcode,
                    'verified' => true,
                    'source_table' => 'adminrights',
                ];
            });
        }

        $merged = $secretaries->concat($admins)->values();

        return response()->json([
            'success' => true,
            'secretaries' => $merged,
            'data' => $merged
        ]);
    }

    /**
     * Fetch secretary details for modal editing.
     */
    public function fetchSecretaryDetails(Request $request)
    {
        $request->validate([
            'secrefno' => 'required|string'
        ]);

        $secretary = SecretaryModel::where('secrefno', $request->secrefno)->first();
        if (!$secretary) {
            return response()->json(['success' => false, 'message' => 'Secretary not found'], 404);
        }

        return response()->json([
            'success' => true,
            'secretary' => $secretary
        ]);
    }

    /**
     * Register a new secretary account with custom or default username (defaults to lowercase lastname).
     */
    public function addSecretary(Request $request)
    {
        $request->validate([
            'secfname' => 'required|string|max:255',
            'secmname' => 'nullable|string|max:255',
            'seclname' => 'required|string|max:255',
            'secsuffix' => 'nullable|string|max:10',
            'username' => 'nullable|string|max:50|unique:secretaryrights,username',
            'secgender' => 'required|in:male,female,MALE,FEMALE',
            'secpassword' => 'required|string|min:5',
            'secbday' => 'nullable|date',
            'seccontactno' => 'nullable|string|max:11',
            'secemail' => 'nullable|email|unique:secretaryrights,secemail',
            'secadrs' => 'nullable|string|max:255'
        ]);

        $secidno = Date::now()->format('Y') . "-" . (SecretaryModel::count() + 1);
        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));
        $username = $request->filled('username') ? strtolower(trim($request->username)) : strtolower(trim($request->seclname));

        $secretary = SecretaryModel::create([
            'secrefno' => Date::now()->format('mdYHis') . 'TASK',
            'secidno' => $secidno,
            'username' => $username,
            'secfname' => $request->secfname,
            'secmname' => $request->secmname,
            'seclname' => $request->seclname,
            'secsuffix' => $request->secsuffix,
            'secgender' => strtoupper($request->secgender),
            'secpassword' => $request->secpassword, // Model casts 'secpassword' => 'hashed'
            'secbday' => $request->secbday,
            'seccontactno' => $request->seccontactno,
            'secemail' => $request->secemail,
            'secadrs' => $request->secadrs,
            'clientcode' => $facilityClientCode,
            'recordeddate' => now(),
            'verified' => true
        ]);

        // Detailed Comment: Structured logging for secretary account registration
        Log::info('Secretary account registered by admin', [
            'username' => $username,
            'secidno' => $secidno,
            'secname' => $request->secfname . ' ' . $request->seclname,
            'clientcode' => $facilityClientCode
        ]);

        return response()->json(['success' => true, 'secretary' => $secretary]);
    }

    /**
     * Update secretary profile or credentials.
     * Supports both admin updating any secretary by secrefno and secretary self-service update.
     */
    public function editSecretary(Request $request)
    {
        $secrefno = $request->input('secrefno');

        // Admin-driven update by secrefno
        if ($secrefno) {
            $secretary = SecretaryModel::where('secrefno', $secrefno)->first();
            if (!$secretary) {
                return response()->json(['success' => false, 'message' => 'Secretary not found'], 404);
            }

            $updateData = [];
            if ($request->has('secfname')) $updateData['secfname'] = $request->secfname;
            if ($request->has('secmname')) $updateData['secmname'] = $request->secmname;
            if ($request->has('seclname')) $updateData['seclname'] = $request->seclname;
            if ($request->has('secsuffix')) $updateData['secsuffix'] = $request->secsuffix;
            if ($request->has('secgender')) $updateData['secgender'] = strtoupper($request->secgender);
            if ($request->has('secbday')) $updateData['secbday'] = $request->secbday;
            if ($request->has('seccontactno')) $updateData['seccontactno'] = $request->seccontactno;
            if ($request->has('secemail')) $updateData['secemail'] = $request->secemail;
            if ($request->has('secadrs')) $updateData['secadrs'] = $request->secadrs;

            if ($request->filled('username')) {
                $updateData['username'] = strtolower(trim($request->username));
            } elseif ($request->has('seclname') && empty($secretary->username)) {
                $updateData['username'] = strtolower(trim($request->seclname));
            }

            if ($request->filled('secpassword')) {
                $updateData['secpassword'] = $request->secpassword;
            }

            $secretary->update($updateData);

            Log::info('Secretary profile updated by admin', [
                'secrefno' => $secrefno,
                'username' => $secretary->username
            ]);

            return response()->json(['success' => true]);
        }

        // Secretary self-service profile update
        if (auth()->guard('secretary')->check()) {
            $user = SecretaryModel::where('secrefno', auth()->guard('secretary')->user()->secrefno)
                ->update([
                    'seccontactno' => $request->sec_contact ?? $request->seccontactno,
                    'secemail' => $request->sec_email ?? $request->secemail
                ]);

            return response()->json(['success' => (bool)$user]);
        }

        return response()->json(['success' => false, 'message' => 'Unauthorized or missing secrefno'], 400);
    }

    /**
     * Delete a secretary account.
     */
    public function deleteSecretary(Request $request)
    {
        $request->validate([
            'secrefno' => 'required|string|exists:secretaryrights,secrefno'
        ]);

        $secretary = SecretaryModel::where('secrefno', $request->secrefno)->first();
        if ($secretary) {
            SecretaryDoctorsModel::where('secrefno', $request->secrefno)->delete();
            $secretary->delete();

            Log::info('Secretary account deleted by admin', ['secrefno' => $request->secrefno]);

            return response()->json([
                'success' => true
            ]);
        }

        return response()->json(['success' => false, 'message' => 'Secretary not found'], 404);
    }

    /**
     * Fetch all administrators for admin users management.
     */
    public function fetchAdmins()
    {
        $admins = AdminModel::all();
        return response()->json(['admins' => $admins]);
    }

    /**
     * Fetch details of a specific admin user for modal editing.
     */
    public function fetchAdminDetails(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'adminrefno' => 'nullable|string'
        ]);

        $query = AdminModel::query();
        if ($request->filled('adminrefno')) {
            $query->where('adminrefno', $request->adminrefno);
        } elseif ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {
            return response()->json(['success' => false, 'message' => 'Admin identifier required'], 400);
        }

        $admin = $query->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found'], 404);
        }

        return response()->json(['success' => true, 'admin' => $admin]);
    }

    /**
     * Register a new admin user with username defaulting to lowercase lastname.
     */
    public function addAdmin(Request $request)
    {
        $request->validate([
            'adminfname' => 'required|string|max:255',
            'adminmname' => 'nullable|string|max:255',
            'adminlname' => 'required|string|max:255',
            'username' => 'nullable|string|max:50|unique:adminrights,username',
            'password' => 'required|string|min:5',
            'admincontactno' => 'nullable|string|max:20',
            'adminemail' => 'nullable|email|max:255|unique:adminrights,adminemail'
        ]);

        $username = $request->filled('username') ? strtolower(trim($request->username)) : strtolower(trim($request->adminlname));
        $facilityClientCode = config('app.client_code', env('CLIENT_CODE', '122377'));
        $adminrefno = Date::now()->format('mdYHis') . 'ADM';
        $adminidno = Date::now()->format('Y') . "-ADM-" . (AdminModel::count() + 1);

        $admin = AdminModel::create([
            'adminrefno' => $adminrefno,
            'adminidno' => $adminidno,
            'username' => $username,
            'password' => Hash::make($request->password),
            'adminfname' => $request->adminfname,
            'adminmname' => $request->adminmname,
            'adminlname' => $request->adminlname,
            'admincontactno' => $request->admincontactno,
            'adminemail' => $request->adminemail,
            'useremail' => $request->adminemail,
            'clientcode' => $facilityClientCode,
            'active' => true
        ]);

        Log::info('Admin account registered by admin', [
            'username' => $username,
            'adminrefno' => $adminrefno,
            'clientcode' => $facilityClientCode
        ]);

        return response()->json(['success' => true, 'admin' => $admin]);
    }

    /**
     * Update an admin user profile and credentials.
     */
    public function editAdmin(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'adminrefno' => 'nullable|string',
            'adminfname' => 'required|string|max:255',
            'adminmname' => 'nullable|string|max:255',
            'adminlname' => 'required|string|max:255',
            'password' => 'nullable|string|min:5',
            'admincontactno' => 'nullable|string|max:20',
            'adminemail' => 'nullable|email|max:255'
        ]);

        $query = AdminModel::query();
        if ($request->filled('adminrefno')) {
            $query->where('adminrefno', $request->adminrefno);
        } elseif ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {
            return response()->json(['success' => false, 'message' => 'Admin identifier required'], 400);
        }

        $admin = $query->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found'], 404);
        }

        $username = $request->filled('username') ? strtolower(trim($request->username)) : ($admin->username ?: strtolower(trim($request->adminlname)));

        $admin->adminfname = $request->adminfname;
        $admin->adminmname = $request->adminmname;
        $admin->adminlname = $request->adminlname;
        $admin->admincontactno = $request->admincontactno;
        $admin->adminemail = $request->adminemail;
        $admin->useremail = $request->adminemail;
        $admin->username = $username;

        if ($request->filled('password')) {
            $admin->password = Hash::make($request->password);
        }

        $admin->save();

        Log::info('Admin account updated by admin', [
            'admin_id' => $admin->id,
            'username' => $username
        ]);

        return response()->json(['success' => true, 'admin' => $admin]);
    }

    /**
     * Delete an admin user account (guards against deleting self).
     */
    public function deleteAdmin(Request $request)
    {
        $request->validate([
            'id' => 'nullable|integer',
            'adminrefno' => 'nullable|string'
        ]);

        $query = AdminModel::query();
        if ($request->filled('adminrefno')) {
            $query->where('adminrefno', $request->adminrefno);
        } elseif ($request->filled('id')) {
            $query->where('id', $request->id);
        } else {
            return response()->json(['success' => false, 'message' => 'Admin identifier required'], 400);
        }

        $admin = $query->first();
        if (!$admin) {
            return response()->json(['success' => false, 'message' => 'Admin not found'], 404);
        }

        // Prevent admin from deleting their own active logged in account
        if (auth()->guard('admin')->check() && auth()->guard('admin')->id() == $admin->id) {
            return response()->json(['success' => false, 'message' => 'You cannot delete your own active administrator account.'], 403);
        }

        $admin->delete();

        Log::info('Admin account deleted by admin', ['deleted_admin_id' => $admin->id]);

        return response()->json(['success' => true]);
    }

    /**
     * Detailed Comment: Self-service profile update for authenticated administrator.
     * Allows admin to edit their own profile and credentials in 'adminrights',
     * strictly bound to the authenticated admin's ID to prevent cross-user tampering.
     */
    public function updateAdminProfile(Request $request)
    {
        $adminAuth = auth()->guard('admin')->user();
        if (!$adminAuth) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated admin'], 401);
        }

        $request->validate([
            'adminfname' => 'required|string|max:100',
            'adminlname' => 'required|string|max:100',
            'username' => 'required|string|max:50|unique:adminrights,username,' . $adminAuth->id,
            'password' => 'nullable|string|min:5',
            'admincontactno' => 'nullable|string|max:25',
            'adminemail' => 'nullable|email|max:100'
        ]);

        $updateData = [
            'adminfname' => $request->adminfname,
            'adminmname' => $request->adminmname,
            'adminlname' => $request->adminlname,
            'admincontactno' => $request->admincontactno,
            'adminemail' => $request->adminemail,
            'username' => strtolower(trim($request->username))
        ];

        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($request->password);
        }

        AdminModel::where('id', $adminAuth->id)->update($updateData);

        Log::info('Admin self-service profile updated', [
            'admin_id' => $adminAuth->id,
            'username' => $request->username
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Profile updated successfully.'
        ]);
    }

    /**
     * Fetch assigned and available doctors for a secretary.
     * Excludes NULL docrefno values to prevent MySQL NOT IN evaluating to empty set.
     */
    public function fetchSecretaryDoctors(Request $request)
    {
        $request->validate([
            'secrefno' => 'required|string'
        ]);

        $secretary = SecretaryModel::where('secrefno', $request->secrefno)->first();
        if (!$secretary) {
            return response()->json(['success' => false, 'message' => 'Secretary not found'], 404);
        }

        // Ensure we only fetch assigned doctors with valid docrefno
        $assigned = SecretaryDoctorsModel::where('secrefno', $request->secrefno)
            ->whereNotNull('docrefno')
            ->get();

        $assigned_doctors = [];
        foreach ($assigned as $assign) {
            $doc = DoctorsProfileModel::select(['docrefno', 'docname'])
                ->where('docrefno', $assign->docrefno)
                ->first();
            if ($doc) {
                $assigned_doctors[] = $doc;
            }
        }

        $availableDocRefNos = SecretaryDoctorsModel::where('secrefno', $request->secrefno)
            ->whereNotNull('docrefno')
            ->pluck('docrefno')
            ->filter()
            ->all();

        // Available doctors query filtering out assigned ones and NULL docrefno
        $availQuery = DoctorsProfileModel::select(['docrefno', 'docname'])
            ->whereNotNull('docrefno');

        if (!empty($availableDocRefNos)) {
            $availQuery->whereNotIn('docrefno', $availableDocRefNos);
        }

        $avail_doctors = $availQuery->get();

        return response()->json([
            'success' => true,
            'name' => trim(($secretary->seclname . ', ' . $secretary->secfname . ' ' . $secretary->secmname . ' ' . $secretary->secsuffix)),
            'assigned_doctors' => $assigned_doctors,
            'avail_doctors' => $avail_doctors
        ]);
    }

    /**
     * Synchronize assigned doctors for a secretary.
     * Adds newly assigned doctors and removes unassigned doctors.
     */
    public function saveAppendedDoctors(Request $request)
    {
        $request->validate([
            'secrefno' => 'required|string',
            'doctors' => 'nullable|array'
        ]);

        $secrefno = $request->secrefno;
        $submittedDoctors = array_values(array_filter($request->input('doctors', []) ?: []));

        // Synchronize: Delete doctors no longer in the assigned list
        SecretaryDoctorsModel::where('secrefno', $secrefno)
            ->whereNotIn('docrefno', $submittedDoctors)
            ->delete();

        // Ensure submitted doctors are created if they do not already exist
        foreach ($submittedDoctors as $doctor) {
            if (!empty($doctor)) {
                SecretaryDoctorsModel::firstOrCreate(
                    ['docrefno' => $doctor, 'secrefno' => $secrefno],
                    [
                        'recordedby' => auth()->user() ? (auth()->user()->username ?? 'admin') : 'admin',
                        'recordeddate' => Date::now(),
                        'active' => true
                    ]
                );
            }
        }

        Log::info('Secretary doctors synchronized', [
            'secrefno' => $secrefno,
            'assigned_count' => count($submittedDoctors)
        ]);

        return response()->json(['success' => true, 'doctors' => $submittedDoctors]);
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
