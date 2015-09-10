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
$PAGE->set_url('/local/departments/download_all.php');
require_login();
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('dd', 'local_departments'));
if ($format) {
    $fields = array(
        'shortname' => 'shortname',
        'fullname' => 'fullname',
        'schoolname' => 'schoolname',
        'summary' => 'summary'
    );
    switch ($format) {
        case 'xls' : user_download_xls($fields);
    }
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dd', 'local_departments'));
echo $OUTPUT->box_start();
echo '<ul>';
echo '<li><a href="download_all.php?format=xls">' . get_string('downloadexcel') . '</a></li>';
echo '</ul>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

function user_download_xls($fields) {

    global $CFG, $DB;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename(get_string('department', 'local_departments') . '.xls');
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
    /*   Bug report #260 
     * Edited by hemalatha c arun <hemalatha@eabyas.in>
     * resolved- If loggedin user is admin, downloading all the department   
     */
    if (is_siteadmin()) {
        $sql = "SELECT distinct(s.id),s.* FROM {local_school} s ORDER BY s.sortorder";
        $schoollist = $DB->get_records_sql($sql);
    } else
        $schoollist = $hierarchy->get_assignedschools();

    $sheetrow = 1;
    foreach ($schoollist as $school) {
        $departments = $DB->get_records('local_department', array('schoolid' => $school->id));
        foreach ($departments as $department) {
            $post = new stdclass();
            $post->shortname = $department->shortname;
            $post->fullname = $department->fullname;
            $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $department->schoolid));
            $post->schoolname = $schoolname;
            $post->summary = $department->description;
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
