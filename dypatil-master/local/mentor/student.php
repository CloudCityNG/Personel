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
 * Course Enrolment reports
 *
 * @package    local
 * @subpackage mentor
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/mentor/lib.php');

$id = required_param('id', PARAM_INT); //Student id
$mode = optional_param('mode', 'current', PARAM_RAW);
global $CFG;
$mymentor = mentor::getInstance();
$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

//if (!has_capability('local/programs:manage', $systemcontext)) {
//    print_error('You dont have permissions');
//}
$PAGE->set_url('/local/mentor/student.php');
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_mentor'));
$PAGE->navbar->add(get_string('mystudents', 'local_mentor'), new moodle_url('/local/mentor/index.php'));
$PAGE->navbar->add(get_string('viewstudent', 'local_mentor'));


$student = $DB->get_record('user', array('id' => $id));
$localuser = $DB->get_record('local_users', array('userid' => $id));
$data = $DB->get_record('local_assignmentor_tostudent', array('studentid' => $id));
$userdata = $DB->get_record('local_userdata', array('userid' => $id, 'schoolid' => $data->schoolid, 'programid' => $data->programid));

$stuname = fullname($student);
$PAGE->set_title($stuname);
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading($stuname); //Student Name

$table = new html_table();
$table->size = array('10%', '45%', '10%', '35%');
$table->align = array('left', 'left', 'left', 'left');
$table->width = '100%';
$curriculumname = $DB->get_field('local_curriculum', 'fullname', array('id' => $userdata->curriculumid));
$curriculum = html_writer::tag('a', $curriculumname, array('target' => '_blank', 'href' => '' . $CFG->wwwroot . '/local/curriculum/viewcurriculum.php?id=' . $userdata->curriculumid . ''));
$table->data[] = array('<b>' . get_string('studentid', 'local_gradesubmission') . '</b>', $userdata->serviceid, '<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid)));
$table->data[] = array('<b>' . get_string('program', 'local_programs') . '</b>', $DB->get_field('local_program', 'fullname', array('id' => $data->programid)), '<b>' . get_string('curriculum', 'local_curriculum') . '</b>', $curriculum);
echo html_writer::table($table);
//$list = '<br/><b>Semesters</b> : <br/><br/>';
//echo $list;
$mymentor->createtabview($mode, $id);
$semesters = $mymentor->student_semester($id, $data->programid, $mode);

if (!empty($semesters)) {
    foreach ($semesters as $semester) {
        $data = array();
        echo '<font size="3em"><b>' . get_string('semester', 'local_semesters') . ': </b>' . $semester->fullname . '</font>';
        $courses = $mymentor->semester_courses($semester->id, $id);
        foreach ($courses as $course) {
            $line = array();
            $line[] = $course->coursename . ' (' . $course->credithours . ' cr.hrs)';
            $line[] = $course->coursetype ? 'Elective' : 'General';
            $line[] = $course->fullname;
            $line[] = $course->instname;
            if ($mode == 'completed') {
                $grades = $DB->get_record('local_user_classgrades', array('userid' => $id, 'courseid' => $course->cid, 'classid' => $course->id, 'semesterid' => $semester->id));
                $line[] = $grades->gradeletter;
            }
            $data[] = $line;
        }
        $table = new html_table();
        $table->head[] = get_string('course', 'local_cobaltcourses');
        $table->head[] = get_string('coursetype', 'local_cobaltcourses');
        $table->head[] = get_string('clclassesname', 'local_clclasses');
        $table->head[] = get_string('instructor', 'local_mentor');
        if ($mode == 'completed')
            $table->head[] = get_string('grades', 'local_mentor');
        $table->size = array('30%', '15%', '25%', '20%', '10%');
        $table->align = array('left', 'left', 'left', 'left');
        $table->width = '100%';
        $table->data = $data;
        echo html_writer::table($table);
        if ($mode == 'completed') {
            $gpa = $DB->get_record('local_user_sem_details', array('semesterid' => $semester->id, 'userid' => $id));
            echo '<font size="2em" style="float:right;">' . get_string('gp', 'local_request') . ': ';
            if ($gpa)
                echo $gpa->gpa;
            else
                echo '-';
            echo '</font><br/><br/>';
        }
    }
} else {
    echo get_string('no_records', 'local_request');
}
echo $OUTPUT->footer();
