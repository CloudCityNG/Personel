<?php

/**
 * script for downloading admissions
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);

if ($format) {
    $fields = array(
        'username' => 'username',
        'password' => 'password',
        'firstname' => 'firstname',
        'middlename' => 'middlename',
        'lastname' => 'lastname',
        'gender' => 'gender',
        'address' => 'address',
        'city' => 'city',
        'dob' => 'dob',
        'country' => 'country',
        'phone' => 'phone',
        'email' => 'email',
        'description' => 'description',
        'schoolname' => 'schoolname',
        'role' => 'role'
    );

    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('users'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
