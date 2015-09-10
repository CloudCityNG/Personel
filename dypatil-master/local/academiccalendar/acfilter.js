'use strict';
$(document).ready(function () {
    /* Initialise the DataTable */
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
    var oTable = tableContainer.dataTable({
        "iDisplayLength": 5,
        "sPaginationType": "bootstrap",
        "aaSorting": [],
        "aoColumnDefs": [
            {"bSearchable": false, "aTargets": [-1]},
            {"bSortable": false, "aTargets": [-1]},
            {"bVisible": false, "aTargets": [4, 5, 6, 7, 8, 9]},
        ],
        "sDom": 'p;l"bottom;"p<"clear">',
        "bInfo": false,
        "oLanguage": {
            "oPaginate": {
                "sFirst": "<<",
                "sLast": "  >>  ",
                "sNext": "  >",
                "bStateSave": true,
                "sPrevious": " <  "
            },
            "sLengthMenu": 'View:  <select>' +
                    '<option value="5">5</option>' +
                    '<option value="10">10</option>' +
                    '<option value="15">15</option>' +
                    '<option value="20">20</option>' +
                    '<option value="50">50</option>' +
                    '<option value="-1">All</option>' +
                    '</select>'
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
        aoColumns: [9, 1, 6, 7, 8, 4],
        customdata: {9: {0: "Global", 1: "Organization", 2: "Program", 3: "Course Offering"}},
        columntitles: {9: "Event Level", 1: "Event Type", 6: "Organization", 7: "Program", 8: "Course Offering", 4: "Start Date"},
        filtertype: {9: "select", 1: "select", 6: "select", 7: "select", 8: "select", 4: "date"},
        dateformat: "dd M yy"
    });

    $('.EventLevel').change(function () {
        var selectVal1 = $('.EventLevel :selected').val();
        if (selectVal1 === '') {
            $(".mine").css({opacity: 0.32});
        } else {
            $(".mine").css({opacity: 1});
        }
    });
    $('.mine').click(function () {
        var selectVal1 = $('.EventLevel :selected').val();
        var selectVal2 = $('.EventType :selected').val();
        var selectVal3 = $('.School :selected').val();
        var selectVal4 = $('.Semester :selected').val();
        var selectVal5 = $('.Program :selected').val();
        var selectVal6 = $('.StartDate').val();
        var selectVal7 = $('.EndDate').val();
        var tablevals = $('#cooktable tr td:nth-child(1)').text();
        if (selectVal1 === '') {
            alert("To download academiccalendar in PDF format\nNote : You can download the PDF after selecting any event level");
        }
        else if (tablevals === 'No matching records found') {
            alert("There are no records to download");
        }
        else {
            window.open("../../local/academiccalendar/acadmiccalendar_pdf.php?eventlev=" + selectVal1 + "&activitytype=" + selectVal2 + "&school=" + selectVal3 + "&semester=" + selectVal4 + '&program=' + selectVal5 + '&startdate=' + selectVal6 + '&enddate=' + selectVal7, '_blank');
        }
    });

});

/*
 * Function: fnShowHide
 *  Purpose:  To toggle the column
 * Inputs:  
 int:iCol - Column index to toggle.
 */
function fnShowHide(iCol) {
    /* Get the DataTables object again - this is not a recreation, just a get of the object */
    var oTable = $('#cooktable').dataTable();
    /*Toggle the column based on input from the user */
    var bVis = oTable.fnSettings().aoColumns[iCol].bVisible;
    oTable.fnSetColumnVis(iCol, bVis ? false : true);
}
/*
 * Function: fnViewevent
 *  Purpose:  To view full data in jQueryUI dialog (used jqueryUI for this)
 * Inputs:  
 int:i - Row index to view.
 */
function fnViewevent(i, etitle) {
    $("#contents").dialog({
        width: 700,
        height: 500,
        modal: true,
        closeText: "",
        title: etitle
    });
    $.ajax({url: "viewevent.php?id=" + i,
        beforeSend: function () {
            $('#contents').html('<img src="ajax-loader.gif" alt="loading.." style="margin:0 auto;" />');
        },
        success: function (result) {
            $("#contents").html(result);
        },
        error: function () {
            $('#contents').html('error');
        }, cache: false, dataType: "html"});
}

function deletecolumn(id) {
    // var info = 'id=' + id;
    $("#cooktable tbody tr").click(function (e) {
        if ($(this).hasClass('row_selected')) {
            //alert($(this).hasClass('row_selected').dt.value);
            $(this).removeClass('row_selected');
        }
        else {
            oTable.$('tr.row_selected').removeClass('row_selected');
            $(this).addClass('row_selected');
        }
    });
    r = confirm("Are you sure you want to delete?", {buttons:
                {Delete: true, Cancel: false}});

    if (r == true)
    {
        $.ajax({
            url: "delete.php?id=" + id,
            success: function () {
                window.location.reload();
                $('#page').append('<div class="message">successfully deleted!</div>');
                $('.message').fadeOut(5000, function () {
                });
            }, error: function ()
            {
                alert(html('error'));
            }});
    }
}
    