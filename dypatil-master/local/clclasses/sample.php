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
        'department1' => 'department1',
        'fullname' => 'fullname',
        'shortname' => 'shortname',
        'description' => 'description',
        'cobaltcourse' => 'cobaltcourse',
        'startdate' => 'startdate',
        'enddate' => 'enddate',
        'classlimit' => 'classlimit',
        'lecturetype' => 'lecturetype',
        'department2' => 'department2',
        'instructor' => 'instructor',
        'starttime' => 'starttime',
        'endtime' => 'endtime',
        'classroom' => 'classroom'
    );

    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

/* ---function to download classes upload file--- */

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename('Classes');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
