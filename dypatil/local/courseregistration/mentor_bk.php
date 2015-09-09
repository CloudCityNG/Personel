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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage Modules
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
global $CFG, $USER, $DB;

$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$approve = optional_param('approve', 0, PARAM_INT);
$reject = optional_param('unapprove', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT);
$current = required_param('current', PARAM_TEXT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/mentor.php');

/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managecourseapproval', 'local_courseregistration'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecourseapproval', 'local_courseregistration'));

/* ---Get the records from the database--- */

/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowclassregistration', 'local_courseregistration'));
}
if (!empty($approve) && !empty($classid)) {

    mentorapprovecourse($userid, $classid);
}
if (!empty($reject) && !empty($classid)) {

    mentorrejectcourse($userid, $classid);
}
$currenttab = $current;
print_mentortabs($currenttab);
$sql = "SELECT ca.*,lc.*,
             cc.fullname as coursename,
             lc.id as ids,
             (SELECT s.fullname from {local_semester} s where s.id=lc.semesterid) AS semestername
        FROM {local_assignmentor_tostudent} m JOIN 
             {local_user_clclasses} ca ON ca.userid=m.studentid  JOIN 
             {local_clclasses} lc ON lc.id=ca.classid JOIN 
             {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
             where m.mentorid={$USER->id} and (ca.event IS NULL or ca.event = 'registration') ";

if ($current == "pending")
    $sql .=" AND ca.mentorapproval=0 AND ca.registrarapproval=0 ";
else if ($current == "completed")
    $sql .=" AND ca.mentorapproval IN (1,2) AND ca.registrarapproval IN (0,1,2)";

$tools = $DB->get_recordset_sql($sql);

$data = array();
foreach ($tools as $tool) {
    $line = array();
    $buttons = array();
    $instructor = array();
    $mentorStatus = $tool->mentorapproval;
    $approveStatus = $tool->studentapproval;
    $registratStatus = $tool->registrarapproval;
    $username = get_userdetails($tool->userid);
    $line[] = html_writer::tag('a', $username, array('href' => '' . $CFG->wwwroot . '/local/mentor/student.php?id=' . $tool->userid . '', 'title' => get_string('view_academicdetail', 'local_courseregistration')));
    $line[] = $tool->shortname;
    $line[] = $tool->coursename;
    $instrctor = array();
    $instructor[] = get_classinst($tool->ids);
    $line[] = implode(' ', $instructor[0]);
    $line[] = $tool->semestername;
    if ($mentorStatus == 0 && $registratStatus == 0) {
        $buttons[] = html_writer::link(new moodle_url('/local/courseregistration/mentor.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'current' => $current, 'unapprove' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_courseregistration'), 'alt' => get_string('reject', 'local_courseregistration'), 'class' => 'iconsmall')));
        $buttons[] = html_writer::link(new moodle_url('/local/courseregistration/mentor.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'current' => $current, 'approve' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_courseregistration'), 'alt' => get_string('approve', 'local_courseregistration'), 'class' => 'iconsmall')));
    }
    if ($current == "pending") {
        $line[] = implode(' ', $buttons);
    }
    $line[] = get_courseregistrationstatus($approveStatus, $mentorStatus, $registratStatus);
    $data[] = $line;
}

$PAGE->requires->js('/local/courseregistration/js/coursereg_mentor.js');
/* ---View Part starts--- */
/* ---start the table--- */
$table = new html_table();
//$table->id = "cooktable";
if ($current == "pending") {
    $currentstat = get_string('mentorapprovalhead', 'local_courseregistration');
    $wd = "15%";
} else {
    //  $currentstat="";
    //  $wd="0%";
}
if ($current == "pending") {
    $table->head = array(
        get_string('studentname', 'local_courseregistration')/* ,get_string('clclassesname','local_clclasses') */, get_string('code', 'local_clclasses'), get_string('coursename', 'local_courseregistration'), get_string('instructor', 'local_courseregistration'), get_string('semester', 'local_semesters'), $currentstat, get_string('status', 'local_courseregistration'));
} else {
    $table->head = array(
        get_string('studentname', 'local_courseregistration')/* ,get_string('clclassesname','local_clclasses') */, get_string('code', 'local_clclasses'), get_string('coursename', 'local_courseregistration'), get_string('instructor', 'local_courseregistration'), get_string('semester', 'local_semesters'), get_string('status', 'local_courseregistration'));
}
$table->id = 'coursereg_mentor';
$table->size = array('15%', '11%', '10%', '20%', '16%', '11%', $wd, '17%');
$table->align = array('left', 'left', 'center', 'center', 'center', 'center', 'center', 'center');
$table->border = "1px";
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
