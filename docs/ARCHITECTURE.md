# Developer Architecture Guide

A quick reference for how this Laravel application is structured and how to add new pages or features.

---

## How It Works (Request Flow)

```
URL → routes/web.php → Controller → Blade View
                                        ↓
                               @push('scripts') loads JS
                                        ↓
                               JS fetches data via API
                                        ↓
                               routes/api.php → Controller method → Model → JSON response
```

---

## File Structure Overview

```
routes/
  web.php              # Page routes only (returns views)
  api.php              # Data routes only (returns JSON)

app/Http/Controllers/
  ManagementController.php        # Admin pages & API methods
  DoctorController.php            # Doctor pages & API methods
  SecretaryController.php         # Secretary pages & API methods
  Api/
    YakapManagementApiController.php  # Dedicated API controller (use for complex features)

app/Models/
  {Feature}Model.php              # Flat models (e.g. MedicineModel.php)
  DiagExamResults/                # Grouped by feature category
  Profiles/
  Soaps/
  Libraries/

resources/views/
  layouts/                        # Base layout files
  pages/
    admin/{category}/{feature}.blade.php
    doctor/{feature}.blade.php
    secretary/{feature}.blade.php
  components/
    sidebar.blade.php             # Navigation sidebar
  modals/                         # Modal partials

resources/js/
  pages/
    admin/{category}/{feature}.js
    doctor/{feature}.js
    secretary/{feature}.js
  helper.js                       # Shared utility functions
```

---

## Adding a New Page / Feature

Follow these steps in order:

### 1. Create the Blade view
`resources/views/pages/{role}/{category}/{feature}.blade.php`

```blade
@extends('layouts.app')

@push('scripts')
    @vite('resources/js/pages/{role}/{category}/{feature}.js')
@endpush

@section('content')
    {{-- Page HTML here --}}
@endsection

@push('modals')
    {{-- Optional: include modal partials here --}}
@endpush
```

### 2. Add a Controller method
In the relevant controller (e.g. `ManagementController` for admin), add a page-serving method:

```php
public function featurePage(): View
{
    return view('pages.admin.category.feature');
}
```

For API data methods, return JSON:

```php
public function fetchFeatureData(Request $request): JsonResponse
{
    $data = FeatureModel::all();
    return response()->json(['data' => $data]);
}
```

> If the feature is complex, create a dedicated API controller under `app/Http/Controllers/Api/`.

### 3. Add the route in `routes/web.php`
`routes/web.php` — page URLs only, no data fetching here.

```php
// Admin Routes — page views only
Route::middleware('auth:admin')->group(function () {
    Route::get('admin/category/feature', [ManagementController::class, 'featurePage'])
        ->name('admin.category.feature');
});
```

### 4. Add the route in `routes/api.php`
`routes/api.php` — data endpoints only, always return JSON.

```php
Route::middleware(['web', 'auth:admin'])->group(function () {
    Route::post('fetch_feature_data', [ManagementController::class, 'fetchFeatureData']);
});
```

### 5. Add the menu item to the sidebar
`resources/views/components/sidebar.blade.php`

For a simple link:
```blade
<li class="nav-item">
    <a class="nav-link text-dark {{ request()->routeIs('admin.category.feature') ? 'active' : '' }}"
        href="{{ route('admin.category.feature') }}">
        <i class="fa-solid fa-icon"></i> Feature Name
    </a>
</li>
```

For a collapsible group (when adding to an existing category):
```blade
<a class="nav-link text-dark {{ request()->routeIs('admin.category.feature') ? 'active' : '' }}"
    style="font-size: 14px; padding-left: 3rem;" href="{{ route('admin.category.feature') }}">
    Feature Name
</a>
```

### 6. Create the JavaScript file
`resources/js/pages/{role}/{category}/{feature}.js`

```js
import axios from 'axios';

// Fetch data from the API
function loadData() {
    axios.post('/fetch_feature_data')
        .then(response => {
            // handle response.data
        });
}

document.addEventListener('DOMContentLoaded', () => {
    loadData();
});
```

---

## Conventions

### Routes (`web.php` vs `api.php`)
- `web.php` — only for routes that return a **view**. No JSON here.
- `api.php` — only for routes that return **JSON**. All API routes use `['web', 'auth:{role}']` middleware.
- Group routes by role: `auth:admin`, `auth:doctor`, `auth:secretary`.
- Use named routes in `web.php`: `->name('admin.category.feature')`.

### Controllers
- Page-serving methods and API methods can live in the same controller (e.g. `ManagementController`).
- For feature-heavy areas, create a dedicated controller under `Api/` (e.g. `YakapManagementApiController`).
- Page methods return `view(...)`, API methods return `response()->json(...)`.

### Views (`resources/views/pages/`)
- Organized by role, then category: `pages/{role}/{category}/{feature}.blade.php`.
- Always extend `layouts.app`.
- Load the feature JS via `@push('scripts')` with `@vite(...)`.
- Include modals via `@push('modals')`.

### JavaScript (`resources/js/pages/`)
- Mirror the same folder structure as views: `pages/{role}/{category}/{feature}.js`.
- Each page gets its own JS file — no mixing features.
- Shared utilities go in `helper.js`.
- Use `axios` for all API calls to `api.php` routes.

### Models (`app/Models/`)
- Simple models live flat: `app/Models/FeatureModel.php`.
- Group related models into subfolders by feature category:
  - `DiagExamResults/` — diagnostic exam result models
  - `Profiles/` — patient profile section models
  - `Soaps/` — SOAP note section models
  - `Libraries/` — reference/library data models

---

## Real Example: Yakap Management (PhilHealth)

| Layer | File |
|---|---|
| Route (web) | `routes/web.php` → `admin/philhealth/yakap-management` |
| Controller | `YakapManagementController@index` → returns view |
| View | `resources/views/pages/admin/philhealth/yakap-management.blade.php` |
| JS | `resources/js/pages/admin/philhealth/yakap-management.js` |
| API Routes | `routes/api.php` → `YakapManagementApiController` |
| Models | `app/Models/EnlistmentModel.php`, `KayakapProfileModel.php` |
| Sidebar | `resources/views/components/sidebar.blade.php` under "PhilHealth Yakap" group |
