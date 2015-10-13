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
        require_once($CFG->dirroot.'/blocks/queries/lib.php');
        require_once($CFG->dirroot.'/blocks/queries/commentform.php');
        require_once($CFG->dirroot.'/blocks/queries/queries_form.php');
        
        //query to enrol users courses particulars
        $courses = enrol_get_users_courses($USER->id);
      
        //calling function to get instuctror data
        $instructorlogin = block_queries_getrole_user($courses,'instructor');
      
        //calling function to get registrar data
        $registrarlogin = block_queries_getrole_user($courses,'registrar');
        
        //calling function to get student data
        $studentlogin = block_queries_getrole_user($courses,'student');
        
        /************calling function for getting data to the logged in user*************/
        $this->content->text = block_queries_display_view($USER,$instructorlogin,$registrarlogin,$studentlogin);
  
        $this->content->footer = '';
        $this->page->requires->js('/blocks/queries/js/commentform_popup.js');
        // Return the content object
        return $this->content;
    }
  }