# KayakAPMD v2 — Database Data Dictionary

*Auto-generated from `kayakapmdv2.sql` (Navicat MySQL dump, 182 tables, dated 2026-09-16; `lib_brgy` removed as a duplicate of `lib_barangay`). Descriptions below are reconstructed from table/column naming conventions and the sparse inline `COMMENT` strings present in the dump — the dump itself contains no data-dictionary, ER diagram, or business documentation. Anything not explicitly stated in a `COMMENT` is labeled as inferred and should be confirmed with the application team before being treated as ground truth.*

## 1. What this database is

This is the backing database for **KayakAPMD**, a Philippine outpatient clinic / physician-office management system built around **PhilHealth's TSEKAP** primary-care benefit package (Annual Physical Examination, patient enlistment, SOAP consultations, diagnostic testing, and e-claims transmittal). Alongside the PhilHealth-specific module it also handles everyday clinic operations: patient registration, doctor/secretary scheduling, walk-in queueing, point-of-sale billing, pharmacy inventory, and HMO billing. `kayakapmd_profile.clientcode` (mirrored as `dw_clientcode` on most transactional tables) identifies the specific clinic/facility ('tenant') a row belongs to, indicating this schema is shared across multiple facilities.

## 2. How to read this document

Tables are grouped into 15 functional modules (Section 5). Each table entry lists every column with its MySQL type, nullability, default, and any inline comment from the source dump. `NULL`/`NOT NULL` and defaults are taken directly from the DDL, not inferred.

## 3. Global schema observations (read before using this schema)

- **No primary keys or indexes are declared anywhere in this dump.** Not one of the 183 `CREATE TABLE` statements includes a `PRIMARY KEY`, `UNIQUE KEY`, or secondary `INDEX` clause, and most `id` columns are plain nullable `bigint UNSIGNED` rather than `AUTO_INCREMENT` primary keys. Only 6 columns across the whole schema are `AUTO_INCREMENT` (the `dd_advice`-style `id` columns, all `NOT NULL AUTO_INCREMENT`, but even these have no `PRIMARY KEY` clause attached). **This is almost certainly an artifact of how the dump was exported (structure-only dump with keys stripped, or a reporting/staging copy of the production schema)** rather than the live production DDL — production databases with this much foreign-key-style linkage (`pHciCaseNo`, `en_CaseNo`, `s_TransNo`, `px_pin` used as join keys throughout) would not function without primary/unique keys and indexes. Confirm the real production DDL with the DBA before treating this dump as authoritative for schema changes.
- **Only 6 real `FOREIGN KEY` constraints exist in the entire schema**, all on the `dd_diagnosticexamresult`, `dd_medicine`, `dd_profile`, and `dd_soap_consultation` tables, all referencing `dd_enlistment.pHciCaseNo` and/or `dd_soap_consultation.pHciTransNo`. Every other cross-table relationship in this database (the vast majority) is enforced only by application code, via shared business keys with matching names — see Section 4.
- **Two schema "generations" coexist for the same concepts**, e.g. `dd_enlistment` vs. `dd_enlistment_1`, `dd_diagnosticexamresult` vs. `dd_diag_examresult_master`, and each `dd_<test>` exam table vs. its `dd_diag_<test>` counterpart. The `_1`/`dd_diag_*` versions consistently add `dw_clientcode`, `px_pin`, `en_CaseNo`/`s_TransNo`, and billing columns (`charge_transcode`, `clinic_cost`, `entry_type`) that the originals lack. This looks like a schema migration/refactor in progress rather than two independently-used feature sets — confirm which version the current application code writes to before building on either.
- **`diagnostic_category` + `diagnostics_masterlist` vs. `dw_lib_diagnostic` vs. `lib_diagnostic` vs. `dd_diagnostic`** all touch "diagnostic" naming but appear to serve different purposes (billing category, billing masterlist, physical-exam-form picklist, geographic-tied lookup, and a patient-level diagnostic record, respectively) — verify with the team rather than assuming redundancy.
- **Encoding**: table/column definitions use `utf8mb4`, but a handful of inline `COMMENT` strings in the source dump contain corrupted curly-quote characters (e.g. `dStatus` comments render as `â€œDâ€\x9d` in the raw dump). This dictionary has normalized those to plain quotes; the underlying stored `COMMENT` metadata in the database itself may still be corrupted and is cosmetic only (it does not affect stored patient data).
- **`pastudents`** (Section 5, Module 15) is a student-enrollment table unrelated to any other table's domain — flagged for the app owner to confirm it belongs in this schema.

## 4. Key shared columns used as de-facto join keys

These columns are **not** declared as foreign keys (except where noted) but recur, identically named, across dozens of tables and are how the application almost certainly joins data:

| Column | Meaning (inferred) | Appears on |
|---|---|---|
| `dw_clientcode` / `clientcode` | Clinic/facility (tenant) identifier | Most transactional and reference tables |
| `pHciCaseNo` / `en_CaseNo` / `dCaseNo` / `caseno` | TSEKAP enlistment case number (identifies a patient's benefit-package enlistment episode) | `dd_enlistment`, `dd_profile`, `dd_soap_consultation`, `dd_diag_*`, `pxwalkinconsultation` |
| `pHciTransNo` / `s_TransNo` / `dTransNo` | TSEKAP transaction/consultation number — identifies a specific SOAP consultation visit | `dd_soap_consultation`, `dd_diag_*`, `dd_medicine`, `dd_document` |
| `px_pin` / `pxrefno` / `pincode` | Patient identifier (PhilHealth PIN or internal patient code) | `pxmasterlist`, `pxwalkinconsultation`, `dd_diag_*`, `stocks_ledger`, `phic_charges` |
| `pMemPin` / `dMemPin` | PhilHealth *member* PIN (may differ from the patient/dependent's own PIN) | `dd_enlistment`, `dd_profile`, `dd_soap_consultation` |
| `docrefno` / `docrefno` | Doctor reference number | `doctors`, `doctorcharges`, `pxcharges`, `docschedules`, `pxwalkinconsultation` |
| `consultationrefno` | Walk-in consultation reference | `pxwalkinconsultation`, `pxcharges`, `pxrxdocuments`, `pxsettlements`, `walkinconsultation_answer` |
| `secrefno` | Secretary/front-office staff reference | `secretaryrights`, `secretary_doctor`, `docquestion` |
| `prodcode` | Pharmacy/stock item code | `stocks_listing`, `stocks_ledger`, `dd_diag_*` |

## 5. Modules & Tables

| # | Module | Tables |
|---|---|---|
| 1 | System / Framework (Laravel) Tables | 8 |
| 2 | Facility & Access Configuration | 3 |
| 3 | Doctors, Secretaries & Staff | 12 |
| 4 | Patient Master Data & Walk-in Consultations | 4 |
| 5 | Billing, Charges & Settlements | 9 |
| 6 | Pharmacy & Inventory | 2 |
| 7 | PhilHealth TSEKAP — Patient Enlistment & APE Profile (`dd_*` core) | 34 |
| 8 | PhilHealth TSEKAP — SOAP Consultation Notes | 19 |
| 9 | PhilHealth TSEKAP — Diagnostic / Laboratory Exam Results | 17 |
| 10 | PhilHealth TSEKAP — Diagnostic Results (billing-enriched / transmittal copies) | 21 |
| 11 | `dd_` Module Reference Libraries | 4 |
| 12 | `dw_lib_` Clinical Reference Libraries | 34 |
| 13 | Geographic Reference (PSGC) | 7 |
| 14 | PhilHealth / HMO Compliance & e-Claims Transmittal | 8 |
| 15 | Uncategorized / Possibly Unrelated | 1 |

## 1. System / Framework (Laravel) Tables

Standard Laravel framework plumbing tables. Not clinic-specific — used for caching, queued jobs, schema migrations history, and web sessions. Safe to ignore for clinical/business logic.

### `cache`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `key` | varchar(191) | NULL | NULL |  |
| `value` | mediumtext | NOT NULL |  |  |
| `expiration` | int | NULL | NULL |  |

### `cache_locks`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `key` | varchar(191) | NULL | NULL |  |
| `owner` | varchar(191) | NULL | NULL |  |
| `expiration` | int | NULL | NULL |  |

### `failed_jobs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `uuid` | varchar(191) | NULL | NULL |  |
| `connection` | text | NOT NULL |  |  |
| `queue` | text | NOT NULL |  |  |
| `payload` | longtext | NOT NULL |  |  |
| `exception` | longtext | NOT NULL |  |  |
| `failed_at` | timestamp | NOT NULL | CURRENT_TIMESTAMP |  |

### `job_batches`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | varchar(191) | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `name` | varchar(191) | NULL | NULL |  |
| `total_jobs` | int | NULL | NULL |  |
| `pending_jobs` | int | NULL | NULL |  |
| `failed_jobs` | int | NULL | NULL |  |
| `failed_job_ids` | longtext | NOT NULL |  |  |
| `options` | mediumtext | NULL |  |  |
| `cancelled_at` | int | NULL | NULL |  |
| `created_at` | int | NULL | NULL |  |
| `finished_at` | int | NULL | NULL |  |

### `jobs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `queue` | varchar(191) | NULL | NULL |  |
| `payload` | longtext | NOT NULL |  |  |
| `attempts` | tinyint UNSIGNED | NULL | NULL |  |
| `reserved_at` | int UNSIGNED | NULL | NULL |  |
| `available_at` | int UNSIGNED | NULL | NULL |  |
| `created_at` | int UNSIGNED | NULL | NULL |  |

### `migrations`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int UNSIGNED | NULL | NULL |  |
| `migration` | varchar(191) | NULL | NULL |  |
| `batch` | int | NULL | NULL |  |

### `sessions`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | varchar(191) | NULL | NULL |  |
| `user_id` | bigint UNSIGNED | NULL | NULL |  |
| `ip_address` | varchar(45) | NULL | NULL |  |
| `user_agent` | text | NULL |  |  |
| `payload` | longtext | NOT NULL |  |  |
| `last_activity` | int | NULL | NULL |  |
| `clientcode` | varchar(191) | NULL | NULL |  |

### `token_info`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `clientcode` | varchar(50) | NULL | NULL |  |
| `userid` | varchar(50) | NULL | NULL |  |
| `username` | varchar(80) | NULL | NULL |  |
| `initiated` | datetime | NULL | NULL |  |
| `status` | tinyint | NULL | NULL | 1 success 0 unsuccessful |

## 2. Facility & Access Configuration

Tenant/facility-level configuration for a single clinic ('client') using this system, plus top-level admin accounts. `kayakapmd_profile` is the master configuration record for a facility (module toggles, PhilHealth/DOH facility codes, SMTP settings, YAKAP benefit ceilings, etc.). `clientcode` / `dw_clientcode` (used throughout almost every other table) identifies which facility/tenant a row belongs to, implying this is a multi-tenant (multi-clinic) deployment.

### `kayakapmd_profile`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `clientcode` | varchar(12) | NULL | NULL | assign code unque for everyclient |
| `facility_type` | varchar(30) | NULL | NULL |  |
| `reports_address` | varchar(200) | NULL | NULL |  |
| `reports_citymunprov` | varchar(200) | NULL | NULL |  |
| `reports_contactnumber` | varchar(200) | NULL | NULL |  |
| `EMR_cert_number` | varchar(80) | NULL | NULL |  |
| `EMR_cert_issuance` | date | NULL | NULL |  |
| `EMR_ID` | varchar(20) | NULL | NULL | ELECTRONIC MEDICAL RECORD PROVIDER ID |
| `HOSP_NAME` | varchar(50) | NULL | NULL | HOSPITAL NAME |
| `HOSP_ADDBRGY` | varchar(3) | NULL | NULL | BARANGAY ADDRESS OF FACILITY |
| `HOSP_ADDMUN` | varchar(2) | NULL | NULL | CITY OR MUNICIPALITY ADDRESS OF FACILITY |
| `HOSP_ADDPROV` | varchar(2) | NULL | NULL | PROVINCE ADDRESS OF FACILITY |
| `HOSP_ADDREG` | varchar(2) | NULL | NULL | REGION ADDRESS OF FACILITY |
| `HOSP_ADDZIPCODE` | varchar(4) | NULL | NULL | ZIP CODE ADDRESS OF FACILITY |
| `HOSP_ADDLHIO` | varchar(2) | NULL | NULL | LHIO CODE ADDRESS OF FACILITY |
| `HOSP_CLASS` | varchar(50) | NULL | NULL | HOSPITAL CLASSIFICATION |
| `url_admin` | varchar(200) | NULL | NULL |  |
| `url_secretary` | varchar(200) | NULL | NULL |  |
| `url_doctor` | varchar(200) | NULL | NULL |  |
| `url_port` | varchar(30) | NULL | NULL |  |
| `database_name` | varchar(200) | NULL | NULL |  |
| `database_pw_base` | longtext | NULL |  |  |
| `ownership_type` | enum('SOLE PROPRIETOR','PARTNERSHIP','SOLO CORPORATION','CORPORATION','LGU','COOPERATIVE') | NULL | NULL |  |
| `businessgroup_name` | varchar(80) | NULL | NULL |  |
| `SECTOR` | varchar(1) | NULL | NULL | SECTOR OF FACILITY |
| `EMAIL_ADD` | varchar(50) | NULL | NULL | EMAIL ADDRESS OF THE FACILITY |
| `TIN` | varchar(15) | NULL | NULL | TIN NUMBER OF THE FACILITY |
| `TEL_NO` | varchar(20) | NULL | NULL | TELEPHONE NUMBER OF THE FACILITY |
| `TELEFAX` | varchar(20) | NULL | NULL | TELEFAX OF THE FACILITY |
| `DATE_REGISTERED` | date | NOT NULL |  | DATE REGISTERED |
| `tokenreceived` | varchar(200) | NULL | NULL |  |
| `tokendatetime` | datetime | NULL | NULL |  |
| `cipher_key` | varchar(50) | NULL | NULL | PASSPHRASE/CIPHER KEY TO BE USED IN GENERATION OF REPORTS |
| `enable_yakap` | tinyint | NULL | NULL |  |
| `enable_consultation` | tinyint | NULL | NULL |  |
| `enable_secretary` | tinyint | NULL | NULL |  |
| `enable_que` | tinyint | NULL | NULL |  |
| `enable_cashier` | tinyint | NULL | NULL |  |
| `enable_pharmacy` | tinyint | NULL | NULL |  |
| `enable_laboratory` | tinyint | NULL | NULL |  |
| `enable_radiology` | tinyint | NULL | NULL |  |
| `admin_name` | varchar(80) | NULL | NULL |  |
| `corp_secretary` | varchar(80) | NULL | NULL |  |
| `phic_incharge` | varchar(80) | NULL | NULL |  |
| `accountant` | varchar(80) | NULL | NULL |  |
| `patient_pix_directory` | varchar(200) | NULL | NULL |  |
| `attachments_directory` | varchar(200) | NULL | NULL |  |
| `attachments_max_size_mb` | double(11, 2) | NULL | NULL |  |
| `yakap_max_amount` | double(11, 2) | NULL | NULL |  |
| `yakap_max_copay` | double(11, 2) | NULL | NULL |  |
| `smtp` | varchar(80) | NULL | NULL |  |
| `smtp_port` | varchar(5) | NULL | NULL |  |
| `smtp_emailadd` | varchar(80) | NULL | NULL |  |
| `email_pk` | varchar(50) | NULL | NULL |  |

### `hci_profile`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `hci_accre_no` | varchar(9) | NULL | NULL |  |
| `hci_username` | varchar(30) | NULL | NULL |  |
| `hci_password` | varchar(30) | NULL | NULL |  |
| `company_name` | varchar(191) | NOT NULL |  |  |
| `company_brgy` | varchar(191) | NULL | NULL |  |
| `company_mun` | varchar(191) | NULL | NULL |  |
| `company_prov` | varchar(191) | NULL | NULL |  |
| `company_region` | varchar(191) | NULL | NULL |  |
| `company_zipcode` | varchar(191) | NULL | NULL |  |
| `company_email` | varchar(191) | NULL | NULL |  |
| `company_mobilenumber` | varchar(30) | NULL | NULL |  |
| `company_telephone` | varchar(30) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

### `adminrights`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `adminrefno` | varchar(191) | NULL | NULL |  |
| `username` | varchar(191) | NULL | NULL |  |
| `adminfname` | varchar(100) | NULL | NULL | Admin first name |
| `adminmname` | varchar(100) | NULL | NULL | Admin middle name |
| `adminlname` | varchar(100) | NULL | NULL | Admin last name |
| `admincontactno` | varchar(25) | NULL | NULL | Contact mobile number |
| `adminemail` | varchar(100) | NULL | NULL | Administrative email address |
| `password` | varchar(191) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |
| `clientcode` | varchar(191) | NULL | NULL |  |

## 3. Doctors, Secretaries & Staff

Physician and front-office (secretary) staff records: credentials, schedules, service/consultation fee lists, and doctor-to-secretary assignments.

### `doctors`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `doccd` | int | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `doccode` | varchar(10) | NULL | NULL |  |
| `docrefno` | varchar(25) | NULL | NULL |  |
| `doclname` | varchar(25) | NULL | NULL |  |
| `docfname` | varchar(35) | NULL | NULL |  |
| `docmname` | varchar(25) | NULL | NULL |  |
| `suffix` | varchar(3) | NULL | NULL |  |
| `titlename` | varchar(5) | NULL | NULL |  |
| `docname` | varchar(70) | NULL | NULL |  |
| `adrs` | varchar(70) | NULL | NULL |  |
| `emailadd` | varchar(80) | NULL | NULL |  |
| `S2no` | varchar(20) | NULL | NULL |  |
| `PTR` | varchar(20) | NULL | NULL |  |
| `Licno` | varchar(20) | NULL | NULL |  |
| `phicno` | varchar(30) | NULL | NULL |  |
| `tin` | varchar(30) | NULL | NULL |  |
| `phicenable` | tinyint(1) | NULL | NULL |  |
| `phicrate` | double(11, 2) | NULL | NULL |  |
| `pfrate` | double(11, 2) | NULL | NULL |  |
| `lastupdate` | datetime | NULL | NULL |  |
| `proftype` | varchar(50) | NULL | NULL |  |
| `disabletext` | int | NULL | NULL |  |
| `cellno` | varchar(11) | NULL | NULL |  |
| `catg` | varchar(3) | NULL | NULL |  |
| `recid` | int | NULL | NULL |  |
| `recby` | varchar(50) | NULL | NULL |  |
| `station` | varchar(50) | NULL | NULL |  |
| `groupname` | varchar(3) | NULL | NULL |  |
| `coacode` | varchar(20) | NULL | NULL |  |
| `accountno` | varchar(50) | NULL | NULL |  |
| `tax` | double(11, 2) | NULL | NULL |  |
| `issuehospOR` | tinyint | NULL | NULL |  |
| `expertise` | varchar(150) | NULL | NULL |  |
| `clinichours` | longtext | NULL |  |  |
| `otherinfo` | longtext | NULL |  |  |
| `biodata` | longtext | NULL |  |  |
| `clinicroom` | varchar(120) | NULL | NULL |  |
| `quevisible` | tinyint | NULL | NULL |  |
| `profgroup` | varchar(30) | NULL | NULL |  |
| `autoAddVAT` | tinyint | NULL | NULL |  |
| `VAT` | double(11, 2) | NULL | NULL |  |
| `allowtextresult` | tinyint | NULL | NULL |  |
| `allowdocsystem` | tinyint | NULL | NULL |  |
| `phicexpiry` | date | NULL | NULL |  |
| `licnoexpiry` | date | NULL | NULL |  |
| `status` | tinyint | NULL | NULL |  |
| `statusreason` | varchar(120) | NULL | NULL |  |
| `vatable` | tinyint | NULL | NULL |  |
| `vatrate` | double(11, 2) | NULL | NULL |  |
| `rodrate` | double(11, 2) | NULL | NULL |  |
| `phicname` | varchar(120) | NULL | NULL |  |
| `department` | varchar(120) | NULL | NULL |  |
| `docfirst` | varchar(80) | NULL | NULL |  |

### `doctorsrights`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NOT NULL | AUTO_INCREMENT | Primary Key |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `docrefno` | varchar(25) | NULL | NULL |  |
| `doclname` | varchar(25) | NULL | NULL |  |
| `docfname` | varchar(35) | NULL | NULL |  |
| `docmname` | varchar(25) | NULL | NULL |  |
| `suffix` | varchar(3) | NULL | NULL |  |
| `titlename` | varchar(5) | NULL | NULL |  |
| `username` | varchar(25) | NULL | NULL |  |
| `pass` | varchar(255) | NULL | NULL |  |
| `eadd` | varchar(25) | NULL | NULL |  |
| `mnumber` | varchar(15) | NULL | NULL |  |
| `tin` | varchar(50) | NULL | NULL |  |
| `address` | varchar(200) | NULL | NULL |  |
| `slcode` | varchar(25) | NULL | NULL |  |
| `taxpercent` | varchar(25) | NULL | NULL |  |
| `bankacct` | varchar(50) | NULL | NULL |  |
| `updateID` | int | NULL | NULL |  |
| `updated` | timestamp | NULL | NULL |  |
| `updatedby` | varchar(50) | NULL | NULL |  |
| `Adminsys` | int | NULL | NULL |  |
| `mobileapp` | int | NULL | NULL |  |
| `logged` | int | NULL | NULL |  |
| `status` | varchar(20) | NULL | NULL |  |
| `expertise` | varchar(100) | NULL | NULL |  |
| `proftype` | varchar(100) | NULL | NULL |  |
| `doctype` | tinyint | NULL | NULL |  |
| `docmgmt` | tinyint | NULL | NULL |  |
| `logged_in` | tinyint | NULL | NULL |  |
| `consultationfee` | float(10, 2) | NULL | NULL |  |

### `doctorsgroup`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `group_name` | varchar(100) | NULL | NULL |  |
| `clinic_start_time` | time | NULL | NULL |  |
| `clinic_end_time` | time | NULL | NULL |  |
| `clinic_schedule` | varchar(100) | NULL | NULL |  |
| `consultation_fee` | double | NULL | NULL |  |
| `updated_by` | varchar(60) | NULL | NULL |  |
| `updated_date` | datetime | NULL | NULL |  |

### `doctorservices`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `servicerefno` | varchar(100) | NULL | NULL |  |
| `servicename` | varchar(100) | NULL | NULL |  |
| `servicedscr` | varchar(100) | NULL | NULL |  |
| `servicecharge` | double | NULL | NULL |  |
| `category` | varchar(100) | NULL | NULL |  |
| `docrefno` | varchar(100) | NULL | NULL |  |

### `doctorcharges`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `docrefno` | varchar(100) | NULL | NULL |  |
| `transdate` | datetime | NULL | NULL |  |
| `docname` | varchar(100) | NULL | NULL |  |
| `servicerefno` | varchar(100) | NULL | NULL |  |
| `servicename` | varchar(100) | NULL | NULL |  |
| `quantity` | int | NULL | NULL |  |
| `total` | float | NULL | NULL |  |
| `discount` | float | NULL | NULL |  |
| `net_total` | float | NULL | NULL |  |
| `paymentrefno` | varchar(100) | NULL | NULL |  |
| `consultationrefno` | varchar(100) | NULL | NULL |  |
| `charge` | varchar(100) | NULL | NULL |  |
| `paymentmethod` | varchar(100) | NULL | NULL |  |

### `doctorsrights`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NOT NULL | AUTO_INCREMENT | Primary Key |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `docrefno` | varchar(25) | NULL | NULL |  |
| `doclname` | varchar(25) | NULL | NULL |  |
| `docfname` | varchar(35) | NULL | NULL |  |
| `docmname` | varchar(25) | NULL | NULL |  |
| `suffix` | varchar(3) | NULL | NULL |  |
| `titlename` | varchar(5) | NULL | NULL |  |
| `username` | varchar(25) | NULL | NULL |  |
| `pass` | varchar(255) | NULL | NULL |  |
| `eadd` | varchar(25) | NULL | NULL |  |
| `mnumber` | varchar(15) | NULL | NULL |  |
| `tin` | varchar(50) | NULL | NULL |  |
| `address` | varchar(200) | NULL | NULL |  |
| `slcode` | varchar(25) | NULL | NULL |  |
| `taxpercent` | varchar(25) | NULL | NULL |  |
| `bankacct` | varchar(50) | NULL | NULL |  |
| `updateID` | int | NULL | NULL |  |
| `updated` | timestamp | NULL | NULL |  |
| `updatedby` | varchar(50) | NULL | NULL |  |
| `Adminsys` | int | NULL | NULL |  |
| `mobileapp` | int | NULL | NULL |  |
| `logged` | int | NULL | NULL |  |
| `status` | varchar(20) | NULL | NULL |  |
| `expertise` | varchar(100) | NULL | NULL |  |
| `proftype` | varchar(100) | NULL | NULL |  |
| `doctype` | tinyint | NULL | NULL |  |
| `docmgmt` | tinyint | NULL | NULL |  |
| `logged_in` | tinyint | NULL | NULL |  |
| `consultationfee` | float(10, 2) | NULL | NULL |  |

### `secretary_doctor`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `docrefno` | varchar(60) | NULL | NULL |  |
| `secrefno` | varchar(60) | NULL | NULL |  |
| `recordedby` | varchar(60) | NULL | NULL |  |
| `recordeddate` | datetime | NULL | NULL |  |
| `active` | tinyint | NULL | NULL |  |

### `secretaryrights`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NOT NULL | AUTO_INCREMENT | Primary Key |
| `secrefno` | varchar(100) | NULL | NULL |  |
| `secidno` | varchar(100) | NULL | NULL |  |
| `username` | varchar(100) | NULL | NULL | Secretary login username |
| `secpassword` | varchar(100) | NULL | NULL |  |
| `secfname` | varchar(100) | NULL | NULL |  |
| `secmname` | varchar(100) | NULL | NULL |  |
| `seclname` | varchar(100) | NULL | NULL |  |
| `secsuffix` | varchar(10) | NULL | NULL |  |
| `secbday` | date | NULL | NULL |  |
| `secgender` | varchar(10) | NULL | NULL |  |
| `secadrs` | longtext | NULL |  |  |
| `seccontactno` | varchar(20) | NULL | NULL |  |
| `secemail` | varchar(50) | NULL | NULL |  |
| `recordedby` | varchar(100) | NULL | NULL |  |
| `recordeddate` | datetime | NULL | NULL |  |
| `logged` | tinyint | NULL | NULL |  |
| `verifieddate` | datetime | NULL | NULL |  |
| `verified` | tinyint | NULL | NULL |  |
| `clientcode` | varchar(191) | NULL | NULL |  |

### `profgroup_masterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `profession_group` | varchar(80) | NULL | NULL |  |

### `docschedules`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `schedrefno` | varchar(191) | NULL | NULL |  |
| `docrefno` | varchar(191) | NULL | NULL |  |
| `day` | enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') | NULL | NULL |  |
| `start` | time | NOT NULL |  |  |
| `end` | time | NOT NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `docquestion`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `docquestionrefno` | varchar(60) | NULL | NULL |  |
| `secrefno` | varchar(60) | NULL | NULL |  |
| `question` | longtext | NULL |  |  |
| `docrefno` | varchar(50) | NULL | NULL |  |
| `docname` | varchar(100) | NULL | NULL |  |
| `recordeddate` | datetime | NULL | NULL |  |
| `recordedby` | varchar(50) | NULL | NULL |  |

### `docrequests`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `consultationrefno` | varchar(191) | NULL | NULL |  |
| `docrefno` | varchar(191) | NULL | NULL |  |
| `requestrefno` | varchar(191) | NULL | NULL |  |
| `request_dscr` | varchar(191) | NULL | NULL |  |
| `request_catg` | varchar(191) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

## 4. Patient Master Data & Walk-in Consultations

The front-desk / EMR core: the patient master list, the walk-in consultation queue/encounter record (vitals, triage, diagnosis, referral, YAKAP flags), prescriptions handed to patients, and generic Q&A captured during a consultation.

### `pxmasterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `pxrefno` | varchar(50) | NULL | NULL |  |
| `pincode` | varchar(21) | NULL | NULL |  |
| `patientname` | varchar(180) | NULL | NULL |  |
| `pxlastname` | varchar(80) | NULL | NULL |  |
| `pxfirstname` | varchar(80) | NULL | NULL |  |
| `pxmidname` | varchar(80) | NULL | NULL |  |
| `pxsuffix` | varchar(10) | NULL | NULL |  |
| `phic_pin` | varchar(30) | NULL | NULL |  |
| `ipd_pincode` | varchar(50) | NULL | NULL | inpatient pin |
| `gender` | varchar(255) | NULL | NULL |  |
| `birthday` | date | NULL | NULL |  |
| `age` | double(11, 2) | NULL | NULL |  |
| `religion` | varchar(80) | NULL | NULL |  |
| `nationality` | varchar(80) | NULL | NULL |  |
| `mobilenumber` | varchar(20) | NULL | NULL |  |
| `emailaddress` | varchar(60) | NULL | NULL |  |
| `address` | varchar(300) | NULL | NULL |  |
| `streetadrs` | varchar(80) | NULL | NULL |  |
| `brgy` | varchar(80) | NULL | NULL |  |
| `muncity` | varchar(80) | NULL | NULL |  |
| `province` | varchar(80) | NULL | NULL |  |
| `zipcode` | varchar(10) | NULL | NULL |  |
| `region` | varchar(80) | NULL | NULL |  |
| `country` | varchar(80) | NULL | NULL |  |
| `ispwd` | tinyint | NULL | NULL |  |
| `last_consultation` | date | NULL | NULL |  |
| `last_enlistcode` | varchar(50) | NULL | NULL |  |
| `last_docrefno` | varchar(50) | NULL | NULL |  |
| `last_docname` | varchar(130) | NULL | NULL |  |
| `classification` | varchar(120) | NULL | NULL |  |
| `followupdate` | date | NULL | NULL |  |
| `followupcheckup` | tinyint(1) | NULL | NULL |  |
| `recordedby` | varchar(80) | NULL | NULL |  |
| `recordeddate` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `canaccess_online` | tinyint(1) | NULL | NULL |  |
| `allow_emailnotification` | tinyint(1) | NULL | NULL |  |
| `allow_sms` | tinyint(1) | NULL | NULL |  |
| `senior_idno` | varchar(80) | NULL | NULL |  |

### `pxwalkinconsultation`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `source_data` | enum('ONLINE','QUELINE','SECRETARY','DOCTOR','ADMIN') | NULL | NULL | Channel through which the consultation originated (ONLINE portal, QUELINE kiosk, SECRETARY desk, DOCTOR console, or ADMIN console) |
| `consultationrefno` | varchar(50) | NULL | NULL |  |
| `pxrefno` | varchar(50) | NULL | NULL |  |
| `pincode` | varchar(21) | NULL | NULL |  |
| `caseno` | varchar(21) | NULL | NULL |  |
| `en_transno` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `soap_caseno` | varchar(21) | NULL | NULL |  |
| `patientname` | varchar(180) | NULL | NULL |  |
| `pxlastname` | varchar(80) | NULL | NULL |  |
| `pxfirstname` | varchar(80) | NULL | NULL |  |
| `pxmidname` | varchar(80) | NULL | NULL |  |
| `pxsuffix` | varchar(10) | NULL | NULL |  |
| `phic_pin` | varchar(30) | NULL | NULL |  |
| `secretary_note` | longtext | NULL |  |  |
| `infectious_risk_type` | varchar(80) | NULL | NULL |  |
| `docrefno` | varchar(50) | NULL | NULL |  |
| `docname` | varchar(120) | NULL | NULL |  |
| `doctors_infectious_risk` | enum('NONE','LOW','MID','HIGH','SEVERLY HIGH') | NULL | NULL |  |
| `gender` | varchar(255) | NULL | NULL |  |
| `birthday` | date | NULL | NULL |  |
| `age` | tinyint | NULL | NULL |  |
| `doccoaopd` | varchar(50) | NULL | NULL |  |
| `mobilenumber` | varchar(20) | NULL | NULL |  |
| `emailaddress` | varchar(60) | NULL | NULL |  |
| `classification` | varchar(80) | NULL | NULL |  |
| `subclassification` | varchar(80) | NULL | NULL |  |
| `weight` | double(10, 2) | NULL | NULL |  |
| `wunit` | varchar(10) | NULL | NULL |  |
| `height` | double(10, 2) | NULL | NULL |  |
| `hunit` | varchar(10) | NULL | NULL |  |
| `temp` | double(10, 2) | NULL | NULL |  |
| `tempunit` | varchar(10) | NULL | NULL |  |
| `requesteddate` | datetime | NULL | NULL |  |
| `requestedby` | varchar(100) | NULL | NULL |  |
| `consulted` | tinyint | NULL | NULL |  |
| `consulteddate` | datetime | NULL | NULL |  |
| `paymentrefno` | varchar(50) | NULL | NULL |  |
| `reasonforconsultation` | longtext | NULL |  |  |
| `impression` | longtext | NULL |  |  |
| `finadiagnosis` | longtext | NOT NULL |  |  |
| `pe_evaluation` | longtext | NULL |  |  |
| `followup` | tinyint | NULL | NULL |  |
| `verifiedpaymentdate` | datetime | NULL | NULL |  |
| `verifiedby` | varchar(50) | NULL | NULL |  |
| `followupdate` | datetime | NULL | NULL |  |
| `respiratoryrate` | float(20, 2) | NULL | NULL |  |
| `pulserate` | float(20, 2) | NULL | NULL |  |
| `bpnumerator` | float(20, 2) | NULL | NULL |  |
| `bpdenominator` | float(20, 2) | NULL | NULL |  |
| `followupcheckup` | tinyint(1) | NULL | NULL |  |
| `modeofpayment` | varchar(50) | NULL | NULL |  |
| `doctorsgroup` | varchar(50) | NULL | NULL |  |
| `transactionstat` | tinyint | NULL | NULL |  |
| `transactionstatby` | varchar(50) | NULL | NULL |  |
| `transactionstatdate` | datetime | NULL | NULL |  |
| `forward_payment_status` | tinyint(1) | NULL | NULL |  |
| `deny_reason` | varchar(500) | NULL | NULL |  |
| `secrefno` | varchar(60) | NULL | NULL |  |
| `recordedby` | varchar(255) | NULL | NULL |  |
| `recordeddate` | datetime | NULL | NULL |  |
| `instructions` | varchar(100) | NULL | NULL |  |
| `radiologypath` | varchar(255) | NULL | NULL |  |
| `laboratorypath` | varchar(255) | NULL | NULL |  |
| `status` | enum('PENDING','FOR CONFIRMATION','WAITING','IN_CONSULTATION','FOR_BILLING','COMPLETED','CANCELLED','NO_SHOW','UNSCHEDULED') | NULL | NULL | Workflow state: PENDING, FOR CONFIRMATION, WAITING, IN_CONSULTATION, FOR_BILLING, COMPLETED, CANCELLED, NO_SHOW, UNSCHEDULED |
| `consultation_date` | datetime | NULL | NULL |  |
| `queueno` | varchar(100) | NULL | NULL |  |
| `lastpayreferenceno` | varchar(35) | NULL | NULL |  |
| `gravida` | double(11, 0) | NULL | NULL |  |
| `para` | double(11, 0) | NULL | NULL |  |
| `abortion` | double(11, 0) | NULL | NULL |  |
| `iufd` | double(11, 0) | NULL | NULL |  |
| `died` | double(11, 0) | NULL | NULL |  |
| `pathologic` | tinyint | NULL | NULL |  |
| `linkaccount` | varchar(20) | NULL | NULL |  |
| `infacilitydelivery` | tinyint | NULL | NULL |  |
| `hmocode` | varchar(80) | NULL | NULL |  |
| `hmoname` | varchar(80) | NULL | NULL |  |
| `slcode` | varchar(30) | NULL | NULL |  |
| `inbound_referral` | tinyint | NULL | NULL |  |
| `inbound_referredby_md` | varchar(80) | NULL | NULL |  |
| `outbound_referral` | tinyint | NULL | NULL |  |
| `outbound_referredby_md` | varchar(80) | NULL | NULL |  |
| `foradmit` | tinyint | NULL | NULL |  |
| `foradmit_instructions` | longtext | NULL |  |  |
| `foryakap` | tinyint | NULL | NULL |  |
| `yakap_validatedby` | varchar(80) | NULL | NULL |  |
| `yakap_validated` | datetime | NULL | NULL |  |
| `forcheckup_confirmedby` | varchar(80) | NULL | NULL |  |
| `forcheckup_confirmed` | datetime | NULL | NULL |  |
| `photo_path` | varchar(100) | NULL | NULL |  |

### `pxrxdocuments`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `rxreferenceno` | varchar(50) | NULL | NULL |  |
| `consultationrefno` | varchar(50) | NULL | NULL |  |
| `medicinecode` | varchar(100) | NULL | NULL |  |
| `medicinename` | varchar(100) | NULL | NULL |  |
| `medicinedosage` | varchar(100) | NULL | NULL |  |
| `medicineduration` | varchar(50) | NULL | NULL |  |
| `medicinequantity` | varchar(50) | NULL | NULL |  |
| `createddate` | datetime | NULL | NULL |  |
| `createdby` | varchar(50) | NULL | NULL |  |
| `docrefno` | varchar(50) | NULL | NULL |  |
| `sentdate` | datetime | NULL | NULL |  |
| `sentby` | varchar(50) | NULL | NULL |  |
| `templatename` | varchar(50) | NULL | NULL |  |

### `walkinconsultation_answer`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `walkinconsuanswerrefno` | varchar(60) | NULL | NULL |  |
| `questionrefno` | varchar(60) | NULL | NULL |  |
| `answer` | longtext | NULL |  |  |
| `pxconsultationrefno` | varchar(60) | NULL | NULL |  |
| `transactedby` | varchar(100) | NULL | NULL |  |
| `transacteddate` | datetime | NULL | NULL |  |

## 5. Billing, Charges & Settlements

Point-of-care billing: charge line items posted against a doctor/consultation, per-encounter financial settlement/reconciliation (splitting the bill across VAT, senior/PWD discount, HMO, PhilHealth, and payment method), and the master price/category lists charges are drawn from.

### `pxcharges`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `transactiontype` | enum('CHARGES','VOID','DISCOUNT','PAYMENTS','COLLECTIONS') | NULL | NULL |  |
| `docrefno` | varchar(100) | NULL | NULL |  |
| `transdate` | datetime | NULL | NULL |  |
| `docname` | varchar(100) | NULL | NULL |  |
| `phic_lib_id` | varchar(100) | NULL | NULL |  |
| `servicerefno` | varchar(100) | NULL | NULL |  |
| `servicename` | varchar(100) | NULL | NULL |  |
| `vatable` | tinyint | NULL | NULL |  |
| `retail` | double(11, 2) | NULL | NULL |  |
| `quantity` | double(11, 2) | NULL | NULL |  |
| `total` | float | NULL | NULL |  |
| `discount` | float | NULL | NULL |  |
| `net_total` | float | NULL | NULL |  |
| `paymentrefno` | varchar(100) | NULL | NULL |  |
| `payment_type` | enum('HMO','PHIC','POCKET') | NULL | NULL |  |
| `consultationrefno` | varchar(100) | NULL | NULL |  |
| `pxcode_pin` | varchar(50) | NULL | NULL |  |
| `pxname` | varchar(120) | NULL | NULL |  |
| `group_category_id` | varchar(5) | NULL | NULL | DRUGS / LAB / XRAY / CTSCAN / CSR / OTHERS |
| `group_category` | varchar(100) | NULL | NULL | DRUGS and MEDS / LABORATORY  / XRAY / CTSCAN / CENTRAL SUPPLIES / OTHERS |
| `paymentmethod` | varchar(100) | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `pxsettlements`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `transactionrefno` | varchar(50) | NULL | NULL |  |
| `consultationrefno` | varchar(50) | NULL | NULL |  |
| `pincode` | varchar(50) | NULL | NULL |  |
| `docrefno` | varchar(50) | NULL | NULL |  |
| `docname` | varchar(120) | NULL | NULL |  |
| `total_doctorspf` | double(11, 2) | NULL | NULL |  |
| `total_vaccines` | double(11, 2) | NULL | NULL |  |
| `total_immunizations` | double(11, 2) | NULL | NULL |  |
| `total_meds` | double(11, 2) | NULL | NULL |  |
| `total_lab` | double(11, 2) | NULL | NULL |  |
| `total_xray` | double(11, 2) | NULL | NULL |  |
| `total_procedures` | double(11, 2) | NULL | NULL |  |
| `total_supplies` | double(11, 2) | NULL | NULL |  |
| `total_others` | double(11, 2) | NULL | NULL |  |
| `total_gross` | double(11, 2) | NULL | NULL |  |
| `less_vat` | double(11, 2) | NULL | NULL |  |
| `less_srpwd` | double(11, 2) | NULL | NULL |  |
| `less_hmo` | double(11, 2) | NULL | NULL |  |
| `less_phic` | double(11, 2) | NULL | NULL |  |
| `less_govt` | double(11, 2) | NULL | NULL |  |
| `less_discount` | double(11, 2) | NULL | NULL |  |
| `net_payable` | double(11, 2) | NULL | NULL |  |
| `payment_cash` | double(11, 2) | NULL | NULL |  |
| `payment_card` | double(11, 2) | NULL | NULL |  |
| `cta_type` | varchar(50) | NULL | NULL | Card transaction subtype (cc for Credit Card, dc for Debit Card) |
| `payment_wallet` | double(11, 2) | NULL | NULL |  |
| `payment_pn` | double(11, 2) | NULL | NULL |  |
| `hmocode` | varchar(50) | NULL | NULL |  |
| `hmoname` | varchar(180) | NULL | NULL |  |
| `hmo_type` | varchar(80) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `cashierbatch` | varchar(50) | NULL | NULL |  |
| `cashier_date` | date | NULL | NULL |  |
| `cashiername` | varchar(80) | NULL | NULL |  |
| `journalcode` | varchar(80) | NULL | NULL |  |
| `slcode` | varchar(50) | NULL | NULL |  |

### `charges_category`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `categoryrefno` | varchar(191) | NULL | NULL |  |
| `categoryname` | varchar(191) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

### `charges_masterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `chargerefno` | varchar(191) | NULL | NULL |  |
| `charge_name` | varchar(191) | NULL | NULL |  |
| `charge_category` | varchar(191) | NULL | NULL |  |
| `charge_amount` | double | NOT NULL |  |  |

### `servicesgroups`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `servicegroup_refno` | varchar(100) | NULL | NULL |  |
| `servicegroup_name` | varchar(100) | NULL | NULL |  |
| `servicegroup_dscr` | varchar(100) | NULL | NULL |  |

### `medicine_masterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `medicine_refno` | varchar(191) | NULL | NULL |  |
| `medicine_name` | varchar(191) | NULL | NULL |  |
| `philhealth_refno` | varchar(191) | NULL | NULL |  |
| `rate` | double | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

### `diagnostic_category`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `category_refno` | varchar(191) | NULL | NULL |  |
| `category_name` | varchar(191) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

### `diagnostics_masterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `diagnosticrefno` | varchar(191) | NULL | NULL |  |
| `diagnostic_name` | varchar(191) | NULL | NULL |  |
| `diagnostic_catg` | varchar(191) | NULL | NULL |  |
| `created_at` | datetime | NULL | NULL |  |
| `updated_at` | datetime | NULL | NULL |  |

### `hmo_masterlist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `hmocode` | varchar(50) | NULL | NULL |  |
| `hmoname` | varchar(120) | NULL | NULL |  |
| `hmoaddress` | varchar(200) | NULL | NULL |  |
| `coacode` | varchar(30) | NULL | NULL |  |
| `accre_no` | varchar(80) | NULL | NULL |  |
| `hmotype` | enum('HMO','COMPANY','GOVERNMENT') | NULL | NULL |  |

## 6. Pharmacy & Inventory

Drug/medical-supply catalog (`stocks_listing`) and the transactional stock movement ledger (`stocks_ledger`) that records dispensing, charges, returns and voids against that catalog.

### `stocks_listing`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `item_grouping` | enum('DRUGS AND MEDS','SUPPLIES','PROCEDURES','DIAGNOSTIC','IMAGING','PROFESSIONAL FEE') | NULL | NULL |  |
| `prodcode` | varchar(20) | NULL | NULL | unquecode / YYYY#####    (year+seqno) |
| `phic_reference_code` | varchar(80) | NULL | NULL | reference on drugcode and lab/cray code |
| `drug_generic` | varchar(80) | NULL | NULL |  |
| `drug_brand` | varchar(80) | NULL | NULL |  |
| `drug_dosage` | varchar(80) | NULL | NULL | 12g/10ml |
| `drug_preperation` | varchar(80) | NULL | NULL |  |
| `drug_add_dscr` | varchar(80) | NULL | NULL |  |
| `drug_grouping` | enum('DRUGS AND MEDS','MEDICAL SUPPLIES') | NULL | NULL |  |
| `drug_type` | varchar(30) | NULL | NULL | dw_lib_drugtype / NDC ANTIBIOTIC VACCINE OTHERS |
| `drug_prescription_type` | enum('OTC','RX_REGULAR','RX_DANGEROUS') | NULL | NULL | Over the Counter (OTC) |
| `unit` | varchar(20) | NULL | NULL |  |
| `prod_itemdscr` | varchar(220) | NULL | NULL | Paracetamol - Biogesic 500mg Tab |
| `oecb_code` | varchar(50) | NULL | NULL | for ER use only |
| `oecb_price` | double(11, 2) | NULL | NULL | for ER use only |
| `yakap_essential` | tinyint | NULL | NULL |  |
| `yakap_essential_code` | varchar(50) | NULL | NULL | use if item is under yakap ESSENTIAL |
| `yakap_essential_price` | double(11, 2) | NULL | NULL | use if item is under yakap ESSENTIAL |
| `pndf_enable` | tinyint | NULL | NULL | PNDF |
| `phic_enable` | tinyint | NULL | NULL |  |
| `last_delivery_no` | varchar(50) | NULL | NULL |  |
| `last_delivery_date` | date | NULL | NULL |  |
| `last_delivery_cost` | double(11, 2) | NULL | NULL |  |
| `cost_ave` | double(11, 2) | NULL | NULL |  |
| `price_regular` | double(11, 2) | NULL | NULL |  |
| `price_phic` | double(11, 2) | NULL | NULL |  |
| `price_hmo` | double(11, 2) | NULL | NULL |  |
| `price_others` | double(11, 2) | NULL | NULL |  |
| `is_inventory` | tinyint | NULL | NULL |  |
| `qty` | double(11, 2) | NULL | NULL |  |
| `qty_level_reorder` | double(11, 2) | NULL | NULL |  |
| `po_code_lastdelivery` | varchar(50) | NULL | NULL |  |
| `supplierid` | varchar(50) | NULL | NULL |  |
| `suppliername` | varchar(120) | NULL | NULL |  |
| `remarks` | varchar(120) | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `stocks_ledger`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint unsigned | NO | auto_increment | Primary key (auto-incrementing ledger entry ID) |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `transactiontype` | enum('CHARGES','RETURNS','VOID','PAYMENTS','DISCOUNT','COLLECTION') | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_transno` | varchar(21) | NULL | NULL |  |
| `patient_name` | varchar(120) | NULL | NULL |  |
| `prodcode` | varchar(20) | NULL | NULL | unquecode / YYYY#####    (year+seqno) |
| `phic_reference_code` | varchar(80) | NULL | NULL | reference on drugcode and lab/cray code |
| `item_dscr` | varchar(220) | NULL | NULL | Paracetamol - Biogesic 500mg Tab |
| `price_type` | varchar(30) | NULL | NULL |  |
| `yakap_essential` | tinyint | NULL | NULL |  |
| `yakap_essential_price` | double(11, 2) | NULL | NULL | use if item is under yakap ESSENTIAL |
| `hmocode` | varchar(50) | NULL | NULL |  |
| `hmoname` | varchar(120) | NULL | NULL |  |
| `pndf_enable` | tinyint | NULL | NULL | PNDF |
| `phic_enable` | tinyint | NULL | NULL |  |
| `cost_ave` | double(11, 2) | NULL | NULL |  |
| `retails` | double(11, 2) | NULL | NULL |  |
| `qty` | double(11, 2) | NULL | NULL |  |
| `unit` | varchar(20) | NULL | NULL |  |
| `vatamt` | double(11, 2) | NULL | NULL |  |
| `totalamt` | double(11, 2) | NULL | NULL |  |
| `item_grouping` | enum('DRUGS AND MEDS','SUPPLIES','PROCEDURES','DIAGNOSTIC','IMAGING','PROFESSIONAL FEE') | NULL | NULL |  |
| `sub_grouping` | varchar(50) | NULL | NULL |  |
| `remarks` | varchar(120) | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `dispenseby` | varchar(80) | NULL | NULL |  |
| `dispensed` | datetime | NULL | NULL |  |
| `dispensed_status` | enum('PENDING','RELEASED','CANCELLED') | NULL | NULL |  |

## 7. PhilHealth TSEKAP — Patient Enlistment & APE Profile (`dd_*` core)

`dd_` tables implement PhilHealth's TSEKAP primary-care / Annual Physical Examination (APE) benefit package data model (column names such as `pHciCaseNo`, `pHciTransNo`, `pMemPin`, `pPatientType MM/DD`, `pATC`, package types P/E/K map directly to PhilHealth TSEKAP e-forms). `dd_enlistment` is the patient/member enlistment record for the benefit package; `dd_profile` and its ~14 `dd_profile_*` satellite tables capture the structured medical-history intake form (one satellite table per history section). `dd_enlistment_1` and `dd_diagnosticexamresult` look like newer/alternate-schema versions of `dd_enlistment` / result headers (see 'Observations' below).

### `dd_enlistment`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `pHciCaseNo` | varchar(21) | NOT NULL |  |  |
| `pHciTransNo` | varchar(21) | NOT NULL |  |  |
| `pEffYear` | varchar(4) | NOT NULL |  |  |
| `pEnlistStat` | enum('1','2','3') | NOT NULL |  |  |
| `pEnlistDate` | date | NOT NULL |  |  |
| `pPackageType` | enum('P','E','K') | NOT NULL |  |  |
| `pMemPin` | varchar(191) | NOT NULL |  |  |
| `pMemFname` | varchar(30) | NOT NULL |  |  |
| `pMemMname` | varchar(30) | NULL | NULL |  |
| `pMemLname` | varchar(30) | NOT NULL |  |  |
| `pMemExtname` | varchar(30) | NULL | NULL |  |
| `pMemDob` | date | NOT NULL |  |  |
| `pPatientPin` | varchar(12) | NOT NULL |  |  |
| `pPatientFname` | varchar(12) | NOT NULL |  |  |
| `pPatientMname` | varchar(30) | NOT NULL |  |  |
| `pPatientLname` | varchar(30) | NOT NULL |  |  |
| `pPatientExtname` | varchar(30) | NULL | NULL |  |
| `pPatientSex` | enum('F','M') | NOT NULL |  |  |
| `pPatientDob` | date | NOT NULL |  |  |
| `pPatientType` | enum('MM','DD') | NOT NULL |  |  |
| `pPatientMobileNo` | varchar(15) | NOT NULL |  |  |
| `pPatientLandlineNo` | varchar(15) | NULL | NULL |  |
| `pWithConsent` | enum('Y','N','X') | NOT NULL |  |  |
| `pTransDate` | date | NOT NULL |  |  |
| `pCreatedBy` | varchar(30) | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_enlistment_1`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `dCaseNo` | varchar(21) | NULL | NULL |  |
| `dTransNo` | varchar(21) | NULL | NULL |  |
| `dEffyear` | varchar(4) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(100) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `dEnlistStat` | enum('1','2','3') | NULL | NULL | 1Active 2cancelled 3transferred |
| `dEnlistDate` | date | NOT NULL |  |  |
| `dPackageType` | enum('P','E','K') | NULL | NULL |  |
| `dMemPin` | varchar(191) | NULL | NULL |  |
| `dMemFname` | varchar(30) | NULL | NULL |  |
| `dMemMname` | varchar(30) | NULL | NULL |  |
| `dMemLname` | varchar(30) | NULL | NULL |  |
| `dMemExtname` | varchar(30) | NULL | NULL |  |
| `dMemDob` | date | NOT NULL |  |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientFname` | varchar(12) | NULL | NULL |  |
| `dPatientMname` | varchar(30) | NULL | NULL |  |
| `dPatientLname` | varchar(30) | NULL | NULL |  |
| `dPatientExtname` | varchar(30) | NULL | NULL |  |
| `patientname` | varchar(200) | NULL | NULL |  |
| `dPatientSex` | enum('F','M') | NULL | NULL |  |
| `dPatientDob` | date | NOT NULL |  |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dPatientMobileNo` | varchar(15) | NULL | NULL |  |
| `dPatientLandlineNo` | varchar(15) | NULL | NULL |  |
| `dWithConsent` | enum('Y','N','X') | NULL | NULL |  |
| `dTransDate` | date | NOT NULL |  |  |
| `created` | datetime | NULL | NULL |  |
| `dCreatedBy` | varchar(30) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `updated` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `for_payment` | varchar(1) | NULL | NULL | FLAG FOR PAYMENT (Y=YES; N=NO) |
| `with_LOA` | varchar(1) | NULL | NULL | FLAG FOR WITH LETTER OF AUTHORIZATION (Y=YES; N=NO) |
| `with_consent` | varchar(1) | NULL | NULL | FLAG FOR WITH CONSENT (Y=YES; N=NO) |
| `date_cancelled` | datetime | NULL | NULL | DATE WHEN USER CANCELLED THE PROFILE |
| `cancelledby` | varchar(20) | NULL | NULL | USER WHO CANCELLED THE PROFILE |
| `date_transferred` | datetime | NULL | NULL | DATE WHEN USER TRANSFERRED THE PROFILE |
| `transferredby` | varchar(20) | NULL | NULL | USER WHO TRANSFERRED THE PROFILE TO OTHER INSTITUTION |
| `transferred_emr_provider_id` | varchar(20) | NULL | NULL | ELECTRONIC MEDICAL RECORD PROVIDER ID |
| `transferred_facilitycode` | varchar(20) | NULL | NULL | DOH Facility Code of the Health Facility |
| `is_dependent_valid` | varchar(1) | NULL | NULL | 1-IF VALID DEPENDENT; 0-INVALID DEPENDENT (1- TRUE; 0-FALSE) |
| `with_disability` | varchar(1) | NULL | NULL | 1-WITH DISABILITY; 0-W/OUT DISABILITY(1- TRUE; 0-FALSE) |
| `dependent_type` | varchar(6) | NULL | NULL |  |
| `avail_free_service` | varchar(1) | NULL | NULL | Y - YES; N - NO |
| `XPS_MODULE` | varchar(50) | NULL | NULL |  |
| `report_trans_no` | varchar(25) | NULL | NULL | TRANSMITTAL NUMBER ON THE GENERATED REPORT |
| `cf4_claimid_no` | varchar(20) | NULL | NULL | CF4 - CLAIM ID NUMBER |
| `cf4_hci_transmittal_no` | varchar(20) | NULL | NULL | CF4 - HCI ECLAIMS TRANSMITAL ID NUMBER PER CLAIM |
| `px_mobileno` | varchar(15) | NULL | NULL |  |
| `px_landline` | varchar(15) | NULL | NULL |  |
| `px_emailadd` | varchar(80) | NULL | NULL |  |

### `dd_profile`

**Declared foreign keys (real SQL constraints):**
- CONSTRAINT `dd_profile_phcicaseno_foreign` FOREIGN KEY (`pHciCaseNo`) REFERENCES `dd_enlistment` (`pHciCaseNo`) ON DELETE RESTRICT ON UPDATE RESTRICT

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `pHciTransNo` | varchar(21) | NOT NULL |  |  |
| `pHciCaseNo` | varchar(21) | NOT NULL |  |  |
| `pProfDate` | date | NOT NULL |  |  |
| `pPatientPin` | varchar(12) | NOT NULL |  |  |
| `pPatientType` | enum('MM','DD') | NOT NULL |  |  |
| `pPatientAge` | varchar(191) | NOT NULL |  |  |
| `pMemPin` | varchar(12) | NOT NULL |  |  |
| `pEffyear` | varchar(4) | NOT NULL |  |  |
| `pATC` | varchar(10) | NOT NULL |  |  |
| `pIsWalkedIn` | enum('Y','N') | NOT NULL |  |  |
| `pTransDate` | date | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL |  |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_profile_1`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `dTransNo` | varchar(21) | NULL | NULL | Prefix = P |
| `en_CaseNo` | varchar(21) | NULL | NULL | source elistment table |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `dProfDate` | date | NOT NULL |  |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `patientname` | varchar(200) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dPatientAge` | varchar(191) | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffyear` | varchar(4) | NULL | NULL |  |
| `dATC` | varchar(10) | NULL | NULL |  |
| `dIsWalkedIn` | enum('Y','N') | NULL | NULL |  |
| `dTransDate` | date | NOT NULL |  |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemakrs` | text | NULL |  |  |
| `updated` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `date_cancelled` | datetime | NULL | NULL | DATE WHEN USER CANCELLED THE PROFILE |
| `date_transferred` | datetime | NULL | NULL | DATE WHEN USER TRANSFERRED THE PROFILE |
| `cancelledby` | varchar(20) | NULL | NULL | USER WHO CANCELLED THE PROFILE |
| `transferredby` | varchar(20) | NULL | NULL | USER WHO TRANSFERRED THE PROFILE TO OTHER INSTITUTION |
| `date_for_payment` | datetime | NULL | NULL | DATE WHEN FLAGGED FOR PAYMENT |
| `for_paymentby` | varchar(20) | NULL | NULL | USER WHO FLAGGED FOR PAYMENT |
| `remarks` | varchar(100) | NULL | NULL | FURTHER REMARKS |
| `presc_type` | int | NULL | NULL |  |
| `profile_otp` | varchar(10) | NULL | NULL | OTP FOR PATIENT RECORD |
| `report_trans_no` | varchar(25) | NULL | NULL | TRANSMITTAL NUMBER ON THE GENERATED REPORT |
| `is_finalize` | varchar(1) | NULL | NULL | Y - YES; N- NO |
| `xps_module` | varchar(50) | NULL | NULL | Module where the data came from |
| `with_atc` | varchar(1) | NULL | NULL | Y - with ATC; N - without ATC |

### `dd_profile_bloodtype`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dBloodType` | enum('A+','B+','AB+','O+','A-','B-','AB-','O-','') | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_famhist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dMdiseaseCode` | varchar(3) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL | Validated Unvalidated Failed |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_fhspecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dMdiseaseCode` | varchar(3) | NULL | NULL |  |
| `dSpecificDesc` | varchar(2000) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_immunization`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dChildImmcode` | varchar(3) | NULL | NULL |  |
| `dYoungwImmcode` | varchar(3) | NULL | NULL |  |
| `dPregwImmcode` | varchar(3) | NULL | NULL |  |
| `dElderlyImmcode` | varchar(3) | NULL | NULL |  |
| `dOtherImm` | varchar(2000) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_medhist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dMdiseaseCode` | varchar(3) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL | Verified Unvalidated Failed |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_menshist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dMenarchePeriod` | float UNSIGNED | NULL | NULL |  |
| `dLastMensPeriod` | date | NULL | NULL |  |
| `dPeriodDuration` | float UNSIGNED | NULL | NULL |  |
| `dMensInterval` | float UNSIGNED | NULL | NULL |  |
| `dPadsPerDay` | float UNSIGNED | NULL | NULL |  |
| `dOnsetSexIc` | float UNSIGNED | NULL | NULL |  |
| `dBirthCtrlMethod` | varchar(21) | NULL | NULL |  |
| `dIsMenopause` | enum('Y','N','') | NULL | NULL |  |
| `dMenopauseAge` | float UNSIGNED | NULL | NULL |  |
| `dIsApplicable` | enum('Y','N') | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_mhspecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `dMdiseaseCode` | varchar(3) | NULL | NULL |  |
| `dSpecificDesc` | varchar(2000) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |

### `dd_profile_ncdqans`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `dQid1_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid2_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid3_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid4_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid5_Ynx` | enum('Y','N','X') | NULL | NULL |  |
| `dQid6_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid7_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid8_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid9_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid10_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid11_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid12_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid13_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid14_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid15_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid16_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid17_abcde` | enum('A','B','C','D','E') | NULL | NULL | lib_ncdq / A: <10% / B: 10–20% / C: 20–30% / D: 30–40% / E: >=40% |
| `dQid18_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid19_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid19_Fbsmg` | varchar(50) | NULL | NULL | Answer for QID19: FBS/RBS in mg/dL |
| `dQid19_Fbsmmol` | varchar(50) | NULL | NULL | Answer for QID19: FBS/RBS in mmol/L |
| `dQid19_Fbsdate` | date | NULL | NULL |  |
| `dQid20_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid20_Choleval` | varchar(3) | NULL | NULL |  |
| `dQid20_Choledate` | date | NULL | NULL | Answer for QID20: Date when is the FBS/RBS Value was taken |
| `dQid21_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid21_Ketonval` | varchar(3) | NULL | NULL |  |
| `dQid21_Ketondate` | date | NULL | NULL | Answer for QID21: Date when is the Urine Ketones Value was taken |
| `dQid22_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid22_Proteinval` | varchar(3) | NULL | NULL |  |
| `dQid22_Proteindate` | date | NULL | NULL | Answer for QID22: Date when the Urine Protein Value was taken |
| `dQid23_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dQid24_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_pegensurvey`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dGenSurveyId` | enum('1','2','') | NULL | NULL |  |
| `dGenSurveyRem` | varchar(2000) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_pemisc`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSkinId` | varchar(3) | NULL | NULL | lib_skin_extremities |
| `dHeentId` | varchar(3) | NULL | NULL | lib_heent |
| `dChestId` | varchar(3) | NULL | NULL | lib_chest |
| `dHeartId` | varchar(3) | NULL | NULL | lib_heart |
| `dAbdomenId` | varchar(3) | NULL | NULL | lib_abdomen |
| `dNeuroId` | varchar(3) | NULL | NULL | lib_neuro |
| `dRectalId` | varchar(3) | NULL | NULL | lib_digital_rectal |
| `dGuId` | varchar(3) | NULL | NULL | lib_genitourinary |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_pepert`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSystolic` | float UNSIGNED | NOT NULL |  |  |
| `dDiastolic` | float UNSIGNED | NOT NULL |  |  |
| `dHr` | float UNSIGNED | NOT NULL |  |  |
| `dRr` | float UNSIGNED | NOT NULL |  |  |
| `dTemp` | float UNSIGNED | NOT NULL |  | Temperature in Celsius |
| `dHeight` | float UNSIGNED | NOT NULL |  | Height of Patient in cm |
| `dWeight` | float UNSIGNED | NOT NULL |  | Weight of Patient in kg |
| `dBMI` | float UNSIGNED | NOT NULL |  |  |
| `dZScore` | varchar(10) | NULL | NULL |  |
| `dLeftVision` | varchar(12) | NULL | NULL |  |
| `dRightVision` | varchar(12) | NULL | NULL |  |
| `dLength` | float UNSIGNED | NULL | NULL | Length of Patient in cm - for Pediatric                 Patient only age 0-24 Months |
| `dHeadCirc` | float UNSIGNED | NULL | NULL |  |
| `dSkinfoldThickness` | float UNSIGNED | NULL | NULL |  |
| `dWaist` | float UNSIGNED | NULL | NULL |  |
| `dHip` | float UNSIGNED | NULL | NULL |  |
| `dLimbs` | float UNSIGNED | NULL | NULL |  |
| `dMidUpperArmCirc` | float UNSIGNED | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_pespecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSkinRem` | text | NULL |  |  |
| `dHeentRem` | text | NULL |  |  |
| `dChestRem` | text | NULL |  |  |
| `dHeartRem` | text | NULL |  |  |
| `dAbdomenRem` | text | NULL |  |  |
| `dNeuroRem` | text | NULL |  |  |
| `dRectalRem` | text | NULL |  |  |
| `dGuRem` | text | NULL |  |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_preghist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dPregCnt` | float UNSIGNED | NULL | NULL |  |
| `dDeliveryCnt` | float UNSIGNED | NULL | NULL |  |
| `dDeliveryTyp` | enum('N','O','B','X','') | NULL | NULL |  |
| `dFullTermCnt` | float UNSIGNED | NULL | NULL |  |
| `dPrematureCnt` | float UNSIGNED | NULL | NULL |  |
| `dAbortionCnt` | float UNSIGNED | NULL | NULL |  |
| `dLivChildrenCnt` | float UNSIGNED | NULL | NULL |  |
| `dWPregIndhyp` | enum('Y','N','') | NULL | NULL |  |
| `dWFamPlan` | enum('Y','N','') | NULL | NULL |  |
| `dIsApplicable` | enum('Y','N','') | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F','') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_sochist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dIsSmoker` | enum('Y','N','X') | NULL | NULL |  |
| `dNoCigpk` | int | NULL | NULL |  |
| `dIsADrinker` | enum('Y','N','X') | NULL | NULL |  |
| `dNoBottles` | int | NULL | NULL |  |
| `dIllDrugUser` | enum('Y','N') | NULL | NULL |  |
| `dIsSexuallyActive` | enum('Y','N') | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_profile_surghist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `p_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSurgDesc` | varchar(500) | NULL | NULL |  |
| `dSurgDate` | date | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_ncdqans`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pQid1_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid2_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid3_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid4_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid5_Yn` | enum('Y','N','X') | NULL | NULL |  |
| `pQid6_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid7_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid8_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid9_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid10_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid11_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid12_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid13_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid14_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid15_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid16_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid17_Yn` | enum('A','B','C','D','E') | NULL | NULL | lib_ncdq / A: <10% / B: 10–20% / C: 20–30% / D: 30–40% / E: >=40% |
| `pQid18_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid19_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid19_Fbsmg` | varchar(50) | NULL | NULL | Answer for QID19: FBS/RBS in mg/dL |
| `pQid19_Fbsmmol` | varchar(50) | NULL | NULL | Answer for QID19: FBS/RBS in mmol/L |
| `pQid19_Fbsdate` | date | NULL | NULL |  |
| `pQid20_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid20_Choleval` | varchar(3) | NULL | NULL |  |
| `pQid20_Choledate` | date | NULL | NULL | Answer for QID20: Date when is the FBS/RBS Value was taken |
| `pQid21_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid21_Ketonval` | varchar(3) | NULL | NULL |  |
| `pQid21_Ketondate` | date | NULL | NULL | Answer for QID21: Date when is the Urine Ketones Value was taken |
| `pQid22_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid22_Proteinval` | varchar(3) | NULL | NULL |  |
| `pQid22_Proteindate` | date | NULL | NULL | Answer for QID22: Date when the Urine Protein Value was taken |
| `pQid23_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pQid24_Yn` | enum('Y','N') | NULL | NULL | lib_ncdq |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_famhist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pMdiseaseCode` | varchar(100) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_fhspecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pMdiseaseCode` | varchar(100) | NULL | NULL |  |
| `pSpecificDesc` | text | NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_medhist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pMdiseaseCode` | varchar(100) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_menshist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pMenarchePeriod` | int UNSIGNED | NULL | NULL |  |
| `pLastMensPeriod` | date | NULL | NULL |  |
| `pPeriodDuration` | int UNSIGNED | NULL | NULL |  |
| `pMensInterval` | int UNSIGNED | NULL | NULL |  |
| `pPadsPerDay` | int UNSIGNED | NULL | NULL |  |
| `pOnsetSexIc` | int UNSIGNED | NULL | NULL |  |
| `pBirthCtrlMethod` | varchar(21) | NULL | NULL |  |
| `pIsMenopause` | enum('Y','N') | NULL | NULL |  |
| `pMenopauseAge` | int UNSIGNED | NULL | NULL |  |
| `pIsApplicable` | enum('Y','N') | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_mhspecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pMdiseaseCode` | varchar(100) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_preghist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pPregCnt` | int UNSIGNED | NULL | NULL |  |
| `pDeliveryCnt` | int UNSIGNED | NULL | NULL |  |
| `pDeliveryTyp` | enum('N','O','B','X') | NULL | NULL |  |
| `pFullTermCnt` | int UNSIGNED | NULL | NULL |  |
| `pPrematureCnt` | int UNSIGNED | NULL | NULL |  |
| `pAbortionCnt` | int UNSIGNED | NULL | NULL |  |
| `pLivChildrenCnt` | int UNSIGNED | NULL | NULL |  |
| `pWPregIndhyp` | enum('Y','N') | NULL | NULL |  |
| `pWFamPlan` | enum('Y','N') | NOT NULL |  |  |
| `pIsApplicable` | enum('Y','N') | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_sochist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pIsSmoker` | enum('Y','N','X') | NOT NULL |  |  |
| `pNoCigpk` | int | NULL | NULL |  |
| `pIsADrinker` | enum('Y','N','X') | NOT NULL |  |  |
| `pNoBottles` | int | NULL | NULL |  |
| `pIllDrugUser` | enum('Y','N') | NOT NULL |  |  |
| `pIsSexuallyActive` | enum('Y','N') | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_surghist`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pSurgDesc` | text | NULL |  |  |
| `pSurgDate` | date | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_pegensurvey`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pGenSurveyId` | enum('1','2') | NULL | NULL |  |
| `pGenSurveyRem` | text | NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_pemisc`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pSkinId` | varchar(3) | NULL | NULL | lib_skin_extremities |
| `pHeentId` | varchar(3) | NULL | NULL | lib_heent |
| `pChestId` | varchar(3) | NULL | NULL | lib_chest |
| `pHeartId` | varchar(3) | NULL | NULL | lib_heart |
| `pAbdomenId` | varchar(3) | NULL | NULL | lib_abdomen |
| `pNeuroId` | varchar(3) | NULL | NULL | lib_neuro |
| `pRectalId` | varchar(3) | NULL | NULL | lib_digital_rectal |
| `pGuId` | varchar(3) | NULL | NULL | lib_genitourinary |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_pepert`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pSystolic` | int UNSIGNED | NOT NULL |  |  |
| `pDiastolic` | int UNSIGNED | NOT NULL |  |  |
| `pHr` | int UNSIGNED | NOT NULL |  |  |
| `pRr` | int UNSIGNED | NOT NULL |  |  |
| `pTemp` | int UNSIGNED | NOT NULL |  | Temperature in Celsius |
| `pHeight` | int UNSIGNED | NOT NULL |  | Height of Patient in cm |
| `pWeight` | int UNSIGNED | NOT NULL |  | Weight of Patient in kg |
| `pBMI` | int UNSIGNED | NOT NULL |  |  |
| `pZScore` | varchar(10) | NULL | NULL |  |
| `pLeftVision` | varchar(12) | NULL | NULL |  |
| `pRightVision` | varchar(12) | NULL | NULL |  |
| `pLength` | int UNSIGNED | NULL | NULL | Length of Patient in cm - for Pediatric                 Patient only age 0-24 Months |
| `pHeadCirc` | int UNSIGNED | NULL | NULL |  |
| `pSkinfoldThickness` | int UNSIGNED | NULL | NULL |  |
| `pWaist` | int UNSIGNED | NULL | NULL |  |
| `pHip` | int UNSIGNED | NULL | NULL |  |
| `pLimbs` | int UNSIGNED | NULL | NULL |  |
| `pMidUpperArmCirc` | int UNSIGNED | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_pespecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pSkinRem` | text | NULL |  |  |
| `pHeentRem` | text | NULL |  |  |
| `pChestRem` | text | NULL |  |  |
| `pHeartRem` | text | NULL |  |  |
| `pAbdomenRem` | text | NULL |  |  |
| `pNeuroRem` | text | NULL |  |  |
| `pRectalRem` | text | NULL |  |  |
| `pGuRem` | text | NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_bloodtype`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pBloodType` | enum('A+','B+','AB+','O+','A-','B-','AB-','O-') | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_immunization`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pChildImmcode` | varchar(100) | NULL | NULL |  |
| `pYoungwImmcode` | varchar(100) | NULL | NULL |  |
| `pPregwImmcode` | varchar(100) | NULL | NULL |  |
| `pElderlyImmcode` | varchar(100) | NULL | NULL |  |
| `pOtherImm` | text | NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

## 8. PhilHealth TSEKAP — SOAP Consultation Notes

The clinical SOAP (Subjective / Objective / Assessment / Plan) consultation note filed for a TSEKAP encounter, plus its child detail tables (ICD diagnoses given, medicines/management prescribed, advice given, physical-exam findings recorded during that specific consultation).

### `dd_soap_consultation`

**Declared foreign keys (real SQL constraints):**
- CONSTRAINT `dd_soap_consultation_phcicaseno_foreign` FOREIGN KEY (`pHciCaseNo`) REFERENCES `dd_enlistment` (`pHciCaseNo`) ON DELETE RESTRICT ON UPDATE RESTRICT

**Relationship documented in source comments (not a DB constraint):**
- `pHciCaseNo` → `dd_enlistment.pHciCaseNo`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `pHciTransNo` | varchar(21) | NOT NULL |  | S+ACCRE_NO+YYY                     Y+MM+5 digits                     series number =                     S+XXXXXXXXX+YY                     YY+MM+99999 |
| `pHciCaseNo` | varchar(21) | NOT NULL |  | FK -> dd_enlistment.pHciCaseNo |
| `pSoapDate` | date | NOT NULL |  | YYYY-MM-DD |
| `pPatientPin` | varchar(12) | NOT NULL |  |  |
| `pPatientType` | enum('MM','DD') | NOT NULL |  |  |
| `pMemPin` | varchar(12) | NOT NULL |  |  |
| `pEffYear` | varchar(4) | NOT NULL |  | Effectivity Year |
| `pATC` | varchar(10) | NOT NULL |  | Authorization Transaction Code                                                 Note: Use ‘WALKEDIN’ as value if                                                 pWalkedIn is ‘Y’ |
| `pIsWalkedIn` | enum('Y','N') | NOT NULL |  | Is Patient Walked In |
| `pCoPay` | varchar(15) | NOT NULL |  | Patient Co-Payment Amount |
| `pTransDate` | date | NOT NULL |  | YYYY-MM-DD |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_soap_1`

**Relationship documented in source comments (not a DB constraint):**
- `en_CaseNo` → `dd_enlistment.pHciCaseNo`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `pHciTransNo` | varchar(21) | NULL | NULL | S+ACCRE_NO+YYY                     Y+MM+5 digits                     series number =                     S+XXXXXXXXX+YY                     YY+MM+99999 |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL | FK -> dd_enlistment.pHciCaseNo |
| `dSoapDate` | date | NOT NULL |  | YYYY-MM-DD |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffYear` | varchar(4) | NULL | NULL | Effectivity Year |
| `dATC` | varchar(10) | NULL | NULL | Authorization Transaction Code                                                 Note: Use ‘WALKEDIN’ as value if                                                 pWalkedIn is ‘Y’ |
| `dIsWalkedIn` | enum('Y','N') | NULL | NULL | Is Patient Walked In |
| `dCoPay` | double(11, 2) | NULL | NULL | Patient Co-Payment Amount |
| `dTransDate` | date | NOT NULL |  | YYYY-MM-DD |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `services_made` | varchar(180) | NULL | NULL | dd_soap_services_type |
| `total_payable` | double(11, 2) | NULL | NULL |  |
| `cta_others` | double(11, 2) | NULL | NULL |  |
| `cta_phic` | double(11, 2) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `status` | enum('PENDING','CONSULTED','CANCELLED') | NULL | NULL |  |
| `report_code` | varchar(80) | NULL | NULL | tranche first report code |
| `report_date` | date | NULL | NULL | tranche first report date |
| `report_code2` | varchar(80) | NULL | NULL | tranche Second report code |
| `report_date2` | date | NULL | NULL | tranche second report date |

### `dd_soap_subjective`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `chifcomplaint` | varchar(2000) | NULL | NULL |  |
| `dIllnessHistory` | text | NOT NULL |  | History of Patient Illnesses |
| `dSignsSymptoms` | text | NOT NULL |  | Pertinent Signs and Symptoms on Admission ID |
| `dOtherComplaint` | text | NULL |  | Other Complaint                             Note: Required if X is included in                             pSignsSymptoms |
| `dPainSite` | text | NULL |  | Site of Pain if Pain Element in                         Pertinent Signs and Symptoms on                         Admission is checked                         Note: Required if 38 is included in                         pSignsSymptoms |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_diagnostic`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dDiagnosticId` | varchar(3) | NULL | NULL |  |
| `dOthRemarks` | text | NULL |  | 500 |
| `dIsPhysicianRecommend` | enum('Y','N','X') | NULL | NULL |  |
| `dPatientRemarks` | enum('RQ','RF','XX') | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `prodcode` | varchar(50) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_icd`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dIcdCode` | varchar(10) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `seq_no` | int | NULL | NULL | sequence of display in dianosis |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_medicine`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL | enlistment table T |
| `s_TransNo` | varchar(21) | NULL | NULL | SOAP table - S |
| `prodcode` | varchar(50) | NULL | NULL |  |
| `prod_itemdscr` | varchar(220) | NULL | NULL |  |
| `dCategory` | varchar(50) | NULL | NULL |  |
| `dDrugCode` | varchar(30) | NULL | NULL |  |
| `dGenericCode` | varchar(5) | NULL | NULL |  |
| `dSaltCode` | varchar(5) | NULL | NULL |  |
| `dStrengthCode` | varchar(5) | NULL | NULL |  |
| `dFormCode` | varchar(5) | NULL | NULL |  |
| `dUnitCode` | varchar(5) | NULL | NULL |  |
| `dPackageCode` | varchar(5) | NULL | NULL |  |
| `dOtherMedicine` | varchar(500) | NULL | NULL | 500 |
| `dOthMedDrugGrouping` | enum('NCD','ANTIBIOTIC','OTHERS') | NULL | NULL |  |
| `dRoute` | varchar(500) | NULL | NULL |  |
| `dQuantity` | double(11, 2) | NULL | NULL |  |
| `dActualUnitPrice` | double(11, 2) | NULL | NULL |  |
| `dTotalAmtPrice` | double(11, 2) | NULL | NULL |  |
| `dInstructionQuantity` | varchar(50) | NULL | NULL |  |
| `dInstructionStrength` | varchar(50) | NULL | NULL |  |
| `dInstructionFrequency` | varchar(50) | NULL | NULL |  |
| `dInstructionPhysician` | varchar(200) | NULL | NULL |  |
| `dIsDispensed` | varchar(1) | NULL | NULL |  |
| `dDateDispensed` | date | NULL | NULL |  |
| `dDispensingPersonnel` | varchar(200) | NULL | NULL |  |
| `dIsApplicable` | enum('Y','N') | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `retail_price` | double(11, 2) | NULL | NULL |  |
| `qty` | double(11, 2) | NULL | NULL |  |
| `total_amt` | double(11, 2) | NULL | NULL |  |
| `chargetype` | enum('CTP','PHIC','HMO','CTA') | NULL | NULL |  |
| `charge_refcode` | varchar(50) | NULL | NULL |  |
| `servicerefno` | varchar(50) | NULL | NULL |  |
| `clinic_cost` | double(11, 2) | NULL | NULL |  |
| `clinic_srp` | double(11, 2) | NULL | NULL |  |
| `pricetype` | varchar(10) | NULL | NULL |  |

### `dd_soap_management`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `pManagementId` | varchar(1) | NULL | NULL |  |
| `pOthRemarks` | varchar(500) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `pDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_advice`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `dRemarks` | varchar(2000) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_pemisc`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSkinId` | varchar(3) | NULL | NULL | lib_skin_extremities |
| `dHeentId` | varchar(3) | NULL | NULL | lib_heent |
| `dChestId` | varchar(3) | NULL | NULL | lib_chest |
| `dHeartId` | varchar(3) | NULL | NULL | lib_heart |
| `dAbdomenId` | varchar(3) | NULL | NULL | lib_abdomen |
| `dNeuroId` | varchar(3) | NULL | NULL | lib_neuro |
| `dRectalId` | varchar(3) | NULL | NULL | lib_digital_rectal |
| `dGuId` | varchar(3) | NULL | NULL | lib_genitourinary |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_pepert`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSystolic` | float UNSIGNED | NOT NULL |  |  |
| `dDiastolic` | float UNSIGNED | NOT NULL |  |  |
| `dHr` | float UNSIGNED | NOT NULL |  |  |
| `dRr` | float UNSIGNED | NOT NULL |  |  |
| `dTemp` | float UNSIGNED | NOT NULL |  | Temperature in Celsius |
| `dHeight` | float UNSIGNED | NOT NULL |  | Height of Patient in cm |
| `dWeight` | float UNSIGNED | NOT NULL |  | Weight of Patient in kg |
| `dBMI` | float UNSIGNED | NOT NULL |  |  |
| `dZScore` | varchar(10) | NULL | NULL |  |
| `dLeftVision` | varchar(12) | NULL | NULL |  |
| `dRightVision` | varchar(12) | NULL | NULL |  |
| `dLength` | float UNSIGNED | NULL | NULL | Length of Patient in cm - for Pediatric                 Patient only age 0-24 Months |
| `dHeadCirc` | float UNSIGNED | NULL | NULL |  |
| `dSkinfoldThickness` | float UNSIGNED | NULL | NULL |  |
| `dWaist` | float UNSIGNED | NULL | NULL |  |
| `dHip` | float UNSIGNED | NULL | NULL |  |
| `dLimbs` | float UNSIGNED | NULL | NULL |  |
| `dMidUpperArmCirc` | float UNSIGNED | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_pespecific`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `en_caseno` | varchar(21) | NULL | NULL |  |
| `dSkinRem` | text | NULL |  |  |
| `dHeentRem` | text | NULL |  |  |
| `dChestRem` | text | NULL |  |  |
| `dHeartRem` | text | NULL |  |  |
| `dAbdomenRem` | text | NULL |  |  |
| `dNeuroRem` | text | NULL |  |  |
| `dRectalRem` | text | NULL |  |  |
| `dGuRem` | text | NULL |  |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_soap_services_type`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | int | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `services_type` | varchar(50) | NULL | NULL |  |

### `dd_subjective`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pIllnessHistory` | text | NOT NULL |  | History of Patient Illnesses |
| `pSignsSymptoms` | text | NOT NULL |  | Pertinent Signs and Symptoms on Admission ID |
| `pOtherComplaint` | text | NULL |  | Other Complaint                             Note: Required if X is included in                             pSignsSymptoms |
| `pPainSite` | text | NULL |  | Site of Pain if Pain Element in                         Pertinent Signs and Symptoms on                         Admission is checked                         Note: Required if 38 is included in                         pSignsSymptoms |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_diagnostic`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pDiagnosticId` | varchar(11) | NULL | NULL |  |
| `pOthRemarks` | text | NULL |  |  |
| `pIsPhysicianRecommend` | enum('Y','N','X') | NOT NULL |  |  |
| `pPatientRemarks` | enum('RQ','RF','XX') | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_icd`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pIcdCode` | varchar(255) | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_medicine`

**Declared foreign keys (real SQL constraints):**
- CONSTRAINT `dd_medicine_phcicaseno_foreign` FOREIGN KEY (`pHciCaseNo`) REFERENCES `dd_enlistment` (`pHciCaseNo`) ON DELETE RESTRICT ON UPDATE RESTRICT
- CONSTRAINT `dd_medicine_phcitransno_foreign` FOREIGN KEY (`pHciTransNo`) REFERENCES `dd_soap_consultation` (`pHciTransNo`) ON DELETE RESTRICT ON UPDATE RESTRICT

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `pHciCaseNo` | varchar(21) | NOT NULL |  |  |
| `pHciTransNo` | varchar(21) | NOT NULL |  |  |
| `pCategory` | varchar(191) | NULL | NULL |  |
| `pDrugCode` | varchar(191) | NULL | NULL |  |
| `pGenericCode` | varchar(191) | NULL | NULL |  |
| `pSaltCode` | varchar(191) | NULL | NULL |  |
| `pStrengthCode` | varchar(191) | NULL | NULL |  |
| `pFormCode` | varchar(191) | NULL | NULL |  |
| `pUnitCode` | varchar(191) | NULL | NULL |  |
| `pPackageCode` | varchar(191) | NULL | NULL |  |
| `pOtherMedicine` | text | NULL |  |  |
| `pOthMedDrugGrouping` | enum('NCD','ANTIBIOTIC','OTHERS') | NULL | NULL |  |
| `pRoute` | text | NULL |  |  |
| `pQuantity` | int | NULL | 0 |  |
| `pActualUnitPrice` | double | NULL | 0 |  |
| `pTotalAmtPrice` | double | NULL | 0 |  |
| `pInstructionQuantity` | varchar(50) | NULL | NULL |  |
| `pInstructionStrength` | varchar(50) | NULL | NULL |  |
| `pIsDispensed` | varchar(191) | NULL | NULL |  |
| `pDateDispensed` | date | NULL | NULL |  |
| `pDispensingPersonnel` | varchar(200) | NULL | NULL |  |
| `pIsApplicable` | enum('Y','N') | NULL | 'N' |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_management`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pManagementId` | varchar(100) | NULL | NULL |  |
| `pOthRemarks` | text | NULL |  |  |
| `pIsPhysicianRecommended` | enum('Y','N','X') | NOT NULL |  |  |
| `pPatientRemarks` | enum('RQ','RF','XX') | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_advice`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pRemarks` | text | NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_document`

**Relationship documented in source comments (not a DB constraint):**
- `pHciCaseNo` → `dd_enlistment.pHciCaseNo`
- `pHciTransNo` → `dd_soap_consultation.pHciTransNo`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `pHciCaseNo` | varchar(21) | NULL | NULL | FK -> dd_enlistment.pHciCaseNo |
| `pHciTransNo` | varchar(21) | NULL | NULL | FK -> dd_soap_consultation.pHciTransNo |
| `pPatientPin` | varchar(12) | NULL | NULL | Refer to Members PIN if patient type is MM; Refer to Dependents PIN if type is DD |
| `pPatientType` | enum('MM','DD') | NULL | NULL |  |
| `pMemPin` | varchar(12) | NULL | NULL | Philhealth Identification Number (PIN) of Primary Member |
| `pDocumentType` | enum('EKAS','EPRESS','OTH') | NULL | NULL | “EKAS” - electronic KonSulTa                         Slip                         “EPRESS” - electronic                         Prescription Slip                         “OTH” - Others |
| `pDocumentUrl` | text | NULL |  | URL of document attachment for download |
| `pTransDate` | date | NULL | NULL | YYYY-MM-DD / Date when the record inserted |
| `pReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

## 9. PhilHealth TSEKAP — Diagnostic / Laboratory Exam Results

One table per lab/diagnostic test type, recording the result entered at point of care. Most of these have a matching `dd_diag_<test>` counterpart (see module 10) that appears to be the same result re-modeled with facility/patient/billing context added for PhilHealth transmittal — the two sets should be reconciled with the application team to confirm which is authoritative.

### `dd_cbc`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NOT NULL |  |  |
| `pLabDate` | date | NOT NULL |  |  |
| `pHematocrit` | varchar(50) | NOT NULL |  |  |
| `pHemoglobinG` | varchar(50) | NOT NULL |  |  |
| `pHemoglobinMmol` | varchar(50) | NOT NULL |  |  |
| `pMhcPg` | varchar(50) | NOT NULL |  |  |
| `pMhcFmol` | varchar(50) | NOT NULL |  |  |
| `pMchGhb` | varchar(50) | NOT NULL |  |  |
| `pMchcMmol` | varchar(50) | NOT NULL |  |  |
| `pMcvUm` | varchar(50) | NOT NULL |  |  |
| `pMcvFl` | varchar(50) | NOT NULL |  |  |
| `pWbc1000` | varchar(50) | NOT NULL |  |  |
| `pWbc10` | varchar(50) | NOT NULL |  |  |
| `pMyelocyte` | varchar(50) | NOT NULL |  |  |
| `pNeutrophilsBnd` | varchar(50) | NOT NULL |  |  |
| `pNeutrophilsSeg` | varchar(50) | NOT NULL |  |  |
| `pLympocytes` | varchar(50) | NOT NULL |  |  |
| `pMonocytes` | varchar(50) | NOT NULL |  |  |
| `pEosinophilis` | varchar(50) | NOT NULL |  |  |
| `pBasophilis` | varchar(50) | NOT NULL |  |  |
| `pPlatelet` | varchar(50) | NOT NULL |  |  |
| `pDateAdded` | date | NOT NULL |  |  |
| `pStatus` | enum('D','N','X','W') | NOT NULL |  |  |
| `pDiagnosticLabFee` | int | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_chestxray`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | varchar(11) | NULL | NULL |  |
| `pRemarksFindings` | text | NULL |  |  |
| `pObservation` | varchar(11) | NULL | NULL |  |
| `pRemarksObservation` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NOT NULL |  |  |
| `pDiagnosticLabFee` | int | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_creatine`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_ecg`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | enum('1','2') | NULL | NULL |  |
| `pRemarks` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_fbs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pGlucoseMg` | varchar(50) | NULL | NULL | Value for Glucose in md/Dl |
| `pGlucoseMmol` | varchar(50) | NULL | NULL | Value for Glucose in mmol/L |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_fecalysis`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pColor` | enum('1','2','3','4','5','6') | NULL | NULL | 1 - Brown;                         2 - Black;                         3 - Red;                         4 - White/Grey;                         5 - Yellow;                         6 - Green; |
| `pConsistency` | enum('1','2','3','4','5','6') | NULL | NULL | 1 - Soft;                         2 - Well-Formed;                         3 - Semi-Formed;                         4 - Watery;                         5 - Mucoid;                         6 - Hard |
| `pRbc` | varchar(50) | NULL | NULL | Value for RBC (/hpf) |
| `pWbc` | varchar(50) | NULL | NULL | Value for WBC(/hpf) |
| `pOva` | varchar(50) | NULL | NULL | Value for RBC (=/-) |
| `pParasite` | varchar(50) | NULL | NULL | Value for Parasite (=/-) |
| `pBlood` | enum('P','A') | NULL | NULL | P - Present;                             A - Absent |
| `pPusCells` | varchar(50) | NULL | NULL | Value for PUS Cells |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_fobt`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | enum('P','N') | NULL | NULL | P - Positive;                             N - Negative |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_hba1c`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL |  |
| `pDiagnosticLabFee` | int | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_lipidprofile`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pLdl` | varchar(50) | NULL | NULL | Value for LDL (mg/dL) |
| `pHdl` | varchar(50) | NULL | NULL | Value for HDL (mg/dL) |
| `pTotal` | varchar(50) | NULL | NULL | Total Value of Cholesterol (mg/dL) |
| `pCholesterol` | varchar(50) | NULL | NULL | Total Value of Cholesterol (mg/dL) |
| `pTriglycerides` | varchar(50) | NULL | NULL | Total Value of Triglycerides (mg/dL) |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_ogtt`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pExamFastingMg` | varchar(50) | NULL | NULL | Result in Fasting Examination (mg/dL) |
| `pExamFastingMmol` | varchar(50) | NULL | NULL | Result in Fasting Examination (mmol/L) |
| `pExamOgttOneHrMg` | varchar(50) | NULL | NULL | Result in OGTT 1 Hour Examination (mg/dL) |
| `pExamOgttOneHrMmol` | varchar(50) | NULL | NULL | Result in OGTT 1 Hour Examination (mmol/L) |
| `pExamOgttTwoHrMg` | varchar(50) | NULL | NULL | Result in OGTT 2 Hours Examination (mg/dL) |
| `pExamOgttTwoHrMmol` | varchar(50) | NULL | NULL | Result in OGTT 2 Hours Examination (mmol/L) |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_otherdiagexam`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pOthDiagExam` | text | NULL |  |  |
| `pFindings` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL |  |
| `pDiagnosticLabFee` | int | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_papsmear`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | text | NULL |  |  |
| `pImpression` | text | NULL |  |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_ppdtest`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pFindings` | enum('P','N') | NULL | NULL | P - Positive;                             N - Negative |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_rbs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pGlucoseMg` | varchar(50) | NULL | NULL | Value for Glucose in md/Dl |
| `pGlucoseMmol` | varchar(50) | NULL | NULL | Value for Glucose in mmol/L |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `pDiagnosticLabFee` | decimal(10, 2) | NULL | NULL |  |
| `pReportStatus` | enum('V','U','F') | NOT NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_sputum`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NULL |  |  |
| `pLabDate` | date | NULL | NULL |  |
| `pDataCollection` | enum('1','2','3','X') | NULL | 'X' |  |
| `pFindings` | enum('1','2') | NULL | NULL |  |
| `pRemarks` | text | NULL |  |  |
| `pNoPlusses` | varchar(5) | NULL | NULL |  |
| `pDateAdded` | date | NULL | NULL |  |
| `pStatus` | enum('D','N','X','W') | NOT NULL |  |  |
| `pDiagnosticLabFee` | int | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_urinalysis`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pReferralFacility` | text | NOT NULL |  |  |
| `pLabDate` | date | NOT NULL |  |  |
| `pGravity` | varchar(50) | NOT NULL |  |  |
| `pAppearance` | varchar(50) | NOT NULL |  |  |
| `pColor` | varchar(50) | NOT NULL |  |  |
| `pGlucose` | varchar(50) | NOT NULL |  |  |
| `pProteins` | varchar(50) | NOT NULL |  |  |
| `pKetones` | varchar(50) | NOT NULL |  |  |
| `pPh` | varchar(50) | NOT NULL |  |  |
| `pRbCells` | varchar(50) | NOT NULL |  |  |
| `pWbCells` | varchar(50) | NOT NULL |  |  |
| `pBacteria` | varchar(50) | NOT NULL |  |  |
| `pCrystals` | varchar(50) | NOT NULL |  |  |
| `pBladderCell` | varchar(50) | NOT NULL |  |  |
| `pSquamousCell` | varchar(50) | NOT NULL |  |  |
| `pTubularCell` | varchar(50) | NOT NULL |  |  |
| `pBroadCasts` | varchar(50) | NOT NULL |  |  |
| `pEpithelialCast` | varchar(50) | NOT NULL |  |  |
| `pGranularCast` | varchar(50) | NOT NULL |  |  |
| `pHyalineCast` | varchar(50) | NOT NULL |  |  |
| `pRbcCast` | varchar(50) | NOT NULL |  |  |
| `pWaxyCast` | varchar(50) | NOT NULL |  |  |
| `pWcCast` | varchar(50) | NOT NULL |  |  |
| `pAlbumin` | varchar(50) | NOT NULL |  |  |
| `pPusCells` | varchar(50) | NOT NULL |  |  |
| `pDateAdded` | date | NOT NULL |  |  |
| `pStatus` | enum('D','N','X','W') | NOT NULL |  |  |
| `pDiagnosticLabFee` | int | NOT NULL |  |  |
| `pReportStatus` | enum('V','U','F') | NULL | 'U' |  |
| `pDeficiencyRemarks` | text | NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `dd_diagnosticexamresult`

**Declared foreign keys (real SQL constraints):**
- CONSTRAINT `dd_diagnosticexamresult_phcicaseno_foreign` FOREIGN KEY (`pHciCaseNo`) REFERENCES `dd_enlistment` (`pHciCaseNo`) ON DELETE RESTRICT ON UPDATE RESTRICT
- CONSTRAINT `dd_diagnosticexamresult_phcitransno_foreign` FOREIGN KEY (`pHciTransNo`) REFERENCES `dd_soap_consultation` (`pHciTransNo`) ON DELETE RESTRICT ON UPDATE RESTRICT

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pHciCaseNo` | varchar(21) | NOT NULL |  |  |
| `pHciTransNo` | varchar(21) | NOT NULL |  |  |
| `pPatientPin` | varchar(12) | NOT NULL |  |  |
| `pPatientType` | enum('MM','DD') | NOT NULL |  |  |
| `pMemPin` | varchar(12) | NOT NULL |  |  |
| `pEffYear` | varchar(4) | NOT NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

## 10. PhilHealth TSEKAP — Diagnostic Results (billing-enriched / transmittal copies)

Mirrors module 9's exam tables but adds `dw_clientcode`, `px_pin`, `en_CaseNo`/`s_TransNo`, and pharmacy/billing linkage columns (`charge_transcode`, `prodcode`, `clinic_cost`, `co_pay`, `vat_amt`, `entry_type` PHIC/INTERNAL). Used to reconcile a diagnostic result against its stock ledger charge and to package results for TSEKAP e-claim transmittal.

### `dd_diag_1_examresult`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffYear` | varchar(4) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_diag_1_examresults`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffYear` | varchar(4) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_diag_cbc`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | text | NOT NULL |  | 1000 |
| `dLabDate` | date | NOT NULL |  |  |
| `dHematocrit` | varchar(50) | NULL | NULL |  |
| `dHemoglobinG` | varchar(50) | NULL | NULL |  |
| `dHemoglobinMmol` | varchar(50) | NULL | NULL |  |
| `dMhcPg` | varchar(50) | NULL | NULL |  |
| `dMhcFmol` | varchar(50) | NULL | NULL |  |
| `dMchGhb` | varchar(50) | NULL | NULL |  |
| `dMchcMmol` | varchar(50) | NULL | NULL |  |
| `dMcvUm` | varchar(50) | NULL | NULL |  |
| `dMcvFl` | varchar(50) | NULL | NULL |  |
| `dWbc1000` | varchar(50) | NULL | NULL |  |
| `dWbc10` | varchar(50) | NULL | NULL |  |
| `dMyelocyte` | varchar(50) | NULL | NULL |  |
| `dNeutrophilsBnd` | varchar(50) | NULL | NULL |  |
| `dNeutrophilsSeg` | varchar(50) | NULL | NULL |  |
| `dLympocytes` | varchar(50) | NULL | NULL |  |
| `dMonocytes` | varchar(50) | NULL | NULL |  |
| `dEosinophilis` | varchar(50) | NULL | NULL |  |
| `dBasophilis` | varchar(50) | NULL | NULL |  |
| `dPlatelet` | varchar(50) | NULL | NULL |  |
| `dDateAdded` | date | NOT NULL |  |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_chestxray`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(2000) | NULL | NULL | 1000 |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | varchar(3) | NULL | NULL |  |
| `dRemarksFindings` | varchar(2000) | NULL | NULL | 2000 |
| `dObservation` | varchar(100) | NULL | NULL |  |
| `dRemarksObservation` | varchar(2000) | NULL | NULL | 2000 |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_creatine`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | varchar(100) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_ecg`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | text | NULL |  |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | enum('1','2') | NULL | NULL |  |
| `dRemarks` | varchar(1000) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_examresult_master`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `specmen_barcode` | varchar(80) | NULL | NULL |  |
| `transcode` | varchar(50) | NULL | NULL |  |
| `report_group` | enum('DIAGNOSTIC','IMAGING','OTHERS') | NULL | NULL | Diagnostoc = L / Imaging  = X / Others = O |
| `result_reportcode` | varchar(50) | NULL | NULL |  |
| `result_reportno` | bigint | NULL | NULL |  |
| `entry_source` | enum('CLINIC','YAKAP') | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffYear` | varchar(4) | NULL | NULL |  |
| `pstatus` | enum('D','N','X','W','V') | NULL | NULL | V = VOID |
| `prodcode` | varchar(50) | NULL | NULL |  |
| `phic_reference_code` | varchar(50) | NULL | NULL |  |
| `item_dscr` | varchar(220) | NULL | NULL |  |
| `px_name` | varchar(120) | NULL | NULL |  |
| `adress` | varchar(220) | NULL | NULL |  |
| `dob` | date | NULL | NULL |  |
| `age` | double(11, 2) | NULL | NULL |  |
| `sex` | enum('MALE','FEMALE') | NULL | NULL |  |
| `doccode` | varchar(50) | NULL | NULL |  |
| `docname` | varchar(80) | NULL | NULL |  |
| `remarks` | longtext | NULL |  |  |
| `extractedby` | varchar(120) | NULL | NULL |  |
| `extracted` | datetime | NULL | NULL |  |
| `resultcreatedby` | varchar(120) | NULL | NULL |  |
| `resultcreated` | datetime | NULL | NULL |  |
| `medtech_name` | varchar(120) | NULL | NULL |  |
| `medtech_licno` | varchar(80) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `orno` | varchar(0) | NULL | NULL |  |
| `ordate` | date | NULL | NULL |  |
| `emailadd` | varchar(80) | NULL | NULL |  |
| `normal_value_refcode` | varchar(0) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |

### `dd_diag_fbs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(2000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dGlucoseMg` | varchar(50) | NULL | NULL | Value for Glucose in md/Dl |
| `dGlucoseMmol` | varchar(50) | NULL | NULL | Value for Glucose in mmol/L |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_fecalysis`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dColor` | enum('1','2','3','4','5','6') | NULL | NULL | 1 - Brown;                         2 - Black;                         3 - Red;                         4 - White/Grey;                         5 - Yellow;                         6 - Green; |
| `dConsistency` | enum('1','2','3','4','5','6') | NULL | NULL | 1 - Soft;   2 - Well-Formed;   3 - Semi-Formed;   4 - Watery;   5 - Mucoid;  6 - Hard |
| `dRbc` | varchar(50) | NULL | NULL | Value for RBC (/hpf) |
| `dWbc` | varchar(50) | NULL | NULL | Value for WBC(/hpf) |
| `dOva` | varchar(50) | NULL | NULL | Value for RBC (=/-) |
| `dParasite` | varchar(50) | NULL | NULL | Value for Parasite (=/-) |
| `dBlood` | enum('P','A') | NULL | NULL | P - Present;                             A - Absent |
| `dPusCells` | varchar(50) | NULL | NULL | Value for PUS Cells |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_fobt`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | enum('P','N') | NULL | NULL | P - Positive;                             N - Negative |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_hba1c`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | varchar(1000) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_lipidprofile`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | text | NULL |  |  |
| `dLabDate` | date | NULL | NULL |  |
| `dLdl` | varchar(50) | NULL | NULL | Value for LDL (mg/dL) |
| `dHdl` | varchar(50) | NULL | NULL | Value for HDL (mg/dL) |
| `dTotal` | varchar(50) | NULL | NULL | Total Value of Cholesterol (mg/dL) |
| `dCholesterol` | varchar(50) | NULL | NULL | Total Value of Cholesterol (mg/dL) |
| `dTriglycerides` | varchar(50) | NULL | NULL | Total Value of Triglycerides (mg/dL) |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_normalvalues`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `prodcode` | varchar(50) | NULL | NULL |  |
| `phic_reference_code` | varchar(50) | NULL | NULL |  |
| `testcode` | varchar(50) | NULL | NULL |  |
| `test_dscr` | varchar(120) | NULL | NULL | cbc createnin |
| `grouping` | varchar(120) | NULL | NULL |  |
| `sub_grouping` | varchar(120) | NULL | NULL |  |
| `sex_applicable` | enum('BOTH','MALE','FEMALE') | NULL | NULL |  |
| `age_min` | double(11, 0) | NULL | NULL |  |
| `age_max` | double(11, 2) | NULL | NULL |  |
| `is_numeric_result` | tinyint | NULL | NULL | 1 = yes then can make min and max value else 0 |
| `is_numeric_min_nv` | double(11, 2) | NULL | NULL |  |
| `is_numeric_max_nv` | double(11, 2) | NULL | NULL |  |
| `nv_default` | enum('SI','CONVENTIONAL') | NULL | NULL |  |
| `nv_si_normal` | varchar(200) | NULL | NULL |  |
| `nv_conventional_normal` | varchar(200) | NULL | NULL |  |
| `unit_dscr` | varchar(50) | NULL | NULL |  |
| `tablename` | varchar(100) | NULL | NULL | name of tablename in the database |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_diag_ogtt`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dExamFastingMg` | varchar(50) | NULL | NULL | Result in Fasting Examination (mg/dL) |
| `dExamFastingMmol` | varchar(50) | NULL | NULL | Result in Fasting Examination (mmol/L) |
| `dExamOgttOneHrMg` | varchar(50) | NULL | NULL | Result in OGTT 1 Hour Examination (mg/dL) |
| `dExamOgttOneHrMmol` | varchar(50) | NULL | NULL | Result in OGTT 1 Hour Examination (mmol/L) |
| `dExamOgttTwoHrMg` | varchar(50) | NULL | NULL | Result in OGTT 2 Hours Examination (mg/dL) |
| `dExamOgttTwoHrMmol` | varchar(50) | NULL | NULL | Result in OGTT 2 Hours Examination (mmol/L) |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_otherdiagexam`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dOthDiagExam` | varchar(1000) | NULL | NULL |  |
| `dFindings` | varchar(1000) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_others_internal_test`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dPatientPin` | varchar(12) | NULL | NULL |  |
| `dPatientType` | enum('MM','DD') | NULL | NULL |  |
| `dMemPin` | varchar(12) | NULL | NULL |  |
| `dEffYear` | varchar(4) | NULL | NULL |  |
| `testresult` | longtext | NULL |  |  |
| `dDateAdded` | date | NOT NULL |  |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |

### `dd_diag_papsmear`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | varchar(500) | NULL | NULL |  |
| `dImpression` | varchar(500) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_ppdtest`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(1000) | NULL | NULL |  |
| `dLabDate` | date | NULL | NULL |  |
| `dFindings` | enum('P','N') | NULL | NULL | P - Positive;                             N - Negative |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_rbs`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | text | NULL |  |  |
| `dLabDate` | date | NULL | NULL |  |
| `dGlucoseMg` | varchar(50) | NULL | NULL | Value for Glucose in md/Dl |
| `dGlucoseMmol` | varchar(50) | NULL | NULL | Value for Glucose in mmol/L |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL | “D” - Done                             “N” - Not yet done                             “X” - Deferred                             “W” - Waived |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL |  |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_sputum`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | text | NULL |  | 1000 |
| `dLabDate` | date | NULL | NULL |  |
| `dDataCollection` | enum('1','2','3','X') | NULL | NULL |  |
| `dFindings` | enum('1','2') | NULL | NULL |  |
| `dRemarks` | text | NULL |  | 2000 |
| `dNoPlusses` | varchar(5) | NULL | NULL |  |
| `dDateAdded` | date | NULL | NULL |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | text | NULL |  | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

### `dd_diag_urinalysis`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `reportcode` | varchar(50) | NULL | NULL | SDFGHJHGHJFF |
| `reportno` | varchar(50) | NULL | NULL | Type+reportno (Diag=L/Imag=X/Others=O) ex.  L100002 |
| `en_CaseNo` | varchar(21) | NULL | NULL |  |
| `s_TransNo` | varchar(21) | NULL | NULL |  |
| `dReferralFacility` | varchar(2000) | NULL | NULL | 1000 |
| `dLabDate` | date | NOT NULL |  |  |
| `dGravity` | varchar(50) | NULL | NULL |  |
| `dAppearance` | varchar(50) | NULL | NULL |  |
| `dColor` | varchar(50) | NULL | NULL |  |
| `dGlucose` | varchar(50) | NULL | NULL |  |
| `dProteins` | varchar(50) | NULL | NULL |  |
| `dKetones` | varchar(50) | NULL | NULL |  |
| `dPh` | varchar(50) | NULL | NULL |  |
| `dRbCells` | varchar(50) | NULL | NULL |  |
| `dWbCells` | varchar(50) | NULL | NULL |  |
| `dBacteria` | varchar(50) | NULL | NULL |  |
| `dCrystals` | varchar(50) | NULL | NULL |  |
| `dBladderCell` | varchar(50) | NULL | NULL |  |
| `dSquamousCell` | varchar(50) | NULL | NULL |  |
| `dTubularCell` | varchar(50) | NULL | NULL |  |
| `dBroadCasts` | varchar(50) | NULL | NULL |  |
| `dEpithelialCast` | varchar(50) | NULL | NULL |  |
| `dGranularCast` | varchar(50) | NULL | NULL |  |
| `dHyalineCast` | varchar(50) | NULL | NULL |  |
| `dRbcCast` | varchar(50) | NULL | NULL |  |
| `dWaxyCast` | varchar(50) | NULL | NULL |  |
| `dWcCast` | varchar(50) | NULL | NULL |  |
| `dAlbumin` | varchar(50) | NULL | NULL |  |
| `dPusCells` | varchar(50) | NULL | NULL |  |
| `dDateAdded` | date | NOT NULL |  |  |
| `dStatus` | enum('D','N','X','W','V') | NULL | NULL |  |
| `dDiagnosticLabFee` | double(11, 2) | NULL | NULL |  |
| `dReportStatus` | enum('V','U','F') | NULL | NULL |  |
| `dDeficiencyRemarks` | varchar(2000) | NULL | NULL | 2000 |
| `createdby` | varchar(80) | NULL | NULL |  |
| `created` | datetime | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `charge_transcode` | varchar(50) | NULL | NULL | transcode of stock_ledger |
| `prodcode` | varchar(50) | NULL | NULL | prodcode of item in the stock_lisitng |
| `clinic_cost` | double(11, 2) | NULL | NULL | cost from stocks_listing |
| `pricetype` | varchar(10) | NULL | NULL |  |
| `co_pay` | double(11, 2) | NULL | NULL |  |
| `vat_amt` | double(11, 2) | NULL | NULL |  |
| `entry_type` | enum('PHIC','INTERNAL') | NULL | NULL |  |
| `resultcode` | varchar(50) | NULL | NULL |  |

## 11. `dd_` Module Reference Libraries

Small lookup/reference tables scoped specifically to the `dd_` (TSEKAP) forms — general survey findings, TSEKAP package types, skin-finding picklist, and generic field/unit reference values.

### `dd_lib_gen_survey`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `GENSURVEY_ID` | varchar(1) | NULL | NULL | GENERAL SURVEY ID |
| `GENSURVEY_DESC` | varchar(100) | NULL | NULL | GENERAL SURVEY DESCRIPTION |
| `DATE_ADDED` | date | NULL | NULL |  |
| `ADDED_BY` | varchar(20) | NULL | NULL |  |
| `LIB_STAT` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `DATE_DEACTIVATED` | date | NULL | NULL | DATE WHEN DEACTIVATED |
| `DEACTIVATED_BY` | varchar(20) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |

### `dd_lib_package_type`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `PACKAGE_ID` | varchar(2) | NULL | NULL | PACKAGE ID NUMBER |
| `PACKAGE_DESC` | varchar(100) | NULL | NULL | PACKAGE DESCRIPTION |
| `PACKAGE_INFO` | varchar(400) | NULL | NULL | PACKAGE TYPE MORE DETAILS |
| `CPO_NO` | varchar(50) | NULL | NULL | CIRCULAR NUMBER OF PACKAGE TYPE |
| `LIB_SORT` | decimal(2, 0) | NULL | NULL | SORTING OF PACKAGE TYPE |
| `LIB_STATUS` | decimal(2, 0) | NULL | NULL | 0 - INACTIVE; 1- ACTIVE |
| `DATE_CREATED` | date | NULL | NULL | DATE CREATED THE RECORD |
| `CREATED_BY` | varchar(20) | NULL | NULL | USER WHO INSERTED THE RECORD |

### `dd_lib_skin`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `SKIN_ID` | varchar(3) | NULL | NULL | SKIN DESCRIPTION ID |
| `SKIN_DESC` | varchar(100) | NULL | NULL | SKIN DESCRIPTION |
| `DATE_ADDED` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `ADDED_BY` | varchar(20) | NULL | NULL | REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER) |
| `SORT_NO` | int | NULL | NULL | SORT NUMBER |
| `LIB_STAT` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `DATE_DEACTIVATED` | datetime | NULL | NULL | DATE DEACTIVATED |
| `DEACTIVATED_BY` | varchar(20) | NULL | NULL | REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER) |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dd_lib_unit_references`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `FIELD_NAME` | varchar(20) | NULL | NULL | FIELD NAME |
| `FIELD_CODE` | varchar(5) | NULL | NULL | FIELD CODE |
| `FIELD_DESC` | varchar(150) | NULL | NULL | FIELD DESCRIPTION |
| `DATE_ADDED` | date | NULL | NULL | DATE WHEN ADDED |
| `ADDED_BY` | varchar(20) | NULL | NULL | USER WHO ADD THE RECORD |
| `SORT_NO` | int | NULL | NULL | SORT NUMBER |
| `LIB_STAT` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `DATE_DEACTIVATED` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `DEACTIVATED_BY` | varchar(20) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

## 12. `dw_lib_` Clinical Reference Libraries

Large family of clinical picklist/lookup tables that populate physical-exam and history dropdowns (body-system findings, ICD-10 codes, immunization schedules by age group, growth-chart Z-score reference tables for WHO child growth standards, medicine/drug component libraries, non-communicable-disease questionnaire library, etc.). These are reference data, not transactional patient data.

### `dw_lib_abdomen`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `abdomen_id` | varchar(3) | NULL | NULL | ABDOMEN DESCRIPTION ID |
| `abdomen_desc` | varchar(100) | NULL | NULL | ABDOMEN_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER) |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER) |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_chest`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `chest_id` | varchar(3) | NULL | NULL | CHEST DESCRIPTION ID |
| `chest_desc` | varchar(100) | NULL | NULL | CHEST DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_chestxray_findings`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `finding_id` | varchar(3) | NULL | NULL | FINDINGS DESCRIPTION ID |
| `finding_desc` | varchar(100) | NULL | NULL | FINDINGS DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER) |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER) |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_chestxray_observation`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `observe_id` | varchar(3) | NULL | NULL | OBSERVATION DESCRIPTION ID |
| `observe_desc` | varchar(100) | NULL | NULL | CHEST DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_diagnostic`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `diagnostic_id` | varchar(3) | NULL | NULL | DIAGNOSTIC DESCRIPTION ID |
| `item_grouping` | enum('DIAGNOSTIC','IMAGING','PROCEDURES','OTHERS') | NULL | NULL |  |
| `diagnostic_desc` | varchar(100) | NULL | NULL | DIAGNOSTIC DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `diag_price` | double(11, 2) | NULL | NULL |  |

### `dw_lib_digital_rectal`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `rectal_id` | varchar(3) | NULL | NULL | DIGITAL RECTAL EXAMINATION DESCRIPTION ID |
| `rectal_desc` | varchar(100) | NULL | NULL | DIGITAL RECTAL EXAMINATION DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_extremities`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `EXTREMITIES_ID` | varchar(3) | NULL | NULL | EXTREMITIES DESCRIPTION ID |
| `EXTREMITIES_DESC` | varchar(100) | NULL | NULL | EXTREMITIES DESCRIPTION |
| `DATE_ADDED` | date | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `ADDED_BY` | varchar(20) | NULL | NULL | REFERS TO THE USER WHO ADDED THE RECORD (LOGGED USER) |
| `SORT_NO` | int | NULL | NULL | SORT NUMBER |
| `LIB_STAT` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `DATE_DEACTIVATED` | date | NULL | NULL | DATE DEACTIVATED |
| `DEACTIVATED_BY` | varchar(20) | NULL | NULL | REFERS TO THE USER WHO DEACTIVATED THE RECORD (LOGGED USER) |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_genitourinary`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `gu_id` | varchar(3) | NULL | NULL | SKIN/EXTREMITIES DESCRIPTION ID |
| `gu_desc` | varchar(100) | NULL | NULL | SKIN/EXTREMITIES DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_heart`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `heart_id` | varchar(3) | NULL | NULL | HEART DESCRIPTION ID |
| `heart_desc` | varchar(100) | NULL | NULL | HEART DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_heent`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `heent_id` | varchar(3) | NULL | NULL | HEENT DESCRIPTION ID |
| `heent_desc` | varchar(100) | NULL | NULL | HEENT_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_icd`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `refcode` | bigint | NULL | NULL |  |
| `icd_code` | varchar(10) | NULL | NULL | ICD 10 CODE |
| `icd_desc` | varchar(500) | NULL | NULL |  |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_immchild`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `imm_code` | varchar(3) | NULL | NULL | HEART DESCRIPTION ID |
| `imm_desc` | varchar(100) | NULL | NULL | HEART_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_immelderly`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `immcode` | varchar(3) | NULL | NULL | HEART DESCRIPTION ID |
| `imm_desc` | varchar(100) | NULL | NULL | HEART_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_immpregw`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `imm_code` | varchar(3) | NULL | NULL | HEART DESCRIPTION ID |
| `imm_desc` | varchar(100) | NULL | NULL | HEART_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |

### `dw_lib_immyoungw`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `imm_code` | varchar(3) | NULL | NULL | HEART DESCRIPTION ID |
| `imm_desc` | varchar(100) | NULL | NULL | HEART_DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |

### `dw_lib_management`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `management_id` | varchar(3) | NULL | NULL | PLAN MANAGEMENT CODE |
| `manadement_desc` | varchar(100) | NULL | NULL | PLAN MANAGEMENT DESCRIPTION |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_mdiseases`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `mdisease_code` | varchar(3) | NULL | NULL | Medical disease ID |
| `mdisease_desc` | varchar(150) | NULL | NULL | Medical disease description |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_medicine`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `DRUG_CODE` | varchar(30) | NULL | NULL |  |
| `DRUG_DESC` | varchar(100) | NULL | NULL |  |
| `GEN_CODE` | varchar(5) | NULL | NULL |  |
| `SALT_CODE` | varchar(5) | NULL | NULL |  |
| `FORM_CODE` | varchar(5) | NULL | NULL |  |
| `STRENGTH_CODE` | varchar(5) | NULL | NULL |  |
| `UNIT_CODE` | varchar(5) | NULL | NULL |  |
| `PACKAGE_CODE` | varchar(5) | NULL | NULL |  |
| `CATEGORY` | varchar(50) | NULL | NULL | CATEGORY DESCRIPTION - KONSULTA |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_meds`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `DRUG_CODE` | varchar(100) | NULL | NULL |  |
| `DRUG_DESCRIPTION` | varchar(50) | NULL | NULL |  |
| `STOCK_DOSAGE` | varchar(20) | NULL | NULL |  |
| `PREPARATION` | varchar(150) | NULL | NULL |  |
| `ADD_DESCRIPTION` | varchar(300) | NULL | NULL |  |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `yakap_essential` | tinyint | NULL | NULL |  |

### `dw_lib_meds_form`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `form_code` | varchar(10) | NULL | NULL |  |
| `form_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_meds_generic`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `gen_code` | varchar(10) | NULL | NULL |  |
| `gen_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_meds_package`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `package_code` | varchar(10) | NULL | NULL |  |
| `package_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_meds_salt`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `salt_code` | varchar(5) | NULL | NULL |  |
| `salt_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_meds_strength`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `strength_code` | varchar(10) | NULL | NULL |  |
| `strength_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_meds_unit`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `unit_code` | varchar(5) | NULL | NULL |  |
| `unit_desc` | varchar(100) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |

### `dw_lib_ncdq`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `QID` | int | NULL | NULL | QUESTION ID |
| `HID` | int | NULL | NULL | HEADER ID |
| `QUESTION_DESC` | varchar(300) | NULL | NULL | QUESTION DESCRIPTION |
| `PARENT_QID` | varchar(20) | NULL | NULL | PARENT QUESTION ID |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_ncdqh`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `HID` | int | NULL | NULL | HEADER ID |
| `HEADER_DESC` | varchar(150) | NULL | NULL | HEADER DESCRIPTION |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_neuro`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `neuro_id` | varchar(3) | NULL | NULL | NEURO DESCRIPTION ID |
| `neuro_desc` | varchar(100) | NULL | NULL | NEURO DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_signs_symptoms`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `symptoms_id` | varchar(3) | NULL | NULL | Signs and Symptoms ID |
| `symptoms_desc` | varchar(100) | NULL | NULL | Signs and Symptoms description |
| `lib_stat` | int | NULL | NULL | LIBRARY STATUS (1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_skin_extremities`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `skin_id` | varchar(3) | NULL | NULL | SKIN/EXTREMITIES DESCRIPTION ID |
| `skin_desc` | varchar(100) | NULL | NULL | SKIN/EXTREMITIES DESCRIPTION |
| `lib_stat` | int | NULL | NULL | STATUS(1=ACTIVE, 0=DEACTIVATED) |
| `updated` | datetime | NULL | NULL | DATE WHEN THE RECORD WAS ADDED |
| `updatedby` | varchar(80) | NULL | NULL | USER WHO ADDED THE RECORD |
| `sort_no` | int | NULL | NULL | SORT NUMBER |
| `date_deactivated` | datetime | NULL | NULL | DATE WHEN DEACTIVATED |
| `deactivatedby` | varchar(80) | NULL | NULL | USER WHO DEACTIVATED THE RECORD |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_zscore_b023`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `LIB_CODE` | varchar(4) | NULL | NULL | Library Code |
| `LENGTH` | varchar(10) | NULL | NULL | BOY LENGTH IN CM |
| `WEIGHT` | varchar(10) | NULL | NULL | BOY WEIGHT IN KG |
| `RESULT_CODE` | varchar(10) | NULL | NULL | Result Code |
| `RESULT_DESC` | varchar(50) | NULL | NULL | Result Desc |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_zscore_b2460`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `LIB_CODE` | varchar(4) | NULL | NULL | Library Code |
| `HEIGHT` | varchar(10) | NULL | NULL | HEIGHT IN CM |
| `WEIGHT` | varchar(10) | NULL | NULL | WEIGHT IN KG |
| `RESULT_CODE` | varchar(10) | NULL | NULL | Result Code |
| `RESULT_DESC` | varchar(50) | NULL | NULL | Result Desc |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_zscore_g023`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `LIB_CODE` | varchar(4) | NULL | NULL | Library Code |
| `LENGTH` | varchar(10) | NULL | NULL | LENGTH IN CM |
| `WEIGHT` | varchar(10) | NULL | NULL | WEIGHT IN KG |
| `RESULT_CODE` | varchar(10) | NULL | NULL | Result Code |
| `RESULT_DESC` | varchar(50) | NULL | NULL | Result Desc |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

### `dw_lib_zscore_g2460`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `LIB_CODE` | varchar(4) | NULL | NULL | Library Code |
| `HEIGHT` | varchar(10) | NULL | NULL | LENGTH IN CM |
| `WEIGHT` | varchar(10) | NULL | NULL | WEIGHT IN KG |
| `RESULT_CODE` | varchar(10) | NULL | NULL | Result Code |
| `RESULT_DESC` | varchar(50) | NULL | NULL | Result Desc |
| `sys_usertype` | tinyint | NULL | NULL | 1 BOTH 2 INTERNAL 3 PHIC ONLY |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |

## 13. Geographic Reference (PSGC)

Philippine Standard Geographic Code-style reference tables for region/province/municipality/barangay/zip code, plus a health-facility classification lookup.

### `lib_region`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `REGION_CODE` | decimal(10, 0) | NULL | NULL | REGION ADDRESS CODE |
| `REGION_NAME` | varchar(50) | NULL | NULL | REGION NAME |
| `PRO_CODE` | decimal(10, 0) | NULL | NULL | PRO CODE |
| `PRO_SORT` | decimal(2, 0) | NULL | NULL | SORTING OF PROCODE |
| `LHIO` | varchar(2) | NULL | NULL | LHIO CODE ADDRESS |
| `REGION_ID` | varchar(10) | NULL | NULL | REGION ID |
| `REGION_DESC` | varchar(50) | NULL | NULL | REGION DESCRIPTION |

### `lib_province`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `PROCODE` | varchar(2) | NULL | NULL | PRO CODE OFFICE CODE |
| `PROVINCE` | varchar(2) | NULL | NULL | PROVINCE OFFICE CODE |
| `PROV_NAME` | varchar(60) | NULL | NULL | MUNICIPALITY OFFICE CODE |
| `AREACODE` | varchar(1) | NULL | NULL | BARANGAY OFFICE CODE |
| `LHIO` | varchar(5) | NULL | NULL | BARANGAY OFFICE CODE |

### `lib_municipality`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `PROCODE` | varchar(2) | NULL | NULL |  |
| `PROVINCE` | varchar(2) | NULL | NULL |  |
| `MUNICIPALITY` | varchar(2) | NULL | NULL |  |
| `MUN_NAME` | varchar(60) | NULL | NULL |  |
| `LHIO` | varchar(2) | NULL | NULL |  |

### `lib_barangay`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `PROCODE` | varchar(255) | NULL | NULL |  |
| `PROVINCE` | varchar(255) | NULL | NULL |  |
| `MUNICIPALITY` | varchar(255) | NULL | NULL |  |
| `BARANGAY` | varchar(255) | NULL | NULL |  |
| `BRGY_NAME` | varchar(255) | NULL | NULL |  |

### `lib_zipcode`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `PROCODE` | varchar(2) | NULL | NULL | PRO CODE OFFICE CODE |
| `PROVINCE` | varchar(2) | NULL | NULL | PROVINCE OFFICE CODE |
| `MUNICIPALITY` | varchar(2) | NULL | NULL | MUNICIPALITY OFFICE CODE |
| `ZIP_CODE` | varchar(4) | NULL | NULL | BARANGAY OFFICE CODE |

### `lib_hci_class`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `CLASS_CODE` | varchar(2) | NULL | NULL | CLASSIFICATION CODE |
| `CLASS_DEF` | varchar(100) | NULL | NULL | CLASSIFICATION CODE DEFINITION |

### `lib_diagnostic`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `diagnostic_id` | int | NULL | NULL |  |
| `diagnostic_dscr` | varchar(120) | NULL | NULL |  |
| `diagnostic_status` | enum('1','0') | NULL | NULL |  |

## 14. PhilHealth / HMO Compliance & e-Claims Transmittal

Tables supporting PhilHealth accreditation credentials, per-item PhilHealth-priced charge detail, sequence-number generation for TSEKAP document codes, and XML packaging/encryption/transmittal of enlistment and consultation data to PhilHealth.

### `pcb`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `clientcode` | varchar(12) | NULL | NULL |  |
| `userid` | varchar(30) | NULL | NULL | to be provided by phic - pusername |
| `passwd` | varchar(30) | NULL | NULL | to be provided by phic pPassword |
| `hciaccreno` | varchar(9) | NULL | NULL |  |
| `pmccno` | varchar(6) | NULL | NULL |  |
| `enlistTotalcnt` | float | NULL | NULL |  |
| `profileTotalcnt` | float | NULL | NULL |  |
| `soapTotalcnt` | float | NULL | NULL |  |
| `certificationid` | varchar(21) | NULL | NULL |  |
| `hcitransmittalnumber` | varchar(21) | NULL | NULL |  |
| `facilityName` | varchar(120) | NULL | NULL |  |
| `region` | varchar(50) | NULL | NULL |  |
| `updatedby` | varchar(80) | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |

### `dd_pcb`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint UNSIGNED | NOT NULL, AUTO_INCREMENT |  |  |
| `pUsername` | varchar(30) | NOT NULL |  |  |
| `pPassword` | varchar(30) | NOT NULL |  |  |
| `pHciAccreNo` | varchar(9) | NOT NULL |  |  |
| `pPMCCNo` | varchar(6) | NOT NULL |  |  |
| `pEnlistTotalCnt` | int | NOT NULL |  |  |
| `pProfileTotalCnt` | int | NOT NULL |  |  |
| `pSoapTotalCnt` | int | NOT NULL |  |  |
| `pCertificationId` | varchar(21) | NOT NULL |  |  |
| `pHciTransmittalNumber` | varchar(21) | NOT NULL |  |  |
| `created_at` | timestamp | NULL | NULL |  |
| `updated_at` | timestamp | NULL | NULL |  |

### `phic_charges`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `px_pin` | varchar(50) | NULL | NULL |  |
| `px_consultcode_cn` | varchar(50) | NULL | NULL |  |
| `en_caseno` | varchar(27) | NULL | NULL |  |
| `s_TransNo` | varchar(27) | NULL | NULL |  |
| `source_entry` | enum('SOAP','CLINIC') | NULL | NULL |  |
| `pxname` | varchar(120) | NULL | NULL |  |
| `item_grouping` | enum('DRUGS AND MEDS','SUPPLIES','PROCEDURES','DIAGNOSTIC','IMAGING','PROFESSIONAL FEE','OTHERS') | NULL | NULL |  |
| `prod_code` | varchar(80) | NULL | NULL |  |
| `drug_code` | varchar(30) | NULL | NULL | CODE REF: GENCODE+SALTCODE+STRENGTHCODE+FORMCODE+UNITCODE+PACKAGECODE |
| `gen_code` | varchar(5) | NULL | NULL | GENERIC CODE OF MEDICINE |
| `salt_code` | varchar(5) | NULL | NULL |  |
| `strength_code` | varchar(5) | NULL | NULL | STRENGTH/DOSAGE CODE OF MEDICINE |
| `form_code` | varchar(5) | NULL | NULL | FORM/ROUTE/PREPARATION CODE OF MEDICINE |
| `unit_code` | varchar(5) | NULL | NULL |  |
| `package_code` | varchar(5) | NULL | NULL | PACKAGE CODE OF MEDICINE |
| `route` | varchar(1000) | NULL | NULL |  |
| `generic_name` | varchar(1000) | NULL | NULL |  |
| `prescribed_quantity` | double(11, 2) | NULL | NULL |  |
| `ins_strength` | varchar(50) | NULL | NULL | STOCK DOSAGE OF MEDICINE PER INSTRUCTION |
| `ins_frequency` | varchar(50) | NULL | NULL | FREQUENCY OF MEDICINE PER INSTRUCTION |
| `qty` | double(11, 2) | NULL | NULL | NUMBER OF MEDICINES PRESCRIBED |
| `unit` | varchar(50) | NULL | NULL |  |
| `isvatable` | tinyint | NULL | NULL |  |
| `actual_price` | double(11, 2) | NULL | NULL | DRUG ACTUAL PRICE LOOK - unit price |
| `phic_price` | double(11, 2) | NULL | NULL |  |
| `co_payment` | double(11, 2) | NULL | NULL | COPAYMENT OF PATIENT (total_price) |
| `total_vatamt` | double(11, 2) | NULL | NULL | (total_price / 1.12) x 0.12 |
| `total_price` | double(11, 2) | NULL | NULL | qty * actual_PRICE |
| `doc_code` | varchar(50) | NULL | NULL |  |
| `doc_name` | varchar(200) | NULL | NULL | PRESCRIBING DOCTOR/PHYSICIAN |
| `is_applicable` | varchar(1) | NULL | NULL |  |
| `checkup_trans_no` | varchar(25) | NULL | NULL | TRANSMITTAL NUMBER ON THE GENERATED REPORT |
| `status` | enum('U','V','F') | NULL | NULL | U - UNVALIDATED; V - VALIDATED; F - FAILED |
| `deficiency_remarks` | varchar(500) | NULL | NULL | REMARKS OF THE REPORT STATUS |
| `updated` | datetime | NOT NULL |  | DATE AND TIME THE RECORD WAS ADDED ---SYSDATE |
| `updatedby` | varchar(20) | NULL | NULL | REFER TO THE USER WHO ADDED THE RECORD ---LOGGED USER |
| `is_dispensed` | enum('Y','N') | NULL | NULL |  |
| `dispensed_date` | date | NULL | NULL |  |
| `dispensedby` | varchar(80) | NULL | NULL |  |
| `category` | varchar(50) | NULL | NULL |  |
| `sub_grouping` | varchar(50) | NULL | NULL |  |

### `tsekap_seqno`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `SEQ_NAME` | varchar(20) | NULL | NULL | SEQUENCE NAME |
| `SEQ_FORMAT` | varchar(15) | NULL | NULL | SEQUENCE FORMAT |
| `SEQ_DESC` | varchar(100) | NULL | NULL | SEQUENCE DESCRIPTION |
| `SEQ_PREFIX` | varchar(10) | NULL | NULL | SEQUENCE PREFIX |
| `CYCLE_PERIOD_FORMAT` | varchar(15) | NULL | NULL | CYCLE PERIOD FORMAT |

### `tsekap_seqno_det`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `SEQ_NAME` | varchar(25) | NULL | NULL | SEQUENCE NAME |
| `seq_code` | varchar(25) | NULL | NULL |  |
| `LAST_VALUE` | int | NULL | NULL | CURRENT SEQUENCE NUMBER OF GENERATED CODE |
| `LAST_GEN_DATE` | datetime | NULL | NULL | LAST GENERATION DATE |
| `LAST_GEN_BY` | varchar(20) | NULL | NULL | LAST GENERATED BY |

### `dq_tbl_upload`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `UPLOAD_ID` | varchar(27) | NULL | NULL |  |
| `UPLOAD_XML` | longtext | NOT NULL |  | XML DATA |
| `UPLOAD_MODULE` | varchar(100) | NULL | NULL | MODULE WHERE XML UPLOADED |
| `DATE_UPLOADED` | datetime | NOT NULL |  | MODULE WHERE XML UPLOADED |
| `RANGE_DATE` | varchar(50) | NULL | NULL | Range from start to end date of search results |

### `xml_enlist_uploading`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `UPLOAD_ID` | varchar(27) | NULL | NULL |  |
| `UPLOAD_XML` | longtext | NOT NULL |  | XML DATA |
| `DATE_UPLOADED` | datetime | NOT NULL |  | MODULE WHERE XML UPLOADED |
| `RANGE_DATE` | varchar(50) | NULL | NULL | Range from start to end date of search results |
| `status` | enum('PENDING','DONE') | NULL | NULL |  |
| `importedby` | varchar(80) | NULL | NULL |  |
| `imported` | datetime | NULL | NULL |  |

### `xml_transmittal_reports`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | tinyint | NULL | NULL |  |
| `dw_clientcode` | varchar(12) | NULL | NULL |  |
| `accre_no` | varchar(9) | NULL | NULL | Accreditation Number |
| `report_code` | varchar(27) | NULL | NULL |  |
| `trans_type` | enum('INDIVIDUAL','BATCH') | NULL | NULL |  |
| `tranche_type` | enum('FIRST','SECOND') | NULL | NULL |  |
| `date_range_start` | date | NOT NULL |  | Date range of generated report |
| `date_range_end` | date | NULL | NULL | Date range of generated report |
| `date_generated` | date | NOT NULL |  | Date Generated |
| `XML_CONTENT` | longtext | NULL |  | XML Content |
| `ENCRYPTED_CONTENT` | longtext | NULL |  | Content Encrypted |
| `verifiedby` | varchar(80) | NULL | NULL |  |
| `status` | enum('PENDING','FOR TRANSMITTAL','TRANSMITTED') | NULL | NULL |  |
| `transmittal_date` | datetime | NULL | NULL |  |
| `transmittal_refno` | varchar(80) | NULL | NULL |  |
| `transmittedby` | varchar(80) | NULL | NULL |  |

## 15. Uncategorized / Possibly Unrelated

`pastudents` is a student-enrollment schema (school code, enrollee type, registration type, student ID workflow) that does not fit the clinic/PhilHealth domain of every other table in this database. It is included here only for completeness — verify with the application owner whether it belongs in this schema at all (e.g. leftover from a shared dev database) or is a genuine module (e.g. school-based clinic enrollee tracking).

### `pastudents`

| Column | Type | Null? | Default | Notes |
|---|---|---|---|---|
| `id` | bigint | NULL | NULL |  |
| `schoolcode` | int | NULL | NULL |  |
| `studenttype` | enum('STUDENT','ALLUMNI','DEACTIVATED') | NULL | NULL |  |
| `studentidno` | varchar(50) | NULL | NULL |  |
| `oldstudentidno` | varchar(50) | NULL | NULL |  |
| `studentcode` | varchar(150) | NULL | NULL |  |
| `applicantcodeid` | varchar(50) | NULL | NULL |  |
| `alias` | varchar(50) | NULL | NULL |  |
| `studentname` | varchar(50) | NULL | NULL |  |
| `firstname` | varchar(50) | NULL | NULL |  |
| `lastname` | varchar(50) | NULL | NULL |  |
| `midname` | varchar(50) | NULL | NULL |  |
| `sex` | enum('MALE','FEMALE') | NULL | NULL |  |
| `emailadd` | varchar(120) | NULL | NULL |  |
| `cpno` | varchar(15) | NULL | NULL |  |
| `username` | varchar(50) | NULL | NULL |  |
| `passcode` | varchar(255) | NULL | NULL |  |
| `reset` | tinyint | NULL | NULL |  |
| `access_status` | enum('PENDING','DEACTIVATED','LOCK','ACTIVE','DEFERRED','FOR ACTIVATION') | NULL | NULL |  |
| `lastlogin` | datetime | NULL | NULL |  |
| `updated` | datetime | NULL | NULL |  |
| `station` | varchar(50) | NULL | NULL |  |
| `programcategory` | varchar(50) | NULL | NULL |  |
| `programcode` | varchar(30) | NULL | NULL |  |
| `programdscr` | varchar(120) | NULL | NULL |  |
| `year_level` | varchar(10) | NULL | NULL |  |
| `courseyear` | varchar(50) | NULL | NULL |  |
| `enrolleecode` | varchar(50) | NULL | NULL |  |
| `enrolleetype` | enum('REGULAR','IRREGULAR') | NULL | NULL |  |
| `registrationtype` | enum('NEW ENROLEE','OLD STUDENT','TRANSFEREE') | NULL | NULL |  |
| `entrytpe` | enum('AUTOMATED','MANUAL') | NULL | NULL |  |
| `accessgrantedby` | varchar(80) | NULL | NULL |  |
| `accessgranted` | datetime | NULL | NULL |  |
| `prospectus` | varchar(50) | NULL | NULL |  |
| `dataentry_status` | enum('PENDING','VERIFIED') | NULL | NULL |  |
| `user_expirydate` | date | NULL | NULL |  |
| `user_passwordupdated` | datetime | NULL | NULL |  |
| `birthdate` | date | NULL | NULL |  |
| `birthplace` | varchar(80) | NULL | NULL |  |
| `parentsguardian` | varchar(180) | NULL | NULL |  |
| `passkeyprovided` | varchar(50) | NULL | NULL |  |
| `email_otp` | int | NULL | NULL |  |
| `email_verified` | tinyint | NULL | NULL |  |
| `email_otp_expiry` | datetime | NULL | NULL |  |
| `cpno_otp` | int | NULL | NULL |  |
| `cp_verified` | tinyint | NULL | NULL |  |
| `cp_otp_expiry` | datetime | NULL | NULL |  |
| `deferred_reason` | varchar(200) | NULL | NULL |  |
| `studentid_status` | enum('NO ATTACHMENT','ATTACHED','VERIFIED','REJECTED') | NULL | NULL |  |
