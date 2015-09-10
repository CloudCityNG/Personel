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
 * @subpackage scheduleexam
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
global $CFG, $exams;

$hierarchy = new hierarchy();
$exams = new schedule_exam();

$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

//    if (!has_capability('local/scheduleexam:manage', $systemcontext)) {
//      print_error('You dont have permissions');
//    }

$PAGE->set_url('/local/scheduleexam/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pageheading', 'local_scheduleexam'), new moodle_url('/local/scheduleexam/index.php'));
$PAGE->navbar->add(get_string('viewscheduledexams', 'local_scheduleexam'));
$PAGE->set_title(get_string('pageheading', 'local_scheduleexam') . ': ' . get_string('viewscheduledexams', 'local_scheduleexam'));

echo $OUTPUT->header();
$context = context_user::instance($USER->id);
//Heading of the page
if (!is_siteadmin()) {
    if (has_capability('local/clclasses:submitgrades', $context)) {
        echo $OUTPUT->heading(get_string('viewscheduledexams', 'local_scheduleexam'));
    }
}



$systemcontext =  context_system::instance();
if (has_capability('local/scheduleexam:manage', $systemcontext)) {
    echo $OUTPUT->heading(get_string('pageheading', 'local_scheduleexam'));
    $currenttab = 'view';
    
    $hierarchy->get_school_items();
    //adding tabs
    $exams->tabs($currenttab);
} elseif (has_capability('local/clclasses:enrollclass', $context)) {
    echo $OUTPUT->heading(get_string('myacademics', 'local_courseregistration'));
    $currenttab = 'scheduledexams';
    //adding tabs
    // to check logged in user is student or not
    $student=$hier->is_student($USER->id); 
    if(empty($student))
    $hier->get_school_items();        
    $exams->studentside_tabs($currenttab);
}
    
$sql = "select ex.*,c.fullname AS classname,s.fullname AS schoolname,sem.fullname AS semestername,extyp.examtype AS examtype,lectyp.lecturetype FROM {local_scheduledexams} ex 
        JOIN {local_school} s on ex.schoolid=s.id
        JOIN {local_clclasses} c on ex.classid=c.id
        JOIN {local_semester} sem on ex.semesterid=sem.id
        JOIN {local_examtypes} extyp on ex.examtype=extyp.id
        JOIN {local_lecturetype} lectyp on ex.lecturetype=lectyp.id";



if (has_capability('local/scheduleexam:manage', $systemcontext)) {
    $schoollist = $hierarchy->get_assignedschools();    
    
    $schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
    if (is_siteadmin()) {
        $schoollist = $hierarchy->get_school_items();
    }
    $schoolidin = implode(',', array_keys($schoollist));
    $sql .= " WHERE ex.schoolid IN ($schoolidin) ORDER BY ex.opendate DESC";
    $exams = $DB->get_records_sql($sql);
}

$today = time();
$context = context_user::instance($USER->id);

if (!is_siteadmin($USER->id)) {
    if (has_capability('local/clclasses:submitgrades', $context)) {
        $instsql = "{$sql} JOIN {local_scheduleclass} sc on sc.classid=ex.classid and sc.semesterid=ex.semesterid
                 JOIN {local_class_completion} cc on ex.id=cc.examid
          where sc.instructorid={$USER->id} and sem.enddate>={$today} ORDER BY ex.opendate DESC";
        $exams = $DB->get_records_sql($instsql);
    }
    $context = context_user::instance($USER->id);
    if (has_capability('local/clclasses:enrollclass', $context)) {
        $sqlstu = "{$sql} JOIN {local_user_clclasses} uc on uc.classid=ex.classid 
                 JOIN {local_class_completion} cc on ex.id=cc.examid
                where uc.userid={$USER->id} and uc.registrarapproval=1 and sem.enddate>={$today} ORDER BY ex.opendate DESC";
        $exams = $DB->get_records_sql($sqlstu);
    }
}

$data = array();
$capabilities_array = array('local/scheduleexam:manage', 'local/scheduleexam:delete', 'local/scheduleexam:update', 'local/scheduleexam:visible');
if ($exams) {
   
    foreach ($exams as $exam) {
        $sql = 'SELECT co.fullname FROM {local_clclasses} c 
                  JOIN {local_cobaltcourses} co on c.cobaltcourseid=co.id where c.id=' . $exam->classid . '';
        $cobcourse = $DB->get_record_sql($sql);
        $line = array();
        $greycss = $exam->visible ? ' ' : 'class="dimmed" ';
        $line[] = '<a ' . $greycss . ' href="' . $CFG->wwwroot . '/local/scheduleexam/view.php?id=' . $exam->id . '">' . format_string($exam->examtype) . '</a>';
        $line[] = $exam->lecturetype;
        $line[] = $exam->classname;
        $line[] = $cobcourse->fullname;
        $exam->starttimehour = ($exam->starttimehour < 10) ? '0' . $exam->starttimehour : $exam->starttimehour;
        $exam->starttimemin = ($exam->starttimemin < 10) ? '0' . $exam->starttimemin : $exam->starttimemin;
        $exam->endtimehour = ($exam->endtimehour < 10) ? '0' . $exam->endtimehour : $exam->endtimehour;
        $exam->endtimemin = ($exam->endtimemin < 10) ? '0' . $exam->endtimemin : $exam->endtimemin;
        $line[] = date("M d Y", $exam->opendate) . ',<br/>' . $exam->starttimehour . ':' . $exam->starttimemin . '  to  ' . $exam->endtimehour . ':' . $exam->endtimemin;

        // $line[] = $exam->schoolname;


        if (has_any_capability($capabilities_array, $systemcontext)) {
            $hierarchy = new hierarchy();
            $line[] = $exam->semestername;
            $buttons = $hierarchy->get_actions('scheduleexam', 'edit', $exam->id, $exam->visible);
            $line[] = $buttons;
            $data[] = $line;
        } else {
            $data[] = $line;
        }
    }
} else {

        $row = new html_table_row();                 
        $optioncell = new html_table_cell(get_string('no_records', 'local_request'));
        $optioncell->colspan = 7;   
        $row ->cells[] = $optioncell;
        $data[]=$row;
 
}

// for description
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    if (has_capability('local/scheduleexam:manage', $systemcontext)) {
        echo $OUTPUT->box(get_string('allowframembedding', 'local_scheduleexam'));
    } else {
        echo $OUTPUT->box(get_string('allowframembedding1', 'local_scheduleexam'));
    }
}

if (!empty($exams)) {
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
echo '</br>';
//View Part starts
//start the table
$PAGE->requires->js('/local/scheduleexam/exam.js');
$table = new html_table();
$table->id = "scheduleexam";
$table->head = array(
    get_string('examname', 'local_scheduleexam'),
    get_string('lecturetype', 'local_scheduleexam'),
    get_string('class', 'local_clclasses'),
    get_string('coursename', 'local_cobaltcourses'),
    get_string('opendateandtime', 'local_scheduleexam'));
// ------------Edited by hema---------------------------------
if (has_any_capability($capabilities_array, $systemcontext)) {
    $table->head[] = get_string('semester', 'local_semesters');
    $table->head[] = get_string('editop', 'local_scheduleexam');
}
$table->size = array('15%', '15%', '15%', '15%', '15%', '15%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left');
$table->width = '100%';

$table->data = $data;

if (!is_siteadmin($USER->id) AND ! (has_capability('local/scheduleexam:manage', $systemcontext))) {
    echo "<table width=100%>
        <tr>
        <td><h4><b>" . get_string('semester', 'local_semesters') . " : " . $exam->semestername . "</b></h4></td>
        </tr>
        </table>";
}
echo html_writer::table($table);

echo $OUTPUT->footer();
