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
 * Attendance report
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$pageparams = new local_att_report_page_params();

$id = required_param('id', PARAM_INT);
$from = optional_param('from', null, PARAM_ACTION);
$pageparams->view = optional_param('view', null, PARAM_INT);
$pageparams->curdate = optional_param('curdate', null, PARAM_INT);
$pageparams->group = optional_param('group', null, PARAM_INT);
$pageparams->sort = optional_param('sort', null, PARAM_INT);
$pageparams->page = optional_param('page', 1, PARAM_INT);
$pageparams->perpage = 10; //get_config('attendance', 'resultsperpage');
//$cm             = get_coursemodule_from_id('attendance', $id, 0, false, MUST_EXIST);
//$course         = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
//$att            = $DB->get_record('attendance', array('id' => $cm->instance), '*', MUST_EXIST);

$att = $DB->get_record('local_attendance', array('id' => $id), '*', MUST_EXIST);
$classinfo = $DB->get_record('local_clclasses', array('id' => $att->classid));
//$sacademicyearinfo  = $DB->get_record('local_academicyear', array('id' =>$sessioninfo ->academicyearid ));
$PAGE->set_context(context_system::instance());

require_login();

$pageparams->init($classinfo);
$att = new local_attendance($att, $classinfo, $PAGE->context, $pageparams);

$att->perm->require_view_reports_capability();

$PAGE->set_url($att->url_report());
$PAGE->set_pagelayout('report');
//$PAGE->set_title($course->shortname. ": ".$att->name.' - '.get_string('report', 'attendance'));
//$PAGE->set_heading($course->fullname);
$PAGE->set_cacheable(true);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add(get_string('report', 'local_attendance'));

$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance_tabs($att, local_attendance_tabs::TAB_REPORT);
$filtercontrols = new local_attendance_filter_controls($att, true);
$reportdata = new local_attendance_report_data($att);

$args = array($classinfo->id, 'attendance', 'report viewed', '/local/attendance/report.php?id=' . $id, '', $att->id);
//call_user_func_array('add_to_log', $args);
// Output starts here.

echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'local_attendance') . ' :: ');
echo $output->render($tabs);
echo $output->render($filtercontrols);
echo $output->render($reportdata);

echo $output->footer();

