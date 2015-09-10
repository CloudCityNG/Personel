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
 * @subpackage gradesubmission
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
global $CFG;
$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

$PAGE->set_title(get_string('managegradesubmission', 'local_gradesubmission'));

$PAGE->set_title(get_string('pluginname', 'local_gradesubmission'));

require_login();

//    if (!has_capability('local/gradesubmission:manage', $systemcontext)) {
//      print_error('You dont have permissions');
//    }

$PAGE->set_url('/local/gradesubmission/index.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_gradesubmission'), new moodle_url('/local/gradesubmission/index.php'));
$PAGE->navbar->add(get_string('info', 'local_gradesubmission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managegradesubmission', 'local_gradesubmission'));
//Heading of the page
$systemcontext = context_system::instance();
$currenttab = 'info';
createtabview_gsub($currenttab);

// for description
// if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
// echo $OUTPUT->box(get_string('helpinfo', 'local_gradesubmission'));
// }
$content = get_string('info_help', 'local_gradesubmission');
echo '<div class="help_cont">' . $content . '<div>';

echo $OUTPUT->footer();
