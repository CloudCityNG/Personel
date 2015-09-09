<?php

require_once(dirname(__FILE__) . '/../../config.php');
$var = optional_param('x', 0, PARAM_INT);
$PAGE->set_pagelayout('admin');
require_once('../../local/scheduleexam/lib.php');
$PAGE->set_url('/local/gradesubmission/gradesview.php');
$systemcontext =context_system::instance();
$PAGE->set_context($systemcontext);
require_login();
if (!isloggedin() || isguestuser()) {
    print_error('You dont have permissions');
}
$strheading = get_string('view');
$PAGE->set_heading($strheading);
$PAGE->navbar->add($strheading);
//$PAGE->set_title($strheading);
$data = array();
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$vals = $DB->get_records_sql("select * from {local_user_examgrades} where userid = {$USER->id}");
$data = array();
foreach ($vals as $val) {
    $list = array();
    $source = $val->source;
    $program = $DB->get_field('local_program', 'fullname', array('id' => $val->programid));
    $list[] = $program;
    $semester = $DB->get_field('local_semester', 'fullname', array('id' => $val->semesterid));
    $list[] = $semester;
    $class = $DB->get_field('local_clclasses', 'fullname', array('id' => $val->classid));
    $list[] = $class;
    $list[] = '';
    if ($source == 'offline') {
        $exams = $DB->get_record('local_scheduledexams', array('examtype' => $val->examid, 'schoolid' => $val->schoolid, 'programid' => $val->programid));
        if (!empty($exams)) {
            $examtype = $DB->get_field('local_examtypes', 'examtype', array('id' => $exams->examtype));
            $list[] = $examtype;
        } else {
            $list[] = '';
        }
    }
    if ($source == 'online') {
        $exams = $DB->get_field('grade_items', 'itemname', array('id' => $val->examid));
        $list[] = $exams;
    }
    $list[] = $val->finalgrade;
    $gradeletter = $DB->get_record_sql("select * from {local_gradeletters}
                                        where schoolid = {$val->schoolid}
                                        and programid = {$val->programid}
                                        and {$val->finalgrade} between markfrom and markto");


    if (!empty($gradeletter)) {
        $list[] = $gradeletter->letter;
        $list[] = $gradeletter->gradepoint;
    } else {
        $list[] = '-';
        $list[] = '-';
    }
    $data[] = $list;
}
if (!empty($data)) {
    $PAGE->requires->js('/local/gradesubmission/gradesview.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
}
echo $OUTPUT->box_start('generalbox');
$table = new html_table();
$table->id = 'gradesview';
$table->head = array(
    get_string('program', 'local_programs'),
    get_string('semester', 'local_semesters'),
    get_string('class', 'local_clclasses'),
    get_string('course', 'local_cobaltcourses'),
    get_string('examname', 'local_gradesubmission'),
    get_string('finalgrade', 'local_gradesubmission'),
    get_string('gradeletter', 'local_gradesubmission'),
    get_string('gradepoints', 'local_gradesubmission')
);
$table->size = array('15%', '15%', '15%', '15%', '10%', '10%', '7%', '10%');
$table->align = array('center', 'center', 'center', 'center', 'center', 'center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (!isset($data))
    echo get_string('no_records', 'local_request');
echo $OUTPUT->box_end();
echo $OUTPUT->footer();
?>