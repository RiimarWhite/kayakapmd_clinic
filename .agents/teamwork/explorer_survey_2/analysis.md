# Codebase Survey & In-Depth Technical Analysis
## Requirements R2 (Clinical Data & Attachment Integration) & R3 (Itemized Billing Calculations)

**Survey Agent**: explorer_survey_2  
**Date**: 2026-10-12  
**Working Directory**: `C:\xampp\htdocs\kayakapmd_clinic\.agents\teamwork\explorer_survey_2\`  
**Target Modals/Views**:
- `#view_consultation_details_modal` in `resources/views/modals/view_consultation_details.blade.php`
- Calling scripts:
  - `resources/js/pages/doctor/dashboard.js` (`openConsultationDetailsModal`)
  - `resources/js/pages/doctor/patients.js` (`openConsultationDetailsModal`)
  - `resources/js/pages/secretary/queue.js` (`openConsultationDetailsModal`)
  - `resources/js/pages/doctor/consultation/form.js` (`populateAndShowConsultationDetails`)
- Backend Endpoints:
  - `POST /api/fetch_patient_medhistory` (`app/Http/Controllers/SecretaryController.php@fetchPatientMedhistory`)
  - `GET /preview-file/{path}` (`routes/web.php`)
  - `POST /api/fetch_radlab_files` & `POST /api/upload_consultation_files` (`DoctorController.php`)
  - `GET /print_pdf` (`DoctorController.php@printPDF`)
  - `POST /api/fetch_patient_charges`, `save_patient_charges`, `delete_patient_charge` (`DoctorController.php`)
  - `POST /api/save_settlements`, `fetch_settlements` (`SecretaryController.php`)

---

## 1. Executive Summary & Core Findings

1. **MySQL 1054 Crash / "N/A" on Patient Address**:
   - In `SecretaryController.php` (line 775), the Eloquent query specifies `'address'` directly in `$query->select([...])` on `ConsultationModel` (`pxwalkinconsultation`).
   - According to the authoritative Data Dictionary (`kayakapmdv2_data_dictionary.md` Module 4) and MySQL schema migrations, `pxwalkinconsultation` has 92 columns and **does NOT contain an `address` column**.
   - Running `select address from pxwalkinconsultation` in MySQL immediately triggers:
     `SQLSTATE[42S22]: Column not found: 1054 Unknown column 'address' in 'field list'`.
   - In SQLite `:memory:` tests, this column returned null without crashing, resulting in the UI persistently showing `"N/A"` for patient address in `#vcd_address`.
   - The authoritative table holding patient addresses is `pxmasterlist` (`pxmasterlist.address`, or composite `streetadrs`, `brgy`, `muncity`, `province`).
   - `ConsultationModel` already defines `public function patient() { return $this->belongsTo(PatientMasterlist::class, 'pxrefno', 'pxrefno'); }`.
   - **Resolution**: Remove `'address'` from `ConsultationModel::select()`, and resolve it via `$record->patient` during data transformation (matching how `DoctorController::printPDF` lines 965-969 already resolves it).

2. **Broken Laboratory & Radiology Links (404 in UI)**:
   - In `SecretaryController.php@fetchPatientMedhistory` (lines 805-806), `laboratorypath` and `radiologypath` are queried as raw disk paths (e.g. `'radiology_results/file.png'`), but **no URL is generated** (unlike `photo_path` which is converted to `photo_url`).
   - In JS rendering logic (`dashboard.js:599`, `patients.js:987`, `queue.js:807`, `form.js:462`), the link is set via:
     `const radUrl = record.radiology_url || record.radiologypath; $("#vcd_rad_preview_btn").attr('href', radUrl);`
   - Because `record.radiology_url` is null, `href` is set to the relative string `'radiology_results/file.png'`. Clicking this navigates relative to the current browser route (e.g. `http://localhost/doctor/dashboard/radiology_results/file.png` or `http://localhost/secretary/queue/radiology_results/file.png`), throwing an immediate **HTTP 404**.
   - Furthermore, in Apache subfolder installations (e.g. `/kayakapmd_clinic`), URLs must route to `/{project}/preview-file/{path}`.
   - **Resolution**:
     - In backend `SecretaryController.php`, generate `$record->radiology_url` and `$record->laboratory_url` using `route('preview.file', ['path' => ...])`.
     - In all 4 JS modal population files, implement a URL normalizer `resolveFileUrl(path)` that prepends `basePath + '/preview-file/'` if a relative path is passed.

3. **Complete Elimination of "N/A" Placeholders in Clinical Payload**:
   - Vitals: Vitals default to `'--'` when unrecorded, but formatting must gracefully handle zero vs null values (e.g., `bpnumerator` / `bpdenominator`, `weight`, `height`, `temp`, `pulserate`, `respiratoryrate`).
   - Demographics: Missing `pincode`, `pxrefno`, `birthday`, `gender`, `mobilenumber` can be defensively cross-filled from `$record->patient` (`pxmasterlist`).
   - Calculated Age: If `age` is null but `birthday` is known, dynamically compute age via `Carbon::parse($birthday)->age` so it never renders `"N/A"`.
   - Attending Physician: If `docname` is empty on consultation, resolve it from `DoctorsProfileModel` using `docrefno`.
   - Prescriptions & Diagnostics: If `stocks_ledger` has no records, fallback to `pxrxdocuments` (`DoctorMedicinesModel`) and `docrequests` (`DocRequestsModel`) so historical consultations from older workflows populate seamlessly.

4. **Itemized Billing & Stocks Ledger Calculations**:
   - `stocks_ledger` stores all consultation line items with `transactiontype = 'CHARGES'`, linked via `px_consultcode_cn = $consultationrefno`.
   - Professional Fee (`PF` / `PROFESSIONAL FEE`) is automatically inserted if missing when charges are fetched, calculating doctor profile fee + VAT according to `autoAddVAT` and `vatrate`.
   - Medicine, diagnostic, and supply pricing dynamically tier based on patient classification: `PHIC` (`price_phic`), `HMO` (`price_hmo`), `OTHERS` (`price_others`), or `REGULAR` (`price_regular` / `cost_ave`).
   - Line items compute `totalamt = qty * cost_ave`.
   - In `#view_consultation_details_modal`, Tab 5 (`#vcd_pane_charges`) displays the itemized breakdown (Description, Category, Qty, Unit Price, Total Amount) and computes the gross total (`#vcd_charges_total`).
   - Secretary checkout (`saveSettlements`) computes categorized totals (`totalMeds`, `totalLab`, `totalPf`, etc.), deducts Senior/PWD (`less_srpwd`), PHIC (`less_phic`), HMO (`less_hmo`), and records net payable in `pxsettlements`.
   - Printable PDF slips (`/print_pdf?type=rx&consultationrefno=...`, `/print_pdf?type=diagnostics&consultationrefno=...`, and `/print_pdf?type=soa&consultationrefno=...`) are streamed via Dompdf with half-A4 landscape (A5) for Rx & Diagnostics, and A4 portrait for SOA.

---

## 2. Deep Dive: Consultation Details Modal & Data Payload (Focus Area 1)

### 2.1 Modal Structure (`view_consultation_details.blade.php`)
The modal `#view_consultation_details_modal` is organized into:
1. **Header**: Consultation Date badge (`#vcd_consultdate_badge`), Ref badge (`#vcd_consultref_badge`), Status badge (`#vcd_status_badge`), Type badge (`#vcd_patient_type_badge`).
2. **Demographics & Vitals Card**:
   - Consulting Doctor: `#vcd_docname`
   - Patient Photo: `#vcd_photo`
   - Name: `#vcd_name`
   - Identifiers: PIN (`#vcd_pincode`), Ref (`#vcd_pxrefno`)
   - Demographics: Sex (`#vcd_sex`), Birthdate (`#vcd_bday`), Age (`#vcd_age`), Mobile (`#vcd_cellno`), Address (`#vcd_address`)
   - Vitals: Weight (`#vcd_weight`), Height (`#vcd_height`), Temp (`#vcd_temp`), Resp. Rate (`#vcd_resprate`), Pulse Rate (`#vcd_pulserate`), Blood Pressure (`#vcd_bp`)
3. **Tab 1: Impressions & Diagnosis (`#vcd_pane_impdiag`)**:
   - Chief Complaint: `#vcd_chief_complaint` (textarea)
   - Impressions: `#vcd_impressions` (textarea)
   - Diagnosis: `#vcd_diagnosis` (textarea)
   - Admission Box: `#vcd_admit_box`, `#vcd_admit_badge`, `#vcd_admit_instructions` (textarea)
4. **Tab 2: Rx & Instructions (`#vcd_pane_rx`)**:
   - Print Rx button: `#vcd_print_rx_btn`
   - Rx Table: `#vcd_rx_table tbody` (Medicine Name, Instructions/Sig, Qty, Dispensed Badge)
   - General Instructions: `#vcd_general_instructions` (textarea)
5. **Tab 3: Diagnostic Requests (`#vcd_pane_dreqs`)**:
   - Print Diagnostics button: `#vcd_print_diag_btn`
   - Diagnostics Table: `#vcd_diagnostics_table tbody` (Request/Procedure Name, Category)
6. **Tab 4: Radiology & Laboratory (`#vcd_pane_radlab`)**:
   - Radiology Status: `#vcd_rad_status` & Button `#vcd_rad_preview_btn`
   - Laboratory Status: `#vcd_lab_status` & Button `#vcd_lab_preview_btn`
7. **Tab 5: Patient Charges (`#vcd_pane_charges`)**:
   - Charges Table: `#vcd_charges_table tbody` (Description, Category, Qty, Unit Price, Total Amount)
   - Gross Total Amount: `#vcd_charges_total`

### 2.2 Comprehensive Mapping: UI Elements to Database Schemas

| UI Element ID | Current Placeholder / Default | Target DB Table & Column | Fallback / Resolution Chain |
|---|---|---|---|
| `#vcd_consultdate_badge` | `Date: --` | `pxwalkinconsultation.consultation_date` | `consulteddate` → `requesteddate` → `recordeddate` → `now()` |
| `#vcd_consultref_badge` | `Ref: --` | `pxwalkinconsultation.consultationrefno` | `pxwalkinconsultation.caseno` |
| `#vcd_status_badge` | `Status: --` | `pxwalkinconsultation.status` | Defaults to `'WAITING'` if empty |
| `#vcd_patient_type_badge` | `Type: REGULAR` | `pxwalkinconsultation.classification` | `hmocode ? 'HMO' : (phic_pin ? 'PHIC' : 'REGULAR')` |
| `#vcd_docname` | `--` | `pxwalkinconsultation.docname` | Lookup `DoctorsProfileModel` via `docrefno` → `'None Assigned'` |
| `#vcd_photo` | `/images/blank_photo.png` | `pxwalkinconsultation.photo_path` | `pxmasterlist.photo_path` → `/images/blank_photo.png` |
| `#vcd_name` | `--` | `pxwalkinconsultation.patientname` | Composite `[pxlastname, pxfirstname, pxmidname]` → `pxmasterlist.patientname` |
| `#vcd_pincode` | `N/A` | `pxwalkinconsultation.pincode` | `pxwalkinconsultation.phic_pin` → `pxmasterlist.pincode` / `phic_pin` |
| `#vcd_pxrefno` | `N/A` | `pxwalkinconsultation.pxrefno` | `pxmasterlist.pxrefno` |
| `#vcd_sex` | `--` | `pxwalkinconsultation.gender` | `pxmasterlist.gender` |
| `#vcd_bday` | `--` | `pxwalkinconsultation.birthday` | `pxmasterlist.birthday` |
| `#vcd_age` | `--` | `pxwalkinconsultation.age` | Computed from `birthday`: `Carbon::parse($bday)->age` |
| `#vcd_cellno` | `--` | `pxwalkinconsultation.mobilenumber` | `pxmasterlist.mobilenumber` |
| `#vcd_address` | `--` | **`pxmasterlist.address`** | Composite `[streetadrs, brgy, muncity, province]` (NOT on `pxwalkinconsultation`) |
| `#vcd_weight` | `--` | `pxwalkinconsultation.weight`, `wunit` | Format: `${weight} ${wunit ?? 'kg'}` |
| `#vcd_height` | `--` | `pxwalkinconsultation.height`, `hunit` | Format: `${height} ${hunit ?? 'cm'}` |
| `#vcd_temp` | `--` | `pxwalkinconsultation.temp`, `tempunit` | Format: `${temp} ${tempunit ?? '°C'}` |
| `#vcd_resprate` | `--` | `pxwalkinconsultation.respiratoryrate` | Format: `${respiratoryrate} cpm` |
| `#vcd_pulserate` | `--` | `pxwalkinconsultation.pulserate` | Format: `${pulserate} bpm` |
| `#vcd_bp` | `--` | `pxwalkinconsultation.bpnumerator`, `bpdenominator` | Format: `${bpnumerator}/${bpdenominator} mmHg` |
| `#vcd_chief_complaint` | (textarea empty) | `pxwalkinconsultation.reasonforconsultation` | `'No chief complaint recorded.'` |
| `#vcd_impressions` | (textarea empty) | `pxwalkinconsultation.impression` | `'No impressions recorded.'` |
| `#vcd_diagnosis` | (textarea empty) | `pxwalkinconsultation.finadiagnosis` | `'No final diagnosis recorded.'` |
| `#vcd_admit_badge` | `Not for Admission` | `pxwalkinconsultation.foradmit` | Flag `1` → `'For Admission'` (bg-danger); `0` → `'Not for Admission'` (bg-secondary) |
| `#vcd_admit_instructions`| (textarea empty) | `pxwalkinconsultation.foradmit_instructions` | Fallback to `instructions` |
| `#vcd_rx_table` | `No prescriptions recorded.` | `stocks_ledger` (`item_grouping = 'DRUGS AND MEDS'`) | Fallback to `pxrxdocuments` (`DoctorMedicinesModel`) |
| `#vcd_general_instructions` | (textarea empty) | `pxwalkinconsultation.instructions` | Doctor general instructions |
| `#vcd_diagnostics_table`| `No diagnostic requests recorded.` | `stocks_ledger` (`item_grouping IN ('DIAGNOSTIC','IMAGING')`) | Fallback to `docrequests` (`DocRequestsModel`) |
| `#vcd_rad_status` / `#vcd_rad_preview_btn` | `No radiology file uploaded.` | `pxwalkinconsultation.radiologypath` | Generated URL via `route('preview.file')` |
| `#vcd_lab_status` / `#vcd_lab_preview_btn` | `No laboratory file uploaded.` | `pxwalkinconsultation.laboratorypath` | Generated URL via `route('preview.file')` |
| `#vcd_charges_table` | `No charges recorded.` | `stocks_ledger` (all charges for `px_consultcode_cn`) | Items with `cost_ave`, `retails`, `qty`, `totalamt` |
| `#vcd_charges_total` | `0.00` | Sum of `totalamt` across `stocks_ledger` | Sum of item totals formatted to 2 decimals |

---

## 3. Deep Dive: Laboratory & Radiology Attachment Integration (Focus Area 2)

### 3.1 Storage Architecture & Filesystem
- **Storage Disk**: Files are stored in the Laravel `private` disk located at `storage/app/private/`.
- **Directory Paths**:
  - Radiology: `storage/app/private/radiology_results/<hash_filename>.<ext>`
  - Laboratory: `storage/app/private/laboratory_results/<hash_filename>.<ext>`
- **Database Schema**:
  - Table: `pxwalkinconsultation`
  - Columns:
    - `radiologypath`: `varchar(255) NULL`
    - `laboratorypath`: `varchar(255) NULL`

### 3.2 File Upload Handlers
1. **Consultation Save & Update** (`ConsultationController.php`):
   - Lines 804-805:
     ```php
     $consultationData['radiologypath'] = $request->hasFile('radiology_file') ? $request->file('radiology_file')->store('radiology_results', 'private') : null;
     $consultationData['laboratorypath'] = $request->hasFile('laboratory_file') ? $request->file('laboratory_file')->store('laboratory_results', 'private') : null;
     ```
   - Lines 993-994:
     ```php
     $consultationData['radiologypath'] = $request->hasFile('radiologypath') ? $request->file('radiologypath')->store('radiology_results', 'private') : $record->radiologypath;
     $consultationData['laboratorypath'] = $request->hasFile('laboratorypath') ? $request->file('laboratorypath')->store('laboratory_results', 'private') : $record->laboratorypath;
     ```
2. **Dedicated Upload Route** (`DoctorController.php`):
   - Method: `uploadConsultationFiles` (lines 1819-1832)
   - Route: `POST /api/upload_consultation_files`
   - Updates `radiologypath` and `laboratorypath` via `$request->file('radiology')->store('radiology_results', 'private')` and `$request->file('laboratory')->store('laboratory_results', 'private')`.

### 3.3 File Retrieval & Preview Routes
1. **Protected Preview Route** (`routes/web.php` lines 108-135):
   ```php
   $previewFileHandler = function ($path) {
       $cleanPath = str_replace(['..', "\0"], '', $path);
       $storagePath = storage_path('app/private/' . $cleanPath);
       if (!file_exists($storagePath) || is_dir($storagePath)) {
           abort(404, 'File not found');
       }

       return response()->file($storagePath, [
           'Content-Disposition' => 'inline; filename="' . basename($storagePath) . '"',
       ]);
   };

   Route::get('/preview-file/{path}', $previewFileHandler)
       ->where('path', '.*')
       ->middleware('auth:web,doctor,secretary,admin')
       ->name('preview.file');

   Route::get('/{project}/preview-file/{path}', function ($project, $path) use ($previewFileHandler) {
       return $previewFileHandler($path);
   })->where('project', 'kayakapmd_clinic|EConsultationv2|[a-zA-Z0-9_-]+')
     ->where('path', '.*')
     ->middleware('auth:web,doctor,secretary,admin')
     ->name('preview.file.project');
   ```
2. **The Gap in `fetchPatientMedhistory`**:
   - `SecretaryController.php` only outputs `$record->radiologypath` and `$record->laboratorypath`.
   - It fails to populate `$record->radiology_url` and `$record->laboratory_url`.
   - In `DoctorController.php:1778-1798` (`fetchRadLabFiles`), full URLs are created via `route('preview.file', ['path' => $path])`.
   - Therefore, `fetchPatientMedhistory` must follow the same pattern:
     ```php
     $record->radiology_url = !empty($record->radiologypath) ? route('preview.file', ['path' => $record->radiologypath]) : null;
     $record->laboratory_url = !empty($record->laboratorypath) ? route('preview.file', ['path' => $record->laboratorypath]) : null;
     ```
3. **The Gap in Frontend JS**:
   - In `dashboard.js`, `patients.js`, `queue.js`, and `form.js`, `openConsultationDetailsModal` assumes `record.radiologypath` can be set as `href`.
   - If `record.radiologypath = "radiology_results/xyz.png"`, setting `href="radiology_results/xyz.png"` fails.
   - A helper `resolveFileUrl(path)` should normalize:
     ```javascript
     function resolveFileUrl(path) {
         if (!path) return null;
         if (path.startsWith("http://") || path.startsWith("https://")) return path;
         const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
         if (path.startsWith("/preview-file/")) return basePath + path;
         return basePath + '/preview-file/' + path.replace(/^\//, '');
     }
     ```

---

## 4. Deep Dive: Itemized Stocks Ledger Charges & Billing Calculations (Focus Area 3)

### 4.1 How Charges are Recorded in `stocks_ledger`
Table: `stocks_ledger` (Authoritative Data Dictionary Module 6)
Key fields:
- `px_consultcode_cn`: Stores `consultationrefno`.
- `transactiontype`: Set to `'CHARGES'`.
- `item_grouping`:
  - `'DRUGS AND MEDS'`: Prescriptions added via `DoctorController@addMedicine`.
  - `'DIAGNOSTIC'`: Laboratory & imaging requests added via `DoctorController@saveDiagnosticRequest`.
  - `'PROFESSIONAL FEE'`: Doctor consultation fee (`PF`), auto-inserted or updated via `fetchPatientCharges`.
  - `'PROCEDURES'`, `'SUPPLIES'`, `'IMAGING'`: Items added via `DoctorController@saveAppendedCharges`.
- `prodcode`: Catalog stock code from `stocks_listing.prodcode`.
- `item_dscr`: Product / procedure description.
- `qty`: Quantity requested.
- `cost_ave` & `retails`: Unit price charged to the patient.
- `totalamt`: Line item total (`qty * cost_ave`).
- `remarks` & `instructions`: Dosage instructions for medicines, or clinical remarks for diagnostics.
- `dispensed_status`: `'PENDING'`, `'RELEASED'`, `'CANCELLED'`.
- `dispensed`: Datetime when dispensed by pharmacy/secretary.

### 4.2 Pricing Tiers Logic
`DoctorController.php` evaluates `ConsultationModel.classification`:
1. **PHIC** (`classification === 'phic' || !empty(phic_pin)`): Uses `stocks_listing.price_phic`. For Doctor PF, uses `DoctorsProfileModel.phicrate`.
2. **HMO** (`classification === 'hmo' || !empty(hmocode)`): Uses `stocks_listing.price_hmo`.
3. **OTHERS** (`classification === 'others'`): Uses `stocks_listing.price_others`.
4. **REGULAR** (default): Uses `stocks_listing.price_regular` (or `cost_ave`). For Doctor PF, uses `DoctorsProfileModel.pfrate ?: consultationfee`.

### 4.3 Doctor Professional Fee & Auto-VAT
When `fetchPatientCharges` runs:
- Checks if a `PROFESSIONAL FEE` record exists in `stocks_ledger`.
- If missing, retrieves doctor profile (`DoctorsProfileModel`).
- If `autoAddVAT == 1` and `vatrate > 0`, VAT is computed: `vatAmount = round(baseFee * (vatrate / 100), 2); totalFee = baseFee + vatAmount`.
- Creates the `PF` line item in `stocks_ledger`.

### 4.4 Modal Tab 5 Display (`#vcd_pane_charges`)
- Data source: `record.charges` from `/api/fetch_patient_medhistory`.
- In `SecretaryController.php:839-842`:
  ```php
  $record->charges = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
      ->select(['id', 'prodcode', 'item_dscr', 'item_grouping', 'qty', 'cost_ave', 'retails', 'totalamt'])
      ->get();
  $record->charges_total = $record->charges->sum('totalamt');
  ```
- JS iterates over `charges`:
  ```javascript
  charges.forEach(c => {
      const qty = parseFloat(c.qty) || 1;
      const unitPrice = parseFloat(c.cost_ave || c.retails || 0);
      const subTotal = parseFloat(c.totalamt || (qty * unitPrice));
      totalAmt += subTotal;
      // append table row
  });
  $("#vcd_charges_total").text(totalAmt.toFixed(2));
  ```

### 4.5 Billing Settlement Checkout (`saveSettlements`)
When front desk / secretary performs final billing:
- Subtotals categorized:
  - `totalMeds` (`DRUGS AND MEDS`)
  - `totalLab` (`DIAGNOSTIC`)
  - `totalPf` (`PROFESSIONAL FEE`)
  - `totalProcedures` (`PROCEDURES`)
  - `totalSupplies` (`SUPPLIES`)
  - `totalVaccines` (`VACCINES`)
  - `totalImmunizations` (`IMMUNIZATION`)
  - `totalOthers`
- Gross total: `totalGross = sum('totalamt')`
- Deductions applied:
  - Senior Citizen / PWD discount: `lessSrpwd`
  - PhilHealth benefit: `lessPhic`
  - HMO coverage: `lessHmo`
  - Custom discount: `lessDiscount`
- Net Payable: `netPayable = max(0, totalGross - lessSrpwd - lessPhic - lessHmo - lessDiscount)`.
- Persisted into `pxsettlements`.

### 4.6 Direct PDF Print Slips (`printPDF` & `printDiagnostics`)
- **Endpoints**:
  - `GET /print_pdf?type=rx&consultationrefno={ref}`
  - `GET /print_pdf?type=diagnostics&consultationrefno={ref}`
  - `GET /print_pdf?type=instructions&consultationrefno={ref}`
  - `GET /print_pdf?type=admission&consultationrefno={ref}`
  - `GET /print_pdf?type=soa&consultationrefno={ref}`
- **Security & Multi-Role Access**:
  - Protected by `Route::middleware('auth:secretary,doctor,admin')` in `routes/web.php:51-54`.
  - Accessible across Secretary, Doctor, and Admin sessions.
- **Dompdf Specifications**:
  - Rx & Diagnostics: Half-A4 Landscape (A5 landscape, 210mm x 148mm), preventing Dompdf missing glyphs and optimizing for compact prescription pads.
  - Admission & SOA: Standard A4 Portrait.
  - Streaming returns HTTP 200 with `application/pdf` and `Content-Disposition: inline`.

---

## 5. Concrete Code Change Proposals for Implementers

### Proposal 1: Fix `SecretaryController.php` (`fetchPatientMedhistory`)
**File**: `app/Http/Controllers/SecretaryController.php` (lines 763-845)
**Problem**:
1. Selecting `'address'` causes SQL 1054 crash in MySQL because `address` is on `pxmasterlist`, not `pxwalkinconsultation`.
2. `laboratorypath` and `radiologypath` do not generate URLs.
3. Fallback to `pxrxdocuments` and `docrequests` is missing for legacy/imported consultations.
4. Patient demographics (pincode, address, age, sex, phone) are not defensively populated from `pxmasterlist`.

**Proposed Implementation**:
```php
        // 1. Remove 'address' from select list
        $history = $query->select([
                'id',
                'consultationrefno',
                'pxrefno',
                'pincode',
                'caseno',
                'patientname',
                'gender',
                'birthday',
                'age',
                'mobilenumber',
                'emailaddress',
                // 'address' removed to prevent MySQL 1054 error
                'photo_path',
                'consultation_date',
                'reasonforconsultation',
                'impression',
                'finadiagnosis',
                'instructions',
                'foradmit',
                'foradmit_instructions',
                'weight',
                'wunit',
                'height',
                'hunit',
                'temp',
                'tempunit',
                'respiratoryrate',
                'pulserate',
                'bpnumerator',
                'bpdenominator',
                'docname',
                'docrefno',
                'classification',
                'subclassification',
                'hmocode',
                'hmoname',
                'phic_pin',
                'status',
                'queueno',
                'recordedby',
                'recordeddate',
                'laboratorypath',
                'radiologypath'
            ])
            ->orderBy('id', 'desc')
            ->get();

        $history->transform(function ($record) {
            // Eagerly resolve related patient from pxmasterlist
            $patientMaster = $record->patient ?? PatientMasterlist::where('pxrefno', $record->pxrefno)->orWhere('pincode', $record->pincode)->first();

            // Populate address safely from PatientMasterlist
            if ($patientMaster) {
                $addrParts = array_filter([$patientMaster->streetadrs, $patientMaster->brgy, $patientMaster->muncity, $patientMaster->province]);
                $record->address = !empty($addrParts) ? implode(', ', $addrParts) : ($patientMaster->address ?? 'N/A');
                if (empty($record->mobilenumber)) $record->mobilenumber = $patientMaster->mobilenumber ?? 'N/A';
                if (empty($record->birthday)) $record->birthday = $patientMaster->birthday;
                if (empty($record->gender)) $record->gender = $patientMaster->gender ?? 'N/A';
                if (empty($record->pincode)) $record->pincode = $patientMaster->pincode ?? $patientMaster->phic_pin ?? 'N/A';
                if (empty($record->photo_path) && !empty($patientMaster->photo_path)) {
                    $record->photo_path = $patientMaster->photo_path;
                }
            } else {
                $record->address = 'N/A';
            }

            // Dynamically calculate age if missing but birthday exists
            if (empty($record->age) && !empty($record->birthday)) {
                try {
                    $record->age = \Carbon\Carbon::parse($record->birthday)->age;
                } catch (\Exception $e) {
                    $record->age = null;
                }
            }

            // Photo URL handling
            if ($record->photo_path && !str_contains($record->photo_path, 'blank_photo.png')) {
                $filename = basename($record->photo_path);
                $record->photo_path = url('/patient/photo/' . $filename);
                $record->photo_url = url('/patient/photo/' . $filename);
            } else {
                $record->photo_path = url('/images/blank_photo.png');
                $record->photo_url = url('/images/blank_photo.png');
            }

            // Generate full preview URLs for Laboratory and Radiology files
            $record->radiology_url = !empty($record->radiologypath) ? route('preview.file', ['path' => $record->radiologypath]) : null;
            $record->laboratory_url = !empty($record->laboratorypath) ? route('preview.file', ['path' => $record->laboratorypath]) : null;

            // Prescriptions (Rx) from stocks_ledger with fallback to DoctorMedicinesModel (pxrxdocuments)
            $rxItems = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->where('item_grouping', 'DRUGS AND MEDS')
                ->select(['prodcode', 'item_dscr', 'qty', 'remarks', 'instructions', 'dispensed_status', 'dispensed'])
                ->get()
                ->map(function ($rx) {
                    $rx->dispensed_flag = ($rx->dispensed_status === 'RELEASED' || !empty($rx->dispensed)) ? 1 : 0;
                    return $rx;
                });

            if ($rxItems->isEmpty()) {
                $legacyRx = DoctorMedicinesModel::where('consultationrefno', $record->consultationrefno)->get()
                    ->map(function ($docRx) {
                        return (object)[
                            'prodcode' => $docRx->medicinecode ?? 'RX',
                            'item_dscr' => $docRx->medicinename,
                            'qty' => $docRx->medicinequantity ?: 1,
                            'remarks' => $docRx->medicinedosage ?? 'As directed',
                            'instructions' => $docRx->medicineduration ?? '',
                            'dispensed_flag' => 0
                        ];
                    });
                $record->rx_items = $legacyRx;
            } else {
                $record->rx_items = $rxItems;
            }

            // Diagnostics from stocks_ledger with fallback to DocRequestsModel (docrequests)
            $diagItems = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->whereIn('item_grouping', ['DIAGNOSTIC', 'IMAGING'])
                ->select(['prodcode', 'item_dscr', 'item_grouping', 'remarks', 'updated'])
                ->get();

            if ($diagItems->isEmpty()) {
                $legacyDiags = DocRequestsModel::where('consultationrefno', $record->consultationrefno)->get()
                    ->map(function ($req) {
                        $master = DiagnosticsMasterlistModel::where('diagnosticrefno', $req->requestrefno)->first();
                        return (object)[
                            'prodcode' => $req->requestrefno,
                            'item_dscr' => $master->diagnostic_name ?? $req->request_dscr ?? 'Diagnostic Procedure',
                            'item_grouping' => $master->diagnostic_catg ?? 'DIAGNOSTIC',
                            'remarks' => $req->request_dscr ?? ''
                        ];
                    });
                $record->diagnostic_items = $legacyDiags;
            } else {
                $record->diagnostic_items = $diagItems;
            }

            // Charges & total calculation
            $record->charges = StocksLedgerModel::where('px_consultcode_cn', $record->consultationrefno)
                ->select(['id', 'prodcode', 'item_dscr', 'item_grouping', 'qty', 'cost_ave', 'retails', 'totalamt'])
                ->get();
            $record->charges_total = $record->charges->sum('totalamt');

            return $record;
        });
```

### Proposal 2: Standardize File Preview URL Normalizer in Frontend JS
**Files**:
- `resources/js/pages/doctor/dashboard.js`
- `resources/js/pages/doctor/patients.js`
- `resources/js/pages/secretary/queue.js`
- `resources/js/pages/doctor/consultation/form.js`

**Proposed JS Function**:
```javascript
function resolvePreviewUrl(urlOrPath) {
    if (!urlOrPath) return null;
    if (urlOrPath.startsWith("http://") || urlOrPath.startsWith("https://")) return urlOrPath;
    const basePath = window.location.pathname.startsWith('/kayakapmd_clinic') ? '/kayakapmd_clinic' : '';
    if (urlOrPath.startsWith("/preview-file/")) return basePath + urlOrPath;
    return `${basePath}/preview-file/${encodeURIComponent(urlOrPath).replace(/%2F/g, '/')}`;
}
```
**Application in `openConsultationDetailsModal`**:
```javascript
// Tab 4: Radiology & Laboratory
const radUrl = resolvePreviewUrl(record.radiology_url || record.radiologypath);
if (radUrl) {
    $("#vcd_rad_status").html('<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Document attached</span>');
    $("#vcd_rad_preview_btn").attr('href', radUrl).removeClass('d-none');
} else {
    $("#vcd_rad_status").text('No radiology file uploaded.');
    $("#vcd_rad_preview_btn").attr('href', '#').addClass('d-none');
}

const labUrl = resolvePreviewUrl(record.laboratory_url || record.laboratorypath);
if (labUrl) {
    $("#vcd_lab_status").html('<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> Document attached</span>');
    $("#vcd_lab_preview_btn").attr('href', labUrl).removeClass('d-none');
} else {
    $("#vcd_lab_status").text('No laboratory file uploaded.');
    $("#vcd_lab_preview_btn").attr('href', '#').addClass('d-none');
}
```

---

## 6. Synthesis & Summary Table

| Requirement / Component | Current State | Root Cause / Vulnerability | Planned Hardening |
|---|---|---|---|
| **Patient Address** | N/A or MySQL 500 error | Selected directly from `pxwalkinconsultation` where column does not exist | Remove from select; resolve via `PatientMasterlist` relationship |
| **Demographics Defaults** | "N/A" for PIN, Ref, Sex, Age | Not fallback-queried when walk-in was created without all fields | Cross-reference with `pxmasterlist` in `SecretaryController` |
| **Laboratory / Radiology Previews** | Link yields 404 Not Found | Set to relative disk path (`radiology_results/...`) instead of preview route | Generate `radiology_url` in backend + `resolvePreviewUrl()` in frontend |
| **Prescriptions List** | Empty if `stocks_ledger` empty | Older visits used `pxrxdocuments` | Merge/fallback to `DoctorMedicinesModel` |
| **Diagnostics List** | Empty if `stocks_ledger` empty | Older visits used `docrequests` | Merge/fallback to `DocRequestsModel` |
| **Billing Charges & Totals** | Populated if ledger exists | PF insertion handles pricing & VAT | Ensure `charges_total` and line item subtotal formatting are guaranteed |
| **PDF Printable Slips** | Direct GET `/print_pdf` works | Multi-role access enabled in `routes/web.php` | Cover with feature tests for `type=rx` and `type=diagnostics` |

