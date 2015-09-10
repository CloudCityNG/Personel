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
 * @subpackage  assigning mentor to student
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
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
//if (!has_capability('local/assignmentor:view', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_url('/local/assignmentor/index.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_assignmentor'), new moodle_url('/local/assignmentor/index.php'));
$PAGE->navbar->add(get_string('view', 'local_assignmentor'));
$currenturl = "{$CFG->wwwroot}/local/assignmentor/index.php";
echo $OUTPUT->header();
/* ----assign academic advisor /parent to student--- */
echo $OUTPUT->heading(get_string('pluginname', 'local_assignmentor'));
$hier = new hierarchy();
/* ---printing tabs --- */
$currenttab = 'view';
$assignee_ob = assign_mentortostudent::getInstance();

 /* ---------school filter--------- */
$schoolids = $assignee_ob->check_loginuser_registrar_admin();
    
$assignee_ob->assignmentor_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('desc_view_mentor', 'local_assignmentor'));
}

/* ------code starts- to unassign students from mentor ----- */
if ($id and $unassign and confirm_sesskey()) {
    $del = $DB->delete_records('local_assignmentor_tostudent', array('id' => $id));
    if ($del) {
        $success = get_string('success_unassign_mentor', 'local_assignmentor');
        $options = array('style' => 'notifysuccess');
        $hier->set_confirmation($success, $currenturl, $options);
    } else {
        $error = get_string('error_unassign_mentor', 'local_assignmentor');
        $options = array('style' => 'notifyproblem');
        $hier->set_confirmation($error, $currenturl, $options);
    }
}
echo $OUTPUT->box_start('generalbox boxaligncenter boxwidthwide');
echo $OUTPUT->box_end();
try {
   
    /* ----only displaying registrar enroll school informations------ */
    foreach ($schoolids as $sid) {
        if ($sid->id != null) {
            $temp[] = $sid->id;
        }
    }
    $school_id = implode(',', $temp);
    /* ---------fetching mentor/parent assigned students-------- */
    $sql1 = "SELECT mp.id as assign_stuid, u.id, concat(u.firstname,' ', u.lastname) as studentname,concat(m.firstname,' ', m.lastname)  as mentor,prg.fullname as program,sc.fullname as school,m.id as mentorid
                     from {$CFG->prefix}local_assignmentor_tostudent as mp
                     JOIN {$CFG->prefix}user AS u ON  mp.studentid=u.id and  u.deleted <> 1
		     JOIN {$CFG->prefix}user AS m ON  mp.mentorid=m.id  and  m.deleted <> 1
		     JOIN {$CFG->prefix}local_program AS prg ON prg.id=mp.programid
		     JOIN {$CFG->prefix}local_school AS sc On sc.id=mp.schoolid where mp.schoolid in ($school_id)";
    $assigned_stulist = $DB->get_records_sql($sql1);
   
    $data = array();
    foreach ($assigned_stulist as $stu) {
        $line = array();
        $line[] = $stu->school;
        $line[] = $stu->program;
        $line[] = '<a href="../users/profile.php?id=' . $stu->id . '">' . $stu->studentname . '</a>';
        $line[] = '<a href="../users/profile.php?id=' . $stu->mentorid . '">' . $stu->mentor . '</a>';

        //  if (has_capability('local/assignmentor:manage', $systemcontext)){
        $line[] = html_writer::link(new moodle_url('/local/assignmentor/index.php', array('id' => $stu->assign_stuid, 'unassign' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('unassign', 'local_departments'), 'title' => get_string('unassign', 'local_departments'), 'class' => 'iconsmall')));
        //   }


        $data[] = $line;
    }
    $PAGE->requires->js('/local/assignmentor/js/assign_mentor_test.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = 'advisor';
    $table->head = array(
        get_string('schoolid', 'local_collegestructure'),
        get_string('program', 'local_programs'),
        get_string('student', 'local_assignmentor'),
        get_string('mentor', 'local_assignmentor'));
    //if (has_capability('local/assignmentor:manage', $systemcontext))
    $table->head[] = get_string('unassign', 'local_assignmentor');
    try {
        if (empty($data)) {
            $s = 'Record Not Found';
            throw new Exception($s);
        }
        $table->size = array('25%', '25%', '20%', '20%', '20%');
        $table->align = array('left', 'left', 'left', 'left');
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




