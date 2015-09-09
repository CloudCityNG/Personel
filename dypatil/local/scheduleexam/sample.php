<?php

/**
 * script for downloading sample excel sheet
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);
if ($format) {
    $fields = array(
        'schoolname' => 'schoolname',
        'semestername' => 'semestername',
        'classname' => 'classname',
        'examtype' => 'examtype',
        'lecturetype' => 'lecturetype',
        'opendate' => 'opendate',
        'starttimehour' => 'starttimehour',
        'starttimemin' => 'starttimemin',
        'endtimehour' => 'endtimehour',
        'endtimemin' => 'endtimemin',
        'grademin' => 'grademin',
        'grademax' => 'grademax'
    );

    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

// function user_download_xls($fields) {
// global $CFG,$DB;
// require_once("$CFG->libdir/excellib.class.php");
// require_once($CFG->dirroot.'/user/profile/lib.php');
// $filename = clean_filename(get_string('exam', 'local_exams').'.xls');
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
    $filename = clean_filename(get_string('exam', 'local_scheduleexam'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
