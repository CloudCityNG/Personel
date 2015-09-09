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
 * prints the form so the user can fill out the evaluation
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');

evaluation_init_evaluation_session();

$id = required_param('id', PARAM_INT);
$completedid = optional_param('completedid', false, PARAM_INT);
$preservevalues = optional_param('preservevalues', 0, PARAM_INT);
$classid = required_param('clid', PARAM_INT);
$gopage = optional_param('gopage', -1, PARAM_INT);
$lastpage = optional_param('lastpage', false, PARAM_INT);
$startitempos = optional_param('startitempos', 0, PARAM_INT);
$lastitempos = optional_param('lastitempos', 0, PARAM_INT);
$anonymous_response = optional_param('anonymous_response', 0, PARAM_INT); //arb

$highlightrequired = false;

if (($formdata = data_submitted()) AND ! confirm_sesskey()) {
    print_error('invalidsesskey');
}

//if the use hit enter into a textfield so the form should not submit
if (isset($formdata->sesskey) AND ! isset($formdata->savevalues) AND ! isset($formdata->gonextpage) AND ! isset($formdata->gopreviouspage)) {

    $gopage = $formdata->lastpage;
}

if (isset($formdata->savevalues)) {
    $savevalues = true;
} else {
    $savevalues = false;
}

if ($gopage < 0 AND ! $savevalues) {
    if (isset($formdata->gonextpage)) {
        $gopage = $lastpage + 1;
        $gonextpage = true;
        $gopreviouspage = false;
    } else if (isset($formdata->gopreviouspage)) {
        $gopage = $lastpage - 1;
        $gonextpage = false;
        $gopreviouspage = true;
    } else {
        print_error('missingparameter');
    }
} else {
    $gonextpage = $gopreviouspage = false;
}

//if (! $cm = get_classmodule_from_id('evaluation', $id)) {
//    print_error('invalidclassmodule');
//}
//if (! $classid = $DB->get_record("class", array("id"=>$cm->class))) {
//    print_error('classmisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id))) {
    print_error('invalidclassmodule');
}

//$context = context_module::instance($cm->id);

$evaluation_complete_cap = false;

//if (has_capability('mod/evaluation:complete', $context)) {
//    $evaluation_complete_cap = true;
//}
//check whether the evaluation is located and! started from the mainsite
//if ($classid->id == SITEID AND !$classid) {
//    $classid = SITEID;
//}
//check whether the evaluation is mapped to the given classid
//if ($classid->id == SITEID AND !has_capability('mod/evaluation:edititems', $context)) {
if ($DB->get_records('evaluation_siteclass_map', array('evaluationid' => $evaluation->id))) {
    $params = array('evaluationid' => $evaluation->id, 'classid' => $classid);
    if (!$DB->get_record('evaluation_siteclass_map', $params)) {
        print_error('notavailable', 'evaluation');
    }
}
//}
//if ($evaluation->anonymous != EVALUATION_ANONYMOUS_YES) {
//    if ($classid->id == SITEID) {
//        require_login($classid, true);
//    } else {
//        require_login($classid, true, $cm);
//    }
//} else {
//    if ($classid->id == SITEID) {
//        require_class_login($classid, true);
//    } else {
//        require_class_login($classid, true, $cm);
//    }
//}
//check whether the given classid exists
//if ($classid AND $classid != SITEID) {
//    if ($classid2 = $DB->get_record('class', array('id'=>$classid))) {
//        require_class_login($classid2); //this overwrites the object $classid :-(
//        $classid = $DB->get_record("class", array("id"=>$cm->class)); // the workaround
//    } else {
//        print_error('invalidclassid');
//    }
//}
//if (!$evaluation_complete_cap) {
//    print_error('error');
//}
// Mark activity viewed for completion-tracking
//$completion = new completion_info($classid);
//$completion->set_module_viewed($cm);
/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

//if ($classid->id == SITEID) {
//    $PAGE->set_cm($cm, $classid); // set's up global $CLASSID
//    $PAGE->set_pagelayout('inclass');
//}

$PAGE->navbar->add(get_string('evaluation:complete', 'local_evaluations'));
$urlparams = array('id' => $id, 'gopage' => $gopage, 'clid' => $classid);
$PAGE->set_url('/local/evaluations/complete.php', $urlparams);
//$PAGE->set_heading(format_string($classid->fullname));
$PAGE->set_title(format_string($evaluation->name));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
//require_capability('local/evaluations:studentview', context_user::instance($USER->id));
echo $OUTPUT->header();
$current_tab = 'completeevl';
//require('tabs.php');
//ishidden check.
//evaluation in classs
//if ((empty($cm->visible) AND
//        !has_capability('moodle/class:viewhiddenactivities', $context)) AND
//        $classid->id != SITEID) {
//    notice(get_string("activityiscurrentlyhidden"));
//}
//ishidden check.
//evaluation on mainsite
//if ((empty($cm->visible) AND
//        !has_capability('moodle/class:viewhiddenactivities', $context)) AND
//        $classid == SITEID) {
//    notice(get_string("activityiscurrentlyhidden"));
//}
//check, if the evaluation is open (timeopen, timeclose)
$checktime = time();
$evaluation_is_closed = ($evaluation->timeopen > $checktime) OR ( $evaluation->timeclose < $checktime AND
        $evaluation->timeclose > 0);

if ($evaluation_is_closed) {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<h2><font color="red">';
    echo get_string('evaluation_is_not_open', 'local_evaluations');
    echo '</font></h2>';
    echo $OUTPUT->continue_button($CFG->wwwroot . '/class/view.php?id=' . $classid);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

//additional check for multiple-submit (prevent browsers back-button).
//the main-check is in view.php
$evaluation_can_submit = true;
if ($evaluation->multiple_submit == 0) {
    if (evaluation_is_already_submitted($evaluation->id, $classid)) {
        $evaluation_can_submit = false;
    }
}
if ($evaluation_can_submit) {
    //preserving the items
    if ($preservevalues == 1) {
        if (!isset($SESSION->evaluation->is_started) OR ! $SESSION->evaluation->is_started == true) {
            print_error('error', '', $CFG->wwwroot . '/class/view.php?id=' . $classid);
        }
        //checken, ob alle required items einen wert haben
        if (evaluation_check_values($startitempos, $lastitempos)) {
            $userid = $USER->id; //arb
            if ($completedid = evaluation_save_values($USER->id, true)) {
                if ($userid > 0) {
                    add_to_log($classid, 'evaluation', 'startcomplete', 'view.php?id=' . $cm->id, $evaluation->id, $classid, $userid);
                }
                if (!$gonextpage AND ! $gopreviouspage) {
                    $preservevalues = false; //es kann gespeichert werden
                }
            } else {
                $savereturn = 'failed';
                if (isset($lastpage)) {
                    $gopage = $lastpage;
                } else {
                    print_error('missingparameter');
                }
            }
        } else {
            $savereturn = 'missing';
            $highlightrequired = true;
            if (isset($lastpage)) {
                $gopage = $lastpage;
            } else {
                print_error('missingparameter');
            }
        }
    }

    //saving the items
    if ($savevalues AND ! $preservevalues) {
        //exists there any pagebreak, so there are values in the evaluation_valuetmp
        $userid = $USER->id; //arb

        if ($evaluation->anonymous == EVALUATION_ANONYMOUS_NO) {
            $evaluationcompleted = evaluation_get_current_completed($evaluation->id, false, $classid);
        } else {
            $evaluationcompleted = false;
        }
        $params = array('id' => $completedid);
        $evaluationcompletedtmp = $DB->get_record('evaluation_completedtmp', $params);
        //fake saving for switchrole
        //    $is_switchrole = evaluation_check_is_switchrole();
        //  if ($is_switchrole) {
        //     $savereturn = 'saved';
        //    evaluation_delete_completedtmp($completedid);
        // } else {
        $new_completed_id = evaluation_save_tmp_values($evaluationcompletedtmp, $evaluationcompleted, $userid);
        if ($new_completed_id) {
            $savereturn = 'saved';
            if ($evaluation->anonymous == EVALUATION_ANONYMOUS_NO) {
                add_to_log($classid->id, 'evaluation', 'submit', 'view.php?id=' . $cm->id, $evaluation->id, $cm->id, $userid);

                evaluation_send_email($cm, $evaluation, $classid, $userid);
            } else {
                evaluation_send_email_anonym($cm, $evaluation, $classid, $userid);
            }
            //tracking the submit
            $tracking = new stdClass();
            $tracking->userid = $USER->id;
            $tracking->evaluation = $evaluation->id;
            $tracking->completed = $new_completed_id;
            $DB->insert_record('evaluation_tracking', $tracking);
            unset($SESSION->evaluation->is_started);

            // Update completion state
            //$completion = new completion_info($classid);
            //if ($completion->is_enabled($cm) && $evaluation->completionsubmit) {
            //    $completion->update_state($cm, COMPLETION_COMPLETE);
            //}
        } else {
            $savereturn = 'failed';
        }
    }

    //}


    if ($allbreaks = evaluation_get_all_break_positions($evaluation->id)) {
        if ($gopage <= 0) {
            $startposition = 0;
        } else {
            if (!isset($allbreaks[$gopage - 1])) {
                $gopage = count($allbreaks);
            }
            $startposition = $allbreaks[$gopage - 1];
        }
        $ispagebreak = true;
    } else {
        $startposition = 0;
        $newpage = 0;
        $ispagebreak = false;
    }

    //get the evaluationitems after the last shown pagebreak
    $select = 'evaluation = ? AND position > ?';
    $params = array($evaluation->id, $startposition);
    $evaluationitems = $DB->get_records_select('evaluation_item', $select, $params, 'position');

    //get the first pagebreak
    $params = array('evaluation' => $evaluation->id, 'typ' => 'pagebreak');
    if ($pagebreaks = $DB->get_records('evaluation_item', $params, 'position')) {
        $pagebreaks = array_values($pagebreaks);
        $firstpagebreak = $pagebreaks[0];
    } else {
        $firstpagebreak = false;
    }
    $maxitemcount = $DB->count_records('evaluation_item', array('evaluation' => $evaluation->id));

    //get the values of completeds before done. Anonymous user can not get these values.
    if ((!isset($SESSION->evaluation->is_started)) AND ( !isset($savereturn)) AND ( $evaluation->anonymous == EVALUATION_ANONYMOUS_NO)) {

        $evaluationcompletedtmp = evaluation_get_current_completed($evaluation->id, true, $classid);
        if (!$evaluationcompletedtmp) {
            $evaluationcompleted = evaluation_get_current_completed($evaluation->id, false, $classid);
            if ($evaluationcompleted) {
                //copy the values to evaluation_valuetmp create a completedtmp
                $evaluationcompletedtmp = evaluation_set_tmp_values($evaluationcompleted);
            }
        }
    } else {
        $evaluationcompletedtmp = evaluation_get_current_completed($evaluation->id, true, $classid);
    }


    /// Print the main part of the page
    ///////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////
    ///////////////////////////////////////////////////////////////////////////
    $analysisurl = new moodle_url('/local/evaluations/analysis.php', array('id' => $id, 'clid' => $classid));
    if ($classid > 0) {
        $analysisurl->param('clid', $classid);
    }
    echo $OUTPUT->heading(format_text($evaluation->name));

    //  if ( (intval($evaluation->publish_stats) == 1) AND
    //         ( has_capability('mod/evaluation:viewanalysepage', $context)) AND
    //         !( has_capability('mod/evaluation:viewreports', $context)) ) {

    $params = array('userid' => $USER->id, 'evaluation' => $evaluation->id);
    if ($multiple_count = $DB->count_records('evaluation_tracking', $params)) {
        //echo $OUTPUT->box_start('mdl-align');
        //echo '<a href="'.$analysisurl->out().'">';
        //echo get_string('completed_evaluations', 'local_evaluations').'</a>';
        //echo $OUTPUT->box_end();
    }
    //}

    if (isset($savereturn) && $savereturn == 'saved') {
        if ($evaluation->page_after_submit) {

            require_once($CFG->libdir . '/filelib.php');

            $page_after_submit_output = file_rewrite_pluginfile_urls($evaluation->page_after_submit, 'pluginfile.php', $context->id, 'mod_evaluation', 'page_after_submit', 0);

            echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
            echo format_text($page_after_submit_output, $evaluation->page_after_submitformat, array('overflowdiv' => true));
            echo $OUTPUT->box_end();
        } else {
            echo '<p align="center">';
            echo '<b><font color="green">';
            echo get_string('entries_saved', 'local_evaluations');
            echo '</font></b>';
            echo '</p>';
            if (has_capability('local/evaluations:addinstance', context_system::instance())) {
                if (intval($evaluation->publish_stats) == 1) {
                    echo '<p align="center"><a href="' . $analysisurl->out() . '">';
                    echo get_string('completed_evaluations', 'local_evaluations') . '</a>';
                    echo '</p>';
                }
            }
        }

        if ($evaluation->site_after_submit) {
            $url = evaluation_encode_target_url($evaluation->site_after_submit);
        } else {
            if ($classid) {
                if ($classid == SITEID) {
                    $url = $CFG->wwwroot;
                } else {
                    $url = $CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid;
                }
            } else {
                if ($classid->id == SITEID) {
                    $url = $CFG->wwwroot;
                } else {
                    $url = $CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid->id;
                }
            }
        }
        echo $OUTPUT->continue_button($url);
    } else {
        if (isset($savereturn) && $savereturn == 'failed') {
            echo $OUTPUT->box_start('mform error');
            echo get_string('saving_failed', 'local_evaluations');
            echo $OUTPUT->box_end();
        }

        if (isset($savereturn) && $savereturn == 'missing') {
            echo $OUTPUT->box_start('mform error');
            echo get_string('saving_failed_because_missing_or_false_values', 'local_evaluations');
            echo $OUTPUT->box_end();
        }

        //print the items
        if (is_array($evaluationitems)) {
            echo $OUTPUT->box_start('evaluation_form');
            echo '<form action="complete.php" method="post" onsubmit=" ">';
            echo '<fieldset>';
            echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
            echo $OUTPUT->box_start('evaluation_anonymousinfo');
            switch ($evaluation->anonymous) {
                case EVALUATION_ANONYMOUS_YES:
                    echo '<input type="hidden" name="anonymous" value="1" />';
                    $inputvalue = 'value="' . EVALUATION_ANONYMOUS_YES . '"';
                    echo '<input type="hidden" name="anonymous_response" ' . $inputvalue . ' />';
                    echo get_string('mode', 'local_evaluations') . ': ' . get_string('anonymous', 'local_evaluations');
                    break;
                case EVALUATION_ANONYMOUS_NO:
                    echo '<input type="hidden" name="anonymous" value="0" />';
                    $inputvalue = 'value="' . EVALUATION_ANONYMOUS_NO . '"';
                    echo '<input type="hidden" name="anonymous_response" ' . $inputvalue . ' />';
                    echo get_string('mode', 'local_evaluations') . ': ';
                    echo get_string('non_anonymous', 'local_evaluations');
                    break;
            }
            echo $OUTPUT->box_end();
            //check, if there exists required-elements
            $params = array('evaluation' => $evaluation->id, 'required' => 1);
            $countreq = $DB->count_records('evaluation_item', $params);
            if ($countreq > 0) {
                echo '<span class="evaluation_required_mark">(*)';
                echo get_string('items_are_required', 'local_evaluations');
                echo '</span>';
            }
            echo $OUTPUT->box_start('evaluation_items');

            unset($startitem);
            $select = 'evaluation = ? AND hasvalue = 1 AND position < ?';
            $params = array($evaluation->id, $startposition);
            $itemnr = $DB->count_records_select('evaluation_item', $select, $params);
            $lastbreakposition = 0;
            $align = right_to_left() ? 'right' : 'left';

            foreach ($evaluationitems as $evaluationitem) {
                if (!isset($startitem)) {
                    //avoid showing double pagebreaks
                    if ($evaluationitem->typ == 'pagebreak') {
                        continue;
                    }
                    $startitem = $evaluationitem;
                }

                if ($evaluationitem->dependitem > 0) {
                    //chech if the conditions are ok
                    $fb_compare_value = evaluation_compare_item_value($evaluationcompletedtmp->id, $evaluationitem->dependitem, $evaluationitem->dependvalue, true);
                    if (!isset($evaluationcompletedtmp->id) OR ! $fb_compare_value) {
                        $lastitem = $evaluationitem;
                        $lastbreakposition = $evaluationitem->position;
                        continue;
                    }
                }

                if ($evaluationitem->dependitem > 0) {
                    $dependstyle = ' evaluation_complete_depend';
                } else {
                    $dependstyle = '';
                }

                echo $OUTPUT->box_start('evaluation_item_box_' . $align . $dependstyle);
                $value = '';
                //get the value
                $frmvaluename = $evaluationitem->typ . '_' . $evaluationitem->id;
                if (isset($savereturn)) {
                    $value = isset($formdata->{$frmvaluename}) ? $formdata->{$frmvaluename} : null;
                    $value = evaluation_clean_input_value($evaluationitem, $value);
                } else {
                    if (isset($evaluationcompletedtmp->id)) {
                        $value = evaluation_get_item_value($evaluationcompletedtmp->id, $evaluationitem->id, true);
                    }
                }
                if ($evaluationitem->hasvalue == 1 AND $evaluation->autonumbering) {
                    $itemnr++;
                    echo $OUTPUT->box_start('evaluation_item_number_' . $align);
                    echo $itemnr;
                    echo $OUTPUT->box_end();
                }
                if ($evaluationitem->typ != 'pagebreak') {
                    echo $OUTPUT->box_start('box generalbox boxalign_' . $align);
                    evaluation_print_item_complete($evaluationitem, $value, $highlightrequired);
                    echo $OUTPUT->box_end();
                }

                echo $OUTPUT->box_end();

                $lastbreakposition = $evaluationitem->position; //last item-pos (item or pagebreak)
                if ($evaluationitem->typ == 'pagebreak') {
                    break;
                } else {
                    $lastitem = $evaluationitem;
                }
            }
            echo $OUTPUT->box_end();
            echo '<input type="hidden" name="id" value="' . $id . '" />';
            echo '<input type="hidden" name="evaluationid" value="' . $evaluation->id . '" />';
            echo '<input type="hidden" name="lastpage" value="' . $gopage . '" />';
            if (isset($evaluationcompletedtmp->id)) {
                $inputvalue = 'value="' . $evaluationcompletedtmp->id . '"';
            } else {
                $inputvalue = 'value=""';
            }
            echo '<input type="hidden" name="completedid" ' . $inputvalue . ' />';
            echo '<input type="hidden" name="clid" value="' . $classid . '" />';
            echo '<input type="hidden" name="preservevalues" value="1" />';
            if (isset($startitem)) {
                echo '<input type="hidden" name="startitempos" value="' . $startitem->position . '" />';
                echo '<input type="hidden" name="lastitempos" value="' . $lastitem->position . '" />';
            }

            if ($ispagebreak AND $lastbreakposition > $firstpagebreak->position) {
                $inputvalue = 'value="' . get_string('previous_page', 'local_evaluations') . '"';
                echo '<input name="gopreviouspage" type="submit" ' . $inputvalue . ' />';
            }
            if ($lastbreakposition < $maxitemcount) {
                $inputvalue = 'value="' . get_string('next_page', 'local_evaluations') . '"';
                echo '<input name="gonextpage" type="submit" ' . $inputvalue . ' />';
            }
            if ($lastbreakposition >= $maxitemcount) { //last page
                $inputvalue = 'value="' . get_string('save_entries', 'local_evaluations') . '"';
                echo '<input name="savevalues" type="submit" ' . $inputvalue . ' />';
            }

            echo '</fieldset>';
            echo '</form>';
            echo $OUTPUT->box_end();

            echo $OUTPUT->box_start('evaluation_complete_cancel');
            if ($classid) {
                $action = 'action="' . $CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid . '"';
            } else {
                if ($classid->id == SITEID) {
                    $action = 'action="' . $CFG->wwwroot . '"';
                } else {
                    $action = 'action="' . $CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid->id . '"';
                }
            }
            echo '<form ' . $action . ' method="post" onsubmit=" ">';
            echo '<fieldset>';
            echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
            echo '<input type="hidden" name="classid" value="' . $classid . '" />';
            echo '<button type="submit">' . get_string('cancel') . '</button>';
            echo '</fieldset>';
            echo '</form>';
            echo $OUTPUT->box_end();
            $SESSION->evaluation->is_started = true;
        }
    }
} else {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    echo '<h2>';
    echo '<font color="red">';
    echo get_string('this_evaluation_is_already_submitted', 'local_evaluations');
    echo '</font>';
    echo '</h2>';
    echo $OUTPUT->continue_button($CFG->wwwroot . '/local/evaluations/view.php?id=' . $id . '&clid=' . $classid->id);
    echo $OUTPUT->box_end();
}
/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();
