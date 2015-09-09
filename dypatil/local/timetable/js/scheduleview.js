'use strict';
$(document).ready(function() {
    /* Initialise the DataTable */
    $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
    var breakpointDefinition = {
        tablet: 1024,
        phone: 480
    };
    var tableContainer = $('#timetable_sc');
    $("#timetable_sc tr th").each(function() {
        $(this)
                .attr("data-hide", "phone,tablet")
    });
    $("#timetable_sc thead tr> :nth-child(1)").each(function() {
        $(this)
                .attr("data-class", "expand")
                .removeAttr("data-hide")
    });
    var oTable = tableContainer.dataTable({
        "iDisplayLength": 5,
        "sPaginationType": "bootstrap",
        "sDom": 'pl;"bottom;"p<"clear">',
        "bInfo": false,
        "aaSorting": [],
        "searching": false,
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
                    '</select>'
        },
         "aoColumnDefs": [{"bVisible": false, "aTargets": [1,2,3]},
        ],
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
        aoColumns: [1, 2,3],
        columntitles: {1: "Class Name", 2: "Course Offering" , 3: "Class Type"},
        filtertype: { 1: "select", 2: "select", 3:"select"}


    });
});


        //
        //echo html_writer::script("
        //        $(document).ready(function() {
        //        $('#timetable_sc').dataTable({
        //        'iDisplayLength': 5,
        //        'fnDrawCallback': function(oSettings) {
        //        if(oSettings._iDisplayLength > oSettings.fnRecordsDisplay()) {
        //        $('#timetable_sc'+'_paginate').hide();
        //        $('#timetable_sc'+'_length').hide();
        //        }
        //        },
        //        'aLengthMenu': [ [5, 10, 25, 50, -1], [5, 10, 25, 50, 'All']],
        //        'searching': false,
        //        'aaSorting': [],
        //} );
        //        } );
        //        ");