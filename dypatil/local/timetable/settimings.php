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
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/timetable/setacademiyear_form.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$scid = optional_param('scid', 0, PARAM_INT);
$from = optional_param('from', 0, PARAM_INT); // used to indicate from main view or toogle view

global $CFG, $DB;
$systemcontext =  context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/timetable/settimings.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/index.php'));

//this is the return url 
$returnurl = new moodle_url('/local/timetable/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/timetable/index.php";

// calling manage_dept class instance.....
$timetable_ob = manage_timetable::getInstance();
$hier1 = new hierarchy();

/* Start code of delete the department  */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        if($from)
         $res_out = $timetable_ob->timetable_delete_single_timeintervals($id, $from);    
            else
        $res_out = $timetable_ob->timetable_delete_timeintervals($id);
        $timetable_ob->success_error_msg($res_out, 'success_del_timeintervals', 'error_del_timeintervals', $currenturl);
    }
    $strheading = get_string('deletetimeintervals', 'local_timetable');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/timetable/index.php', array('id' => $id, 'delete' => 1, 'confirm' => 1,'from'=>$from, 'sesskey' => sesskey()));
    if($from)
    $res_out = $timetable_ob->timetable_delete_single_timeintervals($id,$from);
    else
    $res_out = $timetable_ob->timetable_delete_timeintervals($id);
    if ($res_out > 1) {
        echo $confirm_msg = get_string('usedtimeinterval', 'local_timetable');
        echo $OUTPUT->continue_button(new moodle_url('/local/timetable/index.php'));
    } else {
        $message = get_string('timeinterval_delconfirm', 'local_timetable');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* End of delete the department */

//-----code used to hide and show--------------------------------------------
if (($hide != 0 or $show != 0) and $id and confirm_sesskey()) {

    $timetable_ob->timetable_hideshow_timeintervals($id, $hide, $show, $from);
    //$result = $DB->set_field('local_department', 'visible', $visible, array('id' => $id));
    //$data->department = $DB->get_field('local_department', 'fullname', array('id' => $id));
    //$data->visible = $DB->get_field('local_department', 'visible', array('id' => $id));
    /*
     * ###Bugreport#167- Success Message
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Added succes message after updating the department visibilty
     */
}
//-------end of code hide and show-------------------------------------------
//for edit purpose  //
if ($id > 0)
    $PAGE->navbar->add(get_string('editstandard_timings', 'local_timetable'));
else
    $PAGE->navbar->add(get_string('setstandard_timings', 'local_timetable'));
echo $OUTPUT->header();
//----------------manage dept heading------------------------
echo $OUTPUT->heading(get_string('pluginname', 'local_timetable'));

try {
    //checking the id is greater than one...if its fetching content from table...used in edit purpose
    if ($id > 0) {
        if (!($tool = $DB->get_record('local_timeintervals', array('id' => $id)))) {
            throw new Exception('invalidtoolid');
        }
    } else {
        $tool = new stdClass();
        $tool->id = -1;
    }

// checking if login user is registrar or admin
    $schoolid = $timetable_ob->check_loginuser_registrar_admin();

    //  print_object($schoolid);
    //adding tabs using manage_tabs function
    $currenttab = 'set_timings';
//for edit purpose  //
    if ($id > 0)
        $timetable_ob->timetable_tabs($currenttab, null, 'edit_label');
    else
        $timetable_ob->timetable_tabs($currenttab);

//creating create_dept form instance
    if ($id > 0) {
        $edit = new stdClass();
        $edit = $tool;
        $edit = $timetable_ob->timetable_converting_timeformat($tool);
    }
    else
      $edit=array();
      
    $settimings = new settimings_form(null, array('temp' => $edit, 'scid' => $scid,'id' =>$id));
    //    print_object($tool);
    // if ($id > 0) {
    //    $edit =$timetable_ob-> timetable_converting_timeformat($tool);
    //    print_object($edit);   

    $settimings->set_data($edit);
    //}


    if ($settimings->is_cancelled()) {
        redirect($returnurl);
    } else if ($data = $settimings->get_data()) {          
        //if $id is greater than 0 go for edit else add section
        if ($data->id > 0) {
            // Update code
            // $data->timemodified=time();

            $res = $timetable_ob->timetable_update_timeintervals($data);
            $timetable_ob->success_error_msg($res, 'success_upd_timeintervals', 'error_upd_timeintervals', $currenturl);
        } else {
            //-------------adding code(creation of dept)

            $res = $timetable_ob->add_timeintervals($data);
            if ($res) {
                //$confirm_msg = get_string('usedsamename', 'local_departments');
                // $hier1->set_confirmation($confirm_msg, $currenturl);
            }
            $timetable_ob->success_error_msg($res, 'success_msg_addingtimeintervals', 'error_msg_addingtimeintervals', $currenturl);
        } //........end of else.......
    }//...end of else if...............
//  description of the create departtment form -------------------- 
//echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
    if ($id < 0)
        echo $OUTPUT->box(get_string('create_timesettings', 'local_timetable'));
    else
        echo $OUTPUT->box(get_string('edit_timesettings', 'local_timetable'));
//echo $OUTPUT->box_end();

    $settimings->display();
} catch (Exception $e) {
    echo $e->getMessage();
}


echo $OUTPUT->footer();
?>