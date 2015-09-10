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
 * prints the form to edit the evaluation items such moving, deleting and so on
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once('../../config.php');
require_once('lib.php');
require_once('edit_form.php');
error_reporting(0);
ob_start();
evaluation_init_evaluation_session();

$id = required_param('id', PARAM_INT);
$classid = required_param('clid', PARAM_INT);

if (($formdata = data_submitted()) AND ! confirm_sesskey()) {
    print_error('invalidsesskey');
}

$do_show = optional_param('do_show', 'edit', PARAM_ALPHA);
$moveupitem = optional_param('moveupitem', false, PARAM_INT);
$movedownitem = optional_param('movedownitem', false, PARAM_INT);
$moveitem = optional_param('moveitem', false, PARAM_INT);
$movehere = optional_param('movehere', false, PARAM_INT);
$switchitemrequired = optional_param('switchitemrequired', false, PARAM_INT);

$current_tab = $do_show;
$PAGE->set_pagelayout('admin');
$systemcontext =context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
require_capability('local/evaluations:addinstance', context_system::instance());
$url = new moodle_url('/local/evaluations/edit.php', array('id' => $id, 'clid' => $classid, 'do_show' => $do_show));

//if (! $cm = get_coursemodule_from_id('evaluation', $id)) {
//    print_error('invalidcoursemodule');
//}
//if (! $course = $DB->get_record('course', array('id'=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (!$evaluation = $DB->get_record('local_evaluation', array('id' => $id))) {
    print_error('invalidevaluation');
}

//$context = context_module::instance($cm->id);
//require_login($course, true, $cm);
//require_capability('mod/evaluation:edititems', $context);
//Move up/down items
if ($moveupitem) {
    $item = $DB->get_record('evaluation_item', array('id' => $moveupitem));
    evaluation_moveup_item($item);
}
if ($movedownitem) {
    $item = $DB->get_record('evaluation_item', array('id' => $movedownitem));
    evaluation_movedown_item($item);
}

//Moving of items
if ($movehere && isset($SESSION->evaluation->moving->movingitem)) {
    $item = $DB->get_record('evaluation_item', array('id' => $SESSION->evaluation->moving->movingitem));
    evaluation_move_item($item, intval($movehere));
    $moveitem = false;
}
if ($moveitem) {
    $item = $DB->get_record('evaluation_item', array('id' => $moveitem));
    $SESSION->evaluation->moving->shouldmoving = 1;
    $SESSION->evaluation->moving->movingitem = $moveitem;
} else {
    unset($SESSION->evaluation->moving);
}

if ($switchitemrequired) {
    $item = $DB->get_record('evaluation_item', array('id' => $switchitemrequired));
    @evaluation_switch_item_required($item);
    redirect($url->out(false));
    exit;
}

//The create_template-form
$create_template_form = new evaluation_edit_create_template_form();
$create_template_form->set_evaluationdata(array('context' => $systemcontext, 'clid' => $classid));
$create_template_form->set_form_elements();
$create_template_form->set_data(array('id' => $id, 'clid' => $classid, 'do_show' => 'templates'));
$create_template_formdata = $create_template_form->get_data();
if (isset($create_template_formdata->savetemplate) && $create_template_formdata->savetemplate == 1) {
    //Check the capabilities to create templates.
    //if (!has_capability('mod/evaluation:createprivatetemplate', $context) AND
    //        !has_capability('mod/evaluation:createpublictemplate', $context)) {
    //    print_error('cannotsavetempl', 'evaluation');
    //}
    if (trim($create_template_formdata->templatename) == '') {
        $savereturn = 'notsaved_name';
    } else {
        //If the evaluation is located on the frontpage then templates can be public.
        //     if (has_capability('mod/evaluation:createpublictemplate', get_system_context())) {
        $create_template_formdata->ispublic = isset($create_template_formdata->ispublic) ? 1 : 0;
        //       } else {
        //         $create_template_formdata->ispublic = 0;
        //     }
        if (!evaluation_save_as_template($evaluation, $create_template_formdata->templatename, $create_template_formdata->ispublic)) {
            $savereturn = 'failed';
        } else {
            $savereturn = 'saved';
        }
    }
}

//Get the evaluationitems
$lastposition = 0;
$evaluationitems = $DB->get_records('evaluation_item', array('evaluation' => $evaluation->id), 'position');
if (is_array($evaluationitems)) {
    $evaluationitems = array_values($evaluationitems);
    if (count($evaluationitems) > 0) {
        $lastitem = $evaluationitems[count($evaluationitems) - 1];
        $lastposition = $lastitem->position;
    } else {
        $lastposition = 0;
    }
}
$lastposition++;


//The add_item-form
$add_item_form = new evaluation_edit_add_question_form('edit_item.php');
$add_item_form->set_data(array('cmid' => $id, 'clid' => $classid, 'position' => $lastposition));

//The use_template-form
$use_template_form = new evaluation_edit_use_template_form('use_templ.php');
$use_template_form->set_evaluationdata(array('clid' => $classid));
$use_template_form->set_form_elements();
$use_template_form->set_data(array('id' => $id, 'clid' => $classid));

//Print the page header.
$strevaluations = get_string('modulenameplural', 'local_evaluations');
$strevaluation = get_string('modulename', 'local_evaluations');

$PAGE->set_url('/local/evaluations/edit.php', array('id' => $id, 'clid' => $classid, 'do_show' => $do_show));
//$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($evaluation->name));

//Adding the javascript module for the items dragdrop.
if (count($evaluationitems) > 1) {
    if ($do_show == 'edit' and $CFG->enableajax) {
        $PAGE->requires->strings_for_js(array(
            'pluginname',
            'move_item',
            'position',
                ), 'local_evaluations');
        $PAGE->requires->yui_module('moodle-local_evaluations-dragdrop', 'M.local_evaluations.init_dragdrop', array(array('cmid' => $id)));
    }
}

echo $OUTPUT->header();

/// print the tabs
require('tabs.php');

/// Print the main part of the page.
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

$savereturn = isset($savereturn) ? $savereturn : '';

//Print the messages.
if ($savereturn == 'notsaved_name') {
    echo '<p align="center"><b><font color="red">' .
    get_string('name_required', 'local_evaluations') .
    '</font></b></p>';
}

if ($savereturn == 'saved') {
    echo '<p align="center"><b><font color="green">' .
    get_string('template_saved', 'local_evaluations') .
    '</font></b></p>';
	header( "refresh:2;url='$CFG->wwwroot/local/evaluations/edit.php?id=$id&clid=$classid&do_show=templates'" );
}

if ($savereturn == 'failed') {
    echo '<p align="center"><b><font color="red">' .
    get_string('saving_failed', 'local_evaluations') .
    '</font></b></p>';
}

///////////////////////////////////////////////////////////////////////////
///Print the template-section.
///////////////////////////////////////////////////////////////////////////
if ($do_show == 'templates') {
    echo $OUTPUT->box(get_string('template_eval', 'local_evaluations'));
    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    $use_template_form->display();

    // if (has_capability('mod/evaluation:createprivatetemplate', $context) OR
    //             has_capability('mod/evaluation:createpublictemplate', $context)) {
    $deleteurl = new moodle_url('/local/evaluations/delete_template.php', array('id' => $id, 'clid' => $classid));
    $create_template_form->display();
    echo '<p><a href="' . $deleteurl->out() . '">' .
    get_string('delete_templates', 'local_evaluations') .
    '</a></p>';
    //} else {
    //    echo '&nbsp;';
    //}
    //   if (has_capability('mod/evaluation:edititems', $context)) {
    $urlparams = array('action' => 'exportfile', 'id' => $id, 'clid' => $classid);
    $exporturl = new moodle_url('/local/evaluations/export.php', $urlparams);
    $importurl = new moodle_url('/local/evaluations/import.php', array('id' => $id, 'clid' => $classid));
    echo '<p>
            <a href="' . $exporturl->out() . '">' . get_string('export_questions', 'local_evaluations') . '</a>/
            <a href="' . $importurl->out() . '">' . get_string('import_questions', 'local_evaluations') . '</a>
        </p>';
    // }
    echo $OUTPUT->box_end();
}
///////////////////////////////////////////////////////////////////////////
///Print the Item-Edit-section.
///////////////////////////////////////////////////////////////////////////
if ($do_show == 'edit') {
    echo $OUTPUT->box(get_string('edit_eval', 'local_evaluations'));
    $add_item_form->display();

    if (is_array($evaluationitems)) {
        $itemnr = 0;

        $align = right_to_left() ? 'right' : 'left';

        $helpbutton = $OUTPUT->help_icon('preview', 'local_evaluations');

        echo $OUTPUT->heading($helpbutton . get_string('preview', 'local_evaluations'));
        if (isset($SESSION->evaluation->moving) AND $SESSION->evaluation->moving->shouldmoving == 1) {
            $anker = '<a href="edit.php?id=' . $id . '&clid=' . $classid . '">';
            $anker .= get_string('cancel_moving', 'local_evaluations');
            $anker .= '</a>';
            echo $OUTPUT->heading($anker);
        }

        //Check, if there exists required-elements.
        $params = array('evaluation' => $evaluation->id, 'required' => 1);
        $countreq = $DB->count_records('evaluation_item', $params);
        if ($countreq > 0) {
            echo '<span class="evaluation_required_mark">(*)';
            echo get_string('items_are_required', 'local_evaluations');
            echo '</span>';
        }

        //Use list instead a table
        echo $OUTPUT->box_start('evaluation_items');
        if (isset($SESSION->evaluation->moving) AND $SESSION->evaluation->moving->shouldmoving == 1) {
            $moveposition = 1;
            $movehereurl = new moodle_url($url, array('movehere' => $moveposition));
            //Only shown if shouldmoving = 1
            echo $OUTPUT->box_start('evaluation_item_box_' . $align . ' clipboard');
            $buttonlink = $movehereurl->out();
            $strbutton = get_string('move_here', 'local_evaluations');
            $src = $OUTPUT->pix_url('movehere');
            echo '<a title="' . $strbutton . '" href="' . $buttonlink . '">
                    <img class="movetarget" alt="' . $strbutton . '" src="' . $src . '" />
                  </a>';
            echo $OUTPUT->box_end();
        }
        //Print the inserted items
        $itempos = 0;
        echo '<div id="evaluation_dragarea">'; //The container for the dragging area
        echo '<ul id="evaluation_draglist">'; //The list what includes the draggable items
        foreach ($evaluationitems as $evaluationitem) {
            $itempos++;
            //Hiding the item to move
            if (isset($SESSION->evaluation->moving)) {
                if ($SESSION->evaluation->moving->movingitem == $evaluationitem->id) {
                    continue;
                }
            }
            //Here come the draggable items, each one in a single li-element.
            echo '<li class="evaluation_itemlist generalbox" id="evaluation_item_' . $evaluationitem->id . '">';
            echo '<span class="spinnertest"> </span>';
            if ($evaluationitem->dependitem > 0) {
                $dependstyle = ' evaluation_depend';
            } else {
                $dependstyle = '';
            }
            echo $OUTPUT->box_start('evaluation_item_box_' . $align . $dependstyle, 'evaluation_item_box_' . $evaluationitem->id);
            //Items without value only are labels
            if ($evaluationitem->hasvalue == 1 AND $evaluation->autonumbering) {
                $itemnr++;
                echo $OUTPUT->box_start('evaluation_item_number_' . $align);
                echo $itemnr;
                echo $OUTPUT->box_end();
            }
            echo $OUTPUT->box_start('box boxalign_' . $align);
            echo $OUTPUT->box_start('evaluation_item_commands_' . $align);
            echo '<span class="evaluation_item_commands position">';
            echo $itempos;
            echo '</span>';
            //Print the moveup-button
            if ($evaluationitem->position > 1) {
                echo '<span class="evaluation_item_command_moveup">';
                $moveupurl = new moodle_url($url, array('moveupitem' => $evaluationitem->id));
                $buttonlink = $moveupurl->out();
                $strbutton = get_string('moveup_item', 'local_evaluations');
                echo '<a class="icon up" title="' . $strbutton . '" href="' . $buttonlink . '">
                        <img alt="' . $strbutton . '" src="' . $OUTPUT->pix_url('t/up') . '" />
                      </a>';
                echo '</span>';
            }
            //Print the movedown-button
            if ($evaluationitem->position < $lastposition - 1) {
                echo '<span class="evaluation_item_command_movedown">';
                $urlparams = array('movedownitem' => $evaluationitem->id);
                $movedownurl = new moodle_url($url, $urlparams);
                $buttonlink = $movedownurl->out();
                $strbutton = get_string('movedown_item', 'local_evaluations');
                echo '<a class="icon down" title="' . $strbutton . '" href="' . $buttonlink . '">
                        <img alt="' . $strbutton . '" src="' . $OUTPUT->pix_url('t/down') . '" />
                      </a>';
                echo '</span>';
            }
            //Print the move-button
            if (count($evaluationitems) > 1) {
                echo '<span class="evaluation_item_command_move">';
                $moveurl = new moodle_url($url, array('moveitem' => $evaluationitem->id));
                $buttonlink = $moveurl->out();
                $strbutton = get_string('move_item', 'local_evaluations');
                echo '<a class="editing_move" title="' . $strbutton . '" href="' . $buttonlink . '">
                        <img alt="' . $strbutton . '" src="' . $OUTPUT->pix_url('t/move') . '" />
                      </a>';
                echo '</span>';
            }
            //Print the button to edit the item
            if ($evaluationitem->typ != 'pagebreak') {
                echo '<span class="evaluation_item_command_edit">';
                $editurl = new moodle_url('/local/evaluations/edit_item.php');
                $editurl->params(array('do_show' => $do_show,
                    'cmid' => $id,
                    'clid' => $classid,
                    'id' => $evaluationitem->id,
                    'typ' => $evaluationitem->typ));

                // In edit_item.php the param id is used for the itemid
                // and the cmid is the id to get the module.
                $buttonlink = $editurl->out();
                $strbutton = get_string('edit_item', 'local_evaluations');
                echo '<a class="editing_update" title="' . $strbutton . '" href="' . $buttonlink . '">
                        <img alt="' . $strbutton . '" src="' . $OUTPUT->pix_url('t/edit') . '" />
                      </a>';
                echo '</span>';
            }

            //Print the toggle-button to switch required yes/no
            if ($evaluationitem->hasvalue == 1) {
                echo '<span class="evaluation_item_command_toggle">';
                if ($evaluationitem->required == 1) {
                    $buttontitle = get_string('switch_item_to_not_required', 'local_evaluations');
                    $buttonimg = $OUTPUT->pix_url('required', 'local_evaluations');
                } else {
                    $buttontitle = get_string('switch_item_to_required', 'local_evaluations');
                    $buttonimg = $OUTPUT->pix_url('notrequired', 'local_evaluations');
                }
                $urlparams = array('switchitemrequired' => $evaluationitem->id);
                $requiredurl = new moodle_url($url, $urlparams);
                $buttonlink = $requiredurl->out();
                echo '<a class="icon ' .
                'evaluation_switchrequired" ' .
                'title="' . $buttontitle . '" ' .
                'href="' . $buttonlink . '">' .
                '<img alt="' . $buttontitle . '" src="' . $buttonimg . '" />' .
                '</a>';
                echo '</span>';
            }

            //Print the delete-button
            echo '<span class="evaluation_item_command_toggle">';
            $deleteitemurl = new moodle_url('/local/evaluations/delete_item.php');
            $deleteitemurl->params(array('id' => $id,
                'clid' => $classid,
                'do_show' => $do_show,
                'deleteitem' => $evaluationitem->id));

            $buttonlink = $deleteitemurl->out();
            $strbutton = get_string('delete_item', 'local_evaluations');
            $src = $OUTPUT->pix_url('t/delete');
            echo '<a class="icon delete" title="' . $strbutton . '" href="' . $buttonlink . '">
                    <img alt="' . $strbutton . '" src="' . $src . '" />
                  </a>';
            echo '</span>';
            echo $OUTPUT->box_end();
            if ($evaluationitem->typ != 'pagebreak') {
                evaluation_print_item_preview($evaluationitem);
            } else {
                echo $OUTPUT->box_start('evaluation_pagebreak');
                echo get_string('pagebreak', 'local_evaluations') . '<hr class="evaluation_pagebreak" />';
                echo $OUTPUT->box_end();
            }
            echo $OUTPUT->box_end();
            echo $OUTPUT->box_end();
            echo '<div class="clearer">&nbsp;</div>';
            echo '</li>';
            //Print out the target box if we ar moving an item
            if (isset($SESSION->evaluation->moving) AND $SESSION->evaluation->moving->shouldmoving == 1) {
                echo '<li>';
                $moveposition++;
                $movehereurl->param('movehere', $moveposition);
                echo $OUTPUT->box_start('clipboard'); //Only shown if shouldmoving = 1
                $buttonlink = $movehereurl->out();
                $strbutton = get_string('move_here', 'local_evaluations');
                $src = $OUTPUT->pix_url('movehere');
                echo '<a title="' . $strbutton . '" href="' . $buttonlink . '">
                        <img class="movetarget" alt="' . $strbutton . '" src="' . $src . '" />
                      </a>';
                echo $OUTPUT->box_end();
                echo '</li>';
            }
        }
        echo $OUTPUT->box_end();
        echo '</ul>';
        echo '</div>';
    } else {
        echo $OUTPUT->box(get_string('no_items_available_yet', 'local_evaluations'), 'generalbox boxaligncenter');
    }
}
/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();
