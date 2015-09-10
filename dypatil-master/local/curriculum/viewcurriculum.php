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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/curriculum/lib.php');
$id = required_param('id', PARAM_INT);

$assign = optional_param('assign', 0, PARAM_INT);

global $CFG, $DB;

$systemcontext = context_system::instance();
$currentcss = '/local/curriculum/css/styles.css';
$PAGE->requires->css($currentcss);
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
$PAGE->set_url('/local/curriculum/index.php');

if ($assign) {
    $curriculum = $DB->get_record('local_curriculum', array('id' => $id));
    if (!$curriculum->enableplan)
        redirect(new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $id)));
}
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), "/local/curriculum/index.php", get_string('viewcurriculum', 'local_curriculum'));
$nav = $assign ? get_string('manageplan', 'local_curriculum') : get_string('viewcurriculum', 'local_curriculum');
$PAGE->navbar->add($nav);
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $nav);

echo $OUTPUT->header();

if (!$curriculumlist = $DB->get_record('local_curriculum', array('id' => $id)))
    print_error(' invalid curriculum id');

$enableplan = $curriculumlist->enableplan;
//Heading of the page
$heading = $assign ? get_string('managecurriculum', 'local_curriculum') : $curriculumlist->fullname;
echo $OUTPUT->heading($heading);

$currenttab = "manageplan";
$curriculum = new curricula();
if ($assign)
    $curriculum->print_curriculumtabs($currenttab);
//Get the records from the database
$curriculum->view_curriculum($id);
/** Show the curriculum with all the plans created along with courses 
 *
 *  
 */
$cplan = curriculumplan::getInstance();
$hierarchy = new hierarchy();
$sql = "SELECT cur.*, pro.fullname AS programname
                FROM {local_curriculum} AS cur
                JOIN {local_program} AS pro
                ON pro.id = cur.programid AND pro.schoolid = cur.schoolid
                WHERE cur.id = {$id}";

$curriculum = $DB->get_record_sql($sql);
$data = array();
$table = new html_table();
$table->id = 'curriculumplan';
if ($enableplan)
    $table->headspan = '2';
//$header = '<div>'.get_string('curriculum', 'local_curriculum').': '. $curriculum->fullname . '</div>';
//$header .= '<div>'.get_string('program', 'local_programs').': '.$curriculum->programname/*html_writer::tag('a', $curriculum->programname, array('href' => ''.$CFG->wwwroot.'/local/programs/view.php?id='.$curriculum->programid.''))*/.'</div>';
echo $header;
$table->head[] = $enableplan ? get_string('curriculumplan', 'local_curriculum') : get_string('assignedcourses', 'local_curriculum');
if ($assign)
    $table->head[] = html_writer::tag('a', 'Add Plan', array('href' => '' . $CFG->wwwroot . '/local/curriculum/plan.php?curriculumid=' . $curriculum->id . '&programid=' . $curriculum->programid . '&schoolid=' . $curriculum->schoolid . '&sesskey=' . sesskey() . ''));
if (!$enableplan)
    $table->head[] = html_writer::tag('a', get_string('assign', 'local_curriculum'), array('href' => '' . $CFG->wwwroot . '/local/curriculum/assigncourses.php?mode=assign&cid=' . $curriculum->id . ''));
if ($enableplan) {
    $plans = $DB->get_records_sql("SELECT * FROM {local_curriculum_plan} WHERE programid = {$curriculum->programid} AND curriculumid = {$curriculum->id} ORDER BY sortorder ASC");
    foreach ($plans as $plan) {
        $line = array();
        $line[] = $cplan->display_curriculum_plan($plan);
        if ($assign) {
            $check = $cplan->get_dependency_list($plan->id);
            if ($check == 2)
                $line[] = '';
            else
                $line[] = html_writer::tag('a', get_string('assigncourses', 'local_curriculum'), array('href' => '' . $CFG->wwwroot . '/local/curriculum/assigncourses.php?id=' . $plan->id . '&mode=assign&cid=' . $curriculum->id . ''));
            $line[] = $cplan->get_actions('curriculum', 'plan', $plan->id, $plan->visible, $id);
        }
        $data[] = $line;
    }
} else {
    $ccourses = $DB->get_records('local_curriculum_plancourses', array('planid' => 0, 'curriculumid' => $id));
    foreach ($ccourses as $ccourse) {
        $line = array();
        $course = $DB->get_record('local_cobaltcourses', array('id' => $ccourse->courseid));
        $line[] = html_writer::tag('a', $course->fullname, array('href' => $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->id));
        $line[] = '';
        $data[] = $line;
    }
}
$table->size = array('65%', '20%', '10%');
$table->align = array('left', 'left', 'left');
$table->width = '100%';
$table->data = $data;

// Display the table
echo '<br/>';
echo html_writer::table($table);




echo $OUTPUT->footer();
