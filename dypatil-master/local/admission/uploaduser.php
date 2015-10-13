<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER, $OUTPUT;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/admission/uploaduser.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('uploaduser_title', 'local_admission'));
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//if (!has_capability('local/programs:manage', $systemcontext)) {
//    print_error('You dont have permissions');
//}
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('uploaduser', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$hierarchy = new hierarchy();
$conf = new object();
$admission = cobalt_admission::get_instance();
$currenttab = 'uploaduser';
$admission->report_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('uploaduserdes', 'local_admission'));
}
$mform = new uploaduser_form();
$data = $mform->get_data();
//print_object($data); 
$mform->display();
if ($data) {
    if ($data->same == 1) {
        $data->pcountry = $data->currentcountry;
        $data->permanenthno = $data->currenthno;  
        $data->state = $data->region;   
        $data->city = $data->town;
        $data->pincode = $data->pob;
        $data->contactname = $data->fathername;
    }
    $data->schoolid = $DB->get_field('local_program', 'schoolid', array('id' => $data->programid));
    $applicant = $DB->insert_record('local_admission', $data);
    $update = new Stdclass();
    $update->id = $applicant;
    $program = $DB->get_field('local_program', 'shortname', array('id' => $data->programid));
    $random = random_string(5);
    $update->applicationid = $program . $applicant . $random;
    $applicationid = $DB->update_record('local_admission', $update);
    //***************comment added by anil-start here***************//
        //@ mkdir("uploads/$applicant");
        //$target_path = "uploads/$applicant/";
        //@ $target_path = $target_path . basename($_FILES['uploadfile']['name']);
        //@ $l = move_uploaded_file($_FILES['uploadfile']['tmp_name'], $target_path);
    //***************comment added by anil-ended here***************//
    $username = generateusername($applicant);
    $password = generatePassword();
    $service = $admission->cobalt_admission_info($applicant, $data->curriculumid, $username, $password);
    $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $data->schoolid));
    $programname = $DB->get_field('local_program', 'fullname', array('id' => $data->programid));
    $url = 'localhost/cv1';
    $users = $data->email;
    $from = $USER->email;
    $subject = 'Approval of admission Application';
    $body = 'Congratulations!Your application for school ' . $schoolname . ' under program ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' username:' . $username . ' and password:' . $password . '
your serviceid is ' . $service . ' and application id ' . $update->applicationid . '';
    mail($users, $subject, $body, $from);
    $returnurl = new moodle_url('/local/admission/viewapplicant.php');
    $conf->approve = $data->firstname;
    $message = get_string('approve', 'local_admission', $conf);
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->footer();
?>
