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
 * @subpackage semesters
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/semesters/lib.php');
global $CFG;

$systemcontext =context_system::instance(); 

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

$mysemester = semesters::getInstance();
//If the loggedin user have the capability of managing the batches allow the page
$capabilities_array = $mysemester->semester_capabilities();
if (!has_any_capability($capabilities_array, $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/semesters/info.php');

//Header and the navigation bar
$PAGE->set_title(get_string('semesters', 'local_semesters') . ': ' . get_string('help', 'local_semesters'));

$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php'));
$PAGE->navbar->add(get_string('help', 'local_semesters'));
echo $OUTPUT->header();

$hierarchy = new hierarchy();

//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
//Tab view

$currenttab = 'help';
$mysemester->createtabview($currenttab);

//Description for the page
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('information', 'local_semesters'));
}
echo '<div class="help_cont">' . get_string('info_des', 'local_semesters') . '<div>';
echo $OUTPUT->footer();
