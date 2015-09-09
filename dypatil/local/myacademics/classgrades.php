<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/myacademics/lib.php');
$classid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/myacademics/classgrades.php');
$PAGE->set_pagelayout('admin');
$userid = $USER->id;

require_login();
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_myacademics'));
$PAGE->navbar->add(get_string('viewtranscript', 'local_myacademics'), new moodle_url('/local/myacademics/transcript.php'));
$PAGE->navbar->add(get_string('clsgrade', 'local_myacademics'));
echo $OUTPUT->header();
$name->clsname = $DB->get_field('local_clclasses', 'fullname', array('id' => $classid));

echo $OUTPUT->heading(get_string('clsgrade', 'local_myacademics'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('clsgrade_desc', 'local_myacademics'));
}
$data = array();
$examsql = "SELECT ue.finalgrade,ue.examid,se.examtype,FROM_UNIXTIME(se.opendate,'%D %M, %Y') as examdate,se.grademax FROM {local_user_examgrades} ue,{local_scheduledexams} se WHERE ue.userid={$userid} AND ue.classid={$classid} AND ue.semesterid={$semid} AND ue.examid=se.id";
$examqry = $DB->get_records_sql($examsql);
foreach ($examqry as $exam) {
    $result = array();
    $result[] = $DB->get_field('local_examtypes', 'examtype', array('id' => $exam->examtype));
    $result[] = $exam->examdate;
    $result[] = $exam->grademax;
    $result[] = $exam->finalgrade;
    $data[] = $result;
}
$table = new html_table();
$table->head = array(
    get_string('examname', 'local_gradesubmission'),
    get_string('examdate', 'local_examtype'),
    get_string('max', 'local_myacademics'),
    get_string('score', 'local_gradesubmission')
);
$table->size = array('9%', '9%', '9%', '9%');
$table->align = array('center', 'left', 'left', 'left');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
?>