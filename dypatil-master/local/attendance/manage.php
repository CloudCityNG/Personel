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
 * Manage attendance sessions
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/locallib.php');

$pageparams = new local_att_manage_page_params();

$id = required_param('id', PARAM_INT);
$from = optional_param('from', null, PARAM_ALPHANUMEXT);
$pageparams->view = optional_param('view', null, PARAM_INT);
$pageparams->curdate = optional_param('curdate', null, PARAM_INT);
$pageparams->perpage = 10; //get_config('local_attendance', 'resultsperpage');

$att = $DB->get_record('local_attendance', array('id' => $id), '*', MUST_EXIST);
$classinfo = $DB->get_record('local_clclasses', array('id' => $att->classid));
$PAGE->set_context(context_system::instance());


$pageparams->init($classinfo);

$att = new local_attendance($att, $classinfo, $PAGE->context, $pageparams);
if (!$att->perm->can_manage() && !$att->perm->can_take() && !$att->perm->can_change()) {
    redirect($att->url_view());
}

// If teacher is coming from block, then check for a session exists for today.
if ($from === 'block') {
    $sessions = $att->get_today_sessions();
    $size = count($sessions);
    if ($size == 1) {
        $sess = reset($sessions);
        $nottaken = !$sess->lasttaken && has_capability('mod/attendance:takeattendances', $PAGE->context);
        $canchange = $sess->lasttaken && has_capability('mod/attendance:changeattendances', $PAGE->context);
        if ($nottaken || $canchange) {
            redirect($att->url_take(array('sessionid' => $sess->id, 'grouptype' => $sess->groupid)));
        }
    } else if ($size > 1) {
        $att->curdate = $today;
        // Temporarily set $view for single access to page from block.
        $att->view = LOCAL_ATT_VIEW_DAYS;
    }
}

//print_object($att);
$PAGE->set_url($att->url_manage());
$PAGE->set_pagelayout('admin');
//$PAGE->set_title($course->shortname. ": ".$att->name);
//$PAGE->set_cacheable(true);
//$PAGE->set_button($OUTPUT->update_module_button($cm->id, 'attendance'));
$PAGE->navbar->add('test');

//creating object for renderer file
$output = $PAGE->get_renderer('local_attendance');
$tabs = new local_attendance_tabs($att, local_attendance_tabs::TAB_SESSIONS);
$filtercontrols = new local_attendance_filter_controls($att);
$sesstable = new local_attendance_manage_data($att);

//echo 'session table info';
//print_object($sesstable);
// Output starts here.

echo $output->header();
echo $output->heading(get_string('attendanceforthecourse', 'local_attendance') . ' :: ' . $att->name);
echo $output->render($tabs);
echo $output->render($filtercontrols);
echo $output->render($sesstable);

echo $output->footer();

