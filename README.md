# KayakapMD Clinic

`Started: 12-17-2025`

**KayakapMD Clinic** is a comprehensive, web-based outpatient clinic management and consultation system built with Laravel and Vite. It supports Secretary, Doctor, Cashier, and Administrator consoles with integrated PhilHealth TSEKAP/Yakap profiling, clinical notes (SOAP), diagnosis/prescription management, charges settlement, and PhilHealth e-Claims XML transmittal workflows.

---

## System Requirements

1. **PHP**: ^8.2 (with `gd`, `pdo_mysql`, `mbstring`, `xml`, `openssl`, `curl` extensions enabled)
2. **Composer**: ^2.0 (https://getcomposer.org/)
3. **Node.js**: ^18.0 or ^20.0+ & npm (https://nodejs.org/)
4. **Database**: MySQL 8.0+ or MariaDB 10.4+
5. **Web Server**: Apache/Nginx or Laravel built-in development server (XAMPP / Docker supported)

---

## Automated Setup (Recommended)

Run the automated setup script which handles prerequisites verification, environment configuration, dependency installation, key generation, migrations, and asset compilation:

```bash
# On Linux, macOS, WSL, or Git Bash
bash setup.sh

# Or without starting the servers:
bash setup.sh --no-serve
```

---

## Manual Setup

1. **Clone or place the project**:
   If using XAMPP on Windows, place inside `C:\xampp\htdocs\kayakapmd_clinic`.

2. **Install PHP and Node dependencies**:
   ```bash
   composer install
   npm install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   ```

   Update the essential variables in `.env`:
   ```env
   APP_NAME=KayakapMD
   APP_ENV=local
   APP_KEY=
   APP_DEBUG=true
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=kayakapmdv2
   DB_USERNAME=root
   DB_PASSWORD=

   CLIENT_CODE="122377"

   # PhilHealth e-Claims API & Transmission Configuration
   PHILHEALTH_URL="https://epress.philhealth.gov.ph"
   PHILHEALTH_PASSPHRASE="kayakapmd"
   ```

4. **Generate Application Key**:
   ```bash
   php artisan key:generate
   ```

5. **Run Database Migrations & Seeders**:
   ```bash
   php artisan migrate
   # Seed default administrator if new database:
   php artisan db:seed --class=AdminSeeder
   ```

6. **Build Frontend Assets**:
   ```bash
   npm run build
   ```

---

## Running the Application

In development, run the Laravel backend server and Vite asset compiler concurrently:

```bash
# Terminal 1: Backend Server
php artisan serve

# Terminal 2: Frontend Asset Bundler
npm run dev
```

The application will be accessible at: `http://localhost:8000` (or `http://localhost/kayakapmd_clinic/public` if using Apache virtual host / alias).

---

## Core Modules & Features

- **Secretary Console**: Patient queue management, vital signs recording, patient classification, walk-in consultation registration, and Yakap Medical History/Profiling.
- **Doctor Console**: Patient consultation cards, history of present illness (`hpi`), pertinent past medical history (`pmh`), chief complaints, diagnosis & ICD codes, electronic prescriptions (Rx), and diagnostic exam requests.
- **Admin Console**: Facility profile, theme customization, user accounts (Doctors, Secretaries, Admins), clinic schedules, charges categories, inventory & pharmacy management, and billing settlements.
- **PhilHealth e-Claims & Yakap Subsystem**:
  - Tranche 1 & Tranche 2 XML transmittal report generation.
  - AES-256-CBC payload encryption and decryption.
  - XML preview, formatted transmittal downloads, and direct PhilHealth web service transmission (`getToken`, `isMemberDependentRegistered`, `isATCValid`, `validateReport`, `submitReport`).

---

## Project Directory Structure

```text
kayakapmd_clinic/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Api/                       # Dedicated API controllers (PhilHealthApiController, etc.)
│   │   │   ├── ConsultationController.php # Consultation workflow & details endpoints
│   │   │   ├── DoctorController.php       # Doctor console views and clinical API
│   │   │   ├── ManagementController.php   # Admin console, XML transmittals, billing & settings
│   │   │   ├── SecretaryController.php    # Secretary console, queue, vitals & profiling
│   │   │   └── ReportController.php       # PDF printable report generators (Dompdf)
│   ├── Models/                            # Eloquent Models (ConsultationModel, PCBModel, etc.)
│   │   ├── DiagExamResults/               # PhilHealth lab & diagnostic exam result models
│   │   ├── Profiles/                      # Patient medical history & profiling sub-models
│   │   ├── Soaps/                         # Consultation clinical notes & vitals sub-models
│   │   └── Libraries/                     # PSGC, ICD diagnosis, and disease reference models
│   └── Services/
│       ├── Admin/AdminService.php         # Transmittal XML DOM builder & AES-256 cipher engine
│       └── YakapManagement/ApiService.php # PhilHealth e-Claims REST API client
├── config/                                # Application configuration (services, database, etc.)
├── database/
│   ├── migrations/                        # Database schema migrations
│   └── seeders/                           # Baseline database seeders (AdminSeeder, UserSeeder)
├── docs/                                  # Architectural documentation and guides
├── resources/
│   ├── css/                               # Global stylesheets and Bootstrap customizations
│   ├── js/
│   │   ├── pages/
│   │   │   ├── admin/                     # Admin module scripts (philhealth reports, users, stocks)
│   │   │   ├── doctor/                    # Doctor console scripts (consultation, dashboard, patients)
│   │   │   └── secretary/                 # Secretary console scripts (queue, patient linking)
│   │   └── app.js                         # Main frontend entry point
│   └── views/
│       ├── components/                    # Reusable Blade components (navbar, sidebar)
│       ├── layouts/                       # Master layouts (app.blade.php)
│       ├── modals/                        # Modal partials (yakap_medhistory_modal, generate_xml, etc.)
│       ├── pages/                         # Role-based blade view templates
│       └── printables/                    # Print templates for Rx, lab requests, and financial reports
├── routes/
│   ├── web.php                            # Web page navigation routes
│   └── api.php                            # AJAX / REST API endpoints
├── tests/
│   ├── Feature/                           # End-to-end feature test suites (182 tests)
│   └── Unit/                              # Unit test cases
├── .env.example                           # Template environment configuration with PhilHealth defaults
├── setup.sh                               # Idempotent cross-platform setup script
└── vite.config.js                         # Vite asset bundling and alias configuration
```

---

## Testing

Run the automated test suite with PHPUnit / Artisan:

```bash
php artisan test
```
