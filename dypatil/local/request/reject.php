<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/lib.php');
$id = optional_param('recid', 0, PARAM_INT);
$tid = optional_param('tid', 0, PARAM_INT);
$reject = optional_param('reject', 0, PARAM_INT);
$PAGE->set_url('/local/request/reject.php');
$PAGE->set_title(get_string('pluginname', 'local_profilechange'));
$PAGE->set_heading(get_string('pluginname', 'local_profilechange'));
$hierarchy = new hierarchy();
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->navbar->add(get_string('myprofile', 'local_profilechange'));
$PAGE->navbar->add(get_string('pluginname', 'local_profilechange'));
echo $OUTPUT->header();
$hierarchy = new hierarchy();
if ($id > 0) {
    $value = $DB->set_field('local_request_profile_change', 'reg_approval', $reject, array('id' => $id));
    $message = get_string('reject_profile_change', 'local_request');
    $nexturl = '../../local/request/approval_profile.php';
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $nexturl, $style);
}if ($tid > 0) {
    $value = $DB->set_field('local_request_transcript', 'reg_approval', $reject, array('id' => $tid));
    $message = get_string('reject_transcript', 'local_request');
    $nexturl = '../../local/request/approval_transcript.php';
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $nexturl, $style);
}echo $OUTPUT->footer();
?>