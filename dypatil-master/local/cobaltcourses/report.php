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
 * @subpackage cobaltcourses
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/cobaltcourses/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);

require_login();
if (!has_capability('local/cobaltcourses:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/cobaltcourses/report.php');
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('report', 'local_cobaltcourses'));
/* ---Header and the navigation bar--- */
$PAGE->set_heading(get_string('cobaltcourses', 'local_cobaltcourses'));
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('report', 'local_cobaltcourses'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));
/* ---Tab view--- */
$currenttab = 'report';
createtabview($currenttab);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('viewreportpage', 'local_cobaltcourses'));
}
$records = get_enroll_reports();
$data = array();
foreach ($records as $record) {
    $line = array();
    $line[] = $record->shortname;
    $line[] = $record->fullname;
    $line[] = $record->count;
    $line[] = $DB->get_field('local_department', 'fullname', array('id' => $record->departmentid));
    $line[] = get_programdetails($record->id);
    $data[] = $line;
}

$PAGE->requires->js('/local/cobaltcourses/reportjs.js');
echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
$table = new html_table();
$table->id = "reporttable";
$table->head = array(get_string('courseid', 'local_cobaltcourses'),
    get_string('coursename', 'local_cobaltcourses'),
    get_string('enrollments', 'local_cobaltcourses'),
    get_string('department', 'local_cobaltcourses'),
    get_string('assignedprogram', 'local_programs'));
$table->size = array('10%', '25%', '15%', '25%', '25%');
$table->align = array('left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
