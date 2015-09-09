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
 * Take Attendance
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');
require_once($CFG->dirroot . '/local/attendance/lib.php');

global $CFG, $DB, $USER, $OUTPUT;

$pageparams = new local_att_take_page_params();

$classid = optional_param('classid', 0, PARAM_INT);
$time = optional_param('time', 0, PARAM_TEXT);
 
if ($classid) {

    if (!$DB->record_exists('local_attendance', array('classid' => $classid))) {
        local_attendance_created_attendancesession_whilcron_failure($classid, $time);
    } else {

        $attendanceinfo = $DB->get_record('local_attendance', array('classid' => $classid));
        $id = $attendanceinfo->id;
        //$today = date('Y-m-d');
        $starttime = strtotime($time);
        $sql = "select * from {local_attendance_sessions} where attendanceid=$id and '{$starttime}' = sessdate  ";
        $sessioninfo = $DB->get_record_sql($sql);
        if (empty($sessioninfo)) {
            redirect($CFG->wwwroot . '/local/attendance/sessions.php?id=' . $id . '&action=1&fromcalendar=1');
        } else {
            $pageparams->sessionid = $sessioninfo->id;
            $pageparams->grouptype = 0;
        }
    }
} else {
    $id = required_param('id', PARAM_INT);
    $pageparams->sessionid = required_param('sessionid', PARAM_INT);
    $pageparams->grouptype = required_param('grouptype', PARAM_INT);
}

$pageparams->sort = optional_param('sort', null, PARAM_INT);
$pageparams->copyfrom = optional_param('copyfrom', null, PARAM_INT);
$pageparams->viewmode = optional_param('viewmode', null, PARAM_INT);
$pageparams->gridcols = optional_param('gridcols', null, PARAM_INT);
$pageparams->page = optional_param('page', 1, PARAM_INT);
$pageparams->perpage = optional_param('perpage', 10, PARAM_INT);



//$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
//$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);



$att = $DB->get_record('local_attendance', array('id' => $id), '*', MUST_EXIST);
$classinfo = $DB->get_record('local_clclasses', array('id' => $att->classid));
//$sacademicyearinfo        = $DB->get_record('local_academicyear', array('id' =>$sessioninfo ->academicyearid ));
$PAGE->set_context(context_system::instance());

require_login();

//$pageparams->group = local_groups_get_activity_group($cm, true);

$pageparams->init($classinfo);
$att = new local_attendance($att, $classinfo, $PAGE->context, $pageparams);

//if (!$att->perm->can_take_session($pageparams->grouptype)) {
//    $group = groups_get_group($pageparams->grouptype);
//    throw new moodle_exception('cannottakeforgroup', 'attendance', '', $group->name);
//}
if (($formdata = data_submitted()) && confirm_sesskey()) {
    $att->take_from_form_data($formdata);
}

$PAGE->set_url($att->url_take());
$PAGE->set_pagelayout('admin');
//$PAGE->set_title($course->shortname. ": ".$att->name);
//$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add($att->name);

$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance_tabs($att);
$sesstable = new local_attendance_take_data($att);

// Output starts here.

echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'local_attendance') . ' :: ');
echo $output->render($tabs);
echo $output->render($sesstable);

echo $output->footer();
