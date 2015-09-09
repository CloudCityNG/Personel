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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/myacademics/lib.php');

global $CFG, $USER, $DB;

$systemcontext = context_system::instance();
$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semesterid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/courseregistration/approvestatus.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('myplan', 'local_courseregistration'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('courseapprovalstatus', 'local_courseregistration'));

// Moodle 2.2 and onwards
$currenttab = 'myapprovalstatus';
//adding tabs
print_studenttabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('myclasstabdes', 'local_courseregistration'));
}
$semester = student_semesters($USER->id, 'courseregistration');


if (empty($semester))
    print_cobalterror('nosemester_error', 'local_courseregistration');
echo '<div class="selfilterposition" style="text-align:center;margin:20px;">';
$select = new single_select(new moodle_url('/local/courseregistration/approvestatus.php'), 'semesterid', $semester, $semid, null);
$select->set_label(get_string('semestertranscript', 'local_semesters'));
echo $OUTPUT->render($select);
echo '</div>';

$today = date('Y-m-d');
if ($semid) {
    $query = "SELECT lc.*,cc.id AS courseid,
                     cc.fullname AS coursename,
                     c.registrarapproval AS rapproval,
                     c.mentorapproval AS mapproval,
                     c.studentapproval AS sapproval,
                     cc.credithours AS credithours,
                      (select Max(concat(FROM_UNIXTIME(lsc.startdate, '%d-%m-%Y'),'&nbsp; - &nbsp;',FROM_UNIXTIME(lsc.enddate, '%d-%b-%Y'))) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduledate,
                      (select Max(concat(lsc.starttime,'&nbsp;-&nbsp;',lsc.endtime)) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduletime

                FROM {local_user_clclasses} c JOIN {local_clclasses} lc ON c.classid=lc.id 
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid where c.userid={$USER->id} AND c.semesterid={$semid}";

    $classList = $DB->get_records_sql($query);
    $data = array();
    $credit = 0;

    if ($classList) {
        foreach ($classList as $list) {
            $mentorStatus = $list->mapproval;
            $approveStatus = $list->sapproval;
            $registratStatus = $list->rapproval;
            $line = array();
            $linkcss = $list->visible ? ' ' : 'class="dimmed" ';
            $line[] = $list->coursename;
            $line[] = $list->fullname;
            $instructor[] = get_classinst($list->id);
            $line[] = implode(' ', $instructor[0]);
//            if ($list->scheduledate) {
//                $line[] = $list->scheduledate;
//                $line[] = $list->scheduletime;
//            } else {
//                $line[] = get_string('not_scheduled', 'local_courseregistration');
//                $line[] = get_string('not_scheduled', 'local_courseregistration');
//            }
            $line[] = $list->credithours;
            if ($approveStatus == 1 AND $mentorStatus == 0 AND $registratStatus == 0) {
                $line[] = get_string('waitmentorapproval', 'local_courseregistration');
                $line[] = html_writer::link(new moodle_url('/local/courseregistration/registration.php', array('id' => $list->id, 'semid' => $list->semesterid, 'courseid' => $cid, 'addenroll' => -1, 'page' => $page, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('unenrollme', 'local_courseregistration'), 'alt' => get_string('enrollme', 'local_courseregistration'), 'class' => 'iconsmall')));
            }
            if ($approveStatus == 1 AND $mentorStatus == 0 AND $registratStatus == 1) {
                $line[] = get_string('registrarapproved_withoutmentor', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }
            if ($approveStatus == 1 AND $mentorStatus == 0 AND $registratStatus == 2) {
                $line[] = get_string('registrarrejected_withoutmentor', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }
            if ($approveStatus == 1 AND $mentorStatus == 1 AND $registratStatus == 0) {
                $line[] = get_string('mentorapproved_withoutregistrarapproval', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }
            if ($approveStatus == 1 AND $mentorStatus == 1 AND $registratStatus == 1) {
                $line[] = get_string('registrarmentor_approved', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }

            if ($approveStatus == 1 AND $mentorStatus == 1 AND $registratStatus == 2) {
                $line[] = get_string('mentorapproved_registrarrejected', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }

            if ($approveStatus == 1 AND $mentorStatus == 2 AND $registratStatus == 0) {
                $line[] = get_string('mentorrejected_withoutregistrarapproval', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }

            if ($approveStatus == 1 AND $mentorStatus == 2 AND $registratStatus == 2) {
                $line[] = get_string('mentorregistrar_rejected', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }

            if ($approveStatus == 1 AND $mentorStatus == 2 AND $registratStatus == 1) {
                $line[] = get_string('registraraprroved_mentorrejection', 'local_courseregistration');
                $line[] = get_string('un-enroll', 'local_courseregistration');
            }
            $creditSum = $credit + $list->credithours;
            $credit = $creditSum;
            $data[] = $line;
        }
    } else {
        $line[] = get_string('no_records', 'local_request');
        $data[] = $line;
    }


//View Part starts
//start the table
    $table = new html_table();
    $table->head = array(
        get_string('coursename', 'local_courseregistration'),
        get_string('code', 'local_clclasses'),
        get_string('instructor', 'local_courseregistration'),
//        get_string('date', 'local_courseregistration'),
//        get_string('timing', 'local_courseregistration'), 
        get_string('credithours', 'local_clclasses'),
        get_string('status', 'local_courseregistration'), 
        get_string('unenroll', 'local_courseregistration'));
    $table->size = array('15%', '10%', '10%', '10%', '20%', '10%');
    $table->align = array('left', 'center', 'center', 'center', 'center', 'center');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
