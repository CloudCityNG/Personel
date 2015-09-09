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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage School
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/collegestructure/lib.php');

require_once($CFG->dirroot . '/local/collegestructure/school_form.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$userid = optional_param('userid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$hierarchy = new hierarchy();
$conf = new object();
/* ---First level of checking--- */
require_login();
$school = new school();

/* ---checking the course exists or not--- */
if ($id > 0) {

    if (!($tool = $DB->get_record('local_school', array('id' => $id)))) {
        print_error('invalidtoolid1122', 'local_collegestructure');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}


$PAGE->set_url('/local/collegestructure/school.php', array('id' => $id));
$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);
/* ---second level of checking--- */
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');

    redirect($returnurl);
}

/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
/* ---this is the return url--- */
$returnurl = new moodle_url('/local/collegestructure/index.php', array('id' => $id));

$strheading = get_string('manageschools', 'local_collegestructure');

/* ---Start of delete the school--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {

        $school->school_delete_instance($id);

        redirect($returnurl);
    }
    $strheading = get_string('deleteschool', 'local_collegestructure');
    $PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), "/local/collegestructure/index.php", get_string('viewschool', 'local_collegestructure'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $checkchilditems = $DB->get_records('local_school', array('parentid' => $id));
    /* ---Check if any programs are created for a school--- */
    $checkexistprogram = $DB->get_records('local_program', array('schoolid' => $id));
    /* ---Check if any departments are created for a school--- */
    $checkexistdepartment = $DB->get_record('local_department', array('schoolid' => $id));
    /* ---Check if any conditions are satisfied--- */
    if ($checkchilditems || $checkexistprogram || $checkexistdepartment) {
        $yesurl = new moodle_url('/local/collegestructure/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('cannotdeleteschool', 'local_collegestructure', array('scname' => $tool->fullname));
        echo $message;
        echo $OUTPUT->continue_button(new moodle_url('/local/collegestructure/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey())));
    } else {
        $yesurl = new moodle_url('/local/collegestructure/school.php?id=' . $id . '', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_collegestructure');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}
/* ---End of delete the School--- */

/* ---function for unassigning the users from school--- */
if ($unassign) {
    $returnurl = new moodle_url('/local/collegestructure/assignusers.php');

    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {

        $school->unassign_users_instance($id, $userid);
    }
    $strheading = get_string('unassingheading', 'local_collegestructure');
    $PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), "/local/collegestructure/index.php", get_string('viewschool', 'local_collegestructure'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);

    $yesurl = new moodle_url('/local/collegestructure/school.php', array('id' => $id, 'userid' => $userid, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('unassignregistrar', 'local_collegestructure');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);

    echo $OUTPUT->footer();
    die;
}
/* ---End of function unassigning users--- */
/* ---Start of hide or display the School--- */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
//if (($show >=0 || $hide >=0) and $id and confirm_sesskey()) {
    if (!empty($hide)) {
        $disabled = 0;
    } else {
        $disabled = 1;
    }
    
    //If it ia the parent for other scholls, dont allow to hide it
    $check = $DB->get_field('local_school', 'parentid', array('id' => $id));
    
    
    //Any programs or departments are created under the school, dont allow to inactivate
    $programs = $DB->get_records('local_program', array('schoolid'=>$id));
    $departments = $DB->get_records('local_department', array('schoolid'=>$id));
    
    if($check){
        $message = get_string('failure', 'local_collegestructure');
        $style = array('style' => 'notifyproblem');
    } else if(!empty($programs) || !empty($departments)){
        $message = get_string('failure', 'local_collegestructure');
        $style = array('style' => 'notifyproblem');
    } else {
        $DB->set_field('local_school', 'visible', $disabled, array('id' => $id));
        $data->school = $DB->get_field('local_school', 'fullname', array('id' => $id));
        $data->visible = $DB->get_field('local_school', 'visible', array('id' => $id));
        if ($data->visible == 1) {
            $data->visible = 'Activated';
        } else {
            $data->visible = 'Inactivated';
        }
        $message = get_string('success', 'local_collegestructure', $data);
        $style = array('style' => 'notifysuccess');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
if ($visible >= 0 && $id && confirm_sesskey()) {

    //If it ia the parent for other scholls, dont allow to hide it
    $check = $DB->get_field('local_school', 'parentid', array('id' => $id));
    
    
    //Any programs or departments are created under the school, dont allow to inactivate
    $programs = $DB->get_records('local_program', array('schoolid'=>$id));
    $departments = $DB->get_records('local_department', array('schoolid'=>$id));
    
    if($check){
        $message = get_string('failure', 'local_collegestructure');
        $style = array('style' => 'notifyproblem');
    } else if(!empty($programs) || !empty($departments)){
        $message = get_string('failure', 'local_collegestructure');
        $style = array('style' => 'notifyproblem');
    } else {
        $DB->set_field('local_school', 'visible', $visible, array('id' => $id));
        $data->school = $DB->get_field('local_school', 'fullname', array('id' => $id));
        $data->visible = $DB->get_field('local_school', 'visible', array('id' => $id));
        if ($data->visible == 1) {
            $data->visible = 'Activated';
        } else {
            $data->visible = 'Inactivated';
        }
        $message = get_string('success', 'local_collegestructure', $data);
        $style = array('style' => 'notifysuccess');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$heading = ($id > 0) ? get_string('editschool', 'local_collegestructure') : get_string('createschool', 'local_collegestructure');
$PAGE->navbar->add(get_string('manageschools', 'local_collegestructure'), new moodle_url('/local/collegestructure/index.php', array('id' => $id)));

$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$editform = new school_form(null, array('id' => $id, 'tool' => $tool, 'editoroptions' => $editoroptions));


if ($id > 0) {
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
}
$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {

    $data->description = $data->description['text'];
    /* ---This is the edit form condition--- */
    if ($data->id > 0) {
        /* ---Update the school--- */
        $school->school_update_instance($data->id, $data);
    } else {
        /* ---Create new school--- */

        $school->school_add_instance($data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
if ($id < 0)
    $currenttab = 'create';
else
    $currenttab = 'edit';
echo $OUTPUT->heading(get_string('manageschools', 'local_collegestructure'));
$school->print_collegetabs($currenttab, $id);
if ($id < 0)
    echo $OUTPUT->box(get_string('addschooltabdes', 'local_collegestructure'));
else
    echo $OUTPUT->box(get_string('editschooltabdes', 'local_collegestructure'));
$editform->display();
echo $OUTPUT->footer();
