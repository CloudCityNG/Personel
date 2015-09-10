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
 * print a printview of evaluation-items
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);
$classid = required_param('clid', PARAM_INT);

$PAGE->set_url('/local/evaluations/print.php', array('id' => $id, 'clid' => $classid));

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
//require_capability('mod/evaluation:view', $context);
//$PAGE->set_pagelayout('embedded');
/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

$evaluation_url = new moodle_url('/local/evaluations/index.php');
$PAGE->navbar->add($strevaluations, $evaluation_url);
$PAGE->navbar->add(format_string($evaluation->name));

$PAGE->set_title(format_string($evaluation->name));
//$PAGE->set_heading(format_string($course->fullname));
echo $OUTPUT->header();

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
echo $OUTPUT->heading(format_text($evaluation->name));

$evaluationitems = $DB->get_records('evaluation_item', array('evaluation' => $evaluation->id), 'position');
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//echo $OUTPUT->continue_button('view.php?id='.$id.'&clid='.$classid.'');
if (is_array($evaluationitems)) {
    $itemnr = 0;
    $align = right_to_left() ? 'right' : 'left';

    echo $OUTPUT->box_start('evaluation_items printview');
    //check, if there exists required-elements
    $params = array('evaluation' => $evaluation->id, 'required' => 1);
    $countreq = $DB->count_records('evaluation_item', $params);
    if ($countreq > 0) {
        echo '<span class="evaluation_required_mark">(*)';
        echo get_string('items_are_required', 'local_evaluations');
        echo '</span>';
    }
    //print the inserted items
    $itempos = 0;
    foreach ($evaluationitems as $evaluationitem) {
        echo $OUTPUT->box_start('evaluation_item_box_' . $align);
        $itempos++;
        //Items without value only are labels
        if ($evaluationitem->hasvalue == 1 AND $evaluation->autonumbering) {
            $itemnr++;
            echo $OUTPUT->box_start('evaluation_item_number_' . $align);
            echo $itemnr;
            echo $OUTPUT->box_end();
        }
        echo $OUTPUT->box_start('box generalbox boxalign_' . $align);
        if ($evaluationitem->typ != 'pagebreak') {
            evaluation_print_item_complete($evaluationitem, false, false);
        } else {
            echo $OUTPUT->box_start('evaluation_pagebreak');
            echo '<hr class="evaluation_pagebreak" />';
            echo $OUTPUT->box_end();
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->box(get_string('no_items_available_yet', 'local_evaluations'), 'generalbox boxaligncenter boxwidthwide');
}
//echo $OUTPUT->continue_button('view.php?id='.$id.'&clid='.$classid.'');
echo $OUTPUT->single_button('view.php?id=' . $id . '&clid=' . $classid . '', get_string('back', 'local_evaluations'), 'post');
echo $OUTPUT->box_end();
/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();

