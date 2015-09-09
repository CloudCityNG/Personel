<?php

/**
 * script for downloading courses
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('upload_grades_lib.php');
require_once('lib.php');
$format = optional_param('format', '', PARAM_ALPHA);
// $id=optional_param('id',-1, PARAM_INT); 
global $DB, $USER;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();

$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/local/programs/download_all.php');
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
// $PAGE->navbar->add(get_string('crd', 'local_courseregistration'), new moodle_url('/local/courseregistration/details.php'));
$PAGE->navbar->add(get_string('dd', 'local_programs'));
// admin_externalpage_setup('userbulk');

if ($format) {
    $fields = array(
        'schoolname' => 'schoolname',
        'semestername' => 'semestername',
        'classname' => 'classname',
        'studentname' => 'studentname'
    );

    $ltypes = $DB->get_records_sql("select * from {local_lecturetype}");
    foreach ($ltypes as $ltype) {
        $list[] = $ltype->id;
    }
    $llist = implode(',', $list);

    $PRF_FIELDS = array();
    if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {
        $schools = get_inst_assignedschools();
        foreach ($schools as $school) {
            $schoollist[] = $school->id;
        }
        $schoollist_string = implode(',', $schoollist);
        $today = date('Y-m-d');
        $mysql = "SELECT sem.* FROM {local_semester} AS sem
							JOIN {local_school_semester} AS scl
							ON scl.semesterid = sem.id";
        $where = " WHERE scl.schoolid IN ($schoollist_string)";
        $where .= " AND  '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
        $group = " GROUP BY scl.semesterid";
        $sems = $DB->get_records_sql($mysql . $where . $group);
        foreach ($sems as $sem) {
            $sem_lists[] = $sem->id;
        }
        $sem_list = implode(',', $sem_lists);
        $class_insts = $DB->get_records_sql('select * from {local_scheduleclass} where instructorid =' . $USER->id . ' and semesterid in (' . $sem_list . ') ');
        foreach ($class_insts as $class_inst) {
            $ins_lists[] = $class_inst->classid;
        }
        $ins_list = implode(',', $ins_lists);
        $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt, {local_examtypes}  type, {local_clclasses} cs where  exam.classid in ($ins_list) AND type.id = exam.examtype and lt.id =exam.lecturetype ";
        $exams = $DB->get_records_sql($sql);
        // print_object($exams);exit;
    } else {
        $hier = new hierarchy();
        $schools = $hier->get_assignedschools();
        if (is_siteadmin()) {
            $schools = $hier->get_school_items();
        }
        foreach ($schools as $school) {
            $schoollist[] = $school->id;
        }
        $schoollist_string = implode(',', $schoollist);
        $today = date('Y-m-d');
        $mysql = "SELECT sem.* FROM {local_semester} AS sem
							JOIN {local_school_semester} AS scl
							ON scl.semesterid = sem.id";
        $where = " WHERE scl.schoolid IN ($schoollist_string)";
        $where .= " AND  '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
        $group = " GROUP BY scl.semesterid";
        $sems = $DB->get_records_sql($mysql . $where . $group);
        foreach ($sems as $sem) {
            $sem_lists[] = $sem->id;
        }
        $sem_list = implode(',', $sem_lists);
        $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt, {local_examtypes}  type, {local_clclasses} cs where  exam.classid= cs.id AND exam.schoolid in ($schoollist_string) AND exam.semesterid in ($sem_list)  AND type.id = exam.examtype AND lt.id =exam.lecturetype ";
    }
// $sql = "SELECT  exam.id as eid, type.examtype as examtype, exam.classid as csid, lt.lecturetype as lecturetype, lt.id as lid, exam.schoolid FROM {local_scheduledexams}  exam,  {local_lecturetype}  lt ,{local_examtypes}  type, {local_clclasses} cs where  exam.classid= cs.id AND type.schoolid = exam.schoolid and  exam.lecturetype in ($llist) and  type.id = exam.examtype and lt.id =exam.lecturetype ";
    $exams = $DB->get_records_sql($sql);
    foreach ($exams as $exam) {
        $exam->examtype = strtolower($exam->examtype);
        $exam->lecturetype = strtolower($exam->lecturetype);
        $cname = $DB->get_field('local_clclasses', 'fullname', array('id' => $exam->csid));
        $cname = strtolower($cname);
        $fields[$exam->csid . ':' . $exam->eid . ':' . $exam->lid] = $cname . ':' . $exam->examtype . ':' . $exam->lecturetype;
    }
    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

echo $OUTPUT->header();
// Current tab
$currenttab = 'download';
//adding tabs
createtabview($currenttab);
echo $OUTPUT->heading(get_string('dd', 'local_departments'));
echo $OUTPUT->footer();

// function user_download_xls($fields) {
// global $CFG,$DB;
// require_once("$CFG->libdir/excellib.class.php");
// require_once($CFG->dirroot.'/user/profile/lib.php');
// $filename = clean_filename(get_string('department', 'local_departments').'.xls');
// $workbook = new MoodleExcelWorkbook('-');
// $workbook->send($filename);
// $worksheet = array();
// $worksheet[0] = $workbook->add_worksheet('');
// $col = 0;
// foreach ($fields as $fieldname) {
// $worksheet[0]->write(0, $col, $fieldname);
// $col++;
// }
// $workbook->close();
// die;
// }

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename('Grades');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
