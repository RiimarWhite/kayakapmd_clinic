# Implementation Plan: Fully Activate PhilHealth e-Claims XML Generation & Transmission

Activate and complete the end-to-end PhilHealth e-Claims XML lifecycle in KayakapMD Clinic: from batch selection, Tranche 1 / Tranche 2 XML generation and validation, through AES-256-CBC payload encryption and direct REST transmission to the PhilHealth e-Claims API.

---

## User Review Required

> [!IMPORTANT]
> **API Credentials & Environment**: PhilHealth e-Claims transmission requires an active endpoint (`PHILHEALTH_URL`) and an optional encryption passphrase (`PHILHEALTH_PASSPHRASE`). If not defined in `.env`, the system will use sensible mock/sandbox fallbacks (`https://epress.philhealth.gov.ph` or simulation modes) without crashing.
> 
> **Tranche 1 vs. Tranche 2 Prerequisite**: As enforced by business rules in `AdminService`, Tranche 2 report generation requires that selected patients have an existing Tranche 1 report code (`report_code`).

---

## Proposed Changes

### 1. Backend Controller Methods (`app/Http/Controllers/ManagementController.php`)
Implement the missing controller methods routed by `routes/api.php`:
- `generateXML(Request $request)`:
  - Validates `data` (selected rows array), `type` ('first' or 'second'), `start`, and `end`.
  - Resolves client code from facility profile (`KayakapProfileModel::first()?->clientcode ?? config('app.client_code', '122377')`).
  - Calls `$this->service->generateXML($request->all(), $clientCode)`.
  - Returns `response()->json($result)`.
- `fetchXML(Request $request)`:
  - Fetches the XML document from `XmlTransModel` using `report_code` or `reference`.
  - If not found in `XmlTransModel`, checks `SOAPModel` and related models.
  - Returns `['success' => true, 'document' => $document]`.
- `saveXML(Request $request)`:
  - Confirms or updates status of generated XML report in database.
  - Returns `['success' => true, 'message' => 'XML report saved successfully.']`.
- `encryptXML(Request $request)`:
  - Receives `reference` (report code) and optional `passphrase`.
  - Looks up document content from `XmlTransModel` where `report_code = $reference`.
  - Calls `$this->service->encryptXML(['document' => $document, 'report_code' => $reference, 'passphrase' => $passphrase ?? config('services.philhealth.passphrase', 'kayakapmd')])`.
  - Returns JSON response.
- `decryptXML(Request $request)`:
  - Decrypts AES-256-CBC encrypted payload from `XmlTransModel` when previewing or troubleshooting.
  - Returns `['success' => true, 'document' => $decrypted]`.
- `getXmlData(Request $request)`:
  - Returns HCI Accreditation number (`accreno`) from `PCBModel::first()?->hciaccreno` for filename formatting.

---

### 2. PhilHealth API Client & Controller (`app/Http/Controllers/Api/PhilHealthApiController.php` & `app/Services/YakapManagement/ApiService.php`)
- **`PhilHealthApiController.php`**:
  - Wrap all methods (`generateToken`, `checkRegistration`, `fetchRegistrations`, `checkATC`, `validateXML`, `submitXML`) with `try ... catch` blocks, structured logging (`Log::info`, `Log::error`), and clean `response()->json(...)` returns.
- **`ApiService.php`**:
  - Fix endpoint URL fallback: `config('services.philhealth.url', env('PHILHEALTH_URL', 'https://epress.philhealth.gov.ph'))`.
  - Fix token management: Store and attach the acquired Bearer token or form parameter `Token` cleanly.
  - Improve error reporting so network timeouts or 4xx/5xx responses return structured error messages rather than unhandled raw fatal exceptions.
- **`config/services.php`**:
  - Register PhilHealth configuration section:
    ```php
    'philhealth' => [
        'url' => env('PHILHEALTH_URL', 'https://epress.philhealth.gov.ph'),
        'passphrase' => env('PHILHEALTH_PASSPHRASE', 'kayakapmd'),
    ],
    ```

---

### 3. API Routes Alignment (`routes/api.php`)
- Ensure `Route::post('encrypt_xml', [ManagementController::class, 'encryptXML']);` is active (uncommented).
- Ensure all PhilHealth routes (`generate_xml_first_tranche`, `save_xml_report`, `fetch_xml_data`, `encrypt_xml`, `decrypt_xml`, `get_xml_data`, and PhilHealth API endpoints) are correctly guarded and mapped.

---

### 4. Frontend Reports & PhilHealth Client Wiring
- **`resources/js/pages/admin/philhealth/reports.js`**:
  - Verify and fix table ID references: Fix typo where `#patients_report_table` was used instead of `#patients-report-table` in event handlers (`encrypt`, `xml-select-all`, `btn_save_xml`).
  - Wire XML Transmission button in preview modal: Allow direct submission via `/api/submit_xml` or `/api/validate_xml` with SweetAlert confirmation.
- **`resources/js/pages/admin/philhealth/philhealth-api.js`**:
  - Fix response rendering in `checkRegistration`, `checkATC`, and `fetchRegistrations` with visual alerts (SweetAlert2).
- Recompile assets via `npm run build`.

---

## Verification Plan

### Automated Feature Tests
Create `tests/Feature/PhilHealthXmlAndTransmissionTest.php`:
1. `test_get_xml_data_returns_accreno()`: Verifies endpoint returns PCB accreditation number.
2. `test_generate_xml_first_tranche_generates_valid_xml()`: Seeds test enlistment + SOAP data and verifies valid XML document creation and `XmlTransModel` record insertion.
3. `test_encrypt_xml_encrypts_content()`: Verifies AES-256-CBC encryption and `ENCRYPTED_CONTENT` update in `xml_transmittal_reports`.
4. `test_fetch_xml_data_returns_xml_document()`: Verifies retrieval of generated XML document by report code.
5. `test_philhealth_api_controller_responses()`: Tests JSON structure and error handling of `generate_token`, `validate_xml`, and `submit_xml` endpoints.

### Full Regression Suite
- Run `php artisan test` to verify all existing and new tests pass without regressions.
