$(function () {
    if ($.fn.DataTable.isDataTable("#med_lib_table")) { $("#med_lib_table").DataTable().clear().destroy(); }
    $("#med_lib_table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "/api/fetch_philhealth_medicines",
            type: "POST",
            headers: { 'X-CSRF-TOKEN': $("meta[name='csrf-token'").attr("content") }
        },
        columns: [
            { data: 'DRUG_CODE' },
            { data: 'DRUG_DESC' },
            { data: 'gen_desc' },
            { data: 'salt_desc' },
            { data: 'form_desc' },
            { data: 'strength_desc' },
            { data: 'unit_desc' },
        ],
        columnDefs: [
            { target: 0, width: '1%', className: 'text-nowrap' }
        ],
        order: [[1, 'asc']]
    });
});