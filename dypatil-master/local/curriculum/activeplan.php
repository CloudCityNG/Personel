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

$hierarchy = new hierarchy();
require_login();

$PAGE->set_url('/local/curriculum/activeplan.php', array('id' => $id));

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
$returnurl = new moodle_url('/local/curriculum/index.php', array('id' => $id));
$curriculum = new curricula();

$PAGE->requires->js('/local/curriculum/js/delete.js');
$PAGE->requires->css('/local/curriculum/css/styles.css');

$PAGE->navbar->add(get_string('managecurriculum', 'local_curriculum'), new moodle_url('/local/curriculum/index.php', array('id' => $id)));
$heading = ($id > 0) ? get_string('editcurriculum', 'local_curriculum') : get_string('createcurriculum', 'local_curriculum');
$PAGE->navbar->add($heading);
$PAGE->set_title(get_string('curriculum', 'local_curriculum') . ': ' . $heading);

echo $OUTPUT->header();

$currenttab = "activeplan";

echo $OUTPUT->heading(get_string('managecurriculum', 'local_curriculum'));
$curriculum->print_curriculumtabs($currenttab, $id);
echo $OUTPUT->box(get_string('activeplandes', 'local_curriculum'));

$url = new moodle_url('/local/curriculum/makeactiveplan.php');
echo $OUTPUT->single_button($url, get_string('makeactiveplan', 'local_curriculum'), 'get');

// Display Data grid
$records = $DB->get_records('local_activeplan_batch');

if(empty($records)){
    echo get_string('norecordsfound', 'local_curriculum');
}
else {
    $data = array();
    foreach($records as $record){
        $row = array();
        $plan = $DB->get_record('local_curriculum_plan', array('id'=>$record->planid));
        $semester = $DB->get_record('local_semester', array('id'=>$record->semesterid));
        $batch = $DB->get_record('cohort', array('id'=>$record->batchid));
        $program = $DB->get_record('local_program', array('id'=>$record->programid));
        $school = $DB->get_record('local_school', array('id'=>$record->schoolid));
        
        $row[] = $batch->name;
        $row[] = $plan->fullname;
        $row[] = $semester->fullname;
        $row[] = $program->fullname;
        $row[] = $school->fullname;
        $row[] = html_writer::link(new moodle_url('/local/curriculum/makeactiveplan.php', array('id' => $record->id, 'delete' => $record->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')), array('id'=>'deleteactiveplan_confirm'.$record->id));
        $PAGE->requires->event_handler('#deleteactiveplan_confirm'.$record->id.'', 'click', 'M.util.activeplan_show_confirm_dialog', array('message' =>  get_string('removeactiveplan_confirm', 'local_curriculum'), 'callbackargs' => array('id' => $record->id,'extraparams'=>'&confirm=1&delete='.$record->id.'')));
        $data[] = $row;
    }
    $table = new html_table();
    $table->id = 'activeplantable';
    $table->head = array(get_string('batch', 'local_curriculum'),
                         get_string('curri_plan', 'local_curriculum'),
                         get_string('semester', 'local_curriculum'),
                         get_string('program', 'local_curriculum'),
                         get_string('school', 'local_curriculum'),
                         get_string('action', 'local_curriculum'));
    $table->width = '100%';
    $table->align = array('left', 'left', 'left', 'left', 'left');
    $table->data = $data;
    echo html_writer::table($table);
    echo html_writer::script('
                             $(document).ready(function(){
                                $("#activeplantable").dataTable()
                             });
                             ');
}


echo $OUTPUT->footer();
