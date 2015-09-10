<?php

/**
 * script for downloading applicants
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
$format = optional_param('format', '', PARAM_ALPHA);

if ($format) {
    $fields = array(
        'previousstudent' => 'previousstudent',
        'serviceid' => 'serviceid',
        'typeofprogram' => 'typeofprogram',
        'schoolname' => 'schoolname',
        'programname' => 'programname',
        'typeofstudent' => 'typeofstudent',
        'typeofapplication' => 'typeofapplication',
        'firstname' => 'firstname',
        'middlename' => 'middlename',
        'lastname' => 'lastname',
        'gender' => 'gender',
        'dob' => 'dob',
        'birthcountry' => 'birthcountry',
        'birthplace' => 'birthplace',
        'fathername' => 'fathername',
        'pob' => 'pob',
        'region' => 'region',
        'town' => 'town',
        'current_home_no' => 'current_home_no',
        'current_country' => 'current_country',
        'phone' => 'phone',
        'email' => 'email',
        'howlong' => 'howlong',
        'same' => 'same',
        'permenant_country' => 'permenant_country',
        'permenant_home_no' => 'permenant_home_no',
        'state' => 'state',
        'city' => 'city',
        'pincode' => 'pincode',
        'contactname' => 'contactname',
        'primary_school' => 'primary_school',
        'primary_year' => 'primary_year',
        'primary_score' => 'primary_score',
        'primary_place' => 'primary_place',
        'undergraduate_in' => 'undergraduate_in',
        'ugname' => 'ugname',
        'ug_year' => 'ug_year',
        'ug_score' => 'ug_score',
        'ug_place' => 'ug_place',
        'graduate_in' => 'graduate_in',
        'graduate_name' => 'graduate_name',
        'graduate_year' => 'graduate_year',
        'graduate_score' => 'graduate_score',
        'graduate_place' => 'graduate_place',
        'examname' => 'examname',
        'hallticketno' => 'hallticketno',
        'score' => 'score',
        'no_of_months' => 'no_of_months',
        'reason' => 'reason',
        'description' => 'description'
    );

    switch ($format) {
        case 'csv' : user_download_csv($fields);
    }
    die;
}

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('pluginname', 'local_admission'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
