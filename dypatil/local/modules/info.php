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
$systemcontext =context_system::instance();
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
if (!has_capability('local/modules:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/modules/info.php');
$PAGE->set_title(get_string('pluginname', 'local_modules'));
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managemodule', 'local_modules'), new moodle_url('/local/modules/index.php'));
$PAGE->navbar->add(get_string('info', 'local_modules'));
$instance = new cobalt_modules();
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('modules:manage', 'local_modules'));
$currenttab = "info";
$instance = new cobalt_modules();
$instance->print_tabs($currenttab, -1);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_modules'));
}
$content = get_string('info_help', 'local_modules');
echo '<div class="help_cont">' . $content . '<div>';

echo $OUTPUT->footer();
