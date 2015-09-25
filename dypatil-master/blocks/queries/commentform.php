<?php 
 function commenthtmlform($loginuserid) {
  global $CFG;
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
  $popup .= "<input type='hidden' name='queryid' value='$loginuserid'>";
  $popup .= "<label for='summary' class='summarylabel'>Summary<span style='color:red;'>*</span></label>";
  $popup .= "<input type='text' name='summary' id='summarytextbox$loginuserid'>";
  $popup .= "<div>";
  $popup .= "<label for='comment' class='summarylabel'>Comment<span style='color:red;'>*</span></label>";
  $popup .= "<textarea name='comment' id='comments$loginuserid' rows='3' cols='24'></textarea>";
  $popup .=  "<div id='submitbutton'>";
  $popup .= "<input type='submit' name='submit' id='submit$loginuserid' value='submit'>";
  $popup .=  "</div>";
  $popup .= "</form>";
  $popup .= "</div>";
  return $popup;
 }