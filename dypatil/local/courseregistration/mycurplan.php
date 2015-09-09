

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
 * @subpackage courseregistration
 * @copyright  2014 Vinodkumar aleti <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
global $CFG, $USER, $DB;
$exams = new schedule_exam();
$systemcontext = context_system::instance();
$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
$currentcss = '/local/collegestructure/css/styles.css';
$currentcss = '/local/courseregistration/css/styles.css';
$PAGE->requires->css($currentcss);
$PAGE->requires->js('/local/courseregistration/js/expand.js');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/mycur.php');

/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('mycurriculum', 'local_curriculum'));
$users = users::getInstance();
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('mycurriculum', 'local_curriculum'));

/* ---Moodle 2.2 and onwards--- */
$currenttab = 'myplan';
/* ---adding tabs--- */
$exams->studentside_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('mycurriculumdec', 'local_curriculum'));
}

// -----used get to cuurent active semesterid--------------------------------
$hier = new hierarchy();
$semesterid = $hier->get_allmyactivesemester($USER->id);
foreach ($semesterid as $key => $value)
    $semid_getclass = $key;
//--------------------------------------------------------------------------


$query = "SELECT cp.* FROM {local_userdata} u JOIN {local_curriculum} cp ON cp.id=u.curriculumid where u.userid={$USER->id} and u.curriculumid = {$cid}";
$currlicList = $DB->get_records_sql($query);
foreach ($currlicList as $list) {
    $table = new html_table();
    $table->head = array(get_string('course', 'local_cobaltcourses') . 's', get_string('status'), get_string('grades'), get_string('enrollsem', 'local_curriculum'));
    $table->size = array('40%', '20%', '20%', '20%');
    $table->align = array('left', 'center', 'center', 'center');
    $data = array();
    $out = '';
    if ($list->enableplan) {
        $curriculumpaths = $DB->get_records('local_curriculum_plan', array('curriculumid' => $list->id), $sort = 'sortorder');
        $hds = array();
        $rws = array();
        $grps = array();
        $i = 0;
        $j = 0;
        $out = '<table id="cur_customplan" width="100%" class="generaltable">';
        $out .= '<tr>
                                <th size="40%">' . get_string('course', 'local_cobaltcourses') . 's' . '</th>
                                <th size="20%">' . get_string('status') . '</th>
                                <th size="20%">' . get_string('grades') . '</th>
                                <th size="20%">' . get_string('enrollsem', 'local_curriculum') . '</th>
                        </tr>';


        foreach ($curriculumpaths as $plans) {
            $showdepth = 1;
            $plancourses = diplay_plancourse($plans->id);
            $countofrecord = sizeof($plancourses);

            $out .= '<tr align="center" class="header' . $i . '" onClick="fnslidetoggle(' . $i . ',' . $countofrecord . ')"><td colspan="3">' . display_curriculum_paths($plans, $showdepth) . '</td>
                        <td style="text-align: right;">';
            if ($i == 0)
                $out .= '<img class="smallimg" src="pix/expanded.svg" />';
            else
                $out .= '<img class="smallimg" src="pix/collapsed.svg" />';
            $out .= '</td></tr>';

            $indicate_depth = true;
            $itemdepth = ($indicate_depth) ? 'coursedepth' . min(4, $plans->depth) : 'coursedepth1';
            $k = 0;
            $m = 0;


            foreach ($plancourses as $courses) {

                $style = $i == 0 ? '' : 'style="display: none;"';
                $idname = "innerrow";
                $out .= '<tr ' . $style . ' class="row' . $i . '"  id="outerrow' . $m . '">';
                $out .= '<td size="40%"><div  class="' . $itemdepth . '"><a href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $courses->id . '&sesskey=' . sesskey() . '">' . $courses->shortname . ' </a>: ' . $courses->fullname . ' </div></td>';

                if (strip_tags($courses->status) == "Not Enrolled" or strip_tags($courses->status) == "waiting") {
                    $sta = strip_tags($courses->status);
                    if ($sta == 'waiting')
                        $course_status = 'Waiting for Approval';
                    else
                        $course_status = 'Not Enrolled';
                    $out .= '<td size="20%" onClick="innerslidetoggle(' . $k . ')" class="notenrolled_color"  style="cursor:pointer;" >' . $course_status . '<img class="simg' . $k . '" src="pix/collapsed.svg"></img></td>';
                } else
                    $out .= '<td size="20%" >' . $courses->status . '</td>';

                $grd = isset($courses->grade) ? '<b>' . $courses->grade . '</b>' : '<b>-</b>';
                $out .= '<td size="20%" style="text-align: center;">' . $grd . '</td>';
                $sem = isset($courses->semester) ? $courses->semester : '-';
                $out .= '<td size="20%">' . $sem . '</td>';
                $out .= '</tr>';
                if (strip_tags($courses->status) == "Not Enrolled" or ( strip_tags($courses->status)) == "waiting") {
                    $out .='<tr style="display: none;" class="custom_row "row' . $i . '" r1 "  id="innerrow' . $k . '" >';
                    if ($semid_getclass)
                        $out .= '<td  colspan="4">' . retrieve_listofclclasses_ofcourse($courses->courseid, $semid_getclass) . '</td>';
                    else
                        $out .= '<td  colspan="4">NO Active Semester is Available, To enroll the class. or Add and drop , Registration period is closed </td>';
                    $out .= '</tr>';
                }

                $k++;
                $m++;
            }
            $i++;
        }
        $out .= '</table>';
    } else {
        $ccourses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $cid, 'planid' => 0));
        $index = 0;
        foreach ($ccourses as $ccourse) {
            $flag = 0;
            $course = $DB->get_record('local_cobaltcourses', array('id' => $ccourse->courseid));
            $line = array();
            $line[] = '<a href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->id . '&sesskey=' . sesskey() . '">' . $course->shortname . ' </a>: ' . $course->fullname;
            list($course->status, $course->grade) = get_course_enrolledstatus($course->id, $USER->id);
            if ($course->status == 'Completed')
                $course->status = '<span class="completed_color"  >' . $course->status . '</span>';
            else if (($course->status == 'Not Enrolled') or ( $course->status == 'waiting')) {
                if ($course->status == 'waiting')
                    $course_status = 'Waiting for Approval';
                else
                    $course_status = 'Not Enrolled';
                $flag = 1;
                $idname = 'innerdiv';
                $course->status = '<span class="notenrolled_color" id="outerrow' . $index . '" onclick="plantoggle(' . $index . ')"  style="cursor:pointer;">' . $course_status . '<img class="simg' . $index . '" src="pix/collapsed.svg"></img></span>';
            } else
                $course->status = '<span class="inprogress_color" >' . $course->status . '</span>';
            $line[] = $course->status;
            if ($course->status != 'Not Enrolled') {
                $course->semester = $users->get_coursestatus($course->id, $USER->id, true);
            }
            $line[] = isset($course->grade) ? '<b>' . $course->grade . '</b>' : '<b>-</b>';
            $line[] = isset($course->semester) ? $course->semester : '-';
            $data[] = $line;
            if ($flag == 1) {
                if ($semid_getclass)
                    $msg = new html_table_cell(retrieve_listofclclasses_ofcourse($ccourse->courseid, $semid_getclass));
                else
                    $msg = new html_table_cell('NO Active Semester is Available, To enroll the class ,or Add and drop , Registration period is closed');
                $msg->colspan = 4;
                $row = new html_table_row(array($msg));
                $row->id = "innerdiv$index";
                $row->attributes['class'] = "customrow";
                $data[] = $row;
                $index++;
            }
        }
    }
    // print_object($data); 

    $table->data = $data;
    $table->id = "cur_customplan";
    echo '<div style="border:0px solid red" id="hierarchy-index">';
    $desc = student_currculum_progress($cid, $list->schoolid);
    echo $out;
    if ($data) {

        echo html_writer::table($table);
    }
    echo '</div>';
    echo '<br/>';
}
echo $OUTPUT->footer();
