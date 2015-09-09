'use strict';
$(document).ready(function () {
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#stu1');
    $("#stu1 tr th").each(function () {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#stu1 thead tr> :nth-child(1)").each(function () {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });

    var oTable = tableContainer.dataTable({
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
                "sFirst": "<<",
                "sLast": "  >>  ",
                "sNext": "  >",
                "bStateSave": true,
                "sPrevious": " <  "
            },
            "sLengthMenu": 'View <select>' +
                    '<option value="10">10</option>' +
                    '<option value="20">20</option>' +
                    '<option value="30">35</option>' +
                    '<option value="40">40</option>' +
                    '<option value="50">50</option>' +
                    '<option value="-1">All</option>' +
                    '</select> '
        },
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
});