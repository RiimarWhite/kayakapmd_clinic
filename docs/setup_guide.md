# KayakapMD System

**DrainWiz Computer Systems**

Documented by: Raphydhar-Neezamme J. Ibrahim
Documented on: March 18, 2026

---

## KayakapMD Setup

### Requirements
- Composer
- npm
- PHP ^8.5.4 (project version can be adjusted by changing PHP version inside `composer.lock`)

### Installation Steps

1. Place project files inside the XAMPP `htdocs` directory.
2. Perform `composer install` on the project terminal.
   - This installs the packages (vendors) required for the project, respective to the PHP version of the project.
3. Execute `npm i` or `npm install` to set up node packages.
4. Copy the default `.env` file:
   ```
   cp .env.example .env
   ```
   - Replace `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` accordingly.
5. Execute `php artisan key:generate` to generate a key for the project.
6. Execute `php artisan migrate` to create project tables and data dictionary tables.
   - Ensure the tables listed below are present. If not, copy them from an existing database.
7. Execute `php artisan db:seed AdminSeeder` to generate the default admin account.
   - Username: `admin`
   - Password: `admin123`
8. Execute `php artisan db:seed UserSeeder` to generate dummy data.
9. For client copies, run `npm run build` to compile JavaScript, CSS, and node packages.

---

### Database Tables

#### Data Dictionaries
PhilHealth tables, necessary for API requests. `dd_soap` contains data for consultations, `dd_enlistment` for patients.

- dd_advice
- dd_bloodtype
- dd_cbc
- dd_chestxray
- dd_creatine
- dd_diagnostic
- dd_diagnosticexamresult
- dd_document
- dd_ecg
- dd_enlistment
- dd_famhist
- dd_fbs
- dd_fecalysis
- dd_fhspecific
- dd_fobt
- dd_hba1c
- dd_icd
- dd_immunization
- dd_lipidprofile
- dd_management
- dd_medhist
- dd_mhspecific
- dd_ncdqans
- dd_ogtt
- dd_otherdiagexam
- dd_papsmear
- dd_pcb
- dd_pegensurvey
- dd_pemisc
- dd_pepert
- dd_pespecific
- dd_ppdtest
- dd_preghist
- dd_profile
- dd_rbs
- dd_soap_consultation / dd_soap
- dd_sochist
- dd_sputum
- dd_subjective
- dd_surghist
- dd_urinalysis

#### Libraries
Tables used for dependencies, read-only.

- lib_abdomen
- lib_barangay
- lib_chest
- lib_chestxray-findings
- lib_chestxray_observation
- lib_diagnostic
- lib_digital_rectal
- lib_genitourinary
- lib_heart
- lib_heent
- lib_icd
- lib_immchild
- lib_immelderly
- lib_immpregw
- lib_immyoungw
- lib_management
- lib_mdisease
- lib_medicine
- lib_medicine_form
- lib_medicine_generic
- lib_medicine_package
- lib_medicine_salt
- lib_medicine_strength
- lib_medicine_unit
- lib_municipality
- lib_ncdq
- lib_neuro
- lib_province
- lib_region
- lib_signs_symptoms
- lib_skin_extremities
- lib_zipcode
- lib_zscore

#### Project Tables
Very important project tables.

| Table | Description |
|---|---|
| adminrights | User credentials for admin |
| cache | — |
| cache_locks | — |
| charges_category | Charges category table |
| charges_masterlist | List of all charges |
| diagnostic_category | Diagnostic category table |
| diagnostics_masterlist | List of all diagnostics |
| docquestion | List of doctor's questions |
| docrequests | List of doctor's diagnostic request to patients |
| docschedules | List of doctor's schedules |
| doctorcharges | List of doctor's charges |
| doctors | Doctor profiles (license, name) |
| doctorservices | List of doctor's services |
| doctorsgroup | List of doctor's expertise group (e.g. pediatrician) |
| doctorsrights | User credentials for doctors |
| failed_jobs | — |
| hci_profile | Company profile |
| job_batches | — |
| jobs | — |
| medicine_masterlist | List of all medicines |
| migrations | — |
| profgroup_masterlist | — |
| profile | Company profile (old) |
| pxcharges | Patient's charges list |
| pxmasterlist | List of all patients |
| pxrxdocuments | List of RX assigned to a patient |
| pxsettlements | Patient's settlements |
| pxwalkinconsultation | List of all consultations and respective patients. Laboratory files are listed as `laboratory_path`; radiology files as `radiology_files` |
| secretary_doctor | Stores data of which doctors are assigned to a secretary |
| secretaryrights | User credentials for secretary |
| servicesgroups | Category for services |
| sessions | — |
| walkinconsultation_answer | List of user answers respective to docquestions |

---

## Guide

### Local Hosting (own computer)

- Execute `php artisan serve` to run the project locally. Ensure the setup steps above are done (aside from the first step).
- Alongside the serve command, run `npm run dev` to allow livereload (automatic reload when PHP or JavaScript files are changed).

### Not Working?

Possible causes:
- No composer packages.
- No npm packages.
- PHP version does not match version inside `composer.lock`.

### File Storage

- Images (e.g. patient pictures) are stored inside Laravel's `storage/app/private` folder.
- Company logo and images are stored inside the `public/` directory.

### Admin Panel

- **Profile**
  - Company profile editing and PhilHealth account.
- **Users Management**
  - Secretary
    - Add secretary here.
    - Assign doctors to secretary here.
  - Doctor
    - Add doctor accounts here.
    - Assign schedules to doctor here.
- **Diagnostics Management**
  - Requests
    - Create diagnostic type.
  - Category
    - Create diagnostic category.
- **Medicine Masterlist**
  - Create/add medicine to list as reference for RX.
- **Charges Management**
  - Masterlist
    - Create charges.
  - Category
    - Create charges category.
- **PhilHealth**
  - eKonsulta link (for debug and reference; should be commented out on the client copy)
  - Consultations
    - Export patients to masterlist — `pxwalkinconsultation` data into `pxmasterlist`.
    - Convert selected to SOAP — convert `pxwalkinconsultation` data into PhilHealth format inside `dd_soap_consultations`.
  - Patient Masterlist
    - View `pxmasterlist` and their respective pincode (`pHciCaseNo`), casecode (`pHciTransNo`).
- **Reports**
  - View and print settlements.

> **Note:** `pincode` is `pHciCaseNo` — this refers to the patient and is unique per different patient. `casecode` is `pHciTransNo` — this refers to the transactions/consultation per patient; a patient can have multiple cases.

---

## Process Flow

### Login
- Use secretary's last name for secretary login.
- Use doctor's last name/username for doctor login.
- Use admin username for admin login.

### Secretary
1. Add patient — fill up patient data, member or dependent.
2. Apply consultation date (optional).
3. Added patient goes to the unscheduled patients table (left).
4. Click the import button (blue arrow).
5. Patient information should be present on the form (right).
6. Edit patient details (weight, height, blood pressure).
7. Assign consultation date and doctor to patient — do not skip.
8. Update consultation or save as new consultation (if existing patient).
9. Patient is now scheduled and should show under the patients queue on their respective dates.
10. Can be rescheduled and edited.

### Doctor
1. Patient is now visible under today's patients if the date matches the consultation date.
2. Open the consultations tab.
3. Click patient on the patients table.
4. Patient info shows on the right.
5. Click proceed.
6. Patient Rx, diagnostics, and laboratory/radiology files should be editable and accessible.
7. Add/generate Rx opens a new modal to input selected Rx or write instructions.
8. Add/append diagnostics opens a new modal wherein diagnostic requests can be assigned to the patient.
9. Laboratory and radiology tab allows uploading or viewing of patient documents.

### Back to Secretary
- Import patient to the right; patient charges/settlements should then reflect the charges assigned by the doctor.
