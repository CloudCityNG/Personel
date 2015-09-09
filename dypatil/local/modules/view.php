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
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
$proid = optional_param('id', 0, PARAM_INT);
global $CFG;
$systemcontext =context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/curriculum/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managemodule', 'local_modules'), "/local/modules/index.php", get_string('managemodule', 'local_modules'));
$PAGE->navbar->add(get_string('viewmodules', 'local_modules'));
echo $OUTPUT->header();
$tools = $DB->get_records('local_module_course', array('moduleid' => $proid));
$instance = new cobalt_modules();
$data = array();
if ($tools) {
    foreach ($tools as $tool) {
        $line = array();
        $coursename = $instance->get_coursename($tool->courseid);
        $line[] = $coursename;
        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = get_string('no_course', 'local_modules');
    $data[] = $line;
}
// Moodle 2.2 and onwards
$modulelist = $DB->get_record('local_module', array('id' => $proid));
$prgname = $DB->get_field('local_program', 'fullname', array('id' => $modulelist->programid));
$oout = "<h3>$modulelist->fullname</h3>";
$oout.="<p>" . get_string('description', 'local_modules') . " : " . strip_tags($modulelist->description) . "</font></p>";
$oout.="<p>" . get_string('assign_prog', 'local_modules') . " : $prgname</font></p>";
echo $oout;
$out = get_string('assign_course', 'local_modules');
echo $out;

//View Part starts
//start the table
$table = new html_table();
if ($tools) {
    $table->head = array(get_string('coursename', 'local_modules'));
}
$table->size = array('20%', '20%', '10%', '10%', '10%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
