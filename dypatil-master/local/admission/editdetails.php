<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$schoolid = optional_param('schoid', 0, PARAM_INT);
$programid = optional_param('progid', 0, PARAM_INT);
$curid = optional_param('curid', 0, PARAM_INT);
$id = optional_param('appid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/editdetails.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('edit_title', 'local_admission'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/admissionreport.php'));
$PAGE->navbar->add(get_string('editapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('editapplicant', 'local_admission'));
$hierarchy = new hierarchy();
$admission = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('editdesc', 'local_admission'));
}
if ($schoolid != 0 && $programid != 0 && $curid != 0) {
    $records = new stdclass();
    $records->id = $DB->get_field('local_userdata', 'id', array('applicantid' => $id));
    $records->schoolid = $schoolid;
    $records->programid = $programid;
    $records->curriculumid = $curid;
    $records->usermodified = $USER->id;
    $records->timecreated = time();
    $records->timemodified = time();
    $DB->update_record('local_userdata', $records);
}
$returnurl = new moodle_url('/local/admission/admissionreport.php');
redirect($returnurl);
echo $OUTPUT->footer();
?>