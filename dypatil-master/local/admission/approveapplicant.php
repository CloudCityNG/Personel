<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE;
require_once($CFG->dirroot . '/local/admission/lib.php');
$PAGE->requires->js('/local/admission/js/validation.js');
$atype = required_param('atype', PARAM_INT);
$schoolid = required_param('schoolid', PARAM_INT);
$programid = required_param('programid', PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
//if (!has_capability('local/programs:manage', $systemcontext)) {
//    print_error('You dont have permissions');
//}
$PAGE->set_url('/local/admission/viewapplicant.php');
$returnurl = new moodle_url('/local/admission/viewapplicant.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('viewapplicants', 'local_admission'));
$PAGE->requires->css('/local/admission/css/style.css');
echo $OUTPUT->header();
$baseurl = new moodle_url('/local/admission/viewapplicant.php?ptype=' . $ptype . '&school=' . $schoolid . '&program=' . $programid . '');
$admission = cobalt_admission::get_instance();
$hierarchy = new hierarchy();
$currenttab = 'viewapplicant';
$admission->report_tabs($currenttab);
echo $OUTPUT->heading(get_string('viewapplicants', 'local_admission'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('approveapplicants', 'local_admission'));
}
$userlist = $_POST['check_list'];
$curculum = $admission->cobalt_admission_curculum($schoolid, $programid);
echo '<form action="#" method="POST" onsubmit="return checkcurculum()">';
echo '<label>Curriculum : </label>';
echo '<select name="curs" id="cur">';
foreach ($curculum as $key => $cur) {
    echo '<option value="' . $key . '">' . $cur . '</option>';
}
echo '</select>';
foreach ($userlist as $u) {
    $app = $DB->get_record('local_admission', array('id' => $u));
    $user = array();
    echo '<input type="hidden" name="check_list[]" value="' . $app->id . '">';
    $user[] = html_writer::tag('a', $app->firstname . " " . $app->lastname, array('href' => '' . $CFG->wwwroot . '/local/admission/view.php?id=' . $app->id . ''));
    $user[] = $DB->get_field('local_school', 'fullname', array('id' => $app->schoolid));
    $user[] = $DB->get_field('local_program', 'fullname', array('id' => $app->programid));

    if ($app->typeofprogram == 1) {
        $app->typeofprogram = get_string('undergard', 'local_admission');
    } elseif ($app->typeofprogram == 2) {
        $app->typeofprogram = get_string('grad', 'local_admission');
    } else {
        $app->typeofprogram = get_string('postgrad', 'local_admission');
    }
    $user[] = $app->typeofprogram;
    $data[] = $user;
}
$table = new html_table();
$table->head = array(
    get_string('name', 'local_admission'),
    get_string('schoolname', 'local_collegestructure'),
    get_string('programname', 'local_programs'),
    get_string('programtype', 'local_programs')
);
$table->size = array('9%', '9%', '9%', '9%');
$table->align = array('left', 'left', 'left', 'left');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo '<input type="hidden" name="schoolid" value="' . $schoolid . '">';
echo '<input type="hidden" name="programid" value="' . $programid . '">';
echo '<input type="hidden" name="atype" value="' . $atype . '">';
echo '<input type="submit" value="Accept" >';
echo '</form>';
if (isset($_POST['curs'])) {
    $userlist = $_POST['check_list'];
    $curid = $_POST['curs'];
    foreach ($userlist as $u) {
        $username = generateusername($u);
        $password = generatePassword();
        $s = $admission->cobalt_admission_info($u, $curid, $username, $password);
        $details = $DB->get_record('local_admission', array('id' => $u));
        $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
        $programname = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
        $url = $CFG->wwwroot;
        $user = $details->email;
        $from = $USER->email;
        $subject = get_string('approval_of_admission', 'local_admission');
        if ($details->previousstudent == 1) {
            $body = 'Congratulations!Your application for school ' . $schoolname . ' under program ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' username:' . $username . ' and password:' . $password . ' your serviceid is' . $s . ' ';
        } else {
            $body = 'Congratulations!Your application for school ' . $schoolname . ' under program ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' You can login with previous login details
your serviceid is ' . $s . ' ';
        }
        mail($user, $subject, $body, $from);
    }
    $message = get_string('applicantsuccess', 'local_admission');
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->footer();
?>