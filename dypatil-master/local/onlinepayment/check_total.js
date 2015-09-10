var final = 0;
var total = 0;
var cost1 = 0;
function gettot(cost, id) {

    if ($("#" + id).is(':checked')) {
        //alert($("#finalamount"+id).val());
        total = $("#finalamount" + id).val();
        if (total == 0) {

            $("#finalamount" + id).attr("value", cost);
            $("#finalamount" + id).val(cost);
            cost1 = $("#finalamount" + id).val();
            final = parseFloat(final) + parseFloat(cost1);
            final = final.toFixed(2);
            $("#total").val(final);
        }
        else {

            final = parseFloat(final) + parseFloat(total);
            final = final.toFixed(2);
            $("#total").val(final);
        }
    }
    else {
        cost1 = $("#finalamount" + id).val();
        // $("#finalamount"+id).val(cost);
        final = $("#total").val();
        // alert("cost"+cost1+"final"+final);  
        if (cost1 != 0) {
            if (final != 0) {

                final = parseFloat(final) - parseFloat(cost1);
                final = final.toFixed(2);
                $("#total").val(final);
            }
        }

        if (cost1 == 0) {
            // alert(final+"total"+total);
            final = parseFloat(total) - parseFloat(final);
            final = final.toFixed(2);
            $("#total").val(final);
        }
    }

    //alert(total);
    //$( "#total" ).text(total);
    //$( "#total" ).attr("value",total);        
    //$("#total").val(total);

}// end of function


function get_discountrate(key, classid, itemtype, price) {
    var s = document.getElementsByClassName("dcode");
    //   alert(key);
    $(s).each(function(index) {
//alert(index);
        if (key == index) {
            var dis = $(s[index]).val();
            var request = $.ajax({
                url: "ajaxresponse.php",
                type: "POST",
                data: {'discountcode': dis, 'classid': classid, 'type': itemtype},
                dataType: "html"

            });

            request.done(function(msg) {
                //  alert(msg);
                if (msg == 0) {
                    alert("Invalid  discount code");
                    var total = $("#" + classid + "cost").val();
                    $("#finalamount" + classid).attr("value", total);
                    $("#finalamount" + classid).val(total);

                }
                else {
                    $("#finalamount" + classid).attr("value", msg);
                    //alert(classid);
                    $("#finalamount" + classid).val(msg);

                    $("#dcode" + classid).attr("readonly", "readonly");
                    if ($("#" + classid).is(':checked')) {
                        var difference = parseFloat(price) - parseFloat(msg);
                        //  alert(difference);
                        var tot = $("#total").val();
                        var deduction = tot - difference;

                        $("#total").val(deduction);

                    }
                }
            });

            request.fail(function(jqXHR, textStatus) {
                alert("Request failed: " + textStatus);
            });



        }//end of if      
    }); // end of each functions



}



//
//function get_totalchange($classid){
//  alert('hi');
//  
//}