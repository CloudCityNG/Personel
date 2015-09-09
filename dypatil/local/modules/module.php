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
 * @subpackage Modules
 * @copyright  2013 Sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/modules/lib.php');
require_once($CFG->dirroot . '/local/modules/module_form.php');


$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$unassign = optional_param('unassign', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INT);
$hierarchy = new hierarchy();
require_login();
//checking the course exists or not
if ($id > 0) {
    if (!($tool = $DB->get_record('local_module', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_modules');
    }
    $tool->school_name = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    $tool->program_name = $DB->get_field('local_program', 'fullname', array('id' => $tool->programid));
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_url('/local/modules/module.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$userid = $USER->id;
//get the admin layout
$PAGE->set_pagelayout('admin');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
if ($id > 0)
    $string = get_string('pluginname', 'local_modules') . ':' . get_string('editmodule', 'local_modules');
else
    $string = get_string('pluginname', 'local_modules') . ':' . get_string('createmodule', 'local_modules');
$PAGE->set_title($string);
//this is the return url 
$returnurl = new moodle_url('/local/modules/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/modules/index.php";
$strheading = get_string('createmodule', 'local_modules');
$instance = new cobalt_modules();
/* Start of delete the module */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $result = $instance->module_delete_instance($id);
        $instance->success_error_msg($result, 'success_del_module', 'error_del_module', $currenturl, null);
        redirect($returnurl);
    }
    $strheading = get_string('deletemodule', 'local_modules');
    $PAGE->navbar->add(get_string('managemodule', 'local_modules'), "/local/modules/index.php", get_string('viewmodules', 'local_modules'));
    $PAGE->navbar->add($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $checkexistmodule = $DB->get_records('local_module_permissions', array('moduleid' => $id));
    if ($checkexistmodule) {
        $message = get_string('cannotdeletemodule', 'local_modules');
        echo $message;
        echo $OUTPUT->continue_button(new moodle_url('/local/modules/index.php', array('id' => $id, 'delete' => 0, 'confirm' => 1, 'sesskey' => sesskey())));
    } else {

        if (!$exists = $DB->get_records('local_curriculum_plancourses', array('moduleid' => $id))) {
            $yesurl = new moodle_url('/local/modules/module.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
            $message = get_string('delmoduleconfirm', 'local_modules');
            echo $OUTPUT->box_start('generalbox');
            echo $OUTPUT->confirm($message, $yesurl, $returnurl);
            echo $OUTPUT->box_end();
        } else {
            $yesurl = new moodle_url('/local/modules/index.php');
            $message = get_string('delnotconfirm', 'local_modules');
            echo '<h4>' . $message . '</h4>';
            echo $OUTPUT->single_button(new moodle_url('/local/modules/index.php'), get_string('continue'));
        }
    }
    echo $OUTPUT->footer();
    die;
}


/* function for unassigning the course from modules */
if ($unassign) {
    $returnurls = new moodle_url('/local/modules/assigncourse.php', array('moduleid' => $id));
    $PAGE->url->param('unassign', 1);
    if ($confirm and confirm_sesskey()) {
        $instance->unassign_courses_instance($id, $courseid);
        $message = get_string('delete_cou_module', 'local_modules');
        $style = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurls, $style);
    }
    $strheading = get_string('unassigncourse', 'local_modules');
    $PAGE->navbar->add(get_string('managemodule', 'local_modules'), "/local/modules/index.php", get_string('viewmodules', 'local_modules'));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/modules/module.php', array('id' => $id, 'courseid' => $courseid, 'unassign' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('unassign_del', 'local_modules');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

if ($visible >= 0 && $id && confirm_sesskey()) {
    $result = $DB->set_field('local_module', 'visible', $visible, array('id' => $id));
    $data->module = $DB->get_field('local_module', 'fullname', array('id' => $id));
    $data->visible = $DB->get_field('local_module', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('success', 'local_modules', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_modules', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$PAGE->navbar->add(get_string('managemodule', 'local_modules'), new moodle_url('/local/modules/index.php', array('id' => $id)));
if ($id > 0) {
    $strheading = get_string('editmodule', 'local_modules');
}
$PAGE->navbar->add($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$editform = new module_form(null, array('editoroptions' => $editoroptions));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    //This is the edit form condition
    if ($data->id > 0) {
        // Update
        $data->description = $data->description['text'];
        $result = $instance->module_update_instance($data, $userid);
        $instance->success_error_msg($result, 'success_up_module', 'error_up_module', $currenturl, $data);
    } else {
        // Create new
        $data->description = $data->description['text'];
        $result = $instance->module_add_instance($data, $userid);
        $instance->success_error_msg($result, 'success_add_module', 'error_add_module', $currenturl, $data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('managemodule', 'local_modules'));
if ($id < 0)
    $currenttab = "new";
else
    $currenttab = "edit";
$instance->print_tabs($currenttab, $id);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if ($id < 0)
        echo $OUTPUT->box(get_string('addnewdesc', 'local_modules'));
    else
        echo $OUTPUT->box(get_string('editnewdesc', 'local_modules'));
}
if (!$exists = $DB->get_records('local_curriculum_plancourses', array('moduleid' => $id))) {
    $editform->display();
} else {
    $yesurl = new moodle_url('/local/modules/index.php');
    $message = get_string('notedit', 'local_modules');
    echo $OUTPUT->box_start('generalbox');
    echo '<h4>' . $message . '</h4>';
    echo $OUTPUT->single_button(new moodle_url('/local/modules/index.php'), get_string('continue'));
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
