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
 * @subpackage requsets(course exemption)
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once('../../local/request/lib/lib.php');
global $USER;
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/course_exem.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
$context = context_user::instance($USER->id);
if (!has_capability('local/clclasses:enrollclass', $context) || is_siteadmin()) {
    print_error('You dont have permissions');
}
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}
$strheading = get_string('requestcourseexem', 'local_request');
$PAGE->set_heading($strheading);
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$data = array();

$requestid = new requests();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('requisitionsforexem', 'local_request'));

if ($student = $DB->get_records('local_request_courseexem', array('studentid' => $USER->id))) {
    foreach ($student as $stu) {
        $list = array();
        $list[] = $DB->get_field('local_semester', 'fullname', array('id' => $stu->semesterid));
        $list[] = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $stu->courseid));
        $list[] = date("Y-m-d", $stu->requestedon);
        if ($stu->registrarapproval == 1) {
            $list[] = get_string('approvedc', 'local_request');
        } elseif ($stu->registrarapproval == 2) {
            $list[] = get_string('rejectedc', 'local_request');
        } else {
            $list[] = get_string('pending', 'local_request');
        }
        $data[] = $list;
    }
}

/* -Checking weather student have any current Semester- */

$currenttab = 'view';
$requestid->requestcourseexemtabs($currenttab);

$box = get_string('viewcourseexempage', 'local_request');
echo $OUTPUT->box($box);
$table = new html_table();
$table->id = 'requestcourseexem';
$table->head = array(
    get_string('semester', 'local_semesters'),
    get_string('courseexemfrom', 'local_request'),
    get_string('requested_date', 'local_request'),
    get_string('status', 'local_request')
);
$table->size = array('25%', '25%', '25%', '25%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data))
    echo get_string('no_records', 'local_request');

echo $OUTPUT->footer();
?>