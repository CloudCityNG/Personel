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
 * @package    local
 * @subpackage examtype
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

$schoolid = optional_param('schoolid', 0, PARAM_INT); // School ID
$progid = optional_param('progid', 0, PARAM_INT); // Program ID
global $CFG;
$systemcontext = context_system::instance();
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

$PAGE->set_url('/local/examtype/info.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('pluginname', 'local_examtype'));
$PAGE->navbar->add(get_string('pluginname', 'local_examtype'));
$PAGE->navbar->add(get_string('info', 'local_examtype'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'local_examtype')); //Heading of the page
$currenttab = "info";
$instance = new cobalt_examtype();
$instance->print_examtabs($currenttab, $id = -1);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_examtype'));
}
$content = get_string('info_help', 'local_examtype');
echo '<div class="help_cont">' . $content . '<div>';

echo $OUTPUT->footer();
