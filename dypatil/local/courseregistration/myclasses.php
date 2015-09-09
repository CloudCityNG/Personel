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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
global $CFG, $USER, $DB, $PAGE,$OUTPUT;
$PAGE->set_url('/local/courseregistration/myclasses.php');
$PAGE->set_pagelayout('admin');
$systemcontext = context_user::instance($USER->id);

$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */

/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);

/* ---$PAGE->set_url('/local/courseregistration/myclasses.php');--- */
if(isloggedin()){
$context = context_user::instance($USER->id);
}
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
/* ---Heading of the page--- */

if (has_capability('local/courseregistration:view', $context)) {

    $heading = get_string('myacademics', 'local_courseregistration');
    $PAGE->navbar->add(get_string('myacademics', 'local_courseregistration'));
} else {

    $heading = get_string('mycurrentplan', 'local_courseregistration');
    $PAGE->navbar->add(get_string('mycurrentplan', 'local_courseregistration'));
}
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
//--------error handling
$hier = new hierarchy();

// to check logged in user is student or not
$student=$hier->is_student($USER->id);
if(empty($student))
$hier->get_school_items();

$exams = new schedule_exam();
/* ---Moodle 2.2 and onwards--- */
$currenttab = 'mycurrentplan';
/* ---adding tabs--- */
if (has_capability('local/courseregistration:view', $context))
    $exams->studentside_tabs($currenttab);


if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('myclasstabdes', 'local_courseregistration'));
}
$today = date('Y-m-d');


$query = "SELECT sc.*,cc.id AS courseid,
                     cc.fullname AS coursename,s.fullname as sem,
                     cc.credithours AS credithours                  
                     FROM {local_user_clclasses} c
                     JOIN {local_clclasses} lc ON c.classid=lc.id
                     JOIN {local_scheduleclass} sc ON sc.classid=lc.id 
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                     JOIN {local_semester} s On s.id=c.semesterid
                     where c.userid={$USER->id} AND c.studentapproval=1 AND c.registrarapproval=1 AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate))";                        

                        
$insquery = "SELECT c.*,cc.id AS courseid,
                  cc.fullname AS coursename,
                  cc.credithours AS credithours                    
                  FROM {local_scheduleclass} c
                  JOIN {local_clclasses} lc ON c.classid=lc.id
                  JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                  JOIN {local_semester} s On s.id=c.semesterid           
                  where c.instructorid={$USER->id}  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";                        

if (has_capability('local/courseregistration:view', $context)) {
    $classList = $DB->get_records_sql($query);
} else{
    $classList = $DB->get_records_sql($insquery);
}

$data = array();

$credit = 0;

if ($classList) {
    foreach ($classList as $list) {
        $classinfo = $DB->get_record('local_clclasses',array('id'=>$list->classid));       
        $line = array();
        $instructor = array();
        $line[] = '<a title="View Course Details" href="' . $CFG->wwwroot . '/local/cobaltcourses/viewcourse.php?id=' . $list->courseid . '&sesskey=' . sesskey() . '" target="_blank">' . $list->coursename . '</a>';
        $line[] = '<a title="View Class Details" href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $list->classid . '&sesskey=' . sesskey() . '" target="_blank">' . $classinfo->fullname . '</a>';
        if (has_capability('local/courseregistration:view', $context)) {
            if($list->instructorid >0 ){
            $instructorinfo=$DB->get_record('user', array('id'=>$list->instructorid));   
            $line[] = $instructorinfo->firstname.' '.  $instructorinfo->lastname;
            }
            else
            $line[]= get_string('not_mentioned', 'local_courseregistration');
            $ins = get_string('instructor', 'local_courseregistration');
            $wd = '10%';
        } else {

            $wd = '0%';
        }
        if (($list->startdate > 0 )&& ($list->enddate > 0 ) ) {
            $date= date('d M',$list->startdate).' To '. date('d M Y',$list->enddate);
            $line[] = $date;
            $scheduletime = date('h:i A',strtotime($list->starttime)).'-'.date('h:i A',strtotime($list->endtime));
            !empty($list->availableweekdays) ? $line[] = $scheduletime . '<br />(' . $list->availableweekdays . ')' : $line[] = $scheduletime;
        } else {
            $line[] = get_string('not_scheduled', 'local_courseregistration');
            $line[] = get_string('not_scheduled', 'local_courseregistration');
        }
         // credit hours
        $line[] = $list->credithours;
        if ($classinfo->online && $classinfo->onlinecourseid)
            $line[] = '<a title="Launch Course" href="' . $CFG->wwwroot . '/course/view.php?id=' . $classinfo->onlinecourseid . '&sesskey=' . sesskey() . '" target="_blank">' . get_string('launch', 'local_courseregistration') . '</a>';
        else
            $line[] = get_string('offline', 'local_clclasses');
                       
         //class room
         if($list->classroomid > 0){
            $classroominfo=$DB->get_record('local_classroom', array('id'=>$list->classroomid));
            $line[]= $classroominfo->fullname;
         }
         else
         $line[] ='---';
        
        $creditSum = $credit + $list->credithours;
        $credit = $creditSum;
        if (!has_capability('local/courseregistration:view', $context)) {
            $line[] = '<a title="View Class Details" href="' . $CFG->wwwroot . '/local/courseregistration/mystudents.php?id=' . $list->id . '" >' . get_string('view_student_progress', 'local_courseregistration') . '</a>';
        }
        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = get_string('semnotstart', 'local_semesters');
    $data[] = $line;
}
/* ---View Part starts--- */
/* ---start the table--- */
$table = new html_table();
if (has_capability('local/courseregistration:view', $context))
    $table->head = array(
        get_string('coursename', 'local_courseregistration'), get_string('code', 'local_clclasses'), $ins, get_string('date', 'local_courseregistration'), get_string('timing', 'local_courseregistration'), get_string('credithours', 'local_courseregistration'), get_string('launch', 'local_courseregistration'), get_string('room', 'local_courseregistration'));
else
    $table->head = array(
        get_string('coursename', 'local_courseregistration'), get_string('code', 'local_clclasses'), get_string('date', 'local_courseregistration'), get_string('timing', 'local_courseregistration'), get_string('credithours', 'local_courseregistration'), get_string('launch', 'local_courseregistration'), get_string('room', 'local_courseregistration'), get_string('progress', 'local_courseregistration'));
if (has_capability('local/courseregistration:view', $context)) {

    $table->size = array('15%', '10%', $wd, '20%', '12%', '15%', '15%', '25%');
    $table->align = array('left', 'center', 'center', 'center', 'center', 'center');
} else {

    $table->size = array('15%', '10%', '30%', '20%', '12%', '15%', '15%', '25%', '5%');
    $table->align = array('left', 'center', 'center', 'center', 'center', 'center', 'center');
}
$table->width = '99%';
$table->data = $data;
if (has_capability('local/courseregistration:view', $context)) {

    echo html_writer::tag('h5', '<b>' . get_string('semestername', 'local_semesters') . ' - ' . $list->sem . '</b>');
}
echo html_writer::table($table);
if (has_capability('local/courseregistration:view', $context)) {
    print '<table class="generaltable" style="width:99%" border="0px"><tr> 
    <td style="text-align:center;width:25%;">&nbsp;</td>
    <td style="text-align:center;width:10%;">&nbsp;</td> 
    <td style="text-align:center;width:10%;">&nbsp;</td> 
    <td style="text-align:center;width:25%;">&nbsp;</td>
    <td style="text-align:right;width:12%;font-weight:bold">' . get_string('credithours', 'local_cobaltcourses') . ':</td>
    <td style="text-align:center;width:5%;">' . $credit . ' </td>
    <td style="text-align:center;width:15%;"> </td></tr></table>';
}
echo $OUTPUT->footer();
