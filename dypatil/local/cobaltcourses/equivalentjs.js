
$(document).ready(function () {
    /* Initialise the DataTable */
    var oTable = $('#equivalenttable').dataTable({
        "oLanguage": {
            "sSearch": "Search all columns:"
        },
        "iDisplayLength": 5,
        "aLengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
        "sPaginationType": "full_numbers",
        "sDom": '"&lt;"top"i&gt;rt"bottom"p;',
        "bInfo": false,
        "oLanguage": {
            "oPaginate": {
                "sFirst": " First",
                "sLast": " Last",
                "sNext": " Next",
                "sPrevious": " Previous"
            }
        }
    });

    oTable.coFilter({
        sPlaceHolder: ".filterarea",
        aoColumns: [0, 1],
        columntitles: {0: "Course", 1: "Course Library"},
        filtertype: {0: "select", 1: "select"}

    });
});