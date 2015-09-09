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
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$inst = optional_param('inst', 0, PARAM_INT);

global $CFG;

$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$semclass = new schoolclasses();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/clclasses/index.php');
if (!($classes = $DB->get_record('local_clclasses', array('id' => $id)))) {
    print_error('invalidclassid', 'local_clclasses');
}
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);

if ($inst) {
    $PAGE->navbar->add(get_string('mycourses', 'local_clclasses'), "/local/clclasses/instview.php", get_string('view', 'local_clclasses'));
} else if (has_capability('local/clclasses:manage', $systemcontext)) {
    $PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), "/local/clclasses/index.php", get_string('view', 'local_clclasses'));
} else
    $PAGE->navbar->add(get_string('view', 'local_clclasses'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading($classes->fullname);

/* View class */
$semclass->view_classes($id);
echo $OUTPUT->footer();
