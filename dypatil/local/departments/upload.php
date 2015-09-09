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
 * Bulk department registration script from a comma separated file
 *
 * @package    local
 * @subpackage departments
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot . '/local/departments/lib.php');
require_once('upload_departments_lib.php');
require_once('upload_departments_form.php');
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
if (!has_capability('local/departments:manage', $systemcontext))
    print_error('nopermissions', 'error');
$returnurl = new moodle_url('/local/departments/index.php');
$PAGE->set_url('/local/departments/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_departments') . ' : ' . get_string('uploaddepartments', 'local_departments');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('uploaddepartments', 'local_departments'));

global $USER, $DB;
// array of all valid fields for validation
$STD_FIELDS = array('fullname', 'shortname', 'schoolname', 'description');

$PRF_FIELDS = array();
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_department_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaddepartment');
        $cir = new csv_import_reader($iid, 'uploaddepartment'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('departmentfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_department_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_departments'));
        //--------tab code----------------------
        $currenttab = 'upload';
        $dept_ob = manage_dept::getInstance();
        $dept_ob->dept_tabs($currenttab);
        //--------------------------------------------
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('dept_uploaddes', 'local_departments'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';

        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploaddepartment');
    $filecolumns = uu_validate_department_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

//---creating  object for second form---------------------- 
$mform2 = new admin_department_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploaddepartmentsresult', 'local_departments'));
    $currenttab = 'upload';
    $dept_ob = manage_dept::getInstance();
    $dept_ob->dept_tabs($currenttab);
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    // verification moved to two places: after upload and into form2
    $departmentsnew = 0;
    $departmentsupdated = 0;
    $departmentsuptodate = 0; //not printed yet anywhere
    $departmentserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $departmentsskipped = 0;
    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line
    // init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
    $upt = new uu_progress_tracker();
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;
        // $upt->track('line', $linenum);
        $department = new stdClass();
        // add fields to department object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $department->$key = $value;
        }
        if (!isset($department->fullname)) {
            // prevent warnings below
            $department->fullname = '';
        }
        //--------------------------------this is used to include only new departments-----------------
        if ($optype == UU_DEPARTMENT_ADDNEW) {
            // department creation is a special case - the departmentname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($department->fullname) or $department->fullname === '') {
                $error = true;
            }

            if ($error) {
                $departmentserrors++;
                continue;
            }
        }

        // make sure we really have departmentname
        if (empty($department->fullname)) {
            $departmentserrors++;
            continue;
        }
        $department->schoolname = trim($department->schoolname);
        $school = $DB->get_record('local_school', array('fullname' => $department->schoolname));
        if (empty($school)) {
            echo '<h3 style="color:red;">Invalid school "' . $department->schoolname . '" entered at line no. "' . $lineneum . '" of excelsheet.</h3>';
            goto loop;
        }
        $scid = $school->id;

        $existingdepartment = $DB->get_record_sql("select * from {local_department} where fullname ='$department->fullname' and  schoolid =$scid");
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($department->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($department->$field)) {
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
            case UU_DEPARTMENT_ADDNEW:

                if ($existingdepartment) {
                    $departmentsskipped++;
                    // $upt->track('status', $strdepartmentnotadded, 'warning');
                    $skip = true;
                }
                break;

            case UU_DEPARTMENT_ADD_UPDATE:
                break;
            case UU_DEPARTMENT_UPDATE:
                if (!$existingdepartment) {
                    $departmentsskipped++;
                    // $upt->track('status', $strdepartmentnotupdatednotexists, 'warning');
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
        if (!empty($existingdepartment)) {
            $department->id = $existingdepartment->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($department, $column) or ! property_exists($existingdepartment, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingdepartment->$column) and $existingdepartment->$column !== '') {
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
                    if ($existingdepartment->$column !== trim($department->$column)) {
                        $existingdepartment->$column = $department->$column;
                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {
                $hier = new hierarchy();
                $departments = manage_dept::getInstance();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }
                if (empty($department->fullname)) {
                    echo '<h3 style="color:red;">Please enter Department fullname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (empty($department->shortname)) {
                    echo '<h3 style="color:red;">Please enter Department shortname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (empty($department->schoolname)) {
                    echo '<h3 style="color:red;">Please enter Department schoolname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                // ============checking condition for shortname =================
                if (empty($department->shortname)) {
                    echo '<h3 style="color:red;">Please enter shortname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $fullname = trim($department->fullname);
                $shortname = trim($department->shortname);
                $sql = "select * from {local_department} where fullname = '$fullname' and shortname = '$shortname'";
                $result = $DB->get_records_sql($sql);
                if (empty($result)) {
                    $shortnames = $departments->get_snames();
                    $c = 0;
                    foreach ($shortnames as $shortname) {
                        $str1 = $shortname->shortname;
                        $str2 = $department->shortname;
                        $compare = strcasecmp($str1, $str2);
                        if ($compare == 0) {
                            ++$c;
                            break;
                        }
                    }
                    if ($c != 0) {
                        echo '<h3 style="color:red;">Short name "' . $department->shortname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                        goto loop;
                    }
                }

                // ===========================end===============================	
                // ==============checking condition for schools=====================
                $fac = $department->schoolname;
                $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
                if (empty($scid)) {
                    echo '<h3 style="color:red;">Invalid school "' . $department->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
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
                    echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $department->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                // ==========================end================================		
                $existingdepartment->fullname = $department->fullname;
                $existingdepartment->shortname = $department->shortname;
                $existingdepartment->schoolid = $scid;
                $existingdepartment->description_text = $department->description;
                $existingdepartment->description_format = 1;
                $existingdepartment->visible = 1;
                $existingdepartment->timemodified = time();
                $existingdepartment->usermodified = $USER->id;
                $existingdepartment->sortorder = 1;
                $DB->update_record('local_department', $existingdepartment);
                $departmentsupdated++;
            } else {
                // no department information changed
                // $upt->track('status', $strdepartmentuptodate);
                $departmentsuptodate++;
            }
        } else {     // else start for [!empty($existingdepartment]
            // save the new department to the database
            $data = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            $departments = manage_dept::getInstance();
            if (empty($department->fullname)) {
                echo '<h3 style="color:red;">Please enter Department fullname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($department->shortname)) {
                echo '<h3 style="color:red;">Please enter Department shortname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($department->schoolname)) {
                echo '<h3 style="color:red;">Please enter Department schoolname  in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            // ==============checking condition for schools=====================
            $fac = $department->schoolname;
            $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
            if (empty($scid)) {
                echo '<h3 style="color:red;">Invalid school "' . $department->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
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
                echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $department->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // ==========================end================================	
            // ============checking condition for shortnames =================
            if (empty($department->shortname)) {
                echo '<h3 style="color:red;">Please enter shortname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $shortnames = $departments->get_snames();
            $d = 0;
            foreach ($shortnames as $shortname) {

                $str1 = trim($shortname->shortname);
                $str2 = trim($department->shortname);
                $compare = strcasecmp($str1, $str2);
                if ($compare == 0) {
                    ++$d;
                    break;
                }
            }
            if ($d != 0) {
                echo '<h3 style="color:red;">Short name "' . $department->shortname . '" entered at line "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                goto loop;
            }
            // ===========================end===============================	
            $data->fullname = $department->fullname;
            $data->shortname = $department->shortname;
            $data->schoolid = $scid;
            $data->visible = 1;
            $data->timecreated = time();
            $data->timemodified = 0;
            $data->usermodified = $USER->id;
            $data->description_text = $department->description;
            $data->description_format = 1;
            $data->sortorder = 1;
            $data->id = $DB->insert_record('local_department', $data);
            $data->id++;
            $departmentsnew++;
        }
    }
    // $upt->close(); // close table
    // $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_DEPARTMENT_UPDATE) {
        echo get_string('departmentscreated', 'local_departments') . ': ' . $departmentsnew . '<br />';
    }
    if ($optype == UU_DEPARTMENT_UPDATE or $optype == UU_DEPARTMENT_ADD_UPDATE) {
        echo get_string('departmentsupdated', 'local_departments') . ': ' . $departmentsupdated . '<br />';
    }
    if ($departmentsskipped) {
        echo get_string('departmentsskipped', 'local_departments') . ': ' . $departmentsskipped . '<br />';
    }
    echo get_string('dept_errors', 'local_departments') . ': ' . $departmentserrors . '</p>';
    echo $OUTPUT->box_end();
    if ($optype == UU_DEPARTMENT_UPDATE or $optype == UU_DEPARTMENT_ADD_UPDATE) {
        if ($departmentsnew == 0 && $departmentsupdated == 0 && $departmentsskipped < 1) {
            echo '<h4> Departments skipped because records with those names are already exist.</h4>';
        }
    }
    if ($departmentsskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($departmentsskipped == 1)
            echo '<h4> Department skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $departmentsskipped . ' Departments skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploaddepartmentspreview', 'local_departments'));
$currenttab = 'upload';
$dept_ob = manage_dept::getInstance();
$dept_ob->dept_tabs($currenttab);
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;
