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
      global $CFG, $USER, $PAGE;
      require_once($CFG->dirroot.'/blocks/queries/renderer.php');
       //var a=document.forms['commentsform']['summery'].value;
      $a = html_writer::script("
          function myformvalidation(){
            alert('hi leooffice');
            console.log('sadasdasdads');
          };    
        "); 
  
            //   var a =$('[name='summery']').val();
            //
            //alert(a);
            //
            //var b=document.forms['commentsform']['comment'].value;
            //if (a==null || a=='',b==null || b==''){
            //  alert('Please Fill All Required Field');
            //  return false;
            //}
  
      function mycommentpopupform($adminqueryid = '') {
        $script = html_writer::script('$(document).ready(function() {
                                    $("#showDialog'.$adminqueryid.'").click(function(){
                                      $("#basicModal'.$adminqueryid.'").dialog({
                                        modal: true,
                                        height: 320,
                                        width: 400
                                      });
                                    });
                                  });
                     form = $("#basicModal'.$adminqueryid.'").find( "form" ).on( "submit", function( event ) {                                     
                                        event.preventDefault();
                                        alert("hi");
                                        myformvalidation();
                                        //alert("hi");
                                       });
                    ');
        return $script;
      }
      
      $this->content = new stdClass();
      require_once($CFG->dirroot.'/blocks/queries/queries_form.php');
      require_once($CFG->dirroot.'/blocks/queries/queries_addcomment_form.php');
      //require_once($CFG->dirroot.'/blocks/queries/renderer.php');
      function get_required_javascript() {
          $PAGE->requires->jquery();
      }
      
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
             WHERE cxt.instanceid = $course->id AND ra.roleid = 9 AND cxt.contextlevel = 50 AND u.id = $USER->id";
        $registrarrecord =  $DB->get_record_sql($sql);
        if($registrarrecord){
          $registrarlogin[] = $registrarrecord->id;
        }
      }
      $studentlogin = array();
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
          $studentlogin[] = $studentrecord->id;
        }
      }
      if(is_siteadmin($USER->id)){
        $adminqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid = 2 AND userrole = 'admin' ORDER BY id DESC LIMIT 5");
        
        if($adminqueries) {
          $data = array();
          foreach($adminqueries as $adminquery){
            //print_object(date('d/m/Y h:m a',$adminquery->timecreated));
            $row = array();
            $adminqueryid = $adminquery->id;
            $adm_decription = html_writer:: tag('span',$adminquery->description,array());
            $row[] = html_writer:: tag('p',$adminquery->subject.$adm_decription,array("class"=>"tooltip1"));
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$adminqueryid"));
            $popup = commenthtmlform($adminqueryid);
            $popup .= mycommentpopupform($adminqueryid);
    
            $row[] = $popup;
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array(get_string('subjectt','block_queries'),'');
        $table->width = '100%';
        $table->size = array('90%','10%');
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      elseif(!empty($instructorlogin)){
        $instructorqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid =$USER->id AND userrole = 'instructor' ORDER BY id DESC LIMIT 5");
       
        if($instructorqueries){
          $data = array();
          foreach($instructorqueries as $instructorquery){
            $row = array();
            $instructorid = $instructorquery->id;
            $ins_decription = html_writer:: tag('span',$instructorquery->description,array());
            $row[] = html_writer:: tag('p',$instructorquery->subject.$ins_decription,array('class'=>'tooltip1'));
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$instructorid"));
            
            $popup = commenthtmlform($instructorid);
            $popup .= mycommentpopupform($instructorid);
            
            $row[] = $popup;
           
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array(get_string('subjectt','block_queries'),'');
        $table->width = '100%'; 
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      elseif(!empty($registrarlogin)){
        $registrarqueries = $DB->get_records_sql("SELECT * FROM {queries} WHERE userid =$USER->id AND userrole = 'registrar' ORDER BY id DESC LIMIT 5");
        //print_object($instructorqueries);
        if($registrarqueries){
          $data = array();
          foreach($registrarqueries as $registrarquery){
            $row = array();
            $registrarid = $registrarquery->id;
            $reg_decription = html_writer:: tag('span',$registrarquery->description,array());
            $row[] = html_writer:: tag('p',$registrarquery->subject.$reg_decription,array('class'=>'tooltip1'));
            $row[] = html_writer:: empty_tag('img',array('src'=>$CFG->wwwroot.'/pix/i/feedback_add.gif',"id"=>"showDialog$registrarid"));
            
            $popup = commenthtmlform($registrarid);
            $popup .= mycommentpopupform($registrarid);
            
             $row[] = $popup;
            $data[] = $row;
          }
        }
        $table = new html_table();
        $table->head  = array(get_string('subjectt','block_queries'),'');
        $table->width = '100%';
        $table->size = array('95%','5%');
        $table->data  = $data;
        $this->content->text[] = html_writer::table($table);
        $this->content->text[] = html_writer:: tag('a',get_string('mypreviewqueries','block_queries'),array('href'=>$CFG->wwwroot.'/blocks/queries/display_queries.php'));
        $this->content->text = implode('',$this->content->text);
      }
      elseif(!empty($studentlogin)){
        $formdata = new stdClass();
        $actionpage = $CFG->wwwroot.'/blocks/queries/sendingemail.php';
        $mform= new block_queries_form($actionpage);
        $this->content->text = $mform->render();  //to display form in block
      }
      
        $this->content->footer = '';
        //$this->page->requires->js('/blocks/queries/js/commentform_popup.js');

        // Return the content object
        return $this->content;
    }
  }