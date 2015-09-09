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
 * Bulk exam registration script from a comma separated file
 *
 * @package    local
 * @subpackage exams
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
require_once('upload_exams_lib.php');
require_once('upload_exams_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);
require_login();
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
// if(!has_capability('local/exams:manage', $systemcontext))
// print_error('nopermissions', 'error');
$returnurl = new moodle_url('/local/scheduleexam/index.php');
$PAGE->set_url('/local/scheduleexam/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pageheading', 'local_scheduleexam') . ':' . get_string('uploadexams', 'local_scheduleexam');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pageheading', 'local_scheduleexam'), new moodle_url('/local/scheduleexam/index.php'));
$PAGE->navbar->add(get_string('uploadexams', 'local_scheduleexam'));

global $USER, $DB;
// array of all valid fields for validation
$STD_FIELDS = array('examtype', 'schoolname', 'semestername', 'classname', 'opendate', 'starttimehour', 'starttimemin', 'endtimehour', 'endtimemin', 'lecturetype', 'grademin', 'grademax');

$PRF_FIELDS = array();
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_exam_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadexam');
        $cir = new csv_import_reader($iid, 'uploadexam'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('examfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_exam_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pageheading', 'local_scheduleexam'));
        //--------tab code----------------------
        $currenttab = 'upload';
        $ob = new schedule_exam();
        $ob->tabs($currenttab);
        //--------------------------------------------

        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('uploaddes', 'local_scheduleexam'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploadexam');
    $filecolumns = uu_validate_exam_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

//---creating  object for second form---------------------- 
$mform2 = new admin_exam_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadexamsresult', 'local_scheduleexam'));
    $currenttab = 'upload';
    $ob = new schedule_exam();
    $ob->tabs($currenttab);
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    // verification moved to two places: after upload and into form2
    $examsnew = 0;
    $examsupdated = 0;
    $examsuptodate = 0; //not printed yet anywhere
    $examserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $examsskipped = 0;
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
        $exam = new stdClass();
        // add fields to exam object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $exam->$key = $value;
        }
        if (!isset($exam->classname)) {
            // prevent warnings below
            $exam->classname = '';
        }
        //--------------------------------this is used to include only new exams-----------------
        if ($optype == UU_EXAM_ADDNEW) {
            // exam creation is a special case - the examname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($exam->classname) or $exam->classname === '') {
                $error = true;
            }
            if ($error) {
                $examserrors++;
                continue;
            }
        }
        // make sure we really have examname
        if (empty($exam->classname)) {
            $examserrors++;
            continue;
        }
        $fac = trim($exam->schoolname);
        $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
        if (empty($scid)) {
            echo "<h3 style='color:red;'>Invalid schoolname=>$exam->schoolname at line no=>$linenum.</h3>";
            goto loop;
        }
        $exam->classname = trim($exam->classname);
        $clsid = $DB->get_field('local_clclasses', 'id', array('fullname' => $exam->classname));
        if (empty($clsid)) {
            echo "<h3 style='color:red;'>Invalid classname=>$exam->classname at line no=>$linenum.</h3>";
            goto loop;
        }
        $lectureid = $DB->get_field('local_lecturetype', 'id', array('lecturetype' => $exam->lecturetype));
        if (empty($lectureid)) {
            echo "<h3 style='color:red;'>Invalid lecturetype=>$exam->lecturetype at line no=>$linenum.</h3>";
            goto loop;
        }
        $exam->examtype = trim($exam->examtype);
        $examids = $DB->get_records('local_examtypes', array('examtype' => $exam->examtype));
        if (empty($examids)) {
            echo "<h3 style='color:red;'>Invalid examtype=>$exam->examtype at line no=>$linenum.</h3>";
            goto loop;
        }
        $examid = $DB->get_field('local_examtypes', 'id', array('examtype' => $exam->examtype, 'schoolid' => $scid));
        if (empty($examid)) {
            echo "<h3 style='color:red;'>Examtype=>$exam->examtype entered at line no=>$linenum is not under school $exam->schoolname.</h3>";
            goto loop;
        }
        $lecid = $DB->get_field('local_lecturetype', 'id', array('lecturetype' => $exam->lecturetype, 'schoolid' => $scid));
        if (empty($examid)) {
            echo "<h3 style='color:red;'>Lecturetype=>$exam->lecturetype entered at line no=>$linenum is not under school $exam->schoolname.</h3>";
            goto loop;
        }
        $existingexam = $DB->get_record_sql("select * from {local_scheduledexams} where examtype = $examid and  classid = $clsid and lecturetype = $lecid");
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($exam->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($exam->$field)) {
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
            case UU_EXAM_ADDNEW:
                if ($existingexam) {
                    $examsskipped++;
                    $skip = true;
                }
                break;
            case UU_EXAM_ADD_UPDATE:
                break;
            case UU_EXAM_UPDATE:
                if (!$existingexam) {
                    $examsskipped++;
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
        if (!empty($existingexam)) {
            $exam->id = $existingexam->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($exam, $column) or ! property_exists($existingexam, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingexam->$column) and $existingexam->$column !== '') {
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
                    if ($existingexam->$column !== trim($exam->$column)) {
                        $existingexam->$column = $exam->$column;
                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {
                $hier = new hierarchy();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }
                $exams = manage_dept::getInstance();
                // ==============checking condition for schools=====================
                $fac = trim($exam->schoolname);
                $scid = $DB->get_field('local_school', 'id', array('fullname' => "$fac"));
                if (!$scid) {
                    echo '<h3 style="color:red;">Invalid school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $c = 0;
                foreach ($schools as $school) {
                    if ($school->id == $scid) {
                        ++$c;
                        break;
                    }
                }
                if ($c == 0) {
                    echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                // ************check semsters**********

                $exam->semestername = trim($exam->semestername);
                $sid = $DB->get_field('local_semester', 'id', array('fullname' => $exam->semestername));
                if (empty($sid)) {
                    echo '<h3 style="color:red;">Invalid semester "' . $exam->semester . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                if (!$sem = $DB->get_record('local_school_semester', array('schoolid' => $scid, 'semesterid' => $sid))) {
                    echo '<h3 style="color:green;">Assigned semester "' . $exam->semestername . '" is not under given School "' . $exam->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                // check class
                $e = 0;
                $exam->classname = trim($exam->classname);
                $clsid = $DB->get_field('local_clclasses', 'id', array('fullname' => $exam->classname));
                if (!clsid) {
                    echo '<h3 style="color:red;">Invalid class "' . $exam->classname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                if (!$clclasses = $DB->get_record('local_scheduleclass', array('schoolid' => $scid, 'semesterid' => $sid, 'classid' => $clsid))) {
                    echo '<h3 style="color:red;">Class "' . $exam->classname . '" is not scheduled for semester "' . $exam->semestername . '" or for school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                // check examtype
                if (!$examtype = $DB->get_record('local_examtypes', array('examtype' => $exam->examtype, 'schoolid' => $scid))) {
                    echo '<h3 style="color:red;">Assigned examtype "' . $exam->examtype . '" is not under given school "' . $exam->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                // check opendate
                $odate = strtotime($exam->opendate);
                $ex = explode('/', $exam->opendate);
                $true = checkdate($ex[0], $ex[1], $ex[2]);
                if ($true == false) {
                    echo '<h3 style="color:red;">Invalid  opendate "' . $exam->opendate . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                    goto loop;
                }
                $today = date('m/d/Y');
                $today = strtotime($today);
                if ($odate < $today) {
                    echo '<h3 style="color:red;">Open date should be greater than present date.</h3>';
                    goto loop;
                }
                $date = $DB->get_record('local_semester', array('id' => $sid));
                if ($date->enddate < time()) {
                    echo '<h3 style="color:red;">Semester "' . $exam->semestername . '" is already completed. You are creating supplementary exam.</h3>';
                    goto unchecked;
                }
                $check = (($date->startdate < $odate) && ($date->enddate > $odate));
                if (!$check) {
                    if ($date->enddate > time()) {
                        $date->startdate = date('m/d/Y', $date->startdate);
                        $date->enddate = date('m/d/Y', $date->enddate);
                        $errors = get_string('datevalidation', 'local_scheduleexam', $date);
                        echo '<h3 style="color:red;">' . $errors . '</h3>';
                        goto loop;
                    }
                }
                unchecked:
                // check start time and end time
                if (empty($exam->starttimehour) && $exam->starttimehour != 0) {
                    echo "<h3 style='color:red;'>Please enter Start time hour at line no $linenum.</h3>";
                    goto loop;
                }
                if ($exam->starttimehour > 23 || $exam->starttimehour < 0) {
                    echo "<h3 style='color:red;'>Invalid starttimehour entered at line no $linenum.</h3>";
                    goto loop;
                }
                if (empty($exam->endtimehour) && $exam->endtimehour != 0) {
                    echo "<h3 style='color:red;'>Please enter End time hour at line no $linenum.</h3>";
                    goto loop;
                }
                if ($exam->endtimehour > 23 || $exam->endtimehour < 0) {
                    echo "<h3 style='color:red;'>Invalid endtimehour entered at line no $linenum.</h3>";
                    goto loop;
                }
                if (empty($exam->starttimemin) && $exam->starttimemin != 0) {
                    echo "<h3 style='color:red;'>Please enter Start time min at line no $linenum.</h3>";
                    goto loop;
                }
                if ($exam->starttimemin > 59 || $exam->starttimemin < 0) {
                    echo "<h3 style='color:red;'>Invalid starttimemin entered at line no $linenum.</h3>";
                    goto loop;
                }

                if (empty($exam->endtimemin) && $exam->endtimemin != 0) {
                    echo "<h3 style='color:red;'>Please enter End time min at line no $linenum.</h3>";
                    goto loop;
                }
                if ($exam->endtimemin > 59 || $exam->endtimemin < 0) {
                    echo "<h3 style='color:red;'>Invalid endtimemin entered at line no $linenum.</h3>";
                    goto loop;
                }
                // check grade min and max
                if (empty($exam->grademin) && $exam->grademin != 0) {
                    echo "<h3 style='color:red;'>Please enter grademin at line no $linenum.</h3>";
                    goto loop;
                }
                if (empty($exam->grademax) && $exam->grademax != 0) {
                    echo "<h3 style='color:red;'>Please enter grademax at line no $linenum.</h3>";
                    goto loop;
                }
                if ($exam->grademax <= $exam->grademin) {
                    echo "<h3 style='color:red;'>Grademax should be greater than grademin.</h3>";
                    goto loop;
                }
                if ($exam->endtimemin == 0)
                    $exam->endtimemin = '00';
                if ($exam->endtimehour == 0)
                    $exam->endtimehour = '00';
                if ($exam->starttimehour == 0)
                    $exam->starttimehour = '00';
                if ($exam->starttimemin == 0)
                    $exam->starttimemin = '00';

                $lastime = $exam->endtimehour . $exam->endtimemin;
                $statime = $exam->starttimehour . $exam->starttimemin;
                if ($lastime <= $statime) {
                    echo "<h3 style='color:red;'>Exam end time must be greater than start time at line no : $linenum of excelsheet.</h3>";
                    goto loop;
                }

                $records = $DB->get_records_sql("SELECT * FROM {$CFG->prefix}local_scheduledexams WHERE schoolid = $scid AND
										   semesterid = $sid AND opendate = $odate AND id!=$existingexam->id");
                foreach ($records as $record) {
                    $record->starttimehour = ($record->starttimehour < 10) ? '0' . $record->starttimehour : $record->starttimehour;
                    $record->starttimemin = ($record->starttimemin < 10) ? '0' . $record->starttimemin : $record->starttimemin;
                    $record->endtimehour = ($record->endtimehour < 10) ? '0' . $record->endtimehour : $record->endtimehour;
                    $record->endtimemin = ($record->endtimemin < 10) ? '0' . $record->endtimemin : $record->endtimemin;

                    $starttime = $record->starttimehour . $record->starttimemin;
                    $endtime = $record->endtimehour . $record->endtimemin;
                    $thisstarttime = $exam->starttimehour . $exam->starttimemin;
                    $thisendtime = $exam->endtimehour . $exam->endtimemin;
                    //check if starttime or endtime of this is in the times of previous exams
                    if (($thisstarttime >= $starttime && $thisstarttime <= $endtime) || ($thisendtime >= $starttime && $thisendtime <= $endtime)) {
                        $errors = "<h3 style='color:red;'>An exam is already created from $record->starttimehour:$record->starttimemin to $record->endtimehour:$record->endtimemin for another class in this semester. Please change the timings at line no $linenum.</h3>";
                        echo $errors;
                        goto loop;
                    }
                }
                $existingexam->examtype = $examid;
                $existingexam->schoolid = $scid;
                $existingexam->programid = 0;
                $existingexam->semesterid = $sid;
                $existingexam->classid = $clsid;
                $existingexam->opendate = $odate;
                $existingexam->starttimehour = $exam->starttimehour;
                $existingexam->starttimemin = $exam->starttimemin;
                $existingexam->endtimehour = $exam->endtimehour;
                $existingexam->endtimemin = $exam->endtimemin;
                $existingexam->lecturetype = $lecid;
                $existingexam->grademin = $exam->grademin;
                $existingexam->grademax = $exam->grademax;
                $existingexam->examweightage = 0;
                $existingexam->visible = 1;
                $existingexam->timemodified = time();
                $existingexam->usermodified = $USER->id;
                $DB->update_record('local_scheduledexams', $existingexam);
                $examsupdated++;
            } else {
                // no exam information changed
                $examsuptodate++;
            }
        } else {     // else start for [!empty($existingexam]
            // save the new exam to the database
            $data = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            $exams = manage_dept::getInstance();
            // check schools
            $fac = $exam->schoolname;
            $scid = $DB->get_field('local_school', 'id', array('fullname' => "$fac"));
            if (!$scid) {
                echo '<h3 style="color:red;">Invalid school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $c = 0;
            foreach ($schools as $school) {
                if ($school->id == $scid) {
                    ++$c;
                    break;
                }
            }
            if ($c == 0) {
                echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            // ************check semsters**********
            $exam->semestername = trim($exam->semestername);
            $sid = $DB->get_field('local_semester', 'id', array('fullname' => $exam->semestername));
            if (empty($sid)) {
                echo '<h3 style="color:red;">Invalid semester "' . $exam->semester . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }
            if (!$sem = $DB->get_record('local_school_semester', array('schoolid' => $scid, 'semesterid' => $sid))) {
                echo '<h3 style="color:green;">Assigned semester "' . $exam->semestername . '" is not under given School "' . $exam->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }
            // check class
            $e = 0;
            $exam->classname = trim($exam->classname);
            $clsid = $DB->get_field('local_clclasses', 'id', array('fullname' => $exam->classname));
            if (!clsid) {
                echo '<h3 style="color:red;">Invalid class "' . $exam->classname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }
            if (!$clclasses = $DB->get_record('local_scheduleclass', array('schoolid' => $scid, 'semesterid' => $sid, 'classid' => $clsid))) {
                echo '<h3 style="color:red;">Class "' . $exam->classname . '" is not scheduled for semester "' . $exam->semestername . '" or for school "' . $exam->schoolname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }

            // check examtype
            if (!$examtype = $DB->get_record('local_examtypes', array('examtype' => $exam->examtype, 'schoolid' => $scid))) {
                echo '<h3 style="color:red;">Assigned examtype "' . $exam->examtype . '" is not under given school "' . $exam->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }

            // check opendate
            $odate = strtotime($exam->opendate);
            $ex = explode('/', $exam->opendate);
            $true = checkdate($ex[0], $ex[1], $ex[2]);
            if ($true == false) {
                echo '<h3 style="color:red;">Invalid  opendate "' . $exam->opendate . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                goto loop;
            }
            $today = date('m/d/Y');
            $today = strtotime($today);
            if ($odate < $today) {
                echo '<h3 style="color:red;">Open date should be greater than present date.</h3>';
                goto loop;
            }
            $date = $DB->get_record('local_semester', array('id' => $sid));
            if ($date->enddate < time()) {
                echo '<h3 style="color:red;">Semester "' . $exam->semestername . '" is already completed. You are creating supplementary exam.</h3>';
                goto uncheck;
            }
            $check = (($date->startdate < $odate) && ($date->enddate > $odate));
            if (!$check) {
                if ($date->enddate > time()) {
                    $date->startdate = date('m/d/Y', $date->startdate);
                    $date->enddate = date('m/d/Y', $date->enddate);
                    $errors = get_string('datevalidation', 'local_scheduleexam', $date);
                    echo '<h3 style="color:red;">' . $errors . '</h3>';
                    goto loop;
                }
            }
            uncheck:
            // check start time and end time
            if (empty($exam->starttimehour) && $exam->starttimehour != 0) {
                echo "<h3 style='color:red;'>Please enter Start time hour at line no $linenum.</h3>";
                goto loop;
            }
            if ($exam->starttimehour > 23 || $exam->starttimehour < 0) {
                echo "<h3 style='color:red;'>Invalid starttimehour entered at line no $linenum.</h3>";
                goto loop;
            }
            if (empty($exam->endtimehour) && $exam->endtimehour != 0) {
                echo "<h3 style='color:red;'>Please enter End time hour at line no $linenum.</h3>";
                goto loop;
            }
            if ($exam->endtimehour > 23 || $exam->endtimehour < 0) {
                echo "<h3 style='color:red;'>Invalid endtimehour entered at line no $linenum.</h3>";
                goto loop;
            }
            if (empty($exam->starttimemin) && $exam->starttimemin != 0) {
                echo "<h3 style='color:red;'>Please enter Start time min at line no $linenum.</h3>";
                goto loop;
            }
            if ($exam->starttimemin > 59 || $exam->starttimemin < 0) {
                echo "<h3 style='color:red;'>Invalid starttimemin entered at line no $linenum.</h3>";
                goto loop;
            }

            if (empty($exam->endtimemin) && $exam->endtimemin != 0) {
                echo "<h3 style='color:red;'>Please enter End time min at line no $linenum.</h3>";
                goto loop;
            }
            if ($exam->endtimemin > 59 || $exam->endtimemin < 0) {
                echo "<h3 style='color:red;'>Invalid endtimemin entered at line no $linenum.</h3>";
                goto loop;
            }
            // check grade min and max
            if (empty($exam->grademin) && $exam->grademin != 0) {
                echo "<h3 style='color:red;'>Please enter grademin at line no $linenum.</h3>";
                goto loop;
            }
            if (empty($exam->grademax) && $exam->grademax != 0) {
                echo "<h3 style='color:red;'>Please enter grademax at line no $linenum.</h3>";
                goto loop;
            }
            if ($exam->grademax <= $exam->grademin) {
                echo "<h3 style='color:red;'>Grademax should be greater than grademin.</h3>";
                goto loop;
            }
            if ($exam->endtimemin == 0)
                $exam->endtimemin = '00';
            if ($exam->endtimehour == 0)
                $exam->endtimehour = '00';
            if ($exam->starttimehour == 0)
                $exam->starttimehour = '00';
            if ($exam->starttimemin == 0)
                $exam->starttimemin = '00';

            $lastime = $exam->endtimehour . $exam->endtimemin;
            $statime = $exam->starttimehour . $exam->starttimemin;
            if ($lastime <= $statime) {
                echo "<h3 style='color:red;'>Exam end time must be greater than start time at line no : $linenum of excelsheet.</h3>";
                goto loop;
            }
            $records = $DB->get_records_sql("SELECT * FROM {local_scheduledexams} WHERE schoolid = $scid AND
            semesterid = $sid AND opendate = $odate");
            foreach ($records as $record) {
                $record->starttimehour = ($record->starttimehour < 10) ? '0' . $record->starttimehour : $record->starttimehour;
                $record->starttimemin = ($record->starttimemin < 10) ? '0' . $record->starttimemin : $record->starttimemin;
                $record->endtimehour = ($record->endtimehour < 10) ? '0' . $record->endtimehour : $record->endtimehour;
                $record->endtimemin = ($record->endtimemin < 10) ? '0' . $record->endtimemin : $record->endtimemin;
                $starttime = $record->starttimehour . $record->starttimemin;
                $endtime = $record->endtimehour . $record->endtimemin;
                $thisstarttime = $exam->starttimehour . $exam->starttimemin;
                $thisendtime = $exam->endtimehour . $exam->endtimemin;
                //check if starttime or endtime of this is in the times of previous exams
                if (($thisstarttime >= $starttime && $thisstarttime <= $endtime) || ($thisendtime >= $starttime && $thisendtime <= $endtime)) {
                    $errors = "An exam is already created from $record->starttimehour:$record->starttimemin to $record->endtimehour:$record->endtimemin for another class in this semester. Please change the timings at line no =>$linenum.";
                    echo "<h3 style='color:red;'>" . $errors . "</h3>";
                    goto loop;
                }
            }
            $data->examtype = $examid;
            $data->schoolid = $scid;
            $data->programid = 0;
            $data->semesterid = $sid;
            $data->classid = $clsid;
            $data->opendate = $odate;
            $data->starttimehour = $exam->starttimehour;
            $data->starttimemin = $exam->starttimemin;
            $data->endtimehour = $exam->endtimehour;
            $data->endtimemin = $exam->endtimemin;
            $data->lecturetype = $lecid;
            $data->grademin = $exam->grademin;
            $data->grademax = $exam->grademax;
            $data->examweightage = 0;
            $data->visible = 1;
            $data->timecreated = time();
            $data->timemodified = 0;
            $data->usermodified = $USER->id;
            $data->id = $DB->insert_record('local_scheduledexams', $data);
            $data->id++;
            $examsnew++;
        }
    }
    // $upt->close(); // close table
    // $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_EXAM_UPDATE) {
        echo get_string('examscreated', 'local_scheduleexam') . ': ' . $examsnew . '<br />';
    }
    if ($optype == UU_EXAM_UPDATE or $optype == UU_EXAM_ADD_UPDATE) {
        echo get_string('examsupdated', 'local_scheduleexam') . ': ' . $examsupdated . '<br />';
    }
    if ($examsskipped) {
        echo get_string('examsskipped', 'local_scheduleexam') . ': ' . $examsskipped . '<br />';
    }
    if ($examserrors)
        echo get_string('errors', 'local_scheduleexam') . ': ' . $examserrors;
    echo '</p>';
    echo $OUTPUT->box_end();
    if ($optype == UU_EXAM_UPDATE or $optype == UU_EXAM_ADD_UPDATE) {
        if ($examsnew == 0 && $examsupdated == 0 && $examsskipped = 0)
            echo '<h4> No changes occured in Excel sheet.</h4>';
    }
    if ($examserrors) {
        echo '<h4> Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
    }
    if ($examsskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($examsskipped == 1)
            echo '<h4> Exam skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $examsskipped . ' Exams skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadexamspreview', 'local_scheduleexam'));
$currenttab = 'upload';
$ob = new schedule_exam();
$ob->tabs($currenttab);
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;
