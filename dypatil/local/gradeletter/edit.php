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
 * To edit/delete a gradeletter defined for a school
 *
 * @package    local
 * @subpackage gradeletter
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/gradeletter/lib.php');
require_once($CFG->dirroot . '/local/gradeletter/edit_form.php');
require_once($CFG->dirroot . '/course/lib.php');
//require_once('../../course/lib.php');

global $gletters;
$gletters = graded_letters::getInstance();

$hierarchy = new hierarchy();
$id = optional_param('id', -1, PARAM_INT);    // id; -1 if creating new gradeletter
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$currenttab = optional_param('mode', 'create', PARAM_RAW);

$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/local/gradeletter/edit.php', array('id' => $id));

$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
if (!has_capability('local/gradeletter:manage', $systemcontext)) {
    print_error('You dont have permissions');
}

$returnurl = new moodle_url('/local/gradeletter/index.php', array('id' => $id));

$PAGE->navbar->add(get_string('pluginheading', 'local_gradeletter'), new moodle_url('/local/gradeletter/index.php', array('id' => $id)));

$link = ($id > 0) ? get_string('updategradeletter', 'local_gradeletter') : get_string('creategradeletter', 'local_gradeletter');
$PAGE->navbar->add($link);
$PAGE->set_title(get_string('pluginheading', 'local_gradeletter') . ': ' . $link);

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $data = $DB->get_record('local_gradeletters', array('id' => $id));
        $data->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
        $gletters->gradeletter_delete_instance($id);
        $message = get_string('deletedgradeletter', 'local_gradeletter', $data);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $strheading = get_string('deletegradeletter', 'local_gradeletter');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/gradeletter/edit.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirm', 'local_gradeletter');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

//to hide or unhide 
if ($visible != -1 and $id and confirm_sesskey()) {
    $data = $DB->get_record('local_gradeletters', array('id' => $id));
    $data->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
    $res11 = $DB->set_field('local_gradeletters', 'visible', $visible, array('id' => $id));
    if ($visible == 0) {
        $message = get_string('inactivegradeletters', 'local_gradeletter', $data);
    } else {
        $message = get_string('activegradeletters', 'local_gradeletter', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

if ($id > 0) {
    if (!($tool = $DB->get_record('local_gradeletters', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_gradeletter');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$gradeeditform = new edit_form(null, array('id' => $id));
$gradeeditform->set_data($tool);


if ($gradeeditform->is_cancelled()) {
    redirect($returnurl);
}
else{
 if ($data = $gradeeditform->get_data()) {
  
    $data->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
    if ($data->id > 0) {
        // Update
        $gletters->gradeletter_update_instance($data);
        $message = get_string('updatedgradeletter', 'local_gradeletter', $data);
    } else {
        // Create new
        $gletters->gradeletter_add_instance($data);
        $message = get_string('createdgradeletter', 'local_gradeletter', $data);
    }
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginheading', 'local_gradeletter'));

$currenttab = 'create';
$gletters->createtabview_gl($currenttab, $id);
echo $OUTPUT->box(get_string('managegradelettertabdes', 'local_gradeletter'));
$gradeeditform->display();
echo $OUTPUT->footer();
