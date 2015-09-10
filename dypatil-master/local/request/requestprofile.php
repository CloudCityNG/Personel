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
 * @subpackage requsets(profile_change)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/requestprofile_form.php');
require_once('../../local/request/lib/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/formslib.php');
$id = optional_param('id', -1, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/requestprofile.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('requisitions_profile', 'local_request');
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('newrequest', 'local_request'));
$PAGE->set_title($strheading);
$requestpro = new requests();
$mform = new requestprofile_form();
$hierarchy = new hierarchy();
$nexturl = $CFG->wwwroot . '/local/request/request_profile.php';
$x = optional_param('schoolid', 0, PARAM_INT);
global $USER;
if ($mform->is_cancelled()) {
    /* if click on cancel it redirects us to request_profile.php file */
    redirect($nexturl);
} else if ($fromform = $mform->get_data()) {
    /* presence of data */
    if (isset($fromform->school_name)) {
        $schoolid = $fromform->school_name;
    }
    if (isset($fromform->schoolid)) {
        $schoolid = $fromform->schoolid;
    }
    if (isset($fromform->program_name1)) {
        $program = $fromform->program_name1;
    }
    if (isset($fromform->programid)) {
        $program = $fromform->programid;
    }
    $users = $requestpro->current_user();
    if ($fromform->field_select == 0) {
        $presentdata = $users->firstname . " " . $users->lastname;
        $subject = 1;
    }
    if ($fromform->field_select == 1) {
        $presentdata = $users->email;
        $subject = 2;
    }
    $fromform->programid = $program;
    $fromform->schoolid = $schoolid;
    $fromform->studentid = $USER->id;
    $fromform->subjectcode = $subject;
    $fromform->presentdata = $presentdata;
    $fromform->reason = $fromform->reason['text'];
    $fromform->requested_date = time();
    $registrar = $DB->get_records_sql("select userid from {local_school_permissions} where roleid = 9 and schoolid = {$fromform->schoolid} group by userid");
    foreach ($registrar as $reg) {
        $registrarid = $reg->userid;
    }

    $userto = $DB->get_record('user', array('id' => $registrarid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $message = get_string('reqprofilechnagesmsg', 'local_request');

    if (isset($fromform->changeto) && isset($fromform->reason)) {
        $DB->insert_record('local_request_profile_change', $fromform);
        message_post_message($userfrom, $userto, $message, FORMAT_HTML);
        $conmessage = get_string('requestsubmit', 'local_request');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($conmessage, $nexturl, $options);
    } else {
        redirect($nexturl);
    }
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$currenttab = 'request';
$requestpro->requesttabview($currenttab);
echo $OUTPUT->box(get_string('profilerequestingdes', 'local_request'));
$mform->display();
echo $OUTPUT->footer();
?>