'use strict';
$(document).ready(function() {
    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#paymentstatustable');
    $("#paymentstatustable tr th").each(function() {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#paymentstatustable thead tr> :nth-child(1)").each(function() {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });
    var oTable = tableContainer.dataTable({
        "iDisplayLength": 10,
        "sPaginationType": "bootstrap",
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
                    '<option value="10">10</option>' +
                    '<option value="20">20</option>' +
                    '<option value="50">50</option>' +
                    '<option value="-1">All</option>' +
                    '</select> Entries '
        },
        "bJQueryUI": true,
        // Setup for responsive datatables helper.
        bAutoWidth: false,
        fnPreDrawCallback: function() {
            // Initialize the responsive datatables helper once.
            if (!responsiveHelper) {
                responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
            }
        },
        fnRowCallback: function(nRow, aData, iDisplayIndex, iDisplayIndexFull) {
            responsiveHelper.createExpandIcon(nRow);
        },
    });
  oTable.coFilter({
                   sPlaceHolder: ".filterarea",
    	           aoColumns: [ 1 ],
                   columntitles:{1: "Student Name"},
	           filtertype: {1: "text"}
  });
});
