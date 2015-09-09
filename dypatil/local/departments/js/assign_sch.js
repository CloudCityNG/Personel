
$(document).ready(function () {
    /* Initialise the DataTable */
    var oTable = $('#depttable').dataTable({
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
                "sFirst": "<<",
                "sLast": "  >>  ",
                "sNext": "  >",
                "bStateSave": true,
                "sPrevious": " <  "
            },
        }
    });


    oTable.coFilter({
        sPlaceHolder: ".filterarea",
        aoColumns: [0],
        columntitles: {0: "Course Library"},
        filtertype: {0: "select"}

    });




});