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
 * @subpackage departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/departments/lib.php');
//  department id
$id = required_param('id', PARAM_INT);
global $CFG, $DB;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/departments/viewdept.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('view_dept', 'local_departments'));
echo $OUTPUT->header();
//-------------manage_dept heading------------------------
echo $OUTPUT->heading(get_string('manage_dept', 'local_departments'));
$dept_ob = manage_dept::getInstance();

try {
    $dept = $DB->get_record('local_department', array('id' => $id));
    $PAGE->set_title(get_string('department', 'local_departments') . ': ' . $dept->fullname);

// checking if login user is registrar or admin
    $schoolid = $dept_ob->check_loginuser_registrar_admin();

    $dept_ob = manage_dept::getInstance();
//$currenttab = 'view';   
//$dept_ob->dept_tabs($currenttab,'view');
    //Heading of the page
    echo $OUTPUT->heading($dept->fullname); //Semester Name

    $schoolname = $DB->get_record('local_school', array('id' => $dept->schoolid));
    //$data[]=array('<b>School Assigned to</b>',$schoolname->fullname);

    $data[] = array('<b>' . get_string('deptid', 'local_departments') . '</b>', $dept->shortname . '( ' . get_string('belongs', 'local_departments') . ' <b>' . $schoolname->fullname . '</b>)');
    if (!empty($dept->description_text)) {
        $data[] = array('<b>' . get_string('description', 'local_departments') . '</b>', $dept->description_text);
    }
    // list of assigned school for the perticular department
    $school_list = $DB->get_records('local_assignedschool_dept', array('deptid' => $dept->id));
    if (!empty($school_list)) {
        $school_name = '';
        foreach ($school_list as $sl) {
            $sn = $DB->get_record('local_school', array('id' => $sl->assigned_schoolid));
            $school_name.= $sn->fullname . ',';
        }
        $school_name = substr($school_name, 0, -1);
        $data[] = array('<b>' . get_string('assigned_school', 'local_departments') . '</b>', $school_name);
    }

    // list of assigned instructors for the perticular department
    $instructor_list = $DB->get_records('local_dept_instructor', array('departmentid' => $dept->id));
    if (!empty($instructor_list)) {
        $instructor_name = '';
        foreach ($instructor_list as $sl) {
            $sn = $DB->get_record('user', array('id' => $sl->instructorid));
            $fullname = $sn->firstname . ' ' . $sn->lastname;
            $instructor_name.= $fullname . ',';
        }
        //  removing last char(,) 
        $instructor_name = substr($instructor_name, 0, -1);
        $data[] = array('<b>' . get_string('assigned_instructor', 'local_departments') . '</b>', $instructor_name);
    }

// list of cobalt courses 
    $courses_list = $DB->get_records('local_cobaltcourses', array('departmentid' => $dept->id));
    if (!empty($courses_list)) {
        $courses_name = '';
        $i = 1;
        foreach ($courses_list as $sl) {
            $cou = $i . ' ' . $sl->fullname;

            $courses_name.= '<div>' . html_writer::tag('a', $cou, array('href' => '' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $sl->id . '&title=departments&plugin=departments&page=index')) . '</div>';
            $i++;
        }
        //  removing last char(,) 
        //$courses_name =substr($courses_name,0,-1);
        $data[] = array('<b>' . get_string('assign_course', 'local_departments') . '</b>', $courses_name);
    }

    $table = new html_table();
    $table->align = array('left', 'left');
    $table->size = array('30%');
    $table->width = '100%';
    $table->data = $data;
    echo html_writer::table($table);
} //--------- end of try block---------------------------------------------
catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




