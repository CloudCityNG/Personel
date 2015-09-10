<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE;
require_once($CFG->dirroot . '/local/admission/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('manage', 'local_admission'));
require_login();
$PAGE->set_url('/local/admission/transferreject.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/transferapplicant.php'));
$PAGE->navbar->add(get_string('rejectapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$admision = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('rejectapplicants', 'local_admission'));
}
$returnurl = new moodle_url('/local/admission/transferapplicant.php');
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $status = 2;
        $DB->set_field('local_admission', 'status', $status, array('id' => $id));
        $details = $DB->get_record('local_admission', array('id' => $id));
        $user = $details->email;
        $from = $USER->email;
        $subject = 'Rejection of admission Application';
        $body = 'Dear ' . $details->firstname . ' ' . $details->lastname . '
		       sorry your application rejected.';
        mail($user, $subject, $body, $from);
        redirect($returnurl);
    }
    $yesurl = new moodle_url('/local/admission/transferreject.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('rejects', 'local_admission');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
}
echo $OUTPUT->footer();
?>