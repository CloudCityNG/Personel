<?php

//T1.8 - schedule multiple classtype in class plugin
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
require_once($CFG->dirroot . '/local/timetable/scheduleclass_form.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

$id = optional_param('id', -1, PARAM_INT);  // scheduled class id
$deptid = optional_param('deptid', 0, PARAM_INT);
//$schoid = optional_param('schoid', 0, PARAM_INT); //
$schoolid = optional_param('schoolid', 0, PARAM_INT); //
//$semid = optional_param('semid', 0, PARAM_INT); //
$semid = optional_param('semesterid', 0, PARAM_INT); //
$courseid = optional_param('courseid', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT); //
$view = optional_param('view', '', PARAM_TEXT); //
$classtype = optional_param('classtype', '', PARAM_RAW);

$deleteclasstypeid = optional_param('deleteclasstypeid', 0, PARAM_INT);


// only for editing purpose
if ($id > 0 && $edit > 0) {
    $scheduleinfo = $DB->get_record('local_scheduleclass', array('id' => $id));
    $classtype = array($scheduleinfo->classtypeid);
}


// unassigning the slected classtype
if ($deleteclasstypeid && $classtype) {
    if (!empty($classtype)) {
        $key = array_search($deleteclasstypeid, $classtype);
        if ($key)
            unset($classtype[$key]);
    }
}

$systemcontext = context_system::instance();
$PAGE->requires->css('/local/clclasses/css/style.css');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$semclass = new schoolclasses();
$hierarchy = new hierarchy();
$tmobject = manage_timetable::getInstance();
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
// -------------Edited by hema----------------------------------------
$assigninstructor_cap = array('local/clclasses:manage', 'local/clclasses:assigninstructor', 'local/classroomresources:manage');
if (!has_any_capability($assigninstructor_cap, $systemcontext)) {
    print_error('You dont have permissions');
}
$currenturl = "{$CFG->wwwroot}/local/clclasses/index.php";
$returnurl = "{$CFG->wwwroot}/local/clclasses/scheduleclass.php";
//
if ($id > 0) {
    if (!($tool = $DB->get_record('local_scheduleclass', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_clclasses');
    } else {
        $tool->choose = 1;
        $scheduletime = $DB->get_record('local_scheduleclass', array('id' => $id));
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
//

/* Start of delete the schedule class ----------------- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        echo 'inside the delete';
        $res = $tmobject->timetable_delete_scheduleclass($id, $currenturl);
    }
    $strheading = get_string('delete_scheduleclass', 'local_timetable');
    $PAGE->navbar->add($strheading);
    $PAGE->set_heading($SITE->fullname);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/clclasses/scheduleclass.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirm_scheduleclass', 'local_timetable');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}
/* End of delete the schedule class ----------------------- */

//-----code used to hide and show--------------------------------------------
if ($visible != -1 and $id and $confirm and confirm_sesskey()) {
    $res11 = $DB->set_field('local_scheduleclass', 'visible', $visible, array('id' => $id));
    if ($visible)
        $visible_msg = 'activated';
    else
        $visible_msg = 'inactivated';

    $tmobject->success_error_msg($res11, 'success_visible', 'error_visible', $currenturl, $visible_msg);
    redirect($currenturl);
}
//-------end of code hide and show-------------------------------------------


$PAGE->set_url('/local/timetable/scheduleclass.php', array('id' => $id));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);

$strheading = get_string('manageclasses', 'local_clclasses');
$returnurl = new moodle_url('/local/timetable/index.php?view=scheduled');
$heading = ($id > 0) ? get_string('editschedule', 'local_clclasses') : get_string('scheduleclassroom', 'local_clclasses');
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/timetable/index.php', array('view' => $view)));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);

if ($id > 0) {
    $tool = $tmobject->timetable_converting_dbdata_toeditform($tool);
    $editform = new edit_sheduleclass_form(null, array('id' => $id, 'tool' => $tool, 'deptid' => $deptid, 'schoid' => $schoolid, 'semid' => $semid, 'courseid' => $courseid, 'classid' => $classid, 'classtype' => $classtype, 'classtypeid' => $tool->classtypeid));
    $editform->set_data($tool);
    $data = $editform->get_data();
} else {
    $editform = new sheduleclass_form(null, array('id' => $id, 'tool' => $tool, 'deptid' => $deptid, 'schoid' => $schoolid, 'semid' => $semid, 'courseid' => $courseid, 'classid' => $classid, 'classtype' => $classtype));
    $data = $editform->get_data();
}


if ($editform->is_cancelled()) {
    /* Bug report #297  -  Classes>Enroll Users- Past and Upcoming course offerings
     * @author hemalatha c arun <hemalatha@eabyas.in>
     * Resolved - Redirected to view page , when class is not belongs to active semester
     */
    $semlist = $hierarchy->get_allmyactivesemester(NULL, $schoid);
    foreach ($semlist as $key => $value) {
        $activesemesterid = $key;
    }
    if ($activesemesterid == $semid)
        $returnurl = new moodle_url('/local/clclasses/enroluser.php', array('semid' => $semid, 'id' => $classid));
    else
        $returnurl = new moodle_url('/local/clclasses/index.php');
    redirect($returnurl);
}
else if ($data = $editform->get_data()) {



    if ($data->id > 0) {

        $classtypes = array($data->classtypeid);
        foreach ($classtypes as $classtype) {
            $temp = $tmobject->timetable_addscheduleclass_instance($data, $classtype);
            $DB->update_record('local_scheduleclass', $temp);
        }// end of foreach       

        $semid = $data->semesterid;
        $classid = $data->classid;
        /*  Bug report #331  -  Classes management>Upcoming Semester>Scheduling Instructor
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved- if class not belongs to active semester, it redirect to view page.
         */
        if ($activesemesterid == $semid)
            $returnurl = new moodle_url('/local/clclasses/enroluser.php', array('id' => $classid, 'semid' => $semid));
        else
            $returnurl = new moodle_url('/local/clclasses/index.php');
        $message = get_string('classsuccessschedule', 'local_clclasses');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    else {
        $classtypes = $data->classtype;
        foreach ($classtypes as $classtype) {
            $temp = $tmobject->timetable_addscheduleclass_instance($data, $classtype);

            if ($temp == 0) {
                $message = get_string('classsuccessschedule_withsamedata', 'local_clclasses');
                $options = array('style' => 'notifyproblem');
                $hierarchy->set_confirmation($message, new moodle_url('/local/clclasses/index.php'), $options);
            } else
                $res = $DB->insert_record('local_scheduleclass', $temp);
        }// end of foreach      
        ///* Bug-id#255
        // * @author hemalatha c arun<hemaltha@eabyas.in>
        // * Resolved(enrolling instructor to online course, while assiinging class to instructor)
        // */
        //
        //if ($res) {
        //    $query = "select cl.*,sh.* from {local_scheduleclass} sh 
        // JOIN {local_clclasses} cl ON cl.id = sh.classid and cl.onlinecourseid!=0 where sh.classid= $data->classid and cl.online=1 ";
        //    $onlinecourse_data = $DB->get_record_sql($query);
        //    if ($onlinecourse_data) {
        //        $manual = enrol_get_plugin('manual');
        //        $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
        //        $instance = $DB->get_record('enrol', array('courseid' => $onlinecourse_data->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        //        $manual->enrol_user($instance, $onlinecourse_data->instructorid, $instructorrole->id);
        //    }// end of inner if
        //} // end of outer if
        $semid = $data->semesterid;
        $classid = $data->classid;

        if ($activesemesterid == $semid)
            $returnurl = new moodle_url('/local/clclasses/enroluser.php', array('id' => $classid, 'semid' => $semid));
        else
            $returnurl = new moodle_url('/local/clclasses/index.php');

        $message = get_string('classsuccessschedule', 'local_clclasses');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
}
echo $OUTPUT->header();
$currenttab = "schedule";
echo $OUTPUT->heading(get_string('scheduleclassroom', 'local_clclasses'));
$semclass->print_scheduletabs($currenttab, $id, 0, 1);
$conf = new object();
$conf->fullname = $DB->get_field('local_clclasses', 'fullname', array('id' => $classid));
if ($id < 0)
    echo $OUTPUT->box(get_string('scheduleclassdesc', 'local_clclasses', $conf));
else
    echo $OUTPUT->box(get_string('editcschedule', 'local_clclasses', $conf));
$editform->display();
echo $OUTPUT->footer();
?>

