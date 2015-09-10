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
 * @subpackage collegestructure
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../config.php');
global $CFG,$USER,$PAGE;
$page = optional_param ('page', 0, PARAM_INT);

$systemcontext = context_system::instance();;
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
//$PAGE->set_context($systemcontext);

require_login();
$currentcss = '/local/collegestructure/css/styles.css';
$PAGE->requires->css($currentcss);  
$PAGE->set_url('/local/collegestructure/index.php');

//Header and the navigation bar
$PAGE->set_heading(get_string('errormessage', 'local_collegestructure'));
$PAGE->navbar->add(get_string('errormessage', 'local_collegestructure'));

echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('nopermissions', 'local_collegestructure'));



echo '<img src="'.$CFG->wwwroot.'/local/sorry.jpg" alt="You dont have permissions" >';

echo $OUTPUT->footer();
