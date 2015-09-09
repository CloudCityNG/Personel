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
 * @subpackage Departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/departments/lib.php');

// $sid is schoolid used assign and unassign school to department
$sid = optional_param('sid', -1, PARAM_INT);
$slsid = optional_param('slsid', 0, PARAM_INT);
$dept_id = optional_param('deptid', -1, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$assign = optional_param('assign', 0, PARAM_BOOL);
$moveto = optional_param('moveto', 0, PARAM_INT);
$alpha = optional_param('apha', 0, PARAM_INT);

global $CFG, $DB;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/departments/assign_instructor.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('assign_instructor', 'local_departments'));

echo $OUTPUT->header();

//--------------manage dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));

$dept_ob = manage_dept::getInstance();
$hier1 = new hierarchy();

//adding tabs using  manage dept_tabs function
$currenttab = 'assign_instructor';
$dynamictab = 'assign_instructor';
$dept_ob->dept_tabs($currenttab, $dynamictab);

//  description of the  table -------------------- 
echo $OUTPUT->box(get_string('assigninsttabdes', 'local_departments'));


//echo $OUTPUT->box_end();
$returnurl = new moodle_url('/local/departments/display_instructor.php');
$currenturl = "{$CFG->wwwroot}/local/departments/display_instructor.php";

try {
//------after selecting insrtuctor and department(assigning instructor to department)---------
    $data = data_submitted();
    if (!empty($moveto) && !empty($data)) {
        if (!empty($data->check)) {
            $res = $dept_ob->assign_instructor_department($data);
            $dept_ob->success_error_msg($res, 'sassign_ins', 'eassign_ins', $currenturl);
        }
        $select_msg = get_string('select_instructor', 'local_departments');
        $hier1->set_confirmation($select_msg, $currenturl);
    }
//--------------------------------------------------------
// checking if login user is registrar or admin
    $schoolid = $dept_ob->check_loginuser_registrar_admin();

//-------$count used to avoid school filter(if registrar assigned to more than one school then filter will)
    $count = sizeof($schoolid);
    if ($count > 1) {
        $schoollist = $hier1->get_school_parent($schoolid, '', true);
        $select = new single_select(new moodle_url('/local/departments/assign_instructor.php'), 'slsid', $schoollist, $slsid, null, null);
        $select->set_label(get_string('schoolid', 'local_collegestructure'));
        echo $OUTPUT->render($select);
        echo '</br>';
        $temp_sid = $slsid;
    } else {

//----only displaying registrar enroll school informations------------  
        foreach ($schoolid as $sid) {
            $temp[] = $sid->id;
        }
        $school_id = implode(',', $temp);
        $slsid = -3;
        $temp_sid = $school_id;
    }
//-----------------------------------------------------------------------
    if ($slsid >= 1 or $slsid == -3) {
        echo get_string('assign_ins_heading', 'local_departments');
//----only displaying registrar enroll school informations------------  
        foreach ($schoolid as $sid) {
            $temp[] = $sid->id;
        }
        $school_id = implode(',', $temp);

        //------------- code used to arranging the instructor in aplphabetical order--------------  
//    for ($i=65; $i<=90; ++$i) {
//    	echo '<a href="assign_instructor.php?apha='.$i.'&slsid='.$slsid.'">'.chr($i).'</a>';
//	echo '  ';
//    }
//        echo '<a href="assign_instructor.php?apha=0&slsid='.$slsid.'">All</a>';
        if (!empty($alpha)) {
            $a = chr($alpha);
            $alphabets_order = "u.firstname LIKE  '$a%'";
        } else {
            $alphabets_order = 1;
        }
        //------end of alphabetical order----------------------------------------------------------  
        $PAGE->requires->js('/local/departments/js/assign_test.js');
        $PAGE->requires->js('/local/departments/js/disable_submit.js');


        //--------- fetching instructor only who assign to (current login)registrar assigned  schools--------------
        //feteching instrucotr roleid;
        $get_instructorroleid = $DB->get_record('role', array('shortname' => 'instructor'));
        $moveschoolid = 0;
        $sql1 = "SELECT u.*,per.schoolid From {$CFG->prefix}local_school_permissions AS per
                   INNER JOIN {$CFG->prefix}user AS u
		   ON u.id=per.userid
		   where per.roleid= $get_instructorroleid->id and  per.schoolid in ($temp_sid) and $alphabets_order and u.deleted=0";
        $instructor_list = $DB->get_records_sql($sql1);

        //--- code-used to display only unassigned instructor(new instructor)-------------    
        foreach ($instructor_list as $s => $s_value) {
            $assigned_ins = $DB->get_record('local_dept_instructor', array('instructorid' => $s_value->id, 'schoolid' => $s_value->schoolid));
            if (!empty($assigned_ins))
                unset($instructor_list[$s]);
        }
        //------------------------------------------------------------------------------------
        $data = array();
        if (empty($instructor_list)) {
            $e = get_string('no_records', 'local_request');
           // echo '</table></form></div>';
            $navigationmsg = get_string('nodata_assigninstructorpage', 'local_departments');
            $linkname = get_string('linkname_assigninstructorpage', 'local_departments');
            echo $hier1->cobalt_navigation_msg($navigationmsg, $linkname, $CFG->wwwroot . '/local/users/user.php');
        }
        echo '<form id="movemodules" action="assign_instructor.php" method="post"><div>';
        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
        echo '<table id="stu1" class="generaltable" cellspacing="2" width="90%" cellpadding="4"><thead>';
        echo '<th class="header" scope="col">' . get_string('firstname', 'local_departments') . '</th>';
        echo '<th class="header" scope="col">' . get_string('lastname', 'local_departments') . '</th>';
        echo '<th class="header" scope="col">' . get_string('email', 'local_departments') . '</th>';
        echo '<th class="header" scope="col">' . get_string('select') . '</th>';
        echo '</thead><tbody>';

        foreach ($instructor_list as $ins) {
            $moveschoolid = $ins->schoolid;
            if ($ins->suspended)
                $attr = 'style="color:#999 !important;"';
            else
                $attr = '';

            echo '<tr >';
            echo '<td ' . $attr . '>' . $ins->firstname . '</td>';
            echo '<td ' . $attr . '>' . $ins->lastname . '</td>';
            echo '<td ' . $attr . '>' . $ins->email . '</td>';
            if ($ins->suspended)
                echo '<td><input type="checkbox"  name="check[]" class="check"   ' . $attr . ' disabled=disabled value="' . $ins->id . '"  title="user is suspended" /></td>';
            else
                echo '<td><input type="checkbox"  name="check[]" class="check"  value="' . $ins->id . '"  title="active user" /></td>';
            echo'</tr>';
        }
        echo '</tbody>';
        echo '</table>';
        $out = array();
        $out[0] = get_string('dept_assignto', 'local_departments');

        $deptnewlist = $DB->get_records('local_department', array('schoolid' => $temp_sid, 'visible' => 1));

        $k = 0;
        foreach ($deptnewlist as $dnl) {
            $de_list[] = $dnl->id;
            $k++;
        }
        $assignedschool_deptlist = $DB->get_records('local_assignedschool_dept', array('assigned_schoolid' => $temp_sid));
        foreach ($assignedschool_deptlist as $asschool_list) {
            $de_list[$k] = $asschool_list->deptid;
            $k++;
        }
        $udlist = array_unique($de_list);

        //$deptlist =$DB->get_records_sql("select dept.* from {$CFG->prefix}local_department as dept 
        //                                 LEFT JOIN {$CFG->prefix}local_assignedschool_dept as assigned
        //                                 ON dept.schoolid=assigned.assigned_schoolid
        //                                 where dept.schoolid=$temp_sid group by dept.id" );
        foreach ($udlist as $dept) {
            $dep = $DB->get_record('local_department', array('id' => $dept, 'visible' => 1));
            $out[$dep->id] = format_string($dep->fullname);
        }

        echo html_writer::select($out, 'moveto', $out, null, array('id' => 'movetoid', 'style' => 'margin-top:20px;'), '1');
        //$PAGE->requires->yui_module('moodle-core-formautosubmit',
        //    'M.core.init_formautosubmit',
        //    array(array('selectid' => 'movetoid'))
        //);

        echo '<input type="hidden" name="ins_schoolid" value="' . $moveschoolid . '" />';
        echo '<input  style="margin-top:20px;"   type="submit" id="movetoid" class="assign_deptbutton"  value="' . get_string('dept_assigntodept', 'local_departments') . '" />';
        echo '</div></form>';
    }
} //--------- end of try block---------------------------------------------
catch (Exception $e) {
    //echo $e->getMessage();
}

echo $OUTPUT->footer();
?>


