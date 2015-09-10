
 function enrollclass_ajax(id,semid,courseid,schoolid,classarray){
  $.ajax({
  type: "POST",
  url: "registration.php",
  data: { id:id, semid:semid, courseid:courseid, schoolid:schoolid, addenroll:1,sesskey:'sesskey',ajax_response:1 }
  })
  .done(function( msg ) {
    $.each(classarray, function (index,value) {    
      if (value==id) { 
                $("#result"+value).text("Enrolled , Waiting for Approval");
      }
      else 
       $("#result"+value).text("Already requested,Cannot request for another class of same course");      
       });    
  });   
 }