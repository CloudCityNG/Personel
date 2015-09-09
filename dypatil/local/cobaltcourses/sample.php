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
        'fullname' => 'fullname',
        'shortname' => 'shortname',
        'departmentname' => 'departmentname',
        'schoolname' => 'schoolname',
        'summary' => 'summary',
        'coursetype' => 'coursetype',
        'credithours' => 'credithours',
        'coursecost' => 'coursecost'
    );

    switch ($format) {
        case 'csv' : user_download_csv($fields);
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

function user_download_csv($fields) {
    global $CFG;
    require_once($CFG->libdir . '/csvlib.class.php');
    $filename = clean_filename(get_string('course', 'local_cobaltcourses'));
    $csvexport = new csv_export_writer();
    $csvexport->set_filename($filename);
    $csvexport->add_data($fields);
    $userprofiledata = array();
    $csvexport->add_data($userprofiledata);
    $csvexport->download_file();
    die;
}
