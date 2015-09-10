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
 * @subpackage approval(idcard)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/lib/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$flag = optional_param('flag', 0, PARAM_INT);
$transfer = optional_param('transfer', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/approval/view.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
require_login();
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('details', 'local_approval');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$data = array();
global $USER, $PAGE, $time;
$approveid = new requests();
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$table = new html_table();
$table->align = array('left', 'left');
$table->size = array('30%', '70%');
$table->width = '100%';
if (!$flag && !$transfer) {
    $details = $DB->get_record('local_request_idcard', array('id' => $id));
    $school = $DB->get_field('local_school', 'fullname', array('id' => $details->school_id));
    $program = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
    $semester = $DB->get_field('local_semester', 'fullname', array('id' => $details->semesterid));
    $serviceid = $DB->get_record_sql("SELECT serviceid FROM {local_userdata} where schoolid={$details->school_id} and userid={$details->studentid} group by userid ");
    $name = $DB->get_record_sql("SELECT CONCAT(firstname,' ',lastname) as fullname from {user} where id = {$details->studentid}");
    echo "<div style='float:right'>" . $OUTPUT->single_button(new moodle_url('approval_id.php', array('rejected' => 2, 'id' => $id)), get_string('reject', 'local_approval')) . "</div>";
    echo "<div style='float:right'>" . $OUTPUT->single_button(new moodle_url('approval_id.php', array('approved' => 1, 'id' => $id)), get_string('approve', 'local_approval')) . "</div>";
} if ($flag) {
    $details = $DB->get_record('local_request_profile_change', array('id' => $id));
    $school = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
    $program = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
    $semester = $DB->get_field('local_semester', 'fullname', array('id' => $details->semesterid));
    $serviceid = $DB->get_record_sql("SELECT serviceid FROM {local_userdata} where schoolid={$details->schoolid} and userid={$details->studentid} group by userid ");
    $name = $DB->get_record_sql("SELECT CONCAT(firstname,' ',lastname) as fullname from {user} where id = {$details->studentid}");
}

$table->data[] = array('<b>' . get_string('student_id', 'local_request') . '</b>', $serviceid->serviceid);
if ($transfer) {
    $table->data[] = array('<b>' . get_string('name', 'local_approval') . '</b>', $name->fullname);
}
if (!$flag && !$transfer) {
    $table->data[] = array('<b>' . get_string('name', 'local_approval') . '</b>', $name->fullname);
}
if ($flag) {
    if ($details->subjectcode == 1) {
        $table->data[] = array('<b>' . get_string('subject', 'local_approval') . '</b>', 'Name');
    }if ($details->subjectcode == 2) {
        $table->data[] = array('<b>' . get_string('subject', 'local_approval') . '</b>', 'Email');
    }
    $table->data[] = array('<b>' . get_string('present_data', 'local_approval') . '</b>', $details->presentdata);
    $table->data[] = array('<b>' . get_string('request_to_chage', 'local_approval') . '</b>', $details->changeto);
}
$table->data[] = array('<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', $school);

if ($transfer) {
    $table->data[] = array('<b>' . get_string('presentdata', 'local_approval') . '</b>', $program);
    $table->data[] = array('<b>' . get_string('tochange', 'local_approval') . '</b>', $toprogram);
} else {
    $table->data[] = array('<b>' . get_string('program', 'local_programs') . '</b>', $program);
    $table->data[] = array('<b>' . get_string('semester', 'local_semesters') . '</b>', $semester);
}

$table->data[] = array('<b>Requested Date</b>', date("Y-m-d", $details->requested_date));
$table->data[] = array('<b>Reason</b>', $details->reason);
echo html_writer::table($table);
if (!$flag && !$transfer) {
    echo $OUTPUT->single_button(new moodle_url('approval_id.php'), get_string('back', 'local_approval'));
}if ($flag) {
    echo $OUTPUT->single_button(new moodle_url('approval_profile.php'), get_string('back', 'local_approval'));
}if ($transfer) {
    echo $OUTPUT->single_button(new moodle_url('approval_transfer.php'), get_string('back', 'local_approval'));
}
echo $OUTPUT->footer();
?>