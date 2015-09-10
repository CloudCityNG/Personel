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

/*
 * @package    local
 * @subpackage modules
 * @copyright  2013 Sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/modules/lib.php');
$id = optional_param('id', 0, PARAM_INT); // Department id
$scid = optional_param('scid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT); // which page to show
$moveto = optional_param('moveto', 0, PARAM_INT);
$moduleids = optional_param('moduleid', 0, PARAM_INT);
;
$defaultperpage = 20;
$perpage = optional_param('perpage', $defaultperpage, PARAM_INT); // how many per page
global $DB;
require_login();
$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managemodule', 'local_modules'), "/local/modules/index.php", get_string('viewmodules', 'local_modules'));
$PAGE->navbar->add(get_string('assigncourses', 'local_modules'));
$PAGE->set_url(new moodle_url('/local/modules/assigncourse.php', array('id' => $id)));
$string = get_string('managemodule', 'local_modules') . ':' . get_string('assigncourses', 'local_modules');
$PAGE->set_title($string);
$PAGE->set_pagelayout('admin');
$instance = new cobalt_modules();
echo $OUTPUT->header();

$returnurl = new moodle_url('/local/modules/assigncourse.php', array('id' => $id));

if (!empty($moveto) and $data = data_submitted()) {
    ;
    if (!$destcategory = $DB->get_record('local_module', array('id' => $data->moveto))) {
        print_error('cannotfindmodules', '', '', $data->moveto);
    }
    $courses = array();
    foreach ($data as $key => $value) {
        if (preg_match('/^c\d+$/', $key)) {
            $courseid = substr($key, 1);
            array_push($courses, $courseid);
        }
    }
    $instance->add_courses($courses, $data->moveto);
}
// Prepare the standard URL params for this page. We'll need them later.
$urlparams = array('id' => $id);
if ($page) {
    $urlparams['page'] = $page;
}
if ($perpage) {
    $urlparams['perpage'] = $perpage;
}

echo $OUTPUT->heading(get_string('assigncourses', 'local_modules'));
$tools = $DB->get_records('local_module_course', array('moduleid' => $moduleids));
$data = array();
$modulelist = $DB->get_record('local_module', array('id' => $moduleids));
$prgname = $DB->get_field('local_program', 'fullname', array('id' => $modulelist->programid));
$schoolname = $DB->get_field('local_school', 'fullname', array('id' => $modulelist->schoolid));
$oout = '<table><tr><td><strong>' . get_string('modulename', 'local_modules') . '</strong></td><td> : ' . '' . $modulelist->fullname . '</td></tr>';
$oout.='<tr><td><strong>' . get_string('assign_prog', 'local_modules') . '</strong></td><td> : ' . '' . $prgname . '</td></tr>';
$oout.='<tr><td><strong>' . get_string('assign_school', 'local_modules') . ' </strong></td><td> : ' . '' . $schoolname . '</td></tr></table>';
echo $oout;
if ($tools) {
    foreach ($tools as $tool) {
        $line = array();
        $coursename = $instance->get_coursename($tool->courseid);

        $line[] = '<a title="Assign Course"  href="../cobaltcourses/view.php?id=' . $tool->courseid . '&plugin=modules&page=index&title=Modules">' . $coursename . '</a>';
        $buttons = array();
        $buttons[] = html_writer::link(new moodle_url('/local/modules/module.php', array('id' => $moduleids, 'courseid' => $tool->courseid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_modules'), 'title' => get_string('unassign', 'local_modules'), 'class' => 'iconsmall')));
        $line[] = implode(' ', $buttons);
        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = get_string('no_course', 'local_modules');
    $data[] = $line;
}
$PAGE->requires->js('/local/modules/js/assign.js');
echo '<div class="filter"></div>';
$table = new html_table();
$table->id = "coursetable";
if ($tools) {
    $table->head = array(get_string('coursename', 'local_modules'), get_string('unassigncourse', 'local_modules'));
}
$table->size = array('20%', '20%', '10%', '10%', '10%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo '<hr>';
echo $OUTPUT->box(get_string('assigndesc', 'local_modules'));
echo '<hr>';
$displaylist = array();
$notused = array();
$hier = new hierarchy();
$displaylist = $hier->get_records_cobaltselect_menu('local_program', 'visible=1', null, '', 'id,fullname', '--Select--');
$displaylist = array();
$notused = array();
$hier = new hierarchy();

$schools = $hier->get_assignedschools();
if (is_siteadmin()) {
    $schools = $hier->get_school_items();
}

$school = $hier->get_school_parent($schools);
// print_object($school);
?>
<style>
    .categorypicker1{padding-bottom:11px;margin-left:35px;}
    .dataTables_length{float:none;padding-top:20px;}
    .pagination { margin: 10px 50px;}
</style>
<?php

if (count($schools) != 1) {
    echo '<div class="selfilterpos" >';
    /* Bug report #275  
     * @author hemalatha a arun<hemalatha@eabyas.in>
     * resolved - restrict to display only shared school
     */
    /*  $select = new single_select(new moodle_url('/local/modules/assigncourse.php?moduleid='.$moduleids.''), 'scid', $school, $scid, null, 'switchcategory');
      $select->set_label(get_string('schoolid','local_collegestructure').':');
      echo $OUTPUT->render($select); */
    echo '</div>';
    $displaylist = $hier->get_departments_forschool($modulelist->schoolid, false, true, true);
    echo '<div class="selfilterpos" style="float:none;">';
    $select = new single_select(new moodle_url('/local/modules/assigncourse.php?moduleid=' . $moduleids . '&scid=' . $scid . ''), 'id', $displaylist, $id, null, 'switchcategory');
    $select->set_label(get_string('dept', 'local_departments') . ':');
    $select->set_help_icon('dept_formatname', 'local_departments');
    echo $OUTPUT->render($select);
      echo '</div>';
    if(count( $displaylist)<=1)
       echo $hier-> cobalt_navigation_msg(get_string('navigation_info','local_collegestructure'),get_string('create_department','local_departments'),$CFG->wwwroot.'/local/departments/departments.php');    
  
} else {
    foreach ($schools as $scl) {
        $key = $scl->id;
        $value = $scl->fullname;
    }
    //$displaylist=$hier->get_departments_forschool($key);
    $displaylist = $hier->get_departments_forschool($modulelist->schoolid, false, true, true);
    echo '<div class="selfilterpos" style="float:none;">';
    $select = new single_select(new moodle_url('/local/modules/assigncourse.php?moduleid=' . $moduleids . '&scid=' . $key . ''), 'id', $displaylist, $id, null, 'switchcategory');
    $select->set_label(get_string('dept', 'local_departments') . ':');
    $select->set_help_icon('dept_formatname', 'local_departments');
    echo $OUTPUT->render($select);
    echo '</div>';
    if(count( $displaylist)<=1)
      echo $hier-> cobalt_navigation_msg(get_string('navigation_info','local_collegestructure'),get_string('create_department','local_departments'),$CFG->wwwroot.'/local/departments/departments.php');     
   
}
echo '<hr>';
// Print out all the modules.
$sql = "SELECT * FROM {local_cobaltcourses} where departmentid=$id  AND visible=1";
$courses = $DB->get_records_sql($sql);
if (!empty($id) && !empty($id)) {
    if (!$courses) {
        // There is no course to display.       
       echo $hier-> cobalt_navigation_msg(get_string('navigation_info','local_collegestructure'),get_string('createcourse','local_cobaltcourses'),$CFG->wwwroot.'/local/cobaltcourses/cobaltcourse.php');
    } else {
        // The conditions above have failed, we display a basic list of courses with paging/editing options.
        echo '<form id="movemodules" action="assigncourse.php?moduleid=' . $moduleids . '" method="post" "><div>';
        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
        echo '<table border="0" cellspacing="2" width="70%" cellpadding="4" class="generalbox boxaligncenter"><tr>';
        echo '<th class="header" scope="col">' . get_string('coursename', 'local_modules') . '</th>';
        echo '<th class="header" scope="col">' . get_string('coursetype', 'local_cobaltcourses') . '</th>';
        echo '<th class="header" scope="col" style="text-align:center;">' . get_string('select') . '</th>';
        echo '</tr>';
        $count = 0;
        $abletomovecourses = false;  // for now
        $baseurl = new moodle_url('/local/modules/assigncourse.php', $urlparams + array('sesskey' => sesskey()));
        foreach ($courses as $acourse) {
            $count++;
            echo '<tr>';
            $coursename = $acourse->fullname;
            if ($acourse->coursetype == 0) {
                $coursetype = get_string('general', 'local_cobaltcourses');
            } else {
                $coursetype = get_string('elective', 'local_cobaltcourses');
            }

            $checkexist = $DB->get_records('local_module_course', array('moduleid' => $moduleids, 'courseid' => $acourse->id));
            //condition to make course disable if it is already assigned to module
            if ($checkexist) {
                $startclass = 'class="dimmed"  disabled="disabled"';
                echo '<td align="left"><a ' . $startclass . ' href="../cobaltcourses/view.php?id=' . $acourse->id . '&plugin=modules&page=index&title=Modules"">' . format_string($coursename) . '</td>';
                echo '<td align="left">' . format_string($coursetype) . '</td>';
            } else {
                $startclass = 'class="courseassign"';
                echo '<td align="left"><a ' . $startclass . ' href="../cobaltcourses/view.php?id=' . $acourse->id . '&plugin=modules&page=index&title=Modules"">' . format_string($coursename) . '</a></td>';
                echo '<td align="left">' . format_string($coursetype) . '</td>';
            }
            echo '<td align="center">';
            $select_course = 'c' . $acourse->id . '';
            echo '<input  type="checkbox" name="' . $select_course . '" ' . $startclass . '   id="check_box"/>';
            echo '</td>';
            echo "</tr>";
        }
        $notused = array();

        echo '<tr><td colspan="3" align="right">';
        echo '<input type="hidden" name="moveto" value="' . $moduleids . '" />';
        echo '</td></tr>';
        $PAGE->requires->js('/local/modules/js/module.js');
        echo '<tr><td></td><td></td><td align="center"><input type="submit" name="submit" value="Assign Courses"   id="assign"/></td></tr>';
        echo '</table>';
        echo '</div></form>';
        echo '<br />';
    }
} else {
    echo $OUTPUT->box_start();
    echo $sel_dep_schl = get_string("sel_dep_schl", 'local_modules');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
