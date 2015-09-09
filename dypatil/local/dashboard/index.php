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
 * @subpackage dashboard
 * @copyright  2013 sreenivasula@eabyas.in
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('lib.php');
global $CFG, $DB;
$id = optional_param('id', -1, PARAM_INT);
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_course::instance($id));
$cc = $DB->get_record('course', array('id' => $id));
$PAGE->set_course($cc);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->set_url('/local/dashboard/index.php');
$PAGE->requires->css('/local/dashboard/styles.css');
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_dashboard');
// $PAGE->set_title($string);
$PAGE->navbar->add(get_string('dashboard', 'local_dashboard'), new moodle_url('/local/dashboard/index.php'));
$PAGE->navbar->add(get_string('dashboard', 'local_dashboard'));
$strheading = get_string('dashboard', 'local_dashboard');
echo $OUTPUT->header();
$modules = progress_modules_in_use($id);
$events = progress_event_information($modules, $id);
$attempts = progress_attempts($modules, $id, $events, $USER->id);
$course_name = $DB->get_field('course', 'fullname', array('id' => $id));
$percent = progress_percentage($events, $attempts);
echo HTML_WRITER::start_tag('div', array('class' => 'course_name'));
echo $course_name;
echo HTML_WRITER::end_tag('div');
$bar = '<div class="outer"><div class="dynamic_bar" style="max-width:' . $percent . '%"></div></div>';
$table = table_completion($events, $attempts, $id);
if (empty($table)) {
    echo HTML_WRITER::start_tag('div', array('class' => 'empty'));
    echo 'No Activities created for this Course still...';
    echo HTML_WRITER::end_tag('div');
} else {
    echo '<div class="main_list_bar">
    <span class="task_total">No.of Tasks : ' . count($events) . '</span> 
     <span class="task_bar">' . $bar . '</span>
    <span class="percent">' . $percent . '%' . '</span>
    </div>';
    echo '<div class="list_bar_head"><span class="task_head">Task-List</span> <span class="task_status">Status</span></div>';
    echo '<table class="generaltable">';
    foreach ($table as $key => $value) {
        echo '<tr><td>' . $value . '</td></tr>';
    }
    echo '</table>';
}
// Organise access to JS.
$jsmodule = array(
    'name' => 'local_dashboard',
    'fullpath' => '/local/dashboard/module.js',
    'requires' => array(),
    'strings' => array(
        array('time_expected', 'local_dashboard'),
    ),
);
$progressBarIcons = 1;
$orderby = 'orderbytime';
$showpercentage = 1;
$displayNow = 1;
$displaydate = (!isset($orderby) || $orderby == 'orderbytime') && (!isset($displayNow) || $displayNow == 1);
$arguments = array($CFG->wwwroot, array_keys($modules), $displaydate);
$PAGE->requires->js_init_call('M.local_dashboard.init', $arguments, false, $jsmodule);
echo $OUTPUT->footer();
?>