<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
global $CFG, $USER, $DB;
$exams = new schedule_exam();
$systemcontext = context_system::instance();
$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
$currentcss = '/local/courseregistration/css/style.css';
$PAGE->requires->css($currentcss);
$PAGE->requires->js('/local/courseregistration/js/toggle.js');
$PAGE->requires->js('/local/courseregistration/js/expand.js');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/courseregistration/mycurplans.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('mycurriculum', 'local_curriculum'));
$users = users::getInstance();
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('mycurriculum', 'local_curriculum'));
require_login();
/* ---Moodle 2.2 and onwards--- */
$currenttab = 'myplan';
/* ---adding tabs--- */
$exams->studentside_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('mycurriculumdec', 'local_curriculum'));
}

$query = "SELECT cp.*, u.batchid FROM {local_userdata} u JOIN {local_curriculum} cp ON cp.id=u.curriculumid where u.userid={$USER->id}";
$list = $DB->get_record_sql($query);

//$schoolid = $DB->get_field('local_userdata', 'schoolid', array('userid' => $USER->id));
$desc = student_currculum_progress($list->id, $list->schoolid);
$hier = new hierarchy();
// considering current open academiccalendar event
$semesterid = $hier->get_allmyactivesemester($USER->id);
foreach ($semesterid as $key => $value)
    $semid_getclass = $key;

// without considering events
$semid_withoutevents = get_activesemester_withoutevents($USER->id, $list->schoolid);





//Assume only one curriculum
$curriculumpaths = $DB->get_records('local_curriculum_plan', array('curriculumid' => $list->id));

if (empty($curriculumpaths)) {
    echo "Currently No Plans assigned to this curriculum";
} else {
    echo '<ul id="programs">';
    foreach ($curriculumpaths as $curriculumpath) {
		
		$offeredin = $DB->get_record('local_activeplan_batch', array('batchid'=>$list->batchid, 'planid'=>$curriculumpath->id));
		$semester = $DB->get_record('local_semester', array('id'=>$offeredin->semesterid));
		
        echo '<li><a class="expanded" style="cursor:pointer;
    height: 28px;
    display: block;padding-top: 5px;
    padding-left:10px;margin-bottom: 5px;margin-top:10px; background-color: #C0C0C0;
    font-size: 16px;
    border-radius: 2px;color:black;">' . $curriculumpath->fullname . ' ('.$semester->fullname.')'. '</a>';
        echo '<ul id="course">';
        $plancourses = diplay_plancourse($curriculumpath->id);

        //echo '<table>';
        echo '<div style="background-color: #E8E8E8;
    height: 25px;
    margin-bottom: 5px;
    padding-top: 5px;
    padding-left: 8px;
    font-size: 13px;"><span style="width:50%;display: inline-block;text-align:left;">Course Name</span>
       <span style="width:30%;display: inline-block;text-align:center;">Status</span>
	   <span style="width:15%;display: inline-block;text-align:center;">Grade</span></div>';

	  // print_object($plancourses);
        foreach ($plancourses as $plancourse) {
            $grd = isset($plancourse->grade) ? $plancourse->grade : '<b>-</b>';
            /* $sem=$semid_getclass > 0 ? '<b>'.$DB->get_field('local_semester','fullname',array('id'=>$semid_getclass)).'</b>' : '<b>-</b>'; */

            if (strip_tags($plancourse->status) == 'Not Enrolled' && $semid_getclass > 0)
                $sem = $DB->get_field('local_semester', 'fullname', array('id' => $semid_getclass));
            //------- Edited by hema---------------------
            else if (strip_tags($plancourse->status) == 'Rejected' && $semid_withoutevents > 0)
                $sem = $DB->get_field('local_semester', 'fullname', array('id' => $semid_withoutevents));
            else
                $sem = get_stu_previous_sem($plancourse->courseid);

            /* else
              $sem=get_stu_previous_sem($plancourse->courseid); */
            // $sem = isset($courses->semester) ? $courses->semester : '-' ;
            echo '<li><a class="collapsed" style="cursor:pointer;
display: block;padding-top: 5px; border-bottom: 1px solid #e8e8e8; padding-bottom: 5px;
padding-left: 20px;margin-bottom: 15px;color:black;
 ">
<span style="width: 50%;display: inline-block;">' . $plancourse->fullname . '</span> 
<span style="width: 30%;display: inline-block;text-align:center;">' . $plancourse->status . '</span> 
<span style="width: 15%;display: inline-block;text-align:center;">' . $grd . '</span></a>';
            echo '<ul class="desc">';
            echo '<div style="margin-top: 25px;
margin-bottom: 5px;
padding-left: 20px;">';

            //---------- this part only used for enroll to class----------------------------------
            //$status= get_student_course_status($plancourse->courseid,$semid_getclass,$plancourse->status);
            if (strip_tags($plancourse->status) == 'Not Enrolled') {
                //echo "not enrolled";
                //if ($semid_getclass > 0)
                //    echo $msg = retrieve_listofclclasses_ofcourse($plancourse->courseid, $semid_getclass, $schoolid);
                //else
                //    echo "Presently Course Registration Process Not started You Can't Enroll to Classes of This Course";
            }

            //------------------------------------------------------------------------------
            //echo $semid_getclass;


            if (empty($semid_withoutevents))
                print_cobalterror('notenrolledtoanysem', 'local_courseregistration');

            if (strip_tags($plancourse->status) == 'Completed') {
                //echo "completed";
                echo $msg = get_student_class($plancourse->courseid, $semid_withoutevents, $plancourse->status);
            }
            if (strip_tags($plancourse->status) == 'Rejected') {
                //echo "inprogress";
                echo $msg = get_student_class($plancourse->courseid, $semid_withoutevents, $plancourse->status);
            }
            if (strip_tags($plancourse->status) == 'Enrolled (Inprogress)') {
                //echo "inprogress";
                echo $msg = get_student_class($plancourse->courseid, $semid_withoutevents, $plancourse->status);
            }
            if (strip_tags($plancourse->status) == 'Waiting') {
                //echo "waiting";
                echo $msg = get_student_class($plancourse->courseid, $semid_withoutevents, $plancourse->status);
            }

            echo '</div>';
            echo '</ul>';
            echo '</li>';
        }
        echo '</ul>';
        echo '</li>';
    }
    echo '</ul>';
}

echo $OUTPUT->footer();
