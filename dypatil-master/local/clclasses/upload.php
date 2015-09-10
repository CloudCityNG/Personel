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
 * Bulk class registration script from a comma separated file
 *
 * @package    local
 * @subpackage classs
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
require_once('upload_classes_lib.php');
require_once('upload_classes_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
@set_time_limit(60 * 60); /* ---1 hour should be enough--- */
raise_memory_limit(MEMORY_HUGE);
require_login();
$strclassupdated = get_string('classaccountupdated', 'local_clclasses');
$strclassuptodate = get_string('classaccountuptodate', 'local_clclasses');
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$returnurl = new moodle_url('/local/clclasses/index.php');
$PAGE->set_url('/local/clclasses/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('manageclasses', 'local_clclasses') . ' : ' . get_string('uploadclasss', 'local_clclasses');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_clclasses'), new moodle_url('/local/classs/index.php'));
$PAGE->navbar->add(get_string('uploadclasss', 'local_clclasses'));
global $USER, $DB;
/* ---array of all valid fields for validation--- */
$STD_FIELDS = array('schoolname', 'semestername', 'fullname', 'shortname', 'description', 'cobaltcourse', 'startdate', 'enddate', 'classlimit', 'lecturetype', 'department1', 'department2', 'instructor', 'starttime', 'endtime', 'classroom');

$PRF_FIELDS = array();
/* ---if variable $iid equal to zero,it allows enter into the form--- */
if (empty($iid)) {
    $mform1 = new admin_class_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadclass');
        $cir = new csv_import_reader($iid, 'uploadclass'); //this class fromcsvlib.php(includes csv methods and classes)
        $content = $mform1->get_file_content('classfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        /* ---test if columns ok(to validate the csv file content)--- */
        $filecolumns = uu_validate_class_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        /* ---continue to form2--- */
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manageclasses', 'local_clclasses'));
        /* ---tab code----- */
        $currenttab = 'upload';
        $ob = new schoolclasses();
        $ob->print_classestabs($currenttab);


        if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('uploaddes', 'local_clclasses'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>Sample Excel Sheet</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>Help Manual</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    /* ---if not empty of variable $iid,it get the content from the csv content--- */
    $cir = new csv_import_reader($iid, 'uploadclass');
    $filecolumns = uu_validate_class_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

/* ---creating  object for second form--- */
$mform2 = new admin_class_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
/* ---If a file has been uploaded, then process it--- */
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    /* ---Print the header--- */
    echo $OUTPUT->header();

    echo $OUTPUT->heading(get_string('uploadclasssresult', 'local_clclasses'));
    $currenttab = 'upload';
    $ob = new schoolclasses();
    $ob->print_classestabs($currenttab);
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    /* ---verification moved to two places: after upload and into form2--- */
    $classsnew = 0;
    $classsupdated = 0;
    $classsuptodate = 0; //not printed yet anywhere---*/
    $classserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $classsskipped = 0;


    /* ---init csv import helper--- */
    $cir->init();
    $linenum = 1;
    /* ---init upload progress tracker-this class used to keeping track of code(each rows and columns)--- */
    $upt = new uu_progress_tracker();
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;
        $class = new stdClass();
        /* ---add fields to class object--- */
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                /* ---this should not happen--- */
                continue;
            }
            $key = $filecolumns[$keynum];
            $class->$key = $value;
        }
        if (!isset($class->fullname)) {
            /* ---prevent warnings below--- */
            $class->classname = '';
        }
        /* ---this is used to include only new classs------ */
        if ($optype == UU_CLASS_ADDNEW) {
            /* ---class creation is a special case - the classname may be constructed from templates using firstname and lastname
              better never try this in mixed update types--- */
            $error = false;
            if (!isset($class->fullname) or $class->fullname === '') {
                $error = true;
            }

            if ($error) {
                $classserrors++;
                continue;
            }
        }

        /* ---make sure we really have classname--- */
        if (empty($class->fullname)) {
            $classserrors++;
            continue;
        }
        $class->semestername = trim($class->semestername);
        $sid = $DB->get_field('local_semester', 'id', array('fullname' => $class->semestername));
        if (empty($sid)) {
            echo "Invalid semestername=>$class->semestername at line no=>$linenum";
            goto loop;
        }
        $class->fullname = trim($class->fullname);
        $existingclass = $DB->get_record_sql("select * from {local_clclasses} where fullname = '$class->fullname' and semesterid = $sid");
        /* ---add default values for remaining fields--- */
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($class->$field)) {
                continue;
            }
            /* ---all validation moved to form2--- */
            if (isset($formdata->$field)) {
                /* ---process templates--- */
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($class->$field)) {
                continue;
            }
            if (isset($formdata->$field)) {
                /* ---process templates--- */
                $formdefaults[$field] = true;
            }
        }
        /* ---can we process with update or insert--- */
        $skip = false;
        switch ($optype) {
            case UU_CLASS_ADDNEW:
                if ($existingclass) {
                    $classsskipped++;
                    $skip = true;
                }
                break;
            case UU_CLASS_ADD_UPDATE:
                break;
            case UU_CLASS_UPDATE:
                if (!$existingclass) {
                    $classsskipped++;
                    $skip = true;
                }
                break;
            default:

                $skip = true;
        }

        if ($skip) {
            continue;
        }
        if (!empty($existingclass)) {
            $class->id = $existingclass->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($class, $column) or !property_exists($existingclass, $column)) {
                        /* ---this should never happen--- */
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingclass->$column) and $existingclass->$column !== '') {
                            continue;
                        }
                    } else if ($updatetype == UU_UPDATE_ALLOVERRIDE) {
                        /* ---we override everything--- */
                    } else if ($updatetype == UU_UPDATE_FILEOVERRIDE) {
                        if (!empty($formdefaults[$column])) {
                            /* ---do not override with form defaults--- */
                            continue;
                        }
                    }
                    if ($existingclass->$column !== $class->$column) {
                        $existingclass->$column = $class->$column;
                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {
                $existingclass2 = $DB->get_record_sql('select * from {local_scheduleclass} where classid =' . $existingclass->id . ' and semesterid = ' . $sid . '');
                $data = new stdclass();
                $hier = new hierarchy();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }
                $classs = manage_dept::getInstance();
                /* ---check  schools--- */
                $fac = trim($class->schoolname);
                $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
                if (empty($scid)) {
                    echo '<h3 style="color:red;">Invalid school "' . $class->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
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
                    echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $class->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                /* ---check semesters--- */
                $sid = $DB->get_field('local_semester', 'id', array('fullname' => $class->semestername));
                $sql = "SELECT * from {local_school_semester} where schoolid = {$scid} and semesterid = {$sid}";
                $semesters = $DB->get_records_sql($sql);
                if (empty($semesters)) {
                    echo '<h3 style="color:red;">Assigned semester "' . $class->semestername . '" is not under given School "' . $class->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }
                /* ---check shortname--- */
                $g = 0;
                $class->shortname = trim($class->shortname);
                if (empty($class->shortname)) {
                    echo '<h3 style="color:red;">Please enter shortname  in line no."' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $sql = "select * from {local_clclasses} where fullname = '$class->fullname' and shortname = '$class->shortname'";
                $result = $DB->get_records_sql($sql);
                if (empty($result)) {
                    $shortnames = $DB->get_records_sql('select shortname from {local_clclasses}');
                    foreach ($shortnames as $shortname) {
                        $str1 = trim($shortname->shortname);
                        $str2 = trim($class->shortname);
                        $cmp = strcasecmp($str1, $str2);
                        if ($cmp == 0) {
                            ++$g;
                            break;
                        }
                    }
                    if ($g != 0) {
                        echo '<h3 style="color:red;">Short name "' . $class->shortname . '"  at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                        goto loop;
                    }
                }
                /* ---check startdate and end date--- */
                $startdate = strtotime($class->startdate);
                $enddate = strtotime($class->enddate);
                $std = explode('/', $class->startdate);
                if (!checkdate($std[0], $std[1], $std[2])) {
                    echo '<h3 style="color:red;">Invalid  startdate "' . $class->startdate . '"  format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                    goto loop;
                }
                $end = explode('/', $class->enddate);
                if (!checkdate($end[0], $end[1], $end[2])) {
                    echo '<h3 style="color:red;">Invalid  enddate "' . $class->enddate . '"  format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                    goto loop;
                }
                $today = date('m/d/Y');
                $today = strtotime($today);
                if (empty($class->startdate)) {
                    echo '<h3 style="color:red;">Please enter startdate of class in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                if (empty($class->enddate)) {
                    echo '<h3 style="color:red;">Please enter enddate of class in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                if ($startdate < $today) {
                    echo '<h3 style="color:red;">Start date should not be less than today date in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                if ($startdate > $enddate) {
                    echo '<h3 style="color:red;">Start Date should be less than End Date in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                /* ---check start time and end time--- */
                $s = date('H:i');
                $prstime = strtotime($s);
                $strtime = strtotime($class->starttime);
                $endtime = strtotime($class->endtime);

                if (empty($class->starttime)) {
                    echo '<h3 style="color:red;">Please enter starttime of class in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                if (empty($class->endtime)) {
                    echo '<h3 style="color:red;">Please enter endtime of class in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                if ($today == $class->startdate) {
                    if ($prstime >= $strtime) {
                        echo '<h3 style="color:red;">Start time should be greater than present time in line number ' . $linenum . ' of excel sheet.</h3>';
                        goto loop;
                    }

                    if ($prstime >= $endtime) {
                        echo '<h3 style="color:red;">End time should be greater than present time in line number ' . $linenum . ' of excel sheet.</h3>';
                        goto loop;
                    }
                }
                if ($strtime >= $endtime) {
                    echo '<h3 style="color:red;">Start time should be less than end time in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }

                /* ---check departments--- */
                $departs = $DB->get_records('local_department', array('schoolid' => $scid));
                $depts = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $scid");
                $departs = $departs + $depts;
                $count = 0;
                $dept = $class->department1;
                $mydept = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept'");
                if (empty($mydept)) {
                    echo '<h3 style="color:red;">Invalid department "' . $class->department1 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (!empty($mydept)) {
                    $depid = $mydept->id;
                }
                foreach ($departs as $depart) {
                    if ($depart->id == $depid) {
                        ++$count;
                        break;
                    }
                }
                if ($count == 0) {
                    echo '<h3 style="color:red;">Department "' . $class->department1 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                    goto loop;
                }
                /* ---check department2--- */
                $count2 = 0;
                $dept2 = $class->department2;
                $mydept2 = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept2'");
                if (empty($mydept2)) {
                    echo '<h3 style="color:red;">Invalid department "' . $class->department2 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (!empty($mydept2)) {
                    $depid2 = $mydept2->id;
                }
                foreach ($departs as $depart) {
                    if ($depart->id == $depid2) {
                        ++$count2;
                        break;
                    }
                }
                if ($count2 == 0) {
                    echo '<h3 style="color:red;">Department "' . $class->department2 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                    goto loop;
                }
                /* ---check lecture type--- */
                $ltypes = $DB->get_records_sql("select id from {local_lecturetype} where lecturetype = '$class->lecturetype' ");
                if (empty($ltypes)) {
                    echo '<h3 style="color:red;">Invalid lecturetype "' . $class->lecturetype . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $ltype = $DB->get_record_sql("select id from {local_lecturetype} where lecturetype = '$class->lecturetype' and schoolid = $scid");
                if (empty($ltype)) {
                    echo '<h3 style="color:red;">Lecture Type "' . $class->lecturetype . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                    goto loop;
                }
                /* ---check instructor--- */
                $instruct = $DB->get_field('user', 'id', array('username' => $class->instructor));
                if (empty($instruct)) {
                    echo '<h3 style="color:red;">Unknown instructor "' . $class->instructor . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $existinstructor = $DB->record_exists('local_dept_instructor', array('instructorid' => $instruct, 'departmentid' => $depid2, 'schoolid' => $scid));
                if (!$existinstructor) {
                    echo '<h3 style="color:red;">Instructor "' . $class->instructor . '" entered at line no. "' . $linenum . '" of uploaded excelsheet not belongs to department "' . $class->department2 . '".</h3>';
                    goto loop;
                }
                /* ---check course--- */
                $courseid = $DB->get_field('local_cobaltcourses', 'id', array('fullname' => $class->cobaltcourse));
                if (empty($courseid)) {
                    echo '<h3 style="color:red;">Invalid cobaltcourse "' . $class->cobaltcourse . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $existcourse = $DB->get_records_sql('select id from {local_cobaltcourses} where id = ' . $courseid . ' and schoolid=' . $scid . ' and departmentid = ' . $mydept->id . '');
                if (empty($existcourse)) {
                    echo '<h3 style="color:red;">Course "' . $class->cobaltcourse . '"  entered at line no. "' . $linenum . '" of uploaded excelsheet is not under department=>' . $class->department1 . '.</h3>';
                    goto loop;
                }
                /* ---check limit of class--- */
                if (empty($class->classlimit)) {
                    echo '<h3 style="color:red;">Please enter limit of class members in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
                /* ---check classroom--- */
                $classroomid = $DB->get_field('local_classroom', 'id', array('fullname' => $class->classroom, 'schoolid' => $scid));
                if (empty($classroomid)) {
                    echo '<h3 style="color:red;">Room name entered in line number ' . $linenum . ' of excel sheet is not exists.</h3>';
                    goto loop;
                }
                $rooms = roomlist($startdate, $enddate, $strtime, $endtime, $scid, $existingclass->id);
                $h = 0;
                foreach ($rooms as $key => $value) {
                    if ($key == $classroomid) {
                        $h++;
                        break;
                    }
                }
                if ($h == 0) {
                    echo '<h3 style="color:red;">Please enter valid room name in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }

                $existingclass->schoolid = $scid;
                $existingclass->semesterid = $sid;
                $existingclass->fullname = $class->fullname;
                $existingclass->shortname = $class->shortname;
                $existingclass->description = $class->description;
                $existingclass->cobaltcourseid = $courseid;
                $existingclass->startdate = strtotime($class->startdate);
                $existingclass->enddate = strtotime($class->enddate);
                $existingclass->classlimit = $class->classlimit;
                $existingclass->type = $ltype->id;
                $existingclass->online = 2;
                $existingclass->credithours = 0;
                $existingclass->waitinglist = 0;
                $existingclass->onlinecourse = 0;
                $existingclass->studentlevel = 0;
                $existingclass->departmentid = $depid;
                $existingclass->visible = 1;
                $existingclass->timemodified = time();
                $existingclass->usermodified = $USER->id;
                $DB->update_record('local_clclasses', $existingclass);

                $existingclass2->departmentinid = $depid2;
                $existingclass2->instructorid = $instruct;
                $existingclass2->classid = $existingclass->id;
                $existingclass2->schoolid = $scid;
                $existingclass2->semesterid = $sid;
                $existingclass2->courseid = $courseid;
                $existingclass2->classroomid = $classroomid;
                $existingclass2->sectionid = NULL;
                $existingclass2->startdate = strtotime($class->startdate);
                $existingclass2->enddate = strtotime($class->enddate);
                $existingclass2->starttime = $class->starttime;
                $existingclass2->endtime = $class->endtime;
                $existingclass2->visible = 1;
                $existingclass2->usermodified = $USER->id;
                $existingclass2->timemodified = time();
                $DB->update_record('local_scheduleclass', $existingclass2);
                $classsupdated++;
            } else {

                $classsuptodate++;
            }
        } else {
            $data = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            $classs = manage_dept::getInstance();
            /* ---check  schools--- */
            $fac = trim($class->schoolname);
            $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
            if (empty($scid)) {
                echo '<h3 style="color:red;">Invalid school "' . $class->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
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
                echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $class->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            /* ---check semesters --- */
            $sid = $DB->get_field('local_semester', 'id', array('fullname' => $class->semestername));
            $sql = "SELECT * from {local_school_semester} where schoolid = {$scid} and semesterid = {$sid}";
            $semesters = $DB->get_records_sql($sql);
            if (empty($semesters)) {
                echo '<h3 style="color:red;">Assigned semester "' . $class->semestername . '" is not under given School "' . $class->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                goto loop;
            }
            /* ---check fullname of class--- */
            $fullname = strtolower($class->fullname);
            $names = $DB->get_records_sql('SELECT fullname from {local_clclasses} where semesterid=' . $sid . '');
            $f = 0;
            foreach ($names as $name) {
                $fname = strtolower($name->fullname);
                if ($fname === $fullname) {
                    $f++;
                    break;
                }
            }
            if ($f > 0) {
                echo '<h3 style="color:red;">Fullname "' . $class->fullname . '" entered  at line no. "' . $linenum . '" of excelsheet is alredy exists.</h3>';
                goto loop;
            }
            /* ---check shortname--- */
            $shortname = strtolower($class->shortname);
            $snames = $DB->get_records_sql('SELECT shortname from {local_clclasses} where semesterid=' . $sid . '');
            $g = 0;
            foreach ($snames as $sname) {
                $str1 = trim($sname->shortname);
                $str2 = trim($shortname);
                $compare = strcasecmp($str1, $str2);
                if ($compare == 0) {
                    ++$g;
                    break;
                }
            }
            if ($g > 0) {
                echo '<h3 style="color:red;">Shortname "' . $class->shortname . '" entered  at line no. "' . $linenum . '" of excelsheet is alredy exists.</h3>';
                goto loop;
            }

            /* ---check startdate and end date--- */
            $startdate = strtotime($class->startdate);
            $enddate = strtotime($class->enddate);
            $std = explode('/', $class->startdate);
            if (!checkdate($std[0], $std[1], $std[2])) {
                echo '<h3 style="color:red;">Invalid  startdate "' . $class->startdate . '"  format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                goto loop;
            }
            $end = explode('/', $class->enddate);
            if (!checkdate($end[0], $end[1], $end[2])) {
                echo '<h3 style="color:red;">Invalid  enddate "' . $class->enddate . '"  format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                goto loop;
            }
            $today = date('m/d/Y');
            $today = strtotime($today);
            if (empty($class->startdate)) {
                echo '<h3 style="color:red;">Please enter startdate of class in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            if (empty($class->enddate)) {
                echo '<h3 style="color:red;">Please enter enddate of class in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            if ($startdate < $today) {
                echo '<h3 style="color:red;">Start date should not be less than today date in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            if ($startdate > $enddate) {
                echo '<h3 style="color:red;">Start Date should be less than End Date in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            /* ---check start time and end time--- */
            $s = date('H:i');
            $prstime = strtotime($s);
            $strtime = strtotime($class->starttime);
            $endtime = strtotime($class->endtime);


            if (empty($class->starttime)) {
                echo '<h3 style="color:red;">Please enter starttime of class in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            if (empty($class->endtime)) {
                echo '<h3 style="color:red;">Please enter endtime of class in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            if ($today == $class->startdate) {
                if ($prstime >= $strtime) {
                    echo '<h3 style="color:red;">Start time should be greater than present time in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }

                if ($prstime >= $endtime) {
                    echo '<h3 style="color:red;">End time should be greater than present time in line number ' . $linenum . ' of excel sheet.</h3>';
                    goto loop;
                }
            }
            if ($strtime >= $endtime) {
                echo '<h3 style="color:red;">Start time should be less than end time in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }

            /* ---check departments--- */
            $departs = $DB->get_records('local_department', array('schoolid' => $scid));
            $depts = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $scid");
            $departs = $departs + $depts;
            $count = 0;
            $dept = $class->department1;
            $mydept = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept'");
            if (empty($mydept)) {
                echo '<h3 style="color:red;">Invalid department "' . $class->department1 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!empty($mydept)) {
                $depid = $mydept->id;
            }
            foreach ($departs as $depart) {
                if ($depart->id == $depid) {
                    ++$count;
                    break;
                }
            }
            if ($count == 0) {
                echo '<h3 style="color:red;">Department "' . $class->department1 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                goto loop;
            }

            /* ---check department2--- */
            $count2 = 0;
            $dept2 = $class->department2;
            $mydept2 = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept2'");
            if (empty($mydept2)) {
                echo '<h3 style="color:red;">Invalid department "' . $class->department2 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (!empty($mydept2)) {
                $depid2 = $mydept2->id;
            }
            foreach ($departs as $depart) {
                if ($depart->id == $depid2) {
                    ++$count2;
                    break;
                }
            }
            if ($count2 == 0) {
                echo '<h3 style="color:red;">Department "' . $class->department2 . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                goto loop;
            }
            /* ---check lecture type--- */
            $ltypes = $DB->get_records_sql("select id from {local_lecturetype} where lecturetype = '$class->lecturetype' ");
            if (empty($ltypes)) {
                echo '<h3 style="color:red;">Invalid lecturetype "' . $class->lecturetype . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $ltype = $DB->get_record_sql("select id from {local_lecturetype} where lecturetype = '$class->lecturetype' and schoolid = $scid");
            if (empty($ltype)) {
                echo '<h3 style="color:red;">Lecture Type "' . $class->lecturetype . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $class->schoolname . '".</h3>';
                goto loop;
            }
            /* ---check instructor--- */
            $instruct = $DB->get_field('user', 'id', array('username' => $class->instructor));
            if (empty($instruct)) {
                echo '<h3 style="color:red;">Unknown instructor "' . $class->instructor . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $existinstructor = $DB->record_exists('local_dept_instructor', array('instructorid' => $instruct, 'departmentid' => $depid2, 'schoolid' => $scid));
            if (!$existinstructor) {
                echo '<h3 style="color:red;">Instructor "' . $class->instructor . '" entered at line no. "' . $linenum . '" of uploaded excelsheet not belongs to department "' . $class->department2 . '".</h3>';
                goto loop;
            }
            /* ---check course--- */
            $courseid = $DB->get_field('local_cobaltcourses', 'id', array('fullname' => $class->cobaltcourse));
            if (empty($courseid)) {
                echo '<h3 style="color:red;">Invalid cobaltcourse "' . $class->cobaltcourse . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $existcourse = $DB->get_records_sql('select id from {local_cobaltcourses} where id = ' . $courseid . ' and schoolid=' . $scid . ' and departmentid = ' . $mydept->id . '');
            if (empty($existcourse)) {
                echo '<h3 style="color:red;">Course "' . $class->cobaltcourse . '"  entered at line no. "' . $linenum . '" of uploaded excelsheet is not under department=>' . $class->department1 . '.</h3>';
                goto loop;
            }
            /* ---check limit of class--- */
            if (empty($class->classlimit)) {
                echo '<h3 style="color:red;">Please enter limit of class members in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }
            /* ---check classroom--- */
            $classroomid = $DB->get_field('local_classroom', 'id', array('fullname' => $class->classroom, 'schoolid' => $scid));
            if (empty($classroomid)) {
                echo '<h3 style="color:red;"> Room name entered in line number ' . $linenum . ' of excel sheet does not exists.</h3>';
                goto loop;
            }
            $rooms = roomlist($startdate, $enddate, $strtime, $endtime, $scid, -1);
            $h = 0;
            foreach ($rooms as $key => $value) {
                if ($key == $classroomid) {
                    $h++;
                    break;
                }
            }
            if ($h == 0) {
                echo '<h3 style="color:red;">Please enter valid room name in line number ' . $linenum . ' of excel sheet.</h3>';
                goto loop;
            }

            $data->schoolid = $scid;
            $data->semesterid = $sid;
            $data->fullname = $class->fullname;
            $data->shortname = $class->shortname;
            $data->description = $class->description;
            $data->cobaltcourseid = $courseid;
            $data->startdate = strtotime($class->startdate);
            $data->enddate = strtotime($class->enddate);
            $data->classlimit = $class->classlimit;
            $data->type = $ltype->id;
            $data->online = 2;
            $data->credithours = 0;
            $data->waitinglist = 0;
            $data->onlinecourse = 0;
            $data->studentlevel = 0;
            $data->departmentid = $depid;
            $data->visible = 1;
            $data->timecreated = time();
            $data->timemodified = 0;
            $data->usermodified = $USER->id;
            $data->id = $DB->insert_record('local_clclasses', $data);
            $data2->departmentinid = $depid2;
            $data2->instructorid = $instruct;
            $data2->classid = $data->id;
            $data2->schoolid = $scid;
            $data2->semesterid = $sid;
            $data2->courseid = $courseid;
            $data2->classroomid = $classroomid;
            $data2->sectionid = NULL;
            $data2->startdate = strtotime($class->startdate);
            $data2->enddate = strtotime($class->enddate);
            $data2->starttime = $class->starttime;
            $data2->endtime = $class->endtime;
            $data2->visible = 1;
            $data2->usermodified = $USER->id;
            $data2->timecreated = time();
            $data2->timemodified = 0;
            $data2->id = $DB->insert_record('local_scheduleclass', $data2);
            $data->id++;
            $data2->id++;
            $classsnew++;
        }
    }

    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_CLASS_UPDATE) {
        echo get_string('classscreated', 'local_clclasses') . ': ' . $classsnew . '<br />';
    }
    if ($optype == UU_CLASS_UPDATE or $optype == UU_CLASS_ADD_UPDATE) {
        echo get_string('classsupdated', 'local_clclasses') . ': ' . $classsupdated . '<br />';
    }
    if ($classsskipped) {
        echo get_string('classsskipped', 'local_clclasses') . ': ' . $classsskipped . '<br />';
    }
    echo get_string('errors', 'local_clclasses') . ': ' . $classserrors . '</p>';
    echo $OUTPUT->box_end();

    if ($classsskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($classsskipped == 1)
            echo '<h4> Class skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $classsskipped . ' Classes skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="upload.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
/* ---Print the header--- */


echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('uploadclassspreview', 'local_clclasses'));
$currenttab = 'upload';
$ob = new schoolclasses();
$ob->print_classestabs($currenttab);
/* ---Print the form if valid values are available--- */
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

