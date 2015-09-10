<?php

/**
 * script for downloading admissions
 */
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/*
 * @package    local
 * @subpackage admission
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/adminlib.php');
require_once('lib.php');
$format = optional_param('format', '', PARAM_ALPHA);
global $DB;
$systemcontext = context_system::instance();
if (!has_capability('local/admission:manage', $systemcontext))
    print_error('nopermissions', 'error');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_courseregistration'), new moodle_url('/local/admission/index.php'));
$PAGE->navbar->add(get_string('dds', 'local_admission'));
if ($format) {
    $fields = array(
        'firstname' => 'firstname',
        'middlename' => 'middlename',
        'lastname' => 'lastname',
        'gender' => 'gender',
        'region' => 'region',
        'town' => 'town',
        'howlong' => 'howlong',
        'dob' => 'dob',
        'country' => 'country',
        'placecountry' => 'placecountry',
        'contactname' => 'contactname',
        'pob' => 'pob',
        'phone' => 'phone',
        'email' => 'email',
        'cummulativetotal' => 'cummulativetotal',
        'primaryschool' => 'primaryschool',
        'primaryyear' => 'primaryyear',
        'secondaryschool' => 'secondaryschool',
        'secondaryyear' => 'secondaryyear',
        'univercity' => 'univercity',
        'univercityyear' => 'univercityyear',
        'schoolname' => 'schoolname',
        'programname' => 'programname',
        'typeofapplication' => 'typeofapplication',
        'status' => 'status',
        'semname' => 'semname',
        'dateofapplication' => 'dateofapplication',
        'typeofprogram' => 'typeofprogram',
        'applicationid' => 'applicationid'
    );



    switch ($format) {
        case 'xls' : user_download_xls($fields);
    }
    die;
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dd', 'local_cobaltcourses'));
echo $OUTPUT->box_start();
echo '<ul>';
echo '<li><a href="download_all.php?format=xls">' . get_string('downloadexcel') . '</a></li>';
echo '</ul>';
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

function user_download_xls($fields) {
    $admission = cobalt_admission::get_instance();
    global $CFG, $DB;
    require_once("$CFG->libdir/excellib.class.php");
    $filename = clean_filename(get_string('admission', 'local_admission') . '.xls');
    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_worksheet('');
    $col = 0;
    foreach ($fields as $fieldname) {
        $worksheet[0]->write(0, $col, $fieldname);
        $col++;
    }
    $sheetrow = 1;
    $sql = $admission->cobalt_admission_applicant(0, 0, 0, 0);
    $applicants = $DB->get_records_sql($sql);

    foreach ($applicants as $app) {
        $user = new stdclass();

        $user->firstname = $app->firstname;
        $user->middlename = $app->middlename;
        $user->lastname = $app->lastname;
        $user->gender = $app->gender;
        $user->region = $app->region;
        $user->town = $app->town;
        $user->howlong = $app->howlong;
        $user->dob = $app->dob;
        $user->country = $app->country;
        $user->placecountry = $app->placecountry;
        $user->contactname = $app->contactname;
        $user->pob = $app->pob;
        $user->phone = $app->phone;
        $user->email = $app->email;
        $user->cummulativetotal = $app->cummulativetotal;
        $user->primaryschool = $app->primaryschool;
        $user->primaryyear = $app->primaryyear;
        $user->secondaryschool = $app->secondaryschool;
        $user->secondaryyear = $app->secondaryyear;
        $user->univercity = $app->univercity;
        $user->univercityyear = $app->univercityyear;
        $user->status = $app->status;
        $semid = $DB->get_field('local_semester', 'fullname', array('id' => $app->semid));
        $user->semname = $semid;
        $user->applicationid = $app->applicationid;
        $user->dateofapplication = date('d-m-y', $app->dateofapplication);
        $user->schoolname = $DB->get_field('local_school', 'fullname', array('id' => $app->schoolid));
        $user->programname = $DB->get_field('local_program', 'fullname', array('id' => $app->programid));
        if ($app->typeofapplication == 1) {
            $app->typeofapplication = 'New Applicant';
        } elseif ($app->typeofapplication == 2) {
            $app->typeofapplication = 'Transfer Applicant';
        } else {
            $app->typeofapplication = 'Readmitted Applicant';
        }
        $user->typeofapplication = $app->typeofapplication;


        if ($app->typeofprogram == 1) {
            $app->typeofprogram = 'Undergraduate';
        } elseif ($app->typeofprogram == 2) {
            $app->typeofprogram = 'Graduate';
        } else {
            $app->typeofprogram = 'Postgraduate';
        }
        $user->typeofprogram = $app->typeofprogram;
        $col = 0;
        foreach ($fields as $fieldname) {
            $worksheet[0]->write($sheetrow, $col, $user->$fieldname);
            $col++;
        }
        $sheetrow++;
    }

    $workbook->close();
    die;
}
