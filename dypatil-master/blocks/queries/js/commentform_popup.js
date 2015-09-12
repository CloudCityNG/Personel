function myformvalidation(){
			//var a =$('[name='summery']').val();
		var a=document.forms['commentsform']['summery'].value;
		if (a==null || a==''){
			alert('Please Fill All Required Field');
			return false;
		}
    }
	
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
	
	  // form = $("#basicModal'.$adminqueryid.'").find( "form" ).on( "submit", function( event ) {                                     
      //                                  event.preventDefault();
      //                                  alert("hi");
      //                                  myformvalidation();
      //                                  alert("hi my office");
      //                                 });
		  
//		  var a=document.forms['commentsform']['summery'].value;
			
//		  function myformvalidation(){
//            
//            console.log('this is the myformvalidation declaration');
//                var a =$('[name='summery']').val();
//                alert(a);
//                var b=document.forms['commentsform']['comment'].value;
//                if (a==null || a=='',b==null || b==''){
//                  alert('Please Fill All Required Field');
//                  return false;
//                }
//          } mycommentpopupform
		  
    //code for the display all comments
   function viewresponses(id){
		$('.student'+id).slideToggle('fast');
		if($('.student'+id).css('display') != 'none') {
			$(this).find('.dataTables_wrapper').css('display', 'block');
		} else {
			$(this).find('.dataTables_wrapper').css('display', 'none');
		}
    }