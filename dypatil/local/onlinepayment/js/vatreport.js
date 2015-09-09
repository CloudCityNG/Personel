'use strict';
$(document).ready(function() {
    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#vatreporttable');
    $("#vatreporttable tr th").each(function() {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#vatreporttable thead tr> :nth-child(1)").each(function() {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });
    var oTable = tableContainer.dataTable({
        "iDisplayLength": 20,
	"aaSorting": [],
	"bSortable": false,
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
                    '<option value="20">20</option>' +
                    '<option value="30">30</option>' +
		    '<option value="50">50</option>' +
                    '<option value="100">100</option>' +
                    '<option value="-1">All</option>' +
                    '</select> Entries '
        },
	aoColumnDefs: [
            { "aTargets": [ 0 ], "bSortable": false },
            { "aTargets": [ 1 ], "bSortable": false },
            { "aTargets": [ 2 ], "bSortable": false },
	    { "aTargets": [ 3 ], "bSortable": false },
	    { "aTargets": [ 4 ], "bSortable": false },
            { "aTargets": [ 5 ], "bSortable": false },
	    { "aTargets": [ 6 ], "bSortable": false }
        ],
        //"bJQueryUI": true,
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
});
