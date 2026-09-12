<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfilePostRequest;
use App\Models\EnlistmentModel;
use App\Models\Libraries\IcdLibModel;
use App\Models\Libraries\MedicineLibModel;
use App\Models\ConsultationModel;
use App\Models\PatientMasterlist;
use App\Models\ProfileModel;
use App\Models\Stocks\StocksLedgerModel;
use App\Models\Stocks\StocksListingModel;
use App\Services\YakapManagement\EnlistmentService;
use App\Services\YakapManagement\MedicineService;
use App\Services\YakapManagement\ProfileService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class YakapManagementApiController extends Controller
{
    private $profileService;
    private $medicineService;
    private $enlistmentService;

    public function __construct(
        ProfileService $profileService,
        MedicineService $medicineService,
        EnlistmentService $enlistmentService
    ) {
        $this->profileService = $profileService;
        $this->medicineService = $medicineService;
        $this->enlistmentService = $enlistmentService;
    }

    public function enlistmentSearchAction(Request $request)
    {
        try {
            $pin = $request->input('pin');
            $lastName = $request->input('lastName');
            $firstName = $request->input('firstName');
            $middleName = $request->input('middleName');
            $extension = $request->input('extension');
            $date = $request->input('date');
            $name = $request->input('name');
            $effectiveYear = $request->input('effective-year');

            $query = EnlistmentModel::query();

            if ($pin) {
                $query->where('dPatientPin', 'like', "%{$pin}%");
            }
            if ($lastName) {
                $query->where('dPatientLname', 'like', "%{$lastName}%");
            }
            if ($firstName) {
                $query->where('dPatientFname', 'like', "%{$firstName}%");
            }
            if ($middleName) {
                $query->where('dPatientMname', 'like', "%{$middleName}%");
            }
            if ($extension) {
                $query->where('dPatientExtname', 'like', "%{$extension}%");
            }
            if ($date) {
                $query->whereDate('dPatientDob', $date);
            }
            if ($name) {
                $query->where('patientname', 'like', "%{$name}%");
            }
            if ($effectiveYear) {
                $query->where('dEffyear', $effectiveYear);
            }

            $query = $query->select(
                'dCaseNo',
                'px_pin',
                'dPatientLname',
                'dPatientFname',
                'dPatientMname',
                'dPatientExtname',
                'dPatientType',
                'dPatientDob',
                'dEffyear'
            );

            $results = $query->paginate(5);

            return response()->json($results);
        } catch (\Exception $e) {
            Log::error('Error in enlistmentSearchAction: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while processing your request.'], 500);
        }
    }

    public function enlistmentDetails($caseNo)
    {
        try {
            $enlistment = EnlistmentModel::with(['soaps', 'profiles', 'diagExamResults'])
                ->where('dCaseNo', $caseNo)
                ->first();

            if (! $enlistment) {
                return response()->json(['error' => 'Enlistment record not found.'], 404);
            }

            // dd($enlistment);

            return response()->json($enlistment);
        } catch (\Exception $e) {
            Log::error('Error in enlistmentDetails: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching enlistment details.'], 500);
        }
    }

    public function savePatientDetails(Request $request)
    {
        try {
            // Validate required fields
            $validated = $request->validate([
                'dCaseNo' => 'required|string',
                'dEnlistDate' => 'required|date_format:Y-m-d',
                'dPackageType' => 'required|string',
                'dWithConsent' => 'required|in:Y,N',
                'dPatientType' => 'nullable|string',
                'px_pin' => 'required|string',
                'dPatientLname' => 'required|string|max:60',
                'dPatientFname' => 'required|string|max:60',
                'dPatientMname' => 'nullable|string|max:60',
                'dPatientExtname' => 'nullable|string|max:4',
                'dPatientDob' => 'required|date_format:Y-m-d',
                'dPatientSex' => 'required|in:M,F',
                'dPatientMobileNo' => 'required|string|max:11',
                'dPatientLandlineNo' => 'nullable|string|max:11',
                'dMemPin' => 'required|string|max:12',
                'dMemLname' => 'required|string|max:60',
                'dMemFname' => 'required|string|max:60',
                'dMemMname' => 'nullable|string|max:60',
                'dMemExtname' => 'nullable|string|max:4',
                'dMemDob' => 'required|date_format:Y-m-d',
                'dMemberSex' => 'nullable|in:M,F',
            ]);

            // Find existing record
            $enlistment = EnlistmentModel::where('dCaseNo', $validated['dCaseNo'])->first();

            if (! $enlistment) {
                return response()->json([
                    'success' => false,
                    'message' => 'Enlistment record not found.',
                ], 404);
            }

            // Update timestamp
            $validated['updated'] = now()->format('Y-m-d H:i:s');

            // Combine patient name fields for easier searching
            $patientName = trim(
                $validated['dPatientFname'] . ' ' .
                    ($validated['dPatientMname'] ?? '') . ' ' .
                    $validated['dPatientLname'] . ' ' .
                    ($validated['dPatientExtname'] ?? '')
            );
            $validated['patientname'] = $patientName;

            // Fill and save the model
            $enlistment->fill($validated);
            $enlistment->save();

            return response()->json([
                'success' => true,
                'message' => 'Patient details updated successfully',
                'data' => $enlistment,
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in savePatientDetails: ' . json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('Error in savePatientDetails: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating patient details',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function fetchPatientProfile($transNo)
    {
        try {
            $profile = ProfileModel::with([
                'enlistment',
                'bloodTypes',
                'medHist',
                'famHist',
                'fhSpecific',
                'immunizations',
                'mensHist',
                'pregHist',
                'surgHist',
                'socHist',
                'ncdQans',
                'peGenSurvey',
                'pepert',
                'peMisc',
                'peSpecific',
                'mhSpecific',
            ])->where('dTransNo', $transNo)->first();

            if (! $profile) {
                return response()->json(['error' => 'Profile record not found.'], 404);
            }

            return response()->json($profile);
        } catch (\Exception $e) {
            Log::error('Error in fetchPatientProfile: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching profile details.'], 500);
        }
    }

    public function saveProfileData(ProfilePostRequest $request)
    {
        $data = $request->validated();

        // dd($data);
        try {
            $result = $this->profileService->saveProfileData($data);
        } catch (\Exception $e) {
            Log::error('Error in saveProfileData: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while saving profile data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function searchInhouseMedicine(Request $request): JsonResponse
    {
        try {
            $q = $request->input('q', '');

            $query = StocksListingModel::where('item_grouping', 'DRUGS AND MEDS')->where('phic_reference_code', '!=', '');

            if ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('drug_generic', 'like', "%{$q}%")
                        ->orWhere('drug_brand', 'like', "%{$q}%");
                });
            }

            $results = $query->limit(20)->get();

            $formatted = $results->map(function ($item) {
                return [
                    'id' => $item->phic_reference_code == '' ? 'none' : $item->phic_reference_code,
                    'text' => $item->drug_generic,
                ];
            });

            return response()->json(['results' => $formatted]);
        } catch (\Exception $e) {
            Log::error('Error in searchInhouseMedicine: ' . $e->getMessage());

            return response()->json(['results' => []], 500);
        }
    }

    public function searchPhilhealthMedicine(Request $request): JsonResponse
    {
        try {
            $q = $request->input('q', '');

            $query = MedicineLibModel::query();

            if ($q) {
                $query->where(function ($builder) use ($q) {
                    $builder->where('DRUG_CODE', 'like', "%{$q}%")
                        ->orWhere('DRUG_DESC', 'like', "%{$q}%");
                });
            }

            $results = $query->limit(20)->get();

            $formatted = $results->map(function ($item) {
                return [
                    'id' => $item->DRUG_CODE,
                    'text' => $item->DRUG_CODE . ' - ' . $item->DRUG_DESC,
                ];
            });

            return response()->json(['results' => $formatted]);
        } catch (\Exception $e) {
            Log::error('Error in searchPhilhealthMedicine: ' . $e->getMessage());

            return response()->json(['results' => []], 500);
        }
    }

    public function searchIcdDiagnosis(Request $request)
    {
        try {
            $searchTerm = $request->input('q', '');

            $query = IcdLibModel::where('lib_stat', 1);

            if ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('icd_code', 'like', "%{$searchTerm}%")
                        ->orWhere('icd_desc', 'like', "%{$searchTerm}%");
                });
            }

            $results = $query->limit(20)->get();

            // Format for select2
            $formatted = $results->map(function ($item) {
                return [
                    'id' => $item->icd_code,
                    'text' => $item->icd_code . ' - ' . $item->icd_desc,
                ];
            });

            return response()->json([
                'results' => $formatted,
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchIcdDiagnosis: ' . $e->getMessage());

            return response()->json([
                'results' => [],
            ], 500);
        }
    }

    public function getMedicineDetails(Request $request)
    {
        $source = $request->input('source');
        $medicineCode = $request->input('medicine_code');
        try {
            $medicine = $this->medicineService->getMedicineDetails($medicineCode, $source);

            if (! $medicine) {
                return response()->json(['error' => 'Medicine not found.'], 404);
            }

            return response()->json($medicine);
        } catch (\Exception $e) {
            Log::error('Error in getMedicineDetails: ' . $e->getMessage());

            return response()->json(['error' => 'An error occurred while fetching medicine details.'], 500);
        }
    }

    public function importMedicineFromLedger(Request $request)
    {
        $consultationRefNo = $request->query('consultationrefno') ?? $request->input('consultationrefno');

        if (! $consultationRefNo) {
            return response()->json(['error' => 'Missing consultationrefno.'], 400);
        }

        $ledgerItems = StocksLedgerModel::where('px_consultcode_cn', $consultationRefNo)
            ->where('item_grouping', 'DRUGS AND MEDS')
            ->get();

        if ($ledgerItems->isEmpty()) {
            return response()->json(['medicines' => []]);
        }

        $lookupKeys = $ledgerItems->flatMap(function ($item) {
            return array_filter([$item->prodcode, $item->phic_reference_code]);
        })->unique()->values()->toArray();

        $medicineLibItems = MedicineLibModel::whereIn('DRUG_CODE', $lookupKeys)
            ->get()
            ->keyBy('DRUG_CODE');

        $medicines = $ledgerItems->map(function ($item) use ($medicineLibItems) {
            $lib = $medicineLibItems->get($item->prodcode) ?? $medicineLibItems->get($item->phic_reference_code);
            $actualPrice = $item->retails ?: $item->cost_ave;
            $isDispensed = strtoupper($item->dispensed_status) === 'Y' ? 'Y' : 'N';

            return [
                'id' => $item->id,
                'prodcode' => $item->prodcode,
                'phic_reference_code' => $item->phic_reference_code,
                'item_dscr' => $item->item_dscr,
                'qty' => $item->qty,
                'retails' => $item->retails,
                'cost_ave' => $item->cost_ave,
                'totalamt' => $item->totalamt,
                'item_grouping' => $item->item_grouping,
                'unit' => $item->unit,
                'patient_name' => $item->patient_name,
                'dispensed_status' => $item->dispensed_status,
                'dispensed_date' => $item->dispensed ? $item->dispensed->format('Y-m-d') : null,
                'dispensedby' => $item->dispensedby,
                'prescribed_quantity' => $item->prescribed_quantity,
                'ins_strength' => $item->ins_strength,
                'ins_frequency' => $item->ins_frequency,
                'drug_code' => $lib->DRUG_CODE ?? $item->prodcode,
                'drug_name' => $lib->DRUG_DESC ?? $item->item_dscr,
                'generic_name' => $lib->DRUG_DESC ?? $item->item_dscr,
                'gen_code' => $lib->GEN_CODE ?? null,
                'salt_code' => $lib->SALT_CODE ?? null,
                'strength_code' => $lib->STRENGTH_CODE ?? null,
                'form_code' => $lib->FORM_CODE ?? null,
                'unit_code' => $lib->UNIT_CODE ?? null,
                'package_code' => $lib->PACKAGE_CODE ?? null,
                'medicine_lib' => $lib ? [
                    'DRUG_CODE' => $lib->DRUG_CODE,
                    'DRUG_DESC' => $lib->DRUG_DESC,
                    'GEN_CODE' => $lib->GEN_CODE,
                    'GEN_DESC' => $lib->GEN_DESC,
                    'SALT_CODE' => $lib->SALT_CODE,
                    'SALT_DESC' => $lib->SALT_DESC,
                    'STRENGTH_CODE' => $lib->STRENGTH_CODE,
                    'STRENGTH_DESC' => $lib->STRENGTH_DESC,
                    'FORM_CODE' => $lib->FORM_CODE,
                    'FORM_DESC' => $lib->FORM_DESC,
                    'UNIT_CODE' => $lib->UNIT_CODE,
                    'UNIT_DESC' => $lib->UNIT_DESC,
                    'PACKAGE_CODE' => $lib->PACKAGE_CODE,
                    'PACKAGE_DESC' => $lib->PACKAGE_DESC,
                ] : null,
            ];
        });

        return response()->json(['medicines' => $medicines]);
    }

    public function searchPatientMasterlist(Request $request)
    {
        try {
            $searchTerm = $request->input('name', '');

            if (empty($searchTerm) || strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Please enter at least 2 characters to search'
                ]);
            }

            $query = PatientMasterlist::query();

            // Search by full name or individual name fields
            $query->where(function ($q) use ($searchTerm) {
                $q->where('patientname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxfirstname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxlastname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxmidname', 'like', "%{$searchTerm}%");
            });

            $results = $query->select(
                'pincode',
                'patientname',
                'pxlastname',
                'pxfirstname',
                'pxmidname',
                'pxsuffix',
                'mobilenumber'
            )->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchPatientMasterlist: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for patients',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function searchWalkinConsultations(Request $request)
    {
        try {
            $searchTerm = $request->input('name', '');

            if (empty($searchTerm) || strlen($searchTerm) < 2) {
                return response()->json([
                    'success' => true,
                    'data' => [],
                    'message' => 'Please enter at least 2 characters to search'
                ]);
            }

            $query = ConsultationModel::query();

            $query->where(function ($q) use ($searchTerm) {
                $q->where('patientname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxfirstname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxlastname', 'like', "%{$searchTerm}%")
                    ->orWhere('pxmidname', 'like', "%{$searchTerm}%");
            });

            $query->orderByDesc('consultation_date')->orderByDesc('id');

            $results = $query->select(
                'consultationrefno',
                'consultation_date',
                'pxrefno',
                'patientname',
                'pxlastname',
                'pxfirstname',
                'pxmidname',
                'pxsuffix',
                'mobilenumber'
            )->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Error in searchWalkinConsultations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while searching for consultations',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function linkPatientPin(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'en_caseno' => 'required',
                'px_pin' => 'required'
            ]);

            // Call service to link patient
            $result = $this->enlistmentService->linkPatientToEnlistment(
                $validated['en_caseno'],
                $validated['px_pin']
            );

            return response()->json([
                'success' => $result['success'],
                'message' => $result['message']
            ], 200);
        } catch (ValidationException $e) {
            Log::error('Validation error in linkPatientPin: ' . json_encode($e->errors()));

            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\InvalidArgumentException $e) {
            Log::error('Invalid argument in linkPatientPin: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        } catch (\Exception $e) {
            Log::error('Error in linkPatientPin: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while linking patient',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
