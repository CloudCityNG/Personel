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
 * @subpackage program
 * @copyright  2012 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/examtype/lib.php');
require_once($CFG->dirroot . '/local/examtype/edit_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once('../../course/lib.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
$hierarchy = new hierarchy();
if ($id > 0) {
    if (!($tool = $DB->get_record('local_examtypes', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_examtype');
    }
    $tool->school_name = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$systemcontext = context_system::instance();

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
$PAGE->set_url('/local/examtype/edit.php', array('id' => $id));

$PAGE->set_context($systemcontext);
if (!has_capability('local/examtype:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$returnurl = new moodle_url('/local/examtype/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/examtype/index.php";
$strheading = get_string('addeditexamtype', 'local_examtype');
$instance = new cobalt_examtype();
// delete function 
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $result = $instance->examtype_delete_instance($id);
        $instance->success_error_msg($result, 'success_del_exam', 'error_del_exam', $currenturl, null);
        redirect($returnurl);
    }
    $strheading = get_string('deleteexamtype', 'local_examtype');
    $PAGE->navbar->add(get_string('manageexamtype', 'local_examtype'), new moodle_url('/local/examtype/index.php', array('id' => $id)));

    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    if (!$exists = $DB->get_records('local_scheduledexams', array('examtype' => $id))) {
        $yesurl = new moodle_url('/local/examtype/edit.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_examtype');
        echo $OUTPUT->box_start('generalbox');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->box_end();
    } else {
        $yesurl = new moodle_url('/local/examtype/index.php');
        $message = get_string('delnotconfirm', 'local_examtype');
        echo '<h4>' . $message . '</h4>';
        echo $OUTPUT->single_button(new moodle_url('/local/examtype/index.php'), get_string('continue'));
    }

    echo $OUTPUT->footer();
    die;
}
// end of delete
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    if (!empty($hide)) {
        $disabled = 0;
    } else {
        $disabled = 1;
    }
    $result = $DB->set_field('local_examtypes', 'visible', $disabled, array('id' => $id));
    $data->examtype = $DB->get_field('local_examtypes', 'examtype', array('id' => $id));
    $data->visible = $DB->get_field('local_examtypes', 'visible', array('id' => $id));
    if ($data->visible == 1) {
        $data->visible = 'Activated';
    } else {
        $data->visible = 'Inactivated';
    }
    if ($result) {
        $message = get_string('success', 'local_examtype', $data);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('failure', 'local_examtype', $data);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}

$PAGE->navbar->add(get_string('manageexamtype', 'local_examtype'), new moodle_url('/local/examtype/index.php', array('id' => $id)));
if ($id > 0) {
    $strheading = get_string('editexamtype', 'local_examtype');
    $string = get_string('pluginname', 'local_examtype') . ' : ' . $strheading;
}
$PAGE->navbar->add($strheading);
$string = get_string('pluginname', 'local_examtype') . ' : ' . $strheading;
$PAGE->set_title($string);
$examtypeedit = new edit_form();
$examtypeedit->set_data($tool);
if ($examtypeedit->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $examtypeedit->get_data()) {
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $result = $instance->examtype_update_instance($data);
        $instance->success_error_msg($result, 'success_up_exam', 'error_up_exam', $currenturl, $data);
    } else {
        $data->description = $data->description['text'];
        $result = $instance->examtype_add_instance($data);
        $instance->success_error_msg($result, 'success_add_exam', 'error_add_exam', $currenturl, $data);
    }
    redirect($returnurl);
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageexamtype', 'local_examtype'));
if ($id < 0)
    $currenttab = 'new';
else
    $currenttab = 'edit';
$instance->print_examtabs($currenttab, $id);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if ($id > 0)
        echo $OUTPUT->box(get_string('editexamdesc', 'local_examtype'));
    else
        echo $OUTPUT->box(get_string('createexamdesc', 'local_examtype'));
    echo '</br>';
}
$exists = $DB->get_records('local_scheduledexams', array('examtype' => $id));
if (empty($exists)) {
    $examtypeedit->display();
} else {
    $yesurl = new moodle_url('/local/examtype/index.php');
    $message = get_string('notedit', 'local_examtype');
    echo $OUTPUT->box_start('generalbox');
    echo '<h4>' . $message . '</h4>';
    echo $OUTPUT->single_button(new moodle_url('/local/examtype/index.php'), get_string('continue'));
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
