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
 * print the confirm dialog to use template and create new items from template
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
require_once('use_templ_form.php');

$id = required_param('id', PARAM_INT);
$classid = required_param('clid', PARAM_INT);
$templateid = optional_param('templateid', false, PARAM_INT);
$deleteolditems = optional_param('deleteolditems', 0, PARAM_INT);

if (!$templateid) {
    redirect('edit.php?id=' . $id . '&clid=' . $classid . '');
}

$url = new moodle_url('/local/evaluations/use_templ.php', array('id' => $id, 'clid' => $classid, 'templateid' => $templateid));
if ($deleteolditems !== 0) {
    $url->param('deleteolditems', $deleteolditems);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('admin');
$context = context_system::instance();
require_login();
$PAGE->set_context($context);

//if (! $cm = get_coursemodule_from_id('evaluation', $id)) {
//    print_error('invalidcoursemodule');
//}
//
//if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id))) {
    print_error('invalidcoursemodule');
}

//$context = context_module::instance($cm->id);
//require_login($course, true, $cm);
//require_capability('mod/evaluation:edititems', $context);

$mform = new evaluation_use_templ_form();
$newformdata = array('id' => $id,
    'templateid' => $templateid,
    'clid' => $classid,
    'confirmadd' => '1',
    'deleteolditems' => '1',
    'do_show' => 'edit');
$mform->set_data($newformdata);
$formdata = $mform->get_data();

if ($mform->is_cancelled()) {
    redirect('edit.php?id=' . $id . '&clid=' . $classid . '&do_show=templates');
}

if (isset($formdata->confirmadd) AND $formdata->confirmadd == 1) {
    evaluation_items_from_template($evaluation, $templateid, $deleteolditems);
    redirect('edit.php?id=' . $id . '&clid=' . $classid . '');
}

/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

//$PAGE->navbar->add($strevaluations, new moodle_url('/local/evaluations/index.php', array('id'=>$classid)));
$PAGE->navbar->add(format_string($evaluation->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($evaluation->name));

echo $OUTPUT->header();

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
echo $OUTPUT->heading(format_text($evaluation->name));

echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthnormal');
echo $OUTPUT->heading(get_string('confirmusetemplate', 'local_evaluations'));

$mform->display();

echo $OUTPUT->box_end();

$templateitems = $DB->get_records('evaluation_item', array('template' => $templateid), 'position');
if (is_array($templateitems)) {
    $templateitems = array_values($templateitems);
}

if (is_array($templateitems)) {
    $itemnr = 0;
    $align = right_to_left() ? 'right' : 'left';
    echo $OUTPUT->box_start('evaluation_items');
    foreach ($templateitems as $templateitem) {
        echo $OUTPUT->box_start('evaluation_item_box_' . $align);
        if ($templateitem->hasvalue == 1 AND $evaluation->autonumbering) {
            $itemnr++;
            echo $OUTPUT->box_start('evaluation_item_number_' . $align);
            echo $itemnr;
            echo $OUTPUT->box_end();
        }
        echo $OUTPUT->box_start('box generalbox boxalign_' . $align);
        if ($templateitem->typ != 'pagebreak') {
            // echo '<div class="evaluation_item_'.$align.'">';
            evaluation_print_item_preview($templateitem);
        } else {
            echo $OUTPUT->box_start('evaluation_pagebreak');
            echo get_string('pagebreak', 'local_evaluations') . '<hr class="evaluation_pagebreak" />';
            echo $OUTPUT->box_end();
        }
        echo $OUTPUT->box_end();
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->box(get_string('no_items_available_at_this_template', 'local_evaluations'), 'generalbox boxaligncenter boxwidthwide');
}

echo $OUTPUT->footer();

