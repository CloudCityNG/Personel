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
 * @subpackage scheduleexam
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/edit_form.php');
require_once('../../course/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$systemcontext =context_system::instance();
if (!has_capability('local/scheduleexam:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$hierarchy = new hierarchy();
global $exams;
$exams = new schedule_exam();

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$currenttab = optional_param('mode', 'create', PARAM_RAW);

if ($id > 0) {
    if (!($tool = $DB->get_record('local_scheduledexams', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_scheduleexam');
    }
    $tool->opendate = date("m/d/Y", $tool->opendate);
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_heading($SITE->fullname);

$PAGE->set_url('/local/scheduleexam/edit.php', array('id' => $id));

$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);

$returnurl = new moodle_url('/local/scheduleexam/index.php', array('id' => $id));

$strheading = get_string('pageheading', 'local_scheduleexam');

$PAGE->navbar->add(get_string('pageheading', 'local_scheduleexam'), new moodle_url('/local/scheduleexam/index.php', array('id' => $id)));

$title = ($id > 0) ? get_string('editexamheader', 'local_scheduleexam') : get_string('createexamheader', 'local_scheduleexam');
$PAGE->navbar->add($title);
$PAGE->set_title(get_string('pageheading', 'local_scheduleexam') . ': ' . $title);

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {

        $classid = $DB->get_field("local_scheduledexams", 'classid', array('id' => $id));
        $class_name = $DB->get_field("local_clclasses", 'fullname', array('id' => $classid));
        $co_id = $DB->get_field("local_clclasses", 'cobaltcourseid', array('id' => $classid));
        $sql_msg = "SELECT extyp.examtype,ltyp.lecturetype,s.fullname as semestername,co.fullname as coursename FROM {local_scheduledexams} e,{local_examtypes} extyp,{local_lecturetype} ltyp,{local_semester} s,{local_cobaltcourses} co where e.id={$id} and e.examtype=extyp.id and e.lecturetype=ltyp.id and e.semesterid=s.id and co.id={$co_id}";
        $params = $DB->get_records_sql($sql_msg);

        foreach ($params as $param) {
            $msg = new object();
            $msg->exam_name = $param->examtype . '-' . $param->lecturetype;
            $msg->course_name = $param->coursename;
            $msg->sem_name = $param->semestername;
        }
        $msg->classname = $class_name;
        // Delete
        $dres = $exams->scheduleexam_delete_instance($id); //exit;
        if ($dres == 1) {
            $message = get_string('deletesuccess', 'local_scheduleexam', $msg);
            $options = array('style' => 'notifysuccess');
        }
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deletescheduleexam', 'local_scheduleexam');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('pageheading', 'local_scheduleexam') . ': ' . $strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $check = $DB->get_records("local_user_examgrades", array("examid" => $id));
    $message = get_string('message', 'local_scheduleexam');
    if ($check) {
        $message = '<h3>' . $message . '</h3>';
        echo '<div align="center">';
        echo $OUTPUT->box($message);
        echo '<br/>';
        echo $OUTPUT->continue_button($returnurl);
        echo '</div>';
    } else {
        $yesurl = new moodle_url('/local/scheduleexam/edit.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_scheduleexam');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}

//to hide or unhide 
if ($visible != -1 and $id and confirm_sesskey()) {
    $result = $DB->set_field('local_scheduledexams', 'visible', $visible, array('id' => $id));
    $data->visible = $DB->get_field('local_scheduledexams', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('success', 'local_scheduleexam', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_scheduleexam', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

if ($id > 0) {
    if (!($tool = $DB->get_record('local_scheduledexams', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_gradeletter');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$scheduleexamobj = new edit_form(NULL, array('id' => $id));
$scheduleexamobj->set_data($tool);

if ($scheduleexamobj->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $scheduleexamobj->get_data()) {

    $data->timecreated = time();
    $data->timemodified = time();
    $data->usermodified = $USER->id;


    if ($data->id > 0) {

        $co_id = $DB->get_field("local_clclasses", 'cobaltcourseid', array('id' => $data->classid));
        $class_name = $DB->get_field("local_clclasses", 'fullname', array('id' => $data->classid));
        $sql_msg = "SELECT extyp.examtype,ltyp.lecturetype,s.fullname as semestername,co.fullname as coursename FROM {local_scheduledexams} e,{local_examtypes} extyp,{local_lecturetype} ltyp,{local_semester} s,{local_cobaltcourses} co where e.id={$data->id} and e.examtype=extyp.id and e.lecturetype=ltyp.id and e.semesterid=s.id and co.id={$co_id}";
        $params = $DB->get_records_sql($sql_msg);

        foreach ($params as $param) {
            $msg = new object();
            $msg->exam_name = $param->examtype . '-' . $param->lecturetype;
            $msg->course_name = $param->coursename;
            $msg->sem_name = $param->semestername;
        }
        $msg->classname = $class_name;
        // Update
        $ures = $exams->scheduleexam_update_instance($data);
        if ($ures == 1) {
            $message = get_string('updatesuccess', 'local_scheduleexam', $msg);
            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        }
    } else {

        $cres = $exams->scheduleexam_add_instance($data); //exit;
        $co_id = $DB->get_field("local_clclasses", 'cobaltcourseid', array('id' => $data->classid));
        $class_name = $DB->get_field("local_clclasses", 'fullname', array('id' => $data->classid));
        $sql_msg = "SELECT extyp.examtype,ltyp.lecturetype,s.fullname as semestername,co.fullname as coursename FROM {local_scheduledexams} e,{local_examtypes} extyp,{local_lecturetype} ltyp,{local_semester} s,{local_cobaltcourses} co where e.id={$cres} and e.examtype=extyp.id and e.lecturetype=ltyp.id and e.semesterid=s.id and co.id={$co_id}";
        $params = $DB->get_records_sql($sql_msg);

        foreach ($params as $param) {
            $msg = new object();
            $msg->exam_name = $param->examtype . '-' . $param->lecturetype;
            $msg->course_name = $param->coursename;

            $msg->sem_name = $param->semestername;
        }
        $msg->classname = $class_name;
        // Create new
        if ($cres) {
            $message = get_string('createsuccess', 'local_scheduleexam', $msg);
            $options = array('style' => 'notifysuccess');
            $hierarchy->set_confirmation($message, $returnurl, $options);
        }
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pageheading', 'local_scheduleexam'));
$currenttab = 'create';
$exams->tabs($currenttab, $id);
if ($id < 0)
    echo $OUTPUT->box(get_string('createexamdes', 'local_scheduleexam'));
else
    echo $OUTPUT->box(get_string('editexamdes', 'local_scheduleexam'));
$scheduleexamobj->display();
echo $OUTPUT->footer();
