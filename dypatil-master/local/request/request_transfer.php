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
 * @subpackage requsets(idcard)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
$var = optional_param('x', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
require_once('../../local/request/lib/lib.php');
$PAGE->set_url('/local/request/request_transfer.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}
$strheading = get_string('requisitions_transfer', 'local_request');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('view', 'local_request'));
$data = array();
global $USER;
$url = '../../local/request/requestid.php';
$requestid = new requests();
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('requisitions_transfer', 'local_request'));


/* link for requesting a for id card */
$currenttab = 'view';
$requestid->requesttransfertabview($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('tranferdes', 'local_request'));
}
echo "<br>";
$details = $DB->get_records('local_request_transfer', array('studentid' => $USER->id));
foreach ($details as $detail) {
    $list = array();
    $school = $DB->get_record('local_school', array('id' => $detail->schoolid));
    $program = $DB->get_record('local_program', array('id' => $detail->programid));
    $list[] = $school->fullname;
    $list[] = $program->fullname;
    $semester = $requestid->semester($school->id, $program->id);
    foreach ($semester as $ses) {
        $value1 = $ses->fullname;
        $key1 = $ses->id;
    }
    $list[] = $value1;
    $list[] = date("Y-m-d", $detail->requested_date);
    if ($detail->approvalstatus == 0) {
        $list[] = get_string('pending', 'local_request');
    }
    if ($detail->approvalstatus == 1) {
        $list[] = get_string('approvedc', 'local_request');
    }
    if ($detail->approvalstatus == 2) {
        $list[] = get_string('rejectedc', 'local_request');
    }
    $data[] = $list;
}
if (!empty($data)) {
    $PAGE->requires->js('/local/request/js/requesttransferjs.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
/* display of all requests for id card which are still pending */
echo $OUTPUT->box_start('generalbox');
$table = new html_table();
$table->id = 'requesttransfertable';
$table->head = array(
    get_string('school_name', 'local_request'),
    get_string('program_name', 'local_request'),
    get_string('semester_name', 'local_request'),
    get_string('requested_date', 'local_request'),
    get_string('status', 'local_request')
);
$table->size = array('20', '25%', '25%', '20%', '10%');
$table->align = array('center', 'center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (!isset($data))
    echo get_string('no_records', 'local_request');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>