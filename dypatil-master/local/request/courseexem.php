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
require_once('../../local/request/courseexem_form.php');
require_once('../../local/request/lib/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/formslib.php');

$id = optional_param('id', -1, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/request/courseexem.php');
$systemcontext = context_system::instance();
require_login();
$context = context_system::instance();
$PAGE->set_context($systemcontext);

if (!isloggedin() || isguestuser()) {
    print_error('You dont have permissions');
}
$returnurl = new moodle_url($CFG->wwwroot . '/local/request/course_exem.php');
$hierarchy = new hierarchy();
$requestid = new requests();
/* -Checking weather student have any current Semester- */

$conf = new object();
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('requestcourseexem', 'local_request');
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$tool = new stdClass();
$tool->id = -1;

$maxfiles = 99;
$maxbytes = $CFG->maxbytes;
$definitionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $context);
$attachmentoptions = array('subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes);
$myform = new courseexem_form(null, array('definitionoptions' => $definitionoptions, 'attachmentoptions' => $attachmentoptions));
$myform->set_data($tool);
$context = context_system::instance();
global $USER;
if ($myform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $myform->get_data()) {
    if ($record = $DB->get_record('local_request_courseexem', array('studentid' => $USER->id, 'semesterid' => $data->semesterid, 'courseid' => $data->courseid))) {
        $conf->course = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $record->courseid));
        $conf->status = get_string('pending', 'local_request');
        if ($record->registrarapproval == 1) {
            $conf->status = get_string('approvedc', 'local_request');
        }
        if ($record->registrarapproval == 2) {
            $conf->status = get_string('rejectedc', 'local_request');
        }
        $message = get_string('recordexists', 'local_request', $conf);
        $style = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
    $data->requestedon = time();
    $data->registrarapproval = 0;
    $data->studentid = $USER->id;

    $data->id = $DB->insert_record('local_request_courseexem', $data);

    $data = file_postupdate_standard_editor($data, 'definition', $definitionoptions, $context, 'user', 'draft', $data->id);
    $data = file_postupdate_standard_filemanager($data, 'attachment', $attachmentoptions, $context, 'user', 'draft', $data->id);

    $message = get_string('requestsentsuccess', 'local_request', $conf);
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$currenttab = 'request';
$requestid->requestcourseexemtabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('courseexemdesc', 'local_request'));
}
/* -Checking weather student have any current Semester- */
$schools = $DB->get_records('local_userdata', array('userid' => $USER->id));
$semlist = array();
$cursem = false;
foreach ($schools as $school) {
    $semlist[] = $requestid->currentsemester($school->schoolid);
}
$semlist = array_filter($semlist); //to remove the null values.
$cursem = !empty($semlist) ? true : false;
if (!$cursem) { //If there are no current Semester, display the information for the Student.
    echo $OUTPUT->box(get_string('notefornocurrentsemester', 'local_request'));
} else {
    $myform->display();
}
echo $OUTPUT->footer();
?>