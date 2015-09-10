<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;

require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$flag = optional_param('flag', 0, PARAM_INT);
$schoolid = optional_param('sid', 0, PARAM_INT);
$programid = optional_param('pid', 0, PARAM_INT);
$ptype = optional_param('ptype', 0, PARAM_INT);
$school = optional_param('schoolid', 0, PARAM_INT);
$program = optional_param('programid', 0, PARAM_INT);
$atype = optional_param('typeofapplication', 0, PARAM_INT);
$stype = optional_param('typeofstudent', 0, PARAM_INT);
$pgmtype = optional_param('typeofprogram', 0, PARAM_INT);
$student = optional_param('previousstudent', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('basic_title', 'local_admission'));
$PAGE->set_url('/local/admission/basic.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/index.php'));
$PAGE->navbar->add(get_string('apply', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
echo '<div class="admission">';
echo $OUTPUT->heading(get_string('apply', 'local_admission'));
if ($schoolid > 0) {
    $conf->school = $DB->get_field('local_school', 'fullname', array('id' => $schoolid));
    $conf->pgm = $DB->get_field('local_program', 'fullname', array('id' => $programid));
    $conf->today = date('d-M-Y');
    if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('applydescr', 'local_admission'));
    }
} elseif ($atype > 0 && $stype > 0) {
    $conf->school = $DB->get_field('local_school', 'fullname', array('id' => $school));
    $conf->pgm = $DB->get_field('local_program', 'fullname', array('id' => $program));
    $conf->today = date('d-M-Y');
    if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('local_collegestructure', 'local_admission', $conf));
    }
} else {
    if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('applydescr', 'local_admission'));
    }
}

$returnurl = new moodle_url('/local/admission/index.php');
if ($flag == 0) {
    $mform = new apply_form(null, array('schoolid' => $schoolid, 'programid' => $programid, 'ptype' => $ptype));
    $data = $mform->get_data();
    echo '<div id="first">';
    $mform->display();
    echo '</div>';
    if ($mform->is_cancelled()) {
        redirect($returnurl);
    }
    if ($data) {
        $flag = 1;
    }
}

if ($flag == 1) {
    $PAGE->requires->js('/local/admission/js/first.js');
    $mform = new apply_form(null);
    $data = $mform->get_data();
    if (!empty($data->schoolid) && $data->previousstudent == 1) {

        $apply = new admission_form(new moodle_url('/local/admission/basic.php', array('flag' => 1)), array('schoolid' => $data->schoolid, 'programid' => $data->programid, 'ptype' => $data->typeofprogram, 'atype' => $data->typeofapplication, 'stype' => $data->typeofstudent, 'previousstudent' => $data->previousstudent));
    } elseif (empty($data->schoolid) && $student == 1) {
        $apply = new admission_form(new moodle_url('/local/admission/basic.php', array('flag' => 1)), array('schoolid' => $school, 'programid' => $program, 'ptype' => $pgmtype, 'atype' => $atype, 'stype' => $stype, 'previousstudent' => $student));
    } else {
        $apply = new readmission_form(new moodle_url('/local/admission/basic.php', array('flag' => 1)), array('schoolid' => $data->schoolid, 'programid' => $data->programid, 'ptype' => $data->typeofprogram, 'atype' => $data->typeofapplication, 'stype' => $data->typeofstudent, 'previousstudent' => $data->previousstudent));
    }
    $datas = $apply->get_data();
    $apply->display();
    if ($datas) {
        if ($datas->previousstudent == 1) {
            if ($datas->same == 1) {
                $datas->pcountry = $datas->currentcountry;
                $datas->permanenthno = $datas->currenthno;
                $datas->state = $datas->region;
                $datas->city = $datas->town;
                $datas->pincode = $datas->pob;
                $datas->contactname = $datas->fathername;
            }
            $admission = $DB->insert_record('local_admission', $datas);
            @ mkdir("uploads/$admission");
            $target_path = "uploads/$admission/";
            @ $target_path = $target_path . basename($_FILES['uploadfile']['name']);
            @ $l = move_uploaded_file($_FILES['uploadfile']['tmp_name'], $target_path);
        } else {
            $userid = $DB->get_record('local_userdata', array('serviceid' => $datas->serviceid));
            $previous = $DB->get_record('local_admission', array('id' => $userid->applicantid));
            $datas->firstname = $previous->firstname;
            $datas->middlename = $previous->middlename;
            $datas->lastname = $previous->lastname;
            $datas->gender = $previous->gender;
            $datas->dob = $previous->dob;
            $datas->birthcountry = $previous->birthcountry;
            $datas->birthplace = $previous->birthplace;
            $datas->fathername = $previous->fathername;
            $datas->pob = $previous->pob;
            $datas->region = $previous->region;
            $datas->town = $previous->town;
            $datas->currenthno = $previous->currenthno;
            $datas->currentcountry = $previous->currentcountry;
            $datas->phone = $previous->phone;
            $datas->email = $previous->email;
            $datas->howlong = $previous->howlong;
            $datas->same = $previous->same;
            $datas->pcountry = $previous->pcountry;
            $datas->permanenthno = $previous->permanenthno;
            $datas->state = $previous->state;
            $datas->city = $previous->city;
            $datas->pincode = $previous->pincode;
            $datas->contactname = $previous->contactname;
            $datas->primaryschoolname = $previous->primaryschoolname;
            $datas->primaryyear = $previous->primaryyear;
            $datas->primaryscore = $previous->primaryscore;
            $datas->ugin = $previous->ugin;
            $datas->ugname = $previous->ugname;
            $datas->ugyear = $previous->ugyear;
            $datas->ugscore = $previous->ugscore;
            $datas->graduatein = $previous->graduatein;
            $datas->graduatein = $previous->graduatein;
            $datas->graduateyear = $previous->graduateyear;
            $datas->graduatescore = $previous->graduatescore;
            $datas->examname = $previous->examname;
            $datas->hallticketno = $previous->hallticketno;
            $datas->score = $previous->score;
            $datas->noofmonths = $previous->noofmonths;
            $datas->reason = $previous->reason;
            $datas->description = $previous->description;
            $datas->primaryplace = $previous->primaryplace;
            $datas->ugplace = $previous->ugplace;
            $datas->graduateplace = $previous->graduateplace;
            $admission = $DB->insert_record('local_admission', $datas);
        }

        $update = new Stdclass();
        $update->id = $admission;
        $program = $DB->get_field('local_program', 'shortname', array('id' => $datas->programid));
        $random = random_string(5);
        $update->applicationid = $program . $admission . $random;
        $applicationid = $DB->update_record('local_admission', $update);
        $details = $DB->get_record('local_admission', array('id' => $admission));
        $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
        $programname = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
        $url = $CFG->wwwroot;
        $user = $details->email;
        $from = 'admin@cobaltlms.com';
        $subject = 'Application submission confirmation';
        $body = 'You have applied successfully for "' . $programname . '" program under "' . $schoolname . '". Please wait untill Registrar Office confirms it. 
Application Id : ' . $update->applicationid . ' 
You can track your application status by using this link : ' . $url . ' ';
        mail($user, $subject, $body, $from);
        $conf->success = $program . $admission . $random;
        $conf->program = $programname;
        $returnurl = new moodle_url('/local/admission/index.php');
        $message = get_string('success', 'local_admission', $conf);
        $style = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $style);
    }
}
echo '</div>';
echo $OUTPUT->footer();
?>