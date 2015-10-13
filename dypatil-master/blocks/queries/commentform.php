<?php 
 function commenthtmlform($loginuserid) {
  global $CFG, $DB;
  $popup ='';
  $popup .="<script type='text/javascript'>
  $(document).ready(function(){
  $('#submit$loginuserid').on('click',function(e){
  var summary$loginuserid=$('#summarytextbox$loginuserid').val();
  var comment$loginuserid=$('#comments$loginuserid').val();

  if(summary$loginuserid==undefined || summary$loginuserid==''){
   $('.error_box$loginuserid').html('<p style=\'color:red;\'>Please fill the required fields</p>');
   e.preventDefault();
  }else if(comment$loginuserid==undefined || comment$loginuserid==''){
   $('.error_box$loginuserid').html('<p style=\'color:red;\'>Please fill the required fields</p>');
   e.preventDefault();
  }
    });
  });
    </script>";
    
  $popup .= "<div id='basicModal$loginuserid' style='display:none;'><div class='error_box$loginuserid'></div>";
  $actionpage = $CFG->wwwroot.'/blocks/queries/comment_emailtostudent.php';
  $popup .= '<form name="myForm'.$loginuserid.'" method="post" action="'.$actionpage.'">';
  $popup .= "<div>";
  $result = $DB->get_record('block_queries',array('id'=>$loginuserid));
  $postedby = $result->postedby;
  $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
  $firstname ="<span class='queries_postedusername'>".fullname($posteduser)."</span>";
  $postedtime ="<span class='queries_postedtime'>".date("d/m/y h:i a",$result->timecreated)."</span>";
  $popup .="<div class='queries_querydetailsforcomment'><div class='queries_querydata'>";
  $popup .= "<span><b>".get_string('postedby','block_queries').":</b>".$firstname." <b>".get_string('on','block_queries')."</b>".$postedtime."</span><br>";
  $popup .= "<p class='queries_popupsubjectpara'><b>".get_string('subjectt','block_queries').":</b>&nbsp;&nbsp; ".$result->subject."</p>";
  $popup .= "<p><b>".get_string('descriptionn','block_queries').":</b></p>";
  $popup .= "<p class='queries_querycontent'>".$result->description."</p>";
  $popup .= "</div></div>";
  $popup .=  "<div class='queries_commentformfields'>";
  $popup .= "<input type='hidden' name='queryid' value='$loginuserid'>";
  $popup .= "<label for='summary' class='queries_summarylabel'>Summary<span style='color:red;'>*</span></label>";
  $popup .= "<input type='text' name='summary' id='summarytextbox$loginuserid' class='queries_textboxsummery'>";
  $popup .= "<div>";
  $popup .= "<label for='comment' class='queries_summarylabel'>Comment<span style='color:red;'>*</span></label>";
  $popup .= "<textarea name='comment' id='comments$loginuserid' rows='3' cols='30'></textarea>";
  $popup .=  "<div id='queries_submitbutton'>";
  $popup .= "<input type='submit' name='submit' id='submit$loginuserid' value='submit'>";
  $popup .=  "</div>";
  $popup .=  "</div>";
  $popup .= "</form>";
  $popup .= "</div>";

  return $popup;
 }