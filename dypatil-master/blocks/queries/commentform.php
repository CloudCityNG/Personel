<?php
 function commenthtmlform($loginuserid = '') {
   $popup = html_writer:: start_tag('div',array('id'=>"basicModal$loginuserid",'style'=>'display:none;'));
   $popup .= html_writer:: tag('h4','Add Comment',array('class'=>'formheading'));
   $popup .= html_writer:: start_tag('form',array('name'=>'commentsform','onsubmit'=>'mycommentpopupform('.$loginuserid.')','action'=>''));
   //$popup .= html_writer:: empty_tag('input',array('type'=>'hidden','name'=>'queryid'));
   $popup .= html_writer:: start_tag('div',array('class'=>'summerylabel'));
   $popup .= html_writer:: tag('label','Summery',array('for'=>'summery'));
   
   $popup .= html_writer:: start_tag('span',array('class'=>'summerytextbox'));
   $popup .= html_writer:: empty_tag('input',array('type'=>'text','name'=>'summery','class'=>'textbox'));
   $popup .= html_writer:: end_tag('span');
   
   $popup .= html_writer:: end_tag('div');
   
   $popup .= html_writer:: start_tag('div',array('class'=>'summerylabel'));
   
   $popup .= html_writer:: tag('label','Comment',array('for'=>'comment'));
   
   $popup .= html_writer:: start_tag('span',array('class'=>'summerytextbox'));
   $popup .= html_writer:: tag('textarea','comment',array('class'=>'commentfield'));
   $popup .= html_writer:: end_tag('span');
   
   $popup .= html_writer:: end_tag('div');
   
   $popup .= html_writer:: start_tag('div',array('class'=>'submitbutton'));
   $popup .= html_writer:: empty_tag('input',array('type'=>'submit','name'=>'summery','value'=>'submit',"id"=>"commentsubmit$loginuserid"));
   $popup .= html_writer:: end_tag('div');
   $popup .= html_writer:: end_tag('form');
   
   $popup .= html_writer:: end_tag('div');
   return $popup;
 }