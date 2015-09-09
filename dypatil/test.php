<?php
require_once 'config.php';
ini_set('display_errors',1);
error_reporting(E_ALL);
      global $CFG,$USER;
      require_once($CFG->dirroot.'/mod/wiki/create_form.php');
      
      $PAGE->set_pagelayout('admin');
  $PAGE->set_url('/test.php');
 
    echo  $OUTPUT->header();
            $mform= new mod_wiki_create_form();
            $mform->display();
          echo  $OUTPUT->footer();
            