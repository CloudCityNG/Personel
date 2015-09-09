<?php

/**
 * script for downloading courses
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
$format = optional_param('format', '', PARAM_ALPHA);
global $DB;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/local/cobaltcourses/download_all.php');
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('dd', 'local_cobaltcourses'));
if ($format) {
    $fields = array(
        'shortname' => 'shortname',
        'fullname' => 'fullname',
        'departmentname' => 'courselibraryname',
        'schoolname' => 'organizationname',
        'summary' => 'summary',
        'coursetype' => 'coursetype',
        'credithours' => 'credithours',
        'coursecost' => 'coursecost'
    );
    switch ($format) {
        case 'xls' : user_download_xls($fields);
    }
    die;
}

echo $OUTPUT->header();
/* ---Current tab--- */
$currenttab = 'download';
/* ---adding tabs--- */
createtabview($currenttab);
echo $OUTPUT->heading(get_string('dd', 'local_cobaltcourses'));
echo $OUTPUT->footer();

function user_download_xls($fields) {

    global $CFG, $DB;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename(get_string('course', 'local_cobaltcourses') . '.xls');
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }


    $hierarchy = new hierarchy();
    $schoollist = $hierarchy->get_assignedschools();
    if (is_siteadmin()) {
        $schoollist = $hierarchy->get_school_items();
    }

    $sheetrow = 1;
    foreach ($schoollist as $school) {
        $courses = $DB->get_records('local_cobaltcourses', array('schoolid' => $school->id));
        foreach ($courses as $course) {
            $post = new stdclass();

            $post->fullname = $course->fullname;
            $post->shortname = $course->shortname;
            $departmentname = $DB->get_field('local_department', 'fullname', array('id' => $course->departmentid));
            $post->courselibraryname = $departmentname;
            $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $course->schoolid));
            $post->organizationname = $schoolname;
            $post->summary = $course->summary;
            $post->coursetype = ($course->coursetype == 0) ? 'General' : 'Elective';
            $post->credithours = $course->credithours;
            $post->coursecost = $course->coursecost;
            $col = 0;
            foreach ($fields as $fieldname) {
                $worksheet[0]->write($sheetrow, $col, $post->$fieldname);
                $col++;
            }
            $sheetrow++;
        }
    }

    $workbook->close();
    die;
}
