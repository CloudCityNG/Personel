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
 * @subpackage lecturetype
 * @copyright  2013 sreenivasula@eabyas.in
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lecturetype/lib.php');
require_once($CFG->dirroot . '/local/lecturetype/edit_form.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$systemcontext =context_system::instance();
//checking the course exists or not
if ($id > 0) {
    if (!($tool = $DB->get_record('local_lecturetype', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_lecturetype');
    }
    $tool->school_name = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/local/lecturetype/edit.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
if (!has_capability('local/lecturetype:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
//this is the return url 
$returnurl = new moodle_url('/local/lecturetype/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/lecturetype/index.php";
$strheading = get_string('pluginname', 'local_lecturetype');
$instance = new cobalt_lecturetype();
/* Start of delete the lecturetype */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $result = $instance->lecturetype_delete_instance($id);
        $instance->success_error_msg($result, 'success_del_lecture', 'error_del_lecture', $currenturl, null);
        redirect($returnurl);
    }
    $strheading = get_string('deletelecturetype', 'local_lecturetype');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    if (!$exists = $DB->get_records('local_scheduledexams', array('lecturetype' => $id))) {
        $yesurl = new moodle_url('/local/lecturetype/edit.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_lecturetype');
        echo $OUTPUT->box_start('generalbox');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->box_end();
    } else {
        $yesurl = new moodle_url('/local/lecturetype/index.php', array('id' => $id));
        $message = get_string('delnotconfirm', 'local_examtype');
        echo '<h4>' . $message . '</h4>';
        echo $OUTPUT->single_button(new moodle_url('/local/examtype/index.php'), get_string('continue'));
    }
    echo $OUTPUT->footer();
    die;
}
/* End of delete the lecturetype */
/* Start of hide or display the lecturetype */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    if (!empty($hide)) {
        $disabled = 1;
    } else {
        $disabled = 0;
    }
    $DB->set_field('local_lecturetype', 'visible', $disabled, array('id' => $id));
    redirect($returnurl);
}

$PAGE->navbar->add(get_string('managelecturetypes', 'local_lecturetype'), new moodle_url('/local/lecturetype/index.php', array('id' => $id)));

if ($id > 0) {
    $strheading = $strheading . ' : ' . get_string('edit', 'local_lecturetype');
    $PAGE->navbar->add(get_string('edit', 'local_lecturetype'));
} else {
    $strheading = $strheading . ' : ' . get_string('create', 'local_lecturetype');
    $PAGE->navbar->add(get_string('create', 'local_lecturetype'));
}
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$editform = new edit_form(null, array('editoroptions' => $editoroptions));
$editform->set_data($tool);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    //This is the edit form condition
    if ($data->id > 0) {
        // Update
        $data->description = $data->description['text'];
        $result = $instance->lecturetype_update_instance($data);
        $instance->success_error_msg($result, 'success_up_lecture', 'error_up_lecture', $currenturl, $data);
    } else {
        // Create new
        $data->description = $data->description['text'];
        $result = $instance->lecturetype_add_instance($data);
        $instance->success_error_msg($result, 'success_add_lecture', 'error_add_lecture', $currenturl, $data);
    }
    redirect($returnurl);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('create', 'local_lecturetype'));
if ($id > 0)
    $currenttab = 'edit';
else
    $currenttab = 'create';
$instance->print_lecturetabs($currenttab, $id);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if ($id < 0)
        echo $OUTPUT->box(get_string('allowframe', 'local_lecturetype'));
    else
        echo $OUTPUT->box(get_string('editframe', 'local_lecturetype'));
}
$editform->display();
echo $OUTPUT->footer();
