$(function () {
    if ($.fn.DataTable.isDataTable("#diag_lib_table")) { $("#diag_lib_table").DataTable().clear().destroy(); }
    $("#diag_lib_table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/api/fetch_philhealth_diagnostics",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token'").attr("content") }
        },
        columns: [
            { data: 'diagnostic_id' },
            { data: 'diagnostic_desc' },
        ],
        columnDefs: [
            { target: 0, width: '1%', className: 'text-nowrap' }
        ],
        order: [[0, 'asc']]
    });
});