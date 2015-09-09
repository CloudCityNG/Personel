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
 * @subpackage  Departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/departments/lib.php');
require_once($CFG->dirroot . '/local/departments/dept_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$scid = optional_param('scid', 0, PARAM_INT);

global $CFG, $DB;
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/departments/departments.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));

//this is the return url 
$returnurl = new moodle_url('/local/departments/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/departments/index.php";

// calling manage_dept class instance.....
$dept_ob = manage_dept::getInstance();
$hier1 = new hierarchy();

/* Start code of delete the department  */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $res_out = $dept_ob->delete_update_department($id, $del = 1, $upd = 0, null, $currenturl);
        $dept_ob->success_error_msg($res_out, 'success_er_dept', 'error_er_dept', $currenturl);
    }
    $strheading = get_string('deletedept', 'local_departments');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
//$currenttab = 'deptlist';   
//$dept_ob->dept_tabs($currenttab);
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/departments/departments.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $res_out = $dept_ob->delete_update_department($id, $del = 0, $upd = 0, null, $currenturl);
    if ($res_out == '-3') {
        $deptname = $DB->get_record('local_department', array('id' => $id));
        echo $confirm_msg = get_string('useddept', 'local_departments', $deptname->fullname);
        echo $OUTPUT->continue_button(new moodle_url('/local/departments/index.php'));
    } else {
        $message = get_string('dept_delconfirm', 'local_departments');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* End of delete the department */

//-----code used to hide and show--------------------------------------------
if ($visible != -1 and $id and confirm_sesskey()) {
    $result = $DB->set_field('local_department', 'visible', $visible, array('id' => $id));
    $data->department = $DB->get_field('local_department', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_department', 'visible', array('id' => $id));
    /*
     * ###Bugreport#167- Success Message
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Added succes message after updating the department visibilty
     */
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('dept_success', 'local_departments', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('dept_failure', 'local_departments', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hier1->set_confirmation($message, $returnurl, $style);
}
//-------end of code hide and show-------------------------------------------
//for edit purpose  //
if ($id > 0)
    $PAGE->navbar->add(get_string('edit_department', 'local_departments'));
else
    $PAGE->navbar->add(get_string('createdept', 'local_departments'));
echo $OUTPUT->header();
//----------------manage dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));

try {
    //checking the id is greater than one...if its fetching content from table...used in edit purpose
    if ($id > 0) {
        if (!($tool = $DB->get_record('local_department', array('id' => $id)))) {
            throw new Exception('invalidtoolid');
        }
    } else {
        $tool = new stdClass();
        $tool->id = -1;
    }

// checking if login user is registrar or admin
    $schoolid = $dept_ob->check_loginuser_registrar_admin();
    //adding tabs using manage_tabs function
    $currenttab = 'addnew';
//for edit purpose  //
    if ($id > 0)
        $dept_ob->dept_tabs($currenttab, null, 'edit_label');
    else
        $dept_ob->dept_tabs($currenttab);
    
//creating create_dept form instance
    $dept = new createdept_form(null, array('temp' => $tool, 'scid' => $scid));
    $edit = new stdClass();
    $edit = $tool;
    if ($id > 0) {
        if (!empty($edit->description_text)) {
            $edit->description['text'] = $edit->description_text;
            $edit->description['format'] = $edit->description_format;
        }
        $dept->set_data($edit);
    }


    if ($dept->is_cancelled()) {
        redirect($returnurl);
    } else
       {
       if ($data = $dept->get_data()) {
        //if $id is greater than 0 go for edit else add section
        if ($data->id > 0) {
           // Update code
            // $data->timemodified=time();
            $data->description_text = $data->description['text'];
            $data->description_format = $data->description['format'];
            $res = $dept_ob->delete_update_department($id, $del = 0, $upd = 1, $data, $currenturl);
            $dept_ob->success_error_msg($res, 'success_upd_dept', 'error_upd_dept', $currenturl);
        } else {
            //-------------adding code(creation of dept)
            $data->timecreated = time();
            $data->timemodified = time();
            $data->usermodified = $USER->id;
            $data->description_text = $data->description['text'];
            $data->description_format = $data->description['format'];
            $res = $dept_ob->add_department($data);
            if ($res == '-3') {
                $confirm_msg = get_string('usedsamename', 'local_departments');
                $hier1->set_confirmation($confirm_msg, $currenturl);
            }
            $dept_ob->success_error_msg($res, 'success_ins_dept', 'error_ins_dept', $currenturl);
        } //........end of else.......
    }//...end of else if...............

  }// end of else 
//  description of the create departtment form -------------------- 
//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    if ($id < 0)
        echo $OUTPUT->box(get_string('create_dept_heading', 'local_departments'));
    else
        echo $OUTPUT->box(get_string('up_dept_heading', 'local_departments'));
//echo $OUTPUT->box_end();

    $dept->display();
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>
