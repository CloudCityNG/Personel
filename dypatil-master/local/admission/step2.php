<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE, $OUTPUT;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$PAGE->requires->js('/local/admission/js/validation.js');
$id = optional_param('id', 0, PARAM_INT);
$cid = optional_param('curs', 0, PARAM_INT);
$flag = optional_param('flag', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext =context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/admission/step3.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('acceptapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$currenttab = 'transferaccept';
$admission = cobalt_admission::get_instance();
$admission->applicant_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('step2_desc', 'local_admission'));
}
if ($flag == 0) {
    $details = $DB->get_record('local_admission', array('id' => $id));
    $info = $DB->get_record('local_users', array('applicantid' => $id));
    if (empty($info)) {
        $username = generateusername($id);
        $password = generatePassword();
        $s = tansferaccept($id, $cid, $username, $password);
        $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
        $programname = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
        $url = $CFG->wwwroot;
        $users = $details->email;
        $from = $USER->email;
        $subject = 'Approval of admission Application';
        if ($details->previousstudent == 1) {
            $body = 'Congratulations!Your application for school ' . $schoolname . ' under program ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' username:' . $username . ' and password:' . $password . '
 your serviceid is ' . $s . ' ';
        } else {
            $body = 'Congratulations!Your application for school ' . $schoolname . ' under program ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' you can login withprevious login details
your serviceid is ' . $s . ' ';
        }
        mail($users, $subject, $body, $from);
    }
}
$cur = get_cur($id, $cid);
echo '<div class="selfilterposition" style="margin-left:190px;" >';
$select = new single_select(new moodle_url('/local/admission/step2.php?id=' . $id . '&flag=' . $flag . ''), 'curs', $cur, $cid, null);
$select->set_label(get_string('curriculum', 'local_curriculum'));
$select->attributes['disabled'] = true;
echo $OUTPUT->render($select);
echo html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall', 'id' => 'icon', 'onclick' => 'crculumenable()', 'style' => 'margin-top: -60px;
margin-left: 242px;'));
echo '</div>';
$mform = new transfer_applicant_approve(null, array('curs' => $cid, 'id' => $id, 'flag' => $flag));
$data = $mform->get_data();
$mform->display();
if ($data) {
    $grades = transfer_score($data->courseid, $data->grade, $data->id);
    $record = new stdclass();
    $record->id = $data->id;
    $record->status = 1;
    $localadmission = $DB->update_record('local_admission', $record);
    $returnurl = new moodle_url('/local/admission/transferapplicant.php');
    redirect($returnurl);
}
if ($mform->is_cancelled()) {
    $returnurl = new moodle_url('/local/admission/transferapplicant.php');
}
echo $OUTPUT->footer();
?>