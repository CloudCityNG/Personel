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
 * @subpackage Add/Drop Courses
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/adddrop/lib.php');
global $CFG;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$approve = optional_param('approve', 0, PARAM_INT);
$reject = optional_param('unapprove', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT);
//$current=optional_param('current',0,PARAM_TEXT);
$status = optional_param('status', 0, PARAM_TEXT);
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/adddrop/registrar.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageadddrop', 'local_adddrop'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageadddrop', 'local_adddrop'));
//print_adddroptabs($current,'registrar');

if (!empty($approve) && !empty($classid)) {
    registrarapprovedropcourse($userid, $classid, $status);
}
if (!empty($reject) && !empty($classid)) {
    registrarrejectdropcourse($userid, $classid, $status);
}
/* Bug report #301  -  Registrar>Course Registration- Not assigned to school
 * @author hemalatha c arun <hemalatha@eabyas.in>
 * Resolved- fetching assigned school only  and displaying only his school members 
 */
$hierarchy = new hierarchy();

$assigned_schools = $hierarchy->get_school_items();
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    //if ($current=="pending")
    echo $OUTPUT->box(get_string('pending', 'local_adddrop'));
    //else
    //echo $OUTPUT->box(get_string('completed', 'local_adddrop'));
}
foreach ($assigned_schools as $assignedschool) {
    $asarray[] = $assignedschool->id;
}
$assignedschoolstring = implode(',', $asarray);

$sql = "SELECT ca.*,c.fullname,c.shortname,c.id as classid,lc.fullname as coursename 
        FROM {local_course_adddrop} ca JOIN {local_clclasses} c ON c.id=ca.classid
        JOIN {local_cobaltcourses} lc ON c.cobaltcourseid=lc.id 
        where lc.schoolid in ($assignedschoolstring)";

//if($current=="pending")
// $sql .=" WHERE ca.registrarapproval=0 ";
//if($current=="completed")
//   $sql .=" WHERE ca.registrarapproval IN(1,2) ";
$tools = $DB->get_records_sql($sql);
//print_object($tools);
$data = array();
foreach ($tools as $tool) {
    $line = array();
    $buttons = array();
    $mentorStatus = $tool->mentorapproval;
    $approveStatus = $tool->studentapproval;
    $registratStatus = $tool->registrarapproval;
    $username = get_userdetails($tool->userid);
    $line[] = $username;
    $line[] = '<a  href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $tool->classid . '">' . $tool->shortname . '</a>';
    $line[] = $tool->coursename;
    if ($approveStatus == 1)
        $string = get_string('droping', 'local_adddrop');
    else
        $string = get_string('adding', 'local_adddrop');

    if ($registratStatus == 1) {       
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);        
        $line[] = $statusString[1];
        $line[] = get_string('approved', 'local_adddrop');
        // edited by hema   
        $line[] = get_string('approved', 'local_adddrop');
    }
    if ($registratStatus == 2) {       
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string); 
        $line[] = $statusString[1];
        $line[] = get_string('rejected', 'local_adddrop');
        $line[] = get_string('rejected', 'local_adddrop');
    }
    if ($registratStatus == 0) {
        $buttons[] = html_writer::link(new moodle_url('/local/adddrop/registrar.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'approve' => 1, 'current' => 'completed', 'status' => $approveStatus = $tool->studentapproval, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_courseregistration'), 'alt' => get_string('approve', 'local_courseregistration'), 'class' => 'iconsmall')));
        $buttons[] = '&nbsp;';
        $buttons[] = html_writer::link(new moodle_url('/local/adddrop/registrar.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'current' => 'completed', 'unapprove' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_courseregistration'), 'alt' => get_string('reject', 'local_courseregistration'), 'class' => 'iconsmall')));
        // checking for mentor status when registrar status is pending
        // edited by hemalatha c arun
        // checking, is student requesting for adding course or dropping course   
        if ($approveStatus == 1)
            $actionstring = get_string('dropp', 'local_adddrop');
        else
            $actionstring = get_string('addd', 'local_adddrop');

        if ($mentorStatus == 0)
            $line[] = get_string('mentorpending_wr', 'local_adddrop', $actionstring);
        elseif ($mentorStatus == 1)
            $line[] = get_string('mentorapproved_wr', 'local_adddrop', $actionstring);
        else
            $line[] = get_string('mentorrejected_wr', 'local_adddrop', $actionstring);

        $line[] = implode(' ', $buttons);
        $line[] = get_string('pending', 'local_request');
    }
    $data[] = $line;
}

$PAGE->requires->js('/local/adddrop/js/pending.js');
echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
$table = new html_table();
$table->id = "pending";
$table->head = array(
    get_string('studentname', 'local_courseregistration'),
    get_string('code', 'local_clclasses'),
    get_string('coursename', 'local_courseregistration'),
    get_string('approvalstatus', 'local_adddrop'),
    get_string('status', 'local_courseregistration'),
    get_string('status', 'local_courseregistration'));
$table->size = array('15%', '20%', '20%', '25%', '25%');
$table->align = array('center', 'center', 'center', 'center', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
