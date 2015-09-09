
function fnaddModuleCost(ClassId, MoocId, path) {
    $("#myaddcost").dialog({
	width: 600,
	modal: true,
	closeText: "",
	title: 'Add Price'
    });
    $.ajax({url:path+"/local/onlinepayment/addcost.php?classid="+ClassId+"&moocid="+MoocId,
    beforeSend: function(){
    },
    success:function(result){
	$("#myaddcost").html(result);
    },
     error: function(){
	$("#myaddcost").html('error');
    },cache: false,dataType: "html"});
}
