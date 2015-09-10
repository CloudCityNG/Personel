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
 * @subpackage Classes
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$inst = optional_param('inst', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$examid = optional_param('examid', 0, PARAM_INT);
global $CFG;
$currentjs = '/local/clclasses/js/filter.js';
$PAGE->requires->js($currentjs);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$semclass = new schoolclasses();
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/clclasses/index.php');
$PAGE->set_heading($SITE->fullname);
if ($inst) {
    $PAGE->navbar->add(get_string('mycourses', 'local_clclasses'), "/local/clclasses/instview.php", get_string('view', 'local_clclasses'));
} else {
    $PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), "/local/clclasses/index.php", get_string('view', 'local_clclasses'));
}
$PAGE->navbar->add(get_string('classcriteria', 'local_clclasses'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('classcriteria', 'local_clclasses'));
if ($delete) {

    $semclass->classes_remove_criteria($examid, $id);
}


if ($data = data_submitted()) {

    if (!$destschool = $DB->get_record('local_clclasses', array('id' => $id))) {
        print_error('cannotfindschool', '', '', $data);
    }
    $currenturl = "{$CFG->wwwroot}/local/clclasses/examsetting.php";

    $examids = array();
    $onlineexams = array();
    foreach ($data as $key => $value) {

        if (preg_match('/^c\d+$/', $key)) {
            $examid = substr($key, 1);
            array_push($examids, $examid);
        }
        if (preg_match('/^d\d+$/', $key)) {
            $onlinexam = substr($key, 1);
            array_push($onlineexams, $onlinexam);
        }
    }
    $semclass->class_completion($id, $examids, $onlineexams);
}
$online = "SELECT * FROM {local_clclasses} c where c.id={$id}";
$online = $DB->get_record_sql($online);
echo '<div><h3 > ' . get_string('completioncri', 'local_clclasses') . ':&nbsp;<b>' . $online->fullname . '</b></h3></div>';
echo $OUTPUT->box(get_string('setcriteria', 'local_clclasses'));
echo '<form id="movemodules" action="examsetting.php?id=' . $id . '" method="post" "><div>';
echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
$sql = "select se.*,et.examtype,ll.lecturetype FROM {local_scheduledexams} se,{local_examtypes} et,{local_lecturetype} ll where se.examtype=et.id AND se.lecturetype=ll.id AND se.classid={$id}";
$classes = $DB->get_records_sql($sql);
echo '<h4>' . get_string('offline', 'local_clclasses') . ' Exams </h4>';
if ($classes) {
    foreach ($classes as $classexam) {
        /*
         * ###Bugreport #137-Training Management
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Checking grade submitted or what for particular class
         */
        $checkexist = array();
        $checkgradeentry = $DB->get_record('local_user_examgrades', array('classid' => $id));
        $checkexist = $DB->get_record('local_class_completion', array('examid' => $classexam->id, 'classid' => $id));
        if ($checkexist)
            $startclass = 'class="courseassign" disabled="disabled"';
        else
            $startclass = 'class="courseassign"';
        echo '<table><tr>';
        echo '<td align="center">';
        echo '<input  type="checkbox" name="c' . $classexam->id . '" ' . $startclass . '   id="check_box"/>';
        echo '</td>';
        echo '<td>';
        echo $classexam->examtype . '&nbsp;-&nbsp;' . $classexam->lecturetype;
        echo '</td>';

        echo '<td>';
        if ($checkexist) {
            $grade_status = $DB->get_field('local_user_examgrades', 'finalgrade', array('classid' => $id, 'examid' => $classexam->id));
            if (!$grade_status) {
                $cancel = html_writer::link(new moodle_url('/local/clclasses/examsetting.php', array('examid' => $checkexist->id, 'id' => $id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('removecriteria', 'local_clclasses'), 'alt' => get_string('removecriteria', 'local_clclasses'), 'class' => 'iconsmall')));
                echo '&nbsp;' . $cancel;
            } else {
                $cancel = html_writer::link('#', html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('cantremovecriteria', 'local_clclasses'), 'alt' => get_string('cantremovecriteria', 'local_clclasses'), 'class' => 'disremove')));
                echo '&nbsp;' . $cancel;
            }
        } else
            echo "&nbsp;";
        echo '</td>';

        echo "</tr></table>";
        // }
    }
} else
    echo get_string('noexam', 'local_clclasses') . "</br>";

if (($online->online == 1) && !empty($online->onlinecourseid)) {
    echo '<h4>' . get_string('onlinegradact', 'local_clclasses') . ' </h4>';
    $sql = "select * FROM {grade_items} gi where courseid={$online->onlinecourseid} AND categoryid !='NULL' AND itemtype !='course' ";
    $onlineexams = $DB->get_records_sql($sql);

    foreach ($onlineexams as $onlineexm) {
        /*
         * ###Bugreport #137-Training Management
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Checking grade submitted or what for particular class
         */
        $checkexist = $DB->get_record('local_class_completion', array('examid' => $onlineexm->id, 'classid' => $id));
        if ($checkexist)
            $startclass = 'disabled="disabled"';
        else
            $startclass = 'class="courseassign"';
        echo '<table><tr>';

        echo '<td align="center">';

        echo '<input  type="checkbox" name="d' . $onlineexm->id . '" ' . $startclass . '   id="check_box"/>';
        echo '</td>';
        echo '<td>';
        echo $onlineexm->itemname;
        /*
         * ### Bugreport #172- Online Class
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Added delete buttons to criteria's which already set
         */
        if ($checkexist) {
            $grade_status = $DB->get_field('local_user_examgrades', 'finalgrade', array('classid' => $id, 'examid' => $onlineexm->id));
            if (!$grade_status) {
                $cancel = html_writer::link(new moodle_url('/local/clclasses/examsetting.php', array('examid' => $checkexist->id, 'id' => $id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('removecriteria', 'local_clclasses'), 'alt' => get_string('removecriteria', 'local_clclasses'), 'class' => 'iconsmall')));
                echo '&nbsp;' . $cancel;
            } else {
                $cancel = html_writer::link('#', html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('cantremovecriteria', 'local_clclasses'), 'alt' => get_string('cantremovecriteria', 'local_clclasses'), 'class' => 'disremove')));
                echo '&nbsp;' . $cancel;
            }
        }
        echo '</td>';

        echo "</tr></table>";
    }
}
$presentsem = $DB->get_field('local_clclasses', 'semesterid', array('id' => $id));
$presentsem_dates = $DB->get_record('local_semester', array('id' => $presentsem));
$today = time();
$today = make_timestamp(date('Y', $today), date('m', $today), date('d', $today), 0, 0, 0);

if ($presentsem_dates->startdate < $today && $presentsem_dates->enddate > $today)
    echo '<input type="submit" id="movetoid" ' . $startclass . ' value="Submit Exam Criteria" />';
else
    echo get_string('notinactivesem', 'local_clclasses');

echo '</div></form>';

echo $OUTPUT->footer();
