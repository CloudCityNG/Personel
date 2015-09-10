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

$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);    

$hierarchy = new hierarchy();
require_login();

$PAGE->set_url('/local/curriculum/activeplan.php', array('id' => $id));

$systemcontext = context_system::instance();

$PAGE->set_context($systemcontext);
$PAGE->requires->css('/local/curriculum/css/styles.css');
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
$returnurl = new moodle_url('/local/curriculum/activeplan.php', array('id' => $id));
$curriculum = new curricula();

if($id > 0) {
    if(!$tool = $DB->get_record('local_activeplan_batch', array('id'=>$id))){
        print_error('Invalid ID');
    }
} else {
    $tool = new stdClass();
    $tool->id = $id;
}

/* Start of delete the record */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_records('local_activeplan_batch', array('id'=>$id));
        redirect($returnurl);
    }
    $strheading = get_string('removeactiveplan', 'local_curriculum');
    $PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);
    $PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $strheading);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/curriculum/makeactiveplan.php', array('id' => $id, 'delete' => $delete, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('removeactiveplan_confirm', 'local_curriculum', array());
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
$heading = ($id > 0) ? get_string('editcurriculum', 'local_curriculum') : get_string('createcurriculum', 'local_curriculum');
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $heading);

$editform = new makeactiveplan_form(null, array('id' => $id));


$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    $data->timecreated = time();
    $data->usercreated = $USER->id;
    $DB->insert_record('local_activeplan_batch', $data);
    redirect($returnurl);
} else {
    echo $OUTPUT->header();
    
    $currenttab = "activeplan";
    
    echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
    $curriculum->print_curriculumtabs($currenttab, $id);
    echo $OUTPUT->box(get_string('activeplandes', 'local_curriculum'));
    
    $url = new moodle_url('/local/curriculum/activeplan.php');
    echo $OUTPUT->single_button($url, get_string('viewactiveplan', 'local_curriculum'), 'get', array('style'=>'float: right;'));

    // Form display
    $editform->display();
    
    echo $OUTPUT->footer();
}