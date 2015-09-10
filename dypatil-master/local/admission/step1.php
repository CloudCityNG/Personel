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
$PAGE->set_title(get_string('transferapplicant_title', 'local_admission'));
require_login();
$PAGE->set_url('/local/admission/step1.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('acceptapplicant', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$currenttab = 'transferapplicant';
$admission = cobalt_admission::get_instance();
$admission->applicant_tabs($currenttab);
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('step1_desc', 'local_admission'));
}
$app = $DB->get_record('local_admission', array('id' => $id));
$curculum = $admission->cobalt_admission_curculum($app->schoolid, $app->programid);
$records = $DB->get_record('local_users', array('applicantid' => $id));
if (empty($records)) {
    echo '<form action="step2.php" method="GET" onSubmit="return checkcurculum()">';
    echo '<label>' . get_string('curriculum', 'local_curriculum') . ' : </label>';
    echo '<select name="curs" id="cur">';
    foreach ($curculum as $key => $cur) {
        echo '<option value="' . $key . '">' . $cur . '</option>';
    }
    echo '</select>';
    echo '<br/><br/>';
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
        get_string('programtype', 'local_programs'));
    $table->size = array('9%', '9%', '9%', '9%');
    $table->align = array('left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
    echo '<input type="hidden" name="id" value="' . $id . '">';
    echo '<input type="hidden" name="flag" value="0">';
    echo '<input type="submit" value="Accept" >';
    echo '</form>';
} else {
    $userid = $DB->get_field('local_users', 'userid', array('applicantid' => $id));
    $currid = $DB->get_field('local_userdata', 'curriculumid', array('userid' => $userid));
    $returnurl = new moodle_url('/local/admission/step2.php?id=' . $id . '&curs=' . $currid . '&flag=1');
    redirect($returnurl);
}
echo $OUTPUT->footer();
?>