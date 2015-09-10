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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/clclasses/classes_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL); //
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL); //
$hide = optional_param('hide', 0, PARAM_INT); //
$show = optional_param('show', 0, PARAM_INT); //
$moduleid = optional_param('moduleid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT); //
$proid = optional_param('proid', 0, PARAM_INT);
require_login();
$hierarchy = new hierarchy();
$semclass = new schoolclasses();
$systemcontext = context_system::instance();
if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    $returnurl = new moodle_url('/local/error.php');
    redirect($returnurl);
}
/* ---checking the course exists or not--- */
if ($id > 0) {
    if (!($tool = $DB->get_record('local_clclasses', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_clclasses');
    } else {
        $tool->departmentid = $semclass->get_selecteddepartment($id);
        $ins = array();
        $ins[] = $semclass->get_selectedinstructor($id);
        $tool->instructorid = $ins[0];
        $tool->departmentinid = $semclass->get_selecteddepartment($id);
        $sql = "select * from {local_scheduleclass} where classid={$id} ";
        $scheduletime = $DB->get_record_sql($sql);
        $sql = "select max(classroomid) as classroomid,departmentinid as departmentinid from {local_scheduleclass} where classid={$id} ";
        $classroom = $DB->get_record_sql($sql);
        if ($scheduletime) {
            $start = explode(":", $scheduletime->starttime);
            $end = explode(":", $scheduletime->endtime);
            $tool->starthour = $start[0];
            $tool->startmin = $start[1];
            $tool->endhour = $end[0];
            $tool->endmin = $end[1];
        }
        if ($classroom) {
            $tool->choose = ($classroom->classroomid > 0) ? 1 : 0;
            $tool->classroomid = $classroom->classroomid;
            $tool->departmentinid = $classroom->departmentinid;
        }
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_url('/local/clclasses/classes.php', array('id' => $id));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('manageclasses', 'local_clclasses');
$returnurl = new moodle_url('/local/clclasses/index.php', array('id' => $id, 'page' => $page));
/* Start of delete the faculty */
if ($delete) {

    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $data = $DB->get_record('local_clclasses', array('id' => $id));
        $semclass->classes_delete_instance($id);
        $message = get_string('classdeletesuccess', 'local_clclasses', $data);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deleteclasses', 'local_clclasses');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $checkclasses = $DB->get_records('local_user_clclasses', array('classid' => $id));


    if ($checkclasses) {
        $yesurl = new moodle_url('/local/clclasses/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('cannotdelete', 'local_clclasses');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    } else {
        $yesurl = new moodle_url('/local/clclasses/classes.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delclassesconfirm', 'local_clclasses');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}


/* End of delete the faculty */
/* function for unassigning the modules from classes */
if ($unassign) {
    $returnurl = new moodle_url('/local/clclasses/assignmodules.php', array('classesid' => $id, 'proid' => $proid));

    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {

        $semclass->unassign_module_instance($id, $moduleid);
        redirect($returnurl);
    }
    $strheading = get_string('unassignclasses', 'local_clclasses');
    $PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), "/local/clclasses/index.php", get_string('viewclasses', 'local_clclasses'));

    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);


    $yesurl = new moodle_url('/local/clclasses/classes.php', array('id' => $id, 'moduleid' => $moduleid, 'proid' => $proid, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('unassignclasses', 'local_clclasses');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);

    echo $OUTPUT->footer();
    die;
}
/* Start of hide or display the faculty */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    $data = $DB->get_record('local_clclasses', array('id' => $id));
    if (!empty($hide)) {
        $disabled = 0;
        $message = get_string('classinactivesuccess', 'local_clclasses', $data);
    } else {
        $disabled = 1;
        $message = get_string('classactivesuccess', 'local_clclasses', $data);
    }
    $DB->set_field('local_clclasses', 'visible', $disabled, array('id' => $id));
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
$heading = ($id > 0) ? get_string('editclasses', 'local_clclasses') : get_string('createclasses', 'local_clclasses');
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php', array('id' => $id)));

$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$editform = new classes_form(null, array('editoroptions' => $editoroptions, 'id' => $id));
if ($id > 0) {
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
}
$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    /* ---This is the edit form condition--- */
    $data->description = $data->description['text'];
    if ($data->id > 0) {
        /* ---Update--- */
        $semclass->classes_update_instance($data);
        $message = get_string('classupdatesuccess', 'local_clclasses', $data);
    } else {
        /* ---create classes--- */
        $semclass->classes_add_instance($data);
        $message = get_string('classcreatesuccess', 'local_clclasses', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
echo $OUTPUT->header();
$currenttab = "create";
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
$semclass->print_classestabs($currenttab, $id);
if ($id < 0)
    echo $OUTPUT->box(get_string('addcoursetabdes', 'local_clclasses'));
else
    echo $OUTPUT->box(get_string('editcoursetabdes', 'local_clclasses'));
$editform->display();

echo $OUTPUT->footer();
