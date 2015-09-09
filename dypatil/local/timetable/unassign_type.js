

function unassign_classtype(classtpeid) {

    $('input[name=deleteclasstypeid]').val(classtpeid);
    var s = $('input[name=deleteclasstypeid]').val();

    $("#id_classtype option[value='" + classtpeid + "']").removeAttr("selected");

}

$("#id_classtype option[value=-1]").click(function () {
    $('#id_classtype option').prop('selected', true);
    $("#id_classtype option[value=0]").removeAttr("selected");
    $("#id_classtype option[value=-1]").removeAttr("selected");
});

$("#id_scheduleclasstype").click(function () {

    //  $('#id_deleteclasstypeid').val(33);
    var s = $('input[name="deleteclasstypeid"]').val(0);
    //var s= $('#id_deleteclasstypeid').val()
    //  alert(s);
    // console.log($('#id_deleteclasstypeid').val());
});


function newclasstype(schoolid) {
    $("#createclasstype").dialog({
        width: 450, modal: true,
        closeText: " ",
        position: {my: "center", at: "center", of: "#id_classtype"},
        title: "Create class type"
    });
    $.ajax({url: M.cfg.wwwroot + "/local/timetable/classtypedialog.php?schoolid=" + schoolid,
        beforeSend: function () {
        },
        success: function (result) {
            $("#createclasstype").html(result);
        },
        cache: false, dataType: "html"});
}

function classtypevalidation()
{
    var cltypename = document.getElementById('classtype').value;
    var schoolid = document.getElementById('schoolid').value;
    if (cltypename == "") {
        document.getElementById('error').innerHTML = 'Enter the classtype';
        return false;
    }

    var regex = /\s+/gi;
    var wordCount = cltypename.trim().replace(regex, ' ').split(' ').length;
    if (wordCount == 2) {
        document.getElementById('error').innerHTML = 'Dont provide the space between the words.';
        return false;

    }
    // ----------Edited by hema------------
    //if (cltypename) {
    //    var response = $.ajax({type: "GET",
    //        url: "insertclasstype.php?cltypename=" + escape(cltypename),
    //        async: false,
    //         success: function(result) {
    //               console.log(result);
    //         }
    //    });
    //
    //
    //    if (response == "exist") {
    //        document.getElementById('error').innerHTML = 'This classtype already exists,try with another name';
    //        return false;
    //    }
    //    else
    //        document.getElementById('error').innerHTML = '';
    //}
    //---------------------------------------------


    else {
        document.getElementById('error').innerHTML = '';
        $("#createclasstype").dialog("close");

        $.ajax({type: "POST", url: M.cfg.wwwroot + "/local/timetable/insertclasstype.php?userinputsid=" + schoolid + "&userinputctname=" + cltypename,
            contentType: "application/text; charset=utf-8",
            async: true,
            success: function (result) {
                if (result == 'exists') {
                    alert('Already name is exists, try with another name. Select from drop down ,If not available, its inactivated. To use same type please activate in classtype view.');
                }
                else {
                    $("#createclasstype").html(result);
                    // var newccid = $("#newcobaltcourseid1").val();
                    var newcltype = result;
                    theList = document.getElementById('id_classtype');
                    yourOption = document.createElement("option");
                    yourOption.value = newcltype;
                    yourOption.innerText = cltypename;
                    theList.insertBefore(yourOption, theList.firstChild);
                    theList.selectedIndex = 1;
                }
            },
            cache: false, dataType: "html"});
    }
}
