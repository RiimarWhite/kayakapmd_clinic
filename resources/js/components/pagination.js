/**
 * PaginationComponent - Reusable Bootstrap Pagination Component
 *
 * Usage Example:
 * const pagination = new PaginationComponent({
 *     container: '#pagination_container',
 *     onPageChange: function(page) {
 *         // Handle page change
 *     }
 * });
 *
 * pagination.render(paginationData);
 */

export class PaginationComponent {
    constructor(options = {}) {
        this.container = options.container || '#pagination_container';
        this.onPageChange = options.onPageChange || null;
        this.alignment = options.alignment || 'justify-content-center';
        this.size = options.size || 'sm'; // sm, md, lg
    }

    /**
     * Render pagination controls based on Laravel paginator response
     * @param {Object} response - Laravel paginator response with data, current_page, last_page, total, from, to
     */
    render(response) {
        const $container = $(this.container);
        $container.empty();

        // Exit early if only one page
        if (response.last_page <= 1) {
            return;
        }

        const currentPage = response.current_page;
        const lastPage = response.last_page;
        const total = response.total;
        const from = response.from;
        const to = response.to;

        let html = this._buildPaginationHtml(currentPage, lastPage);
        html += this._buildRecordCountHtml(from, to, total);

        $container.html(html);
        this._attachEventListeners(lastPage);
    }

    /**
     * Build pagination HTML structure with windowed page numbers
     * e.g. Previous 1 2 3 ... 8 9 [10] 11 12 ... 20 21 22 Next
     * @private
     */
    _buildPaginationHtml(currentPage, lastPage) {
        let html = `
            <nav aria-label="Pagination Navigation">
                <ul class="pagination ${this.alignment}">
        `;

        // Previous button
        html += `
            <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
                <a class="page-link pagination-link" href="#" data-page="${currentPage - 1}">Previous</a>
            </li>
        `;

        const pages = this._getPageNumbers(currentPage, lastPage);

        pages.forEach((page) => {
            if (page === '...') {
                html += `
                <li class="page-item disabled">
                    <span class="page-link">…</span>
                </li>`;
            } else {
                html += `
                <li class="page-item ${page === currentPage ? 'active' : ''}">
                    <a class="page-link pagination-link" href="#" data-page="${page}">${page}</a>
                </li>`;
            }
        });

        // Next button
        html += `
            <li class="page-item ${currentPage === lastPage ? 'disabled' : ''}">
                <a class="page-link pagination-link" href="#" data-page="${currentPage + 1}">Next</a>
            </li>
                </ul>
            </nav>
        `;

        return html;
    }

    /**
     * Calculate which page numbers to show, inserting '...' for gaps.
     * Always shows first 2, last 2, and a window of 2 around the current page.
     * @private
     */
    _getPageNumbers(currentPage, lastPage) {
        const delta = 2; // pages on each side of current
        const range = [];
        const pages = [];

        // Build the set of page numbers to always show
        const rangeSet = new Set();

        // First two pages
        for (let i = 1; i <= Math.min(2, lastPage); i++) {
            rangeSet.add(i);
        }

        // Window around current page
        for (let i = Math.max(1, currentPage - delta); i <= Math.min(lastPage, currentPage + delta); i++) {
            rangeSet.add(i);
        }

        // Last two pages
        for (let i = Math.max(1, lastPage - 1); i <= lastPage; i++) {
            rangeSet.add(i);
        }

        const sorted = Array.from(rangeSet).sort((a, b) => a - b);

        let prev = null;
        for (const page of sorted) {
            if (prev !== null && page - prev > 1) {
                pages.push('...');
            }
            pages.push(page);
            prev = page;
        }

        return pages;
    }

    /**
     * Build record count display HTML
     * @private
     */
    _buildRecordCountHtml(from, to, total) {
        return `
            <div class="text-center mb-3">
                <small class="text-muted">Showing ${from} to ${to} of ${total} records</small>
            </div>
        `;
    }

    /**
     * Attach event listeners to pagination links
     * @private
     */
    _attachEventListeners(lastPage) {
        const self = this;

        $(".pagination-link").on("click", function (e) {
            e.preventDefault();

            const page = $(this).data("page");

            // Validate page number
            if (page > 0 && page <= lastPage) {
                if (self.onPageChange && typeof self.onPageChange === "function") {
                    self.onPageChange(page);
                }
            }
        });
    }

    /**
     * Set custom callback for page change
     */
    setOnPageChange(callback) {
        this.onPageChange = callback;
    }

    /**
     * Set pagination alignment
     */
    setAlignment(alignmentClass) {
        this.alignment = alignmentClass;
    }

    /**
     * Clear pagination controls
     */
    clear() {
        $(this.container).empty();
    }
}
