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
 * @subpackage Naveen
 * @copyright  2012 Naveen <Naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
$PAGE->requires->css('/local/gradesubmission/css/style.css');
global $CFG, $OUTPUT;

$school = optional_param('school', 0, PARAM_INT);
$program = optional_param('program', 0, PARAM_INT);
$semester = optional_param('semester', 0, PARAM_INT);
$class = optional_param('class', 0, PARAM_INT);

$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
//checking User loggedin or not

require_login();

//Setting page url
$PAGE->set_url('/local/gradesubmission/index_1.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managegradesubmission', 'local_gradesubmission'));
echo $OUTPUT->header();

//Heading of the page
echo $OUTPUT->heading(get_string('managegradesubmission', 'local_gradesubmission'));

// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_gradesubmission'));
}
//local_gradesubmission_cron();exit;
$hierarchy = new hierarchy();
$schools = $hierarchy->get_assignedschools();
$schoolist = $hierarchy->get_school_parent($schools);
echo '<br/><br/>';
echo '<div class="selfilterpos" id="school">';
$sch = new single_select(new moodle_url('/local/gradesubmission/index_1.php'), 'school', $schoolist, $school, null);
$sch->set_label(get_string('schoolname', 'local_gradesubmission') . ':&nbsp&nbsp&nbsp&nbsp');
echo $OUTPUT->render($sch);
echo '</div>';

if (isset($schoolist)) {
    $programslist = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$school AND visible=1", null, '', 'id,fullname', '--Select--');    
    echo '<div class="selfilterpos" id="program">';
    $prog = new single_select(new moodle_url('/local/gradesubmission/index_1.php?school=' . $school), 'program', $programslist, $program, null);
    $prog->set_label(get_string('programname', 'local_gradesubmission') . ':&nbsp&nbsp&nbsp');
    echo $OUTPUT->render($prog);
    echo '</div>';
}

if (isset($schoolist)) {

    if ($program != 0) {
        $semesterslist = $hierarchy->get_school_semesters($school);
        echo '<div class="selfilterpos" id="semester">';
        $sem = new single_select(new moodle_url('/local/gradesubmission/index_1.php?school=' . $school . '&program=' . $program), 'semester', $semesterslist, $semester, null);
        $sem->set_label(get_string('semestername', 'local_gradesubmission') . ':&nbsp&nbsp&nbsp');
        echo $OUTPUT->render($sem);
        echo '</div>';
    }
}
$gradesub = grade_submission::getInstance();
if (isset($semester)) {

    echo '<div class="selfilterpos" id="class" >';
    $clclasseslist = $hierarchy->get_records_cobaltselect_menu('local_clclasses', "schoolid=$school and semesterid=$semester and visible=1", null, '', 'id,fullname', 'Select Class');
    $cls = new single_select(new moodle_url('/local/gradesubmission/index_1.php?school=' . $school . '&program=' . $program . '&semester=' . $semester), 'class', $clclasseslist, $class, null);
    $cls->set_label(get_string('classname', 'local_gradesubmission') . ':&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp&nbsp');
    echo $OUTPUT->render($cls);
    echo '</div>';
}



if (!empty($class)) {

    $today = time();

    $users = $gradesub->get_class_users($semester, $class);
    $exams = $gradesub->get_class_exams($semester, $class);

    // print_object($exams);


    if (empty($users)) {
        echo '<div style="border:1px groove red; padding:10px;color:red;">' . (get_string('nousers', 'local_gradesubmission')) . '</div>';
    }
    if (empty($exams)) {
        echo '<div style="border:1px groove red; padding:10px;color:red;">' . (get_string('noexam', 'local_gradesubmission')) . '</div>';
    }

    foreach ($exams as $exam) {
        if ($exam->opendate > $today) {
            $examsnotcomp = 1;
            echo '<div style="border:1px groove red; padding:10px;color:red;">' . (get_string('examnotcompleted', 'local_gradesubmission')) . '</div>';
        }
    }
}

if (!empty($_POST)) {

    $returnurl = new moodle_url('/local/gradesubmission/index_1.php', array('school' => $school, 'program' => $program, 'semester' => $semester, 'class' => $class));
    $data = $_REQUEST;
    $res = $gradesub->local_insert_gradesubmission($data, $semester, $class);
    // echo $res .'</br>'; exit;
    if ($res == 'err') {
        $message = get_string('error', 'local_gradesubmission');
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
}


//if(!empty($users) AND !empty($exams) AND  $examsnotcomp !=1 )
if (!empty($users) AND ! empty($exams)) {

    echo "<div style='overflow:scroll;width:100%;'>";
    echo "<form action='index_1.php?school=" . $school . "&program=" . $program . "&semester=" . $semester . "&class=" . $class . "' method='POST'>";
    $table = new html_table();
    $heads = array('S.No.', 'Student ID', 'Student Name');

    foreach ($exams as $exam) {
        // print_object($exam);
        if ($exam->source == 'offline') {
            $examtype = $exam->examtype;
            $lecturetype = $exam->lecturetype;
            $sqlq = "SELECT extype.examtype,ltype.lecturetype from {local_examtypes} extype, {local_lecturetype} ltype 
                WHERE extype.id = {$examtype} and ltype.id = {$lecturetype}";

            $classexams1 = $DB->get_records_sql($sqlq);
            foreach ($classexams1 as $examname) {
                array_push($heads, $examname->examtype . '-' . $examname->lecturetype . '(' . $exam->grademax . ')');
            }
        } else {

            $sql1 = "SELECT itemname,grademax from {grade_items} where id={$exam->examid}";
            $classexams1 = $DB->get_records_sql($sql1);

            foreach ($classexams1 as $examname) {
                array_push($heads, $examname->itemname . '(' . round($examname->grademax, 2) . ')');
            }
        }
    }

    array_push($heads, 'Course Total', 'Grade Percentage', 'Grade', 'Grade Point');

    $table->head = $heads;

    $data = array();
    $sno = 1;


    foreach ($users as $user) {

        //'<input type="hidden" name="userid" value='.$user->userid.'>';
        $info1 = array($sno, $user->userid, $user->firstname . ' ' . $user->lastname);

        $grademaxtot = 0;
        $coursetotal = 0;
        foreach ($exams as $exam) {
            $info2 = array();

            if ($exam->source == 'offline') {
                $gsql = "SELECT * from {local_user_examgrades} where examid={$exam->examid} and semesterid={$exam->semesterid} and classid={$exam->classid} and userid={$user->userid} and source='offline'";
                $exgrade = $DB->get_record_sql($gsql);

                $finalexgrade = ($exgrade) ? $exgrade->finalgrade : '';
                //   $info2[] = "<input type='text' name='examid[$exam->examid][$user->userid]' value='{$finalexgrade}' />/{$exam->grademax}";
                $info2[] = "<input type='text' name='examid[$exam->examid][$user->userid][$exam->source]' value='{$finalexgrade}' />";
                $grademaxtot = $grademaxtot + $exam->grademax;
                $coursetotal = $coursetotal + $finalexgrade;
            } else {
                $sql = "SELECT c.onlinecourseid, comp.* from {local_class_completion} comp 
                    JOIN {local_clclasses} c on comp.classid=c.id
                    where comp.schoolid=c.schoolid and comp.semesterid = c.semesterid and comp.examid={$exam->examid} and comp.source='online'";

                $coid = $DB->get_record_sql($gsql);

                $gsql = "SELECT gg.*,gi.grademax from {grade_grades} gg 
                    JOIN  {grade_items} gi on gi.id=gg.itemid
                    where gi.id={$exam->examid} and gg.userid={$user->userid}";

                $exgrade = $DB->get_record_sql($gsql);

                $finalexgrade = ($exgrade) ? $exgrade->finalgrade : '';

                $finalgrd = round($finalexgrade, 2);
                $grademax = round($exgrade->grademax);

                $info2[] = "<input type='text' name='examid[$exam->examid][$user->userid][$exam->source]' value='{$finalgrd}' readonly ='readonly' />";

                $grademaxtot = $grademaxtot + $grademax;
                $coursetotal = $coursetotal + $finalexgrade;
            }

            foreach ($info2 as $info) {
                array_push($info1, $info);
            }
        }

        $coutotal = ($coursetotal) ? $coursetotal : '---';
        $grademaxtot = ($grademaxtot) ? $grademaxtot : '---';

        array_push($info1, $coutotal . '/' . $grademaxtot);
        $percentage = ($coutotal / $grademaxtot) * 100;
        $per = round($percentage);
        array_push($info1, $per . '%');

        $psql = "SELECT letter,gradepoint from {local_gradeletters} where {$per} BETWEEN markfrom and markto";
        $gradepoint = $DB->get_record_sql($psql);

        if ($gradepoint) {
            $gletter = $gradepoint->letter;
            $gpoint = $gradepoint->gradepoint;
        } else {
            $gletter = 'Not Defined';
            $gpoint = 'Not Defined';
        }

        // $gletter = ($gradepoint->letter) ? $gradepoint->letter : 'Not Defined' ;
        //  $gpoint = ($gradepoint->gradepoint) ? $gradepoint->gradepoint : 'Not Defined' ;
        array_push($info1, $gletter, $gpoint);

        $data[] = $info1;
        $sno++;

        if ($coutotal != '---') {

            $sql = "SELECT * from {local_user_classgrades} where userid={$user->userid} and semesterid={$semester} and classid={$class}";
            $record_exists = $DB->get_record_sql($sql);

            if ($record_exists) {
                $clsdet->id = $record_exists->id;
                $clsdet->coursetotal = $coutotal;
                $clsdet->percentage = $per;
                $clsdet->gradeletter = $gletter;
                $clsdet->gradepoint = $gpoint;
                $clsdet->timemodified = time();
                $clsdet->usermodified = $USER->id;
                $DB->update_record('local_user_classgrades', $clsdet);
            } else {

                $clsdet = new stdClass();
                $clsdet->userid = $user->userid;
                $clsdet->schoolid = $school;
                $clsdet->programid = $program;
                $clsdet->semesterid = $semester;
                $clsdet->classid = $class;
                $clsdet->courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $class));
                $clsdet->coursetotal = $coutotal;
                $clsdet->percentage = $per;
                $clsdet->gradeletter = $gletter;
                $clsdet->gradepoint = $gpoint;
                $clsdet->timecreated = time();
                $clsdet->timemodified = time();
                $clsdet->usermodified = $USER->id;

                $DB->insert_record('local_user_classgrades', $clsdet);
            }
        }
    }

    $table->data = $data;

//print_object($table);
    echo html_writer::table($table);
    echo "</div>";
    echo "<div id='gsubmit'>";
    echo "<input type='submit' name='submit' id='gsubmitbut' value='Submit Grades' />";
    echo '</div>';
    echo "</form>";
}

echo $OUTPUT->footer();
