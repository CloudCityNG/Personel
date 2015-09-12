function myformvalidation(){
            //alert('hi leooffice');
            console.log('sadasdasdads');
			//var a =$('[name='summery']').val();
			var a=document.forms['commentsform']['summery'].value;
			alert(a);
            var b=document.forms['commentsform']['comment'].value;
            if (a==null || a=='',b==null || b==''){
              alert('Please Fill All Required Field');
              return false;
            }
          };
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
		  
	function mycommentpopupform(queryid){
		
		$('#basicModal'+queryid).dialog({
		   modal: true,
		   height: 320,
		   width: 400 
		});
		event.preventDefault();
		myformvalidation();
		alert("hi my office");
	}
	

   function viewresponses(id){
		$('.student'+id).slideToggle('fast');
		if($('.student'+id).css('display') != 'none') {
			$(this).find('.dataTables_wrapper').css('display', 'block');
		} else {
			$(this).find('.dataTables_wrapper').css('display', 'none');
		}
    }