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
 * @package    local
 * @subpackage Pramod
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/graduation/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $DB;
$curid = optional_param('curid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
//$x = local_graduation_cron(); exit;
$systemcontext = context_system::instance();
// get the admin layout
$PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//if (!has_capability('local/lecturetype:manage', $systemcontext)) {
//    print_error('You dont have permissions');
//}
$PAGE->set_url('/local/graduation/memo.php');
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('memo', 'local_graduation');
$PAGE->set_title($string);
$PAGE->navbar->add(get_string('memo', 'local_graduation'));
//$PAGE->navbar->add(get_string('view', 'local_graduation'));
$strheading = get_string('memo', 'local_graduation');
// echo $OUTPUT->header();
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
// Heading of the page

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('memodesc', 'local_semesters'));
}

$stu_semsql = "SELECT ls.id,ls.fullname,us.userid
               FROM {local_user_semester} AS us
               JOIN {local_semester} AS ls
               ON us.semesterid=ls.id where ls.visible=1 and registrarapproval=1 and us.userid={$userid} group by ls.id";

$student_sems = $DB->get_records_sql($stu_semsql);
//print_object($student_sems);
foreach ($student_sems as $stusem) {
// echo $stusem->id;
    $semuserid = $stusem->userid;
    $clclasses = student_clclasses_grad($stusem->id, $semuserid);
    // print_object($clclasses); //exit;
    $data = array();
    foreach ($clclasses as $cls) {

        $cocourseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $cls->classid));

        $cur_pro_sql = "SELECT * FROM {local_curriculum_plancourses} planco 
                        JOIN {local_userdata} udat on udat.curriculumid=planco.curriculumid 
                         where planco.curriculumid={$curid} and planco.courseid={$cocourseid} and udat.userid={$semuserid}";

        $course_exist_incur = $DB->get_records_sql($cur_pro_sql);
        //print_object($course_exist_incur);

        if ($course_exist_incur) {

            $line = array();
            $coursename = get_cobalt_course_grad($cls->classid);
            $line[] = $coursename;

            $maxmarks = $DB->get_field('local_scheduledexams', 'sum(grademax) AS grademax', array('classid' => $cls->classid));
            if (empty($maxmarks)) {
                $line[] = get_string('no_exams', 'local_myacademics');
            } else {
                $line[] = $maxmarks;
            }

            $mark = $DB->get_record('local_user_classgrades', array('classid' => $cls->classid, 'userid' => $semuserid));

            if (!empty($mark)) {
                $line[] = $mark->coursetotal;
                $line[] = $mark->percentage . '%';
                $line[] = $mark->gradeletter;
                $wgp = total_grade_points_grad($mark->gradepoint, $mark->classid);
            } else {
                $line[] = empty($maxmarks) ? '-' : get_string('not_graded', 'local_myacademics');
                $line[] = empty($maxmarks) ? '-' : '-';
                $line[] = empty($maxmarks) ? '-' : '-';
                $wgp = empty($maxmarks) ? '-' : 0;
            }
            $line[] = $wgp;
            $data[] = $line;
            //print_r($line);
        }
    }

    $table = new html_table();
    $table->head = array(
        get_string('course', 'local_cobaltcourses'), get_string('maxscore', 'local_graduation'), get_string('score', 'local_gradesubmission'), get_string('percentage', 'local_graduation'), get_string('gradeletter', 'local_gradesubmission'), get_string('wgp', 'local_graduation'));
    $table->size = array('27%', '9%', '10%', '10%', '8%', '8%');
    $table->align = array('left', 'left', 'left', 'left', 'center');
    $table->width = '99%';
    $table->data = $data;

    $sname = $DB->get_field('local_semester', 'fullname', array('id' => $stusem->id));
    $sem_gpa = $DB->get_field('local_user_sem_details', 'gpa', array('userid' => $semuserid, 'semesterid' => $stusem->id));
    if (empty($sem_gpa)) {
        $sem_gpa = get_string('none');
    }

    $stu_status = $DB->get_field('local_user_sem_details', 'studentstatus', array('userid' => $semuserid, 'semesterid' => $stusem->id));

    if ($stu_status == 0) {
        $status = get_string('good_standing', 'local_graduation');
    } elseif ($stu_status == 1) {
        $status = get_string('probation', 'local_graduation');
    } elseif ($stu_status == 2) {
        $status = get_string('academic_dismissal', 'local_graduation');
    } elseif ($stu_status == 99) {
        $status = "-";
    } else {
        $status = "-";
    }

    echo "</br>
                        <table width=100%>
                        <tr>
                        <td align='left'><span><h4><b>" . strtoupper(get_string('semester', 'local_semesters')) . " : </span>" . $sname . "</b></h4></td>
                        <td><span><h4><b>" . get_string('gp', 'local_request') . " : </span>" . $sem_gpa . "</b></h4></td>
                        <td align='right'><span><h4><b>" . strtoupper(get_string('status', 'local_request')) . " : </span>" . $status . "</b></h4></td>
                        </tr>
                        </table>";
    echo html_writer::table($table);
}


echo $OUTPUT->footer();
