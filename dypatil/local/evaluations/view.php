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
 * the first page to view the evaluation
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
global $USER;
$id = required_param('id', PARAM_INT);
$classid = optional_param('clid', false, PARAM_INT);

$current_tab = 'overview';
$PAGE->navbar->add(get_string('pluginname', 'local_evaluations'), new moodle_url('/local/evaluations/index.php'));
//$PAGE->navbar->add($strheading);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
//if(!require_capability('local/evaluations:view', context_system::instance())){
//    require_capability('local/evaluations:studentview', context_user::instance($USER->id));
//}
//if (! $cm = get_classmodule_from_id('local_evaluations', $id)) {
//    print_error('invalidclassmodule');
//}
//if (! $class = $DB->get_record("class", array("id"=>$cm->class))) {
//    print_error('classmisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id))) {
    print_error('invalidevaluationid');
}

//$context = context_module::instance($cm->id);
//$evaluation_complete_cap = false;
//if (has_capability('mod/evaluation:complete', $context)) {
//    $evaluation_complete_cap = true;
//}
//if (isset($CFG->evaluation_allowfullanonymous)
//            AND $CFG->evaluation_allowfullanonymous
//            AND $class->id == SITEID
//            AND (!$evaluation->classid OR $evaluation->classid == SITEID)
//            AND $evaluation->anonymous == EVALUATION_ANONYMOUS_YES ) {
//    $evaluation_complete_cap = true;
//}
//check whether the evaluation is located and! started from the mainsite
//if ($class->id == SITEID AND !$evaluation->classid) {
//    $evaluation->classid = SITEID;
//}
//check whether the evaluation is mapped to the given classid
//if ($class->id == SITEID AND !has_capability('mod/evaluation:edititems', $context)) {
//    if ($DB->get_records('evaluation_siteclass_map', array('evaluationid'=>$evaluation->id))) {
//        $params = array('evaluationid'=>$evaluation->id, 'classid'=>$evaluation->classid);
//        if (!$DB->get_record('evaluation_siteclass_map', $params)) {
//            print_error('invalidclassmodule');
//        }
//    }
//}
//if ($evaluation->anonymous != EVALUATION_ANONYMOUS_YES) {
//    if ($class->id == SITEID) {
//        require_login($class, true);
//    } else {
//        require_login($class, true, $cm);
//    }
//} else {
//    if ($class->id == SITEID) {
//        require_class_login($class, true);
//    } else {
//        require_class_login($class, true, $cm);
//    }
//}
//check whether the given classid exists
//if ($evaluation->classid AND $evaluation->classid != SITEID) {
//    if ($class2 = $DB->get_record('class', array('id'=>$evaluation->classid))) {
//        require_class_login($class2); //this overwrites the object $class :-(
//        $class = $DB->get_record("class", array("id"=>$cm->class)); // the workaround
//    } else {
//        print_error('invalidclassid');
//    }
//}
//
//if ($evaluation->anonymous == EVALUATION_ANONYMOUS_NO) {
//    add_to_log($class->id, 'local_evaluations', 'view', 'view.php?id='.$cm->id, $evaluation->id, $cm->id);
//}
/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

//if ($class->id == SITEID) {
//    $PAGE->set_context($context);
//    $PAGE->set_cm($cm, $class); // set's up global $CLASS
//    $PAGE->set_pagelayout('inclass');
//}
$PAGE->set_url('/local/evaluations/view.php', array('id' => $id, 'clid' => $evaluation->classid, 'do_show' => 'view'));
$PAGE->set_title(format_string($evaluation->name));
$PAGE->set_pagelayout('admin');
$systemcontext =context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
//$PAGE->set_heading(format_string($class->fullname));
echo $OUTPUT->header();

//ishidden check.
//evaluation in classs
//$cap_viewhiddenactivities = has_capability('moodle/class:viewhiddenactivities', $context);
//if ((empty($cm->visible) and !$cap_viewhiddenactivities) AND $class->id != SITEID) {
//   notice(get_string("activityiscurrentlyhidden"));
//}
//ishidden check.
//evaluation on mainsite
//if ((empty($cm->visible) and !$cap_viewhiddenactivities) AND $evaluation->classid == SITEID) {
//    notice(get_string("activityiscurrentlyhidden"));
//}
/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
/// print the tabs
require('tabs.php');
echo $OUTPUT->box(get_string('overview_eval', 'local_evaluations'));
$previewimg = $OUTPUT->pix_icon('t/preview', get_string('preview'));
$previewlnk = '<a href="' . $CFG->wwwroot . '/local/evaluations/print.php?id=' . $id . '&clid=' . $classid . '">' . $previewimg . '</a>';

echo $OUTPUT->heading(format_text($evaluation->name . ' ' . $previewlnk));

//show some infos to the evaluation
//if (has_capability('mod/evaluation:edititems', $context)) {
//get the groupid
//  $groupselect = groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/evaluation/view.php?id='.$cm->id, true);
//  $mygroupid = groups_get_activity_group($cm);
//    echo $OUTPUT->box_start('boxaligncenter boxwidthwide');
//   echo $groupselect.'<div class="clearer">&nbsp;</div>';
$evaltype = array('1' => get_string('evoltype1', 'local_evaluations'),
    '2' => get_string('evoltype3', 'local_evaluations'),
    '3' => get_string('evoltype4', 'local_evaluations'));
$table = new html_table();
$line = array();
$evtdname = $DB->get_record('user', array('id' => $evaluation->evaluatinginstructor));
$evtnginstructorlist = explode(',', $evaluation->evaluatedinstructor);
foreach ($evtnginstructorlist as $evtnginstructor) {
    $evngname = $DB->get_record('user', array('id' => $evtnginstructor));
    $evngnames[] = $evngname->firstname . '' . $evngname->lastname;
}
$evngnamelist = implode(',', $evngnames);
$line[] = array('<b>' . get_string('class', 'local_clclasses') . '</b>', $DB->get_field('local_clclasses', 'fullname', array('id' => $evaluation->classid)));
$line[] = array('<b>' . get_string('evaluation_type', 'local_evaluations') . '</b>', $evaltype[$evaluation->evaluationtype]);
if ($evaluation->evaluationtype < 3) {
    $line[] = array('<b>' . get_string('evaluatinginstructor', 'local_evaluations') . '</b>', $evtdname->firstname . '' . $evtdname->lastname);
}
if ($evaluation->evaluationtype == 2) {
    $line[] = array('<b>' . get_string('evaluatedinstructor', 'local_evaluations') . '</b>', $evngnamelist);
}
$sem = $DB->get_record_sql("SELECT s.fullname
                                             FROM mdl_local_semester as s
                                             JOIN mdl_local_clclasses as c
                                             ON s.id=c.semesterid
                                             WHERE c.id=$evaluation->classid");
$line[] = array('<b>' . get_string('semester', 'local_semesters') . '</b>', $sem->fullname);

$sandp = $DB->get_record_sql('SELECT s.fullname as schoolname
                                                        FROM mdl_local_school as s,mdl_local_scheduleclass as l
                                                        WHERE l.schoolid = s.id and l.instructorid=' . $evaluation->evaluatinginstructor . ' and l.classid = ' . $evaluation->classid . '');
$completedscount = evaluation_get_completeds_group_count($evaluation);
if ($evaluation->evaluationtype < 3) {
    $line[] = array('<b>' . get_string('schoolid', 'local_collegestructure') . '</b>', $sandp->schoolname);
}
$line[] = array('<b>' . get_string('completed_evaluations', 'local_evaluations') . '</b>', $completedscount);
$params = array('evaluation' => $evaluation->id, 'hasvalue' => 1);
$itemscount = $DB->count_records('evaluation_item', $params);
$line[] = array('<b>' . get_string('questions', 'local_evaluations') . '</b>', $itemscount);
if ($evaluation->timeopen) {
    $line[] = array('<b>' . get_string('evaluationopen', 'local_evaluations') . '</b>', userdate($evaluation->timeopen));
}
if ($evaluation->timeclose) {
    $line[] = array('<b>' . get_string('evaluationclose', 'local_evaluations') . '</b>', userdate($evaluation->timeclose));
}
//  echo $OUTPUT->box_end();
//}
$table->data = $line;
echo html_writer::table($table);
//if (has_capability('mod/evaluation:edititems', $context)) {
echo $OUTPUT->heading(get_string('description', 'local_evaluations'), 4);
//}
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//$options = (object)array('noclean'=>true);
//echo format_module_intro('local_evaluations', $evaluation, $cm->id);
echo $evaluation->description;
echo $OUTPUT->box_end();

//if (has_capability('mod/evaluation:edititems', $context)) {
// require_once($CFG->libdir . '/filelib.php');
//$page_after_submit_output = file_rewrite_pluginfile_urls($evaluation->page_after_submit,
//                                                    'pluginfile.php',
//                                                $context->id,
//                                            'mod_evaluation',
//                                        'page_after_submit',
//                                    0);
//    echo $OUTPUT->heading(get_string("page_after_submit", "evaluation"), 4);
//    echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//echo format_text($page_after_submit_output,
//             $evaluation->page_after_submitformat,
//         array('overflowdiv'=>true));
//   echo $OUTPUT->box_end();
//}
//if ( (intval($evaluation->publish_stats) == 1) AND
//                ( has_capability('mod/evaluation:viewanalysepage', $context)) AND
//                !( has_capability('mod/evaluation:viewreports', $context)) ) {
//
if (has_capability('local/evaluations:addinstance', context_system::instance())) {
    $params = array('userid' => $USER->id, 'evaluation' => $evaluation->id);
    if ($multiple_count = $DB->count_records('evaluation_tracking', $params)) {
        $url_params = array('id' => $id, 'clid' => $classid);
        $analysisurl = new moodle_url('/local/evaluations/analysis.php', $url_params);
        echo '<div class="mdl-align"><a href="' . $analysisurl->out() . '">';
        echo get_string('completed_evaluations', 'local_evaluations') . '</a>';
        echo '</div>';
    }
}
//}
//####### mapclass-start
//if (has_capability('mod/evaluation:mapclass', $context)) {
//    if ($evaluation->class == SITEID) {
//        echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//        echo '<div class="mdl-align">';
//        echo '<form action="mapclass.php" method="get">';
//        echo '<fieldset>';
//        echo '<input type="hidden" name="sesskey" value="'.sesskey().'" />';
//        echo '<input type="hidden" name="id" value="'.$id.'" />';
//        echo '<button type="submit">'.get_string('mapclasss', 'local_evaluations').'</button>';
//        echo $OUTPUT->help_icon('mapclass', 'local_evaluations');
//        echo '</fieldset>';
//        echo '</form>';
//        echo '<br />';
//        echo '</div>';
//        echo $OUTPUT->box_end();
//    }
//}
//####### mapclass-end
//####### completed-start
//if ($evaluation_complete_cap) {
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//check, whether the evaluation is open (timeopen, timeclose)
$checktime = time();
if (($evaluation->timeopen > $checktime) OR ( $evaluation->timeclose < $checktime AND $evaluation->timeclose > 0)) {

    echo '<h2><font color="red">' . get_string('evaluation_is_not_open', 'local_evaluations') . '</font></h2>';
    echo $OUTPUT->continue_button($CFG->wwwroot . '/local/clclasses/view.php?id=' . $evaluation->classid);
    echo $OUTPUT->box_end();
    echo $OUTPUT->footer();
    exit;
}

//check multiple Submit
$evaluation_can_submit = true;
if ($evaluation->multiple_submit == 0) {
    if (evaluation_is_already_submitted($evaluation->id, $evaluation->classid)) {
        $evaluation_can_submit = false;
    }
}
if ($evaluation_can_submit) {
    //if the user is not known so we cannot save the values temporarly
    if (!isloggedin() or isguestuser()) {
        $completefile = 'complete_guest.php';
        $guestid = sesskey();
    } else {
        $completefile = 'complete.php';
        $guestid = false;
    }

    $url_params = array('id' => $id, 'clid' => $classid, 'gopage' => 0);
    $completeurl = new moodle_url('/local/evaluations/' . $completefile, $url_params);

    $evaluationcompletedtmp = evaluation_get_current_completed($evaluation->id, true, $evaluation->classid, $guestid);
    if ($evaluationcompletedtmp) {
        if ($startpage = evaluation_get_page_to_continue($evaluation->id, $evaluation->classid, $guestid)) {
            $completeurl->param('gopage', $startpage);
        }
        if ($itemscount > 0) {
            echo '<a href="' . $completeurl->out() . '">' . get_string('continue_the_form', 'local_evaluations') . '</a>';
        } else {
            echo "No question are available for this evaluation";
        }
    } else {
        if ($itemscount > 0) {
            echo '<a href="' . $completeurl->out() . '">' . get_string('complete_the_form', 'local_evaluations') . '</a>';
        } else {
            echo "No question are available for this evaluation";
        }
    }
} else {
    echo '<h2><font color="red">';
    echo get_string('this_evaluation_is_already_submitted', 'local_evaluations');
    echo '</font></h2>';
    if ($evaluation) {
        echo $OUTPUT->continue_button($CFG->wwwroot . '/local/clclasses/view.php?id=' . $evaluation->classid);
    } else {
        echo $OUTPUT->continue_button($CFG->wwwroot . '/local/clclasses/view.php?id=' . $evaluation->classid);
    }
}
echo $OUTPUT->box_end();
//}
//####### completed-end
/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();

