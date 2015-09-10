<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');
require_once($CFG->dirroot . '/local/clclasses/enroluser_form.php');
$hierarchy = new hierarchy();
$conf = new object();
$semclass = new schoolclasses();
$id = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$activesemesterid = optional_param('activesemid', 0, PARAM_INT);


$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/clclasses/enroluser.php');
$PAGE->set_heading(get_string('pluginname', 'local_clclasses'));
$PAGE->navbar->add(get_string('manageclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('enrol_user', 'local_clclasses'));
echo $OUTPUT->header();

// -------------Edited by hema----------------------------------------
$enrolluser_cap = array('local/clclasses:manage', 'local/clclasses:enrolluser');
if (!has_any_capability($enrolluser_cap, $systemcontext)) {
    print_error('You dont have permissions');
}
//

$class = $DB->get_field('local_clclasses', 'fullname', array('id' => $id));
echo $OUTPUT->heading(get_string('class_enroll', 'local_clclasses', $class));
/* Tabs comes here */
$currenttab = "enroll";
$semclass->print_scheduletabs($currenttab, 0, 1, 0);
/* Description comes here */
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('enrol_user_desc', 'local_clclasses'));
}
$mform = new enroluser_form(null, array('id' => $id, 'semid' => $semid, 'activesemid'=>$activesemesterid));
$data = $mform->get_data();
if ($mform->is_cancelled()) {
    $returnurl = new moodle_url('/local/clclasses/index.php');
    redirect($returnurl);
}
if ($data) {
    $noofuser = sizeof($data->susers);
    $mform = new enroluser_form('enroluser.php', array('susers' => $data->susers, 'id' => $data->id, 'semid' => $data->semid));

    if ($noofuser == 0) {
        echo get_string('nouseravail', 'local_clclasses');
    } else {
        /* Bug report #292  -  Classes>Limit>Enroll- Unlimited
         * @author hemalatha c arun <hemalatha@eabyas.in> 
         * Resolved- Added condition not to enroll student class, when class limit is exceeded.
         */
        $class_limitoverflow = 0;
        $classlimit = $DB->get_record('local_clclasses', array('id' => $data->id));
        $countsofuser_enrolled = $DB->count_records('local_user_clclasses', array('classid' => $data->id));

        if ($countsofuser_enrolled < $classlimit->classlimit) {
//-------------------------------------------------------------------------
            foreach ($data->susers as $userid) {
                $record = new Stdclass();
                $record->userid = $userid;
                $record->classid = $data->id;
                $currentclass = $DB->get_record('local_clclasses', array('id' => $data->id), '*', MUST_EXIST);
                if ($currentclass->online == 1 && $currentclass->onlinecourseid != 0) {
                    $manual = enrol_get_plugin('manual');
                    $studentrole = $DB->get_record('role', array('shortname' => 'student'));
                    $instance = $DB->get_record('enrol', array('courseid' => $currentclass->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                    $manual->enrol_user($instance, $userid, $studentrole->id);
                }
                $record->semesterid = $data->semid;
                $record->programid = 0;
                $record->studentapproval = 1;
                $record->registrarapproval = 1;
                $record->timecreated = time();
                $record->timemodified = time();
                $record->usermodified = $USER->id;
                $record->ecourseid = 0;
                $DB->insert_record('local_user_clclasses', $record);
                $semuser = $DB->get_record('local_user_semester', array('userid' => $userid, 'semesterid' => $data->semid));
                if (empty($semuser)) {
                    $sem = new Stdclass();
                    $sem->userid = $userid;
                    $sem->semesterid = $data->semid;
                    $sem->registrarapproval = 1;
                    $sem->timecreated = time();
                    $sem->timemodified = time();
                    $sem->usermodified = $USER->id;
                    $sem->programid = 0;
                    $sem->curriculumid = 0;
                    $DB->insert_record('local_user_semester', $sem);
                } // end of if
            } // end of for each
        }// end of of if
        else
            $class_limitoverflow = 1;
    }// end  of else
    $conf->classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $data->id));
    /* echo '<div class="notifysuccess">';
      echo get_string('enrollsuccess','local_clclasses',$conf);
      echo '</div>'; */
    $returnurl = new moodle_url('/local/clclasses/enroluser.php', array('id' => $data->id, 'semid' => $data->semid, 'activesemid'=>$activesemesterid));
    if ($class_limitoverflow == 0) {
        $message = get_string('enrollsuccess', 'local_clclasses', $conf);
        $style = array('style' => 'notifysuccess');
    } else {
        $message = get_string('classlimitexceded', 'local_clclasses', $conf);
        $style = array('style' => 'notifyproblem');
    }
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
$mform->display();
echo $OUTPUT->footer();
?>
