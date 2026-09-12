$(function() {
    loadInventory();

    function loadInventory() {
        if ($.fn.DataTable.isDataTable("#stocks_listing_table")) {
            $("#stocks_listing_table").DataTable().clear().destroy();
        }

        $("#stocks_listing_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_inventory",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: { filter: $("#filter-category").val() }
            },
            columns: [
                { data: 'prod_itemdscr' },
                { data: 'qty' }
            ],
            columnDefs: [
                { targets: [0, 1], className: "align-middle" },
                { target: 1, width: '10%', className: "text-center" }
            ],
            language: {
                emptyTable: "No records yet.",
                zeroRecords: "No items marked as inventory found."
            },
            order: [[0, 'asc']]
        });
    }

    $("#filter-category").on("change", function () {
        loadInventory();
    });
});
