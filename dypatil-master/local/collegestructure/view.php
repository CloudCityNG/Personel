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
 * @subpackage School
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/collegestructure/lib.php');
$schoolid = optional_param('id', 0, PARAM_INT);
global $CFG;
/* ---First level of checing--- */
require_login();
$systemcontext = context_system::instance();
?>
<script type="text/javascript" language="javascript" src="jquery.js"></script>
<link rel="stylesheet" href="http://code.jquery.com/ui/1.10.3/themes/smoothness/jquery-ui.css" />
<script src="http://code.jquery.com/jquery-1.9.1.js"></script>
<script src="http://code.jquery.com/ui/1.10.3/jquery-ui.js"></script>

<?php

/* ---get the admin layout--- */
$PAGE->requires->js('/local/collegestructure/js/view.js');
$PAGE->requires->css('/local/collegestructure/css/view.css');
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check whether the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
/* ---second level of checking--- */
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');

    redirect($returnurl);
}
$PAGE->set_url('/local/collegestructure/view.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), "/local/collegestructure/index.php", get_string('viewschool', 'local_collegestructure'));
$PAGE->navbar->add(get_string('viewschool', 'local_collegestructure'));
echo $OUTPUT->header();
/* ---Get the records from the database--- */

if (!$school = $DB->get_record('local_school', array('id' => $schoolid))) {
    print_error('invalidschoolid');
}

echo $OUTPUT->heading($school->fullname);
echo $OUTPUT->box($school->description);




//echo get_string('programsanddepartments','local_collegestructure');
//echo $OUTPUT->heading($school->fullname);
$programs = $DB->get_records('local_program', array('schoolid' => $schoolid), 'visible DESC');
if (!$departments = $DB->get_records_sql("SELECT * FROM {local_department} WHERE visible=1 AND schoolid = $schoolid ORDER by visible DESC")) {
    $departments = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $schoolid GROUP BY sd.deptid ORDER BY d.visible DESC");
}

echo '<div id="firstpane" class="menu_list programs_list">';
foreach ($programs as $program) {
    $curriculums = $DB->get_records('local_curriculum', array('programid' => $program->id), 'visible DESC');
    $cur_info = empty($curriculums) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('curriculum', 'local_curriculum') . 's)</span>' : '';
    $level = $program->programlevel == 1 ? 'Undergraduate' : 'Graduate';
    $visible = $program->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
    echo '<p class="menu_head menu_program"><b>' . get_string('program', 'local_programs') . ': </b>' . $program->fullname . $cur_info . '<span style="float: right;"> ' . $program->duration . ' years ' . $level . '</span> ' . $visible . '</p>';
    echo '<div class="menu_body">';
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
    foreach ($curriculums as $curriculum) {
        $visible = $curriculum->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
        if ($curriculum->enableplan) {
            $plans = $DB->get_records('local_curriculum_plan', array('curriculumid' => $curriculum->id), 'visible DESC');
            $plan_info = empty($plans) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cuplan', 'local_curriculum') . 's)</span>' : '';
            echo '<p class="menu_head menu_curriculum"><b>' . get_string('curriculum', 'local_curriculum') . ': </b>' . $curriculum->fullname . ' - Plans Enabled ' . $plan_info . $visible . '</p>';
            echo '<div class="menu_body">';
            echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
            foreach ($plans as $plan) {
                $visible = $plan->visible ? '<span class="visible" style="float: right;"> Active &nbsp;</span>' : '<span style="float: right;color:#FA440D;"> Inactive &nbsp;</span>';
                $plan_courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $curriculum->id, 'planid' => $plan->id));
                $course_info = empty($plan_courses) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
                echo '<p class="menu_head menu_plan"><b>' . get_string('cuplan', 'local_curriculum') . ': </b>' . $plan->fullname . $course_info . $visible . '</p>';
                echo '<div class="menu_body">';
                echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
                foreach ($plan_courses as $plan_course) {
                    list_from_courses($plan_course->courseid);
                }
                echo "</div>"; //.firstpane
                echo "</div>"; //.menu_body
            }
            echo "</div>"; //.firstpane
            echo "</div>"; //.menu_body
        } else {
            $cur_courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $curriculumid, 'planid' => 0));
            $course_info = empty($cur_courses) ? ' <span style="float: right;color:#FA440D;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
            echo '<p class="menu_head menu_curriculum"><b>' . get_string('curriculum', 'local_curriculum') . ': </b>' . $curriculum->fullname . ' - ' . get_string('cuplan', 'local_cobaltcourses') . ' Not Enabled ' . $course_info . $visible . '</p>';
            echo '<div class="menu_body">';
            echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
            foreach ($cur_courses as $cur_course) {
                list_from_courses($cur_course->courseid);
            }
            echo "</div>"; //.firstpane
            echo "</div>"; //.menu_body
        }
    }
    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body
}
echo '</div>'; //.menu_list


echo '<div id="firstpane" class="menu_list departments_list">';
foreach ($departments as $department) {
    $courses = $DB->get_records('local_cobaltcourses', array('departmentid' => $department->id), 'visible DESC');
    $course_info = empty($courses) ? ' <span style="float: right;color:#FA5D08;">(No ' . get_string('cobaltcourses', 'local_cobaltcourses') . ' Available)</span>' : '';
    $inst_list = $DB->get_records_sql('select ld.* from {local_dept_instructor} ld, {user} u where  ld.departmentid='.$department->id.' and ld.instructorid=u.id and u.deleted=0');
    $inst_info = empty($inst_list) ? ' <span style="float: right;color:#FA5D08;">(No ' . get_string('instructor', 'local_clclasses') . 's Assigned)</span>' : '';
    echo '<p class="menu_head menu_department"><b>' . get_string('department', 'local_departments') . ': </b>' . $department->fullname . $course_info . $visible . '</p>';
    echo '<div class="menu_body">';
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
    echo '<p class="menu_head menu_instlist"><b>' . get_string('instructor', 'local_clclasses') . 's </b>' . $inst_info . '</p>';
    echo '<div class="menu_body">';
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
    foreach ($inst_list as $inst) {
        $user = $DB->get_record('user', array('id' => $inst->instructorid));
        echo '<p class="menu_head menu_inst"><b>' . get_string('instructor', 'local_clclasses') . ': </b>' . fullname($user) . '</p>';
    }
    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body

    echo '<p class="menu_head menu_courselist"><b>' . get_string('cobaltcourses', 'local_cobaltcourses') . ' </b>' . $course_info . '</p>';
    echo '<div class="menu_body">';
    echo '<div id="firstpane" class="menu_list" style="margin-left:2%">';
    foreach ($courses as $course) {
        list_from_courses($course->id);
    }
    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body

    echo "</div>"; //.firstpane
    echo "</div>"; //.menu_body
}
echo '</div>';



echo $OUTPUT->footer();
