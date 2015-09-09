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
require_once('../../local/request/requesttransfer_form.php');
require_once('../../local/request/lib/lib.php');
require_once('../../message/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/formslib.php');
$id = optional_param('id', -1, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/requestid.php');
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}

$strheading = get_string('requisitions_transfer', 'local_request');
$PAGE->set_title($strheading);
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('newrequest', 'local_request'));
$PAGE->set_title($strheading);

$mform = new requesttransfer_form();
$hierarchy = new hierarchy();
$nexturl = $CFG->wwwroot . '/local/request/request_transfer.php';
//Form processing and displaying is done here
$x = optional_param('schoolid', 0, PARAM_INT);
$requestid = new requests();
global $USER;
if ($mform->is_cancelled()) {
    /* if click on cancel it redirects us to request_id.php file */
    redirect($nexturl);
} else if ($fromform = $mform->get_data()) {
    /* presence of data */
    if (isset($fromform->school_name)) {
        $schoolid = $fromform->school_name;
        if (isset($fromform->program_name)) {
            $pro_id = $fromform->program_name;
            $semesters = $requestid->semester($fromform->school_name, $fromform->program_name);
            foreach ($semesters as $sem) {
                $sem->id;
            }
        }
        if (isset($fromform->programid)) {
            $pro_id = $fromform->programid;
            $semesters = $requestid->semester($fromform->school_name, $fromform->programid);
            foreach ($semesters as $sem) {
                $sem->id;
            }
        }
    }
    if (isset($fromform->schoolid)) {
        $schoolid = $fromform->schoolid;
        if (isset($fromform->program_name1)) {
            $pro_id = $fromform->program_name1;
            $semesters = $requestid->semester($fromform->schoolid, $fromform->program_name1);
            foreach ($semesters as $sem) {
                $sem->id;
            }
        }
        if (isset($fromform->programid)) {
            $pro_id = $fromform->programid;
            $semesters = $requestid->semester($fromform->schoolid, $fromform->programid);
            foreach ($semesters as $sem) {
                $sem->id;
            }
        }
    }
    $fromform->schoolid = $schoolid;
    $fromform->semesterid = $sem->id;
    $fromform->programid = $pro_id;
    $fromform->notification = 0;
    $fromform->requested_date = time();
    $fromform->reg_approval = 0;
    $fromform->regapproval_date = 0;
    $registrar = $DB->get_records_sql("select userid from {local_school_permissions} where roleid = 9 and schoolid = {$fromform->schoolid} group by userid");
    foreach ($registrar as $reg) {
        $registrarid = $reg->userid;
    }
    $userto = $DB->get_record('user', array('id' => $registrarid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $message = get_string('usertransferrequest', 'local_request', $userfrom->firstname);
    if ($DB->insert_record('local_request_transfer', $fromform)) {
        message_post_message($userfrom, $userto, $message, FORMAT_HTML);
        $conmessage = get_string('requestsubmit', 'local_request');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($conmessage, $nexturl, $options);
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$check = $DB->get_record_sql("SELECT * FROM {local_request_transfer} WHERE studentid = {$USER->id} AND approvalstatus = 0");
if (!empty($check)) {
    $message = get_string('requesttransfererror', 'local_request');
    $options = array('style' => 'notifyproblem');
    $hierarchy->set_confirmation($message, $nexturl, $options);
}
$currenttab = 'request';
$requestid->requesttransfertabview($currenttab);

$mform->display();
echo $OUTPUT->footer();
?>