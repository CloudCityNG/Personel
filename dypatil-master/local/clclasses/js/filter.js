$(document).ready(function() {
    var selectReg = function() {
        var checked = $("input:checked").length;
        if ((checked == ""))  {
            $('input[type="submit"]').attr('disabled', 'disabled');
        }
        else
            $('input[type="submit"]').removeAttr('disabled');
    };
    selectReg();
    $("input[type=checkbox]").on("click", selectReg);

});