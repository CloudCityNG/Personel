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
 * @subpackage courseregistration
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
global $CFG, $USER, $DB;
$exams = new schedule_exam();
$systemcontext =context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
$currentcss = '/local/collegestructure/css/styles.css';
$PAGE->requires->css($currentcss);
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/courseregistration/mycur.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('mycurriculum', 'local_curriculum'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('myacademics', 'local_courseregistration'));

/* ---Moodle 2.2 and onwards--- */
$currenttab = "myplan";
$exams->studentside_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('mycurriculumdec', 'local_curriculum'));
}

/* ---Get the records from the database--- */
$query = "SELECT cp.fullname,cp.id, cp.schoolid,p.fullname as programname,cp.visible  FROM {local_userdata} u JOIN {local_curriculum} cp ON cp.id=u.curriculumid 
    JOIN {local_program} p on cp.programid=p.id where u.userid={$USER->id}";
$curricula = $DB->get_records_sql($query);

$data = array();
if ($curricula) {
    foreach ($curricula as $cur) {

        $line = array();
        $linkcss = $cur->visible ? ' ' : 'class="dimmed" disabled="disabled"';
        $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/courseregistration/mycurplans.php?id=' . $cur->id . '">' . format_string($cur->fullname) . '</a>';

        $programname = $cur->programname;
        $line[] = $programname;
        
        $school = $DB->get_record('local_school', array('id'=>$cur->schoolid), '*', MUST_EXIST);
        $line[] = $school->fullname;
        
        $result = $DB->get_record('local_graduation', array('userid' => $USER->id, 'curriculumid' => $cur->id));
        if (empty($result)) {
            $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/courseregistration/mycurplans.php?id=' . $cur->id . '">' . get_string('inprogress', 'local_courseregistration') . '</a>';
        } else {
            $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/courseregistration/mycurplans.php?id=' . $cur->id . '">Completed</a>';
        }
        if ($result)
            $x = true;
        $linkscss = $x ? ' ' : 'class="dimmed" disabled="disabled"';
        $line[] = '<a  ' . $linkscss . ' href="' . $CFG->wwwroot . '/local/graduation/memo_pdf.php?curid=' . $cur->id . '&userid=' . $USER->id . '&prgid=' . $result->programid . '&year=' . $result->year . '"><button ' . $linkscss . ' type="button" target="_blank">' . get_string('download', 'local_request') . '</button></a>';

        $data[] = $line;
    }
}
else {

    $line = array();
    $line[] = get_string('no_records', 'local_request');
    $data[] = $line;
}

/* ---View Part starts--- */
/* ---start the table--- */
$table = new html_table();
$table->head = array(
    get_string('headername', 'local_curriculum'), get_string('programname', 'local_programs'), get_string('schoolid', 'local_collegestructure'), get_string('curstatus', 'local_curriculum'), get_string('downloadtottranscript', 'local_courseregistration'));
$table->size = array('20%', '20%', '20%', '20%', '20%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
