'use strict';
$(document).ready(function () {
    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#approvaltranscripttable');
    $("#approvaltranscripttable tr th").each(function () {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#approvaltranscripttable thead tr> :nth-child(1)").each(function () {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });
    var oTable = tableContainer.dataTable({
        "iDisplayLength": 5,
        "sPaginationType": "bootstrap",
        "sDom": 'pl;"bottom;"p<"clear">',
        "bInfo": false,
        "aoColumnDefs": [
            {"bVisible": false, "aTargets": [6]},
        ],
        "oLanguage": {
            "oPaginate": {
                "sFirst": "<<",
                "sLast": "  >>  ",
                "sNext": "  >",
                "bStateSave": true,
                "sPrevious": " <  "
            },
            "sLengthMenu": 'View <select>' +
                    '<option value="10">5</option>' +
                    '<option value="20">10</option>' +
                    '<option value="30">15</option>' +
                    '<option value="40">20</option>' +
                    '<option value="50">50</option>' +
                    '<option value="-1">All</option>' +
                    '</select> '
        },
        "bJQueryUI": true,
        // Setup for responsive datatables helper.
        bAutoWidth: false,
        fnPreDrawCallback: function () {
            // Initialize the responsive datatables helper once.
            if (!responsiveHelper) {
                responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
            }
        },
        fnRowCallback: function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            responsiveHelper.createExpandIcon(nRow);
        },
    });
    oTable.coFilter({
        sPlaceHolder: ".filterarea",
        aoColumns: [2, 3, 4, 6],
        customdata: {6: {0: "Pending", 1: "Approved", 2: "Rejected"}},
        columntitles: {2: "Organization", 3: "Program", 4: "Course Offering", 6: "status"},
        filtertype: {2: "select", 3: "select", 4: "select", 6: "select"}

    });
});