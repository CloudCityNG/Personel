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
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $PAGE;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/collegestructure/lib.php');
$systemcontext = context_system::instance();
require_login();
$school = new school();
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');

    redirect($returnurl);
}
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/collegestructure/info.php');
/* ---Header and the navigation bar--- */
$PAGE->set_title(get_string('manageschools', 'local_collegestructure'));
$PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), new moodle_url('/local/collegestructure/index.php'));
$PAGE->navbar->add(get_string('info', 'local_collegestructure'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
$currenttab = 'info';
echo $OUTPUT->heading(get_string('manageschools', 'local_collegestructure'));
$school->print_collegetabs($currenttab, $id = NULL);
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('information', 'local_collegestructure'));
}
echo '<div class="help_cont">' . get_string('help_des', 'local_collegestructure') . '<div>';
;
echo $OUTPUT->footer();
