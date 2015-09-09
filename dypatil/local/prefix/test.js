'use strict';

$(document).ready(function () {
    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#cooktable');
    $("#cooktable tr th").each(function () {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#cooktable thead tr> :nth-child(1)").each(function () {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });

    var iTable = tableContainer.dataTable({
        "iDisplayLength": 5,
        "sPaginationType": "bootstrap",
        "aoColumnDefs": [
            {"bSearchable": false, "aTargets": [-1]},
            {"bSortable": false, "aTargets": [-1]},
        ],
        "sDom": 'pl;"bottom;"p<"clear">',
        "bInfo": false,
        "oLanguage": {
            "oPaginate": {
                "sFirst": "First",
                "sLast": "  Last  ",
                "sNext": "  Next",
                "bStateSave": true,
                "sPrevious": " Previous  "
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



    iTable.coFilter({
        sPlaceHolder: ".filterarea",
        aoColumns: [0, 1, 2, 3, 4, 5],
        columntitles: {0: "Entity", 1: "Organization", 2: "Program", 3: "Sequence", 4: "Prefix", 5: "Suffix"},
        filtertype: {0: "select", 1: "select", 2: "select", 3: "select", 4: "select", 5: "select"}

    });


});