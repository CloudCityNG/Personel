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
require_once($CFG->dirroot . '/local/curriculum/lib.php');
require_once($CFG->dirroot . '/local/curriculum/curriculum_form.php');
require_once($CFG->dirroot . '/local/lib.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$moduleid = optional_param('moduleid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$proid = optional_param('proid', 0, PARAM_INT);
$hierarchy = new hierarchy();
require_login();
//checking the course exists or not
if ($id > 0) {
    if (!($tool = $DB->get_record('local_curriculum', array('id' => $id)))) {
        print_error('invalidcurriculumid', 'local_curriculum');
    } else {
        $settings = $DB->get_record('local_level_settings', array('levelid' => $id));
        if ($graduatelevel = $DB->get_record('local_level_settings', array('levelid' => $id, 'entityid' => 1))) {

            $tool->mincrhour = $graduatelevel->mincredithours;
        }
        $countmincredithours = $DB->get_records('local_level_settings', array('levelid' => $id, 'entityid' => 2));

        foreach ($countmincredithours as $cchours) {

            if ($cchours->subentityid == 1)
                $tool->mincredithours[0] = $cchours->mincredithours;
            if ($cchours->subentityid == 2)
                $tool->mincredithours[1] = $cchours->mincredithours;
            if ($cchours->subentityid == 3)
                $tool->mincredithours[2] = $cchours->mincredithours;
            if ($cchours->subentityid == 4)
                $tool->mincredithours[3] = $cchours->mincredithours;
        }
    }
}

else {
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_url('/local/curriculum/curriculum.php', array('id' => $id));

$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);

//get the admin layout
$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
//this is the return url 

$strheading = get_string('managecurriculum', 'local_curriculum');
$returnurl = new moodle_url('/local/curriculum/index.php', array('id' => $id, 'page' => $page));
$curriculum = new curricula();
/* Start of delete the faculty */
if ($delete) {

    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {

        $curriculum->curriculum_delete_instance($id);
        redirect($returnurl);
    }
    $strheading = get_string('deletecurriculum', 'local_curriculum');
    $PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $checkexistplan = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $id));
    $studentassign = $DB->get_records('local_user_semester', array('curriculumid' => $id));
    $studentdata = $DB->get_records('local_userdata', array('curriculumid' => $id));
    if ($checkexistplan || $studentassign || $studentdata) {
        $yesurl = new moodle_url('/local/curriculum/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey()));

        $message = get_string('cannotdelete', 'local_curriculum', array('scname' => $tool->fullname));
        echo $OUTPUT->box($message);
        echo $OUTPUT->continue_button($yesurl);
        //echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    } else {
        $yesurl = new moodle_url('/local/curriculum/curriculum.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delcurriculumconfirm', 'local_curriculum', array('scname' => $tool->fullname));
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    }
    echo $OUTPUT->footer();
    die;
}


/* End of delete the faculty */
/* function for unassigning the modules from curriculum */
if ($unassign) {
    $returnurl = new moodle_url('/local/curriculum/assignmodules.php', array('curriculumid' => $id, 'proid' => $proid));

    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {

        $curriculum->unassign_module_instance($id, $moduleid);
        redirect($returnurl);
    }
    $strheading = get_string('unassigncurriculum', 'local_curriculum');
    $PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), "/local/curriculum/index.php", get_string('viewcurriculum', 'local_curriculum'));

    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);


    $yesurl = new moodle_url('/local/curriculum/curriculum.php', array('id' => $id, 'moduleid' => $moduleid, 'proid' => $proid, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('unassigncurriculum', 'local_curriculum');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);

    echo $OUTPUT->footer();
    die;
}
/* Start of hide or display the faculty */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    if (!empty($hide)) {
        $disabled = 0;
    } else {
        $disabled = 1;
    }
    $result = $DB->set_field('local_curriculum', 'visible', $disabled, array('id' => $id));
    $data->curriculum = $DB->get_field('local_curriculum', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_curriculum', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('success', 'local_curriculum', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_curriculum', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
$heading = ($id > 0) ? get_string('editcurriculum', 'local_curriculum') : get_string('createcurriculum', 'local_curriculum');
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $heading);

$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$editform = new curriculum_form(null, array('id' => $id, 'editoroptions' => $editoroptions));

if ($id > 0) {
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
}
$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $data->description = $data->description['text'];
    $data->timemodified = time();
    //This is the edit form condition
    if ($data->id > 0) {
        // Update
        $curriculum->curriculum_update_instance($data);
    } else {

        //create curriculum   
        $data->timecreated = time();
        $curriculum->curriculum_add_instance($data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();

if ($id < 0)
    $currenttab = "create";
else
    $currenttab = "edit";


echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
$curriculum->print_curriculumtabs($currenttab, $id);
if ($id < 0)
    echo $OUTPUT->box(get_string('addcurriculumdes', 'local_curriculum'));
else
    echo $OUTPUT->box(get_string('editcurriculumdes', 'local_curriculum'));
$editform->display();
echo $OUTPUT->footer();
