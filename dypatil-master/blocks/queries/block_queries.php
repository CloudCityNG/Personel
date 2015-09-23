<?php
defined('MOODLE_INTERNAL') || die();
  class block_queries extends block_base {
        /**
        * block initializations
        */
    public function init() {
      $this->title = get_string('pluginname', 'block_queries');
    }
    function get_required_javascript() {
        $this->page->requires->jquery();
        $this->page->requires->js(('/blocks/queries/js/commentform_popup.js'),true);
        $this->page->requires->js(('/blocks/queries/js/validate.min.js'),true);
      }
    public function get_content() {
      global $DB;
      if ($this->content !== null) {
        return $this->content;
      }
      global $CFG, $USER, $PAGE;
      $this->content = new stdClass();
      require_once($CFG->dirroot.'/blocks/queries/commentform.php');
      require_once($CFG->dirroot.'/blocks/queries/queries_form.php');
      
      $courses = enrol_get_users_courses($USER->id);
       /************Query for wheather login user instructor or not*************/
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
     /************Query for wheather login user registrar or not*************/
      $registrarlogin = array();
      foreach($courses as $course) {
        $sql="SELECT u.id, u.email, u.firstname, u.lastname
            FROM {context} AS cxt
            JOIN {role_assignments} AS ra
            ON cxt.id = ra.contextid 
            JOIN {user} AS u
            ON ra.userid = u.id
            WHERE cxt.instanceid = $course->id AND ra.roleid = 9 AND cxt.contextlevel = 50 AND u.id = $USER->id";
        $registrarrecord =  $DB->get_record_sql($sql);
        if($registrarrecord){
          $registrarlogin[] = $registrarrecord->id;
        }
      }
      /************Query for wheather login user student or not*************/
      $studentlogin = array();
      foreach($courses as $course) {
        $sql="SELECT u.id, u.email, u.firstname, u.lastname, ra.roleid
            FROM {context} AS cxt
            JOIN {role_assignments} AS ra
            ON cxt.id = ra.contextid 
            JOIN {user} AS u
            ON ra.userid = u.id
            WHERE cxt.instanceid = $course->id AND ra.roleid = 5 AND cxt.contextlevel = 50 AND u.id = $USER->id";
        $studentrecord =  $DB->get_record_sql($sql);
        if($studentrecord){
          $studentlogin[] = $studentrecord->id;
        }
      }
      /*********************for admin login**************************/
      if(is_siteadmin($USER->id)){
        $adminqueries = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid = 2 AND userrole = 'admin' ORDER BY id DESC LIMIT 5");
        if($adminqueries) {
            $data = array();
            foreach($adminqueries as $adminquery){
              $row = array();
              $adminqueryid = $adminquery->id;
              $adm_decription = html_writer:: tag('span',$adminquery->description,array());
              $row[] = html_writer:: tag('p',$adminquery->subject.$adm_decription,array("class"=>"tooltip1"));
              $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$adminqueryid",'title'=>get_string('addacomment','block_queries'),'onclick'=>"mycommentpopupform($adminqueryid)",'class'=>'iconclass'));
              $row[] = commenthtmlform($adminqueryid);
              $data[] = $row;
            }
          
          $table = new html_table();
          $table->head  = array(get_string('subjectt','block_queries'),get_string('comment','block_queries'));
          $table->width = '100%';
          $table->size = array('90%','10%');
          $table->data  = $data;
          $this->content->text[] = html_writer::table($table);
          $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
          $this->content->text = implode('',$this->content->text);
        }
        else {
          $this->content->text = html_writer:: tag('p',get_string('noprevioussubjects','block_queries'),array());
        }
      }  /**********************for Instructor login*************************/
      elseif(!empty($instructorlogin)){
        $instructorqueries = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid =$USER->id AND userrole = 'instructor' ORDER BY id DESC LIMIT 5");
       
        if($instructorqueries){
          $data = array();
          foreach($instructorqueries as $instructorquery){
            $row = array();
            $instructorid = $instructorquery->id;
            $ins_decription = html_writer:: tag('span',$instructorquery->description,array());
            $row[] = html_writer:: tag('p',$instructorquery->subject.$ins_decription,array('class'=>'tooltip1'));
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$instructorid",'title'=>get_string('addacomment','block_queries'),"onclick"=>"mycommentpopupform($instructorid)",'class'=>'iconclass'));
            
            $row[] = commenthtmlform($instructorid);  
            //$row[] = $popup;
           
            $data[] = $row;
          }
          $table = new html_table();
         $table->head  = array(get_string('subjectt','block_queries'),get_string('comment','block_queries'));
          $table->width = '100%'; 
          $table->data  = $data;
          $this->content->text[] = html_writer::table($table);
          $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
          $this->content->text = implode('',$this->content->text);
        }
        else {
          $this->content->text = html_writer:: tag('p',get_string('noprevioussubjects','block_queries'),array());
        }
      }  /************************for Registrar login****************************/
      elseif(!empty($registrarlogin)) {
        $registrarqueries = $DB->get_records_sql("SELECT * FROM {block_queries} WHERE userid =$USER->id AND userrole = 'registrar' ORDER BY id DESC LIMIT 5");
        if($registrarqueries){
          $data = array();
          foreach($registrarqueries as $registrarquery){
            $row = array();
            $registrarid = $registrarquery->id;
            $reg_decription = html_writer:: tag('span',$registrarquery->description,array());
            $row[] = html_writer:: tag('p',$registrarquery->subject.$reg_decription,array('class'=>'tooltip1'));
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$registrarid",'title'=>get_string('addacomment','block_queries'),"onclick"=>"mycommentpopupform($registrarid)",'class'=>'iconclass'));
            
            $popup = commenthtmlform($registrarid);
                       
            $row[] = $popup;
            $data[] = $row;
          }
          $table = new html_table();
          $table->head  = array(get_string('subjectt','block_queries'),get_string('comment','block_queries'));
          $table->width = '100%';
          $table->size = array('95%','5%');
          $table->data  = $data;
          $this->content->text[] = html_writer:: tag('h5','Ask a Question',array());
          $this->content->text[] = html_writer::table($table);
          $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
          $this->content->text = implode('',$this->content->text);
        }
        else {
          $this->content->text = html_writer:: tag('p',get_string('noprevioussubjects','block_queries'),array());
        }
      }  /**********************for student login**************************/
      elseif(!empty($studentlogin)){
        $formdata = new stdClass();
        $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
        $mform= new block_queries_form($actionpage);
        $this->content->text = $mform->render();  //to display form in block
      }
        $this->content->footer = '';
        $this->page->requires->js('/blocks/queries/js/commentform_popup.js');
        // Return the content object
        return $this->content;
    }
  }