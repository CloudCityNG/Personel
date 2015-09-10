<?php

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

/**
 * Bulk admission registration script from a comma separated file
 *
 * @package    tool
 * @subpackage admission
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('upload_admissions_lib.php');
require_once('upload_admissions_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

require_login();

$stradmissionupdated = get_string('admissionaccountupdated', 'local_admission');
$stradmissionuptodate = get_string('admissionaccountuptodate', 'local_admission');
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
global $USER, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$string = get_string('pluginname', 'local_admission') . ' : ' . get_string('uploadadmissions', 'local_admission');
$PAGE->set_title($string);
$PAGE->set_url('/local/admission/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_admission');
$PAGE->navbar->add(get_string('manage', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('uploadadmissions', 'local_admission'));
$returnurl = new moodle_url('/local/admission/upload.php');
$STD_FIELDS = array('firstname', 'middlename', 'lastname', 'gender', 'dob', 'birthcountry', 'birthplace', 'fathername', 'pob', 'region', 'town', 'current_home_no', 'current_country', 'phone', 'email', 'howlong', 'same', 'permenant_country', 'permenant_home_no', 'state', 'city', 'pincode', 'contactname', 'primary_school', 'primary_year', 'primary_score', 'primary_place', 'undergraduate_in', 'ugname', 'ug_year', 'ug_score', 'ug_place', 'graduate_in', 'graduate_name', 'graduate_year', 'graduate_score', 'graduate_place', 'examname', 'hallticketno', 'score', 'no_of_months', 'reason', 'description', 'schoolname', 'programname', 'typeofstudent', 'typeofapplication', 'previousstudent', 'serviceid', 'typeofprogram');

$PRF_FIELDS = array();
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_admission_form1();
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadadmission');
        $cir = new csv_import_reader($iid, 'uploadadmission'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('admissionfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_admission_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        $instance = cobalt_admission::get_instance();
        echo $OUTPUT->heading(get_string('pluginname', 'local_admission'));
        // Current tab
        $currenttab = 'upload';
        //adding tabs
        $instance->report_tabs($currenttab);

        echo $OUTPUT->box(get_string('uploadtabdes', 'local_admission'));
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploadadmission');
    $filecolumns = uu_validate_admission_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadadmissionsresult', 'local_admission'));
$optype = 'UU_ADMISSION_ADDNEW';
// verification moved to two places: after upload and into form2
$admissionsnew = 0;
$admissionsupdated = 0;
$admissionsuptodate = 0; //not printed yet anywhere
$admissionserrors = 0;
$admissionsskipped = 0;
// init csv import helper
$cir->init();
$linenum = 1; //column header is first line
// init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
$upt = new uu_progress_tracker();
loop:
$data = new stdclass();
while ($line = $cir->next()) {
    $upt->flush();
    $linenum++;
    $admission = new stdClass();
    // add fields to admission object
    foreach ($line as $keynum => $value) {
        if (!isset($filecolumns[$keynum])) {
            // this should not happen
            continue;
        }
        $key = $filecolumns[$keynum];
        $admission->$key = $value;
    }
    if (!empty($admission->previousstudent) && !empty($admission->serviceid) && $admission->previousstudent == 2) {

        // schools validation
        $scname = trim($admission->schoolname);
        $schoollist = $DB->get_record('local_school', array('fullname' => $scname));
        if (empty($schoollist)) {
            echo '<h3 style="color:red;">Invalid school  "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $scid = $schoollist->id;
        // check schoolname
        $hier = new hierarchy();
        $schools = $hier->get_assignedschools();
        if (is_siteadmin()) {
            $schools = $hier->get_school_items();
        }
        $c = 0;
        foreach ($schools as $scl) {
            if ($scid == $scl->id) {
                ++$c;
                break;
            }
        }
        if ($c == 0) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }

        if (empty($admission->typeofprogram)) {
            echo '<h3 style="color:red;">Please enter value for "typeofprogram" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // type of program validation
        if ($admission->typeofprogram > 2 || $admission->typeofprogram < 1) {
            echo '<h3 style="color:red;">You have entered invalid typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        if (empty($admission->typeofapplication)) {
            echo '<h3 style="color:red;">Please enter value for "typeofapplication" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if ($admission->typeofapplication > 2 || $admission->typeofapplication < 1) {
            echo '<h3 style="color:red;">You have entered invalid typeofapplication "' . $admission->typeofapplication . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        //  program validation
        $prg = trim($admission->programname);
        $programlist = $DB->get_record('local_program', array('fullname' => $prg));
        if (empty($programlist)) {
            echo '<h3 style="color:red;">Invalid program name "' . $admission->programname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $prgid = $programlist->id;

        $programs = $DB->get_records('local_program', array('id' => $prgid, 'schoolid' => $scid, 'programlevel' => $admission->typeofprogram));
        if (empty($programs)) {
            echo '<h3 style="color:red;">Program "' . $admission->programname . '" is not under given School "' . $admission->schoolname . '" or not under given typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $time = date('m/d/Y');
        $time = strtotime($time);
        $openadms = $DB->get_record_sql('select * from {local_event_activities} where schoolid = ' . $scid . ' and programid = ' . $prgid . ' and publish = 1 and ((' . $time . ' BETWEEN startdate and enddate) or (' . $time . ' >= startdate and enddate = 0))  and eventtypeid = 1');
        $serviceid = trim($admission->serviceid);
        if (empty($openadms)) {
            echo '<h3 style="color:red;">Admissions are not opened for the applied program entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $applcntid = $DB->get_field('local_userdata', 'applicantid', array('serviceid' => $serviceid, 'schoolid' => $scid));
        if (empty($applcntid)) {
            echo '<h3 style="color:red;">You have entered invalid serviceid "' . $admission->serviceid . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        $result = $DB->get_record('local_admission', array('id' => $applcntid));
        $previous = $result;
        $previous->dateofapplication = time();
        $previous->typeofprogram = $admission->typeofprogram;
        $previous->typeofapplication = $admission->typeofapplication;
        $previous->programid = $prgid;
        $previous->status = 0;
        $DB->insert_record('local_admission', $previous);
        $admissionsnew++;
        // print_object($previous);
    } else {
        if ($admission->previousstudent != 1) {
            echo '<h3 style="color:red;">You have entered invalid previousstudent "' . $admission->previousstudent . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        if (!isset($admission->email)) {
            // prevent warnings below
            $admission->email = '';
        }
        //--------------------------------this is used to include only new admissions-----------------
        if ($optype == 'UU_ADMISSION_ADDNEW') {
            // admission creation is a special case - the admissionname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($admission->email) or $admission->email === '') {
                $error = true;
            }
            if ($error) {
                $admissionserrors++;
                continue;
            }
        }
        // make sure we really have admissionname
        if (empty($admission->email)) {
            $admissionserrors++;
            continue;
        }
        $admission->email = trim($admission->email);
        $existingadmission = $DB->get_record_sql("select * from {local_admission} where email = '$admission->email' and (status=0 or status=1)");
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($admission->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($admission->$field)) {
                continue;
            }
            if (isset($formdata->$field)) {
                $formdefaults[$field] = true;
            }
        }
        // can we process with update or insert?
        $skip = false;
        if ($optype == 'UU_ADMISSION_ADDNEW') {
            if ($existingadmission) {
                $admissionsskipped++;
                $skip = true;
            }
        }
        if ($skip) {
            continue;
        }
        // save the new admission to the database
        // schools validation
        $scname = trim($admission->schoolname);
        $schoollist = $DB->get_record('local_school', array('fullname' => $scname));
        if (empty($schoollist)) {
            echo '<h3 style="color:red;">Invalid school  "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $scid = $schoollist->id;
        // check schoolname
        $hier = new hierarchy();
        $schools = $hier->get_assignedschools();
        if (is_siteadmin()) {
            $schools = $hier->get_school_items();
        }
        $c = 0;
        foreach ($schools as $scl) {
            if ($scid == $scl->id) {
                ++$c;
                break;
            }
        }
        if ($c == 0) {
            echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->typeofprogram)) {
            echo '<h3 style="color:red;">Please enter value for "typeofprogram" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->typeofapplication)) {
            echo '<h3 style="color:red;">Please enter value for "typeofapplication" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if ($admission->typeofapplication > 2 || $admission->typeofapplication < 1) {
            echo '<h3 style="color:red;">You have entered invalid typeofapplication "' . $admission->typeofapplication . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        // type of program validation
        if ($admission->typeofprogram > 2 || $admission->typeofprogram < 1) {
            echo '<h3 style="color:red;">You have entered invalid typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        //  program validation
        $prg = trim($admission->programname);
        $programlist = $DB->get_record('local_program', array('fullname' => $prg));
        if (empty($programlist)) {
            echo '<h3 style="color:red;">Invalid program name "' . $admission->programname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $prgid = $programlist->id;

        $programs = $DB->get_records('local_program', array('id' => $prgid, 'schoolid' => $scid, 'programlevel' => $admission->typeofprogram));
        if (empty($programs)) {
            echo '<h3 style="color:red;">Program "' . $admission->programname . '" is not under given School "' . $admission->schoolname . '" or not under given typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
            goto loop;
        }
        $time = date('m/d/Y');
        $time = strtotime($time);
        $openadms = $DB->get_record_sql('select * from {local_event_activities} where schoolid = ' . $scid . ' and programid = ' . $prgid . ' and publish = 1 and ((' . $time . ' BETWEEN startdate and enddate) or (' . $time . ' >= startdate and enddate = 0))  and eventtypeid = 1');
        if (empty($openadms)) {
            echo '<h3 style="color:red;">Admissions are not opened for the applied program entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // type of student validation
        if ($admission->typeofstudent > 3 || $admission->typeofstudent < 1) {
            echo '<h3 style="color:red;">You have entered invalid typeofstudent "' . $admission->typeofstudent . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
            goto loop;
        }
        $year = time();
        $year_in_number = date('Y');
        if (empty($admission->dob)) {
            echo '<h3 style="color:red;">Please enter dob in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $ex = explode('/', $admission->dob);
        $howlong = $year_in_number - $ex[2];
        $dob = strtotime($admission->dob);
        $true = checkdate($ex[0], $ex[1], $ex[2]);
        if ($true == false) {
            echo '<h3 style="color:red;">Invalid  dob "' . $admission->dob . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
            goto loop;
        }
        // check dob
        if ($dob > $year) {
            echo '<h3 style="color:red;">dob "' . $admission->dob . '" should be less than present date at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // check howlong
        if ($howlong < $admission->howlong) {
            echo '<h3 style="color:red;"> howlong ' . $admission->howlong . '  should not be greater than your age at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // check primary year
        if ($admission->primary_year > $year_in_number) {
            echo '<h3 style="color:red;">primary_year "' . $admission->primary_year . '" should not br greater than present date at line no. "' . $linenum . '".</h3>';
            goto loop;
        }
        if ($admission->primary_year < $ex[2]) {
            echo '<h3 style="color:red;">primary_year "' . $admission->primary_year . '" should not be less than dob at line no. "' . $linenum . '".</h3>';
            goto loop;
        }
        if ($admission->typeofprogram == 2) {
            if ($admission->ug_year > $year_in_number) {
                echo '<h3 style="color:red;">ug_year  "' . $admission->ug_year . '" should not be greater than present date at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if ($admission->ug_year < $admission->primary_year) {
                echo '<h3 style="color:red;">ug_year "' . $admission->ug_year . '"  should not be less than primary year at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if ($admission->ug_year < $ex[2]) {
                echo '<h3 style="color:red;">ug_year "' . $admission->ug_year . '" should not be less than dob at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if ($admission->ug_year == $admission->primary_year) {
                echo '<h3 style="color:red;">ug_year "' . $admission->ug_year . '"  should not be less than primary year at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        if ($admission->same > 2 || $admission->same < 1) {
            echo '<h3 style="color:red;">Number entered for field same "' . $admission->same . '" is invalid at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $value1 = numeric_validation($admission->pob);
        if ($value1 == 0) {
            echo '<h3 style="color:red;">Enter valid value for field pob in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        // check phone number
        $phone_length = strlen($admission->phone);
        if ($phone_length > 15 || $phone_length < 10) {
            echo '<h3 style="color:red;">Enter valid phone number in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->current_home_no)) {
            echo '<h3 style="color:red;">Please enter current_home_no in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->current_country)) {
            echo '<h3 style="color:red;">Please enter current_country in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->fathername)) {
            echo '<h3 style="color:red;">Please enter fathername in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->pob)) {
            echo '<h3 style="color:red;">Please enter pob in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->region)) {
            echo '<h3 style="color:red;">Please enter region in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->town)) {
            echo '<h3 style="color:red;">Please enter town in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->birthcountry)) {
            echo '<h3 style="color:red;">Please enter birthcountry in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $check_mail = filter_var($admission->email, FILTER_VALIDATE_EMAIL);
        if ($check_mail == false) {
            echo '<h3 style="color:red;">Please enter valid email in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $country = get_string_manager()->get_list_of_countries();
        if (!empty($admission->birthcountry)) {
            if (!array_key_exists($admission->birthcountry, $country)) {
                echo '<h3 style="color:red;">Please enter valid code for birthcountry in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        if (!empty($admission->current_country)) {
            if (!array_key_exists($admission->current_country, $country)) {
                echo '<h3 style="color:red;">Please enter valid code for current_country  in line  no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }

        if (empty($admission->same)) {
            echo '<h3 style="color:red;">Please enter value for "same" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->firstname)) {
            echo '<h3 style="color:red;">Please enter firstname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->lastname)) {
            echo '<h3 style="color:red;">Please enter lastname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->gender)) {
            echo '<h3 style="color:red;">Please enter gender in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }

        if (empty($admission->birthplace)) {
            echo '<h3 style="color:red;">Please enter birthplace in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->primary_school)) {
            echo '<h3 style="color:red;">Please enter primary_school in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->primary_year)) {
            echo '<h3 style="color:red;">Please enter primary_year in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->primary_score)) {
            echo '<h3 style="color:red;">Please enter primary_score in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->primary_place)) {
            echo '<h3 style="color:red;">Please enter primary_place in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if (empty($admission->typeofstudent)) {
            echo '<h3 style="color:red;">Please enter typeofstudent in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        if ($admission->typeofstudent == 2) {
            if (empty($admission->examname)) {
                echo '<h3 style="color:red;">Please enter examname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->hallticketno)) {
                echo '<h3 style="color:red;">Please enter hallticketno in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->score)) {
                echo '<h3 style="color:red;">Please enter score in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        if ($admission->typeofstudent == 3) {
            if (empty($admission->no_of_months)) {
                echo '<h3 style="color:red;">Please enter no_of_months in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->reason)) {
                echo '<h3 style="color:red;">Please enter reason in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->description)) {
                echo '<h3 style="color:red;">Please enter description in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        if ($admission->typeofprogram == 2) {
            if (empty($admission->undergraduate_in)) {
                echo '<h3 style="color:red;">Please enter undergraduate_in in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->ugname)) {
                echo '<h3 style="color:red;">Please enter ugname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->ug_year)) {
                echo '<h3 style="color:red;">Please enter ug_year in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->ug_score)) {
                echo '<h3 style="color:red;">Please enter ug_score in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->ug_place)) {
                echo '<h3 style="color:red;">Please enter ug_place in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        if ($admission->same == 2) {
            if (empty($admission->permenant_country)) {
                echo '<h3 style="color:red;">Please enter permenant_country in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!empty($admission->permenant_country)) {
                if (!array_key_exists($admission->permenant_country, $country)) {
                    echo '<h3 style="color:red;">Please enter valid code for permenant_country in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
            }
            if (empty($admission->city)) {
                echo '<h3 style="color:red;">Please enter city in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->permenant_home_no)) {
                echo '<h3 style="color:red;">Please enter permenant_home_no in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->state)) {
                echo '<h3 style="color:red;">Please enter state in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($admission->pincode)) {
                echo '<h3 style="color:red;">Please enter pincode in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $value2 = numeric_validation($admission->pincode);
            if ($value2 == 0) {
                echo '<h3 style="color:red;">Enter valid value for field pincode in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            if (empty($admission->contactname)) {
                echo '<h3 style="color:red;">Please enter contactname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
        }
        $data->firstname = $admission->firstname;
        $data->middlename = $admission->middlename;
        $data->lastname = $admission->lastname;
        $data->gender = $admission->gender;
        $data->dob = $dob;
        $data->birthcountry = $admission->birthcountry;
        $data->birthplace = $admission->birthplace;
        $data->fathername = $admission->fathername;
        $data->pob = $admission->pob;
        $data->region = $admission->region;
        $data->town = $admission->town;
        $data->howlong = $admission->howlong;
        $data->currenthno = $admission->current_home_no;
        $data->currentcountry = $admission->current_country;
        $data->phone = $admission->phone;
        $data->email = $admission->email;
        $data->same = $admission->same;
        if ($data->same == 1) {
            $data->pcountry = $admission->current_country;
            $data->permenanthno = $admission->current_home_no;
            $data->state = $admission->region;
            $data->city = $admission->town;
            $data->pincode = $admission->pob;
            $data->contactname = $admission->fathername;
        }
        if ($data->same == 2) {
            $data->pcountry = $admission->permenant_country;
            $data->permanenthno = $admission->permenant_home_no;
            $data->state = $admission->state;
            $data->city = $admission->city;
            $data->pincode = $admission->pincode;
            $data->contactname = $admission->contactname;
        }
        $data->primaryschoolname = $admission->primary_school;
        $data->primaryyear = $admission->primary_year;
        $data->primaryscore = $admission->primary_score;
        $data->primaryplace = $admission->primary_place;
        $data->typeofstudent = $admission->typeofstudent;
        //international student
        if ($data->typeofstudent == 2) {
            $data->examname = $admission->examname;
            $data->hallticketno = $admission->hallticketno;
            $data->score = $admission->score;
        } elseif ($data->typeofstudent == 3) {   //mature student
            $data->noofmonths = $admission->no_of_months;
            $data->reason = $admission->reason;
            $data->description = $admission->description;
        }

        $data->schoolid = $scid;
        $data->programid = $prgid;
        $data->typeofapplication = 1;
        $data->status = 0;
        $data->typeofprogram = $admission->typeofprogram;
        if ($data->typeofprogram == 2) { // graduate
            $data->ugin = $admission->undergraduate_in;
            $data->ugname = $admission->ugname;
            $data->ugyear = $admission->ug_year;
            $data->ugscore = $admission->ug_score;
            $data->ugplace = $admission->ug_place;
        }
        $data->previousstudent = 1;
        $data->dateofapplication = time();
        $data->id = $DB->insert_record('local_admission', $data);
        $program = $DB->get_field('local_program', 'shortname', array('id' => $data->programid));
        $random = random_string(5);
        $update = new Stdclass();
        $update->id = $data->id;
        $update->applicationid = $program . $data->id . $random;
        $applicationid = $DB->update_record('local_admission', $update);
        $data->id ++;
        $admissionsnew++;
    }
}

// $upt->close(); // close table
// $cir->close();
$cir->cleanup(true);
echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
echo '<p>';
if ($optype != 'UU_ADMISSION_UPDATE') {
    echo get_string('admissionscreated', 'local_admission') . ': ' . $admissionsnew . '<br />';
}
if ($optype == 'UU_ADMISSION_UPDATE' or $optype == 'UU_ADMISSION_ADD_UPDATE') {
    echo get_string('admissionsupdated', 'local_admission') . ': ' . $admissionsupdated . '<br />';
}
if ($admissionsskipped) {
    echo get_string('admissionsskipped', 'local_admission') . ': ' . $admissionsskipped . '<br />';
}
if ($admissionserrors)
    echo get_string('errors', 'local_admission') . ': ' . $admissionserrors;
echo '</p>';
echo $OUTPUT->box_end();
if ($admissionsskipped) {
    echo $OUTPUT->box_start('generalbox');
    if ($admissionsskipped == 1)
        echo '<h4> Admission skipped because record with that email is  already exists.</h4>';
    else
        echo '<h4>' . $admissionsskipped . ' admissions skipped because records with those emails are already exists.</h4>';
    echo $OUTPUT->box_end();
}
if ($admissionserrors) {
    echo '<h4> Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
}
echo '<div style="margin-left:35%;"><a href="viewapplicant.php"><button>Continue</button></a></div>';
echo $OUTPUT->footer();
die;
