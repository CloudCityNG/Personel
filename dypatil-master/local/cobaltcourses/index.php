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
require_once($CFG->dirroot . '/local/cobaltcourses/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $CFG, $DB;

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
if (!has_capability('local/cobaltcourses:manage', $systemcontext)) {
    print_cobalterror('permissions_error', 'local_collegestructure');
}
$PAGE->set_url('/local/cobaltcourses/index.php');
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('courselist', 'local_cobaltcourses'));
/* ---Header and the navigation bar--- */
$title = get_string('pluginname', 'local_cobaltcourses');
$PAGE->set_heading(get_string('cobaltcourses', 'local_cobaltcourses'));
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url($CFG->wwwroot . '/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('courselist', 'local_cobaltcourses'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));

$hierarchy = new hierarchy();
if (is_siteadmin($USER->id)) {
    $schoollist = $DB->get_records('local_school', array('visible' => 1));
    if (empty($schoollist))
        throw new schoolnotfound_exception();
} else {
    $schoollist = $hierarchy->get_assignedschools();
}
try {
    $parent = $hierarchy->get_school_parent($schoollist, '', false, false);
    $count = count($schoollist); //Count of schools to which registrar is assigned---*/
    $schoolid = implode(',', array_keys($parent));
    $data = array();
    $capabilities_array = array('local/cobaltcourses:manage', 'local/cobaltcourses:delete', 'local/cobaltcourses:update', 'local/cobaltcourses:visible');
    if ($count < 1) {
        throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
    }
    /* ---display the list of Cobalt Courses under the assigned School--- */
    $sql = "SELECT * FROM {local_cobaltcourses} WHERE schoolid IN ($schoolid)";
    $courses = $DB->get_records_sql($sql);
    foreach ($courses as $course) {
        $line = array();

        $linkcss = $course->visible ? ' ' : 'class="dimmed" ';
        $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->id . '&title=' . $title . '&plugin=cobaltcourses&page=index">' . format_string($course->shortname) . '</a>';
        $line[] = $course->fullname;
        $line[] = ($course->coursetype == 0) ? get_string('general', 'local_cobaltcourses') : get_string('elective', 'local_cobaltcourses');
        $line[] = $DB->get_field('local_department', 'fullname', array('id' => $course->departmentid));
        $line[] = $DB->get_field('local_school', 'fullname', array('id' => $course->schoolid));
        $line[] = $course->credithours;
        //-------------------Edited by hema------------------------------------------

        if (has_any_capability($capabilities_array, $systemcontext)) {
            $line[] = $hierarchy->get_actions('cobaltcourses', 'cobaltcourse', $course->id, $course->visible);
        }
        $data[] = $line;
    }


    /* ---Current tab--- */
    $currenttab = 'view';
    /* ---adding tabs--- */
    createtabview($currenttab);
    /* ---Heading of the page--- */


    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('descriptionforviewpage', 'local_cobaltcourses'));
    }

    $PAGE->requires->js('/local/cobaltcourses/course.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';

    if (!empty($data)) {
        echo '<div id="createbutton" style="float:right;">';
        echo $OUTPUT->single_button(new moodle_url('/local/cobaltcourses/download_all.php?format=xls', array('format' => 'xls')), get_string('download_all', 'local_cobaltcourses'));
        echo '</div>';
    }
    $head = array();
    $head[] = get_string('courseid', 'local_cobaltcourses');
    $head[] = get_string('course', 'local_cobaltcourses');
    $head[] = get_string('type', 'local_cobaltcourses');
    $head[] = get_string('department', 'local_cobaltcourses');
    $head[] = get_string('schoolid', 'local_collegestructure');
    $head[] = get_string('credithours', 'local_cobaltcourses');
//-------------------Edited by hema------------------------------------------
    if (has_any_capability($capabilities_array, $systemcontext)) {
        array_push($head, get_string('action'));
    }

    /* ---View Part starts--- */
    /* ---start the table--- */
    $table = new html_table();
    $table->id = "coursetable";
    $table->head = $head;
    $table->size = array('10%', '25%', '5%', '20%', '20%', '10%', '10%');

    $table->align = array('left', 'left', 'left', 'left', 'left', 'center', 'left');
    $table->width = '100%';
    $table->data = $data;
    if (!empty($data))
        echo html_writer::table($table);
    else
        echo get_string('no_records', 'local_request');
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();

