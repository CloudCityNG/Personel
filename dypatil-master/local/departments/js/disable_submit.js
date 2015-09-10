$(document).ready(function () {

    var disable_submit = function () {
        var checked = $("input:checked").length;
        var selected = document.getElementById('movetoid').value;

        if ((selected == '0' || checked == '0')) {
            $('.assign_deptbutton').attr('disabled', 'disabled');
        }
        else
            $('.assign_deptbutton').removeAttr('disabled');
    };
    disable_submit();
    $(".check").on("click", disable_submit);
    $('#movetoid').on("change", disable_submit);

});
