<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE;
require_once($CFG->dirroot.'/lib/formslib.php');

class block_queries_form extends moodleform {
     public function definition() {
          global $CFG, $USER ,$DB;
          $mform = $this->_form;
          
          $courses = enrol_get_users_courses($USER->id);
            $instructors12=array();
          foreach($courses as $course){
            // to get instructors in course level
               $sql="SELECT u.id, u.email, u.firstname, u.lastname
                    FROM {context} AS cxt
                    JOIN {role_assignments} AS ra
                    ON cxt.id = ra.contextid 
                    JOIN {user} AS u
                    ON ra.userid = u.id
                    WHERE cxt.instanceid = $course->id AND ra.roleid = 10 AND cxt.contextlevel = 50";
               $instructors =  $DB->get_records_sql($sql);                                                                                                                                                                                                
               foreach($instructors as $instructor){
                    $fullname = fullname($instructor);
                    $instructors12[$instructor->id.',instructor'] = $fullname;
               } 
          }
           $registraroptions=array();
          foreach($courses as $course){
               // to get registrars in course level
               $sql="SELECT u.id, u.email, u.firstname, u.lastname, ra.roleid
                    FROM {context} AS cxt
                    JOIN {role_assignments} AS ra
                    ON cxt.id = ra.contextid 
                    JOIN {user} AS u
                    ON ra.userid = u.id
                    WHERE cxt.instanceid = $course->id AND ra.roleid = 9 AND cxt.contextlevel = 50";
               $registrars =  $DB->get_records_sql($sql);
               foreach($registrars as $registrar){
                    $registrarfullname = fullname($registrar);
                    $registraroptions[$registrar->id.',registrar'] = $registrarfullname;
               }
          }
          $record = $DB->get_record_sql("SELECT * FROM {user} WHERE id = 2");
          $adminoption=array();
          $adminoption[$record->id.',admin'] = $record->firstname;
          $options = array( get_string('instructor', 'block_queries')=>$instructors12,get_string('registrar', 'block_queries')=>$registraroptions,get_string('admin', 'block_queries')=>$adminoption);
          $mform->addElement('html', html_writer::tag('span',get_string('usertype','block_queries'),array()));
          
          $mform->addElement('html', html_writer::start_tag('div',array('class'=>'moodleform_div')));
          $mform->addElement('selectgroups', 'usertype','', $options,array('class'=>'moodleform_selector'));
          //$mform->addHelpButton('usertype', 'askaquestion', 'block_queries');
          $mform->addElement('html',html_writer::end_tag('div',array()));
         
          $adminoption[$record->id] = $record->firstname;
          $mform->addElement('text','subject','',array('class'=>'moodleform_subject','placeholder' => 'Subject')); 
          $mform->setType('subject',PARAM_RAW);
          
          $mform->addElement('textarea', 'description','',array('class'=>'moodleform_textarea','placeholder' => 'Description'),'wrap="virtual" rows="3" cols="25" ');
          
          $this->add_action_buttons(FALSE,get_string('postquery','block_queries'));
          $mform->addElement('html', html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php?studentid='.$USER->id,'class'=>'mypreviewqueries')));
     }
}
