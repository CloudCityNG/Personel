function myformvalidation(){
            alert('hi leooffice');
            console.log('sadasdasdads');
			    //var a =$('[name='summery']').val();
			alert(a);
            var b=document.forms['commentsform']['comment'].value;
            if (a==null || a=='',b==null || b==''){
              alert('Please Fill All Required Field');
              return false;
            }
          };
		  
  function mycommentpopupform($adminqueryid = '') {
	$(document).ready(function() {
								$("#showDialog'.$adminqueryid.'").click(function(){
								  $("#basicModal'.$adminqueryid.'").dialog({
									modal: true,
									height: 320,
									width: 400
								  });
								});
							  });
	form = $("#basicModal'.$adminqueryid.'").find( "form" ).on( "submit", function( event ) {                                     
			  event.preventDefault();
			  alert("hi");
			  myformvalidation();
			  //alert("hi");
		   });  
  }
  
  //function myfunction(id) {
  //  $('.view'+id).slideToggle('fast');
  //}