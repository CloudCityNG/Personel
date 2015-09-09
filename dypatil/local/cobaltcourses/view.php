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
 * @subpackage cobaltcourses
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');

$id = required_param('id', PARAM_INT);
$plugin = optional_param('plugin', 'cobaltcourses', PARAM_RAW);
$page = optional_param('page', 'index', PARAM_RAW);
$title = optional_param('title', 'Manage Courses', PARAM_RAW);
global $CFG;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

if (!$record = $DB->get_record('local_cobaltcourses', array('id' => $id))) {
    print_error('invalidcourseid');
}
$PAGE->set_url('/local/cobaltcourses/view.php');
$PAGE->set_title('Cobalt Courses: ' . $record->shortname);
/* ---Header and the navigation bar--- */
$PAGE->set_heading('Cobalt Courses');
if (has_capability('local/cobaltcourses:manage', $systemcontext)) {
    $PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
} else {
    $PAGE->navbar->add($title, new moodle_url('/local/' . $plugin . '/' . $page . '.php'));
}

$PAGE->navbar->add(get_string('viewdetails', 'local_cobaltcourses'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading($record->fullname);

$table = new html_table();
$table->align = array('left', 'left');
$table->size = array('30%', '70%');
$table->width = '100%';
$department = $DB->get_field('local_department', 'fullname', array('id' => $record->departmentid));
$school = $DB->get_field('local_school', 'fullname', array('id' => $record->schoolid));
$type = ($record->coursetype) ? get_string('general', 'local_cobaltcourses') : get_string('elective', 'local_cobaltcourses');
$table->data[] = array('<b>' . get_string('shortname', 'local_cobaltcourses') . '</b>', $record->shortname);
$table->data[] = array('<b>' . get_string('dept', 'local_departments') . '</b>', $department);
$table->data[] = array('<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', $school);
$table->data[] = array('<b>' . get_string('coursetype', 'local_cobaltcourses') . '</b>', $type);
$table->data[] = array('<b>' . get_string('credithours', 'local_cobaltcourses') . '</b>', $record->credithours);
if ($DB->record_exists('local_course_prerequisite', array('courseid' => $record->id, 'schoolid' => $record->schoolid))) {
    $table->data[] = array('<b>' . get_string('courseprerequisite', 'local_cobaltcourses') . '</b>', related_courses('local_course_prerequisite', $record));
}
if ($DB->record_exists('local_course_equivalent', array('courseid' => $record->id, 'schoolid' => $record->schoolid))) {
    $table->data[] = array('<b>' . get_string('courseequivalent', 'local_cobaltcourses') . '</b>', related_courses('local_course_equivalent', $record));
}
$table->data[] = array('<b>' . get_string('coursecost', 'local_cobaltcourses') . '</b>', $record->coursecost);
$table->data[] = array('<b>' . get_string('description', 'local_cobaltcourses') . '</b>', $record->summary);

echo html_writer::table($table);
echo $OUTPUT->footer();
