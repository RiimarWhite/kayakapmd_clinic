$(function () {
    loadStocksLegderTable();

    function loadStocksLegderTable() {
        if ($.fn.DataTable.isDataTable("#stocks_ledger_table")) {
            $("#stocks_ledger_table").DataTable().clear().destroy();
        }

        $("#stocks_ledger_table").DataTable({
            processing: true,
            serverSide: true,
            ajax: {
                url: "/api/fetch_stocks_ledger",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") }
            },
            columns: [
                {
                    data: 'updated',
                    render: function (data) {
                        return data != null ? new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(data)) : "N/A";
                    }
                },
                { data: 'patient_name' },
                { data: 'item_dscr' },
                { data: 'dispensed_status' },
                {
                    data: 'dispensed',
                    render: function (data) {
                        return data != null ? new Intl.DateTimeFormat('en-US', { year: 'numeric', month: 'long', day: 'numeric' }).format(new Date(data)) : "N/A";
                    }
                }
            ],
            columnDefs: [
                { targets: [0, 4], width: '1%', className: 'text-nowrap text-center' },
                { targets: [2, 3], width: '1%', className: 'text-nowrap' },
                { target: '_all', className: 'align-middle' }
            ],
            language: {
                emptyTable: 'No records yet.'
            },
        });
    }
});
