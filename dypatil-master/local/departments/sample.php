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
$systemcontext =context_system::instance();
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
        'fullname' => 'fullname',
        'shortname' => 'shortname',
        'schoolname' => 'schoolname',
        'description' => 'description'
    );

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

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('department', 'local_departments'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
