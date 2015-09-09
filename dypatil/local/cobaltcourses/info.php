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
 * @subpackage cobaltcourses
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/cobaltcourses/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/cobaltcourses:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/cobaltcourses/info.php');
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('courselist', 'local_cobaltcourses'));
/* ---Header and the navigation bar--- */
$title = get_string('pluginname', 'local_cobaltcourses');
$PAGE->set_heading(get_string('cobaltcourses', 'local_cobaltcourses'));
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('help', 'local_cobaltcourses'));

echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));
/* ---Current tab--- */
$currenttab = 'information';
/* ---adding tabs--- */
createtabview($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpinfo', 'local_cobaltcourses'));
}
$content = get_string('info_help', 'local_cobaltcourses');
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();

