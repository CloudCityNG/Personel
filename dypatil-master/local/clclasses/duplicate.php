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
 * @package    local
 * @subpackage classes
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/clclasses/duplicate_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
global $CFG;
$id = optional_param('id', -1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/clclasses/index.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('duplicate', 'local_clclasses'));
$hierarchy = new hierarchy();
$semclass = new schoolclasses();
$tool = $DB->get_record('local_clclasses', array('id' => $id));
$tool->departmentid = $semclass->get_selecteddepartment($id);
$tool->schoolid = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
$tool->semesterid = $DB->get_field('local_semester', 'fullname', array('id' => $tool->semesterid));
$tool->departmentid = $DB->get_field('local_department', 'fullname', array('id' => $tool->departmentid));
$tool->cobaltcourseid = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $tool->cobaltcourseid));
$tool->shortname = '';
if ($tool->online == 1)
    $tool->online = get_string('online', 'local_clclasses');
else
    $tool->online = get_string('offline', 'local_clclasses');
if ($tool->type == 1)
    $tool->type = get_string('clsmode_1', 'local_clclasses');
else
    $tool->type = get_string('clsmode_2', 'local_clclasses');
$returnurl = new moodle_url('/local/clclasses/index.php', array('id' => $id, 'page' => $page));
$mform = new duplicate_form();
$mform->set_data($tool);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $mform->get_data()) {
    $shortname = $data->shortname;
    $main = $DB->get_record('local_clclasses', array('id' => $data->id));
    $main->description = array('text' => $main->description, 'format' => FORMAT_HTML);
    $data = $main;
    $data->description = $main->description['text'];
    $data->startdate = 0;
    $data->enddate = 0;
    $data->shortname = $shortname;
    $data->timecreated = time();
    $data->usermodified = $USER->id;
    $result = $DB->insert_record('local_clclasses', $data);
    if ($result) {
        $message = get_string('duplicatesuccess', 'local_clclasses', $data);
        $options = array('style' => 'notifysuccess');
    } else {
        $message = get_string('duplicatefail', 'local_clclasses');
        $options = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $options);
}

$mform->display();
echo $OUTPUT->footer();
?>