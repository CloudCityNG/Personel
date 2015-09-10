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
 * @subpackage scheduleexam
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
$id = optional_param('id', 0, PARAM_INT);


global $CFG, $out;

$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/scheduleexam/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);

$PAGE->navbar->add(get_string('examdetails', 'local_scheduleexam'));

echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('examdetails', 'local_scheduleexam'));

$exam = $DB->get_record('local_scheduledexams', array('id' => $id));

$sql = "select s.fullname AS schoolname,sem.fullname AS semestername,c.fullname AS classname,
             extyp.examtype AS examtype,lectyp.lecturetype FROM {local_school} s,{local_clclasses} c,{local_semester} sem,{local_examtypes} extyp,{local_lecturetype} lectyp
             where s.id={$exam->schoolid} and c.id={$exam->classid} and sem.id={$exam->semesterid} and extyp.id={$exam->examtype} and lectyp.id={$exam->lecturetype}";

$examdet = $DB->get_record_sql($sql);

$sql = 'SELECT co.fullname FROM {local_clclasses} c 
                  JOIN {local_cobaltcourses} co on c.cobaltcourseid=co.id where c.id=' . $exam->classid . '';
$cobcourse = $DB->get_record_sql($sql);


$table = new html_table();
$table->align = array('left', 'left');
$table->size = array('30%', '70%');
$table->width = '100%';

$table->data[] = array('<b>' . get_string('examname', 'local_gradesubmission') . '</b>', $examdet->examtype);
$table->data[] = array('<b>' . get_string('lecturetypename', 'local_lecturetype') . '</b>', $examdet->lecturetype);
$table->data[] = array('<b>' . get_string('classesname', 'local_clclasses') . '</b>', $examdet->classname);
$table->data[] = array('<b>' . get_string('coursename', 'local_cobaltcourses') . '</b>', $cobcourse->fullname);
$table->data[] = array('<b>' . get_string('semestername', 'local_semesters') . '</b>', $examdet->semestername);
$table->data[] = array('<b>' . get_string('schoolname', 'local_collegestructure') . '</b>', $examdet->schoolname);
echo html_writer::table($table);


echo $OUTPUT->footer();
