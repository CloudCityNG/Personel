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
 * Version details.
 *
 * @package    local
 * @subpackage approval(idcard)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/lib/lib.php');
require_once('../../local/lib.php');
global $USER, $PAGE;

$id = required_param('id', PARAM_INT);
$approve = optional_param('approve', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/exemdetails.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
$requestss = new requests();
require_login();
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$strheading = get_string('course_exem', 'local_request');
$PAGE->navbar->add($strheading, new moodle_url('/local/request/approveexem.php'));
$PAGE->navbar->add(get_string('viewdetails', 'local_request'));
$PAGE->set_title($strheading);

$data = array();
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('viewdetails', 'local_request'));

$request = $DB->get_record('local_request_courseexem', array('id' => $id));
$student = $requestss->requestedstudent($request);

$out = array();
$contextid = context_user::instance($request->studentid);
$fs = get_file_storage();
$params = array($contextid->id, 'user', 'draft', '.');
$files = $fs->get_area_files($contextid->id, 'user', 'draft', $request->attachment);
$url = "{$CFG->wwwroot}/local/request/draftfile.php/$contextid->id/user/draft";

foreach ($files as $file) {
    $filename = $file->get_filename();
    $fileurl = $url . $file->get_filepath() . $file->get_itemid() . '/' . $filename;
    if ($filename != '.')
        $out[] = html_writer::link($fileurl, $filename);
}
$br = html_writer::empty_tag('br');
$attachment = implode($br, $out);

$table = new html_table();
$table->data = array();
$table->data[] = array('<b>' . get_string('studentid', 'local_request') . '</b>', ': ' . $student->serviceid);
$table->data[] = array('<b>' . get_string('name', 'local_request') . '</b>', ': ' . $student->student);
$table->data[] = array('<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', ': ' . $DB->get_field('local_school', 'fullname', array('id' => $student->schoolid)));
$table->data[] = array('<b>' . get_string('program', 'local_programs') . '</b>', ': ' . $DB->get_field('local_program', 'fullname', array('id' => $student->programid)));
$table->data[] = array('<b>' . get_string('semester', 'local_semesters') . '</b>', ': ' . $student->semester);
$table->data[] = array('<b>' . get_string('course', 'local_cobaltcourses') . '</b>', ': ' . $student->course);
$table->data[] = array('<b>' . get_string('gradesobtained', 'local_request') . '</b>', ': ' . $student->grades);
$table->data[] = array('<b>' . get_string('attachment', 'local_request') . '</b>', ': ' . $attachment);
if ($student->registrarapproval == 1)
    $table->data[] = array('<b>' . get_string('status', 'local_users') . '</b>', ': ' . '<font color="green">' . get_string('approvedc', 'local_users') . '</font>');
if ($student->registrarapproval == 2)
    $table->data[] = array('<b>' . get_string('status', 'local_users') . '</b>', ': ' . '<font color="red">' . get_string('rejectedc', 'local_users') . '</font>');

$table->size = array('25%', '75%');
$table->align = array('left', 'left');
$table->width = '100%';
echo html_writer::table($table);

if ($approve) {
    echo '<form action="approveexem.php?id=' . $id . '&approve=1" method="post">';
    echo '<table style="width:50%" border="0"><tr><td>' . get_string('enter_grades', 'local_users') . ' :</td><td>';
    echo '<input type="text" name="grades" style="width:20%;margin-top:4%;" /> /100</td></tr>';
    echo '<tr><td></td><td><input type="submit" name="submit" value="submit" /></td></tr>';
    echo '</table>';
    echo '</form>';
} else if ($student->registrarapproval == 0) {
    echo '<br/><div style="width:25%;"><div style="width:50%;float:left;">';
    echo $OUTPUT->single_button(new moodle_url('/local/request/exemdetails.php', array('id' => $id, 'approve' => 1)), get_string('approve'));
    echo '</div><div style="width:50%;float:right;">';
    echo $OUTPUT->single_button(new moodle_url('/local/request/approveexem.php', array('id' => $id, 'reject' => 1)), get_string('reject'));
    echo '</div></div>';
}

echo $OUTPUT->footer();
?>