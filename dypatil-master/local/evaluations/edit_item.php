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
 * prints the form to edit a dedicated item
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");

evaluation_init_evaluation_session();

$cmid = required_param('cmid', PARAM_INT);
$classid = required_param('clid', PARAM_INT);
$typ = optional_param('typ', false, PARAM_ALPHA);
$id = optional_param('id', false, PARAM_INT);
$action = optional_param('action', false, PARAM_ALPHA);

$editurl = new moodle_url('/local/evaluations/edit.php', array('id' => $cmid, 'clid' => $classid));

if (!$typ) {
    redirect($editurl->out(false));
}

$url = new moodle_url('/local/evaluations/edit_item.php', array('cmid' => $cmid, 'clid' => $classid));
if ($typ !== false) {
    $url->param('typ', $typ);
}
if ($id !== false) {
    $url->param('id', $id);
}
$PAGE->set_url($url);

// set up some general variables
$usehtmleditor = can_use_html_editor();


if (($formdata = data_submitted()) AND ! confirm_sesskey()) {
    print_error('invalidsesskey');
}

//if (! $cm = get_coursemodule_from_id('evaluation', $cmid)) {
//    print_error('invalidcoursemodule');
//}
//if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $cmid))) {
    print_error('invalidevaluation');
}

//$context = context_module::instance($cm->id);
//require_login($course, true, $cm);
//require_capability('mod/evaluation:edititems', $context);
//if the typ is pagebreak so the item will be saved directly
if ($typ == 'pagebreak') {
    evaluation_create_pagebreak($evaluation->id);
    redirect($editurl->out(false));
    exit;
}

//get the existing item or create it
// $formdata->itemid = isset($formdata->itemid) ? $formdata->itemid : NULL;
if ($id and $item = $DB->get_record('evaluation_item', array('id' => $id))) {
    $typ = $item->typ;
} else {
    $item = new stdClass();
    $item->id = null;
    $item->position = -1;
    if (!$typ) {
        print_error('typemissing', 'local_evaluations', $editurl->out(false));
    }
    $item->typ = $typ;
    $item->options = '';
}

require_once($CFG->dirroot . '/local/evaluations/item/' . $typ . '/lib.php');

$itemobj = evaluation_get_item_class($typ);

$itemobj->build_editform($item, $evaluation, $cmid, $classid);

if ($itemobj->is_cancelled()) {
    redirect($editurl->out(false));
    exit;
}
if ($itemobj->get_data()) {
    if ($item = $itemobj->save_item()) {
        evaluation_move_item($item, $item->position);
        redirect($editurl->out(false));
    }
}

////////////////////////////////////////////////////////////////////////////////////
/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

if ($item->id) {
    $PAGE->navbar->add(get_string('edit_item', 'local_evaluations'));
} else {
    $PAGE->navbar->add(get_string('add_item', 'local_evaluations'));
}
//$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($evaluation->name));
echo $OUTPUT->header();
/// print the tabs
require('tabs.php');
/// Print the main part of the page
echo $OUTPUT->heading(format_text($evaluation->name));
//print errormsg
if (isset($error)) {
    echo $error;
}
$itemobj->show_editform();

if ($typ != 'label') {
    $PAGE->requires->js('/local/evaluations/evaluation.js');
    $PAGE->requires->js_function_call('set_item_focus', Array('id_itemname'));
}

/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();
