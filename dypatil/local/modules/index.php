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
 * @subpackage Modules
 * @copyright  2013 Sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once('lib.php');
global $CFG;
$systemcontext = context_system::instance();
$proid = optional_param('proid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/modules:view', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/modules/index.php');
$string = get_string('pluginname', 'local_modules') . ':' . get_string('viewmodules', 'local_modules');
$PAGE->set_title($string);
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managemodule', 'local_modules'));
$PAGE->navbar->add(get_string('viewmodules', 'local_modules'));
$instance = new cobalt_modules();
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('managemodule', 'local_modules'));
$hier = new hierarchy();
$schools = $hier->get_assignedschools();
$currenttab = "lists";
$instance->print_tabs($currenttab, -1);

// 
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_modules'));
}

$schools = $hier->get_school_parent($schools, $selected = array(), $inctop = false, $all = false);
if (is_siteadmin()) {
    $schools = $hier->get_school_items();
}

$schoollist_string = implode(',', array_keys($schools));
if (empty($schoollist_string)) {
    echo get_string('no_records', 'local_request');

    echo $OUTPUT->footer();
    die;
}
$tools = $DB->get_records_sql('select * from {local_module} where schoolid in (' . $schoollist_string . ')');
$data = array();
$capabilities_array = array('local/modules:manage', 'local/modules:delete', 'local/modules:update', 'local/modules:visible');
$assigncourse_cap = array('local/modules:manage', 'local/modules:assigncourse');
foreach ($tools as $tool) {
    $line = array();
    $linkcss = $tool->visible ? ' ' : 'class="dimmed" ';
    $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/modules/view.php?id=' . $tool->id . '">' . format_string($tool->fullname) . '</a>';
    $school_name = $DB->get_record('local_school', array('id' => $tool->schoolid));
    $programname = $instance->get_programname($tool->programid);
    $line[] = $DB->get_field('local_program', 'fullname', array('id' => $tool->programid));
    if (count($schools) > 1) {
        $line[] = $school_name->fullname;
    }

    if (has_any_capability($assigncourse_cap, $systemcontext)) {
        $line[] = '<a ' . $linkcss . ' title="Assign Course" href="' . $CFG->wwwroot . '/local/modules/assigncourse.php?moduleid=' . $tool->id . '&sesskey=' . sesskey() . '">Assign Courses</a>';
    } else
        $line[] = '<a ' . $linkcss . ' title="Permission denied" >Assign Courses</a>';

    // ------------------- Edited by hema------------------------------

    if (has_any_capability($capabilities_array, $systemcontext)) {
        $pluginname = 'modules';
        $plugin = 'module';
        $line[] = $hier->get_actions($pluginname, $plugin, $tool->id, $tool->visible);
    }
    $data[] = $line;
}
$PAGE->requires->js('/local/modules/js/module.js');

echo '<div class="filterarea"></div>';
//View Part starts
$table = new html_table();
$table->id = "moduletable";
$multiple = $hier->get_assignedschools();
if (is_siteadmin()) {
    $multiple = $hier->get_school_items();
}
if (count($multiple) == 1) {
    $table->head = array(
        get_string('modulename', 'local_modules'),
        get_string('programname', 'local_programs'),
        get_string('viewcourses', 'local_modules'));
    $table->size = array('25%', '25%', '25%', '25%');
    $table->align = array('left', 'left', 'left', 'center');
} else {
    $table->head = array(
        get_string('modulename', 'local_modules'),
        get_string('programname', 'local_programs'),
        get_string('schoolname', 'local_collegestructure'),
        get_string('viewcourses', 'local_modules'));
    $table->size = array('25%', '25%', '20%', '20%', '10%');
    $table->align = array('left', 'left', 'left', 'center', 'center');
}

// ------------------- Edited by hema------------------------------ 
if (has_any_capability($capabilities_array, $systemcontext)) {
    array_push($table->head, get_string('actions', 'local_modules'));
}

$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();
