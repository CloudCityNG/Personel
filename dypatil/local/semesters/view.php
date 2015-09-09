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

$id = required_param('id', PARAM_INT); //Semester id
global $CFG;
$mysemester = semesters::getInstance();
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
$PAGE->set_url('/local/semesters/view.php');
//Header and the navigation bar
$PAGE->set_heading(get_string('semesters', 'local_semesters'));
$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php'));
$PAGE->navbar->add(get_string('viewsemester', 'local_semesters'));


$semester = $DB->get_record('local_semester', array('id' => $id));

$PAGE->set_title(get_string('semesters', 'local_semesters') . ': ' . $semester->fullname);
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading($semester->fullname); //Semester Name

$table = new html_table();
$table->align = array('left', 'left');
$table->size = array('30%', '70%');
$table->width = '100%';
$name = $mysemester->names($semester);
$table->data[] = array('<b>' . get_string('schoolname', 'local_collegestructure') . '</b>', $name->school);
$table->data[] = array('<b>' . get_string('startdate', 'local_semesters') . '</b>', $name->startdate);
$table->data[] = array('<b>' . get_string('enddate', 'local_semesters') . '</b>', $name->enddate);
$table->data[] = array('<b>' . get_string('mincredits', 'local_semesters') . '</b>', $semester->mincredit);
$table->data[] = array('<b>' . get_string('maxcredits', 'local_semesters') . '</b>', $semester->maxcredit);
$table->data[] = array('<b>' . get_string('description', 'local_semesters') . '</b>', $semester->description);

echo html_writer::table($table);

echo $OUTPUT->footer();
