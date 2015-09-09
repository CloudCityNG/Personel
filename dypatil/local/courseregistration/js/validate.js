//Niranjan changes
$(document).ready(function () {
    var selectReg = function () {
        var selected = document.getElementById('id_semester').value;

        if ((selected == '0')) {
            $('#id_addfilter').attr('disabled', 'disabled');
        }
        else {
            $('#id_addfilter').removeAttr('disabled');
        }
    };
    selectReg();
    $('#id_semester').on("change", selectReg);
});
