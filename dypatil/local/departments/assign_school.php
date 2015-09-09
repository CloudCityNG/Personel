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
$dept_id = optional_param('deptid', -1, PARAM_INT);
$id = optional_param('id', -1, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$assign = optional_param('assign', 0, PARAM_BOOL);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$page = optional_param('page', 0, PARAM_INT);
$perpage = 5;
$spage = $page * $perpage;
global $CFG, $DB;
$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();
$PAGE->set_url('/local/departments/assign_school.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$returnurl = new moodle_url('/local/departments/assign_school.php');
$currenturl = "{$CFG->wwwroot}/local/departments/assign_school.php?page=$page&perpage=$perpage";
$dept_ob = manage_dept::getInstance();
$hier1 = new hierarchy();

//---code starts- to unassign school from department----
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {

        $defaultschoolid = $DB->get_record('local_department', array('id' => $dept_id));
        $flag = 0;
        if ($defaultschoolid->schoolid == $sid)
            $flag = 1;
        $used_dept = $DB->get_records('local_cobaltcourses', array('schoolid' => $sid, 'departmentid' => $dept_id));
        if (empty($used_dept) && ($flag == 0)) {
            $DB->delete_records('local_assignedschool_dept', array('id' => $id));
            $dept_name = $DB->get_record('local_department', array('id' => $dept_id));
            $school_name = $DB->get_record('local_school', array('id' => $sid));
            $msg_strings = new stdClass();
            $msg_strings->school = $school_name->fullname;
            $msg_strings->department = $dept_name->fullname;
            $unassign_dept_msg = get_string('unassign_dept_msg', 'local_departments', $msg_strings);
            $options = array('style' => 'notifysuccess');
            $hier1->set_confirmation($unassign_dept_msg, $currenturl, $options);
        } else {
            $options = array('style' => 'notifyproblem');
            $error_unassign_msg = get_string('error_unassign_msg', 'local_departments');
            $hier1->set_confirmation($error_unassign_msg, $currenturl, $options);
        }
    }
}
if ($sid and $unassign and confirm_sesskey()) {
    $strheading = get_string('unassign_school', 'local_departments');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    $currenttab = 'assign_school';
    //$dept_ob->dept_tabs($currenttab);
    echo $OUTPUT->heading($strheading);
    $defaultschoolid = $DB->get_record('local_department', array('id' => $dept_id));
    $flag = 0;
    if ($defaultschoolid->schoolid == $sid)
        $flag = 1;
    $used_dept = $DB->get_records('local_cobaltcourses', array('schoolid' => $sid, 'departmentid' => $dept_id));
    if (!empty($used_dept) || ($flag == 1)) {
        $temp = new stdClass();
        $deptname = $DB->get_record('local_department', array('id' => $dept_id));
        $temp->dept = $deptname->fullname;
        $schoolname = $DB->get_record('local_school', array('id' => $sid));
        $temp->school = $schoolname->fullname;
        echo $confirm_msg = get_string('school_already_inuse_withdept', 'local_departments', $temp);
        echo $OUTPUT->continue_button(new moodle_url('/local/departments/assign_school.php'));
    } else {
        $yesurl = new moodle_url('/local/departments/assign_school.php', array('id' => $id, 'sid' => $sid, 'deptid' => $dept_id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage));
        $message = get_string('delconfirm_unassignschool', 'local_departments');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }

    echo $OUTPUT->footer();
    die;
}
//---code end- to unassign school from department-----

$PAGE->navbar->add(get_string('assign_school', 'local_collegestructure'));
echo $OUTPUT->header();

//-------------------manage dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));

//adding tabs using prefix_tabs function
$currenttab = 'assign_school';

$dept_ob->dept_tabs($currenttab);

//---code starts- to assign school to department----
if ($sid and $assign and confirm_sesskey()) {
    $exists_deptlist = $DB->get_record('local_assignedschool_dept', array('deptid' => $dept_id, 'assigned_schoolid' => $sid));

    // exit;
    $exists_msg = get_string('exists', 'local_departments');
    if (!empty($exists_deptlist))
        $hier1->set_confirmation($exists_msg, $currenturl);
    else {

        $assign_deptlist = new stdClass();
        $assign_deptlist->deptid = $dept_id;
        $assign_deptlist->assigned_schoolid = $sid;
        $assign_deptlist->timecreated = time();
        $assign_deptlist->timemodified = time();
        $assign_deptlist->usermodified = $USER->id;
        $assign_s = $DB->insert_record('local_assignedschool_dept', $assign_deptlist);
        $dept_name = $DB->get_record('local_department', array('id' => $dept_id));
        $school_name = $DB->get_record('local_school', array('id' => $sid));
        $msg_strings = new stdClass();
        $msg_strings->school = $school_name->fullname;
        $msg_strings->department = $dept_name->fullname;
        $dept_ob->success_error_msg($assign_s, 'suuccessfully_assigneds', 'error_assigneds', $currenturl, $msg_strings);
    }
}//---code end- to assign school to department-----
//  description of the  table -------------------- 
//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
echo $OUTPUT->box(get_string('assign_school_description', 'local_departments'));
//echo $OUTPUT->box_end();
//$PAGE->requires->js('/local/departments/js/assign_sch.js');
try {
// checking if login user is registrar or admin
    $schoolid = $dept_ob->check_loginuser_registrar_admin();

//----only displaying registrar enroll school informations------------  
    foreach ($schoolid as $sid) {
        $temp[] = $sid->id;
    }

    $school_id = implode(',', $temp);
    //-------fetching department and its default schools-----
    //-------used when logged user is siteadmin-----
    if (is_siteadmin($USER->id)) {
        $count_sql = "SELECT * From {$CFG->prefix}local_department where schoolid in ($school_id) ";
        $get_count = $DB->get_records_sql($count_sql);
        $totalcount = sizeof($get_count);
        $sql1 = "SELECT * From {$CFG->prefix}local_department where schoolid in ($school_id) LIMIT $spage,$perpage";
        $unique_dept = $DB->get_records_sql($sql1);
        foreach ($unique_dept as $udept) {
            // print_object($udept);
            $final_array[] = $udept;
            $sql = ("select * from {$CFG->prefix}local_assignedschool_dept where deptid=$udept->id  and assigned_schoolid in ($school_id) ");
            $assigned_school = $DB->get_records_sql($sql);
            foreach ($assigned_school as $udept) {
                $final_array[] = $udept;
            }
        }
    } else {
        //------used when logged in user is registrar ...other than admin
        $defaultdept = $DB->get_records_sql("SELECT * From {$CFG->prefix}local_department where schoolid in ($school_id)");
        $assignedschool_dept = $DB->get_records_sql("SELECT dept.* from {$CFG->prefix}local_department as dept
            JOIN  {$CFG->prefix}local_assignedschool_dept as adept on adept.deptid=dept.id  where adept.assigned_schoolid in ($school_id) ");
        $unique_dept = $defaultdept + $assignedschool_dept;
        $totalcount = sizeof($unique_dept);
        // used for pagination
        $unique_dept = array_values($unique_dept);        
        $startindex = $spage;       
        $endindex = $startindex + $perpage;
        for ($i = $startindex; $i < $endindex; $i++) {
            if (!empty($unique_dept[$i])) {            
                $keyrecord = $unique_dept[$i];                
                $final_array[] = $keyrecord;
                $sql = ("select * from {$CFG->prefix}local_assignedschool_dept where deptid=$keyrecord->id  and assigned_schoolid in ($school_id) ");

                $assigned_school = $DB->get_records_sql($sql);            
                foreach ($assigned_school as $udept) {
                    $final_array[] = $udept;
                }
            }
        }

    }

    //echo "<div id='filter-box' ><a id='button' href='javascript:void(0);'>Filters</a>";
    //echo '<div class="filterarea"></div></div>';
    //
    $table = new html_table();
    $table->id = "assign_s";
    $table->head = array(
        get_string('deptfullname', 'local_departments'),
        get_string('schoolid', 'local_collegestructure'),
        get_string('dept_unassign', 'local_departments'),
        get_string('dept_assignto', 'local_departments'),
    );
    if (empty($final_array)) {
        $s = get_string('no_records', 'local_request');
        throw new Exception($s);
    }


    foreach ($final_array as $fa) {
        $cells = array();
        $sql = ("select * from {$CFG->prefix}local_assignedschool_dept where deptid=$fa->id and assigned_schoolid in ($school_id) ");
        $assigned_school = $DB->get_records_sql($sql);
        $count = sizeof($assigned_school);
        $count = ($count >= 1 ? $count + 1 : 1);
        if (isset($fa->fullname)) {
            $cells[0] = new html_table_cell();
            $cells[0]->text = $fa->fullname;
            $cells[0]->rowspan = $count;
            $cells[0]->style = 'vertical-align:middle;';
            $departmentid = $fa->id;
        }
        $cells[1] = new html_table_cell();
        $cells[2] = new html_table_cell();
        if (!empty($fa->schoolid))
            $sid = $fa->schoolid;
        else
            $sid = $fa->assigned_schoolid;
        $schoolname = $DB->get_record('local_school', array('id' => $sid));
        $cells[1]->text = $schoolname->fullname;
        //----used fetech id of (assigned_school_dept)
        $assigned_id = isset($fa->deptid) ? $fa->id : 0;
        if (empty($assigned_id))
            $cells[2]->text = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('dept_unassign', 'local_departments'), 'title' => get_string('unassign_msg', 'local_departments'), 'class' => 'iconsmall'));
        else
            $cells[2]->text = html_writer::link(new moodle_url('/local/departments/assign_school.php', array('id' => $assigned_id, 'sid' => $schoolname->id, 'deptid' => $departmentid, 'unassign' => 1, 'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_departments'), 'title' => get_string('dept_unassign', 'local_departments'), 'class' => 'iconsmall')));

        if (isset($fa->fullname)) {
            $departmentid = $fa->id;
            //-- used to remove default school from single select drop down  list--    
            $tempsidlist = $dept_ob->remove_defaultschool($schoolid, $departmentid);

            $schoollist = $hier1->get_school_parent($tempsidlist);
            $select = new single_select(new moodle_url('/local/departments/assign_school.php', array('deptid' => $departmentid, 'assign' => 1, 'sesskey' => sesskey(), 'page' => $page, 'perpage' => $perpage)), 'sid', $schoollist, 'assign', null, null);
            // $OUTPUT->render($select);
            $cells[3] = new html_table_cell();
            $cells[3]->text = $OUTPUT->render($select);
            $cells[3]->rowspan = $count;
            $cells[3]->style = 'vertical-align:middle;';
        }
        $row = new html_table_row($cells);
        $table->data[] = $row;
    }
    $table->size = array('15%', '15%', '15%', '15%');
    $table->align = array('left', 'left', 'left', 'center');
    $table->width = '99%';
    echo html_writer::table($table);

    $baseurl = new moodle_url($CFG->wwwroot . '/local/departments/assign_school.php', array('perpage' => $perpage, 'page' => $page));
    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $baseurl);
} //--------- end of try block---------------------------------------------
catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




