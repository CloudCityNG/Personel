
	function mycommentpopupform(queryid){
		$('#basicModal'+queryid).dialog({
		   modal: true,
		   height: 320,
		   width: 370 
		});
	}
	//var a = form.summery.value;
	//var a =$("[name='summery']").val();
	//var a=document.forms['myForm']['summery'].value;
	//var a = document.getElementById("comment").value;
	//var a = $["#summery"].val();
	//var a = $('#summery' );

    //code for the display all comments
   function viewresponses(id){
		$('.student'+id).slideToggle('fast');
		if($('.student'+id).css('display') != 'none') {
			$(this).find('.dataTables_wrapper').css('display', 'block');
		} else {
			$(this).find('.dataTables_wrapper').css('display', 'none');
		}
    }