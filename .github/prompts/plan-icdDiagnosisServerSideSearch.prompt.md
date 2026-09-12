## Plan: Server-Side Search for ICD Diagnosis Dropdown

The current implementation loads 13k ICD diagnosis options into the DOM, causing severe performance issues. This plan implements server-side search using select2 AJAX with:

- Initial load of 20 options
- Debounced server-side search (300ms delay)
- 20 results per search
- Preserving selected values in the diagnosis table

**Steps**

1. **Create API endpoint for ICD diagnosis search** (_independent_)
    - Add GET route `/api/icd-diagnosis/search` in [routes/api.php](routes/api.php)
    - Create controller method `searchIcdDiagnosis` in [app/Http/Controllers/Api/YakapManagementController.php](app/Http/Controllers/Api/YakapManagementController.php)
    - Return JSON with id, text format for select2 compatibility
    - Implement search on both `icd_code` and `icd_desc` fields
    - Limit results to 20

2. **Update Blade template to remove server-side options** (_depends on step 1_)
    - Modify [resources/views/components/yakap-management/soap-tabs/assessment-diagnosis.blade.php](resources/views/components/yakap-management/soap-tabs/assessment-diagnosis.blade.php)
    - Remove `@foreach ($icdDiagnosisLibrary as $diagnosis)` loop
    - Keep single placeholder option only

3. **Implement select2 AJAX configuration** (_depends on step 2_)
    - Modify [resources/js/pages/admin/philhealth/soap-form.js](resources/js/pages/admin/philhealth/soap-form.js)
    - Configure select2 with AJAX settings:
        - URL: `/api/icd-diagnosis/search`
        - Data function: pass search term (`q`) parameter
        - Process results: map response to select2 format
        - Delay: 300ms (debounce)
        - Minimum input length: 0 (allow initial load)
        - Cache: true (reduce redundant requests)

4. **Update diagnosis table row insertion** (_depends on step 3_)
    - Modify `addDiagnosisBtn` click handler in [resources/js/pages/admin/philhealth/soap-form.js](resources/js/pages/admin/philhealth/soap-form.js)
    - Store both `refcode` value and display text
    - Ensure data is preserved for form submission

5. **Update controller to limit initial library load** (_parallel with step 1_)
    - Modify `fetchLibraries` method in [app/Http/Controllers/Api/YakapManagementController.php](app/Http/Controllers/Api/YakapManagementController.php)
    - Note: Method already has `limit(20)` but currently loads all for IcdLibModel
    - Pass pagination parameter or remove `$icdDiagnosisLibrary` from compact (no longer needed in view)

**Relevant files**

- [routes/api.php](routes/api.php) — add new search endpoint
- [app/Http/Controllers/Api/YakapManagementController.php](app/Http/Controllers/Api/YakapManagementController.php) — create `searchIcdDiagnosis` method, modify `index` method
- [app/Models/Libraries/IcdLibModel.php](app/Models/Libraries/IcdLibModel.php) — model for querying `dw_lib_icd` table (fields: `refcode`, `icd_code`, `icd_desc`, `lib_stat`)
- [resources/views/components/yakap-management/soap-tabs/assessment-diagnosis.blade.php](resources/views/components/yakap-management/soap-tabs/assessment-diagnosis.blade.php) — remove foreach loop
- [resources/js/pages/admin/philhealth/soap-form.js](resources/js/pages/admin/philhealth/soap-form.js) — implement select2 AJAX configuration

**Verification**

1. Load YAKAP management page - verify no 13k options rendered in DOM (inspect element should show only placeholder)
2. Click diagnosis dropdown without typing - verify first 20 options load
3. Type search terms (e.g., "diabetes") - verify results appear after 300ms delay
4. Type quickly - verify only one request sent after typing stops (debounce working)
5. Select diagnosis and click "Add Diagnosis" - verify correct code and description appear in table
6. Check browser performance (DevTools Performance tab) - verify no lag when opening dropdown
7. Test search on both ICD code and description - verify both fields are searchable
8. Submit SOAP form - verify selected diagnoses are saved correctly

**Decisions**

- **Search fields**: Search both `icd_code` and `icd_desc` for better UX
- **Debounce delay**: 300ms balances responsiveness with server load
- **Result limit**: 20 items prevents DOM bloat while providing enough options
- **Minimum input length**: 0 allows initial load of 20 options without typing
- **Cache enabled**: Reduces duplicate requests for same search terms
- **API method**: GET (read operation) vs POST (existing pattern uses POST)
- **Remove `$icdDiagnosisLibrary` from controller**: No longer needed since options load via AJAX

**Further Considerations**

1. **Should we apply this pattern to other large dropdowns?** The medicine dropdown (`#dMedicine`) also uses `$icdDiagnosisLibrary` variable (found in [resources/views/components/yakap-management/soap-tabs/medicine.blade.php](resources/views/components/yakap-management/soap-tabs/medicine.blade.php)). Should we refactor it similarly?

2. **API authentication**: Should the new API endpoint require authentication/authorization? Current pattern shows other API routes may have middleware protection.

3. **Error handling**: Should we add user-friendly error messages if the AJAX request fails (network error, server error)?
