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
$alpha = optional_param('apha', 0, PARAM_INT);
$newins = optional_param('newins', 0, PARAM_INT);
$instr_id = optional_param('instr', 0, PARAM_INT);
$deptid = optional_param('deptid', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_INT);
$id = optional_param('id', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);

global $CFG, $DB;
$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/departments/display_instructor.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('display_instructor', 'local_departments'));
$currenturl = "{$CFG->wwwroot}/local/departments/display_instructor.php";
$returnurl = new moodle_url('/local/departments/display_instructor.php');

$dept_ob = manage_dept::getInstance();
$hier1 = new hierarchy();

//---code starts- to unassign instructor from department----
// function to get instructor name and department name using id of local_dept_instructor table
function get_dynamic_language_strings($id) {
    global $DB, $CFG;
    $exists_ins = $DB->get_record('local_dept_instructor', array('id' => $id));
    $name = $DB->get_record('user', array('id' => $exists_ins->instructorid));
    $userfullname = $name->firstname . '' . $name->lastname;
    $deptname = $DB->get_record('local_department', array('id' => $exists_ins->departmentid));
    $temp = new stdClass(); //used for dynamic language strings
    $temp->instructorname = $userfullname;
    $temp->departmentname = $deptname->fullname;
    return $temp;
}

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $temp = get_dynamic_language_strings($id);
        /* ---start of vijaya--- */
        $conf = new object();
        $dept = $DB->get_record('local_dept_instructor', array('id' => $id));
        $conf->username = $DB->get_field('user', 'username', array('id' => $dept->instructorid));
        $conf->deptname = $DB->get_field('local_department', 'fullname', array('id' => $dept->departmentid));
        /* ---end of vijaya--- */
        if ($DB->delete_records('local_dept_instructor', array('id' => $id))) {
            /* ---start of vijaya--- */
            $message = get_string('msg_del_ins_dept', 'local_departments', $conf);
            $userfrom = $DB->get_record('user', array('id' => $USER->id));
            $userto = $DB->get_record('user', array('id' => $dept->instructorid));
            /* Bug report #253  -  Invoice Messages
             * @author hemalatha c arun <hemalatha@eabyas.in> 
             * Resolved - changed the message format
             */
            $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
            /* ---end of vijaya--- */
            $options = array('style' => 'notifysuccess');
            $suc_unassign_ins = get_string('success_unassign_ins', 'local_departments', $temp);
            $hier1->set_confirmation($suc_unassign_ins, $currenturl, $options);
        } else {
            $options = array('style' => 'notifyproblem');
            $error_unassign_ins = get_string('error_unassign_ins', 'local_departments', $temp);
            $hier1->set_confirmation($error_unassign_ins, $currenturl, $options);
        }
    }
}
if ($id and $unassign and confirm_sesskey()) {
    $strheading = get_string('unassign_instructor', 'local_departments');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    $currenttab = 'display_instructor';
    //$dept_ob->dept_tabs($currenttab);
    echo $OUTPUT->heading($strheading);

    //check if insrtuctor...is assigned to any active class room .....
    $exists_ins = $DB->get_record('local_dept_instructor', array('id' => $id));

    $temp = get_dynamic_language_strings($id);
    $exists = '';
    $exists = $DB->get_record_sql("select distinct instructorid from {local_scheduleclass} where instructorid=$exists_ins->instructorid and departmentinid=$exists_ins->departmentid  group by instructorid");
    //  $exists=$DB->get_records('local_scheduleclass',array('instructorid'=>$exists_ins->instructorid,'departmentinid'=>$exists_ins->departmentid));
    // print_object($exists);
    if ($exists) {
        echo $confirm_msg = get_string('instructor_assigrned_toclass', 'local_departments', $temp);
        echo $OUTPUT->continue_button(new moodle_url('/local/departments/display_instructor.php'));
        // $error_unassign_ins=get_string('error_unassign_ins','local_departments');
        // $hier1->set_confirmation($error_unassign_ins,$currenturl); 
    } else {
        $yesurl = new moodle_url('/local/departments/display_instructor.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm_unassignsinstructor', 'local_departments', $temp);
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        // $DB->delete_records('local_dept_instructor',array('id'=>$id));
        // $suc_unassign_ins=get_string('success_unassign_ins','local_departments');
        // $hier1->set_confirmation($suc_unassign_ins,$currenturl); 
    }
    echo $OUTPUT->footer();
    die;
}
//---code end- to unassign instructor from department------------------------------

echo $OUTPUT->header();
//-------------manage_dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));
$currenttab = 'display_instructor';
$dept_ob->dept_tabs($currenttab);
//  description of the  table -------------------- 
//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
echo $OUTPUT->box(get_string('dept_ins_desc', 'local_departments'));
//echo $OUTPUT->box_end();
//---------assign new instructor  button--------------

if (has_any_capability(array('local/departments:assigninstructor','local/departments:manage'), $systemcontext) ) {
    echo '<form action="assign_instructor.php" method="post">
         <input type="submit" value="Assign new instructor" id="id_submitbutton" style="float:right;" ></input>
	 </form>';
}

try {
// checking if login user is registrar or admin
    $schoolid = $dept_ob->check_loginuser_registrar_admin();
//----only displaying registrar enroll school informations------------  
    foreach ($schoolid as $sid) {
        $temp[] = $sid->id;
    }
    $school_id = implode(',', $temp);

    //------------- code used to arranging the instructor in aplphabetical order--------------
    ///if count is greater than 5 only display alphabetical order to sort
    $get_count = $DB->get_records_sql("select * from {$CFG->prefix}local_dept_instructor as ins where  ins.schoolid in ($school_id)");
    $count = sizeof($get_count);
    if ($count > 5) {
        for ($i = 65; $i <= 90; ++$i) {

            $letter = ($i == $alpha) ? '<span style="color:rgb(0, 0, 211); font-family:Opensans-Bold;">' . chr($i) . '</span>' : chr($i);
            echo '<a href="display_instructor.php?apha=' . $i . '">' . $letter . '</a>';
            echo '  ';
        }
        echo '<a href="display_instructor.php?apha=0">All</a>';
    }
    if (!empty($alpha)) {
        $a = chr($alpha);
        $alphabets_order = "u.firstname LIKE  '$a%'";
    } else {
        $alphabets_order = 1;
    }
    //------end of alphabetical order----------------------------------------------------------
    //--------- fetching instructor only who assign to (current login)registrar assigned  schools--------------
    $sql1 = "SELECT  ins.id as insid,ins.*,u.*  from {$CFG->prefix}local_dept_instructor as ins
                     JOIN {$CFG->prefix}user AS u
		     ON  ins.instructorid=u.id where ins.schoolid in ($school_id) and $alphabets_order AND ins.instructorid=u.id AND u.deleted=0";
    $instructor_list = $DB->get_records_sql($sql1);
    
    //print_object
    $data = array();
    foreach ($instructor_list as $ins) {
        $line = array();
        $line[] = html_writer::tag('a', $ins->firstname . ' ' . $ins->lastname, array('href' => '' . $CFG->wwwroot . '/local/users/profile.php?id=' . $ins->id . ''));
        $deptname = $DB->get_record('local_department', array('id' => $ins->departmentid));
        $line[] = $deptname->fullname;
        $schoolname = $DB->get_record('local_school', array('id' => $ins->schoolid));
        $line[] = $schoolname->fullname;
         // checking permission, user has unassigning capability or not
        if (has_any_capability(array('local/departments:assigninstructor','local/departments:manage'), $systemcontext) ) {
        $line[] = html_writer::link(new moodle_url('/local/departments/display_instructor.php', array('id' => $ins->insid, 'instr' => $ins->instructorid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('dept_unassign', 'local_departments'), 'title' => get_string('dept_unassign', 'local_departments'), 'class' => 'iconsmall')));
        }
        
        $data[] = $line;
    }
    $PAGE->requires->js('/local/departments/js/display_ins.js');

    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';

    //View Part starts
    //start the table
    $table = new html_table();
    $table->id = 'instable';
    $table->head = array(
        get_string('instructor', 'local_departments'),
        get_string('deptfullname', 'local_departments'),
        get_string('assign_school', 'local_collegestructure'));    
         if (has_any_capability(array('local/departments:assigninstructor','local/departments:manage'), $systemcontext) ) {              
                   array_push($table->head, get_string('dept_unassign', 'local_departments'));
         }
    try {
        if (empty($data)) {
            $s = get_string('no_records', 'local_request');
            throw new Exception($s);
        }
        $table->size = array('15%', '15%', '15%', '15%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
        //  print_object($data);
    } catch (Exception $s) {
        echo html_writer::table($table);
        echo $s->getMessage();
    }
} //--------- end of try block---------------------------------------------
catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
?>




