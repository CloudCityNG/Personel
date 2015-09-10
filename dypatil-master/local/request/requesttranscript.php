<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Version details.
 *
 * @package    local
 * @subpackage requsets(idcard)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/requesttranscript_form.php');
require_once('../../local/request/lib/lib.php');
require_once('../../message/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/formslib.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/requesttranscript.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {
    print_error('You dont have permissions');
}
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('requesttranscript', 'local_request');
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('newrequest', 'local_request'));
$PAGE->set_title($strheading);

$mform = new requesttranscript_form();
$hierarchy = new hierarchy();
$nexturl = $CFG->wwwroot . '/local/request/request_transcript.php';
//Form processing and displaying is done here
$x = optional_param('schoolid', 0, PARAM_INT);
$requestid = new requests();
global $USER;

if ($mform->is_cancelled()) {
    /* if click on cancel it redirects us to request_id.php file */
    redirect($nexturl);
} else if ($fromform = $mform->get_data()) {
    /* presence of data */
    $check = $DB->get_record_sql("SELECT * FROM {local_request_transcript} WHERE studentid = {$USER->id} AND req_semester = {$fromform->semester_name}");
    $schools = $requestid->school();
    foreach ($schools as $school) {
        $value = $school->fullname;
        $key = $school->id;
    }
    $programs = $requestid->program($key);
    foreach ($programs as $pro) {
        $pro_val = $pro->fullname;
        $pro_key = $pro->id;
    }
    $cur_semester = $requestid->current_sem($key, $pro_key);
    foreach ($cur_semester as $cus_ses) {
        $sesid = $cus_ses->id;
        $fromform->semester = $sesid;
    }
    $fromform->studentid = $USER->id;
    $fromform->notification = '0';
    $fromform->requested_date = time();
    $fromform->req_semester = $fromform->semester_name;
    $fromform->reason = $fromform->reason['text'];

    $registrar = $DB->get_records_sql("select userid from {local_school_permissions} where roleid = 9 and schoolid = {$key} group by userid");

    foreach ($registrar as $reg) {
        $registrarid = $reg->userid;
    }
    $userto = $DB->get_record('user', array('id' => $registrarid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $message = get_string('reqtranscriptsmsg', 'local_request');
    if (!empty($check)) {
        $sem = $DB->get_field('local_semester', 'fullname', array('id' => $fromform->semester_name));
        $message = get_string('requesttranscriptrepeat', 'local_request', $sem);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $nexturl, $options);
    } else {
        if ($DB->insert_record('local_request_transcript', $fromform)) {
            message_post_message($userfrom, $userto, $message, FORMAT_HTML);
            $conmessage = get_string('requestsubmit', 'local_request');
            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation($conmessage, $nexturl, $options);
        }
    }
} else {
    
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$currenttab = 'request';
$requestid->requesttranscripttabview($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('transdesc', 'local_request'));
}
$mform->display();
echo $OUTPUT->footer();
?>