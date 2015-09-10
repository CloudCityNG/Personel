'use strict';
$(document).ready(function () {

    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#coursereg_mentor');
    $("#advisor tr th").each(function () {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#advisor thead tr> :nth-child(1)").each(function () {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });

    var oTable = tableContainer.dataTable({
        "iDisplayLength": 5,
        "sPaginationType": "bootstrap",
        "aoColumnDefs": [{"bVisible": false, "aTargets": [5]},
        ],
        "sDom": 'pl;"bottom;"p<"clear">',
        "bInfo": false,
        "oLanguage": {
            "oPaginate": {
                "sFirst": "<<",
                "sLast": "  >>  ",
                "sNext": "  >",
                "bStateSave": true,
                "sPrevious": " <  "
            },
            "sLengthMenu": 'View <select>' +
                    '<option value="5">5</option>' +
                    '<option value="10">10</option>' +
                    '<option value="15">15</option>' +
                    '<option value="20">20</option>' +
                    '<option value="50">50</option>' +
                    '<option value="-1">All</option>' +
                    '</select> '
        },
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
        aoColumns: [1, 2, 5, 0],
        columntitles: {0: "Student", 1: "Class", 2: "Course", 5: "Status"},
        customdata: {5: {0: "Approved", 1: "Rejected", 2: "Pending"}},
        filtertype: {0: "text", 1: "select", 2: "select", 5: "select"}
      
    });
});