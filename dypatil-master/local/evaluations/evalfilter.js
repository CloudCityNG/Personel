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
        "aLengthMenu": [[5, 10, 25, -1], [5, 10, 25, "All"]],
        "aoColumnDefs": [
            {"bSearchable": false, "aTargets": [-1]},
            {"bSortable": false, "aTargets": [-1]},
            {"bVisible": false, "aTargets": [3, 4]},
        ],
        "sPaginationType": "bootstrap",
        "sDom": '"bottom;p"<"clear">lrtip',
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
    })


    oTable.coFilter({
        sPlaceHolder: ".filterarea",
        aoColumns: [2, 3, 4],
        columntitles: {2: "Evaluation Type", 3: "Course Offering", 4: "Organization"},
        filtertype: {2: "select", 3: "select", 4: "select"},
    });
});