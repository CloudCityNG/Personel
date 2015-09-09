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
 * @subpackage  Creating prefix and suffix
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/departments/lib.php');
global $CFG, $DB;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/departments/info.php');

//Header and the navigation bar
$PAGE->set_title(get_string('manage_dept', 'local_departments'));
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('help', 'local_departments'));
echo $OUTPUT->header();

//-------------manage_dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));
//adding tabs using prefix_tabs function
$currenttab = 'help';
$dept_ob = manage_dept::getInstance();
$dept_ob->dept_tabs($currenttab);
//get_string('helpinfo', 'local_departments')
//echo $OUTPUT->box();
$content = get_string('info_help', 'local_departments');
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();
?>




