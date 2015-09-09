<?php
require_once(dirname(__FILE__).'/../../config.php');
global $PAGE, $USER, $DB;
//function get_required_javascript() {
//   $this->page->requires->jquery();
//   $this->page->requires->js('/blocks/queries/js/magnific-popup.js');
//   //$this->page->requires->js('/blocks/queries/js/responsive.js');
//   $this->page->requires->js('/blocks/queries/js/add_comment_form_popup.js');
//}
$PAGE->requires->jquery();
$PAGE->requires->js('/blocks/queries/js/magnific-popup.js');
$PAGE->requires->js('/blocks/queries/js/responsive.js');
$PAGE->requires->js('/blocks/queries/js/add_comment_form_popup.js');

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
            $row[] = 'Not Responded'; 
         }else {
             $row[] = 'Responded'; 
         }
         $deleteicon = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/grade_incorrect.png'));
         $data[] = $row;
      }
      $table = new html_table();
      $table->head  = array('subject','Description','posted by','postedtime','status');
      $table->width = '100%';
      $table->size= array('15%','34%','20%','16%','15%');
      $table->id    = 'queryresponse';  
      $table->data  = $data;
      $string = html_writer:: tag('h3','My Queries',array());
      $string .= html_writer::table($table);
      $string .= html_writer:: tag('a',get_string('backtohome','block_queries'),array('href'=>$CFG->wwwroot.'/index.php','class'=>'backtohome'));
      echo $string;
   }
   else{
      $courses = enrol_get_users_courses($USER->id);
      //for login user is a instructor
      $allinstructors = array();
      foreach($courses as $course){
         $sql = "SELECT u.id, u.email, u.firstname, u.lastname, ra.roleid, cxt.instanceid AS courseid
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
               $row[] = $instructorresponse->subject;
               $row[] = $instructorresponse->description;
               $postedby = $instructorresponse->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $postedby");
               $student = fullname($posteduser);
               $row[] = $student;
               $row[] = date("d M,Y h:i a",$instructorresponse->timecreated);
               if($instructorresponse->status === 0){
                  $row[] = 'Not Responded'; 
               }else {
                  $row[] = 'Responded'; 
               }
               $deleteicon = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/grade_incorrect.png'));
               $row[] = html_writer:: tag('a',$deleteicon,array('href'=>$CFG->wwwroot.'/deleterecord_querty.php?id='));
               $row[] = html_writer:: tag('a','Add comment', array('href'=>$CFG->wwwroot.'/blocks/queries/queries_addcomment_form.php?queryid='.$instructorresponse->id));
                $row[] = $instructorresponse->userrole;
               $data[] = $row;
            }
         }
      }
      //else{
      //    $string = html_writer:: tag('h4','You do not have queries',array());
      //}
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
            $row[] = $registrarresponse->subject;
            $row[] = $registrarresponse->description;
            $postedby = $registrarresponse->postedby;
            $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
            $studentname = fullname($posteduser);
            $row[] = $studentname;
            $row[] = date("d M,Y h:i a",$registrarresponse->timecreated);
            if($registrarresponse->status === 0){
               $row[] = 'Not Responded'; 
            }else {
               $row[] = 'Responded'; 
            }
            $deleteicon = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/grade_incorrect.png'));
            $row[] = html_writer:: tag('a',$deleteicon,array('href'=>$CFG->wwwroot.'/deleterecord_querty.php?id='));
            $row[] = html_writer:: tag('a','Add comment', array('href'=>$CFG->wwwroot.'/blocks/queries/queries_addcommet_form.php?queryid='.$registrarresponse->id));
            $row[] = $registrarresponse->userrole;
            $data[] = $row;
         }
      }
      //else{
      //    $string = html_writer:: tag('h4','You do not have queries',array());
      //}
      if(is_siteadmin()){
           $adminqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = 2 AND userrole = 'admin'");
         if(!empty($adminqueries)){
            $data = array();
            foreach($adminqueries as $adminquery){
               $row = array();
               $row[] = $adminquery->subject;
               $row[] = $adminquery->description;
               $postedby = $adminquery->postedby;
               $posteduser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$postedby");
               $studentname = fullname($posteduser);
               $row[] = $studentname;
               $row[] = date("d M, Y h:i a",$adminquery->timecreated);
               if($adminquery->status === 0){
                  $row[] = 'Not Responded'; 
               }else {
                   $row[] = 'Responded'; 
               }
               $deleteicon = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/grade_incorrect.png'));
               $row[] = html_writer:: tag('a',$deleteicon,array('href'=>$CFG->wwwroot.'/deleterecord_querty.php?id='.$adminquery->id));
               $row[] = html_writer:: tag('a','Add comment', array('href'=>$CFG->wwwroot.'/blocks/queries/queries_addcomment_form.php'));
               $row[] = $adminquery->userrole;
               $data[] = $row;
            }                
         }
         //else {
         //    $string = html_writer:: tag('h4','You do not have queries',array());
         //}
      }
      if(!empty($data)){
         $table = new html_table();
         $table->head  = array('subject','Description','posted by','postedtime','status','Action', 'Comment','role');
         $table->width = '100%';
         $table->id    = 'queryresponse';  
         $table->data  = $data;
         $string = html_writer:: tag('h3','My Queries',array());
         $string .= html_writer::table($table);
         echo $string;
      }
      else {
         echo $string = html_writer:: tag('h4','You do not have queries',array()); 
      }
   }
//   echo "<div>You can click the button to show the basic jQuery UI dialog box.</div>
//		 <input type='button' value='Show basic dialog!' id='showDialog' />";
//   echo "<div id='basicModal' title='Basic dialog'>
//         <p>This is the default dialog which is useful for displaying i.</p>
//         </div>";

  
echo $OUTPUT->footer();