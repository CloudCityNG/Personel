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
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/mentor/lib.php');

$currenttab = optional_param('mode', 'view', PARAM_RAW);
global $CFG, $USER;
$mymentor = mentor::getInstance();
$systemcontext =context_system::instance();
require_login();
//print_object($systemcontext);
$mentor = false;
if (has_capability('local/clclasses:approvemystudentclclasses', $systemcontext)) {
    $mentor = true;
//}else if(has_capability('clclasses:approvemystudentclclasses', $systemcontext)){
//    $mentor = false ;
} else {
    print_error('You dont have permissions');
}

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);


$PAGE->set_url('/local/mentor/index.php');
if ($mentor) {
    $PAGE->set_title(get_string('mystudents', 'local_mentor'));
//Header and the navigation bar
    $PAGE->set_heading(get_string('pluginname', 'local_mentor'));
    $PAGE->navbar->add(get_string('mystudents', 'local_mentor'));
} else {
    $PAGE->set_title('Parent');
    $PAGE->set_heading('Parent');
    $PAGE->navbar->add('Parent');
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('mystudents', 'local_mentor'));
//Description for the page
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewstudentspage', 'local_mentor'));
}
$students = $mymentor->get_assigned_students($mentor);
$data = array();
if (!empty($students)) {
    foreach ($students as $student) {
        $line = array();
        $list = $mymentor->get_student_details($student->studentid);
        $line[] = html_writer::tag('a', fullname($list), array('href' => '' . $CFG->wwwroot . '/local/users/profile.php?id=' . $student->studentid . ''));
        $programname = $DB->get_field('local_program', 'fullname', array('id' => $student->programid));
        $line[] = $programname;
        $line[] = html_writer::tag('a', get_string('view', 'local_mentor'), array('href' => '' . $CFG->wwwroot . '/local/mentor/student.php?id=' . $student->studentid . ''));

        $data[] = $line;
    }
} else {
    echo get_string('nostudentsassigned', 'local_mentor');
}
$PAGE->requires->js('/local/mentor/js/mentor_index.js');
$table = new html_table();
$table->id = 'mentor_view';
$table->head = array(get_string('studentname', 'local_gradesubmission'), get_string('program', 'local_programs'), get_string('viewdetails', 'local_mentor'));
$table->size = array('33%', '33%', '33%');
$table->align = array('left', 'left');
$table->width = '100%';
$table->data = $data;
if (!empty($students)) {
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
