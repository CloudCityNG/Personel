<?php

/**
 * script for downloading courses
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
$format = optional_param('format', '', PARAM_ALPHA);
// $id=optional_param('id',-1, PARAM_INT); 
global $DB;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/programs/download_all.php');
require_login();
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
$PAGE->navbar->add(get_string('dd', 'local_programs'));
// admin_externalpage_setup('userbulk');

if ($format) {
    $fields = array(
        'shortname' => 'shortname',
        'fullname' => 'fullname',
        'schoolname' => 'schoolname',
        'summary' => 'summary',
        'type' => 'type',
        'duration' => 'duration'
    );
    switch ($format) {
        case 'xls' : user_download_xls($fields);
    }
    die;
}
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dd', 'local_programs'));
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

function user_download_xls($fields) {
    global $CFG, $DB;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename(get_string('program', 'local_programs') . '.xls');
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
    $sheetrow = 1;
    foreach ($schoollist as $school) {
        $programs = $DB->get_records('local_program', array('schoolid' => $school->id));
        foreach ($programs as $program) {
            $post = new stdclass();
            $post->shortname = $program->shortname;
            $post->fullname = $program->fullname;
            $departmentname = $DB->get_field('local_department', 'fullname', array('id' => $program->departmentid));
            $post->departmentname = $departmentname;
            $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $program->schoolid));
            $post->schoolname = $schoolname;
            $post->summary = $program->description;
            $post->type = ($program->type == 0) ? 'UnderGraduate' : 'PostGraduate';
            $post->duration = $program->duration;
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
