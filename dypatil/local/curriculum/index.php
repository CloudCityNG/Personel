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
require_once('lib.php');

global $CFG;

$systemcontext = context_system::instance();
$facid = optional_param('facid', 0, PARAM_INT);
$proid = optional_param('proid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/curriculum/index.php');
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . get_string('view', 'local_curriculum'));
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url($CFG->wwwroot . '/local/curriculum/index.php'));
$PAGE->navbar->add(get_string('view', 'local_curriculum'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
$currenttab = "view";
$curriculum = new curricula();
$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();

$curriculum->print_curriculumtabs($currenttab);
// Moodle 2.2 and onwards

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_curriculum'));
}

$schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}

$schoolidin = implode(',', array_keys($schoollist));
//Get the records from the database
$sql = "SELECT c.id,c.visible, c.enableplan, c.enddate,c.programid,c.schoolid,c.fullname as curriculumname,s.fullname as schoolname,p.fullname as programname
            FROM {local_curriculum} c
            JOIN {local_program} AS p ON c.programid = p.id
            JOIN {local_school} AS s ON c.schoolid=s.id
            WHERE c.schoolid IN ($schoolidin)";
$tools = $DB->get_records_sql($sql);

$data = array();
  $capabilities_array = array('local/curriculum:manage', 'local/curriculum:delete', 'local/curriculum:update', 'local/curriculum:visible');
if ($tools) {
    foreach ($tools as $tool) {
        $line = array();
        $linkcss = $tool->visible ? ' ' : 'class="dimmed" ';
        $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/curriculum/viewcurriculum.php?id=' . $tool->id . '">' . format_string($tool->curriculumname) . '</a>';

        $programname = $tool->programname;
        $line[] = $programname;
        $line[] = $tool->schoolname;

        $line[] = date("d-M-Y", $tool->enddate);

        if ($tool->enableplan)
            $line[] = '<a ' . $linkcss . ' title="Add Curriculum Plan" href="' . $CFG->wwwroot . '/local/curriculum/viewcurriculum.php?id=' . $tool->id . '&assign=1&sesskey=' . sesskey() . '">' . get_string('manageplan', 'local_curriculum') . '</a>';
        else
            $line[] = '<a ' . $linkcss . ' title="Assign Courses" href="' . $CFG->wwwroot . '/local/curriculum/assigncourses.php?cid=' . $tool->id . '&mode=assign&sesskey=' . sesskey() . '">' . get_string('assigncourses', 'local_curriculum') . '</a>';

        $buttons = array();
        $delete_cap = array('local/curriculum:manage', 'local/curriculum:delete');
        if (has_any_capability($delete_cap, $systemcontext)) {
            $buttons[] = html_writer::link(new moodle_url('/local/curriculum/curriculum.php', array('id' => $tool->id, 'page' => $page, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
        }

        $update_cap = array('local/curriculum:manage', 'local/curriculum:update');
        if (has_any_capability($update_cap, $systemcontext)) {
            $buttons[] = html_writer::link(new moodle_url('/local/curriculum/curriculum.php', array('id' => $tool->id, 'page' => $page, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        }


        $visible_cap = array('local/curriculum:manage', 'local/curriculum:visible');
        if (has_any_capability($visible_cap, $systemcontext)) {
            if ($tool->visible) {
                $buttons[] = html_writer::link(new moodle_url('/local/curriculum/curriculum.php', array('id' => $tool->id, 'page' => $page, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
            } else {
                $buttons[] = html_writer::link(new moodle_url('/local/curriculum/curriculum.php', array('id' => $tool->id, 'page' => $page, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
            }
        }
        // ------------------- Edited by hema------------------------------
      

        if (has_any_capability($capabilities_array, $systemcontext)) {
            $line[] = implode(' ', $buttons);
        }

        $data[] = $line;
    }
} else {

    $line = array();
    $line[] = get_string('no_records', 'local_request');
    $data[] = $line;
}

$PAGE->requires->js('/local/curriculum/js/curriculum.js');

echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//View Part starts
//start the table
$table = new html_table();
$table->id = 'curriculumtable';
$table->head = array(
    get_string('headername', 'local_curriculum'), get_string('programname', 'local_programs'), get_string('schoolname', 'local_collegestructure'), get_string('validtill', 'local_curriculum'), get_string('addcuplan', 'local_curriculum'));

// ------------------- Edited by hema------------------------------ 
if (has_any_capability($capabilities_array, $systemcontext)) {
    array_push($table->head, get_string('action', 'local_curriculum'));
}
$table->size = array('20%', '23%', '22%', '11%', '15%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);

//echo $OUTPUT->single_button(new moodle_url('/local/curriculum/curriculum.php', array('id' => -1)), get_string('createcurriculum','local_curriculum'));

echo $OUTPUT->footer();
