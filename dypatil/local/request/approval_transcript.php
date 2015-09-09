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
 * @subpackage approvals(transcripts)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/lib/lib.php');
$var = optional_param('x', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/approval_transcript.php');
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('transcriptapprovals', 'local_request');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$requestpro = new requests();
$data = array();
global $USER;
$url = '../../local/request/requesttranscript.php';
if ($var) {
    redirect($url);
}
$data = array();
echo $OUTPUT->header();
$requests = $DB->get_records('local_request_transcript');

foreach ($requests as $request) {
    $list = array();
    $buttons = array();
    $details = $DB->get_record_sql("select lud.schoolid,luc.semesterid,lud.userid,lud.serviceid,lud.programid
        from {local_userdata} lud
        INNER JOIN {local_user_clclasses} luc 
        ON lud.userid = luc.userid AND lud.userid = {$request->studentid}
        AND luc.semesterid = {$request->req_semester} group by lud.schoolid");
    if ($vals = $DB->get_records_sql("SELECT * FROM {local_school_permissions} where userid = '" . $USER->id . "' and schoolid='" . $details->schoolid . "'")) {

        $serviceid = $requestpro->service($details->schoolid, $details->programid, $request->studentid);
        $user = $requestpro->users($details->schoolid, $request->studentid);
        $list[] = $serviceid->serviceid;
        $list[] = $user->fullname;
        $list[] = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
        $list[] = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
        $list[] = $DB->get_field('local_semester', 'fullname', array('id' => $request->req_semester));
        $list[] = date("Y-m-d", $request->requested_date);
        $reg_approval = $request->reg_approval;
        if ($reg_approval == 0) {
            $list[] = get_string('pending', 'local_request');
        }
        if ($reg_approval == 1) {
            $list[] = get_string('approvedc', 'local_request');
        }
        if ($reg_approval == 2) {
            $list[] = get_string('rejectedc', 'local_request');
        }
        if ($reg_approval == 0) {
            $buttons[] = html_writer::link(new moodle_url('/local/request/transcriptpdf.php', array('id' => $request->id, 'semid' => $request->req_semester)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/check'), 'title' => get_string('approve', 'local_courseregistration'), 'alt' => get_string('approve', 'local_courseregistration'), 'class' => 'iconsmall')));

            $buttons[] = html_writer::link(new moodle_url('/local/request/reject.php', array('tid' => $request->id, 'reject' => 2)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('reject', 'local_courseregistration'), 'alt' => get_string('reject', 'local_courseregistration'), 'class' => 'iconsmall')));
            $list[] = implode(' ', $buttons);
        } else if ($reg_approval == 1) {
            $list[] = get_string('approvedc', 'local_request');
        } else
        if ($reg_approval == 2) {
            $list[] = get_string('rejectedc', 'local_request');
        }

        $data[] = $list;
    }
}
echo $OUTPUT->heading($strheading);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('transcriptapprovedes', 'local_request'));
}
echo "<br>";
echo $OUTPUT->box_start('generalbox');
if (!empty($data)) {
    $PAGE->requires->js('/local/request/js/approvetranscriptjs.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
$table = new html_table();
$table->id = "approvaltranscripttable";
$table->head = array(
    get_string('studentid', 'local_request'),
    get_string('name', 'local_request'),
    get_string('schoolid', 'local_collegestructure'),
    get_string('program', 'local_programs'),
    get_string('semester', 'local_semesters'),
    get_string('requesteddate', 'local_request'),
    get_string('status', 'local_request'),
    get_string('actions_req', 'local_request')
);
$table->size = array('12%', '13%', '10%', '8%', '13%', '13%', '13%', '5%');
$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (!isset($data))
    echo get_string('no_records', 'local_request');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>