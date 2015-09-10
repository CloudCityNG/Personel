/*
 * Using at assigning courses to the Curriculum plan
 * To enable 'Assign' button
 */
/* Please dont modify */
$(document).ready(function () {
    $('#program').click(function () {
        if ($(this).is(':checked')) {
            $('#d').hide();
            $('#p').slideDown();
        }
    });
    $('#department').click(function () {
        if ($(this).is(':checked')) {
            $('#p').hide();
            $('#d').slideDown();
        }
    });

//var checkAll = function() {
//    //var checkBoxes = $("input#checkid");
//    //    checkBoxes.attr("checked", !checkBoxes.attr("checked"));
//    if ($('.checkid').is(':checked')) {
//	$('.checkid').removeAttr('checked');
//    }else if(!$('.checkid').is(':checked')){
//	$('.checkid').attr('checked', 'checked');
//    }
//};
////checkAll();
//$( ".checkall" ).on( "click", checkAll );


    var countChecked = function () {
        var n = $("input:checked").length;
        if (n != 0) {
            $('input[type="submit"]').removeAttr('disabled');
        } else {
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
    };
    countChecked();
    $("input[type=checkbox]").on("click", countChecked);
});


//
//(function($) {
///*
// * Function: fnGetColumnData
// * Purpose:  Return an array of table values from a particular column.
// * Returns:  array string: 1d data array
// * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
// *           int:iColumn - the id of the column to extract the data from
// *           bool:bUnique - optional - if set to false duplicated values are not filtered out
// *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
// *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
// * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
// */
//$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
//    // check that we have a column id
//    if ( typeof iColumn == "undefined" ) return new Array();
//     
//    // by default we only want unique data
//    if ( typeof bUnique == "undefined" ) bUnique = true;
//     
//    // by default we do want to only look at filtered data
//    if ( typeof bFiltered == "undefined" ) bFiltered = true;
//     
//    // by default we do not want to include empty values
//    if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
//     
//    // list of rows which we're going to loop through
//    var aiRows;
//     
//    // use only filtered rows
//    if (bFiltered == true) aiRows = oSettings.aiDisplay;
//    // use all rows
//    else aiRows = oSettings.aiDisplayMaster; // all row numbers
// 
//    // set up data array   
//    var asResultData = new Array();
//     
//    for (var i=0,c=aiRows.length; i<c; i++) {
//        iRow = aiRows[i];
//        var aData = this.fnGetData(iRow);
//	
//        var sValue = aData[iColumn];
//         
//        // ignore empty values?
//        if (bIgnoreEmpty == true && sValue.length == 0) continue;
// 
//        // ignore unique values?
//        else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
//         
//        // else push the value onto the result data array
//        else asResultData.push(sValue);
//    }
//     
//    return asResultData;
//}}(jQuery));
// 
// 
//function fnCreateSelect( aData, heading )
//{
//var i;
//    var r='<select><option value="">Select '+heading+( $("th").val());+'</option>', i, iLen=aData.length;
//    for ( i=0 ; i<iLen ; i++ )
//    {
//        r += '<option value="'+aData[i]+'">'+aData[i]+'</option>';
//    }
//    return r+'</select>';
//}
// 
// 
//$(document).ready(function() {
//    /* Initialise the DataTable */
//    var oTable = $('#cooktable').dataTable( {
//"oLanguage": {
//"sSearch": "Search all columns:"
//},
//"iDisplayLength": 5,
//	"aLengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
// "sPaginationType": "full_numbers",
// "aoColumnDefs": [ 
// { "bSearchable":true, "aTargets": [ 3 ] }
//],
//"aoColumnDefs": [ 
// { "bSortable": false, "aTargets": [ 3 ] }
//],
//"sDom": '&l;"bottomp;"',
//"bInfo": false,
//"oLanguage": {
//"oPaginate": {
//"sFirst": "First",
//"sLast":"Last",
//"sNext": ">>",
//"sPrevious":"<<"
//  }
//    }
//    } );
//     
//    /* Add a select menu for each TH element in the table footer */Are you using curriculum/js/filter.js file
//    $("tfoot th").each( function ( i ) {
//        if(i==0 || i==1){
//        this.innerHTML = fnCreateSelect( oTable.fnGetColumnData(i), $(this).text() );
//        $('select', this).change( function () {
//            oTable.fnFilter( $(this).val(), i );
//        } );
//}
//    } );
//} );
