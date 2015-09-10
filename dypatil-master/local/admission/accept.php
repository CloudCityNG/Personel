<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $PAGE;
require_once($CFG->dirroot . '/local/admission/lib.php');
$PAGE->requires->js('/local/admission/js/validation.js');
$id = optional_param('id', 0, PARAM_INT);
$hierarchy = new hierarchy();
$conf = new object();
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/admission/accept.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('acceptapplicant', 'local_admission'));
echo $OUTPUT->header();
$currenttab = 'accept';
$admission = cobalt_admission::get_instance();
$admission->applicant_tabs($currenttab);
echo $OUTPUT->heading(get_string('acceptapplicant', 'local_admission'));

$app = $DB->get_record('local_admission', array('id' => $id));
$curculum = $admission->cobalt_admission_curculum($app->schoolid, $app->programid);
echo '<form action="#" method="POST" onsubmit="return checkcurculum()">';
echo '<label>' . get_string('curriculum', 'local_curriculum') . ' : </label>';
echo '<select name="curs" id="cur">';
foreach ($curculum as $key => $cur) {
    echo '<option value="' . $key . '">' . $cur . '</option>';
}
echo '</select>';
$user = array();
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
echo '<input type="hidden" name="id" value="' . $id . '">';
echo '<input type="submit" value="Accept" >';
echo '</form>';
if (isset($_POST['curs'])) {
    $curid = $_POST['curs'];
    $user = $_POST['id'];
    $previous = $DB->get_field('local_admission', 'previousstudent', array('id' => $user));
    $username = generateusername($user);
    $password = generatePassword();
    $s = $admission->cobalt_admission_info($user, $curid, $username, $password);
    $details = $DB->get_record('local_admission', array('id' => $user));
    $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $details->schoolid));
    $programname = $DB->get_field('local_program', 'fullname', array('id' => $details->programid));
    $url = $CFG->wwwroot;
    $users = $details->email;
    $from = $USER->email;
    $subject = get_string('approval_of_admission', 'local_admission');
    if ($previous == 1) {
        $body = 'Congratulations!Your application for ' . get_string('schoolid', 'local_collegestructure') . ' ' . $schoolname . ' under ' . get_string('program', 'local_programs') . ' ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' username:' . $username . ' and password:' . $password . '
your serviceid is ' . $s . ' ';
    } else {
        $body = 'Congratulations!Your application for ' . get_string('schoolid', 'local_collegestructure') . ' ' . $schoolname . ' under ' . get_string('program', 'local_programs') . ' ' . $programname . '  was approved by our registrar please login to the following url for further process ' . $url . ' You can login with previous login details
your serviceid is ' . $s . ' ';
    }
    mail($users, $subject, $body, $from);
    $returnurl = new moodle_url('/local/admission/viewapplicant.php');
    $conf->approve = $details->firstname;
    $message = get_string('approve', 'local_admission', $conf);
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
}
echo $OUTPUT->footer();
?>