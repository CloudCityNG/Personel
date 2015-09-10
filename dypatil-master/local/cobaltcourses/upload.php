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
 * Bulk course registration script from a comma separated file
 *
 * @package    local
 * @subpackage departments
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('upload_courses_lib.php');
require_once('upload_courses_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);

@set_time_limit(60 * 60);
raise_memory_limit(MEMORY_HUGE);

require_login();

$strcourseupdated = get_string('courseaccountupdated', 'local_cobaltcourses');
$strcourseuptodate = get_string('courseaccountuptodate', 'local_cobaltcourses');
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);

$returnurl = new moodle_url('/local/cobaltcourses/index.php');
$systemcontext = context_system::instance();
if (!has_capability('local/cobaltcourses:manage', $systemcontext))
    print_error('nopermissions', 'error');
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/cobaltcourses/upload.php');
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_cobaltcourses');
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('uploadcourses', 'local_cobaltcourses'));

$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('uploadcourses', 'local_cobaltcourses'));

global $USER, $DB;
/* ---array of all valid fields for validation--- */
$STD_FIELDS = array('fullname', 'shortname', 'departmentname', 'schoolname', 'summary', 'coursetype', 'credithours', 'coursecost');

$PRF_FIELDS = array();
/* ---if variable $iid equal to zero,it allows enter into the form--- */
if (empty($iid)) {
    $mform1 = new admin_course_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadcourse');
        $cir = new csv_import_reader($iid, 'uploadcourse');

        $content = $mform1->get_file_content('coursefile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }

        $filecolumns = uu_validate_course_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        /* ---continue to form2--- */
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_cobaltcourses'));
        /* ---Current tab--- */
        $currenttab = 'upload';
        /* ---adding tabs--- */
        createtabview($currenttab);
        echo $OUTPUT->box(get_string('helpdestab', 'local_cobaltcourses'));
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    /* ---if not empty of variable $iid,it get the content from the csv content--- */
    $cir = new csv_import_reader($iid, 'uploadcourse');
    $filecolumns = uu_validate_course_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

/* ---creating  object for second form--- */
$mform2 = new admin_course_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
/* ---If a file has been uploaded, then process it--- */
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    /* ---Print the header--- */
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadcoursesresult', 'local_cobaltcourses'));
    /* ---Current tab--- */
    $currenttab = 'upload';
    /* ---adding tabs--- */
    createtabview($currenttab);

    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    /* ---verification moved to two places: after upload and into form2--- */
    $coursesnew = 0;
    $coursesupdated = 0;
    $coursesuptodate = 0;
    /* ---not printed yet anywhere--- */
    $courseserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $coursesskipped = 0;
    /* ---nit csv import helper--- */
    $cir->init();
    $linenum = 1;
    /* ---column header is first line--- */
    /* ---init upload progress tracker------this class used to keeping track of code(each rows and columns)--- */
    $upt = new uu_progress_tracker();
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;

        /* ---add fields to course object--- */
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                /* ---this should not happen--- */
                continue;
            }
            $key = $filecolumns[$keynum];
            $course->$key = $value;
        }
        if (!isset($course->fullname)) {
            /* ---prevent warnings below--- */
            $course->fullname = '';
        }
        /* ---this is used to include only new courses--- */
        if ($optype == UU_COURSE_ADDNEW) {
            /* ---course creation is a special case - the coursename may be constructed from templates using firstname and lastname--- */
            /* ---better never try this in mixed update types--- */
            $error = false;
            if (!isset($course->fullname) or $course->fullname === '') {
                $error = true;
            }
            if ($error) {
                $courseserrors++;
                continue;
            }
        }
        /* ---make sure we really have coursename--- */
        if (empty($course->fullname)) {
            $courseserrors++;
            continue;
        }
        $school = $DB->get_record('local_school', array('fullname' => $course->schoolname));
        if (empty($school)) {
            echo '<h3 style="color:red;">Invalid school "' . $course->schoolname . '" entered at line no. "' . $lineneum . '" of excelsheet.</h3>';
            goto loop;
        }
        $scid = $school->id;
        $course->fullname = trim($course->fullname);
        $course->shortname = trim($course->shortname);
        $existingcourse = $DB->get_record_sql("select * from {local_cobaltcourses} where  fullname = '$course->fullname' and shortname = '$course->shortname' and schoolid = $scid ");
        /* ---add default values for remaining fields--- */
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($course->$field)) {
                continue;
            }
            /* ---all validation moved to form2--- */
            if (isset($formdata->$field)) {
                /* ---process templates--- */
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($course->$field)) {
                continue;
            }
            if (isset($formdata->$field)) {
                /* ---process templates--- */
                $formdefaults[$field] = true;
            }
        }
        /* ---can we process with update or insert?--- */
        $skip = false;
        switch ($optype) {
            case UU_COURSE_ADDNEW:
                if ($existingcourse) {
                    $coursesskipped++;

                    $skip = true;
                }
                break;
            case UU_COURSE_ADD_UPDATE:
                break;
            case UU_COURSE_UPDATE:
                if (!$existingcourse) {
                    $coursesskipped++;
                    $skip = true;
                }
                break;
            default:
                /* ---unknown type--- */
                $skip = true;
        }

        if ($skip) {
            continue;
        }
        if (!empty($existingcourse)) {
            $course->id = $existingcourse->id;
            $cost = $course->coursecost;
            $cost = (float) $cost;
            $course->coursecost = number_format($cost, 2);
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($course, $column) or ! property_exists($existingcourse, $column)) {
                        /* ---this should never happen--- */
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingcourse->$column) and $existingcourse->$column !== '') {
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
                    if ($existingcourse->$column !== trim($course->$column)) {
                        $existingcourse->$column = $course->$column;
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
                /* ---checking condition for shortnames--- */
                if (empty($course->shortname)) {
                    echo '<h3 style="color:red;">Please enter shortname in line "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $fullname = $course->fullname;
                $shortname = $course->shortname;
                $sql = "select * from {local_cobaltcourses} where fullname = '$fullname' and shortname = '$shortname'";
                $result = $DB->get_records_sql($sql);
                if (empty($result)) {
                    $shortnames = get_snames();
                    $c = 0;
                    foreach ($shortnames as $shortname) {
                        $str1 = trim($shortname->shortname);
                        $str2 = trim($course->shortname);
                        $compare = strcasecmp($str1, $str2);
                        if ($compare == 0) {
                            ++$c;
                            break;
                        }
                    }
                    if ($c != 0) {
                        echo '<h3 style="color:red;">Short name "' . $course->shortname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                        goto loop;
                    }
                }


                /* ---checking condition for schools--- */
                $fac = trim($course->schoolname);
                if (empty($course->schoolname)) {
                    echo '<h3 style="color:red;">Please enter School Name in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));

                if (empty($scid)) {
                    echo '<h3 style="color:red;">Invalid school "' . $course->schoolname . '" entered at line no. "' . $lineneum . '" of excelsheet.</h3>';
                    goto loop;
                }
                $d = 0;
                foreach ($schools as $school) {
                    if ($school->id == $scid) {
                        ++$d;
                        break;
                    }
                }
                if ($d == 0) {
                    echo '<h3 style="color:red;">Sorry you are not assigned to this  school "' . $course->schoolname . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
                    goto loop;
                }

                /* ---checking condition for department--- */
                $departs = get_departments($scid);
                $count = 0;
                $dept = $course->departmentname;
                $mydept = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept'");
                if (!empty($mydept))
                    $depid = $mydept->id;
                else {
                    echo '<h3 style="color:red;">Invalid Department "' . $course->departmentname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                foreach ($departs as $depart) {
                    if ($depart->id == $depid) {
                        ++$count;
                        break;
                    }
                }
                if ($count == 0) {
                    echo '<h3 style="color:red;">Department "' . $course->departmentname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $course->schoolname . '".</h3>';
                    goto loop;
                }
                /* ---checking condition for course type--- */
                if (!empty($course->coursetype)) {
                    if ($course->coursetype > 1 || $course->coursetype < 0) {
                        echo '<h3 style="color:red;">Course Type "' . $course->coursetype . '" entered at line no "' . $linenum . '" of excelsheet is invalid.</h3>';
                        goto loop;
                    }
                    $true = is_numeric($course->coursetype);
                    if ($true == false) {
                        echo '<h3 style="color:red;">Please enter course type in numbers at line number: "' . $linenum . '" of excel sheet</h3>';
                        goto loop;
                    }
                } else {
                    $course->coursetype = 0;
                }
                /* ---end--- */

                if (empty($course->credithours)) {
                    echo '<h3 style="color:red;">Please enter credithours in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                } else {
                    $true = is_numeric($course->credithours);
                    if ($true == false) {
                        echo '<h3 style="color:red;">Please enter credithours in numbers at line number: "' . $linenum . '" of excel sheet.</h3>';
                        goto loop;
                    }
                }
                if (empty($course->fullname)) {
                    echo '<h3 style="color:red;">Please enter fullname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $existingcourse->fullname = $course->fullname;
                $existingcourse->shortname = $course->shortname;
                $existingcourse->schoolid = $scid;
                $existingcourse->departmentid = $depid;
                $existingcourse->summary = $course->summary;
                $existingcourse->coursetype = $course->coursetype;
                $existingcourse->credithours = $course->credithours;
                $existingcourse->coursecost = $course->coursecost;
                $existingcourse->visible = 1;
                $existingcourse->usermodified = $USER->id;
                $existingcourse->timemodified = time();
                $DB->update_record('local_cobaltcourses', $existingcourse);
                $coursesupdated++;
            } else {
                $coursesuptodate++;
            }
        } else {
            /* ---else start for [!empty($existingcourse]--- */
            /* ---save the new course to the database--- */
            $data = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            /* ---checking condition for schools--- */
            if (empty($course->schoolname)) {
                echo '<h3 style="color:red;">Please enter School Name in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $fac = $course->schoolname;
            $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));

            if (empty($scid)) {
                echo '<h3 style="color:red;">Invalid school "' . $course->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
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
                echo '<h3 style="color:red;">Sorry you are not assigned to this  school "' . $course->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            /* ---checking condition for shortnames--- */
            if (empty($course->shortname)) {
                echo '<h3 style="color:red;">Please enter shortname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $shortnames = get_snames();
            $c = 0;
            foreach ($shortnames as $shortname) {
                $str1 = trim($shortname->shortname);
                $str2 = trim($course->shortname);
                $compare = strcasecmp($str1, $str2);
                if ($compare == 0) {
                    ++$c;
                    break;
                }
            }
            if ($c != 0) {
                echo '<h3 style="color:red;">Short name "' . $course->shortname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                goto loop;
            }
            /* ---end--- */
            /* ---checking condition for department--- */
            $departs = get_departments($scid);
            $count = 0;
            $dept = $course->departmentname;
            $mydept = $DB->get_record_sql("SELECT * FROM {local_department} WHERE fullname = '$dept'");
            if (!empty($mydept))
                $depid = $mydept->id;
            else {
                echo '<h3 style="color:red;">Invalid Department "' . $course->departmentname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            foreach ($departs as $depart) {
                if ($depart->id == $depid) {
                    ++$count;
                    break;
                }
            }
            if ($count == 0) {
                echo '<h3 style="color:red;">Department "' . $course->departmentname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is not under given School "' . $course->schoolname . '".</h3>';
                goto loop;
            }
            /* ---end--- */
            /* ---checking condition for course type--- */
            if (!empty($course->coursetype)) {
                if ($course->coursetype > 1 || $course->coursetype < 0) {
                    echo '<h3 style="color:red;"> Course Type "' . $course->coursetype . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is invalid.</h3>';
                    goto loop;
                }
                $true = is_numeric($course->coursetype);
                if ($true == false) {
                    echo '<h3 style="color:red;">Please enter course type in numbers at line number: "' . $linenum . '" of excel sheet.</h3>';
                    goto loop;
                }
            } else {
                $course->coursetype = 0;
            }
            /* ---end--- */
            if (empty($course->credithours)) {
                echo '<h3 style="color:red;">Please enter credithours in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            } else {
                $true = is_numeric($course->credithours);
                if ($true == false) {
                    echo '<h3 style="color:red;">Please enter credithours in numbers at line number: "' . $linenum . '" of excel sheet.</h3>';
                    goto loop;
                }
            }
            if (empty($course->fullname)) {
                echo '<h3 style="color:red;">Please enter fullname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $data->fullname = $course->fullname;
            $data->shortname = $course->shortname;
            $data->schoolid = $scid;
            $data->departmentid = $depid;
            $data->summary = $course->summary;
            $data->coursetype = $course->coursetype;
            $data->credithours = $course->credithours;
            $data->coursecost = $course->coursecost;
            $data->visible = 1;
            $data->timecreated = time();
            $data->usermodified = $USER->id;
            $data->id = $DB->insert_record('local_cobaltcourses', $data);
            $data->id++;
            $coursesnew++;
        }
    }

    $cir->cleanup(true);
    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_COURSE_UPDATE) {
        echo get_string('coursescreated', 'local_cobaltcourses') . ': ' . $coursesnew . '<br />';
    }
    if ($optype == UU_COURSE_UPDATE or $optype == UU_COURSE_ADD_UPDATE) {
        echo get_string('coursesupdated', 'local_cobaltcourses') . ': ' . $coursesupdated . '<br />';
    }
    if ($coursesskipped) {
        echo get_string('coursesskipped', 'local_cobaltcourses') . ': ' . $coursesskipped . '<br />';
    }
    echo get_string('errors', 'local_cobaltcourses') . ': ' . $courseserrors . '</p>';
    echo $OUTPUT->box_end();
    if ($optype == UU_COURSE_UPDATE or $optype == UU_COURSE_ADD_UPDATE) {
        if ($coursesnew == 0 && $coursesupdated == 0 && $coursesskipped = 0) {
            echo '<h4> Courses skipped because records with those names are already exist.</h4>';
        }
    }
    if ($coursesskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($coursesskipped == 1)
            echo '<h4> Course skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $coursesskipped . ' courses skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
/* ---Print the header--- */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadcoursespreview', 'local_cobaltcourses'));
/* ---Current tab--- */
$currenttab = 'upload';
//adding tabs---*/
createtabview($currenttab);
/* ---Print the form if valid values are available--- */
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

