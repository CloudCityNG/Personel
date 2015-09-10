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
 * @subpackage  assigning mentor to student
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot . '/local/lib.php');
$sid = optional_param('sid', 0, PARAM_INT);
$programid = optional_param('pid', 0, PARAM_INT);
$assigneerole_id = optional_param('aid', 0, PARAM_INT);
$mentorid = optional_param('mentorid', 0, PARAM_INT);
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/assignmentor/assign_mentor.php');
$PAGE->set_heading($SITE->fullname);
//if (!has_capability('local/assignmentor:manage', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->navbar->add(get_string('pluginname', 'local_assignmentor'), new moodle_url('/local/assignmentor/assign_mentor.php'));
$PAGE->navbar->add(get_string('assign', 'local_assignmentor'));
$currenturl = "{$CFG->wwwroot}/local/assignmentor/index.php";
echo $OUTPUT->header();
/* ----heading---- */
echo $OUTPUT->heading(get_string('pluginname', 'local_assignmentor'));
$hier = new hierarchy();
/* ---adding tabs using prefix_tabs function--- */
$currenttab = 'assignmentor';
$assignee_ob = assign_mentortostudent::getInstance();
$assignee_ob->assignmentor_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('desc_assign_mentor', 'local_assignmentor'));
}
/* ---description of the  table --- */
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
echo $OUTPUT->box_end();
/* ---after selecting students and mentor(assigning mentor to department)---- */
$data = data_submitted();
if (!empty($mentorid) && !empty($data)) {
    if (!empty($data->check)) {
        $count = sizeof($data->check);
        $res = $assignee_ob->assign_mentorparent_tostudent($data);
        $lang_name = $DB->get_record('user', array('id' => $sid));
        $lang_mentorname = $DB->get_record('user', array('id' => $mentorid));
        $temp_lang = new stdClass();
        $temp_lang->mentor = $lang_mentorname->firstname . ' ' . $lang_mentorname->lastname;
        $temp_lang->student = $lang_name->firstname . ' ' . $lang_name->lastname;
        if ($res) {
            if ($count > 1)
                $conform_msg = get_string('sassign_mentor_plural', 'local_assignmentor', $temp_lang);
            else
                $conform_msg = get_string('sassign_mentor_singular', 'local_assignmentor', $temp_lang);
            $options = array('style' => 'notifysuccess');
        }
        else {
            $conform_msg = get_string('eassign_mentor', 'local_assignmentor', $temp_lang);
            $options = array('style' => 'notifyproblem');
        }
        $hier->set_confirmation($conform_msg, $currenturl, $options);
    }
}

try {
    /* ----school filter----- */
    $schoolids = $assignee_ob->check_loginuser_registrar_admin();
    $schoollist = $hier->get_school_parent($schoolids, '', true);
    $school_select = new single_select(new moodle_url('/local/assignmentor/assign_mentor.php'), 'sid', $schoollist, $sid, null, null);
    $school_select->set_label(get_string('select', 'local_collegestructure'));
    echo '<div>' . $OUTPUT->render($school_select) . '</div>';
    $programlist = $hier->get_records_cobaltselect_menu('local_program', "schoolid=$sid AND visible=1", null, '', 'id,fullname', '--Select--');
    $prg_select = new single_select(new moodle_url('/local/assignmentor/assign_mentor.php', array('sid' => $sid)), 'pid', $programlist, $programid, null);
    $prg_select->set_label(get_string('selectprogram', 'local_programs'));

    echo '<div style="margin-top:10px;">' . $OUTPUT->render($prg_select) . '</div>';
    echo '<br/><br/>';
    $currentyear = date("Y", time());
    /* ----fetching students of perticular program and students---- */
    $sql1 = "SELECT u.id,u.firstname,u.lastname,u.email ,from_unixtime(ud.timecreated,'%Y') as year From {$CFG->prefix}local_userdata AS ud
                   INNER JOIN {$CFG->prefix}user AS u
		   ON u.id=ud.userid
		   where ud.schoolid=$sid and  ud.programid=$programid and from_unixtime(ud.timecreated,'%Y')=$currentyear";
    $student_list = $DB->get_records_sql($sql1);
    /* ---used to remove already assigned student id 's--- */
    foreach ($student_list as $stu => $stu_value) {
        $exists = $DB->get_record('local_assignmentor_tostudent', array('studentid' => $stu_value->id, 'schoolid' => $sid, 'programid' => $programid));
        if ($exists)
            unset($student_list[$stu]);
    }

    if (!empty($sid)and ! empty($programid)) {
        if (empty($student_list)) {
            $e = 'No Records Found';
            throw new Exception($e);
        }
        $PAGE->requires->js('/local/assignmentor/js/assign_stu_pagitest.js');
        $PAGE->requires->js('/local/assignmentor/js/disable_submit.js');
        echo '<form id="assign_mentor" action="assign_mentor.php" method="post"><div>';
        echo '<input type="hidden" name="sesskey" value="' . sesskey() . '" />';
        echo '<table id="stu1" class="generaltable" cellspacing="2" width="90%" cellpadding="2"><thead>';
        echo '<th class="header" scope="col">' . get_string('select') . '</th>';
        echo '<th class="header" scope="col">' . get_string('serviceid', 'local_assignmentor') . '</th>';
        echo '<th class="header" scope="col">' . get_string('studentname', 'local_assignmentor') . '</th>';
        echo '<th class="header" scope="col">' . get_string('joiningdate', 'local_assignmentor') . '</th>';
        echo '</thead><tbody>';

        /* ---code used to remain as checked while refereshing--- */
        $data = array();
        $data = data_submitted();
        foreach ($student_list as $stu) {
            echo '<tr>';
            if (!empty($data->check)) {
                $data1 = array();
                $data1 = $data->check;
                if (in_array($stu->id, $data1))
                    echo '<td><input type="checkbox"  checked  name="check[]" class="check" value="' . $stu->id . '" /></td>';
                else
                    echo '<td><input type="checkbox"   name="check[]" class="check" value="' . $stu->id . '" /></td>';
            } else
                echo '<td><input type="checkbox"   name="check[]" class="check" value="' . $stu->id . '" /></td>';
            $studentservice_id = $DB->get_record('local_userdata', array('userid' => $stu->id));
            echo '<td>' . $studentservice_id->serviceid . '</td>';
            echo '<td>' . $stu->firstname . ' ' . $stu->lastname . '</td>';

            echo '<td>' . $stu->year . '</td>';
            echo'</tr>';
        }
        echo '</tbody>';
        echo '</table>';

        /* ---after select asignee role only...another dropdwon(filter is will be enabled)--- */
        $mentorlevel = "local/clclasses:approvemystudentclclasses";
        $roleid = get_roles_with_capability($mentorlevel, $permissions = NULL, $context = '');
        if (empty($roleid)) {
            $e = get_string('assigncap', 'local_assignmentor');
            throw new Exception($e);
        }
        foreach ($roleid as $roles) {
            if ($roles->shortname == 'mentor')
                $assignee_roleid = $roles->id;
        }

        $out = array();
        $out[0] = get_string('assignadvisor', 'local_assignmentor');
        if($assignee_roleid){
        $mentor_parentlist = $DB->get_records_sql("select u.id,u.firstname from {$CFG->prefix}local_school_permissions AS per
			JOIN {$CFG->prefix}user AS u ON u.id=per.userid	 where u.deleted<>1 and u.suspended<>1 and per.schoolid=$sid and per.roleid=$assignee_roleid ");
        
        foreach ($mentor_parentlist as $mp_list) {
            $out[$mp_list->id] = format_string($mp_list->firstname);
       
           }
        }
        if (empty($mentor_parentlist)) {
            $e = get_string('noadvisortoschool', 'local_assignmentor');
            throw new Exception($e);
        }

        echo html_writer::select($out, 'mentorid', $out, null, array('id' => 'mentorid'), '1');
        echo '<input type="hidden" name="sid" value="' . $sid . '" />';
        echo '<input type="hidden" name="pid" value="' . $programid . '" />';
        echo '<input type="hidden" name="aid" value="' . $assignee_roleid . '" />';
        echo '<input  style="margin-top:20px;"   type="submit" id="mentorid" class="assign_advisorbutton"  value="Assign to Academic advisor" />';
        echo '</div></form>';
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




