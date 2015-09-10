<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/request/lib/lib.php');
require_once($CFG->dirroot . '/local/request/transcript_form.php');
$totalgradepoints = optional_param('totalgradepoints', 0, PARAM_INT);
$totalcredits = optional_param('totalcredits', 0, PARAM_INT);
$systemcontext = context_system::instance();
$request = new requests();
$PAGE->set_url('/local/request/transcript.php');
$PAGE->set_pagelayout('admin');
$conf = new object();
require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('mytranscripts', 'local_request'));
$PAGE->navbar->add(get_string('mytranscripts', 'local_request'), new moodle_url('/local/request/transcript.php'));
$PAGE->navbar->add(get_string('viewtranscript', 'local_request'));
$mform = new transcriptform();
$data = $mform->get_data();
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('viewtranscript', 'local_request'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('transcriptdesc', 'local_request'));
}

$mform->display();
$returnurl = new moodle_url('/local/myacademics/index.php');
if ($mform->is_cancelled()) {
    redirect($returnurl);
}
if ($data) {
    $grades = $request->myacademics_grades($data->programid, $data->semesterid);
    $data = array();
    foreach ($grades as $grade) {
        $result = array();
        $result[] = $DB->get_field('local_clclasses', 'fullname', array('id' => $grade->classid));
        $result[] = $DB->get_field('local_semester', 'fullname', array('id' => $grade->semesterid));
        $result[] = $DB->get_field('local_program', 'fullname', array('id' => $grade->programid));
        $result[] = $grade->coursetotal;
        $result[] = $grade->percentage;
        $result[] = $grade->gradeletter;
        $wgp = $request->total_grade_points($grade->gradepoint, $grade->classid);
        $result[] = $wgp;
        $data[] = $result;
        $totalgradepoints = $totalgradepoints + $wgp;
        $credits = $request->total_grade_credits($grade->classid);
        $totalcredits = $totalcredits + $credits;
    }
    $table = new html_table();
    $table->head = array(
        get_string('course', 'local_cobaltcourses'),
        get_string('semester', 'local_semesters'),
        get_string('program', 'local_programs'),
        get_string('score', 'local_gradesubmission'),
        get_string('percentage', 'local_request'),
        get_string('gradeletter', 'local_gradesubmission'),
        get_string('gradepoint', 'local_request')
    );
    $table->size = array('13%', '13%', '13%', '13%', '13%', '13%', '13%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
    $conf->gpa = $totalgradepoints / $totalcredits;
    echo get_string('gpa', 'local_request', $conf);
}
echo $OUTPUT->footer();
?>