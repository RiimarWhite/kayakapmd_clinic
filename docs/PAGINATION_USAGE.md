/**
 * PaginationComponent - Reusable Bootstrap Pagination
 * 
 * A flexible, reusable pagination component for displaying Laravel paginated results
 * with Bootstrap styling. Integrates seamlessly with AJAX requests.
 * 
 * =============================================================================
 * BASIC USAGE
 * =============================================================================
 * 
 * 1. Import the component in your JavaScript file:
 * 
 *    import { PaginationComponent } from "./../../../components/pagination.js";
 * 
 * 2. Initialize the component with a container and callback:
 * 
 *    const pagination = new PaginationComponent({
 *        container: '#pagination_container',
 *        onPageChange: function(page) {
 *            // Handle page change - fetch data for the new page
 *            fetchDataForPage(page);
 *        }
 *    });
 * 
 * 3. Render pagination when you receive data from your API:
 * 
 *    $.ajax({
 *        url: '/api/your-endpoint',
 *        success: function(response) {
 *            // response contains: data, current_page, last_page, total, from, to
 *            pagination.render(response);
 *        }
 *    });
 * 
 * =============================================================================
 * CONFIGURATION OPTIONS
 * =============================================================================
 * 
 * container (string)      - CSS selector for the pagination container element
 *                          Default: '#pagination_container'
 * 
 * onPageChange (function) - Callback function executed when user clicks a page
 *                          Receives page number as parameter
 * 
 * alignment (string)      - Bootstrap alignment class for pagination
 *                          Default: 'justify-content-center'
 *                          Options: 'justify-content-start', 'justify-content-center', 
 *                                   'justify-content-end', 'justify-content-around', 
 *                                   'justify-content-between'
 * 
 * =============================================================================
 * EXAMPLE: ENLISTMENT SEARCH WITH PAGINATION
 * =============================================================================
 * 
 * // In your page's main JS file
 * import { PaginationComponent } from "./../../../components/pagination.js";
 * 
 * $(function() {
 *     // Initialize pagination component
 *     const pagination = new PaginationComponent({
 *         container: '#pagination_container',
 *         onPageChange: function(page) {
 *             searchEnlistments(page); // Fetch page data
 *         },
 *         alignment: 'justify-content-center'
 *     });
 * 
 *     // Search button handler
 *     $('#search-btn').on('click', function() {
 *         searchEnlistments(1); // Reset to page 1
 *     });
 * 
 *     // Search function
 *     function searchEnlistments(page = 1) {
 *         const filters = {
 *             firstName: $('#firstName').val(),
 *             lastName: $('#lastName').val(),
 *             email: $('#email').val(),
 *             page: page
 *         };
 * 
 *         $.ajax({
 *             url: '/api/search-enlistments',
 *             type: 'GET',
 *             data: filters,
 *             success: function(response) {
 *                 // Render table rows
 *                 renderTableRows(response.data);
 * 
 *                 // Render pagination controls
 *                 pagination.render(response);
 *             },
 *             error: function(error) {
 *                 console.error('Search failed:', error);
 *             }
 *         });
 *     }
 * 
 *     function renderTableRows(rows) {
 *         const tbody = $('#table tbody');
 *         tbody.empty();
 * 
 *         rows.forEach(function(row) {
 *             tbody.append(`
 *                 <tr>
 *                     <td>${row.firstName}</td>
 *                     <td>${row.lastName}</td>
 *                     <td>${row.email}</td>
 *                 </tr>
 *             `);
 *         });
 *     }
 * });
 * 
 * =============================================================================
 * EXPECTED API RESPONSE FORMAT
 * =============================================================================
 * 
 * Your endpoint should return a Laravel paginated response:
 * 
 * {
 *     "data": [
 *         { "id": 1, "name": "John Doe", ... },
 *         { "id": 2, "name": "Jane Smith", ... },
 *         ...
 *     ],
 *     "current_page": 1,
 *     "last_page": 5,
 *     "per_page": 5,
 *     "total": 23,
 *     "from": 1,
 *     "to": 5,
 *     "path": "/api/your-endpoint",
 *     ...
 * }
 * 
 * To get this format in Laravel, use:
 * 
 *     $results = YourModel::paginate(5);  // 5 items per page
 *     return response()->json($results);
 * 
 * =============================================================================
 * METHOD REFERENCE
 * =============================================================================
 * 
 * render(response)
 *     - Main method to render pagination controls
 *     - Parameters: response object from Laravel paginator
 *     - Returns: void
 *     - Usage: pagination.render(apiResponse);
 * 
 * setOnPageChange(callback)
 *     - Update the page change callback at runtime
 *     - Parameters: callback function(page) {}
 *     - Returns: void
 *     - Usage: pagination.setOnPageChange(function(page) { ... });
 * 
 * setAlignment(alignmentClass)
 *     - Change pagination alignment
 *     - Parameters: Bootstrap alignment class string
 *     - Returns: void
 *     - Usage: pagination.setAlignment('justify-content-start');
 * 
 * clear()
 *     - Clear pagination controls from container
 *     - Parameters: none
 *     - Returns: void
 *     - Usage: pagination.clear();
 * 
 * =============================================================================
 * STYLING & CUSTOMIZATION
 * =============================================================================
 * 
 * The component generates Bootstrap-compatible HTML:
 * 
 *     <nav aria-label="Pagination Navigation">
 *         <ul class="pagination [alignment-class]">
 *             <li class="page-item [disabled|active]">
 *                 <a class="page-link pagination-link" data-page="N">...</a>
 *             </li>
 *         </ul>
 *     </nav>
 *     <small class="text-muted">Showing X to Y of Z records</small>
 * 
 * You can customize styling by overriding CSS classes:
 * 
 *     .pagination { ... }
 *     .page-item { ... }
 *     .page-item.active { ... }
 *     .page-item.disabled { ... }
 *     .page-link { ... }
 *     .text-muted { ... }
 * 
 * =============================================================================
 */
