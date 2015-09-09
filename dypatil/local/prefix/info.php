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
require_once($CFG->dirroot . '/local/prefix/lib.php');
global $CFG, $DB;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->set_url('/local/prefix/info.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_prefix'), new moodle_url('/local/prefix/index.php'));
$PAGE->navbar->add(get_string('info', 'local_prefix'));

echo $OUTPUT->header();
//===========prefix and suffix heading------------------------


echo $OUTPUT->heading(get_string('pluginname', 'local_prefix'));

//adding tabs using prefix_tabs function
$currenttab = 'info';
$prefix = prefix_suffix::getInstance();
$prefix->prefix_tabs($currenttab);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_prefix'));
}

echo '<div class="help_cont">' . get_string('helpinfo_des', 'local_prefix') . '<div>';

echo $OUTPUT->footer();
?>




