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
 *
 * @package    local
 * @subpackage classes
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
global $CFG;
$systemcontext = context_system::instance();
$facid = optional_param('facid', 0, PARAM_INT);
$proid = optional_param('proid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$semclass = new schoolclasses();
$hierarchy = new hierarchy();
/* * Niranjan-check for capabily check
 * Here I am checking if a user having the manage capabily is there or not. 
 * If capability is not there I am redirecting to the error.php page.
 */
if (!has_capability('local/clclasses:view', $systemcontext) && !is_siteadmin()) {
    $returnurl = new moodle_url('/local/error.php');

    redirect($returnurl);
}
$PAGE->set_url('/local/clclasses/index.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('view', 'local_clclasses'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
$currenttab = "view";
$schoollist = $hierarchy->get_assignedschools();
$semclass->print_classestabs($currenttab);

//if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
//    echo $OUTPUT->box(get_string('allowframembedding', 'local_clclasses'));
//}

if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}
$count = count($schoollist); //Count of schools to which registrar is assigned
if ($count < 1) {
    throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
}
$data = array();
$capabilities_array = array('local/clclasses:manage', 'local/clclasses:delete', 'local/clclasses:update', 'local/clclasses:visible', 'local/clclasses:assigninstructor', 'local/clclasses:enrolluser');
///* ---Only assigned school records can be shown to registrar--- */
//foreach ($schoollist as $school) {
//    if ($school->id != null) {
//        /* ---Get the records from the database--- */
//        $sql = "SELECT lc.id,lc.online,
//             lc.visible AS visible,
//             lc.classlimit AS classlimit,lc.departmentid,lc.cobaltcourseid,
//             lc.shortname,cc.fullname AS coursename,cc.shortname as coursecode,
//             lc.fullname AS classname,
//             ls.fullname AS semestername,ls.id AS semesterid,
//             s.fullname AS schoolname,s.id AS schoolid,
//            (select Max(concat(FROM_UNIXTIME(lsc.startdate, '%d/%b/%Y'),'&nbsp; - &nbsp;',FROM_UNIXTIME(lsc.enddate, '%d/%b/%Y'))) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduledate,
//            (select Max(concat(lsc.starttime,'&nbsp;-&nbsp;',lsc.endtime)) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduletime,
//            (select DISTINCT lsc.availableweekdays FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS availableweekdays
//
//       FROM {local_clclasses} lc JOIN {local_semester} ls 
//       ON lc.semesterid=ls.id JOIN {local_school} s 
//       ON lc.schoolid=s.id JOIN {local_cobaltcourses} cc 
//       ON lc.cobaltcourseid=cc.id where lc.schoolid={$school->id} order by scheduledate DESC";
//    }
//    $classlists = $DB->get_records_sql($sql);
//	$date = date('Y-m-d');
//    // edited by hema on 23 jun 2014: used to fetch current semesterid
//    // $semlist = $hierarchy->get_allmyactivesemester(NULL, $school->id);
//	
//	// $semlist = $DB->get_records_sql("SELECT scl.semesterid FROM {local_semester} AS sem
//                        // JOIN {local_school_semester} AS scl
//                        // ON scl.semesterid = sem.id WHERE scl.schoolid IN ($school->id) AND (
//						// ('{$date}' < from_unixtime( sem.startdate,  '%Y-%m-%d' )) OR 
//						// ('{$date}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' ))
//						// )
//						// group by scl.id ORDER BY sem.startdate DESC");
//    // foreach ($semlist as $key => $value) {
//        // $activesemesterid = $key;
//    // }
//
//    if ($classlists) {
//        $i = 0;
//
//       
//        foreach ($classlists as $classlist) {
//            $line = array();
//            $linkcss = $classlist->visible ? ' ' : 'class="dimmed" ';
//            $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $classlist->id . '">' . $classlist->classname . '</a>';
//
//            $classonline = ($classlist->online == 1) ? get_string('online', 'local_clclasses') : get_string('offline', 'local_clclasses');
//
//            $line[] = $classlist->coursecode;
//            $line[] = $classlist->semestername;
//           
//            $line[] = $classonline;
//            $instructor = array();
//            $instructor[] = $semclass->get_classinstructors($classlist->id);
//            $line[] = implode(', ', $instructor[0]);
//
//            if ($classlist->scheduledate) {
//                $line[] = $classlist->scheduledate;
//                !empty($classlist->availableweekdays) ? $line[] = $classlist->scheduletime . '<br />(' . $classlist->availableweekdays . ')' : $line[] = $classlist->scheduletime;
//            } else {
//                $line[] = html_writer::tag('a', get_string('scheduleclass', 'local_classroomresources'), array('href' => '' . $CFG->wwwroot . '/local/clclasses/scheduleclass.php?classid=' . $classlist->id . '&semid=' . $classlist->semesterid . '&schoid=' . $classlist->schoolid . '&deptid=' . $classlist->departmentid . '&courseid=' . $classlist->cobaltcourseid . ''));
//                $line[] = html_writer::tag('a', get_string('scheduleclass', 'local_classroomresources'), array('href' => '' . $CFG->wwwroot . '/local/clclasses/scheduleclass.php?classid=' . $classlist->id . '&semid=' . $classlist->semesterid . '&schoid=' . $classlist->schoolid . '&deptid=' . $classlist->departmentid . '&courseid=' . $classlist->cobaltcourseid . ''));
//            }
//
//            $buttons = array();
//
//            // ------------------- Edited by hema------------------------------
//            $delete_cap = array('local/clclasses:manage', 'local/clclasses:delete');
//            if (has_any_capability($delete_cap, $systemcontext)) {
//                $buttons[] = html_writer::link(new moodle_url('/local/clclasses/createclass.php', array('id' => $classlist->id, 'page' => $page, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
//            }
//
//            $update_cap = array('local/clclasses:manage', 'local/clclasses:update');
//            if (has_any_capability($update_cap, $systemcontext)) {
//                $buttons[] = html_writer::link(new moodle_url('/local/clclasses/createclass.php', array('id' => $classlist->id, 'page' => $page, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
//            }
//
//            $visible_cap = array('local/clclasses:manage', 'local/clclasses:visible');
//            if (has_any_capability($visible_cap, $systemcontext)) {
//                if ($classlist->visible) {
//                    $buttons[] = html_writer::link(new moodle_url('/local/clclasses/createclass.php', array('id' => $classlist->id, 'page' => $page, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
//                } else {
//                    $buttons[] = html_writer::link(new moodle_url('/local/clclasses/createclass.php', array('id' => $classlist->id, 'page' => $page, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
//                }
//            }
//
//            $buttons[] = html_writer::link(new moodle_url('/local/evaluations/create_evaluation.php', array('clid' => $classlist->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => '' . $CFG->wwwroot . '/local/clclasses/pix/evaluations.png', 'title' => get_string('createevaluation', 'local_clclasses'), 'alt' => get_string('createevaluation', 'local_clclasses'), 'class' => 'iconsmall')));
//
//            $buttons[] = html_writer::link(new moodle_url('/local/clclasses/examsetting.php', array('id' => $classlist->id, 'semid' => $classlist->semesterid, 'schoolid' => $classlist->schoolid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => '' . $CFG->wwwroot . '/local/clclasses/pix/exam.png', 'title' => get_string('criteria', 'local_clclasses'), 'alt' => get_string('criteria', 'local_clclasses'), 'class' => 'iconsmall')));
//            /* $buttons[] =  html_writer::link(new moodle_url('/local/clclasses/classes.php#Iheader', array('id'=>$classlist->id,'semid'=>$classlist->semesterid,'schoolid'=>$classlist->schoolid,'sesskey'=>sesskey())), html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/instructor'), 'title'=>get_string('assigninstructor','local_clclasses'),'alt'=>get_string('assigninstructor','local_clclasses'), 'class'=>'iconsmall'))); */
//            //
//
//            $assigninstructor_cap = array('local/clclasses:manage', 'local/clclasses:assigninstructor');
//            if (has_any_capability($assigninstructor_cap, $systemcontext)) {
//                $schedule = $DB->get_field('local_scheduleclass', 'id', array('classid' => $classlist->id));
//                if(!$schedule){
//                    $schedule = -1;
//                }
//                $buttons[] = html_writer::link(new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $schedule, 'classid' => $classlist->id, 'semid' => $classlist->semesterid, 'schoid' => $classlist->schoolid, 'deptid' => $classlist->departmentid, 'courseid' => $classlist->cobaltcourseid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/instructor'), 'title' => get_string('assigninstructor', 'local_clclasses'), 'alt' => get_string('assigninstructor', 'local_clclasses'), 'class' => 'iconsmall')));
//            }
//
//            // edited by hema disabling enroluser button when course offering is not a current semester
//            $enrolluser_cap = array('local/clclasses:manage', 'local/clclasses:enrolluser');
//            if (has_any_capability($enrolluser_cap, $systemcontext)) {
//                //if ($activesemesterid == $classlist->semesterid) {
//                    $enroluserurl = html_writer::link(new moodle_url('/local/clclasses/classenrol.php', array('id' => $classlist->id)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('enrollusers', 'local_clclasses'), 'alt' => get_string('enrollusers', 'local_clclasses'), 'class' => 'iconsmall')));
//                //} else {
//                    //$enroluserurl = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/enrolusers'), 'title' => get_string('enrollusers_pastsemester', 'local_clclasses'), 'alt' => get_string('enrollusers_thissemester', 'local_clclasses'), 'class' => 'iconsmall'));
//                //}
//
//                $buttons[] = $enroluserurl;
//            }
//
//            $buttons[] = html_writer::link(new moodle_url('/local/clclasses/duplicate.php', array('id' => $classlist->id, 'page' => $page, 'semid' => $classlist->semesterid)), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/copy'), 'title' => get_string('duplicate'), 'alt' => get_string('duplicate'), 'class' => 'iconsmall')));
//
//            if (has_any_capability($capabilities_array, $systemcontext))
//                $line[] = implode(' ', $buttons);
//
//            $data[] = $line;
//        }
//    }
//	$i++;
//}
////print_object($data);
//$PAGE->requires->js('/local/clclasses/filters/classes.js');
//echo "<div id='filter-box' >";
//echo '<div class="filterarea"></div></div>';
//$table = new html_table();
//$table->id = "classtable";
//$table->head = array(
//    get_string('headername', 'local_clclasses'), get_string('cobaltcoursecode', 'local_clclasses'), get_string('semester', 'local_semesters'), get_string('type', 'local_clclasses'), get_string('instructor', 'local_clclasses'), get_string('date', 'local_clclasses'), get_string('time', 'local_clclasses'));
//// ------------------- Edited by hema------------------------------ 
//if (has_any_capability($capabilities_array, $systemcontext)) {
//    array_push($table->head, get_string('action', 'local_clclasses'));
//}
//$table->size = array('11%', '12%', '10%', '5%', '10%', '20%', '8%', '20%');
//$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
//$table->width = '99%';
//$table->data = $data;
//echo html_writer::table($table);
//echo $OUTPUT->footer();

/* ---Only assigned school records can be shown to registrar--- */

$output = $PAGE->get_renderer('local_clclasses');


$clobject = new classes($schoollist);
echo $output->render($clobject);
echo $output->footer();
