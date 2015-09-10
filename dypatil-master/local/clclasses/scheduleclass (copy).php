<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
require_once($CFG->dirroot . '/local/clclasses/createclass_form.php');
require_once($CFG->dirroot . '/lib/enrollib.php');
$id = optional_param('id', -1, PARAM_INT);
$deptid = optional_param('deptid', 0, PARAM_INT);
$schoid = optional_param('schoid', 0, PARAM_INT); //
$semid = optional_param('semid', 0, PARAM_INT); //
$courseid = optional_param('courseid', 0, PARAM_INT);
$classid = optional_param('classid', 0, PARAM_INT); //
$systemcontext = context_system::instance();
$PAGE->requires->css('/local/clclasses/css/style.css');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$semclass = new schoolclasses();
$hierarchy = new hierarchy();
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
//
if ($id > 0) {
    if (!($tool = $DB->get_record('local_scheduleclass', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_clclasses');
    } else {
        $tool->choose = 1;
        $scheduletime = $DB->get_record('local_scheduleclass', array('id' => $id));
        $start = explode(":", $scheduletime->starttime);
        $end = explode(":", $scheduletime->endtime);
        $tool->starthour = $start[0];
        $tool->startmin = $start[1];
        $tool->endhour = $end[0];
        $tool->endmin = $end[1];
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
//
$PAGE->set_url('/local/clclasses/scheduleclass.php', array('id' => $id));
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('manageclasses', 'local_clclasses');
$returnurl = new moodle_url('/local/clclasses/index.php', array('id' => $id));
$heading = ($id > 0) ? get_string('editschedule', 'local_clclasses') : get_string('scheduleclassroom', 'local_clclasses');
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php', array('id' => $id)));
$PAGE->navbar->add($heading);
$PAGE->set_title($strheading);
$editform = new sheduleclass_form(null, array('id' => $id, 'deptid' => $deptid, 'schoid' => $schoid, 'semid' => $semid, 'courseid' => $courseid, 'classid' => $classid));
$availabledate = explode('-', $tool->availableweekdays);
in_array('M', $availabledate) ? $tool->mon = 1 : $tool->mon = 0;
in_array('TU', $availabledate) ? $tool->tue = 1 : $tool->tue = 0;
in_array('W', $availabledate) ? $tool->wed = 1 : $tool->wed = 0;
in_array('TH', $availabledate) ? $tool->thu = 1 : $tool->thu = 0;
in_array('F', $availabledate) ? $tool->fri = 1 : $tool->fri = 0;
in_array('SA', $availabledate) ? $tool->sat = 1 : $tool->sat = 0;
in_array('SU', $availabledate) ? $tool->sun = 1 : $tool->sun = 0;


$editform->set_data($tool);
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
        
        $data->mon ? $availabledates[] = 'M' : null;
        $data->tue ? $availabledates[] = 'TU' : null;
        $data->wed ? $availabledates[] = 'W' : null;
        $data->thu ? $availabledates[] = 'TH' : null;
        $data->fri ? $availabledates[] = 'F' : null;
        $data->sat ? $availabledates[] = 'SA' : null;
        $data->sun ? $availabledates[] = 'SU' : null;
        $availabledates = implode('-', $availabledates);
        $data->availableweekdays = $availabledates;
        $data->starttime = $data->starthour . ':' . $data->startmin;
        $data->endtime = $data->endhour . ':' . $data->endmin;
        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();
        /* Bug-id#255
         * @author hemalatha c arun<hemaltha@eabyas.in>
         * Resolved(enrolling instructor to online course, while assiinging class to instructor)
         */
        $query = "select cl.*,sh.* from {local_scheduleclass} sh 
         JOIN {local_clclasses} cl ON cl.id = sh.classid and cl.onlinecourseid!=0 where sh.classid= $data->classid and cl.online=1 ";
        $onlinecourse_data = $DB->get_record_sql($query);
        if ($onlinecourse_data) {
            $existingdata = $DB->get_record('local_scheduleclass', array('classid' => $data->classid));
            $exists_onlinedata = $DB->get_record('local_clclasses', array('id' => $data->classid));

            if ($existingdata->instructorid == $data->instructorid) {
                
            } else {
                if ($existingdata->instructorid != $data->instructorid) {
                    // unenroll exists instructor from course, when the instructor changed in a class
                    $manual = enrol_get_plugin('manual');
                    $instance = $DB->get_record('enrol', array('courseid' => $exists_onlinedata->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                    $manual->unenrol_user($instance, $existingdata->instructorid);
                    // enroll the updated instructor to online course
                    $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
                    $instance = $DB->get_record('enrol', array('courseid' => $exists_onlinedata->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                    $manual->enrol_user($instance, $data->instructorid, $instructorrole->id);
                }
            }
        }// end of main if
        $DB->update_record('local_scheduleclass', $data);
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
        $data->mon ? $availabledates[] = 'M' : null;
        $data->tue ? $availabledates[] = 'TU' : null;
        $data->wed ? $availabledates[] = 'W' : null;
        $data->thu ? $availabledates[] = 'TH' : null;
        $data->fri ? $availabledates[] = 'F' : null;
        $data->sat ? $availabledates[] = 'SA' : null;
        $data->sun ? $availabledates[] = 'SU' : null;
        $availabledates = implode('-', $availabledates);
        $data->availableweekdays = $availabledates;
        $data->starttime = $data->starthour . ':' . $data->startmin;
        $data->endtime = $data->endhour . ':' . $data->endmin;
        $data->usermodified = $USER->id;
        $data->timecreated = time();
        $data->timemodified = time();
        /* Bug-id#255
         * @author hemalatha c arun<hemaltha@eabyas.in>
         * Resolved(enrolling instructor to online course, while assiinging class to instructor)
         */
        $res = $DB->insert_record('local_scheduleclass', $data);
        if ($res) {
            $query = "select cl.*,sh.* from {local_scheduleclass} sh 
         JOIN {local_clclasses} cl ON cl.id = sh.classid and cl.onlinecourseid!=0 where sh.classid= $data->classid and cl.online=1 ";
            $onlinecourse_data = $DB->get_record_sql($query);
            if ($onlinecourse_data) {
                $manual = enrol_get_plugin('manual');
                $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
                $instance = $DB->get_record('enrol', array('courseid' => $onlinecourse_data->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                $manual->enrol_user($instance, $onlinecourse_data->instructorid, $instructorrole->id);
            }// end of inner if
        } // end of outer if
        $semid = $data->semesterid;
        $classid = $data->classid;
        echo $activesemesterid;
        echo $semid;
        if ($activesemesterid == $semid)
            $returnurl = new moodle_url('/local/clclasses/enroluser.php', array('id' => $classid, 'semid' => $semid));
        else
            $returnurl = new moodle_url('/local/clclasses/index.php');

        $message = get_string('classsuccessschedule', 'local_clclasses');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    /* $data->starttime=$data->starthour.':'.$data->startmin;
      $data->endtime=$data->endhour.':'.$data->endmin;
      $data->usermodified=$USER->id;
      $data->timecreated=time();
      $data->timemodified=time();
      $DB->insert_record('local_scheduleclass',$data); */
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
