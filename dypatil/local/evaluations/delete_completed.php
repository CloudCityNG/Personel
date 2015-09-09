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
 * prints the form to confirm the deleting of a completed
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
require_once('delete_completed_form.php');

$id = required_param('id', PARAM_INT);
$classid = required_param('clid', PARAM_INT);
$completedid = optional_param('completedid', 0, PARAM_INT);
$return = optional_param('return', 'entries', PARAM_ALPHA);

if ($completedid == 0) {
    print_error('no_complete_to_delete', 'evaluation', 'show_entries.php?id=' . $id . '&clid=' . $classid . '&do_show=showentries');
}

$PAGE->set_url('/local/evaluations/delete_completed.php', array('id' => $id, 'clid' => $classid, 'completed' => $completedid));

//if (! $cm = get_coursemodule_from_id('evaluation', $id)) {
//    print_error('invalidcoursemodule');
//}
//
//if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id))) {
    print_error('invalidevaluation');
}

//$context = context_module::instance($cm->id);
//
//require_login($course, true, $cm);
//
//require_capability('mod/evaluation:deletesubmissions', $context);

$mform = new evaluation_delete_completed_form();
$newformdata = array('id' => $id,
    'clid' => $classid,
    'completedid' => $completedid,
    'confirmdelete' => '1',
    'do_show' => 'edit',
    'return' => $return);
$mform->set_data($newformdata);
$formdata = $mform->get_data();

if ($mform->is_cancelled()) {
    if ($return == 'entriesanonym') {
        redirect('show_entries_anonym.php?id=' . $id . '&clid=' . $classid . '');
    } else {
        redirect('show_entries.php?id=' . $id . '&clid=' . $classid . '&do_show=showentries');
    }
}

if (isset($formdata->confirmdelete) AND $formdata->confirmdelete == 1) {
    if ($completed = $DB->get_record('evaluation_completed', array('id' => $completedid))) {
        evaluation_delete_completed($completedid);
        add_to_log($classid, 'evaluation', 'delete', 'view.php?id=' . $id, $evaluation->id
        );

        if ($return == 'entriesanonym') {
            redirect('show_entries_anonym.php?id=' . $id . '&clid=' . $classid . '');
        } else {
            redirect('show_entries.php?id=' . $id . '&clid=' . $classid . '&do_show=showentries');
        }
    }
}

/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

$PAGE->navbar->add(get_string('delete_entry', 'local_evaluations'));
//$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($evaluation->name));
echo $OUTPUT->header();

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
echo $OUTPUT->heading(format_text($evaluation->name));
echo $OUTPUT->box_start('generalbox errorboxcontent boxaligncenter boxwidthnormal');
echo print_string('confirmdeleteentry', 'local_evaluations');
echo '<div id="delquestion">';
$mform->display();
echo '</div>';
echo $OUTPUT->box_end();


echo $OUTPUT->footer();
