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
 * @subpackage sms
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/jasper/lib.php');
require_once($CFG->dirroot . '/local/jasper/jasper_form.php');


$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);

require_login();
//checking the course exists or not
if ($id > 0) {
    if (!($tool = $DB->get_record('local_jasper', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_jasper');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}

$PAGE->set_url('/local/jasper/jasper.php', array('id' => $id));

$systemcontext =context_system::instance();
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
$returnurl = new moodle_url('/local/jasper/index.php', array('id' => $id));

$strheading = get_string('managejasper', 'local_jasper');

/* Start of delete the settings */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {

        jasper_delete_instance($id);
        $returnurl = new moodle_url('/local/jasper/index.php', array('deleted' => 1));
        redirect($returnurl);
    }
    $strheading = get_string('deletejasper', 'local_jasper');
    $PAGE->navbar->add(get_string('managejasper', 'local_jasper'), "/local/jasper/index.php", get_string('viewjasper', 'local_jasper'));

    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/jasper/jasper.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('deljasperconfirm', 'local_jasper');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);

    echo $OUTPUT->footer();
    die;
}
/* End of delete the faculty */


/* Start of hide or display the faculty */
if ((!empty($hide) or ! empty($show)) and $id and confirm_sesskey()) {
    if (!empty($hide)) {
        $disabled = 0;
    } else {
        $disabled = 1;
    }
    $DB->set_field('local_jasper', 'visible', $disabled, array('id' => $id));
    redirect($returnurl);
}

$PAGE->navbar->add(get_string('pluginname', 'local_jasper'), new moodle_url('/local/jasper/index.php', array('id' => $id)));
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);

$editform = new jasper_form(null, array('editoroptions' => $editoroptions));
$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {

    //This is the edit form condition
    if ($data->id > 0) {
        // Update
        jasper_update_instance($data);
        $returnurl = new moodle_url('/local/jasper/index.php', array('updated' => $id));
    } else {
        // Create new
        jasper_add_instance($data);
        $returnurl = new moodle_url('/local/jasper/index.php', array('created' => $id));
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
$editform->display();
echo $OUTPUT->footer();
