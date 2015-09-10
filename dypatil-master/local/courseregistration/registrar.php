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
 * @subpackage Course Registration
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/adddrop/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
global $CFG;

$systemcontext = context_system::instance();
$facid = optional_param('facid', 0, PARAM_INT);
$proid = optional_param('proid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$approve = optional_param('approve', 0, PARAM_INT);
$batchid = optional_param('batchid', 0, PARAM_INT);
$reject = optional_param('unapprove', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT);
//$current=required_param('current',PARAM_TEXT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/courseregistration/registrar.php');

/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageclassapproval', 'local_courseregistration'), new moodle_url('/local/courseregistration/index.php')); //

echo $OUTPUT->header();
//$currenttab=$current;


/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('manageclassapproval', 'local_courseregistration'));
//print_registrationtabs('courseapprovals');
/* ---Get the records from the database--- */


if (!empty($approve) && !empty($classid)) {

    registrarapprovecourse($userid, $classid);
}
if (!empty($reject) && !empty($classid)) {

    registrarrejectcourse($userid, $classid);
}

/*
 * ###Bugreport #174-Course Approvals
 * @author Naveen Kumar<naveen@eabyas.in>
 * (Resolved) Retreiving assigned schools clclasses only
 */
$hierarchy = new hierarchy();
$assigned_schools = $hierarchy->get_school_items();

/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowclassregistration', 'local_courseregistration'));
}

foreach ($assigned_schools as $assignedschool) {
    $asarray[] = $assignedschool->id;
}
$assignedschoolstring = implode(',', $asarray);
// $compare_scale_clause = $DB->sql_compare_text("event")  . ' != ' . $DB->sql_compare_text(":et");
$query = "SELECT c.id as userclassid,lc.*,
                cc.id AS courseid,
                c.classid AS classid,
                c.userid AS userid,c.event,
                cc.fullname AS coursename,
                c.registrarapproval AS rapproval,
                c.mentorapproval AS mapproval,
                c.studentapproval AS sapproval,(SELECT s.fullname from {local_semester} s where s.id=lc.semesterid) AS semestername
          FROM {local_user_clclasses} c JOIN 
               {local_clclasses} lc ON c.classid=lc.id and c.registrarapproval != 3 JOIN 
               {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid where (c.event IS NULL or c.event = 'registration') and lc.schoolid in ($assignedschoolstring) group by c.id";

$userclclasses = $DB->get_records_sql($query);
//print_object($userclclasses);

$data = array();
foreach ($userclclasses as $userclass) {

    $line = array();
    $buttons = array();
    $instructor = array();
    $mentorStatus = $userclass->mapproval;
    $approveStatus = $userclass->sapproval;
    $registratStatus = $userclass->rapproval;
    $username = get_userdetails($userclass->userid);
    $line[] = $username;
    $line[] = $userclass->shortname;
    $line[] = $userclass->coursename;
    $instructor[] = get_classinst($userclass->id);

    if ($registratStatus == 0) {

        $buttons[] = html_writer::link(new moodle_url('/local/courseregistration/registrar.php', array('userid' => $userclass->userid, 'classid' => $userclass->classid, 'page' => $page, 'current' => 'pending', 'unapprove' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_courseregistration'), 'alt' => get_string('reject', 'local_courseregistration'), 'class' => 'iconsmall')));
        $buttons[] = '&nbsp;';
        $buttons[] = html_writer::link(new moodle_url('/local/courseregistration/registrar.php', array('userid' => $userclass->userid, 'classid' => $userclass->classid, 'page' => $page, 'current' => 'pending', 'approve' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_courseregistration'), 'alt' => get_string('approve', 'local_courseregistration'), 'class' => 'iconsmall')));
        // checking for mentor status when registrar status is pending
        // edited by hemalatha c arun
        // checking, is student requesting for adding course or dropping course   
        if ($approveStatus == 1)
            $actionstring = get_string('addd', 'local_adddrop');
        else
            $actionstring = get_string('dropp', 'local_adddrop');

        if ($mentorStatus == 0)
            $line[] = get_string('mentorpending_wr', 'local_adddrop', $actionstring);
        elseif ($mentorStatus == 1)
            $line[] = get_string('mentorapproved_wr', 'local_adddrop', $actionstring);
        else
            $line[] = get_string('mentorrejected_wr', 'local_adddrop', $actionstring);
        
        $line[] = implode(' ', $buttons);
        $line[] = get_string('pending', 'local_request');
    }else if ($approveStatus == 1 AND $registratStatus == 1) {
        $string = get_string('adding', 'local_adddrop');
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
        $line[] = $statusString[1];
        $line[] = get_string('approved', 'local_adddrop');
        $line[] = get_string('approved', 'local_adddrop');
    } else if ($approveStatus == 1 AND $registratStatus == 5) {
        // if status  is 5 , means student unenrolled by registrar
        $string = get_string('adding', 'local_adddrop');
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
        $line[] = $statusString[1];
        $line[] = get_string('unenolled', 'local_adddrop');
        $line[] = get_string('unenolled', 'local_adddrop');
    }else
    if ($approveStatus == 1 AND $registratStatus == 2) {
        $string = get_string('adding', 'local_adddrop');
        $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
        $line[] = $statusString[1];
        $line[] = get_string('rejected', 'local_courseregistration');
        $line[] = get_string('rejected', 'local_courseregistration');
    }
    $data[] = $line;
}


$PAGE->requires->js('/local/courseregistration/filters/registration.js');
echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
/* ---start the table--- */
$table = new html_table();
$table->id = "classregistration";

if (has_capability('local/courseregistration:manage', $systemcontext))
    $currentstat = get_string('confirm', 'local_courseregistration');
else
    $currentstat = get_string('mentorappr', 'local_courseregistration');
$wd = "15%";

$table->head = array(
    get_string('studentname', 'local_courseregistration'),
    //get_string('clclassesname','local_clclasses'),
    get_string('code', 'local_clclasses'),
    get_string('coursename', 'local_courseregistration'),
    // get_string('instructor','local_courseregistration'),
    // get_string('semester', 'local_semesters'),
    get_string('approvalstatus', 'local_adddrop'),
    get_string('status', 'local_courseregistration'),
    get_string('status', 'local_courseregistration'));
$table->size = array('15%', '15%', '25%', '25%', '10%', '10%');
$table->size[] = $wd;
$table->align = array('left', 'left', 'center', 'center', 'center', 'left', 'left');
$table->border = "1px";
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
