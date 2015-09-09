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
require_once($CFG->dirroot . '/local/adddrop/lib.php');
global $CFG, $USER;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$approve = optional_param('approve', 0, PARAM_INT);
$reject = optional_param('unapprove', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT);
//$current=optional_param('current',0,PARAM_TEXT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/adddrop/mentor.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageadddropapproval', 'local_adddrop'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageadddropapproval', 'local_adddrop'));
//print_adddroptabs($current,'mentor');
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowcourseregistration', 'local_adddrop'));
}
if (!empty($approve) && !empty($classid)) {
    mentorapprovaladddrop($userid, $classid);
}
if (!empty($reject) && !empty($classid)) {
    mentorrejectdropcourse($userid, $classid);
}
$sql = "SELECT ca.* ,u.id as userids,
				 u.username,
				 u.firstname,
				 u.lastname,
				 c.fullname,
				 c.shortname,lc.fullname as coursename
				 FROM 
				      {local_assignmentor_tostudent} m JOIN {user} u ON m.studentid=u.id
					  JOIN {local_course_adddrop} ca ON ca.userid=u.id 
					  JOIN {local_clclasses} c ON c.id=ca.classid
					  JOIN {local_cobaltcourses} lc ON c.cobaltcourseid=lc.id";
//
//if($current=="pending")
//    $sql .=" where m.mentorid={$USER->id} AND ca.mentorapproval=0 ";
//if($current=="completed")
//    $sql .=" where m.mentorid={$USER->id} AND ca.mentorapproval IN (1,2)";
$tools = $DB->get_recordset_sql($sql);
$data = array();
//print_object($tools);
foreach ($tools as $tool) {
    $line = array();
    $buttons = array();
    $mentorStatus = $tool->mentorapproval;
    $approveStatus = $tool->studentapproval;
    $registratStatus = $tool->registrarapproval;
    //echo '</br>';
    $username = get_userdetails($tool->userid);
    // $line[]=$username;
    $line[] = html_writer::tag('a', $username, array('href' => '' . $CFG->wwwroot . '/local/mentor/student.php?id=' . $tool->userid . '', 'title' => get_string('view_academicdetail', 'local_courseregistration')));

    $line[] = '<a  href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $tool->classid . '">' . $tool->shortname . '</a>';
    $line[] = $tool->coursename;
    /*
     * ###Bug #160-Add and drop -mentor side
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * (Resolved) added suitable codition to get proper message(changes done in js also).
     */

    if ($approveStatus == 1)
        $string = get_string('droping', 'local_adddrop');
    else
        $string = get_string('adding', 'local_adddrop');

    if ($registratStatus == 1) {
        //  $string=get_string('droping','local_adddrop');
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
        //$line[]=$statusString[0];
        $line[] = $statusString[1];
        $line[] = get_string('approved', 'local_adddrop');
        $line[] = get_string('approved', 'local_adddrop');
    }
    if ($registratStatus == 2) {
        // $string=get_string('adding','local_adddrop');
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
        //$line[]=$statusString[0];
        $line[] = $statusString[1];
        $line[] = get_string('rejected', 'local_adddrop');
        $line[] = get_string('rejected', 'local_adddrop');
    }
    //($mentorStatus==0 ) && $current=="pending"
    if ($registratStatus == 0) {
        // checking, is student requesting for adding course or dropping course   
        if ($approveStatus == 1)
            $actionstring = get_string('dropp', 'local_adddrop');
        else
            $actionstring = get_string('addd', 'local_adddrop');

        if ($mentorStatus == 0) {

            $buttons[] = html_writer::link(new moodle_url('/local/adddrop/mentor.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'unapprove' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_courseregistration'), 'alt' => get_string('reject', 'local_courseregistration'), 'class' => 'iconsmall')));
            $buttons[] = '&nbsp;';
            $buttons[] = html_writer::link(new moodle_url('/local/adddrop/mentor.php', array('userid' => $tool->userid, 'classid' => $tool->classid, 'page' => $page, 'approve' => 1, 'status' => $approveStatus = $tool->studentapproval, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_courseregistration'), 'alt' => get_string('approve', 'local_courseregistration'), 'class' => 'iconsmall')));

            $line[] = get_string('mentorpending_wr', 'local_adddrop', $actionstring);
            $line[] = implode(' ', $buttons);
        } elseif ($mentorStatus == 1) {
            $line[] = get_string('mentorapproved_wr', 'local_adddrop', $actionstring);
            $line[] = get_string('pending', 'local_request');
        } else {
            $line[] = get_string('mentorrejected_wr', 'local_adddrop', $actionstring);
            $line[] = get_string('pending', 'local_request');
        }
        $line[] = get_string('pending', 'local_request');
    }


    $data[] = $line;
}
  
/*
 * ###Enhancement #187-Course Approvals
 * @author Naveen Kumar<naveen@eabyas.in>
 * (Resolved) Added jQuery data table filters
 */
$PAGE->requires->js('/local/adddrop/js/adddrop_mentor.js');
echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
$table = new html_table();
$table->head = array(
    get_string('studentname', 'local_courseregistration'), 
    get_string('code', 'local_clclasses'), 
    get_string('coursename', 'local_courseregistration'),
    get_string('approvalstatus', 'local_adddrop'),
    get_string('status', 'local_courseregistration'));
$table->id = 'adddrop_mentor';
$table->size = array('20%', '20%', '20%', '25%', '15%');
//if($current=="pending"){
$table->head[] = get_string('status', 'local_courseregistration');
$table->size[] = array('12%');
//}
$table->align = array('center', 'center', 'center', 'center', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
