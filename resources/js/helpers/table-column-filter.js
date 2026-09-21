/**
 * Detailed Comment: Reusable DataTables Column Filter & Ordering Dropdown Helper.
 * Empowers any DataTables table header (<th>) with an interactive filter/sort dropdown modal menu:
 * - Directional Ordering: Ascending, Descending, and Reset sorting triggers.
 * - Search Filtering: Free-text search input or distinct value checkbox picklist.
 * - State Highlighting: Toggles active badges and filter icons to indicate applied constraints.
 * - Non-interfering: Prevents dropdown click propagation from triggering parent DataTables sort handlers.
 */

export function initTableColumnFilters(dataTable, tableSelector) {
    const $table = $(tableSelector);

    // Detailed Comment: Clean up previous event bindings on $table and dataTable to prevent duplicate listeners on table reload
    $table.off('.colFilter');
    dataTable.off('draw.colFilter order.colFilter search.colFilter');

    // Detailed Comment: Stop click propagation on .column-filter-container so clicks inside the filter menu
    // or toggle button NEVER bubble up to the parent <th> element. This prevents DataTables from triggering
    // unwanted column header re-sorting when interacting with filter controls.
    $table.on('click.colFilter', '.column-filter-container', function (e) {
        e.stopPropagation();
    });

    // Detailed Comment: Toggle Bootstrap dropdown explicitly via JavaScript API using static positioning.
    // By omitting data-bs-toggle="dropdown" from the button markup, we prevent Bootstrap's global data-api
    // from triggering a concurrent toggle (which causes an immediate open-then-close double-toggle bug).
    $table.on('click.colFilter', '.column-filter-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        const currentBtn = this;
        // Detailed Comment: Close any open sibling column filter dropdowns before toggling this one
        // to prevent multiple dropdown menus from overlapping or stacking across table columns
        $table.find('.column-filter-btn').each(function () {
            if (this !== currentBtn && window.bootstrap && window.bootstrap.Dropdown) {
                const inst = window.bootstrap.Dropdown.getInstance(this);
                if (inst) inst.hide();
            }
        });

        if (window.bootstrap && window.bootstrap.Dropdown) {
            const dd = window.bootstrap.Dropdown.getOrCreateInstance(this, {
                display: 'static',
                autoClose: 'outside'
            });
            dd.toggle();
        }
    });

    // Detailed Comment: Prevent clicks inside the dropdown menu from auto-closing or bubbling to header
    $table.on('click.colFilter', '.column-filter-menu', function (e) {
        e.stopPropagation();
    });

    // Detailed Comment: Global click outside listener to ensure filter menus cleanly dismiss
    $(document).off('click.colFilterOutside').on('click.colFilterOutside', function (e) {
        if (!$(e.target).closest('.column-filter-container').length) {
            $table.find('.column-filter-btn').each(function () {
                if (window.bootstrap && window.bootstrap.Dropdown) {
                    const inst = window.bootstrap.Dropdown.getInstance(this);
                    if (inst) inst.hide();
                }
            });
        }
    });

    // 1. Sort Ascending
    $table.on('click.colFilter', '.btn-sort-asc', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const $menu = $(this).closest('.column-filter-menu');
        const colIdx = parseInt($menu.data('col'), 10);
        const $container = $menu.closest('.column-filter-container');

        dataTable.order([colIdx, 'asc']).draw();

        // Close dropdown
        const btnEl = $container.find('.column-filter-btn')[0];
        if (btnEl && window.bootstrap && window.bootstrap.Dropdown) {
            const instance = window.bootstrap.Dropdown.getInstance(btnEl);
            if (instance) instance.hide();
        }
    });

    // 2. Sort Descending
    $table.on('click.colFilter', '.btn-sort-desc', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const $menu = $(this).closest('.column-filter-menu');
        const colIdx = parseInt($menu.data('col'), 10);
        const $container = $menu.closest('.column-filter-container');

        dataTable.order([colIdx, 'desc']).draw();

        // Close dropdown
        const btnEl = $container.find('.column-filter-btn')[0];
        if (btnEl && window.bootstrap && window.bootstrap.Dropdown) {
            const instance = window.bootstrap.Dropdown.getInstance(btnEl);
            if (instance) instance.hide();
        }
    });

    // 3. Clear Sort
    $table.on('click.colFilter', '.btn-sort-clear', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const $menu = $(this).closest('.column-filter-menu');
        const $container = $menu.closest('.column-filter-container');

        dataTable.order([]).draw();

        // Close dropdown
        const btnEl = $container.find('.column-filter-btn')[0];
        if (btnEl && window.bootstrap && window.bootstrap.Dropdown) {
            const instance = window.bootstrap.Dropdown.getInstance(btnEl);
            if (instance) instance.hide();
        }
    });

    // 4. Filter Apply
    $table.on('click.colFilter', '.btn-filter-apply', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const $menu = $(this).closest('.column-filter-menu');
        const colIdx = parseInt($menu.data('col'), 10);
        const $container = $menu.closest('.column-filter-container');

        const textVal = ($menu.find('.col-filter-input').val() || '').trim();
        const checkedVals = $menu.find('.col-filter-check:checked').map(function () {
            return $(this).val();
        }).get();

        if (checkedVals.length > 0) {
            const regexStr = '^(' + checkedVals.map(function (v) {
                return $.fn.dataTable.util.escapeRegex(v);
            }).join('|') + ')$';

            dataTable.column(colIdx).search(regexStr, true, false).draw();
        } else if (textVal) {
            dataTable.column(colIdx).search(textVal).draw();
        } else {
            dataTable.column(colIdx).search('').draw();
        }

        // Close dropdown
        const btnEl = $container.find('.column-filter-btn')[0];
        if (btnEl && window.bootstrap && window.bootstrap.Dropdown) {
            const instance = window.bootstrap.Dropdown.getInstance(btnEl);
            if (instance) instance.hide();
        }
    });

    // Detailed Comment: Allow pressing Enter inside text filter input to trigger filter apply
    $table.on('keydown.colFilter', '.col-filter-input', function (e) {
        if (e.key === 'Enter' || e.keyCode === 13) {
            e.preventDefault();
            e.stopPropagation();
            $(this).closest('.column-filter-menu').find('.btn-filter-apply').trigger('click');
        }
    });

    // 5. Filter Clear
    $table.on('click.colFilter', '.btn-filter-clear', function (e) {
        e.stopPropagation();
        e.preventDefault();
        const $menu = $(this).closest('.column-filter-menu');
        const colIdx = parseInt($menu.data('col'), 10);
        const $container = $menu.closest('.column-filter-container');

        $menu.find('.col-filter-input').val('');
        $menu.find('.col-filter-check').prop('checked', false);

        dataTable.column(colIdx).search('').draw();

        // Close dropdown
        const btnEl = $container.find('.column-filter-btn')[0];
        if (btnEl && window.bootstrap && window.bootstrap.Dropdown) {
            const instance = window.bootstrap.Dropdown.getInstance(btnEl);
            if (instance) instance.hide();
        }
    });

    /**
     * Detailed Comment: Synchronizes filter values directly from DataTables internal state.
     * Handles:
     * - Removal of directional sort badge (.sort-badge) after the filter icon, relying on native DataTables sorting indicators.
     * - Displaying the actual active filter value (text or picklist selections) on the column header badge.
     */
    function syncFilterAndSortBadges() {
        $table.find('.column-filter-container').each(function () {
            const $container = $(this);
            const $btn = $container.find('.column-filter-btn');
            const $menu = $container.find('.column-filter-menu');
            const colIdx = parseInt($menu.data('col'), 10);
            if (isNaN(colIdx)) return;

            // Detailed Comment: Remove directional sort badge after the filter icon as requested.
            // Native DataTables header sorting arrows and modal sort buttons already provide clear directional cues.
            $btn.find('.sort-badge').remove();

            // --- 2. Sync Search / Picklist Filter Values on Column Header ---
            $btn.find('.filter-badge').remove();
            let searchVal = '';
            try {
                searchVal = dataTable.column(colIdx).search() || '';
            } catch (err) {
                searchVal = '';
            }

            if (searchVal) {
                const isPicklist = searchVal.startsWith('^(') && searchVal.endsWith(')$');
                let displayLabel = '';

                if (isPicklist) {
                    const inner = searchVal.substring(2, searchVal.length - 2);
                    const items = inner.split('|').map(v => v.replace(/\\([.*+?^${}()|[\]\/\\])/g, '$1'));

                    if (items.length === 1) {
                        displayLabel = items[0];
                    } else if (items.length === 2) {
                        displayLabel = items.join(', ');
                    } else {
                        displayLabel = `${items[0]}, ${items[1]} (+${items.length - 2})`;
                    }

                    $menu.find('.col-filter-check').each(function () {
                        const chkVal = $(this).val();
                        $(this).prop('checked', items.includes(chkVal));
                    });
                } else {
                    displayLabel = searchVal;

                    const $input = $menu.find('.col-filter-input');
                    if ($input.length && !$input.is(':focus') && $input.val() !== searchVal) {
                        $input.val(searchVal);
                    }
                }

                // Detailed Comment: Highlight filter button and display the active filter text or picklist value
                $btn.addClass('btn-primary text-white').removeClass('btn-light btn-outline-secondary');
                $btn.find('.filter-icon').removeClass('text-secondary').addClass('text-white');
                $btn.append(`<span class="badge bg-danger ms-1 filter-badge text-truncate" style="max-width: 90px; vertical-align: middle;" title="Filtered by: ${displayLabel}">${displayLabel}</span>`);
            } else {
                $btn.removeClass('btn-primary btn-info text-white').addClass('btn-light');
                $btn.find('.filter-icon').removeClass('text-white').addClass('text-secondary');
            }
        });
    }

    // Detailed Comment: Listen for DataTables draw and order events to keep badge UI synchronized with table state
    dataTable.on('draw.colFilter order.colFilter search.colFilter', syncFilterAndSortBadges);

    // Initial sync for default table ordering or filters
    syncFilterAndSortBadges();
}

/**
 * Detailed Comment: Generates HTML markup for a column filter and ordering dropdown header.
 * Attaches window boundary, dynamic display, and fixed Popper strategy directly to trigger button,
 * and dynamically sets alignment (dropdown-menu-end for rightmost columns, start for leftmost/center).
 * @param {string} title Column header display title
 * @param {number} colIdx DataTables column index (0-based)
 * @param {object} options Optional settings: { type: 'text'|'picklist', picklist: ['A', 'B'], alignEnd: boolean }
 */
export function renderColumnFilterHeader(title, colIdx, options = {}) {
    let picklistHtml = '';
    if (options.picklist && options.picklist.length) {
        picklistHtml = `
            <div class="mb-2 p-1 border rounded bg-light" style="max-height: 120px; overflow-y: auto;">
                ${options.picklist.map(opt => `
                    <div class="form-check form-check-sm mb-1 text-start">
                        <input class="form-check-input col-filter-check" type="checkbox" value="${opt}" id="chk_${colIdx}_${opt.replace(/\s+/g, '_')}">
                        <label class="form-check-label small" for="chk_${colIdx}_${opt.replace(/\s+/g, '_')}">${opt}</label>
                    </div>
                `).join('')}
            </div>
        `;
    }

    const textInputHtml = !options.picklist || options.picklist.length === 0 ? `
        <div class="mb-2">
            <input type="text" class="form-control form-control-sm col-filter-input" placeholder="Search ${title}...">
        </div>
    ` : '';

    // Detailed Comment: Align dropdown to end on right-side columns (colIdx >= 6) to avoid right-edge overflow,
    // and align to start on left/center columns to prevent negative horizontal coordinate clipping.
    const isEnd = (options.alignEnd !== undefined) ? options.alignEnd : (colIdx >= 6);
    const menuAlignClass = isEnd ? 'dropdown-menu-end' : '';

    return `
        <div class="d-flex align-items-center justify-content-between gap-1 text-nowrap">
            <span>${title}</span>
            <div class="dropdown d-inline-block column-filter-container">
                <button class="btn btn-sm btn-light border py-0 px-1 column-filter-btn d-inline-flex align-items-center" type="button"
                    data-bs-display="static" data-bs-auto-close="outside" aria-expanded="false" title="Sort & Filter ${title}">
                    <i class="fa-solid fa-filter small text-secondary filter-icon"></i>
                </button>
                <div class="dropdown-menu ${menuAlignClass} shadow p-3 column-filter-menu" data-col="${colIdx}"
                    style="min-width: 240px; z-index: 1070;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="dropdown-header p-0 text-uppercase fw-bold text-muted small m-0"><i class="fa-solid fa-arrow-down-short-wide me-1"></i> Sort</h6>
                        <button type="button" class="btn btn-xs btn-link text-decoration-none text-muted p-0 btn-sort-clear" title="Clear sort">Clear</button>
                    </div>
                    <div class="btn-group btn-group-sm w-100 mb-3" role="group">
                        <button type="button" class="btn btn-outline-primary btn-sort-asc" title="Sort Ascending">
                            <i class="fa-solid fa-arrow-up-a-z me-1"></i> Asc
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-sort-desc" title="Sort Descending">
                            <i class="fa-solid fa-arrow-down-z-a me-1"></i> Desc
                        </button>
                    </div>
                    <hr class="dropdown-divider my-2">
                    <h6 class="dropdown-header p-0 text-uppercase fw-bold text-muted small mb-2"><i class="fa-solid fa-filter me-1"></i> Filter</h6>
                    ${textInputHtml}
                    ${picklistHtml}
                    <div class="d-flex justify-content-between gap-2 mt-2">
                        <button type="button" class="btn btn-sm btn-secondary w-50 btn-filter-clear">Clear</button>
                        <button type="button" class="btn btn-sm btn-primary w-50 btn-filter-apply">Apply</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}
