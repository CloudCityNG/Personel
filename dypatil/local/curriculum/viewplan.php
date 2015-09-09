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
 * @subpackage curriculumplan
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/curriculum/lib.php');

$id = required_param('id', PARAM_INT); //plan id
$cid = required_param('cid', PARAM_INT);
global $CFG, $DB;

$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$record = $DB->get_record('local_curriculum_plan', array('id' => $id));
$PAGE->set_url('/local/curriculum/viewplan.php');
$PAGE->set_title(get_string('pluginname', 'local_curriculum') . ':' . $record->fullname);
//Header and the navigation bar
$PAGE->set_heading(get_string('curriculumplan', 'local_curriculum'));
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
$PAGE->navbar->add(get_string('manageplan', 'local_curriculum'), new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1)));
$PAGE->navbar->add(get_string('viewdetails', 'local_curriculum'));
$cplan = curriculumplan::getInstance();
//echo $OUTPUT->header();
echo $OUTPUT->header();
$hierarchy = new hierarchy();
try {
    $schoollist = $hierarchy->get_assignedschools();
    if (is_siteadmin()) {
        $schoollist = $hierarchy->get_school_items();
    }
    $count = count($schoollist); //Count of schools to which registrar is assigned
    if ($count < 1) {
        throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
    }
//$cplan->createtabview($mode);
//Heading of the page
    echo $OUTPUT->heading($record->fullname);

    $table = new html_table();
    $table->align = array('left', 'left');
    $table->size = array('25%', '75%');
    $table->width = '100%';
    $name = $cplan->name($record);
    $type = ($record->type) ? get_string('semester', 'local_semesters') : get_string('year');

    $table->data[] = array('<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', $name->school);
    $table->data[] = array('<b>' . get_string('program', 'local_programs') . '</b>', $name->program);
    $table->data[] = array('<b>' . get_string('curriculum', 'local_curriculum') . '</b>', $name->curriculum);
    $table->data[] = array('<b>' . get_string('plantype', 'local_curriculum') . '</b>', $type);
    $noofcourses = 0;
    $assign = '(' . html_writer::tag('a', 'Assign', array('href' => '' . $CFG->wwwroot . '/local/curriculum/assigncourses.php?id=' . $record->id . '&mode=assign&cid=' . $cid . '')) . ')';
    if ($DB->record_exists('local_curriculum_plancourses', array('planid' => $record->id))) {
        $noofcourses = $DB->count_records('local_curriculum_plancourses', array('planid' => $record->id));
    }


    if ($DB->record_exists('local_curriculum_plan', array('parentid' => $record->id))) {
        $childcount = $DB->count_records('local_curriculum_plan', array('parentid' => $record->id));
        $table->data[] = array('<b>' . get_string('no_child_plan', 'local_curriculum') . '</b>', $childcount);
    } else {
        $parentplan = $DB->get_field('local_curriculum_plan', 'fullname', array('id' => $record->parentid));
        if ($parentplan)
            $table->data[] = array('<b>' . get_string('parentplan', 'local_curriculum') . '</b>', $parentplan);
        $table->data[] = array('<b>' . get_string('no_course_plan', 'local_curriculum') . '</b>', $noofcourses . ' ' . $assign);
    }
    $table->data[] = array('<b>' . get_string('description', 'local_curriculum') . '</b>', $record->description);

    echo html_writer::table($table);
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
