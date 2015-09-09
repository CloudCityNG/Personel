<?php
defined('MOODLE_INTERNAL') || die();
  class block_queries extends block_base {
        /**
        * block initializations
        */
    public function init() {
      $this->title = get_string('pluginname', 'block_queries');
    }
    public function get_content() {
      global $DB;
      if ($this->content !== null) {
        return $this->content;
      }
      global $CFG,$USER;
      $this->content = new stdClass();
      require_once($CFG->dirroot.'/blocks/queries/queries_form.php');
      
      $courses = enrol_get_users_courses($USER->id);
      //print_object($courses);
      $instructorlogin = array();
      foreach($courses as $course) {
        $sql="SELECT u.id, u.email, u.firstname, u.lastname
             FROM {context} AS cxt
             JOIN {role_assignments} AS ra
             ON cxt.id = ra.contextid 
             JOIN {user} AS u
             ON ra.userid = u.id
             WHERE cxt.instanceid = $course->id AND ra.roleid = 10 AND cxt.contextlevel = 50 AND u.id = $USER->id";
         $instructor =  $DB->get_record_sql($sql);
        if($instructor) {
          $instructorlogin[] = $instructor->id;
        }
      }
      $registrarlogin = array();
      foreach($courses as $course) {
        $sql="SELECT u.id, u.email, u.firstname, u.lastname
             FROM {context} AS cxt
             JOIN {role_assignments} AS ra
             ON cxt.id = ra.contextid 
             JOIN {user} AS u
             ON ra.userid = u.id
             WHERE cxt.instanceid = $course->id AND ra.roleid = 5 AND cxt.contextlevel = 50 AND u.id = $USER->id";
        $studentrecord =  $DB->get_record_sql($sql);
        if($studentrecord){
          $registrarlogin[] = $studentrecord->id;
        }
      }
      if(is_siteadmin($USER->id)){
        $adminqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = 2 AND userrole = 'admin' ORDER BY 'timecreated' DESC LIMIT 5");
        //print_object($adminqueries);exit;
        if($adminqueries) {
          $data = array();
          foreach($adminqueries as $adminquery){
            $row = array();
            $row[] = $adminquery->subject;
            $row[] = "<input type= 'submit' name='submit' value='submit'>";
            $description = html_writer:: tag('span',$adminquery->description,array());
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array('Subject','comment');
        $table->width = '100%'; 
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      elseif(!empty($instructorlogin)){
        $instructorqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = $USER->id AND userrole = 'instructor' ORDER BY 'timecreated' DESC LIMIT 5");
        //print_object($instructorqueries);
        if($instructorqueries){
          $data = array();
          foreach($instructorqueries as $instructorquery){
            $row[] = array();
            $row[] = $instructorquery->subject;
            $row[] = html_writer:: tag('a','Add comment',array('href'=>$CFG->wwwroot.'/#'));
            $instructordecription = $instructorquery->description;
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array('Subject','comment');
        $table->width = '100%'; 
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      elseif(!empty($registrarlogin)){
        $registrarqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = $USER->id AND userrole = 'instructor' ORDER BY 'timecreated' DESC LIMIT 5");
        //print_object($instructorqueries);
        if($registrarqueries){
          $data = array();
          foreach($registrarqueries as $registrarquery){
            $row[] = array();
            $row[] = $registrarquery->subject;
            $row[] = html_writer:: tag('a','Add comment',array('href'=>$CFG->wwwroot.'/#'));
            $registrardecription = $registrarquery->description;
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array('Subject','comment');
        $table->width = '100%'; 
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      else {
        $formdata = new stdClass();
        $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
        $mform= new block_queries_form($actionpage);
        $this->content->text = $mform->render();  //to display form in block
        //$this->content->text = $mform->display();  //to display form in block
        //$toinsertrecord = new stdclass();
        //if($formdata = $mform->get_data()) {
        //  $toinsertrecord->usertype = $formdata->usertype;
        //  if(($formdata->usertype === 'instructor') && ($formdata->instrs > 0)){
        //    $toinsertrecord->userid = $formdata->instrs;
        //  }
        //  elseif(($formdata->usertype === 'registrar') && $formdata->registrs > 0){
        //    $toinsertrecord->userid = $formdata->registrs;
        //  }
        //  elseif(($formdata->usertype === 'admin') && $formdata->siteadmin > 0){
        //    $toinsertrecord->userid = $formdata->siteadmin;
        //  }
        //  $toinsertrecord->subject = $formdata->subject;
        //  $toinsertrecord->description = $formdata->description['text'];
        //  $toinsertrecord->description_format = $formdata->description['format'];
        //  $toinsertrecord->postedby = $USER->id;
        //  $toinsertrecord->status = 0;
        //  $toinsertrecord->timecreated = time();     
        //    
        //  $DB->insert_record('queries',$toinsertrecord );
        //}
      }
        
        //// Return the content object
        return $this->content;
    }
  }