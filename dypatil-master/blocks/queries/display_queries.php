<?php
require_once(dirname(__FILE__).'/../../config.php');
global $PAGE, $USER, $DB, $CFG;

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
      $studentpostedqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE postedby = $studentid");
      $data = array();
      foreach($studentpostedqueries as $studentpostedquerie){
         $row = array();
         $row[] = $studentpostedquerie->subject;
         $row[] = $studentpostedquerie->description;
         $postedby = $studentpostedquerie->postedby;
         $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
         $studentname = fullname($posteduser);
         $row[] = $studentname;
         $row[] = html_writer:: tag('span',date("d/m/Y h:i a",$studentpostedquerie->timecreated),array('class'=>'date'));
         if($studentpostedquerie->status == 0){
            $row[] = get_string('notresponded','block_queries'); 
         }else {
             $row[] = get_string('responded','block_queries'); 
         }
         $data[] = $row;
      }
      $table = new html_table();
      $table->head  = array('subject','Description','posted by','postedtime','status');
      $table->width = '100%';
      $table->size= array('20%','36%','20%','12%','12%');
      $table->id    = 'queryresponse';  
      $table->data  = $data;
      $string = html_writer:: tag('h3',get_string('myqueries','block_queries'),array());
      $string .= html_writer::table($table);
      $string .= html_writer:: tag('a',get_string('backtohome','block_queries'),array('href'=>$CFG->wwwroot.'/index.php','class'=>'backtohome'));
      echo $string;
   }
   else{
      $courses = enrol_get_users_courses($USER->id);
      //for login user is a instructor
      $allinstructors = array();
      foreach($courses as $course){
         $sql="SELECT u.id, u.email, u.firstname, u.lastname, ra.roleid, cxt.instanceid AS courseid
               FROM {context} AS cxt
               JOIN {role_assignments} AS ra
               ON cxt.id = ra.contextid 
               JOIN {user} AS u
               ON ra.userid = u.id
               WHERE cxt.instanceid = $course->id AND ra.roleid = 10 AND cxt.contextlevel = 50 AND u.id = $USER->id";
         $instructors =  $DB->get_record_sql($sql);
         if($instructors){
            $allinstructors[] = $instructors->id;
         }        
      }
      if(!empty($allinstructors)) {
         $registrarid = $USER->id;
           $instructorresponses = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = $registrarid AND userrole = 'instructor'");
         if(!empty($instructorresponses)){
           $data = array();
            foreach($instructorresponses as $instructorresponse){
               $row = array();
               $ins_id = $instructorresponse->id;
               $row[] = $instructorresponse->subject;
               $row[] = $instructorresponse->description;
               $postedby = $instructorresponse->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
               //$student = fullname($posteduser);
               $student = $posteduser->firstname;
               $row[] = $student;
               $row[] = date("d/m/y h:i a",$instructorresponse->timecreated);
               if($instructorresponse->status === 0){
                  $row[] = get_string('notresponded','block_queries'); 
               }else {
                  $row[] = get_string('responded','block_queries'); 
               }
               
               $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$ins_id","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($ins_id)"));
               //$row[] = $instructorresponse->userrole;
               $popup = commenthtmlform($ins_id);
               $row[] = $popup;
               $data[] = $row;
            }
         }
      }
      //for login user is a registrar
      $allregistrars = array();
      foreach($courses as $course){
          // to get registrars in course level
         $sql = "SELECT u.id, u.email, u.firstname, u.lastname, ra.roleid, cxt.instanceid AS courseid
                 FROM {context} AS cxt
                 JOIN {role_assignments} AS ra
                 ON cxt.id = ra.contextid 
                 JOIN {user} AS u
                 ON ra.userid = u.id
                 WHERE cxt.instanceid = $course->id AND ra.roleid = 9 AND cxt.contextlevel = 50 AND u.id =$USER->id";
         $registrars =  $DB->get_record_sql($sql);
         if($registrars){
            $allregistrars[] = $registrars->id;
         }
      }
      if(!empty($allregistrars)){
         $registrarresponses = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = $USER->id AND userrole = 'registrar'");
         $data = array();
         foreach($registrarresponses as $registrarresponse){
            $row = array();
            $reg_id = $registrarresponse->id;
            $row[] = $registrarresponse->subject;
            $row[] = $registrarresponse->description;
            $postedby = $registrarresponse->postedby;
            $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
            //$studentname = fullname($posteduser);
            $studentname = $posteduser->firstname;
            $row[] = $studentname;
            $row[] = date("d/m/y h:i a",$registrarresponse->timecreated);
            if($registrarresponse->status == 0){
               $row[] = get_string('notresponded','block_queries'); 
            }else {
               $row[] = get_string('responded','block_queries'); 
            }
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$reg_id","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($reg_id)"));
            //$row[] = $registrarresponse->userrole;
            $popup = commenthtmlform($reg_id);
            $row[] = $popup;
            $data[] = $row;
            
            // code for display coments in toggle
            $queryresponses = $DB->get_records_sql("SELECT * FROM {query_response} WHERE queryid =$reg_id");
            if($queryresponses){
               $commentsdata = array(); 
               foreach($queryresponses as $queryresponse){
                  $commentsrow = array();
                  $commentsrow[] = $queryresponse->summery;
                  $commentsrow[] = $queryresponse->comment;
                  $responduserid = $queryresponse->responduser;
                  $respondusername = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$responduserid");
                  $commentsrow[] = $respondusername->firstname;
                  $commentsrow[] = time("d/m/y h:i a",$queryresponse->postedtime);
                  
                  $commentsdata[] = $commentsrow;
               }
               $table = new html_table();
               $table->head  = array(get_string('summery','block_queries'),get_string('comment','block_queries'),
                                     get_string('postedby','block_queries'),get_string('postedtime','block_queries'));
               $table->width = '100%';
               $table->size = array('30%','50%','10%','10%');  
               $table->data  = $commentsdata;
               $string1 = html_writer:: tag('h3',get_string('comments','block_queries'),array());
               $string1 .= html_writer::table($table);
            }
         }
      }
      if(is_siteadmin()){
           $adminqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = 2 AND userrole = 'admin'");
         if(!empty($adminqueries)){
            $data = array();
            foreach($adminqueries as $adminquery){
               $row = array();
               $adminqueryid = $adminquery->id;
               $row[] = $adminquery->subject;
               $row[] = $adminquery->description;
               $postedby = $adminquery->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
               //$studentname = fullname($posteduser);
               $studentname = $posteduser->firstname;
               $row[] = $studentname;
               $row[] = date("d/m/y h:i a",$adminquery->timecreated);
               if($adminquery->status == 0){
                  $row[] = get_string('notresponded','block_queries');  
               } else {
                  $row[] = get_string('responded','block_queries'); 
               }
               $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$adminqueryid","class"=>"commenticonpostion","onclick"=>"mycommentpopupform($adminqueryid)"));             
               $popup = commenthtmlform($adminqueryid);
               //$popup .= mycommentpopupform($adminqueryid);
               $row[] = $popup;
               $data[] = $row;
            }                
         }
      }
      if(!empty($data)){
         $table = new html_table();
         $table->head  = array(get_string('subjectt','block_queries'),get_string('descriptionn','block_queries'),
                                 get_string('postedby','block_queries'),get_string('postedtime','block_queries'),
                                 get_string('status','block_queries'),get_string('comment','block_queries'));
         $table->width = '100%';
         $table->size = array('20%','40%','15%','10%','10%','2%');
         $table->id    = 'queryresponse';  
         $table->data  = $data;
         $string = html_writer:: tag('h3',get_string('myqueries','block_queries'),array());
         $string .= html_writer::table($table);
         echo $string;
      }
      else {
         echo $string = html_writer:: tag('h4',get_string('noqueries','block_queries'),array()); 
      }
   }
echo $OUTPUT->footer();