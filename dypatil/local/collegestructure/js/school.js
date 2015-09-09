$(document).ready(function () {

    var selectReg = function () {
        var checked = $("input:checked").length;
        var selected = document.getElementById('movetoid').value;
        if ((selected == '' || checked == '0')) {
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
        else
            $('input[type="submit"]').removeAttr('disabled');
    };
    selectReg();
    $("input[type=checkbox]").on("click", selectReg);
    $('#movetoid').on("change", selectReg);

});
