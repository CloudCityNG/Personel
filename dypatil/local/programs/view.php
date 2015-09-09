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
 * Course Enrolment reports
 *
 * @package    local
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/programs/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/programs/renderer.php');

$id = required_param('id', PARAM_INT); //Program id
global $CFG;
$myprogram = programs::getInstance();
$renderer = new program_files_renderer();
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/programs:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/programs/view.php');
//Header and the navigation bar
$PAGE->set_heading(get_string('programs', 'local_programs'));
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
$PAGE->navbar->add(get_string('viewprogram', 'local_programs'));

$program = $DB->get_record('local_program', array('id' => $id));
$PAGE->set_title('' . get_string('program', 'local_programs') . ': ' . $program->shortname);
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading($program->fullname); //Program Name
echo $renderer->program_viewer($program); //Displays the program details.
//check whether the Batch is enable or not.
$batchenable = false;
//if($DB->get_record('local_school_settings', array('schoolid'=>$program->schoolid,'name'=>'batch', 'value'=>1)))
if ($DB->get_record_select('local_school_settings', 'schoolid = ? AND name LIKE ? AND value = ?', array($program->schoolid, 'batch', 1)))
    $batchenable = true;
$batch = ($batchenable) ? ', Batches' : '';
echo get_string('curr_module', 'local_programs');

//get the list of dependencies of the program
$curriculums = $DB->get_records('local_curriculum', array('programid' => $program->id));
if ($batchenable)
    $batches = $DB->get_records('local_batches', array('programid' => $program->id));
$modules = $DB->get_records('local_module', array('programid' => $program->id));

//if nothing is created under program.
//if(empty($curriculums) && empty($batches) && empty($modules))
//echo "<h3>No Records found.</h3>";
echo '<div style="float:left;width:30%;padding-right:3%;">';
if (!empty($curriculums)) {
    echo $renderer->dependency_viewer($curriculums, 1); //Displays list of Curriculums
} else {
    echo get_string('no_curriculum', 'local_programs');
}
echo '</div>';
echo '<div style="float:left;width:30%;padding-right:3%;">';
if ($batchenable && !empty($batches)) {
    echo $renderer->dependency_viewer($batches, 2); //Displays list of Batches
} else if ($batchenable && empty($batches)) {
    echo get_string('no_batches', 'local_programs');
}
echo '</div>';
echo '<div style="float:left;width:30%;padding-right:3%;">';
if (!empty($modules)) {
    echo $renderer->dependency_viewer($modules, 3); //Displays list of Modules
} else {
    echo get_string('no_modules', 'local_programs');
}
echo '</div>';

echo $OUTPUT->footer();
