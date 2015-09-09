function cobaltcourse(sid, did)
{
    $("#myratings").dialog({
        width: 450, modal: true,
        closeText: "",
        position: {my: "center", at: "center", of: "#id_fullname"},
        title: "Create Main Course"
    });
    $.ajax({url: "createcourse.php?schoid=" + sid + "&deptid=" + did,
        beforeSend: function() {
        },
        success: function(result) {
            $("#myratings").html(result);
        },
        cache: false, dataType: "html"});

}
function onlinecourse(sid, did) {
    $("#myratings").dialog({
        width: 450, modal: true,
        closeText: "", position: {my: "center", at: "center", of: "#id_fullname"},
        title: "Create Online Moodle Course"});
    $.ajax({url: "onlinecourse.php?schoid=" + sid + "&deptid=" + did,
        beforeSend: function() {
        },
        success: function(result) {
            $("#myratings").html(result);
        },
        cache: false, dataType: "html"});
}
function coursevalidation() {
    var x = document.getElementById('fullname').value;
    var y = document.getElementById('shortname').value;
    var z = document.getElementById('credithour').value;
    var ct = document.getElementById('coursetype').value;
    var cc = document.getElementById('coursecost').value;    
    var s = document.getElementById('sid').value;
    var d = document.getElementById('did').value;
    if (x == "") {
        document.getElementById('error').innerHTML = 'Enter Course Fullname';
        return false;
    }
    // ----------Edited by hema------------
    if (y) {
        var response = $.ajax({type: "GET",
            url: "namevalidation.php?sn=" + escape(y),
            async: false
        }).responseText;


        if (response == "exist") {
            document.getElementById('error').innerHTML = 'Entered Course ID used by another course';
            return false;
        }
        else
            document.getElementById('error').innerHTML = '';
    }
    //---------------------------------------------

    if (y == "") {
        document.getElementById('error').innerHTML = 'Enter Course ID';
        return false;
    }

    if (z == "") {
        document.getElementById('error').innerHTML = 'Enter Credit Hours';
        return false;
    }
    if (isNaN(z)) {
        document.getElementById('error').innerHTML = 'Enter Numeric value for Credit hours';
        return false;
    }
    if (z < 1) {
        document.getElementById('error').innerHTML = 'Enter Positive value for Credit hours';
        return false;
    }
    if (isNaN(cc)) {
        document.getElementById('error').innerHTML = 'Enter Numeric value for course Cost';
        return false;
    }
    if (cc !="" && cc < 1) {
        document.getElementById('error').innerHTML = 'Enter Positive value for Course Cost';
        return false;
    }
    else {
        document.getElementById('error').innerHTML = '';
        $("#myratings").dialog("close");
        $.ajax({type: "POST", url: "insertcourse.php?schoid=" + s + "&deptid=" + d + "&cn=" + escape(x) + "&sn=" + escape(y) + "&cr=" + escape(z) + "&ct=" + escape(ct) + "&cc=" + escape(cc),
            contentType: "application/text; charset=utf-8",
            beforeSend: function(result) {
                // xhr.setRequestHeader("Accept-Charset","utf-8");
                // xhr.setRequestHeader("Content-Type","application/x-www-form-urlencoded;charset=utf-8");
                // alert(result);  
            },
            success: function(result) {
                $("#myratings1").html(result);
                var newccid = $("#newcobaltcourseid1").val();
                theList = document.getElementById('id_cobaltcourseid');
                yourOption = document.createElement("option");
                yourOption.value = newccid;
                yourOption.innerText = x;
                theList.insertBefore(yourOption, theList.firstChild);
                theList.selectedIndex = 0;
            },
            cache: false, dataType: "html"});
    }
}
function onlinevalidation() {
    var x = document.getElementById('fullname').value;
    var y = document.getElementById('shortname').value;
    var s = document.getElementById('sid').value;
    var d = document.getElementById('did').value;
    if (x == "") {
        document.getElementById('error').innerHTML = 'Enter Course Fullname';
        return false;
    }
    if (y == "") {
        document.getElementById('error').innerHTML = 'Enter Course ID';
        return false;
    }
    else {
        document.getElementById('error').innerHTML = '';
        $("#myratings").dialog("close");

        $.ajax({url: "insertmoodlecourse.php?schoid=" + s + "&deptid=" + d + "&cn=" + x + "&sn=" + y,
            beforeSend: function() {
            },
            success: function(result) {
                
                theList = document.getElementById('id_onlinecourseid');
                yourOption = document.createElement("option");
                yourOption.value = x;
                yourOption.innerHTML = x;
                theList.insertBefore(yourOption, theList.firstChild);
                theList.selectedIndex = 0;
                $("#myratings2").html(result);
            },
            cache: false, dataType: "html"});
    }
}
