<?php

/**
 * script for downloading learningplans
 */
require_once(dirname(__FILE__) . '/../../config.php');
$format = optional_param('format', '', PARAM_ALPHA);

if ($format) {
    $fields = array(
        'shortname' => 'shortname',
       'username' => 'username'
    );
    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename('Users sample');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array('lp_shortname','testuser');
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
