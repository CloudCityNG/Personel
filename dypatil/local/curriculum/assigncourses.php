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
 *
 * @package    Collegestucture
 * @subpackage curriculumplan
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/curriculum/lib.php');
require_once($CFG->dirroot . '/local/curriculum/curriculum_form.php');

$id = optional_param('id', 0, PARAM_INT); //plan id
$mode = required_param('mode', PARAM_RAW); //to activate current tab
$cid = optional_param('cid', 0, PARAM_INT); //curriculum id
$moveto = optional_param('moveto', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$flag = optional_param('flag', '', PARAM_RAW);
$sesskey = optional_param('sesskey', '', PARAM_RAW);
$programid = optional_param('pid', 0, PARAM_INT);
$departmentid = optional_param('did', 0, PARAM_INT);
$moduleid = optional_param('moduleid', 0, PARAM_INT); // which page to show
$page = optional_param('page', 0, PARAM_INT);
$perpage = 10;

global $DB, $CFG;

require_login();
$systemcontext = context_system::instance();

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/curriculum/assigncourses.php');
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . get_string('assigncourses', 'local_curriculum'));
//Header and the navigation bar
$PAGE->set_heading(get_string('curriculumplan', 'local_curriculum'));


$PAGE->set_pagelayout('admin');
$cplan = curriculumplan::getInstance();
$conf = new object();

$returnurl = new moodle_url('/local/curriculum/assigncourses.php', array('id' => $id, 'mode' => $mode, 'cid' => $cid));
$hierarchy = new hierarchy();

//Adding the courses to the Curriculumplan 
if (!empty($moveto) and $data = data_submitted()) {
    $crs = array();
    foreach ($data as $key => $value) {
        if (preg_match('/^c\d+$/', $key)) {
            $cuid = substr($key, 1);
            array_push($crs, $cuid);
        }
    }
    $currenturl = new moodle_url('/local/curriculum/assigncourses.php', array('id' => $id, 'mode' => $mode, 'cid' => $cid));
    if ($flag) {
        //"Assign Module" is set, assign all the courses from the module to the Curriculum 
        $conf->module = $DB->get_field('local_module', 'fullname', array('id' => $moduleid));
        $cplan->assign_modules($id, $data->moveto, $moduleid);
        $hierarchy->set_confirmation(get_string('moduleassignedsuccess', 'local_curriculum', $conf), $currenturl, array('style' => 'notifysuccess'));
    }
    //If no courses are selected, show a message.
    if (empty($crs)) {
        $params = array('id' => $id, 'mode' => $mode, 'cid' => $cid, 'type' => $type);
        if ($moduleid) {
            $params['pid'] = $programid;
            $params['moduleid'] = $moduleid;
        }
        if ($departmentid) {
            $params['did'] = $departmentid;
        }
        $currenturl = new moodle_url('/local/curriculum/assigncourses.php', $params);
        $hierarchy->set_confirmation(get_string('pleaseselectcourse', 'local_curriculum'), $currenturl, array('style' => 'notifyproblem'));
    }
    if ($id)
        $to = $DB->get_field('local_curriculum_plan', 'fullname', array('id' => $id));
    else
        $to = $DB->get_field('local_curriculum', 'fullname', array('id' => $cid));
    $graduatesql = "SELECT * FROM {local_graduation} WHERE curriculumid={$cid}";
    $graduateduser = $DB->get_records_sql($graduatesql);
    if (empty($graduateduser)) {


        $cplan->assign_courses($id, $crs, $data->moveto, $moduleid, $departmentid);
        $hierarchy->set_confirmation(get_string('assignedsuccess', 'local_curriculum', $to), $currenturl, array('style' => 'notifysuccess'));
    } else {
        $currenturl = new moodle_url('/local/curriculum/index.php');
        $hierarchy->set_confirmation(get_string('error_graduated', 'local_curriculum'), $currenturl, array('style' => 'notifyproblem'));
    }
    //Assign courses to the Curriculum/plan.
}
// end of the courses assigning to curriculumplan
//Unassign courses from the plan
if ($unassign) {
    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        $conf->course = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $courseid));
        //unassign cobalt course from the Curriculum plan.
        $cplan->unassign_course($id, $cid, $courseid);
        $message = get_string('unassignsuccess', 'local_curriculum', $conf);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
    $PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $cid)));
    if ($id)
        $PAGE->navbar->add(get_string('manageplan', 'local_curriculum'), new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1)));
    $strheading = get_string('unassigncourse', 'local_curriculum');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    //display confirmation message to unassign.
    $yesurl = new moodle_url('/local/curriculum/assigncourses.php', array('id' => $id, 'mode' => $mode, 'cid' => $cid, 'unassign' => 1, 'courseid' => $courseid, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('confirmunassign', 'local_curriculum');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

//page breadcrumb and header
$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $cid)));
if ($id)
    $PAGE->navbar->add(get_string('manageplan', 'local_curriculum'), new moodle_url('/local/curriculum/viewcurriculum.php', array('id' => $cid, 'assign' => 1)));
$PAGE->navbar->add(get_string('assigncourses', 'local_curriculum'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
//try{
$schoollist = $hierarchy->get_assignedschools();
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$count = count($schoollist); //Count of schools to which registrar is assigned
if ($count < 1) {
    throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
}
//Tab view
$curriculum = new curricula();
$curriculum->print_curriculumtabs($mode);

if ($id)
    $plan = $DB->get_record('local_curriculum_plan', array('id' => $id));
$cur = $DB->get_record('local_curriculum', array('id' => $cid));
if ($id)
    $name = $cplan->name($plan);
else
    $name = $cplan->name($cur);

echo ($id) ? '<h4>' . $cur->shortname . ': ' . $plan->fullname . '</h4>' : '<h4>' . $cur->fullname . '</h4>';
echo $OUTPUT->box(get_string('viewassigncoursetoplanpage', 'local_curriculum'));

//display the details of plan.
$out = '<br/><table border="0" style="width:100%;"><tr><td style="width:50%;"><b>' . get_string('schoolid', 'local_collegestructure') . ' : </b>' . $name->school . '</td><td align="right" style="width:50%;"><b>' . get_string('program', 'local_programs') . ' : </b>' . $name->program . '</td></tr></table>';

if (isset($plan) && $plan->parentid) {
    //if has the Parent plan, display the name.
    $parentplan = $DB->get_field('local_curriculum_plan', 'fullname', array('id' => $plan->parentid));
    $out .= '<b>' . get_string('parentplan', 'local_curriculum') . ': </b>' . $parentplan;
}
$out .= '<br/>';
echo $out;

$spage = $page * $perpage;
if ($id) {
    $conf->plan = $plan->fullname;
    $child = $cplan->get_dependency_list($id);
    if ($child == 2) {
        //If plan has any child plans, Don't allow to assign Courses.
        $message = get_string('haschildcantassigncourse', 'local_curriculum', $conf);
        echo $message;
    } else {
        
    }
}
if ($id) {
    $conf->plan = $plan->fullname;
    $child = $cplan->get_dependency_list($id);
    if ($child == 2) {
        //If plan has any child plans, Don't allow to assign Courses.
        $message = get_string('haschildcantassigncourse', 'local_curriculum', $conf);
        echo $message;
    } else {
        $courses = $DB->get_records('local_curriculum_plancourses', array('planid' => $plan->id, 'curriculumid' => $plan->curriculumid), '', '*', $spage, $perpage);
        $totalcount = $DB->count_records('local_curriculum_plancourses', array('planid' => $plan->id, 'curriculumid' => $plan->curriculumid));
    }
} else if ($DB->get_field('local_curriculum', 'enableplan', array('id' => $cid))) {
    $message = get_string('planenabledcurriculum', 'local_curriculum', $cur->fullname);
    $message .= $line[] = ' <a ' . $linkcss . ' title="Add Curriculum Plan" href="' . $CFG->wwwroot . '/local/curriculum/viewcurriculum.php?id=' . $cid . '&assign=1&sesskey=' . sesskey() . '">' . get_string('manageplan', 'local_curriculum') . '</a>';
    echo $message;
} else {
    $courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $cid), '', '*', $spage, $perpage);
    $totalcount = $DB->count_records('local_curriculum_plancourses', array('curriculumid' => $plan->curriculumid));
}

//$DB->get_records($table, array $conditions=null, $sort='', $fields='*', $limitfrom=0, $limitnum=0) 
//$courses = $DB->get_records('local_curriculum_plancourses', array('planid'=>$plan->id, 'curriculumid'=>$plan->curriculumid), '', '*', $spage, $perpage);
//$totalcount = $DB->count_records('local_curriculum_plancourses', array('planid'=>$plan->id, 'curriculumid'=>$plan->curriculumid));
if (isset($courses) && isset($totalcount)) {
    $options = array('id' => $id, 'mode' => $mode, 'cid' => $cid, 'type' => $type);
    $baseurl = new moodle_url($CFG->wwwroot . '/local/curriculum/assigncourses.php', $options + array('perpage' => $perpage, 'page' => $page,));
    $data = array();
    $head = array();
    if (empty($courses)) {
        //No courses assigned to the plan, show the message.
        $data[] = array(get_string('nocoursesassigned', 'local_curriculum'));
    } else {
        //Display the list of courses assigned to the plan, with unassign option.
        foreach ($courses as $course) {
            $line = array();
            $cname = $DB->get_record('local_cobaltcourses', array('id' => $course->courseid));
            $line[] = html_writer::tag('a', $cname->shortname, array('href' => '' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->courseid . '')) . ': ' . $cname->fullname;
            $line[] = html_writer::link(new moodle_url('/local/curriculum/assigncourses.php', array('id' => $plan->id, 'mode' => $mode, 'cid' => $cid, 'courseid' => $course->courseid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_curriculum'), 'title' => get_string('unassign', 'local_curriculum'), 'class' => 'iconsmall')));
            $data[] = $line;
        }
        $head[] = get_string('cobaltcourse', 'local_curriculum');
        $head[] = get_string('unassign', 'local_curriculum');
    }
    $table = new html_table();
    $table->head = $head;
    $table->size = array('60%', '40%');
    $table->align = array('left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    echo html_writer::table($table);
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
    echo '<br/>';
//Description to assign courses.
    $descripion = $id ? get_string('descforassign', 'local_curriculum', $plan) : get_string('descforassign', 'local_curriculum', $cur);
    echo $OUTPUT->box($descripion);

    $tool = new stdClass();
    $tool->type = $_GET['type'];
    $tool->pid = $programid;
    $tool->did = $departmentid;
    $tool->moduleid = $moduleid;
    $PAGE->requires->js('/local/curriculum/js/filter.js');
//List of Modules.
    $school = $hierarchy->get_school_parent($schoollist, array(), false, false);
    $school = array_keys($school);
    $checked1 = '';
    $display1 = 'display:none;';
    $checked2 = '';
    $display2 = 'display:none;';
    if ($departmentid) {
        $checked2 = 'checked="checked"';
        $display2 = 'display:block;';
    } else if ($programid || $moduleid) {
        $checked1 = 'checked="checked"';
        $display1 = 'display:block;';
    }

    echo '<br/>';
    echo '<form id="frm1" action="assigncourses.php" method="get">';
    echo '<div id="r" style="margin-bottom: 10px;">';
    echo '' . get_string('choose') . ': <input type="radio" id="program" name="type" value="1" ' . $checked1 . ' />' . get_string('programs', 'local_programs') . '&nbsp;' . '&' . '&nbsp;' . get_string('pluginname', 'local_modules') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '<input type="radio" id="department" name="type" value="2" ' . $checked2 . ' />' . get_string('dept', 'local_departments') . '&nbsp;&nbsp;&nbsp;&nbsp;';
    echo '</div>';
    echo '</form>';

    // Edited by hema
    /* Bug report #274  
     * @author hemalatha c arun <hemalatha@eabyas.in>
     * resolved - restricted list only programs of perticular school(instead of displaying all the course)
     */
    //$p = $cplan->get_assigned_programlist($plan->schoolid);
    if(!$plan){
        list($usql, $params) = $DB->get_in_or_equal($cur->schoolid);
        $d = $cplan->get_assigned_deptlist($cur->schoolid, true);
    } else {
        list($usql, $params) = $DB->get_in_or_equal($plan->schoolid);
        $d = $cplan->get_assigned_deptlist($plan->schoolid, true);
    }
    $p = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid $usql AND visible = 1", $params, '', 'id,fullname', 'Select Program',0,0,0);
    $modules = $hierarchy->get_records_cobaltselect_menu('local_module', "programid=$programid AND visible=1", null, '', 'id,fullname', 'Select Module');   
    

    echo '<div id="p" style="margin-bottom: 10px;' . $display1 . '">';
//if($assigntype==1)
    echo '<div style="margin-bottom: 10px;">';
    $select1 = new single_select(new moodle_url('/local/curriculum/assigncourses.php?id=' . $plan->id . '&mode=' . $mode . '&cid=' . $cid . ''), 'pid', $p, $programid, null, 'switchcategory');
    $select1->set_label(get_string('selectprogram', 'local_programs') . ':&nbsp&nbsp');
    echo $OUTPUT->render($select1);
    echo '</div>';      
    
    if ($programid) {
        echo '<div style="margin-bottom: 10px;">';
        $modules = $hierarchy->get_records_cobaltselect_menu('local_module', "programid=$programid AND visible=1", null, '', 'id,fullname', 'Select Module',0,0,0);
        $select2 = new single_select(new moodle_url('/local/curriculum/assigncourses.php?id=' . $plan->id . '&mode=' . $mode . '&cid=' . $cid . '&pid=' . $programid . ''), 'moduleid', $modules, $moduleid, null, 'switchcategory');
        $select2->set_label(get_string('selectmodule', 'local_modules') . ':&nbsp&nbsp&nbsp&nbsp');
        echo $OUTPUT->render($select2);
        echo '</div>';     
        if(count($modules)<=1)
        echo $hierarchy->cobalt_navigation_msg(get_string('navigation_info','local_collegestructure'),get_string('createmodule', 'local_modules'),$CFG->wwwroot.'/local/modules/module.php');   
        
    }
    echo '</div>';
    echo '<div id="d" style="margin-bottom: 10px;' . $display2 . '">';
    $select3 = new single_select(new moodle_url('/local/curriculum/assigncourses.php?id=' . $plan->id . '&mode=' . $mode . '&cid=' . $cid . ''), 'did', $d, $departmentid, null, 'switchcategory');
    $select3->set_label(get_string('selectdepartment', 'local_cobaltcourses') . ':&nbsp&nbsp&nbsp&nbsp');
    echo $OUTPUT->render($select3);
 
    // navigation handling
    if(count($d)<=1){        
    echo $hierarchy->cobalt_navigation_msg(get_string('navigation_info','local_collegestructure'),get_string('create_department', 'local_departments'),$CFG->wwwroot.'/local/departments/departments.php');   
    } 
    echo '</div>';
    $checkcount = $cplan->course_count($id, $cid, $moduleid, $departmentid);
    $disable = ($checkcount) ? 'disabled="disabled"' : '';

//Display the list of courses in the module to assign.
    if ($moduleid || $departmentid) {
        //If all the courses are assigned to the plan, Disable the button.

        if ($moduleid)
            echo $OUTPUT->box(get_string('noteforassignmodule', 'local_curriculum'));
        else
            echo $OUTPUT->box(get_string('noteforassigndept', 'local_curriculum'));
        
        
        if ($departmentid) {
            $ccourses = $DB->get_records('local_cobaltcourses', array('departmentid' => $departmentid, 'visible' => 1));
            $cobaltcourses = array();
            foreach ($ccourses as $ccourse) {
                $cobaltcourses[$ccourse->id] = format_string($ccourse->fullname);
            }
        } else {
            $cobaltcourses = $hierarchy->get_courses_module($moduleid, $none = true);
        }
        if (empty($cobaltcourses)) {
            //Display message if no courses are available in the selected module.
            if ($moduleid)
                $record = $DB->get_field('local_module', 'fullname', array('id' => $moduleid));          
            if ($departmentid)
                $record = $DB->get_field('local_department', 'fullname', array('id' => $departmentid));           
              
            $linkurl=$CFG->wwwroot.'/local/modules/assigncourse.php?moduleid='.$moduleid;
            $linkname=get_string('assigncoursetomodule', 'local_curriculum');
            echo $OUTPUT->box($hierarchy->cobalt_navigation_msg(get_string('nocoursesmodule', 'local_curriculum', $record)." Click here to ",$linkname,$linkurl));
        }else {

            $table = new html_table();
            $heading = '<a id="selectall" style="cursor:pointer;">' . get_string('selectall') . '</a>';
            $table->head = array(get_string('cobaltcourse', 'local_curriculum'), get_string('select'));
            $table->size = array('60%', '40%');
            $table->align = array('left', 'center');
            $table->width = '99%';
            $data = array();
            echo '<form id="movemodules" action="assigncourses.php?id=' . $plan->id . '&pid=' . $programid . '&moduleid=' . $moduleid . '&did=' . $departmentid . '&type=' . $type . '&mode=' . $mode . '&cid=' . $cid . '" method="post">';
            foreach ($cobaltcourses as $key => $cobaltcourse) {
                $line = array();
                $check = $DB->get_record('local_curriculum_plancourses', array('curriculumid' => $cid, 'courseid' => $key));
                if (!empty($check))
                    $startclass = 'class="dimmed"  disabled="disabled"';
                else
                    $startclass = 'class="courseassign"';
                $line[] = '<a ' . $startclass . ' target="_blank" href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $key . '">' . $cobaltcourse . '</a>';
                $line[] = '<input type="checkbox" name="c' . $key . '" ' . $startclass . ' />';
                $data[] = $line;
            }
            $value = $id ? $id : $cid;
            $submitbutton = '<input type="hidden" name="moveto" value = "' . $value . '" />';
            //$submitbutton .= '<input type="checkbox" name="mycheck" class="checkall" value="Check All" />';
            $submitbutton .= '<input type="submit" id="movetoid" class = "click autosubmit" value="Assign Courses" ' . $disable . ' />';
            if ($moduleid)
                $submitbutton .= '<input type="submit" id="movetoid" name="flag" class = "click autosubmit" value="Assign Module" ' . $disable . ' />';
            $data[] = array('', $submitbutton);
            $table->data = $data;
            echo html_writer::table($table);
            echo '</form>';
        }
    }
}
echo $OUTPUT->footer();
