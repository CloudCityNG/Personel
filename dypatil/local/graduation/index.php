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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Graduation
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/graduation/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $DB, $USER;

//$x = local_graduation_cron(); exit;
$systemcontext = context_system::instance();
// get the admin layout
$PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not
require_login();
$PAGE->set_context($systemcontext);
if (!has_capability('local/graduation:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$PAGE->set_url('/local/graduation/index.php');
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_graduation');
$PAGE->set_title($string);
$PAGE->navbar->add(get_string('graduation', 'local_graduation'));
$strheading = get_string('graduation', 'local_graduation');
// echo $OUTPUT->header();
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
// Heading of the page
$sql = "SELECT lg.* FROM {local_school_permissions} sp,{local_graduation} lg WHERE sp.userid={$USER->id}  AND sp.schoolid=lg.schoolid";
//$gradstu = $DB->get_records('local_graduation');
$gradstu = $DB->get_records_sql($sql);
$data = array();
foreach ($gradstu as $gradst) {

    $line = array();
    $line[] = $DB->get_field('local_userdata', 'serviceid', array('userid' => $gradst->userid));
    $line[] = $DB->get_field('user', 'firstname', array('id' => $gradst->userid));
    $line[] = $DB->get_field('local_program', 'fullname', array('id' => $gradst->programid));
    $line[] = $DB->get_field('local_curriculum', 'fullname', array('id' => $gradst->curriculumid));
    $line[] = $gradst->year;
    //$line[] =  '<button type="button" disabled>Download</button>';
    $line[] = '<a href="memo_pdf.php?curid=' . $gradst->curriculumid . '&userid=' . $gradst->userid . '&prgid=' . $gradst->programid . '&year=' . $gradst->year . '"><button type="button" >' . get_string('download', 'local_request') . '</button></a>';
    //$line[] = '<input type="button" value="Download" onclick="location.href="memo.php";/>';
    $data[] = $line;
}
//print_object($data);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_graduation'));
}
$PAGE->requires->js('/local/graduation/grad.js');

echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//  View Part starts
$table = new html_table();
$table->id = "graduationtable";
$table->head = array(
    get_string('studentid', 'local_graduation'), get_string('studentname', 'local_gradesubmission'), get_string('program', 'local_programs'), get_string('curriculum', 'local_curriculum'), get_string('year', 'local_graduation'), get_string('memo', 'local_graduation'));
$table->size = array('10%', '15%', '25%', '25%', '10%', '14%');
$table->align = array('left', 'left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
