<?php

require_once(dirname(__FILE__) . '/../../config.php');

global $DB, $USER, $CFG,$PAGE;

$PAGE->set_url('/blocks/queries/comment_emailtostudent.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Email to student');
$PAGE->set_pagelayout('admin');
echo $OUTPUT->header();
if($commentformdata = data_submitted()) {
        $commentdata = new stdclass();
        $commentdata->queryid = $commentformdata->queryid;
        $commentdata->responduser = $USER->id;
        $commentdata->summary = $commentformdata->summary;
        $commentdata->comment = $commentformdata->comment;
        $commentdata->postedtime = time();
        
        /********object for change status in queries table from 0 to 1********/
        $toupdate = new stdclass();
        $toupdate->id = $commentformdata->queryid;
        $toupdate->status = 1;
        $queryresponse=$DB->insert_record('query_response',$commentdata);
        $update = $DB->update_record('queries',$toupdate); 
        $queryrecord = $DB->get_record_sql("SELECT * FROM {queries} WHERE id=$commentformdata->queryid");
        $touser = $DB->get_record_sql("SELECT * FROM {user} WHERE id=$queryrecord->postedby");
        
        /*************to send email to student***************/
        $emailobject = new stdClass();
        
        $emailobject->fullname = fullname($touser);
        $emailobject->summary = $commentformdata->summary;
        $emailobject->comment = $commentformdata->comment;
        
        $subject = get_string('subjectforemailtostudent','block_queries',$queryrecord->subject);
        $emailtext = get_string('replytostudenttext','block_queries',$emailobject);   
        $emailhtml = get_string('replytostudenthtml','block_queries',$emailobject); //Email html body
        
        email_to_user($touser,$USER,$subject,$emailtext,$emailhtml);
        redirect($CFG->wwwroot.'/blocks/queries/display_queries.php');
    }
echo $OUTPUT->footer();