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
 * @subpackage requsets(profile_change)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once('../../local/request/lib/lib.php');
$var = optional_param('x', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/approval/approval_profile.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {
    print_error('You dont have permissions');
}
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('profileapprovals', 'local_approval');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$requestpro = new requests();
$data = array();
global $USER;
$url = '../../local/request/requestprofile.php';
if ($var) {
    redirect($url);
}
$data = array();
echo $OUTPUT->header();
$requests = $DB->get_records('local_request_profile_change', array('reg_approval' => '0'));
foreach ($requests as $request) {
    $list = array();
    if ($vals = $DB->get_records_sql("SELECT * FROM {local_school_permissions} where userid = '" . $USER->id . "' and schoolid='" . $request->schoolid . "'")) {
        $serviceid = $requestpro->service($request->schoolid, $request->studentid);
        $list[] = $serviceid->serviceid;
        $list[] = $DB->get_field('local_school', 'fullname', array('id' => $request->schoolid));
        $list[] = $DB->get_field('local_program', 'fullname', array('id' => $request->programid));
        if ($request->subjectcode == 1) {
            $list[] = get_string('name', 'local_approval');
        }
        if ($request->subjectcode == 2) {
            $list[] = get_string('emailid', 'local_admission');
        }
        $list[] = "<a href='view.php?id=" . $request->id . "&flag=1'>" . $request->presentdata . "</a>";
        $list[] = $request->changeto;
        $list[] = date("Y-m-d", $request->requested_date);
        $reg_approval = $request->reg_approval;
        if ($reg_approval == 0) {
            $list[] = "<a href='../../local/profilechange/changeprofile.php?confirm=1&recid={$request->id}'>Confirm</a>";
        }
        if ($reg_approval == 1) {
            $list[] = get_string('approved', 'local_approval');
        }
        if ($reg_approval == 2) {
            $list[] = get_string('rejected', 'local_approval');
        }
        $data[] = $list;
    }
}
echo $OUTPUT->heading($strheading);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('profileapprovedes', 'local_approval'));
}
echo "<br>";
echo $OUTPUT->box_start('generalbox');
if (!empty($data)) {
    $PAGE->requires->js('/local/approval/approveprofilejs.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
$table = new html_table();
$table->id = "approvalprofiletable";
$table->head = array(
    get_string('studentid', 'local_approval'),
    get_string('schoolid', 'local_collegestructure'),
    get_string('program', 'local_programs'),
    get_string('subject', 'local_approval'),
    get_string('present_data', 'local_approval'),
    get_string('request_to_chage', 'local_approval'),
    get_string('requesteddate', 'local_approval'),
    get_string('status', 'local_approval')
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