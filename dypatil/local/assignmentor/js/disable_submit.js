$(document).ready(function () {

    var disable_submit = function () {
        var checked = $("input:checked").length;
        var selected = document.getElementById('mentorid').value;

        if ((selected == '0' || checked == '0')) {
            $('.assign_advisorbutton').attr('disabled', 'disabled');
        }
        else
            $('.assign_advisorbutton').removeAttr('disabled');
    };
    disable_submit();
    $(".check").on("click", disable_submit);
    $('#mentorid').on("change", disable_submit);

});
