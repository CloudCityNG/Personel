(function($) {
/*
 * Function: fnGetColumnData
 * Purpose:  Return an array of table values from a particular column.
 * Returns:  array string: 1d data array
 * Inputs:   object:oSettings - dataTable settings object. This is always the last argument past to the function
 *           int:iColumn - the id of the column to extract the data from
 *           bool:bUnique - optional - if set to false duplicated values are not filtered out
 *           bool:bFiltered - optional - if set to false all the table data is used (not only the filtered)
 *           bool:bIgnoreEmpty - optional - if set to false empty values are not filtered from the result array
 * Author:   Benedikt Forchhammer <b.forchhammer /AT\ mind2.de>
 */
$.fn.dataTableExt.oApi.fnGetColumnData = function ( oSettings, iColumn, bUnique, bFiltered, bIgnoreEmpty ) {
    // check that we have a column id
    if ( typeof iColumn == "undefined" ) return new Array();
     
    // by default we only want unique data
    if ( typeof bUnique == "undefined" ) bUnique = true;
     
    // by default we do want to only look at filtered data
    if ( typeof bFiltered == "undefined" ) bFiltered = true;
     
    // by default we do not want to include empty values
    if ( typeof bIgnoreEmpty == "undefined" ) bIgnoreEmpty = true;
     
    // list of rows which we're going to loop through
    var aiRows;
     
    // use only filtered rows
    if (bFiltered == true) aiRows = oSettings.aiDisplay;
    // use all rows
    else aiRows = oSettings.aiDisplayMaster; // all row numbers
 
    // set up data array   
    var asResultData = new Array();
     
    for (var i=0,c=aiRows.length; i<c; i++) {
        iRow = aiRows[i];
        var aData = this.fnGetData(iRow);
	
        var sValue = aData[iColumn];
         
        // ignore empty values?
        if (bIgnoreEmpty == true && sValue.length == 0) continue;
 
        // ignore unique values?
        else if (bUnique == true && jQuery.inArray(sValue, asResultData) > -1) continue;
         
        // else push the value onto the result data array
        else asResultData.push(sValue);
    }
     
    return asResultData;
}
/*
 * Function: .coFilter
 * Purpose:  Returns  filters for table.
 * Returns:  array string: 1d data array
 * Inputs:  
        string:sPlaceHolder -  optional - Position of the filters in data table(ex: thead th,tfoot th....etc DEFAULT:thead th)
        array:aoColumns - optional - List of column indexes of filters(for which columns you want to put the filters)(ex:aoColumns:[1,2,...]) 
        array:filtertype - optional - Type of filter  like text box,select box,date etc...(ex:filtertype: {0: "select", 2 : "date", 3: "text"}....DEFAULT  type is "text") 
        string:dateformat - optional - This one for date type filtering...(ex:dateformat:"D ,M d,yy" ) DEFAULT:"dd/mm/yy"
 * Author:   Naveen Kumar<naveen@eabyas.in>
 */
 $.fn.coFilter = function (options) {
    
    //Refernce to present data table
    var oTable = this;
	this.bStateSave= true;
	    //Parameters with default values
    var defaults = {
           sPlaceHolder: "thead th",
	   columntitles:null,
           aoColumns: null,
           filtertype:null,
	   customdata:null,
           dateformat:"dd/mm/yy"
    };
    //setting default parametera to properties object
    var properties = $.extend(defaults, options);
   //To get the length of the customdata array

 //  alert(properties.customdata[7]);
//ends here customdata array length
    /*
     *Filter types defination starts from here
     */
    /*
      * Function: fnCreateSelect
      *  Purpose:  To create select box type filter for data table  
      * Returns:  select box element with given data.
      * Inputs:  
           object:aData - Data object from the original data source for the row.
           string:heading - optional - Default option string for select box
      */
    function fnCreateSelect( j,aData, heading ){
      var i;
      var iLen=0;
       $.each(aData, function(key, val) { iLen+=1; } );
       var wreg=/ /g;
       var cheading=heading.replace(wreg,"");
      var r='<div class="selfilterpos"><label>'+heading+'</label><select class ="'+cheading+'" name="selfilter'+j+'"><option value="">Select '+heading+( $("th").val());+'</option>', i;
     
     //alert(aData.length);
      for ( i=0 ; i<iLen ; i++ ){
           var regX = /(<([^>]+)>)/ig;
         var t=aData[i].replace(regX, "");
        r += '<option value="'+t+'">'+t+'</option>';
      }
      return r+'</select></div>';
    }
    /*
      * Function: fnCreateText
      *  Purpose:  To create Text box type filter for data table  
      * Returns:  Textbox element
      * Inputs:  
	   object:aData - Data object from the original data source for the row.
	   string:heading - optional - Default option string for select box
    */
    function fnCreateText( j,aData, heading ){
	var wreg=/ /g;
	var cheading=heading.replace(wreg,"");
	var r='<div class="textfilterpos"><label>'+heading+'</label><input type="text" value="" class="'+cheading+'" name="textfilter'+j+'"></div>';
	return r;
    }
    /*
      * Function: fnCreateDate
      *  Purpose:  To create Text box with date picker filter for data table  
      * Returns:  Textbox element with jQueryUI date picker (Used jqueryUI for this datepicker)
      * Inputs:  
	   object:aData - Data object from the original data source for the row.
	   string:heading - optional - Default option string for select box
    */
    function fnCreateDate( j,aData, heading ){
	var wreg=/ /g;
       var cheading=heading.replace(wreg,"");
      var r='<div class="datefilterpos"><label>'+heading+'</label><input  type="text" value="" class="'+cheading+'" name="datefilter'+j+'" ></div>';
      return r;
    }
    
            function fnCreateDateRangeInput(oTable) {

            var aoFragments = sRangeFormat.split(/[}{]/);

            th.html("");
            //th.html(_fnRangeLabelPart(0));
            var sFromId = oTable.attr("id") + '_range_from_' + i;
            var from = $('<input type="text" class="date_range_filter" id="' + sFromId + '" rel="' + i + '"/>');
            from.datepicker();
            //th.append(from);
            //th.append(_fnRangeLabelPart(1));
            var sToId = oTable.attr("id") + '_range_to_' + i;
            var to = $('<input type="text" class="date_range_filter" id="' + sToId + '" rel="' + i + '"/>');
            //th.append(to);
            //th.append(_fnRangeLabelPart(2));

            for (ti = 0; ti < aoFragments.length; ti++) {

                if (aoFragments[ti] == properties.sDateFromToken) {
                    th.append(from);
                } else {
                    if (aoFragments[ti] == properties.sDateToToken) {
                        th.append(to);
                    } else {
                        th.append(aoFragments[ti]);
                    }
                }
            }


            th.wrapInner('<span class="filter_column filter_date_range" />');
            to.datepicker();
            var index = i;
            aiCustomSearch_Indexes.push(i);


            //------------start date range filtering function

            //$.fn.dataTableExt.afnFiltering.push(
            oTable.dataTableExt.afnFiltering.push(

            function(oSettings, aData, iDataIndex) {
                if (oTable.attr("id") != oSettings.sTableId) return true;

                var dStartDate = from.datepicker("getDate");

                var dEndDate = to.datepicker("getDate");

                if (dStartDate == null && dEndDate == null) {
                    return true;
                }

                var dCellDate = null;
                try {
                    if (aData[_fnColumnIndex(index)] == null || aData[_fnColumnIndex(index)] == "") return false;
                    dCellDate = $.datepicker.parseDate($.datepicker.regional[""].dateFormat, aData[_fnColumnIndex(index)]);
                } catch (ex) {
                    return false;
                }
                if (dCellDate == null) return false;


                if (dStartDate == null && dCellDate <= dEndDate) {
                    return true;
                } else if (dStartDate <= dCellDate && dEndDate == null) {
                    return true;
                } else if (dStartDate <= dCellDate && dCellDate <= dEndDate) {
                    return true;
                }
                return false;
            });
            //------------end date range filtering function

            $('#' + sFromId + ',#' + sToId, th)
                .change(function() {
                oTable.fnDraw();
                fnOnFiltered();
            });


        }
    /*
     *Filter types defination Ends here
    */
    /* Add filters in a given position with given types */

		
    //$(properties.sPlaceHolder).each( function ( i ) {
    //     if($.inArray(i,properties.aoColumns) != -1){
	//To select the type of filter for given position
	//i for position index
	//for (i=0;i<properties.aoColumns.length; i++) {
	$(properties.aoColumns).each(function(i){
         switch (properties.filtertype[properties.aoColumns[i]]) {
            //For select box
	    case "select":
		if (properties.customdata != null && !$.isEmptyObject(properties.customdata[properties.aoColumns[i]])) {
		    var data=properties.customdata[properties.aoColumns[i]];
		} else {
		    var data=oTable.fnGetColumnData(properties.aoColumns[i]);
		}
		if(data.length ===undefined || (data.length !=='undefined' && data.length>0)){
                this.innerHTML = fnCreateSelect( i,data ,properties.columntitles[properties.aoColumns[i]]);
		 $('.filterarea').append(this.innerHTML);
		        var wreg=/ /g;
                       var cheading=properties.columntitles[properties.aoColumns[i]].replace(wreg,"");
                $("."+cheading+"").change( function () {
                oTable.fnFilter( $(this).val(),properties.aoColumns[i],0,0,false);
                });
		}
                break;
	    //For date selector
             case "date":
                 this.innerHTML = fnCreateDate( i,oTable.fnGetColumnData(properties.aoColumns[i]),properties.columntitles[properties.aoColumns[i]] );
		  $('.filterarea').append(this.innerHTML);
		var wreg=/ /g;
                var cheading=properties.columntitles[properties.aoColumns[i]].replace(wreg,"");
		 $("."+cheading+"").datepicker({
                    changeMonth: true,
                    changeYear: true,
                    dateFormat: properties.dateformat,
                     showOn: "button",
                     buttonImageOnly: false
                });
		//On user keyup
                $("."+cheading+"").keyup( function () {
                    oTable.fnFilter( $(this).val(), properties.aoColumns[i],0,0,false);
                });
		//On select of date
                $("."+cheading+"").change( function () {
                    oTable.fnFilter( $(this).val(), properties.aoColumns[i],0,0,false );
                });
                break;
                case "date-range":
                  this.innerHTML = fnCreateDateRangeInput( i,oTable.fnGetColumnData(properties.aoColumns[i]),properties.columntitles[properties.aoColumns[i]] );
                
                 break;
	    //For text box and also default case
            case "text":
            default:
                this.innerHTML = fnCreateText( i,oTable.fnGetColumnData(properties.aoColumns[i]),properties.columntitles[properties.aoColumns[i]] );
		var wreg=/ /g;
                var cheading=properties.columntitles[properties.aoColumns[i]].replace(wreg,"");
		 $('.filterarea').append(this.innerHTML);
                $("."+cheading+"").keyup( function () {
                 oTable.fnFilter( $(this).val(), properties.aoColumns[i] );
                });
                break;
        }
	});
    //}});
 };

    $('.filterarea').before('<a href="#" id="close-filter" class="filter-toggle">Filters</a>');
     $('.filterarea').before('<a href="#" id="open-filter" class="filter-toggle">Filters</a>');
$('#open-filter').hide();
    $('a#open-filter').click(function() {
        $('.filterarea').slideDown(1000);
	$('#close-filter').show();
        $('#open-filter').hide();
        return false;
    });
    $('a#close-filter').click(function() {
        $('.filterarea').slideUp(1000);
        $('#open-filter').show();
	        $('#close-filter').hide();
        return false;
    });
})(jQuery);