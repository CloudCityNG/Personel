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
$PAGE->set_url('/local/request/request_id.php');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {

    print_error('You dont have permissions');
}

$strheading = get_string('requisitions_id', 'local_request');
$PAGE->set_title($strheading);
$PAGE->set_heading($strheading);
$PAGE->navbar->add($strheading);
$PAGE->navbar->add(get_string('view', 'local_request'));
$data = array();
global $USER;
$url = '../../local/request/requestid.php';
$requestid = new requests();
echo $OUTPUT->header();
if ($student = $DB->get_records('local_request_idcard', array('studentid' => $USER->id))) {
    foreach ($student as $students) {
        $list = array();
        $sql_pro = $DB->get_record_sql("select fullname from {local_program} where id=$students->programid");
        $list[] = $sql_pro->fullname;
        $list[] = $DB->get_field('local_semester', 'fullname', array('id' => $students->semesterid));
        $list[] = strtoupper(date("d-M-Y", $students->requested_date));
        if ($students->reg_approval == 1) {
            $list[] = get_string('approvedc', 'local_request');
        } elseif ($students->reg_approval == 2) {
            $list[] = get_string('rejectedc', 'local_request');
        } else {
            $list[] = get_string('pending', 'local_request');
        }
        $data[] = $list;
    }
}

echo $OUTPUT->heading(get_string('requisitions_id', 'local_request'));
/* link for requesting a for id card */
$currenttab = 'view';
$requestid->requestidtabview($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('requestiddes', 'local_request'));
}

if (!empty($data)) {
    $PAGE->requires->js('/local/request/js/requestidjs.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
/* display of all requests for id card which are still pending */
echo $OUTPUT->box_start('generalbox');
$table = new html_table();
$table->id = 'requestidtable';
$table->head = array(
    get_string('program', 'local_programs'),
    get_string('semester', 'local_semesters'),
    get_string('requested_date', 'local_request'),
    get_string('status', 'local_request')
);
$table->size = array('25%', '25%', '25%', '25%');
$table->align = array('center', 'center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (!isset($data))
    echo get_string('no_records', 'local_request');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>