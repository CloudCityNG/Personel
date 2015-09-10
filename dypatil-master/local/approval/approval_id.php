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
require_once('../../local/approval/lib.php');
require_once('../../message/lib.php');
$accept = optional_param('approved', 0, PARAM_INT);
$reject = optional_param('rejected', 0, PARAM_INT);
$tabval = optional_param('mode', 'pending', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/approval/approval_id.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
require_login();
if (!is_siteadmin() && !has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$strheading = get_string('approval_id', 'local_approval');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$data = array();
global $USER, $PAGE, $time;

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
if ($accept) {
    /* --- records with the status approved --- */
    $student = $DB->get_record('local_request_idcard', array('id' => $id));
    $userto = $DB->get_record('user', array('id' => $student->studentid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $time = time();
    $DB->set_field('local_request_idcard', 'reg_approval', 1, array('id' => $id));
    $DB->set_field('local_request_idcard', 'regapproved_date', $time, array('id' => $id));
    $message = get_string('acceptmsg', 'local_approval');
    $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
}
if ($reject) {
    $student = $DB->get_record('local_request_idcard', array('id' => $id));
    $userto = $DB->get_record('user', array('id' => $student->studentid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    /* --- records with the status rejected --- */
    $DB->set_field('local_request_idcard', 'reg_approval', 2, array('id' => $id));
    $DB->set_field('local_request_idcard', 'regapproved_date', $time, array('id' => $id));
    $message = get_string('rejectmsg', 'local_approval');
    $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
}
$sql = "SELECT lri.* FROM {local_request_idcard} lri 
					 INNER JOIN {local_school_permissions} lsp 
					 ON lri.school_id = lsp.schoolid and lsp.userid = {$USER->id} 
					 and lsp.roleid = 9 ";

if ($tabval == 'pending') {
    $sql .= ' and lri.reg_approval=0';
}
if ($tabval == 'approved') {
    $sql .= ' and lri.reg_approval=1';
}
if ($tabval == 'rejected') {
    $sql .= ' and lri.reg_approval=2';
}
$sql .= ' group by lsp.schoolid';
$student = $DB->get_records_sql($sql);

foreach ($student as $students) {
    $list = array();
    $stidentid = $DB->get_record_sql("SELECT lud.serviceid FROM {local_request_idcard} lr
				 INNER JOIN {local_userdata} lud ON lud.userid = lr.studentid
				 and lr.studentid={$students->studentid} group by lud.userid");
    $list[] = $stidentid->serviceid;
    $username = $DB->get_record_sql("select CONCAT(firstname,' ',lastname) as fullname from {user} where id={$students->studentid}");
    $list[] = "<a href='view.php?id=" . $students->id . "'>" . $username->fullname . "</a>";
    $sql_school = $DB->get_record_sql("select fullname from {local_school} where id={$students->school_id} group by id");
    $list[] = $sql_school->fullname;
    $sql_pro = $DB->get_record_sql("select fullname from {local_program} where id=$students->programid");
    $list[] = $sql_pro->fullname;
    $list[] = date("Y-m-d", $students->requested_date);
    if ($students->reg_approval == 0) {

        $list[] = html_writer::link(new moodle_url('/local/approval/approval_id.php', array('approved' => 1, 'id' => $students->id)), get_string('approve', 'local_approval')) . "<span> / </span>" . html_writer::link(new moodle_url('/local/approval/approval_id.php', array('rejected' => 2, 'id' => $students->id)), get_string('reject', 'local_approval'));
    }if ($students->reg_approval == 1) {

        $list[] = get_string('approved', 'local_approval');
    }if ($students->reg_approval == 2) {

        $list[] = get_string('rejected', 'local_approval');
    }

    $data[] = $list;
}
statusview($tabval);
if ($tabval) {
    if ($tabval == 'pending') {
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('pendingdes', 'local_approval'));
        }
    }
    if ($tabval == 'approved') {
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('acceptdes', 'local_approval'));
        }
    }
    if ($tabval == 'rejected') {
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('rejectdis', 'local_approval'));
        }
    }
    echo "<br>";
    echo $OUTPUT->box_start('generalbox');
    if (!empty($data)) {
        $PAGE->requires->js('/local/approval/approvidjs.js');
        echo "<div id='filter-box' >";
        echo '<div class="filterarea"></div></div>';
    }
    $table = new html_table();
    $table->id = "approvalidtable";
    $table->head = array(
        get_string('studentid', 'local_approval'),
        get_string('name', 'local_approval'),
        get_string('schoolid', 'local_collegestructure'),
        get_string('program', 'local_programs'),
        get_string('requesteddate', 'local_approval'),
        get_string('status', 'local_approval')
    );
    $table->size = array('15%', '15%', '20%', '15%', '15%', '15%');
    $table->align = array('center', 'center', 'center', 'center');
    $table->width = '100%';
    $table->data = $data;
    echo html_writer::table($table);
    if (empty($data))
        echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
?>
