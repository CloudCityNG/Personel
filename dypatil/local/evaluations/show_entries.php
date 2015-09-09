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
 * print the single entries
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/tablelib.php');

////////////////////////////////////////////////////////
//get the params
////////////////////////////////////////////////////////
$id = required_param('id', PARAM_INT);
$classid = required_param('clid', PARAM_INT);
$userid = optional_param('userid', false, PARAM_INT);
$do_show = required_param('do_show', PARAM_ALPHA);
$perpage = optional_param('perpage', EVALUATION_DEFAULT_PAGE_COUNT, PARAM_INT);  // how many per page
$showall = optional_param('showall', false, PARAM_INT);  // should we show all users
// $SESSION->evaluation->current_tab = $do_show;
$current_tab = $do_show;

////////////////////////////////////////////////////////
//get the objects
////////////////////////////////////////////////////////

if ($userid) {
    $formdata->userid = intval($userid);
}

//if (! $cm = get_coursemodule_from_id('evaluation', $id)) {
//    print_error('invalidcoursemodule');
//}
//
//if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
//    print_error('coursemisconf');
//}

if (!$evaluation = $DB->get_record("local_evaluation", array("id" => $id, 'classid' => $classid))) {
    print_error('invalidevaluationid');
}

$url = new moodle_url('/local/evaluations/show_entries.php', array('id' => $id, 'clid' => $classid, 'do_show' => $do_show));
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();
require_login();
$PAGE->set_context($systemcontext);
require_capability('local/evaluations:addinstance', context_system::instance());
$PAGE->set_url($url);
$PAGE->set_title(format_string($evaluation->name));
echo $OUTPUT->header();
//$context = context_module::instance($cm->id);
//
//require_login($course, true, $cm);

if (($formdata = data_submitted()) AND ! confirm_sesskey()) {
    print_error('invalidsesskey');
}

//require_capability('mod/evaluation:viewreports', $context);
////////////////////////////////////////////////////////
//get the responses of given user
////////////////////////////////////////////////////////
if ($do_show == 'showoneentry') {
    //get the evaluationitems
    $evaluationitems = $DB->get_records('evaluation_item', array('evaluation' => $evaluation->id), 'position');

    $params = array('evaluation' => $evaluation->id,
        'userid' => $userid,
        'anonymous_response' => EVALUATION_ANONYMOUS_NO);

    $evaluationcompleted = $DB->get_record('evaluation_completed', $params); //arb
}

/// Print the page header
$strevaluations = get_string("modulenameplural", "local_evaluations");
$strevaluation = get_string("modulename", "local_evaluations");

//$PAGE->set_heading(format_string($course->fullname));


require('tabs.php');
echo $OUTPUT->box(get_string('entries_eval', 'local_evaluations'));
/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
////////////////////////////////////////////////////////
/// Print the links to get responses and analysis
////////////////////////////////////////////////////////
if ($do_show == 'showentries') {
    //print the link to analysis
//    if (has_capability('mod/evaluation:viewreports', $context)) {
    //get the effective groupmode of this course and module
    //       if (isset($cm->groupmode) && empty($course->groupmodeforce)) {
    //         $groupmode =  $cm->groupmode;
    //    } else {
    //       $groupmode = $course->groupmode;
    //   }
    //$groupselect = groups_print_activity_menu($cm, $url->out(), true);
    //$mygroupid = groups_get_activity_group($cm);
    // preparing the table for output
    $baseurl = new moodle_url('/local/evaluations/show_entries.php');
    $baseurl->params(array('id' => $id, 'clid' => $classid, 'do_show' => $do_show, 'showall' => $showall));

    $tablecolumns = array('userpic', 'fullname', 'completed_timemodified');
    $tableheaders = array(get_string('userpic'), get_string('fullnameuser'), get_string('date'));

//        if (has_capability('mod/evaluation:deletesubmissions', $context)) {
    $tablecolumns[] = 'deleteentry';
    $tableheaders[] = '';
    //      }

    $table = new flexible_table('evaluation-showentry-list-' . $classid);

    $table->define_columns($tablecolumns);
    $table->define_headers($tableheaders);
    $table->define_baseurl($baseurl);

    $table->sortable(true, 'lastname', SORT_DESC);
    $table->set_attribute('cellspacing', '0');
    $table->set_attribute('id', 'showentrytable');
    $table->set_attribute('class', 'generaltable generalbox');
    $table->set_control_variables(array(
        TABLE_VAR_SORT => 'ssort',
        TABLE_VAR_IFIRST => 'sifirst',
        TABLE_VAR_ILAST => 'silast',
        TABLE_VAR_PAGE => 'spage'
    ));
    $table->setup();

    if ($table->get_sql_sort()) {
        $sort = $table->get_sql_sort();
    } else {
        $sort = '';
    }

    list($where, $params) = $table->get_sql_where();
    if ($where) {
        $where .= ' AND';
    }

    //get students in conjunction with groupmode
    //if ($groupmode > 0) {
    //    if ($mygroupid > 0) {
    //        $usedgroupid = $mygroupid;
    //    } else {
    //        $usedgroupid = false;
    //    }
    //} else {
    //    $usedgroupid = false;
    //}
    //$matchcount = evaluation_count_complete_users($id, $usedgroupid);
    $matchcount = evaluation_count_complete_users($id);
    $table->initialbars(true);

    if ($showall) {
        $startpage = false;
        $pagecount = false;
    } else {
        $table->pagesize($perpage, $matchcount);
        $startpage = $table->get_page_start();
        $pagecount = $table->get_page_size();
    }
    //$students = evaluation_get_complete_users($id, $usedgroupid, $where, $params, $sort, $startpage, $pagecount);
    $students = evaluation_get_complete_users($id, null, $where, $params, $sort, $startpage, $pagecount);
    $str_analyse = get_string('analysis', 'local_evaluations');
    $str_complete = get_string('completed_evaluations', 'local_evaluations');
    $str_course = get_string('course');

    //      $completed_fb_count = evaluation_get_completeds_group_count($evaluation, $mygroupid);
    //if ($evaluation->course == SITEID) {
    //    $analysisurl = new moodle_url('/local/evaluations/analysis_class.php', array('id'=>$id, 'clid'=>$classid));
    //    echo $OUTPUT->box_start('mdl-align');
    //    echo '<a href="'.$analysisurl->out().'">';
    //    echo $str_course.' '.$str_analyse.' ('.$str_complete.': '.intval($completed_fb_count).')';
    //    echo '</a>';
    //    echo $OUTPUT->help_icon('viewcompleted', 'local_evaluations');
    //    echo $OUTPUT->box_end();
    //} else {
    //$analysisurl = new moodle_url('/local/evaluations/analysis.php', array('id'=>$id, 'clid'=>$classid));
    //echo $OUTPUT->box_start('mdl-align');
    //echo '<a href="'.$analysisurl->out().'">';
    //echo $str_analyse.' ('.$str_complete.': '.intval($completed_fb_count).')';
    //echo '</a>';
    //echo $OUTPUT->box_end();
//        }
}

//####### viewreports-start
//    if (has_capability('mod/evaluation:viewreports', $context)) {
//print the list of students
//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
//echo isset($groupselect) ? $groupselect : '';
//echo '<div class="clearer"></div>';
echo $OUTPUT->box_start('mdl-align');
if (!$students) {
    //   $table->print_html();
} else {
    echo print_string('non_anonymous_entries', 'local_evaluations');
    echo ' (' . count($students) . ')<hr />';

    foreach ($students as $student) {
        $params = array('userid' => $student->id,
            'evaluation' => $evaluation->id,
            'anonymous_response' => EVALUATION_ANONYMOUS_NO);

        $completed_count = $DB->count_records('evaluation_completed', $params);
        if ($completed_count > 0) {

            //userpicture and link to the profilepage
            $fullname_url = $CFG->wwwroot . '/user/view.php?id=' . $student->id . '&amp;class=' . $classid;
            $profilelink = '<strong><a href="' . $fullname_url . '">' . fullname($student) . '</a></strong>';
            $data = array($OUTPUT->user_picture($student, array('classid' => $classid)), $profilelink);

            //link to the entry of the user
            $params = array('evaluation' => $evaluation->id,
                'userid' => $student->id,
                'anonymous_response' => EVALUATION_ANONYMOUS_NO);

            $evaluationcompleted = $DB->get_record('evaluation_completed', $params);
            $showentryurl_params = array('userid' => $student->id, 'do_show' => 'showoneentry');
            $showentryurl = new moodle_url($url, $showentryurl_params);
            $showentrylink = '<a href="' . $showentryurl->out() . '">' . userdate($evaluationcompleted->timemodified) . '</a>';
            $data[] = $showentrylink;

            //link to delete the entry
            //    if (has_capability('mod/evaluation:deletesubmissions', $context)) {
            $delete_url_params = array('id' => $id,
                'clid' => $classid,
                'completedid' => $evaluationcompleted->id,
                'do_show' => 'showoneentry');

            $deleteentryurl = new moodle_url($CFG->wwwroot . '/local/evaluations/delete_completed.php', $delete_url_params);
            $deleteentrylink = '<a href="' . $deleteentryurl->out() . '">' .
                    get_string('delete_entry', 'local_evaluations') .
                    '</a>';
            $data[] = $deleteentrylink;
            //  }
            $table->add_data($data);
        }
    }
    $table->print_html();

    $allurl = new moodle_url($baseurl);

    if ($showall) {
        $allurl->param('showall', 0);
        echo $OUTPUT->container(html_writer::link($allurl, get_string('showperpage', '', EVALUATION_DEFAULT_PAGE_COUNT)), array(), 'showall');
    } else if ($matchcount > 0 && $perpage < $matchcount) {
        $allurl->param('showall', 1);
        echo $OUTPUT->container(html_writer::link($allurl, get_string('showall', '', $matchcount)), array(), 'showall');
    }
}
?>
<hr />
<table width="100%">
    <tr>
        <td align="left" colspan="2">
<?php
$params = array('evaluation' => $evaluation->id,
    'anonymous_response' => EVALUATION_ANONYMOUS_YES);

$evaluation_completeds_count = $DB->count_records('evaluation_completed', $params);
print_string('anonymous_entries', 'local_evaluations');
echo '&nbsp;(' . $evaluation_completeds_count . ')';
?>
        </td>
        <td align="right">
            <?php
            $url_params = array('sesskey' => sesskey(),
                'userid' => 0,
                'clid' => $classid,
                'do_show' => 'showoneentry',
                'id' => $id);
            $aurl = new moodle_url('show_entries_anonym.php', $url_params);
            echo $OUTPUT->single_button($aurl, get_string('showreponses', 'local_evaluations'));
            ?>
        </td>
    </tr>
</table>
            <?php
            echo $OUTPUT->box_end();
            // echo $OUTPUT->box_end();
            //}
            //}
////////////////////////////////////////////////////////
/// Print the responses of the given user
////////////////////////////////////////////////////////
            if ($do_show == 'showoneentry') {
                echo $OUTPUT->heading(format_text($evaluation->name));

                //print the items
                if (is_array($evaluationitems)) {
                    $align = right_to_left() ? 'right' : 'left';
                    $usr = $DB->get_record('user', array('id' => $userid));

                    if ($evaluationcompleted) {
                        echo $OUTPUT->heading(userdate($evaluationcompleted->timemodified) . ' (' . fullname($usr) . ')', 3);
                    } else {
                        echo $OUTPUT->heading(get_string('not_completed_yet', 'local_evaluations'), 3);
                    }

                    echo $OUTPUT->box_start('evaluation_items');
                    $itemnr = 0;
                    foreach ($evaluationitems as $evaluationitem) {
                        //get the values
                        $params = array('completed' => $evaluationcompleted->id, 'item' => $evaluationitem->id);
                        $value = $DB->get_record('evaluation_value', $params);
                        echo $OUTPUT->box_start('evaluation_item_box_' . $align);
                        if ($evaluationitem->hasvalue == 1 AND $evaluation->autonumbering) {
                            $itemnr++;
                            echo $OUTPUT->box_start('evaluation_item_number_' . $align);
                            echo $itemnr;
                            echo $OUTPUT->box_end();
                        }

                        if ($evaluationitem->typ != 'pagebreak') {
                            echo $OUTPUT->box_start('box generalbox boxalign_' . $align);
                            if (isset($value->value)) {
                                evaluation_print_item_show_value($evaluationitem, $value->value);
                            } else {
                                evaluation_print_item_show_value($evaluationitem, false);
                            }
                            echo $OUTPUT->box_end();
                        }
                        echo $OUTPUT->box_end();
                    }
                    echo $OUTPUT->box_end();
                }
                echo $OUTPUT->continue_button(new moodle_url($url, array('do_show' => 'showentries')));
            }
/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

            echo $OUTPUT->footer();
            