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
require_once('../../message/lib.php');
global $USER, $PAGE;

$approve = optional_param('approve', 0, PARAM_INT);
$reject = optional_param('reject', 0, PARAM_INT);
//$tabval = optional_param('mode', 'pending', PARAM_RAW);
$id = optional_param('id', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/approveexem.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
require_login();
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$request = new requests();
$strheading = get_string('approveexem', 'local_request');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$returnurl = new moodle_url($CFG->wwwroot . '/local/request/approveexem.php');
$data = array();
echo $OUTPUT->header();
$hierarchy = new hierarchy();
$requestss = new requests();
$time = time();
if ($approve) {
    /* records with the status approved */

    if (insert_courseexemgrades($id, $_POST)) {
        $student = $DB->get_record('local_request_courseexem', array('id' => $id));
        $userto = $DB->get_record('user', array('id' => $student->studentid));
        $userfrom = $DB->get_record('user', array('id' => $USER->id));
        $DB->set_field('local_request_courseexem', 'registrarapproval', 1, array('id' => $id));
        $DB->set_field('local_request_courseexem', 'regapprovedon', $time, array('id' => $id));
        $message = get_string('acceptmsgexem', 'local_request');
        $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
    }
    $style = array('style' => 'notifysuccess');
    $approvesuccess = get_string('approvesuccess', 'local_request');
    $hierarchy->set_confirmation($approvesuccess, $returnurl, $style);
}
if ($reject) {
    $student = $DB->get_record('local_request_courseexem', array('id' => $id));
    $userto = $DB->get_record('user', array('id' => $student->studentid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    /* records with the status rejected */
    $DB->set_field('local_request_courseexem', 'registrarapproval', 2, array('id' => $id));
    $DB->set_field('local_request_courseexem', 'regapprovedon', $time, array('id' => $id));
    $message = get_string('rejectmsgexem', 'local_request');
    $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
    $style = array('style' => 'notifyproblem');
    $rejectsuccess = get_string('rejectsuccess', 'local_request');
    $hierarchy->set_confirmation($rejectsuccess, $returnurl, $style);
}


$schoollist = $hierarchy->get_assignedschools();
$schoollist = $hierarchy->get_school_parent($schoollist, array(), false, false);
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$schoollist = array_keys($schoollist);
list($usql, $params) = $DB->get_in_or_equal($schoollist);
$requests = $DB->get_records_select('local_request_courseexem', 'schoolid ' . $usql, $params);
echo $OUTPUT->heading($strheading);
//$request->courseexemptiontabview($tabval);
foreach ($requests as $request) {
    $list = array();
    $buttons = array();
    $student = $requestss->requestedstudent($request);
    $list[] = $student->serviceid;
    $list[] = $student->student;
    $list[] = $student->semester;
    $list[] = $student->course;
    $list[] = $student->grades . '/100';
    $list[] = date('Y-m-d', $student->requestedon);

    $list[] = html_writer::tag('a', get_string('view'), array('href' => '' . $CFG->wwwroot . '/local/request/exemdetails.php?id=' . $request->id . ''));
    if ($request->registrarapproval == 0) {
        $buttons[] = html_writer::link($CFG->wwwroot . '/local/request/exemdetails.php?id=' . $request->id . '&approve=1', html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_request'), 'alt' => get_string('approve', 'local_request'), 'class' => 'iconsmall')));
        $buttons[] = '&nbsp;';
        $buttons[] = html_writer::link($CFG->wwwroot . '/local/request/approveexem.php?id=' . $request->id . '&reject=1', html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_request'), 'alt' => get_string('reject', 'local_request'), 'class' => 'iconsmall')));

        $list[] = implode(' ', $buttons);

        // $list[] = html_writer::tag('a', get_string('approve'), array('href' => ''.$CFG->wwwroot.'/local/request/exemdetails.php?id='.$request->id.'&approve=1')) //. ' ' . html_writer::tag('a', get_string('reject'), array('href' => ''.$CFG->wwwroot.'/local/request/approveexem.php?id='.$request->id.'&reject=1'));
    } else if ($request->registrarapproval == 1) {
        $list[] = get_string('approvedc', 'local_request');
    } else if ($request->registrarapproval == 2) {
        $list[] = get_string('rejectedc', 'local_request');
    }
    if ($request->registrarapproval == 0) {
        $list[] = get_string('pending', 'local_request');
    } else if ($request->registrarapproval == 1) {
        $list[] = get_string('approvedc', 'local_request');
    } else if ($request->registrarapproval == 2) {
        $list[] = get_string('rejectedc', 'local_request');
    }

    $data[] = $list;
}

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('pendingcourseexemption', 'local_request'));
}

if (!empty($data)) {
    $PAGE->requires->js('/local/request/approveexemjs.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
$table = new html_table();
$table->id = "approveexemtable";
$table->head = array(
    get_string('studentid', 'local_request'),
    get_string('name', 'local_request'),
    get_string('semester', 'local_semesters'),
    get_string('courseexemfrom', 'local_request'),
    get_string('gradesobtained', 'local_request'),
    get_string('requesteddate', 'local_request'),
    get_string('viewdetails', 'local_request'),
    get_string('status', 'local_request'),
    get_string('status', 'local_request')
);
$table->size = array('10%', '15%', '20%', '20%', '10%', '10%', '5%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data))
    echo get_string('no_records', 'local_request');
//}
echo $OUTPUT->footer();
?>
