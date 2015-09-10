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
 * @subpackage semesters
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/semesters/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $CFG;
$mysemester = semesters::getInstance();
$systemcontext =context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$capabilities_array = $mysemester->semester_capabilities();
if (!has_any_capability($capabilities_array, $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/semesters/report.php');
$PAGE->set_title(get_string('semesters', 'local_semesters') . ': ' . get_string('report', 'local_semesters'));
//Header and the navigation bar
$PAGE->set_heading(get_string('semesters', 'local_semesters'));
$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php'));
$PAGE->navbar->add(get_string('report', 'local_semesters'));
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
$currenttab = 'report';
$mysemester->createtabview($currenttab);
$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();

if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$parent = $hierarchy->get_school_parent($schoollist, '', false, false);
$schoolid = implode(',', array_keys($parent));
//get the reports for the Current & Completed Semesters.

$semesters = $mysemester->get_listofsemesters($currenttab, $schoolid);
$data = array();
foreach ($semesters as $semester) {
    $line = array();
    $line[] = $semester->fullname;
    $line[] = $DB->count_records('local_user_semester', array('semesterid' => $semester->id));
    $line[] = $DB->count_records('local_user_sem_details', array('semesterid' => $semester->id, 'studentstatus' => 0));
    $line[] = $DB->count_records('local_user_sem_details', array('semesterid' => $semester->id, 'studentstatus' => 1));
    $line[] = $DB->count_records('local_user_sem_details', array('semesterid' => $semester->id, 'studentstatus' => 2));
    $data[] = $line;
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewsemesterreportspage', 'local_semesters'));
}
$PAGE->requires->js('/local/semesters/reportjs.js');

echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//View Part starts
//start the table
$table = new html_table();
$table->id = "semesterreporttable";
$head = array();
$head[] = get_string('semester', 'local_semesters');
$head[] = get_string('enrolled', 'local_semesters');
$head[] = get_string('completed', 'local_semesters');
$head[] = get_string('probated', 'local_semesters');
$head[] = get_string('dismissed', 'local_semesters');
$table->head = $head;
$table->size = array('32%', '17%', '17%', '17%', '17%');
$table->align = array('left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
// Display the table
echo html_writer::table($table);
echo $OUTPUT->footer();
