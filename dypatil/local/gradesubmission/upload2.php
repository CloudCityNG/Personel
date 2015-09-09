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
 * Bulk grades registration script from a comma separated file
 *
 * @package    local
 * @subpackage grades
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
require_once('upload_grades_lib.php');
require_once('upload_grades_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);

require_login();



$strgradesupdated = get_string('gradesaccountupdated', 'local_gradesubmission');
$strgradesuptodate = get_string('gradesaccountuptodate', 'local_gradesubmission');
//$strgradesnotadded = get_string('gradesnotaddedregistered','local_gradesubmission');
//$strgradesdeleted = get_string('gradesdeleted','local_gradesubmission');
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);

$returnurl = new moodle_url('/local/gradesubmission/upload.php');
$PAGE->set_url('/local/gradesubmission/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_gradesubmission');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_gradesubmission'), new moodle_url('/local/gradesubmission/index.php'));
$PAGE->navbar->add(get_string('uploadgrades', 'local_gradesubmission'));

global $USER, $DB;



// array of all valid fields for validation
$STD_FIELDS = array(
    'school' => 'school',
    'program' => 'program',
    'semester' => 'semester',
    'classname' => 'classname',
    'student' => 'student'
);
// $PRF_FIELDS = array('internal', 'external', 'lab', 'other');
$PRF_FIELDS = array();
// $sql = "SELECT type.id, type.examtype FROM {local_scheduledexams} AS exam JOIN {local_examtypes} AS type ON type.id = exam.examtype AND type.schoolid = exam.schoolid AND type.programid = exam.programid";
// $exams = $DB->get_records_sql($sql);
$exams = $DB->get_records_sql("select * from {local_examtypes}");
foreach ($exams as $exam) {
    $exam->examtype = strtolower($exam->examtype);
    $PRF_FIELDS[] = $exam->examtype;
}

// print_r($PRF_FIELDS); 
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_grades_form1();
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadgrades');
        $cir = new csv_import_reader($iid, 'uploadgrades'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('gradesfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_grades_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        //--------tab code----------------------
        //$currenttab = 'upload';
        //$dept_ob = manage_dept::getInstance();
        //$dept_ob->dept_tabs($currenttab);
        //--------------------------------------------
        echo $OUTPUT->heading_with_help(get_string('uploadgrades', 'local_gradesubmission'), 'uploadgrades', 'local_gradesubmission');
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('uploaddes', 'local_gradesubmission'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>Sample Excel Sheet</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>Help Manual</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploadgrades');
    $filecolumns = uu_validate_grades_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

//---creating  object for second form---------------------- 
$mform2 = new admin_grades_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadgradessresult', 'local_gradesubmission'));
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    // verification moved to two places: after upload and into form2
    $gradesnew = 0;
    $gradesupdated = 0;
    $gradesuptodate = 0; //not printed yet anywhere
    $gradeserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $gradesskipped = 0;


    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line
    // init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
    $upt = new uu_progress_tracker();
    //print_r($upt);
    // $upt->start(); // start table
    loop:
    while ($line = $cir->next()) {


        $upt->flush();
        $linenum++;
        // $upt->track('line', $linenum);
        $grades = new stdClass();
        // add fields to grades object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $grades->$key = $value;
            if (in_array($key, $upt->columns)) {
                // $upt->track($key, s($value), 'normal');  
            }
        }
// print_object($grades);
        if (!isset($grades->classname)) {
            // prevent warnings below
            $grades->classname = '';
        }
        if (!isset($grades->semester)) {
            // prevent warnings below
            $grades->semester = '';
        }
        if (!isset($grades->student)) {
            // prevent warnings below
            $grades->student = '';
        }

        //--------------------------------this is used to include only new grades-----------------
        if ($optype == UU_GRADES_ADDNEW) {
            // grades creation is a special case - the gradesname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if ((!isset($grades->classname) || !isset($grades->semester) || !isset($grades->student)) or ( ($grades->classname === '') || ($grades->semester === '') || ($grades->student === ''))) {
                $error = true;
            }

            if ($error) {
                $gradeserrors++;
                continue;
            }
        }

        // make sure we really have gradesname
        if (empty($grades->student) || empty($grades->semester) || empty($grades->classname)) {
            // $upt->track('status', get_string('missingfield', 'error', 'fullname'), 'error');
            // $upt->track('fullname', $errorstr, 'error');
            $gradeserrors++;
            continue;
        }
        $school = $DB->get_record('local_school', array('fullname' => $grades->school));
        $program = $DB->get_record('local_program', array('fullname' => $grades->program));
        $semester = $DB->get_record('local_semester', array('fullname' => $grades->semester));
        $class = $DB->get_record('local_clclasses', array('fullname' => $grades->classname));
        $user = $DB->get_record_select("user", "CONCAT(firstname, ' ', lastname) = :student", array('student' => $grades->student));
        // if ($existinggrades = $DB->get_record('local_grades', array('fullname' => $grades->fullname, 'schoolid' => $scid))) {
        if ($existinggrades = $DB->get_record_sql("select * from {local_user_classgrades} where semesterid = $semester->id and  classid = $class->id and userid = $user->id")) {
            // $upt->track('id', $existinggrades->id, 'normal', false);
        }
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($grades->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
                if (in_array($field, $upt->columns)) {
                    // $upt->track($field, s($grades->$field), 'normal',false);
                }
            }
        }

        foreach ($PRF_FIELDS as $field) {
            if (isset($grades->$field)) {
                continue;
            }
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }

        // can we process with update or insert?
        $skip = false;
        switch ($optype) {
            case UU_GRADES_ADDNEW:

                if ($existinggrades) {
                    $gradesskipped++;
                    // $upt->track('status', $strgradesnotadded, 'warning');
                    $skip = true;
                }
                break;

            case UU_GRADES_ADD_UPDATE:
                break;
            case UU_GRADES_UPDATE:
                if (!$existinggrades) {
                    $gradesskipped++;
                    // $upt->track('status', $strgradesnotupdatednotexists, 'warning');
                    $skip = true;
                }
                break;
            default:
                // unknown type
                $skip = true;
        }

        if ($skip) {
            continue;
        }
        //print_object($line);exit;
        if (!empty($existinggrades)) {
            $grades->id = $existinggrades->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($grades, $column) or ! property_exists($existinggrades, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existinggrades->$column) and $existinggrades->$column !== '') {
                            continue;
                        }
                    } else if ($updatetype == UU_UPDATE_ALLOVERRIDE) {
                        // we override everything
                    } else if ($updatetype == UU_UPDATE_FILEOVERRIDE) {
                        if (!empty($formdefaults[$column])) {
                            // do not override with form defaults
                            continue;
                        }
                    }
                    if ($existinggrades->$column !== $grades->$column) {
                        if (in_array($column, $upt->columns)) {

                            // $upt->track($column, s($existinggrades->$column).'-->'.s($grades->$column), 'info', false);
                        }
                        $existinggrades->$column = $grades->$column;

                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {

                $hier = new hierarchy();
                //$grs = manage_dept::getInstance();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }

                // ==============checking condition for schools=====================
                $fac = $grades->schoolname;
                $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
                if (empty($scid)) {
                    echo '<h3 style="color:red;">Invalid school "' . $grades->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $c = 0;
                foreach ($schools as $scl) {
                    if ($scl->id == $school->id) {
                        ++$c;
                        break;
                    }
                }
                if ($c == 0) {
                    echo '<h3>Sorry you are not assigned to this school: "' . $grades->school . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                // ==========================end================================		
                if (empty($grades->school)) {
                    echo '<h3 style="color:red;">Please enter School in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (empty($grades->program)) {
                    echo '<h3 style="color:red;">Please enter Program in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (empty($grades->semester)) {
                    echo '<h3 style="color:red;">Please enter Semester in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $existinggrades->schoolid = $school->id;
                $existinggrades->programid = $program->id;
                $existinggrades->semester = $semester->id;
                $existinggrades->classid = $class->id;
                $existinggrades->courseid = $class->cobaltcourseid;
                $existinggrades->coursetotal = $coursetotal;
                $existinggrades->source = 'upl';
                $existinggrades->usermodified = $USER->id;
                $existinggrades->timemodified = time();
                $DB->update_record('local_user_classgrades', $existinggrades);
                $gradessupdated++;
            } else {
                // no grades information changed
                // $upt->track('status', $strgradesuptodate);
                $gradessuptodate++;
            }
        } else {     // else start for [!empty($existinggrades]
            // save the new grades to the database
            $exgrades = new stdclass();
            $clsgrades = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            // print_object($PRF_FIELDS);
            // ==============checking condition for schools=====================
            if (empty($grades->school)) {
                echo '<h3 style="color:red;">Please enter School in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($school)) {
                echo '<h3 style="color:red;">Invalid school "' . $grades->school . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $c = 0;
            foreach ($schools as $scl) {
                if ($school->id == $scl->id) {
                    ++$c;
                    break;
                }
            }
            if ($c == 0) {
                echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $grades->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // ==========================end========================
            // ============checking condition for Program =================
            if (empty($grades->program)) {
                echo '<h3 style="color:red;">Please enter Program in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($program)) {
                echo '<h3 style="color:red;">Invalid Program "' . $grades->program . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!$DB->record_exists('local_program', array('id' => $program->id, 'schoolid' => $school->id))) {
                echo '<h3 style="color:red;">The Program: "' . $grades->program . '" doesn\'t exists under the School: "' . $grades->school . '" in line no. ' . $linenum . ' of uploaded excelsheet</h3>';
                goto loop;
            }
            // ===========================end======================
            // ============checking condition for Semester =================
            if (empty($grades->semester)) {
                echo '<h3 style="color:red;">Please enter Semester in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($semester)) {
                echo '<h3 style="color:red;">Invalid Semester "' . $grades->semester . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!$record = $DB->get_record_sql("SELECT * FROM {local_semester} sem, {local_school_semester} sch WHERE sch.semesterid = sem.id AND sem.id = $semester->id AND sch.schoolid = $school->id")) {
                echo '<h3 style="color:red;">The Semester: "' . $grades->semester . '" doesn\'t exists under the School: "' . $grades->school . '" in line no. ' . $linenum . ' of uploaded excelsheet</h3>';
                goto loop;
            }
            // ===========================end=====================
            // ============checking condition for Class =================
            if (empty($grades->classname)) {
                echo '<h3 style="color:red;">Please enter Class name in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($class)) {
                echo '<h3 style="color:red;">Invalid Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!$DB->record_exists('local_clclasses', array('id' => $class->id, 'schoolid' => $school->id, 'semesterid' => $semester->id))) {
                echo '<h3 style="color:red;">The Class: "' . $grades->classname . '" doesn\'t exists under the Semester: "' . $grades->semester . '" in line no. ' . $linenum . ' of uploaded excelsheet</h3>';
                goto loop;
            }
            // ===========================end=======================
            // ============checking condition for the exams================
            if (!$exams = $DB->get_records('local_scheduledexams', array('semesterid' => $semester->id, 'classid' => $class->id))) {
                echo '<h3 style="color:red;">No Exams are scheduled for this Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of 	uploaded excelsheet.</h3>';
                goto loop;
            }

            foreach ($exams as $exam) {
                $type = $DB->get_record('local_examtypes', array('id' => $exam->examtype));
                if ($type->examtype == $grades->$type->examtype)
                    ;
            }
            $sql = "SELECT type.id, type.examtype FROM {local_scheduledexams} AS exam JOIN {local_examtypes} AS type ON type.id = exam.examtype AND type.schoolid = exam.schoolid AND type.programid = exam.programid";
            $exams = $DB->get_records_sql($sql);
            if (empty($exams)) {
                
            }
            // ===========================end=======================
            // ============checking users enrolled to the class =============
            $gradesub = grade_submission::getInstance();
            if (!$DB->record_exists('local_user_clclasses', array('classid' => $class->id, 'semesterid' => $semester->id, 'userid' => $user->id, 'registrarapproval' => 1))) {
                echo '<h3 style="color:red;">Student "' . $grades->student . '" is not enrolled to the Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // ===========================end=======================
            // ============checking exams are created for the class=========
            $classexams = $gradesub->get_class_exams($semester->id, $class->id);
            if (empty($classexams)) {
                echo '<h3 style="color:red;">No Exams are Scheduled to the Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // ===========================end=======================
            // ============checking exams are completed to the class========
            $today = date('Y-m-d');
            foreach ($exams as $exam) {
                if (date('Y-m-d', $exam->opendate) > $today) {
                    echo '<h3 style="color:red;">Exams are not at completed for the Class "' . $grades->classname . '" entered at line no. "' . $linenum . '" of 	uploaded excelsheet.</h3>';
                    goto loop;
                }
            }
            // ===========================end=======================
            $total = count($PRF_FIELDS);
            $sum = 0;
            $grademaxtot = 0;
            for ($i = 0; $i < $total; $i++) {
                if (isset($admission->$PRF_FIELDS[$i]) && !empty($admission->$PRF_FIELDS[$i])) {
                    $examtypeid = $DB->get_field('local_examtypes', 'id', array('examtype' => $PRF_FIELDS[$i]));
                    // echo $examtypeid;
                    $gsql = "SELECT * from {local_user_examgrades} where examid={$examtypeid} and semesterid={$semester->id} and classid=	{$class->classid} and userid={$user->userid} and source='offline'";
                    $exgrade = $DB->get_record_sql($gsql);
                    $grademaxtot = $grademaxtot + $exam->grademax;
                    if (!$existype = get_record_sql("select * from {local_scheduledexams} where examtype= $examtypeid and schoolid = $school->id and 		 programid = $program->id and semesterid = $semester->id and classid = $class->id")) {
                        echo 'Invalid gradesubmission at ' . $linenum . '';
                        goto loop;
                    } else {
                        $exgrades->userid = $user->id;
                        $exgrades->source = 'offline';
                        $exgrades->schoolid = $school->id;
                        $exgrades->programid = $program->id;
                        $exgrades->semesterid = $semester->id;
                        $exgrades->classid = $class->id;
                        $exgrades->courseid = $class->cobaltcourseid;
                        $exgrades->examid = $examtypeid;
                        $exgrades->finalgrade = $admission->$PRF_FIELDS[$i];
                        $exgrades->timecreated = time();
                        $exgrades->timemodified = 0;
                        $exgrades->usermodified = $USER->id;
                        $exgrades->id = $DB->insert_record('local_user_examgrades', $exgrades);
                        $DB->insert_record('local_user_examgrades', $data);
                        $sum = $sum + $admission->$PRF_FIELDS[$i];
                    }
                }
            }
            $clsgrades->userid = $user->id;
            $clsgrades->schoolid = $school->id;
            $clsgrades->programid = $program->id;
            $clsgrades->semesterid = $semester->id;
            $clsgrades->classid = $class->id;
            $clsgrades->courseid = $class->cobaltcourseid;
            $clsgrades->coursetotal = $sum;
            $clsgrades->percentage = ($sum / $grademaxtot) * 100;
            $per = $data->percentage;
            $psql = "SELECT letter,gradepoint from {local_gradeletters} where {$per} BETWEEN markfrom and markto";
            $gradepoint = $DB->get_record_sql($psql);

            if ($gradepoint) {
                $gletter = $gradepoint->letter;
                $gpoint = $gradepoint->gradepoint;
            } else {
                $gletter = 'Not Defined';
                $gpoint = 'Not Defined';
            }
            $clsgrades->gradeletter = $gletter;
            $clsgrades->gradepoint = $gpoint;
            $clsgrades->timecreated = time();
            $clsgrades->timemodified = 0;
            $clsgrades->usermodified = $USER->id;
            $clsgrades->id = $DB->insert_record('local_user_classgrades', $clsgrades);
            $DB->insert_record('local_user_clsgrade_hist', $clsgrades);
            // $clsgrades->id++;
            $gradesnew++;
        }
    }
    // $upt->close(); // close table
    // $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_GRADES_UPDATE) {
        echo get_string('gradescreated', 'local_gradesubmission') . ': ' . $gradesnew . '<br />';
    }
    if ($optype == UU_GRADES_UPDATE or $optype == UU_GRADES_ADD_UPDATE) {
        echo get_string('gradesupdated', 'local_gradesubmission') . ': ' . $gradesupdated . '<br />';
    }
    if ($gradesskipped) {
        echo get_string('gradesskipped', 'local_gradesubmission') . ': ' . $gradesskipped . '<br />';
    }
    echo get_string('errors', 'local_gradesubmission') . ': ' . $gradeserrors . '</p>';
    echo $OUTPUT->box_end();
    //echo $OUTPUT->footer();
    if ($gradesskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($gradesskipped == 1)
            echo '<h4> Grades skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $gradesskipped . ' Grades skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="upload.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
// Print the header


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploadgradespreview', 'local_gradesubmission'));
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

