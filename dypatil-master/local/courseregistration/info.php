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
require_once($CFG->dirroot . '/local/users/lib.php');
require_once('../../local/courseregistration/lib.php');
$currentcss = '/local/co/css/styles.css';
$PAGE->requires->css($currentcss);
global $CFG, $USER, $DB;

$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$sort = optional_param('sort', 'name', PARAM_ALPHANUM);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$semesterid = optional_param('semester', 0, PARAM_INT);
$perpage = 10;
//$classcount=10;
//get the admin layout
global $CFG, $USER;
$PAGE->set_pagelayout('admin');
//$myuser = users::getInstance();
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/info.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageregistration', 'local_courseregistration'));
$PAGE->navbar->add(get_string('info', 'local_courseregistration'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page

echo $OUTPUT->heading(get_string('manageclassapproval', 'local_courseregistration'));

// Moodle 2.2 and onwards
$currenttab = "info";
print_registrationtabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_courseregistration'));
}

echo $OUTPUT->footer();
