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
 * @subpackage lecturetype
 * @copyright  2013 sreenivasula@eabyas.in
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lecturetype/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
// get the admin layout
$PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/lecturetype:view', $systemcontext)) {
    print_cobalterror('permissions_error','local_collegestructure');
}
$PAGE->set_url('/local/lecturetype/index.php');
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_lecturetype') . ' : ' . get_string('view', 'local_lecturetype');
$PAGE->set_title($string);
$PAGE->navbar->add(get_string('managelecturetypes', 'local_lecturetype'), new moodle_url('/local/lecturetype/index.php'));
$PAGE->navbar->add(get_string('view', 'local_lecturetype'));
$strheading = get_string('managelecturetypes', 'local_lecturetype');
// echo $OUTPUT->header();
echo $OUTPUT->header();
// Heading of the page
echo $OUTPUT->heading($strheading);
$currenttab = 'view';      // as ‘view’ page will be our default page, value to the ‘$currentab’ is  given as a static value.
$instance = new cobalt_lecturetype();

// echo $OUTPUT->heading(get_string('managelecturetypes', 'local_lecturetype'));
$hier = new hierarchy();
$schools = $hier->get_assignedschools();
$schools = $hier->get_school_parent($schools, $selected = array(), $inctop = false, $all = false);
if (is_siteadmin()) {
    $schools = $hier->get_school_items();
}
$instance->print_lecturetabs($currenttab, $id = -1);

$schoollist_string = implode(',', array_keys($schools));
if (empty($schoollist_string)) {
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->footer();
    die;
}
//Get the records from the database
$tools = $DB->get_records_sql('select * from {local_lecturetype} where schoolid in (' . $schoollist_string . ')');
$data = array();
foreach ($tools as $tool) {
    $line = array();
    $line[] = $tool->lecturetype;
    $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    $line[] = $schoolname;
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/lecturetype/edit.php', array('id' => $tool->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/lecturetype/edit.php', array('id' => $tool->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    if (has_capability('local/lecturetype:manage', $systemcontext))
        $line[] = implode(' ', $buttons);
    $data[] = $line;
}
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_lecturetype'));
}
//  View Part starts
//  start the table
$table = new html_table();
$table->head = array(
    get_string('headername', 'local_lecturetype'),
    get_string('schoolname', 'local_collegestructure'));
if (has_capability('local/lecturetype:manage', $systemcontext))
    $table->head[] = get_string('action');

$table->size = array('35%', '35%', '35%');
$table->align = array('center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
