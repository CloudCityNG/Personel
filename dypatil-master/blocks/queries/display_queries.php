<?php
require_once(dirname(__FILE__).'/../../config.php');
global $PAGE, $USER, $DB, $CFG, $OUTPUT;
require_once($CFG->dirroot.'/blocks/queries/lib.php');
require_once($CFG->dirroot.'/blocks/queries/commentform.php');
$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/queries/js/responsive.js');
$PAGE->requires->js('/blocks/queries/js/commentform_popup.js');

$studentid = optional_param('studentid',null,PARAM_INT);

$PAGE->set_url('/blocks/queries/display_queries.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Queries table');
$PAGE->navbar->add('My Queries');
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
      // display records for student 
   if(!empty($studentid)){
      
      $studentpostedqueries = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE postedby = $studentid ORDER BY id DESC");
      $data = array();
      foreach($studentpostedqueries as $studentpostedquerie){
         $row = array();
         $postedby = $studentpostedquerie->userid;
         $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
         $postedto = fullname($posteduser);      
         
         $date_student = html_writer:: tag('span',date("d/m/Y h:i a",$studentpostedquerie->timecreated),array('class'=>'date'));
         if($studentpostedquerie->status == 0){
            $ifelse_student = get_string('notresponded','block_queries'); 
         }else {
            $ifelse_student = get_string('responded','block_queries'); 
         }
         //here we are calling js fuction to get the toggle
         $comment='<a href="javascript:void(0)" onclick="view('.$studentpostedquerie->id.')">View comments</a>';
         
         //here we are showing the table content in a list-----------------
         $profilepicture = $OUTPUT->user_picture($posteduser,array('size'=>35));
         $test='<div>
         <div class="profilepicture">'.$profilepicture.'</div>
         <p class="subjectclass"><b>'.$studentpostedquerie->subject.'</b><br><b>'.get_string('postedto','block_queries').': </b>'.$postedto.' - '.$date_student.'</p>
         <hr class="horizontalline">
         <p class="comment_summary">'.$studentpostedquerie->description.'</p>
         <div class="informationdiv"><b>'.get_string('status','block_queries').': </b>'.$ifelse_student.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$comment.'</div>
         <div class="toggle_style"><span style="display:none;"  class="toggle'.$studentpostedquerie->id.'">';
             
         /********* code for display comments in toggle-starts here************/
          
          $queryresponses = $DB->get_records_sql("SELECT * FROM {block_query_response} WHERE queryid=$studentpostedquerie->id ORDER BY id DESC");
            if($queryresponses){
                  foreach($queryresponses as $queryresponse){
                     $responduserid = $queryresponse->responduser;
                     $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                     
                     $comments = html_writer:: tag('b',$queryresponse->summary,array('class'=>'comment_summary'));
                     $postedby = html_writer:: tag('b',get_string('postedby','block_queries'),array());
                     $date = html_writer:: tag('span',date("d/m/y h:i a",$queryresponse->postedtime),array('class'=>'queries_postedtime'));
                     $comments .= html_writer:: tag('p',$postedby .' : '.$respondusername->firstname.'<b>&nbsp;&nbsp;&nbsp;'.get_string("on","block_queries").'</b>'.$date,array('class'=>'posted_by'));                    
                     
                     $comments .= html_writer:: start_tag('div',array( 'class'=>'togglediv'));
                     $comments .= html_writer:: tag('p',$queryresponse->comment,array('class'=>'toggle_comment'));
                     $comments .= html_writer:: end_tag('div',array());
                     
                     $comments .= html_writer:: start_tag('div',array( 'class'=>'toggledate'));
                     $comments .= html_writer:: end_tag('div',array());
                     $test .=$comments;
                    
                  }
               }
               //else condition if no comments are there to display----------
               else{
                  $test .= html_writer:: tag('p',get_string('nocomments','block_queries'),array('class'=>'nocomments'));
               }        
         /********* code for display comments in toggle-ended here************/
         $test.='</span></div></div>'; 
         $row[]=$test;
         $data[]=$row;
      }
      $table = new html_table();
      //table head-------
      $table->head = array('');
      $table->width = '100%';
      $table->id    = 'queryresponse';
      $table->data  = $data;
      $string = html_writer:: tag('h3',get_string('myqueries','block_queries'),array());
      $string .= html_writer::table($table);
      $string .= html_writer:: tag('a',get_string('backtohome','block_queries'),array('href'=>$CFG->wwwroot.'/index.php'));
      echo $string;
      //Here is our script for getting the toggle-------
       echo "<script>
             function view(id){
                  $('.toggle'+id).slideToggle('fast');
             }
      </script>";
      //end of script------------------------------------
   }
   else{
      $courses = enrol_get_users_courses($USER->id);  
      //call function for check login user instructor or not
       $instructorlogin = block_queries_getrole_user($courses,'instructor');
       
      if(!empty($instructorlogin)) {
         $registrarid = $USER->id;
         $instructorresponses = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid = $registrarid AND userrole = 'instructor' ORDER BY id DESC");
         if(!empty($instructorresponses)){
            $data = array();
            foreach($instructorresponses as $instructorresponse){
               $row = array();
               $ins_id = $instructorresponse->id;
               $postedby = $instructorresponse->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
               $student = fullname($posteduser);
               $date_instructor = date("d/m/y h:i a",$instructorresponse->timecreated);
               if($instructorresponse->status == 0){
                  $ifelse_instructor = get_string('notresponded','block_queries'); 
               }else {
                  $ifelse_instructor = get_string('responded','block_queries'); 
               }
               $click= html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/t/message.svg',"id"=>"showDialog$ins_id","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($ins_id)"));
               $popup = commenthtmlform($ins_id);
               //here we are calling js fuction to get the toggle
               $comment='<a href="javascript:void(0)" onclick="view('.$instructorresponse->id.')">View comments</a>';
               
               //here we are showing the table content in a list-----------------
                              
               $profilepicture = $OUTPUT->user_picture($posteduser,array('size'=>35));
               $test='<div><div>
               <div class="profilepicture">'.$profilepicture.'</div>
               <p class="subjectclass"><b>'.$instructorresponse->subject.'</b><br>'.get_string("by","block_queries").'&nbsp;&nbsp;'.$student.' - '.$date_instructor.'</p>
               <hr class="horizontalline">
               <p class="comment_summary">'.$instructorresponse->description.'</p>
               <div class="informationdiv"><b>'.get_string('status','block_queries').': </b>'.$ifelse_instructor.'<b>&nbsp;&nbsp;|&nbsp;&nbsp;'.get_string('comment','block_queries').':&nbsp; </b>'.$click.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$comment.'</div>
               </div>
               <div class="toggle_style"><span style="display:none;"  class="toggle'.$instructorresponse->id.'">';
             
               /********* code for display comments in toggle-started here************/
               
               $queryresponses = $DB->get_records_sql("SELECT * FROM {block_query_response} WHERE queryid=$ins_id ORDER BY id DESC");
               if($queryresponses){
                  foreach($queryresponses as $queryresponse){
                     $responduserid = $queryresponse->responduser;
                     $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                     
                    
                     
                     $comments = html_writer:: start_tag('div',array( 'class'=>'togglediv'));
                     $comments .= html_writer:: tag('b',$queryresponse->summary,array('class'=>'comment_summary'));
                     $time = html_writer:: tag('span','('.date("d/m/y h:i a",$queryresponse->postedtime).')',array());
                     $comments .= html_writer:: tag('p',$respondusername->firstname.$time,array('class'=>'posted_by'));
                     $comments .= html_writer:: tag('p',$queryresponse->comment,array('class'=>'toggle_comment'));
                     $comments .= html_writer:: end_tag('div',array());
               
                     $test .=$comments;
                    
                  }
               }
               //else condition if no comments are there to display----------
               else{
                  $test .= html_writer:: tag('p',get_string('nocomments','block_queries'));
               }        
               /*********end of code for display comments in toggle-starts here************/
               $test.='</span></div></div>';
               $test.= $popup;
               $row[]=$test;
               $data[]=$row;     
            }
         }
      }
      //for login user is a registrar
      
      $courses = enrol_get_users_courses($USER->id);
      
      $registrarlogin = block_queries_getrole_user($courses,'registrar');
      
      if(!empty($registrarlogin)){
         $registrarresponses = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid = $USER->id AND userrole = 'registrar' ORDER BY id DESC");
         $data = array();
         foreach($registrarresponses as $registrarresponse){
            $row = array();
            $reg_id = $registrarresponse->id;
            $postedby = $registrarresponse->postedby;
            $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
            $studentname = fullname($posteduser);
            $date_registrar = date("d/m/y h:i a",$registrarresponse->timecreated);
            if($registrarresponse->status == 0){
               $ifelse_registrar = get_string('notresponded','block_queries'); 
            }else {
               $ifelse_registrar = get_string('responded','block_queries'); 
            }
            $click_registrar = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/t/message.svg',"id"=>"showDialog$reg_id","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($reg_id)"));
            $popup_registrar = commenthtmlform($reg_id);
            //here we are calling js fuction to get the toggle
            $comment_registrar='<a href="javascript:void(0)" onclick="view('.$registrarresponse->id.')">View comments</a>';
            
            $profilepicture = $OUTPUT->user_picture($posteduser,array('size'=>35));
            $test='<div><div>
               <div class="profilepicture">'.$profilepicture.'</div>
               <p class="subjectclass"><b>'.$registrarresponse->subject.'</b><br>'.get_string("by","block_queries").'&nbsp;&nbsp;'.$studentname.' - '.$date_registrar.'</p>
               <hr class="horizontalline">
               <p class="comment_summary">'.$registrarresponse->description.'</p>
               <div class="informationdiv"><b>'.get_string('status','block_queries').': </b>'.$ifelse_registrar.'<b>&nbsp;&nbsp;|&nbsp;&nbsp;'.get_string('comment','block_queries').':&nbsp; </b>'.$click_registrar.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$comment_registrar.'</div>
               </div>
               <div class="toggle_style"><span style="display:none;"  class="toggle'.$registrarresponse->id.'">';
            
            
            /********* code for display comments in toggle-started here************/
           
           $queryresponses = $DB->get_records_sql("SELECT * FROM {block_query_response} WHERE queryid=$reg_id ORDER BY id DESC");
          if($queryresponses){
                  foreach($queryresponses as $queryresponse){
                     $responduserid = $queryresponse->responduser;
                     $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                     
                     
                     
                     $comments = html_writer:: start_tag('div',array( 'class'=>'togglediv'));
                     $comments .= html_writer:: tag('b',$queryresponse->summary,array('class'=>'comment_summary'));
                     $time= html_writer:: tag('span','('.date("d/m/y h:i a",$queryresponse->postedtime).')',array());
                     $comments .= html_writer:: tag('p',$respondusername->firstname.$time,array('class'=>'posted_by'));
                     
                     $comments .= html_writer:: tag('p',$queryresponse->comment,array('class'=>'toggle_comment'));
                     $comments .= html_writer:: end_tag('div',array());
                     $test .=$comments;
                  
                     $responduserid = $queryresponse->responduser;
                     $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                    
                  }
               }
               //else condition if no comments are there to display----------
               else{
                  $test .= html_writer:: tag('p',get_string('nocomments','block_queries'));
               }         
            /*********end of code for display comments in toggle-started here************/
            $test.='</span></div></div>';
            $test.=$popup_registrar;
            $row[]=$test;
            $data[] = $row;
         }
      }
      if(is_siteadmin()){
           $adminqueries = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid = 2 AND userrole = 'admin' ORDER BY id DESC");
         if(!empty($adminqueries)){
            $data = array();
            foreach($adminqueries as $adminquery){
               $row = array();
               $adminqueryid = $adminquery->id;
               $postedby = $adminquery->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
               $studentname = html_writer:: tag('a',$posteduser->firstname,array('href'=>$CFG->wwwroot.'/user/profile.php?id='.$posteduser->id));
               $date_admin = date("d/m/y h:i a",$adminquery->timecreated);
               if($adminquery->status == 0){
                  $ifelse_admin = get_string('notresponded','block_queries');  
               } else {
                  $ifelse_admin = get_string('responded','block_queries'); 
               }
               $click_admin = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/t/message.svg',"id"=>"showDialog$adminqueryid","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($adminqueryid)"));             
             
               $popup_admin = commenthtmlform($adminqueryid);
             
               //here we are calling js fuction to get the toggle
               $comment='<a href="javascript:void(0)" onclick="view('.$adminquery->id.')">View comments</a>';
               
               //here we are showing the table content in a list-----------------
               $profilepicture = $OUTPUT->user_picture($posteduser,array('size'=>35));
               $test ='<div><div>
               <div class="profilepicture">'.$profilepicture.'</div>
               <p class="subjectclass"><b>'.$adminquery->subject.'</b><br>'.get_string("by","block_queries").'&nbsp;'.$studentname.' - '.$date_admin.'</p>
               <hr class="horizontalline">
               <p class="comment_summary">'.$adminquery->description.'</p>
               <div class="informationdiv"><b>'.get_string('status','block_queries').': </b>'.$ifelse_admin.'<b>&nbsp;&nbsp;|&nbsp;&nbsp;'.get_string('comment','block_queries').':&nbsp; </b>'.$click_admin.'&nbsp;&nbsp;|&nbsp;&nbsp;'.$comment.'</div>
               </div>
               <div class="toggle_style"><span style="display:none;"  class="toggle'.$adminquery->id.'">';
               /********* code for display comments in toggle-started here************/
               $queryresponses = $DB->get_records_sql("SELECT * FROM {block_query_response} WHERE queryid=$adminqueryid ORDER BY id DESC");
               if($queryresponses){
                  foreach($queryresponses as $queryresponse){
                     $responduserid = $queryresponse->responduser;
                     $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                     
                     $comments = html_writer:: start_tag('div',array( 'class'=>'togglediv'));
                     $comments .= html_writer:: tag('b',$queryresponse->summary,array('class'=>'comment_summary'));
                     
                     $time= html_writer:: tag('span','('.date("d/m/y h:i a",$queryresponse->postedtime).')',array());
                     $comments .= html_writer:: tag('p',$respondusername->firstname.$time,array('class'=>'posted_by'));
                     
                     $comments .= html_writer:: tag('p',$queryresponse->comment,array('class'=>'toggle_comment'));
                     $comments .= html_writer:: end_tag('div',array());
                     $test .=$comments;
                     
                    
                  }
               }   //else condition if no comments are there to display----------
               else{
                  $test .= html_writer:: tag('p',get_string('nocomments','block_queries'));
               }           
               /*********end of code for display comments in toggle-started here************/
               $test.='</span></div></div>';
               $test .=$popup_admin;
               $row[]=$test;
               $data[] = $row;
            }                
         }
      }
      if(!empty($data)){
         $table_ins = new html_table();
         //table head-------
      $table_ins->head = array('');
      $table_ins->width = '100%';
      $table_ins->id    = 'queryresponse';
      $table_ins->data  = $data;
      $string = html_writer:: tag('h3',get_string('myqueries','block_queries'),array());
      $string .= html_writer::table($table_ins);
      $string .= html_writer:: tag('a',get_string('backtohome','block_queries'),array('href'=>$CFG->wwwroot.'/index.php'));
      echo $string;
      //Here is our script for getting the toggle-------
       echo "<script>
             function view(id){
                  $('.toggle'+id).slideToggle('fast');
             }
      </script>";
      //end of script------------------------------------
      }
      else {
         echo $string = html_writer:: tag('h4',get_string('noqueries','block_queries'),array()); 
      }
   }
echo $OUTPUT->footer();