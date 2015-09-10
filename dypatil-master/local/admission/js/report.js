'use strict';
$(document).ready(function() {
    /* Initialise the DataTable */
   $(".DTTT_container.ui-buttonset.ui-buttonset-multi").hide();
    var responsiveHelper = undefined;
var breakpointDefinition = {
    tablet: 1024,
    phone : 480
};
var tableContainer = $('#report');
$("#report tr th").each(function(){
 $(this)
   .attr("data-hide","phone,tablet")
});
  $("#report thead tr> :nth-child(1)").each(function(){
   $(this)
   .attr("data-class","expand")
   .removeAttr("data-hide")
   });
   var oTable = tableContainer.dataTable( {
                           "iDisplayLength": 5,
                           "sPaginationType": "bootstrap",
                           "aoColumnDefs": [ 
                                                       { "bSearchable":false, "aTargets": [ -1 ]},
                                                       { "bSortable": false, "aTargets": [ -1 ] },
                                                        ],
                           "sDom": 'pl;"bottom;"p<"clear">',
                           "bInfo": false,
                           "oLanguage": {
                           "oPaginate": {
                           "sFirst": "First",
                           "sLast":"  Last  ",
                           "sNext": "  Next",
			   "bStateSave": true,
                           "sPrevious":" Previous  "
                           },
                          "sLengthMenu": 'View <select>'+
			             '<option value="10">5</option>'+
			             '<option value="20">10</option>'+
			             '<option value="30">15</option>'+
			             '<option value="40">20</option>'+
			             '<option value="50">50</option>'+
			             '<option value="-1">All</option>'+
			             '</select> '
                            },
                          "bJQueryUI": true ,
                          
    // Setup for responsive datatables helper.
    bAutoWidth     : false,

    fnPreDrawCallback: function () {
        // Initialize the responsive datatables helper once.
        if (!responsiveHelper) {
            responsiveHelper = new ResponsiveDatatablesHelper(tableContainer, breakpointDefinition);
        }
    },
    fnRowCallback  : function (nRow, aData, iDisplayIndex, iDisplayIndexFull) {
        responsiveHelper.createExpandIcon(nRow);
    },
    //fnDrawCallback : function (oSettings) {
    //   responsiveHelper.respond();
    //}
    });
        oTable.coFilter({
                   sPlaceHolder: ".filterarea",
    	           aoColumns: [ 2,3,4,5,6,7],
                   columntitles:{2: "Organization", 3 : "Program", 4: "Registration Type", 5 : "Program Level",6: "Student Type",7: "Status"},
	           filtertype: {2: "select",3: "select", 4 : "select",5 : "select",6 : "select",7: "select"},
	          dateformat:"D ,M d,yy"
  });
    } );