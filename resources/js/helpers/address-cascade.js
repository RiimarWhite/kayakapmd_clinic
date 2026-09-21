/**
 * Detailed Comment: Reusable PSGC Address Cascade Helper for KayakapMD Clinic.
 * Interfaces with the Philippine Standard Geographic Code (PSGC) reference tables:
 * - lib_region (Regions)
 * - lib_province (Provinces)
 * - lib_municipality (Cities and Municipalities)
 * - lib_barangay (Barangays)
 * - lib_zipcode (Postal Zip Codes)
 * Provides asynchronous cascading population, composite address string generation,
 * and programmatic pre-population for Edit modals across Patient Masterlist,
 * Doctor Management, Secretary Management, and Company Profile.
 */

export function initAddressCascade(options) {
    const $region = $(options.regionSel);
    const $prov = $(options.provSel);
    const $mun = $(options.munSel);
    const $brgy = $(options.brgySel);
    const $zip = options.zipInput ? $(options.zipInput) : null;
    const $street = options.streetInput ? $(options.streetInput) : null;
    const $fullAddress = options.fullAddressInput ? $(options.fullAddressInput) : null;

    let isProgrammaticSet = false;

    // Helper: update compiled address string
    function updateFullAddress() {
        if (!$fullAddress || !$fullAddress.length) return;

        const street = $street ? ($street.val() || '').trim() : '';
        const brgyText = $brgy && $brgy.find('option:selected').text().trim();
        const munText = $mun && $mun.find('option:selected').text().trim();
        const provText = $prov && $prov.find('option:selected').text().trim();
        const zipVal = $zip ? ($zip.val() || '').trim() : '';

        const parts = [];
        if (street) parts.push(street);
        if (brgyText && !brgyText.startsWith('--')) parts.push('Brgy. ' + brgyText);
        if (munText && !munText.startsWith('--')) parts.push(munText);
        if (provText && !provText.startsWith('--')) parts.push(provText);
        if (zipVal) parts.push(zipVal);

        const compiled = parts.join(', ');
        $fullAddress.val(compiled);

        if (typeof options.onAddressChange === 'function') {
            options.onAddressChange({
                street,
                brgy: brgyText && !brgyText.startsWith('--') ? brgyText : '',
                muncity: munText && !munText.startsWith('--') ? munText : '',
                province: provText && !provText.startsWith('--') ? provText : '',
                region: $region ? $region.val() : '',
                zipcode: zipVal,
                fullAddress: compiled
            });
        }
    }

    // 1. Load administrative regions
    function loadRegions(selectedRegionVal, callback) {
        $.ajax({
            url: '/api/address/regions',
            type: 'GET',
            success: function (res) {
                if (res.regions) {
                    $region.empty().append('<option value="" selected disabled>-- Select Region --</option>');
                    res.regions.forEach(function (r) {
                        const code = r.REGION_CODE;
                        const proCode = r.PRO_CODE;
                        const desc = r.REGION_DESC || r.REGION_NAME;
                        $region.append(`<option value="${code}" data-procode="${proCode}">${desc}</option>`);
                    });

                    if (selectedRegionVal) {
                        // Match by code or description
                        const opt = $region.find(`option[value="${selectedRegionVal}"]`).length
                            ? selectedRegionVal
                            : $region.find(`option:contains("${selectedRegionVal}")`).val();
                        if (opt) $region.val(opt);
                    }

                    if (typeof callback === 'function') callback();
                }
            }
        });
    }

    // 2. Load provinces on region selection
    function loadProvinces(proCode, selectedProvVal, callback) {
        if (!proCode) {
            $prov.empty().append('<option value="" selected disabled>-- Select Province --</option>').prop('disabled', true);
            $mun.empty().append('<option value="" selected disabled>-- Select Municipality --</option>').prop('disabled', true);
            $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);
            if ($zip) $zip.val('');
            return;
        }

        $prov.prop('disabled', true).empty().append('<option value="" selected disabled>Loading provinces...</option>');
        $mun.empty().append('<option value="" selected disabled>-- Select Municipality --</option>').prop('disabled', true);
        $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);

        $.ajax({
            url: '/api/address/provinces',
            type: 'GET',
            data: { pro_code: proCode },
            success: function (res) {
                $prov.empty().append('<option value="" selected disabled>-- Select Province --</option>');
                if (res.provinces && res.provinces.length) {
                    res.provinces.forEach(function (p) {
                        $prov.append(`<option value="${p.PROVINCE}" data-procode="${p.PROCODE}">${p.PROV_NAME}</option>`);
                    });
                    $prov.prop('disabled', false);

                    if (selectedProvVal) {
                        const opt = $prov.find(`option[value="${selectedProvVal}"]`).length
                            ? selectedProvVal
                            : $prov.find(`option:contains("${selectedProvVal}")`).val();
                        if (opt) $prov.val(opt);
                    }
                }
                if (typeof callback === 'function') callback();
            }
        });
    }

    // 3. Load municipalities on province selection
    function loadMunicipalities(proCode, provCode, selectedMunVal, callback) {
        if (!provCode) {
            $mun.empty().append('<option value="" selected disabled>-- Select Municipality --</option>').prop('disabled', true);
            $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);
            if ($zip) $zip.val('');
            return;
        }

        $mun.prop('disabled', true).empty().append('<option value="" selected disabled>Loading municipalities...</option>');
        $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);

        $.ajax({
            url: '/api/address/municipalities',
            type: 'GET',
            data: { pro_code: proCode, province: provCode },
            success: function (res) {
                $mun.empty().append('<option value="" selected disabled>-- Select Municipality --</option>');
                if (res.municipalities && res.municipalities.length) {
                    res.municipalities.forEach(function (m) {
                        $mun.append(`<option value="${m.MUNICIPALITY}" data-procode="${m.PROCODE}" data-prov="${m.PROVINCE}">${m.MUN_NAME}</option>`);
                    });
                    $mun.prop('disabled', false);

                    if (selectedMunVal) {
                        const opt = $mun.find(`option[value="${selectedMunVal}"]`).length
                            ? selectedMunVal
                            : $mun.find(`option:contains("${selectedMunVal}")`).val();
                        if (opt) $mun.val(opt);
                    }
                }
                if (typeof callback === 'function') callback();
            }
        });
    }

    // 4. Load barangays and zipcode on municipality selection
    function loadBarangays(proCode, provCode, munCode, selectedBrgyVal, callback) {
        if (!munCode) {
            $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);
            if ($zip) $zip.val('');
            return;
        }

        $brgy.prop('disabled', true).empty().append('<option value="" selected disabled>Loading barangays...</option>');

        // Fetch barangays
        $.ajax({
            url: '/api/address/barangays',
            type: 'GET',
            data: { pro_code: proCode, province: provCode, municipality: munCode },
            success: function (res) {
                $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>');
                if (res.barangays && res.barangays.length) {
                    res.barangays.forEach(function (b) {
                        $brgy.append(`<option value="${b.BARANGAY}">${b.BRGY_NAME}</option>`);
                    });
                    $brgy.prop('disabled', false);

                    if (selectedBrgyVal) {
                        const opt = $brgy.find(`option[value="${selectedBrgyVal}"]`).length
                            ? selectedBrgyVal
                            : $brgy.find(`option:contains("${selectedBrgyVal}")`).val();
                        if (opt) $brgy.val(opt);
                    }
                }
                if (typeof callback === 'function') callback();
            }
        });

        // Fetch zipcode
        if ($zip) {
            $.ajax({
                url: '/api/address/zipcode',
                type: 'GET',
                data: { pro_code: proCode, province: provCode, municipality: munCode },
                success: function (res) {
                    if (res.zipcode) {
                        $zip.val(res.zipcode).prop('disabled', false);
                    }
                    updateFullAddress();
                }
            });
        }
    }

    // Event bindings
    $region.on('change', function () {
        if (isProgrammaticSet) return;
        const proCode = $(this).find('option:selected').data('procode');
        loadProvinces(proCode);
        updateFullAddress();
    });

    $prov.on('change', function () {
        if (isProgrammaticSet) return;
        const provCode = $(this).val();
        const proCode = $(this).find('option:selected').data('procode') || $region.find('option:selected').data('procode');
        loadMunicipalities(proCode, provCode);
        updateFullAddress();
    });

    $mun.on('change', function () {
        if (isProgrammaticSet) return;
        const munCode = $(this).val();
        const provCode = $(this).find('option:selected').data('prov') || $prov.val();
        const proCode = $(this).find('option:selected').data('procode') || $region.find('option:selected').data('procode');
        loadBarangays(proCode, provCode, munCode);
        updateFullAddress();
    });

    $brgy.on('change', function () {
        if (isProgrammaticSet) return;
        updateFullAddress();
    });

    if ($street) {
        $street.on('input', function () {
            if (isProgrammaticSet) return;
            updateFullAddress();
        });
    }

    if ($zip) {
        $zip.on('input', function () {
            if (isProgrammaticSet) return;
            updateFullAddress();
        });
    }

    // Programmatic address setter for edit modals
    function setAddressValues(data) {
        isProgrammaticSet = true;
        if ($street && data.streetadrs !== undefined) $street.val(data.streetadrs);
        if ($zip && data.zipcode !== undefined) $zip.val(data.zipcode).prop('disabled', false);
        if ($fullAddress && data.address !== undefined) $fullAddress.val(data.address);

        loadRegions(data.region, function () {
            const proCode = $region.find('option:selected').data('procode');
            if (proCode && data.province) {
                loadProvinces(proCode, data.province, function () {
                    const provCode = $prov.val();
                    if (provCode && data.muncity) {
                        loadMunicipalities(proCode, provCode, data.muncity, function () {
                            const munCode = $mun.val();
                            if (munCode && data.brgy) {
                                loadBarangays(proCode, provCode, munCode, data.brgy, function () {
                                    isProgrammaticSet = false;
                                    updateFullAddress();
                                });
                            } else {
                                isProgrammaticSet = false;
                            }
                        });
                    } else {
                        isProgrammaticSet = false;
                    }
                });
            } else {
                isProgrammaticSet = false;
            }
        });
    }

    function reset() {
        isProgrammaticSet = true;
        $region.val('');
        $prov.empty().append('<option value="" selected disabled>-- Select Province --</option>').prop('disabled', true);
        $mun.empty().append('<option value="" selected disabled>-- Select Municipality --</option>').prop('disabled', true);
        $brgy.empty().append('<option value="" selected disabled>-- Select Barangay --</option>').prop('disabled', true);
        if ($zip) $zip.val('').prop('disabled', true);
        if ($street) $street.val('');
        if ($fullAddress) $fullAddress.val('');
        isProgrammaticSet = false;
    }

    // Initial load
    loadRegions();

    return {
        loadRegions,
        setAddressValues,
        updateFullAddress,
        reset
    };
}
