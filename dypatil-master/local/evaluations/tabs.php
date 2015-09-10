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
 * prints the tabbed bar
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package feedback
 */
defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row = array();
$inactive = array();
$activated = array();

//some pages deliver the cmid instead the id
//if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
//   $usedid = $cmid;
//} else {
//   $usedid = $id;
//}
//$context = context_module::instance($usedid);
//$courseid = optional_param('courseid', false, PARAM_INT);
// $current_tab = $SESSION->feedback->current_tab;
echo $OUTPUT->heading(get_string('pluginname', 'local_evaluations'));
if (!isset($current_tab)) {
    $current_tab = '';
}

if (isset($id) && $id > 0) {
    $row[] = new tabobject('view', new moodle_url('/local/evaluations/index.php'), get_string('view', 'local_evaluations'));
    if (has_capability('local/evaluations:addinstance', context_system::instance())) {
        $row[] = new tabobject('editevaluation', new moodle_url('/local/evaluations/create_evaluation.php', array('id' => $id, 'clid' => $classid)), get_string('create', 'local_evaluations'));
    }
    $viewurl = new moodle_url('/local/evaluations/view.php', array('id' => $id, 'clid' => $classid, 'do_show' => 'view'));
    $row[] = new tabobject('overview', $viewurl->out(), get_string('overview', 'local_evaluations'));

    if (isset($_REQUEST['gopage'])) {
        $row[] = new tabobject('completeevl', new moodle_url('/local/evaluations/complete.php'), get_string('complete', 'local_evaluations'));
    }

    if (has_capability('local/evaluations:addinstance', context_system::instance())) {
        $editurl = new moodle_url('/local/evaluations/edit.php', array('id' => $id, 'clid' => $classid, 'do_show' => 'edit'));
        $row[] = new tabobject('edit', $editurl->out(), get_string('edit_items', 'local_evaluations'));

        $templateurl = new moodle_url('/local/evaluations/edit.php', array('id' => $id, 'clid' => $classid, 'do_show' => 'templates'));
        $row[] = new tabobject('templates', $templateurl->out(), get_string('templates', 'local_evaluations'));
    }

//if (has_capability('mod/feedback:viewreports', $context)) {
    //if ($feedback->course == SITEID) {
//        $url_params = array('id'=>$usedid, 'courseid'=>$courseid, 'do_show'=>'analysis');
    //       $analysisurl = new moodle_url('/local/evaluations/analysis_course.php', $url_params);
    //      $row[] = new tabobject('analysis',
    //                             $analysisurl->out(),
    //                            get_string('analysis', 'local_evaluations'));
    //  } else {
    if (has_capability('local/evaluations:addinstance', context_system::instance())) {
        $url_params = array('id' => $id, 'clid' => $classid, 'do_show' => 'analysis');
        $analysisurl = new moodle_url('/local/evaluations/analysis.php', $url_params);
        $row[] = new tabobject('analysis', $analysisurl->out(), get_string('analysis', 'local_evaluations'));
    }
    //}
    if (has_capability('local/evaluations:addinstance', context_system::instance())) {
        $url_params = array('id' => $id, 'clid' => $classid, 'do_show' => 'showentries');
        $reporturl = new moodle_url('/local/evaluations/show_entries.php', $url_params);
        $row[] = new tabobject('showentries', $reporturl->out(), get_string('show_entries', 'local_evaluations'));
    }


    // if ($feedback->anonymous == FEEDBACK_ANONYMOUS_NO AND $feedback->course != SITEID) {
    //$nonrespondenturl = new moodle_url('/local/evaluations/show_nonrespondents.php', array('id'=>$usedid));
    //$row[] = new tabobject('nonrespondents',
    //                        $nonrespondenturl->out(),
    //                        get_string('show_nonrespondents', 'local_evaluations'));
    //}
//}
}
if (count($row) > 1) {
    $tabs[] = $row;

    print_tabs($tabs, $current_tab, $inactive, $activated);
}

