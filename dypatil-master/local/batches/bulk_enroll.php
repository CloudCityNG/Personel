<?php
// $Id: inscriptions_massives.php 356 2010-02-27 13:15:34Z ppollet $
/**
 * A bulk enrolment plugin that allow teachers to massively enrol existing accounts to their courses,
 * with an option of adding every user to a group
 * Version for Moodle 1.9.x courtesy of Patrick POLLET & Valery FREMAUX  France, February 2010
 * Version for Moodle 2.x by pp@patrickpollet.net March 2012
 */

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once($CFG->libdir.'/csvlib.class.php');
require_once ($CFG->dirroot.'/local/batches/bulk_enrollib.php');
require_once ($CFG->dirroot.'/local/batches/bulk_enroll_form.php');

$batchid=optional_param('bid',0,PARAM_INT);
$mode=optional_param('mode','',PARAM_TEXT);
///// Security and access check
require_login();
//$context = context_course::instance($course->id);
//require_capability('moodle/role:assign', $context);
//
///// Start making page
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/batches/mass_enroll.php');
//
$strinscriptions = get_string('bulkenrol', 'local_batches');
//
//$PAGE->set_heading($course->fullname . ': ' . $strinscriptions);
$PAGE->set_context(context_system::instance());
$PAGE->set_title($strinscriptions);
echo $OUTPUT->header();
echo html_writer::link(new moodle_url('/local/batches/index.php'),'Back',array('id'=>'back_tp_course'));

$mform = new bulk_batch_enroll_form('',array('batchid'=>$batchid ,'mode'=>$mode));
// -------------- admission lib file  object--------------------------
$admission = cobalt_admission::get_instance();

if ($mform->is_cancelled()) {
    redirect(new moodle_url('/local/batches/index.php'));
} else
if ($data = $mform->get_data(false)) { // no magic quotes
    //echo $OUTPUT->heading($strinscriptions);    

    $iid = csv_import_reader::get_new_iid('uploaduser');
    $cir = new csv_import_reader($iid, 'uploaduser');

   // print_object($cir);
    $content = $mform->get_file_content('attachment');
    
   // print_object($content);

    $readcount = $cir->load_csv_content($content, $data->encoding, $data->delimiter_name);

   // print_object($readcount);
    unset($content);

    if ($readcount === false) {
        print_error('csvloaderror', '', $returnurl);
    } else if ($readcount == 0) {
        print_error('csvemptyfile', 'error', $returnurl);
    }
    
    
    //local_batches_upload_preview($cir);
    if($data->enroll)
     $result = bulk_batch_enroll_existingstudents($cir,  $data);
    else
     $result = bulk_batch_enroll_newstudents($cir,  $data);

    
    $cir->close();
    $cir->cleanup(false); // only currently uploaded CSV file 

    if ($data->mailreport) {
        $a = new StdClass();
        //$a->course = $course->fullname;
        $a->report = $result;
            $url = $CFG->wwwroot;
  //  $users = $details->email;
    $from = $USER->email;
//    $subject = get_string('approval_of_admission', 'local_admission');
//    if ($previous == 1) {
//        $body = 'Congratulations!Your application for ' . get_string('schoolid', 'local_collegestructure') . ' ' . $schoolname . ' under ' . get_string('program', 'local_programs') . ' ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' username:' . $username . ' and password:' . $password . '
//your serviceid is ' . $s . ' ';
//    } else {
//        $body = 'Congratulations!Your application for ' . get_string('schoolid', 'local_collegestructure') . ' ' . $schoolname . ' under ' . get_string('program', 'local_programs') . ' ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' You can login with previous login details
//your serviceid is ' . $s . ' ';
//    }
//    mail($users, $subject, $body, $from);
//        
//        email_to_user($USER, $USER, get_string('mail_enrolment_subject', 'local_mass_enroll', $CFG->wwwroot),
//        get_string('mail_enrolment', 'local_mass_enroll', $a));
//        $result .= "\n" . get_string('email_sent', 'local_mass_enroll', $USER->email);
    }

    echo $OUTPUT->box(nl2br($result), 'center');

    echo $OUTPUT->continue_button(new moodle_url('/local/batches')); // Back to course page
    echo $OUTPUT->footer();
    die();
}
echo $OUTPUT->heading_with_help($strinscriptions, 'mass_enroll', 'local_batches');
echo html_writer::link(new moodle_url('/local/batches/sample.php',array('format'=>'csv')),'Sample',array('id'=>'back_tp_course'));
//echo $OUTPUT->box (get_string('mass_enroll_info', 'local_mass_enroll'), 'center');
echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';
$mform->display();
echo $OUTPUT->footer();