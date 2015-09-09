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
 *
 * @package    local
 * @subpackage  Mentor View
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$unassign = optional_param('unassign', 0, PARAM_INT);
$sid = optional_param('sid', 0, PARAM_INT);
$programid = optional_param('pid', 0, PARAM_INT);
$assignee_id = optional_param('aid', 0, PARAM_INT);
global $CFG, $DB;
$systemcontext =context_system::instance();
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/assignmentor/mentorview.php');
$PAGE->navbar->add(get_string('pluginname', 'local_assignmentor'), new moodle_url('/local/assignmentor/index.php'));
$PAGE->navbar->add(get_string('assign_n', 'local_assignmentor'));
$currenturl = "{$CFG->wwwroot}/local/assignmentor/index.php";
echo $OUTPUT->heading(get_string('pluginname', 'local_assignmentor'));
echo $OUTPUT->header();
$hier = new hierarchy();
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
echo get_string('mentor', 'local_assignmentor') . ' ' . get_string('view', 'local_assignmentor');
echo $OUTPUT->box_end();

try {

    $school_id = 2;
    /* ----fetching mentor/parent assigned students---- */
    $sql1 = "SELECT mp.id as assign_stuid, u.id, u.firstname,prg.fullname as program,sc.fullname as school
                     from {$CFG->prefix}local_assignmentor_tostudent as mp
                     JOIN {$CFG->prefix}user AS u ON  mp.studentid=u.id
		     JOIN {$CFG->prefix}local_program AS prg ON prg.id=mp.programid
		     JOIN {$CFG->prefix}local_school AS sc On sc.id=mp.schoolid
		     where mp.schoolid in ($school_id) and mp.mentorid=12 ";
    $assigned_stulist = $DB->get_records_sql($sql1);
    $data = array();
    foreach ($assigned_stulist as $stu) {
        $line = array();
        $line[] = $stu->school;
        $line[] = $stu->program;
        $line[] = $stu->firstname;
        $line[] = '<input type="button" value="View Report"></input>';
        $data[] = $line;
    }
    $PAGE->requires->js('/local/assignmentor/js/assign_mentor_test.js');


    $table = new html_table();
    $table->id = 'depttable';
    $table->head = array(
        get_string('schoolid', 'local_collegestructure'),
        get_string('program', 'local_programs'),
        get_string('student', 'local_assignmentor'),
        get_string('viewreport', 'local_assignmentor'));
    try {
        if (empty($data)) {
            $s = 'Record Not Found';
            throw new Exception($s);
        }
        $table->size = array('15%', '15%', '15%', '15%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
    } catch (Exception $s) {
        echo html_writer::table($table);
        echo $s->getMessage();
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
echo $OUTPUT->footer();
?>




