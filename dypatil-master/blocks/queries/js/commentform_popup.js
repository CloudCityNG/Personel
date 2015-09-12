	function myformvalidation(){
		var a=document.forms['commentsform']['summery'].value;
		var b=document.forms['commentsform']['summery'].value;
		if (a==null || a=='',b==null || b==''){
			alert('All Fields are Required');
			return false;
		}
    }
	//var a =$('[name='summery']').val();
	//if( document.commentsform.summery.value == "" ){
	//	   alert( "Please provide your name!" );
	//	   document.myForm.Name.focus() ;
	//	   return false;
	//	}
	
	function mycommentpopupform(queryid){
		
		$('#basicModal'+queryid).dialog({
		   modal: true,
		   height: 320,
		   width: 400 
		});
		event.preventDefault();
		myformvalidation();
	}
    //code for the display all comments
   function viewresponses(id){
		$('.student'+id).slideToggle('fast');
		if($('.student'+id).css('display') != 'none') {
			$(this).find('.dataTables_wrapper').css('display', 'block');
		} else {
			$(this).find('.dataTables_wrapper').css('display', 'none');
		}
    }