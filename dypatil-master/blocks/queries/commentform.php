<?php
 //function commenthtmlform($loginuserid = '') {
 //  $popup = html_writer:: start_tag('div',array('id'=>"basicModal$loginuserid",'style'=>'display:none;'));
 //  $popup .= html_writer:: tag('h4','Add Comment',array('class'=>'formheading'));
 //  $popup .= html_writer:: start_tag('form',array('name'=>'myForm','id'=>'commentform','action'=>'#','onsubmit'=>'return commentformvalidation()'));
 //  //$popup .= html_writer:: empty_tag('input',array('type'=>'hidden','name'=>'queryid'));
 //  $popup .= html_writer:: start_tag('div',array('class'=>'summerylabel'));
 //  $popup .= html_writer:: tag('label','Summery',array('for'=>'summery'));
 //  
 //  $popup .= html_writer:: start_tag('span',array('class'=>'summerytextbox'));
 //  $popup .= html_writer:: empty_tag('input',array('type'=>'text','name'=>'summery','id'=>'summery','class'=>'textbox'));
 //  $popup .= html_writer:: end_tag('span');
 //  
 //  $popup .= html_writer:: end_tag('div');
 //  
 //  $popup .= html_writer:: start_tag('div',array('class'=>'summerylabel'));
 //  
 //  $popup .= html_writer:: tag('label','Comment',array('for'=>'comment'));
 //  
 //  $popup .= html_writer:: start_tag('span',array('class'=>'summerytextbox'));
 //  $popup .= html_writer:: tag('textarea','comment',array('class'=>'commentfield','id'=>'comment'));
 //  $popup .= html_writer:: end_tag('span');
 //  
 //  $popup .= html_writer:: end_tag('div');
 //  
 //  $popup .= html_writer:: start_tag('div',array('class'=>'submitbutton'));
 //  $popup .= html_writer:: empty_tag('input',array('type'=>'submit','name'=>'submit','value'=>'submit',"id"=>"commentsubmit$loginuserid"));
 //  $popup .= html_writer:: end_tag('div');
 //  $popup .= html_writer:: end_tag('form');
 //  
 //  $popup .= html_writer:: end_tag('div');
 //  
 ////$popup .= html_writer:: script("
 ////    function commentformvalidation(){
 ////        event.preventDefault();
 ////        alert('hi');
 ////        var a = document.forms['myForm']['summery'].value();
 ////        alert(a);
 ////        if(a =='' || a == null){
 ////           alert('Please Enter Summery field');
 ////            return false;
 ////        }
 ////        else{
 ////            alert(a);
 ////        }
 ////    }
 ////   ");
 //return $popup;
 //}
 
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