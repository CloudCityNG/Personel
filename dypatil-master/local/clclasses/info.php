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
require_once($CFG->dirroot . '/local/clclasses/lib.php');
global $CFG;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if (!has_capability('local/clclasses:manage', $systemcontext) && !is_siteadmin()) {
    $returnurl = new moodle_url('/local/error.php');
    redirect($returnurl);
}
$PAGE->set_url('/local/clclasses/index.php');
$clssinfo = new schoolclasses();
$hierarchy = new hierarchy();
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'));
$PAGE->navbar->add(get_string('info', 'local_clclasses'));
$PAGE->requires->css('/local/clclasses/css/style.css');
echo $OUTPUT->header();
$currenttab = "info";
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
$clssinfo->print_classestabs($currenttab);
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('help_info', 'local_clclasses'));
}
$content = get_string('info_help', 'local_clclasses');
$semester = get_string('semester', 'local_semesters');
$content = preg_replace("/semester/i", $semester, $content);
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();
