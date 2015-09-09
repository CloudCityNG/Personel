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

$schoolid = optional_param('schoolid', 0, PARAM_INT);
$semesterid = optional_param('semesterid', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_INT);
global $CFG;

$hierarchy = new hierarchy();
$mysemester = semesters::getInstance();
$conf = new object();

$systemcontext = context_system::instance();

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
if (!has_capability('local/semesters:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/semesters/assign.php');
$PAGE->set_title(get_string('pluginname', 'local_semesters'));
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php'));
$returnurl = new moodle_url('/local/semesters/assign.php');
if ($unassign) {
    $DB->delete_records('local_school_semester', array('schoolid' => $schoolid, 'semesterid' => $semesterid));
    $conf->school = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
    $conf->semester = $DB->get_field('local_semester', 'fullname', array('id' => $semesterid));
    $message = get_string('unassignedschool', 'local_semesters', $conf);
    $hierarchy->set_confirmation($message, $returnurl);
}
if ($schoolid) {
    $rec = new stdClass();
    $rec->schoolid = $schoolid;
    $rec->semesterid = $semesterid;
    $DB->insert_record('local_school_semester', $rec);
    $conf->school = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
    $conf->semester = $DB->get_field('local_semester', 'fullname', array('id' => $semesterid));
    $message = get_string('assignedschool', 'local_semesters', $conf);
    $hierarchy->set_confirmation($message, $returnurl);
}

$PAGE->navbar->add(get_string('assignschool', 'local_collegestructure'));
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
$currenttab = 'assign';
$mysemester->createtabview($currenttab);

$today = date('Y-m-d');
$mysql = "SELECT * FROM {local_semester} WHERE  '{$today}' <= from_unixtime( startdate,  '%Y-%m-%d' )";
$semesters = $DB->get_records_sql($mysql);

$data = array();
foreach ($semesters as $semester) {
    $line = array();
    $line[] = $semester->fullname;
    $schoollist = $DB->get_records('local_school_semester', array('semesterid' => $semester->id));
    $schoolname = '';
    $unassign = '';
    foreach ($schoollist as $school) {
        $schoolname .= '<div>' . $DB->get_field('local_school', 'fullname', array('id' => $school->schoolid)) . '</div>';
        $unassign .= '<div>' . html_writer::link(new moodle_url('/local/semesters/assign.php', array('schoolid' => $school->schoolid, 'semesterid' => $school->semesterid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('unassign', 'local_semesters'), 'alt' => get_string('unassign', 'local_semesters'), 'class' => 'iconsmall'))) . '</div>';
    }
    $line[] = $schoolname;
    $line[] = $unassign;
    $items = $hierarchy->get_school_items();
    $items = $mysemester->remove_assignedschool($items, $semester->id);
    $parents = $hierarchy->get_school_parent($items);
    $scl = new single_select(new moodle_url('/local/semesters/assign.php?semesterid=' . $semester->id . ''), 'schoolid', $parents, '', null);
    $line[] = $OUTPUT->render($scl);
    $data[] = $line;
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewsemesterreportspage', 'local_semesters'));
}
if (!empty($data)) {  //if data is present in the table then only display the filters
    $PAGE->requires->js('/local/semesters/semesterjs.js');
}
//View Part starts
//start the table
$table = new html_table();
$table->id = "semestertable";
$head = array();
$head[] = get_string('semestername', 'local_semesters');
$head[] = get_string('assignedtoschools', 'local_collegestructure');
$head[] = get_string('unassign', 'local_semesters');
$head[] = get_string('assignto', 'local_semesters');
$table->head = $head;
$table->size = array('25%', '25%', '25%', '25%');
$table->align = array('left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
// Display the table
echo html_writer::table($table);
echo $OUTPUT->footer();
