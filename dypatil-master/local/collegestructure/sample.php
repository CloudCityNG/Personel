<?php

/**
 * script for downloading schools
 */
require_once(dirname(__FILE__) . '/../../config.php');
$format = optional_param('format', '', PARAM_ALPHA);

if ($format) {
    $fields = array(
        'fullname' => 'fullname',
        'parentid' => 'parentid',
        'type' => 'type',
        'description' => 'description',
        'visible' => 'visible',
        'childpermissions' => 'childpermissions'
    );
    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename('Schools');
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
