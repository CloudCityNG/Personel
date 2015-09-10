<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('contact_title', 'local_admission'));
require_login();
$PAGE->set_url('/local/admission/contact.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('contactapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$hierarchy = new hierarchy();
$admision = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('contactapplicants', 'local_admission'));
}
$returnurl = new moodle_url('/local/admission/viewapplicant.php');
$mform = new contact_form(null, array('id' => $id));
$data = $mform->get_data();
$mform->display();
if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($data) {
    $user = $DB->get_field('local_admission', 'email', array('id' => $data->id));
    $from = $USER->email;
    $subject = $data->subject;
    $body = $data->message;
    mail($user, $subject, $body, $from);
    $message = get_string('contactsuccess', 'local_admission');
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->footer();
?>
