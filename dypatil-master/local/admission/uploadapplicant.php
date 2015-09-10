<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/uploadapplicant.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('uploadapplicant_title', 'local_admission'));
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//if (!has_capability('local/programs:manage', $systemcontext)) {
//    print_error('You dont have permissions');
//}
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('uploadapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$hierarchy = new hierarchy();
$conf = new object();
$admission = cobalt_admission::get_instance();
$mform = new newapplicant_form();
$data = $mform->get_data();
$currenttab = 'uploadapplicant';
$admission->report_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('uploadapplicantdes', 'local_admission'));
}
$mform->display();

if ($data) {

    if ($data->same == 1) {
        $data->pcountry = $data->currentcountry;
        $data->permanenthno = $data->currenthno;
        $data->state = $data->region;
        $data->city = $data->town;
        $data->pincode = $data->pob;
        $data->contactname = $data->fathername;
    }
    $data->schoolid = $DB->get_field('local_program', 'schoolid', array('id' => $data->programid));
    // -------------------------------upload file code---------------------------------------------
         $filemanageroptions=   array(
             'maxfiles' =>2,        
            'subdirs' => 0,
            'accepted_types' => '*'
        );
     global $COURSE;
    $context = context_course::instance($COURSE->id);
    $draftitemid = file_get_submitted_draft_itemid('uploadfile');
    $data->uploadfile = $draftitemid;  
    $applicant = $DB->insert_record('local_admission', $data);   
    file_save_draft_area_files($draftitemid, $context->id, 'local_admission', "applicantfile_$applicant",
                                         $applicant );
    
    // ---------------- end of uploading file---------------------------
    
    $update = new Stdclass();
    $update->id = $applicant;
    $program = $DB->get_field('local_program', 'shortname', array('id' => $data->programid));
    $random = random_string(5);
    $update->applicationid = $program . $applicant . $random;
    $applicationid = $DB->update_record('local_admission', $update);    
    $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
    $programname = $DB->get_field('local_program', 'fullname', array('id' => $data->programid));

    $users = $data->email;
    $from = $USER->email;
    $subject = 'Application submission confirmation';
    $body = 'You have applied successfully for "' . $programname . '" program under "' . $schoolname . '". Please wait untill Registrar Office confirms it. 
Application Id : ' . $update->applicationid . ' 
You can track your application status by using this link : ' . $CFG->wwwroot . '';
    mail($users, $subject, $body, $from);
    $conf->success = $program . $applicant . $random;
    $conf->program = $programname;
    $returnurl = new moodle_url('/local/admission/viewapplicant.php');
    $message = get_string('success', 'local_admission', $conf);
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->footer();
?>
