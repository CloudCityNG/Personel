<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once('application_form.php');
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/admission/applicationstatus.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/index.php'));
$PAGE->navbar->add(get_string('viewapplication', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
$mform = new applicationstatus_form();
echo $OUTPUT->heading(get_string('status', 'local_admission'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewstatus', 'local_admission'));
}
$data = $mform->get_data();
$mform->display();
$returnurl = new moodle_url('/local/admission/index.php');
if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($data) {
    $status = $DB->get_field('local_admission', 'status', array('applicationid' => $data->applicationid));
    switch ($status) {
        case 0:
            echo html_writer::tag('div', get_string('waiting_for_approval', 'local_admission'), array('class' => 'statusmessage'));
            break;
        case 1:
            echo html_writer::tag('div', get_string('approved_approval', 'local_admission'), array('class' => 'statusmessage'));
            break;
        case 2:
            echo html_writer::tag('div', get_string('rejected_by_registrar', 'local_admission'), array('class' => 'statusmessage'));
            break;
    }
}
echo $OUTPUT->footer();
