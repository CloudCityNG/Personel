<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE, $DB, $USER;
$PAGE->set_url('/blocks/queries/sendingemail.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('sending email');
//$PAGE->navbar->add('sending email');
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
    if($formdata = data_submitted()){
      $toinsertrecord = new stdclass();
      $useridandtype = explode(',',$formdata->usertype);
      $toinsertrecord->userid = $useridandtype[0];
      $toinsertrecord->userrole = $useridandtype[1];
      $toinsertrecord->subject = $formdata->subject;
      $toinsertrecord->description = $formdata->description;
      $toinsertrecord->postedby = $USER->id;
      $toinsertrecord->status = 0;
      $toinsertrecord->timecreated = time();
      //print_object($toinsertrecord);
      $DB->insert_record('queries',$toinsertrecord);
      //send email code
      //$sendingdata = $DB->get_record_sql("SELECT * FROM {queries} WHERE id = $useridandtype[0]");
      $tosenduser = $toinsertrecord->userid;
      $toUser = $DB->get_record_sql("SELECT * FROM {user} WHERE id = $tosenduser");
      //for email body
      $emailbodyobject = new stdClass();
      $emailbodyobject->fullname = fullname($toUser);
      $emailbodyobject->description = $toinsertrecord->description;
      $messageText = get_string('emailtext','block_queries',$emailbodyobject);  //Email text
      $messageHtml = get_string('emailhtmlbody','block_queries', $emailbodyobject);  //email html body
   
      email_to_user($toUser, $USER, $toinsertrecord->subject, $messageText, $messageHtml);
      $url = $CFG->wwwroot.'/blocks/queries/display_queries.php?studentid='.$USER->id;
      redirect($url);
   }     
echo $OUTPUT->footer();