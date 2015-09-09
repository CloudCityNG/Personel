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
 * @subpackage Course Registration
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/clclasses/filters/class_filter_form.php');
require_once($CFG->dirroot . '/local/clclasses/filters/class.php');
$PAGE->requires->js('/local/courseregistration/js/validate.js');
global $CFG, $USER, $DB;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$sort = optional_param('sort', 'name', PARAM_ALPHANUM);
$dir = optional_param('dir', 'ASC', PARAM_ALPHA);
$semesterid = optional_param('semester', 0, PARAM_INT);
$perpage = 10;
$usercurculum = $DB->get_field('local_userdata', 'curriculumid', array('userid' => $USER->id));
//get the admin layout
global $CFG, $USER;
$PAGE->set_context($systemcontext);

if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/index.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageregistration', 'local_courseregistration'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageregistration', 'local_courseregistration'));
// Moodle 2.2 and onwards
$currenttab = "courseregistration";
print_studenttabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('courseregistrationdes', 'local_courseregistration'));
}

/* start of the filters functions */
$filters = new classfiltering();
$sort = "id";
$fullnamedisplay = "Course Name";
$clclassesshortname = "Short Name";
$school = "School";
$coursename = "Credit Hours";
$classlimit = "Course Type";

list($extrasql, $params) = $filters->get_sql_filter();
if ($extrasql) {
    $classcount = get_classcount($extrasql, $params);
} else
    $classcount = $DB->count_records('local_clclasses');

$clclasses = get_class_listing($sort, $dir, $page * $perpage, $perpage, $extrasql, $params);
$baseurl = new moodle_url('/local/courseregistration/index.php', array('sort' => $sort, 'dir' => $dir, 'perpage' => $perpage));

if (!$clclasses) {
    $data = array();
    $line = array();
    $line[] = get_string('nocoursefound', 'local_courseregistration');
    $data[] = $line;

    $table = new html_table();
} else if ($extrasql) {
    $table = new html_table();
    $table->id = "classtable";
    $table->head[] = $fullnamedisplay;
    $table->head[] = $clclassesshortname;
    $table->head[] = $school;
    $table->head[] = $coursename;
    $table->head[] = $classlimit;
    $table->head[] = get_string('action');
    $data = array();
    foreach ($clclasses as $class) {
        $course_exist = $DB->get_record('local_curriculum_plancourses', array('curriculumid' => $usercurculum, 'courseid' => $class->id));
        $line = array();
        $school = $DB->get_record('local_school', array('id' => $class->schoolid));
        if (empty($course_exist)) {
            $star = '<b style="color:red;">*</b>';
            $line[] = $class->fullname . $star;
        } else {
            $line[] = $class->fullname;
        }
        $line[] = $class->shortname;
        $line[] = $school->fullname;
        $line[] = $class->credithours;
        if ($class->coursetype == 0)
            $line[] = get_string('general', 'local_courseregistration');
        if ($class->coursetype == 1)
            $line[] = get_string('elective', 'local_courseregistration');
        if (!$class->visible) {
            foreach ($line as $k => $v) {
                $line[$k] = html_writer::tag('span', $v, array('class' => 'usersuspended'));
            }
        }
        $line[] = '<a  title="View Classes" href="' . $CFG->wwwroot . '/local/courseregistration/viewclasses.php?id=' . $class->id . '&semid=' . $class->semesterid . '&sesskey=' . sesskey() . '">' . get_string('viewclclasses', 'local_courseregistration') . '</a>';

        $data[] = $line;
    }
} else {
    $table = new html_table();
    $data = array();
    $line = array();
    $line[] = get_string('selectsemester', 'local_semesters');
    $data[] = $line;
}

$filters->display_add();
$filters->display_active();
if ($extrasql)
    echo $OUTPUT->paging_bar($classcount, $page, $perpage, $baseurl);
$table->id = "classtable";
$table->size = array('20%', '20%', '15%', '15%', '20%', '20%');
$table->align = array('left', 'center', 'left', 'center', 'center', 'left');
$table->width = '100%';
$table->style = "display:block";
$table->data = $data;
echo '<p><b>Note :</b> Here <span style="color:red;">*</span> Mark indicates that courses are not included in your curriculum.</p>';
echo html_writer::table($table);

if ($extrasql)
    echo $OUTPUT->paging_bar($classcount, $page, $perpage, $baseurl);
echo $OUTPUT->footer();
