$(function () {
    loadTransactions();

    function loadTransactions() {
        $("#transactions_table").DataTable().destroy().clear();
        $("#transactions_table").DataTable({
            processing: true, serverSide: true,
            ajax: {
                url: "/api/fetch_transactions",
                type: "POST",
                headers: { "X-CSRF-TOKEN": $("meta[name='csrf-token']").attr("content") },
                data: function (d) {
                    d.filter_start = $("#report_start").val();
                    d.filter_end = $("#report_end").val();
                }
            },
            columns: [
                { data: 'transactionrefno' },
                { data: 'created_at', render: (data) => new Date(data).toDateString() },
                { data: 'net_total' }, { data: 'cash' }, { data: 'cta' }, { data: 'hmo' },
                { data: 'created_at', render: (data) => new Date(data).toLocaleDateString() }
            ],
            language: { emptyTable: "No records yet." }
        });
    }

    $("#filter_reports_btn").on("click", loadTransactions);

    $("#report_start, #report_end").on("change", function () {
        if ($("#report_start").val() && $("#report_end").val()) loadTransactions();
    });
});
