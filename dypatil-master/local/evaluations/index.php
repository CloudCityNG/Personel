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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage Academiccalendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/evaluations/lib.php');
$hierarchy = new hierarchy();
$systemcontext =context_system::instance();

$PAGE->set_url('/local/evaluations/index.php');
$PAGE->set_pagelayout('admin');
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_evaluations'));
//Header and the navigation bar
$PAGE->set_heading(get_string('pluginname', 'local_evaluations'));
$PAGE->navbar->add(get_string('viewevaluations', 'local_evaluations'));
$usercontext = context_user::instance($USER->id);
$systemcontext =context_system::instance();
echo $OUTPUT->header();

//Heading of the page
$evaltype = array('1' => get_string('evoltype2', 'local_evaluations'),
    '2' => get_string('evoltype3', 'local_evaluations'),
    '3' => get_string('evoltype4', 'local_evaluations'));
if (has_capability('local/clclasses:enrollclass', $usercontext) && !is_siteadmin()) {
    $assigned_clclasses = $DB->get_records_sql('select classid from {local_user_clclasses} where userid=' . $USER->id . ' and registrarapproval=1');

    foreach ($assigned_clclasses as $assigned_class) {
        $ass_class[] = $assigned_class->classid;
    }
    if (!empty($ass_class)) {
        $ass_classstring = implode(',', $ass_class);
        $evaluations = $DB->get_records_sql('select * from {local_evaluation} where classid in (' . $ass_classstring . ') and (evaluationtype=1 OR evaluationtype=3) AND publish_stats=1');
    }
} else if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
    $assigned_s=$DB->get_records('local_school_permissions',array('userid'=>$USER->id));
    if(empty($assigned_s))
        throw new notassignedschool_exception();
    $evaluations = $DB->get_records_sql("SELECT * FROM {local_evaluation} where evaluationtype=2 AND FIND_IN_SET('$USER->id',evaluatedinstructor) AND publish_stats=1");
} elseif (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
    $assigned_schools = $hierarchy->get_assignedschools();
    foreach ($assigned_schools as $assigned_school) {
        if ($assigned_school->id != null) {
            $aschools[] = $assigned_school->id;
        }
    }
    $schoollist = implode(',', $aschools);
    empty($schoollist) ? $schoollist = 0 : $schoollist;
    $clclasses = $DB->get_records_sql("SELECT * FROM {local_clclasses} WHERE schoolid in ($schoollist)");
    foreach ($clclasses as $class) {

        $asclass[] = $class->id;
    }
    $clclassesstring = implode(',', $asclass);
    if (!empty($clclassesstring)) {
        $evaluations = $DB->get_records_sql("SELECT * FROM {local_evaluation} WHERE classid in ($clclassesstring)");
    }
} elseif (is_siteadmin()) {
    $sl=$DB->get_records('local_school',array('visible'=>1));
    if(empty($sl))
     throw new schoolnotfound_exception();    
    $evaluations = $DB->get_records('local_evaluation');
}
$data = array();
foreach ($evaluations as $evaluation) {
    $line = array();
    $params = array();
    // $params['id'] = $evaluation->id;
    // $params['clid'] = $evaluation->classid;
    // $view = new moodle_url(''.$CFG->wwwroot.'/local/evaluations/view.php',$params);
    // $line[] = html_writer::link($view, $evaluation->name);
    $linkcss = $evaluation->publish_stats ? ' ' : 'class="dimmed" ';
    $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/evaluations/view.php?id=' . $evaluation->id . '&clid=' . $evaluation->classid . '">' . format_string($evaluation->name) . '</a>';
    $line[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $evaluation->classid));
    $line[] = $evaltype[$evaluation->evaluationtype];

    $sem = $DB->get_record_sql("SELECT s.fullname
                                             FROM mdl_local_semester as s
                                             JOIN mdl_local_clclasses as c
                                             ON s.id=c.semesterid
                                             WHERE c.id=$evaluation->classid");
    $line[] = $sem->fullname;
    $sandp = $DB->get_record_sql('SELECT s.fullname as schoolname
                                                        FROM mdl_local_school as s,mdl_local_scheduleclass as l
                                                        WHERE l.schoolid = s.id and l.instructorid=' . $evaluation->evaluatinginstructor . ' and l.classid = ' . $evaluation->classid . '');
    $completedscount = evaluation_get_completeds_group_count($evaluation);
    $line[] = $sandp->schoolname;
    $params = array('evaluation' => $evaluation->id, 'hasvalue' => 1);
    $itemscount = $DB->count_records('evaluation_item', $params);
    $evaluation_can_submit = true;
    if ($evaluation->multiple_submit == 0) {
        if (evaluation_is_already_submitted($evaluation->id, $evaluation->classid)) {
            $evaluation_can_submit = false;
        }
    }

    if ($evaluation_can_submit) {

        $checktime = time();
        if (($evaluation->timeopen > $checktime) OR ( $evaluation->timeclose < $checktime AND $evaluation->timeclose > 0)) {
            $ected = '<font color="red">';
            $ected .= get_string('evaluation_is_not_open', 'local_evaluations');
            $ected .= '</font>';
            $line[] = $ected;
        } else {
            //if the user is not known so we cannot save the values temporarly
            if (!isloggedin() or isguestuser()) {
                $completefile = 'complete_guest.php';
                $guestid = sesskey();
            } else {
                $completefile = 'complete.php';
                $guestid = false;
            }

            $url_params = array('id' => $evaluation->id, 'clid' => $evaluation->classid, 'gopage' => 0);
            $completeurl = new moodle_url('/local/evaluations/' . $completefile, $url_params);

            $evaluationcompletedtmp = evaluation_get_current_completed($evaluation->id, true, $evaluation->classid, $guestid);
            if ($evaluationcompletedtmp) {
                if ($startpage = evaluation_get_page_to_continue($evaluation->id, $evaluation->classid, $guestid)) {
                    $completeurl->param('gopage', $startpage);
                }
                if ($itemscount > 0) {
                    $line[] = '<a href="' . $completeurl->out() . '">' . get_string('continue_the_form', 'local_evaluations') . '</a>';
                } else {
                    $line[] = "No questions are available";
                }
            } else {
                if ($itemscount > 0) {
                    $line[] = '<a href="' . $completeurl->out() . '">' . get_string('complete_the_form', 'local_evaluations') . '</a>';
                } else {
                    $line[] = "No questions are available";
                }
            }
        }
    } else {
        $cted = '<font color="red">';
        $cted .= get_string('this_evaluation_is_already_submitted', 'local_evaluations');
        $cted .= '</font>';
        $line[] = $cted;
    }
//$line[] =$completedscount;
    $buttons = html_writer::link(new moodle_url('/local/evaluations/create_evaluation.php', array('id' => $evaluation->id, 'clid' => $evaluation->classid, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons .= html_writer::link(new moodle_url('/local/evaluations/create_evaluation.php', array('id' => $evaluation->id, 'clid' => $evaluation->classid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    if ($evaluation->publish_stats > 0) {
        $buttons .= html_writer::link(new moodle_url('/local/evaluations/create_evaluation.php', array('id' => $evaluation->id, 'clid' => $evaluation->classid, 'visible' => !$evaluation->publish_stats, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
    } else {
        $buttons .= html_writer::link(new moodle_url('/local/evaluations/create_evaluation.php', array('id' => $evaluation->id, 'clid' => $evaluation->classid, 'visible' => !$evaluation->publish_stats, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
    }
    /*
     * ###Bugreport#170-Evaluations
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Changed the capability to check permission
     */
    if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
        $line[] = $buttons;
    }

    $data[] = $line;
}
$currenttab = 'view';
require('tabs.php');


// Moodle 2.2 and onwards
// echo $OUTPUT->heading(get_string('viewevaluations', 'local_evaluations'));
//View Part starts
echo $OUTPUT->box(get_string('view_eval', 'local_evaluations'));
echo "<div id='filter-box' >";
if (!empty($evaluation)) {
    $PAGE->requires->js('/local/evaluations/evalfilter.js');
    echo '<div class="filterarea"></div></div>';
}

$table = new html_table();
$table->id = "cooktable";
//$table->attributes =  array('onclick'=>'fnShowHide(0);');
$table->head = array(get_string('name', 'local_evaluations'),
    get_string('class', 'local_clclasses'),
    get_string('evaluation_type', 'local_evaluations'),
    get_string('semester', 'local_semesters'),
    get_string('schoolid', 'local_collegestructure'),
    get_string('estatus', 'local_evaluations')
);
if (has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
    $table->head[] = get_string('action');
}
$table->width = '100%';
$table->align = array('left', 'left', 'left', 'left', 'left');
$table->size = array('30%', '10%', '32%', '10%', '18%');
$table->data = $data;
if (empty($evaluations))
    echo get_string('exception_evaluations', 'local_evaluations');
else
    echo html_writer::table($table);
echo '<div id="contents"></div>';
echo $OUTPUT->footer();
?>