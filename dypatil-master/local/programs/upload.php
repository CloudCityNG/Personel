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
 * Bulk program registration script from a comma separated file
 *
 * @package    local
 * @subpackage uploadprograms
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('upload_programs_lib.php');
require_once('upload_programs_form.php');
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
$PAGE->set_context($systemcontext);
if (!has_capability('local/programs:manage', $systemcontext))
    print_error('nopermissions', 'error');
$returnurl = new moodle_url('/local/programs/index.php');
$PAGE->set_url('/local/programs/upload.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_title(get_string('programs', 'local_programs') . ': ' . get_string('uploadprograms', 'local_programs'));
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_programs');
// $PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
$PAGE->navbar->add(get_string('uploadprograms', 'local_programs'));
$myprogram = programs::getInstance();
global $USER;
// array of all valid fields for validation
$STD_FIELDS = array('fullname', 'shortname', 'schoolname', 'description', 'type', 'programlevel', 'duration');
$PRF_FIELDS = array();
//  if variable $iid equal to zero,it allows enter into the form
if (empty($iid)) {
    $mform1 = new admin_program_form1();
    if ($mform1->is_cancelled())
        redirect($returnurl);
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadprogram');
        $cir = new csv_import_reader($iid, 'uploadprogram'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('programfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_program_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_programs'));
        // Current tab
        $currenttab = 'upload';
        //adding tabs
        $myprogram->createtabview($currenttab);

        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('uploaddes', 'local_programs'));
        }
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploadprogram');
    $filecolumns = uu_validate_program_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}
//---creating  object for second form---------------------- 
$mform2 = new admin_program_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    // Current tab
    $currenttab = 'upload';
    //adding tabs
    $myprogram->createtabview($currenttab);
    echo $OUTPUT->heading(get_string('uploadprogramsresult', 'local_programs'));
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    // verification moved to two places: after upload and into form2
    $programsnew = 0;
    $programsupdated = 0;
    $programsuptodate = 0; //not printed yet anywhere
    $programserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $programsskipped = 0;
    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line
    // init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
    $upt = new uu_progress_tracker();
    // $upt->start(); // start table
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;
        // $upt->track('line', $linenum);
        $program = new stdClass();
        // add fields to program object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $program->$key = $value;
        }
        if (!isset($program->fullname)) {
            // prevent warnings below
            $program->fullname = '';
        }
        //  this is used to include only new programs
        if ($optype == UU_PROGRAM_ADDNEW) {
            // program creation is a special case - the programname may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($program->fullname) or $program->fullname === '') {
                $error = true;
            }
            if ($error) {
                $programserrors++;
                continue;
            }
        }
        // make sure we really have programname
        if (empty($program->fullname)) {
            // $upt->track('status', get_string('missingfield', 'error', 'fullname'), 'error');
            // $upt->track('fullname', $errorstr, 'error');
            $programserrors++;
            continue;
        }
        $program->schoolname = trim($program->schoolname);
        $school = $DB->get_record('local_school', array('fullname' => $program->schoolname));
        if (empty($school)) {
            echo '<h3 style="color:red;">Invalid school "' . $program->schoolname . '" entered at line no."' . $linenum . '" of uploaded excelsheet.</h3>';
            goto loop;
        }
        $scid = $school->id;
        $existingprogram = $DB->get_record('local_program', array('fullname' => $program->fullname, 'schoolid' => $scid));
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($program->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        //  print_object($PRF_FIELDS);
        foreach ($PRF_FIELDS as $field) {
            if (isset($program->$field)) {
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
            case UU_PROGRAM_ADDNEW:
                if ($existingprogram) {
                    $programsskipped++;
                    // $upt->track('status', $strprogramnotadded, 'warning');
                    $skip = true;
                }
                break;
            case UU_PROGRAM_ADD_UPDATE:
                break;
            case UU_PROGRAM_UPDATE:
                if (!$existingprogram) {
                    $programsskipped++;
                    // $upt->track('status', $strprogramnotupdatednotexists, 'warning');
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
        if (!empty($existingprogram)) {
            $program->id = $existingprogram->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($program, $column) or ! property_exists($existingprogram, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingprogram->$column) and $existingprogram->$column !== '') {
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
                    if ($existingprogram->$column !== trim($program->$column)) {
                        $existingprogram->$column = $program->$column;
                        $doupdate = true;
                    }
                }
            }
            if ($doupdate) {
                $hier = new hierarchy();
                $prgs = programs::getInstance();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }
                $fullname = trim($program->fullname);
                $shortname = trim($program->shortname);
                // check shortname
                if (empty($program->shortname)) {
                    echo '<h3 style="color:red;">Please enter shortname  in line no."' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                $sql = "select * from {local_program} where fullname = '$fullname' and shortname = '$shortname'";
                $result = $DB->get_records_sql($sql);
                if (empty($result)) {
                    $shortnames = $prgs->get_snames();
                    $c = 0;
                    foreach ($shortnames as $shortname) {
                        $str1 = trim($shortname->shortname);
                        $str2 = trim($program->shortname);
                        $x = strcasecmp($str1, $str2);
                        if ($x == 0) {
                            ++$c;
                            break;
                        }
                    }
                    if ($c != 0) {
                        echo '<h3 style="color:red;">Short name "' . $program->shortname . '"  at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                        goto loop;
                    }
                }
                // ==============checking condition for schools=====================
                $fac = trim($program->schoolname);
                $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
                if (empty($scid)) {
                    echo '<h3 style="color:red;">Invalid school "' . $program->schoolname . '" entered at line no."' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $d = 0;
                foreach ($schools as $scl) {
                    if ($scl->id == $scid) {
                        ++$d;
                        break;
                    }
                }
                if ($d == 0) {
                    echo '<h3 style="color:red;">Sorry you are not assigned to this  school "' . $program->schoolname . '" entered at line no."' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                // check programlevel
                if (!empty($program->programlevel)) {
                    if ($program->programlevel > 2 || $program->programlevel <= 0) {
                        echo "<h3 style='color:red;'>Programlevel '" . $program->programlevel . "' entered at line no. '" . $linenum . "' of uploaded excelsheet  doesn't exists.</h3>";
                        goto loop;
                    }
                } else {
                    $program->programlevel = 1;
                }

                // =======================end================================		
                // ===============checking condition for programtype=================
                if (!empty($program->type)) {
                    if ($program->type > 2 || $program->type <= 0) {
                        echo "<h3 style='color:red;'>Program Type '" . $program->type . "' entered at line no. '" . $linenum . "' of uploaded excelsheet  doesn't exists.</h3>";
                        goto loop;
                    }
                } else {
                    $program->type = 1;
                }
                // check program duration
                if (empty($program->duration)) {
                    echo '<h3 style="color:red;">Please enter program duration  in line no "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                if (!is_numeric($program->duration)) {
                    echo "<h3 style='color:red;'>Program Duration must be in numeric at line no " . $linenum . " of uploaded excelsheet.</h3>";
                    goto loop;
                }
                $existingprogram->fullname = $fullname;
                $existingprogram->shortname = $program->shortname;
                $existingprogram->schoolid = $scid;
                $existingprogram->departmentid = 0;
                $existingprogram->description = $program->description;
                $existingprogram->type = $program->type;
                $existingprogram->programlevel = $program->programlevel;
                $existingprogram->duration = $program->duration;
                $existingprogram->visible = 1;
                $existingprogram->usermodified = $USER->id;
                $existingprogram->sortorder = 1;
                $existingprogram->timemodified = time();
                $DB->update_record('local_program', $existingprogram);
                $programsupdated++;
            } else {
                // no program information changed
                // $upt->track('status', $strprogramuptodate);
                $programsuptodate++;
            }
        } else {     // else start for [!empty($existingprogram]
            $data = new stdclass();
            $hier = new hierarchy();
            $schools = $hier->get_assignedschools();
            if (is_siteadmin()) {
                $schools = $hier->get_school_items();
            }
            $prgs = programs::getInstance();
            $fullname = trim($program->fullname);
            $shortname = trim($program->shortname);
            // check program duration
            if (empty($program->duration)) {
                echo '<h3 style="color:red;">Please enter program duration  in line  no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($program->fullname)) {
                echo '<h3 style="color:red;">Please enter program fullname  in line  no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            if (empty($program->shortname)) {
                echo '<h3 style="color:red;">Please enter program shortname  in line  no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // check schools
            $fac = trim($program->schoolname);
            $scid = $DB->get_field('local_school', 'id', array('fullname' => $fac));
            if (empty($scid)) {
                echo '<h3 style="color:red;">Invalid school "' . $program->schoolname . '" entered at  line no."' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $c = 0;
            foreach ($schools as $scl) {
                if ($scl->id == $scid) {
                    ++$c;
                    break;
                }
            }
            if ($c == 0) {
                echo '<h3 style="color:red;">Sorry! you are not assigned to this  school "' . $program->schoolname . '" entered at  line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // ==========================end================================	
            // ============checking condition for shortnames =================

            $shortnames = $prgs->get_snames();
            $d = 0;
            foreach ($shortnames as $shortname) {
                $str1 = trim($shortname->shortname);
                $str2 = trim($program->shortname);
                $compare = strcasecmp($str1, $str2);
                if ($compare == 0) {
                    ++$d;
                    break;
                }
            }
            if ($d != 0) {
                echo '<h3 style="color:red;">Short name "' . $program->shortname . '"  entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                goto loop;
            }
            // ===========================end===============================	
            // ===============checking condition for programlevel=================
            if (!empty($program->programlevel)) {
                if ($program->programlevel > 2 || $program->programlevel <= 0) {
                    echo "<h3 style='color:red;'>Programlevel '" . $program->programlevel . "' entered at line no. '" . $linenum . "' of uploaded excelsheet  doesn't exists.</h3>";
                    goto loop;
                }
            } else {
                $program->programlevel = 1;
            }

            // =======================end================================		
            // ===============checking condition for programtype=================
            if (!empty($program->type)) {
                if ($program->type > 2 || $program->type <= 0) {
                    echo "<h3 style='color:red;'>Program Type '" . $program->type . "' entered at line no. '" . $linenum . "' of uploaded excelsheet  doesn't exists.</h3>";
                    goto loop;
                }
            } else {
                $program->type = 1;
            }
            // =======================end================================
            if (!is_numeric($program->duration)) {
                echo "<h3 style='color:red;'>Program Duration must be in numeric at line no " . $linenum . " of uploaded excelsheet.</h3>";
                goto loop;
            }

            $data->fullname = $fullname;
            $data->shortname = $program->shortname;
            $data->schoolid = $scid;
            $data->departmentid = 0;
            $data->description = $program->description;
            $data->type = $program->type;
            $data->duration = $program->duration;
            $data->visible = 1;
            $data->programlevel = $program->programlevel;
            $data->timecreated = time();
            $data->usermodified = $USER->id;
            $data->id = $DB->insert_record('local_program', $data);
            $data->id++;
            $programsnew++;
        }  //else end for [!empty($existingprogram]
    } // end of while ($cir->next())
    // $upt->close(); // close table
    // $cir->close();
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_PROGRAM_UPDATE) {
        echo get_string('programscreated', 'local_programs') . ': ' . $programsnew . '<br />';
    }
    if ($optype == UU_PROGRAM_UPDATE or $optype == UU_PROGRAM_ADD_UPDATE) {
        echo get_string('programsupdated', 'local_programs') . ': ' . $programsupdated . '<br />';
    }
    if ($programsskipped) {
        echo get_string('programsskipped', 'local_programs') . ': ' . $programsskipped . '<br />';
    }
    if ($programserrors)
        echo get_string('errors', 'local_programs') . ': ' . $programserrors;
    echo '</p>';
    if ($programserrors)
        echo '<h4> Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
    echo $OUTPUT->box_end();
    if ($optype == UU_PROGRAM_UPDATE or $optype == UU_PROGRAM_ADD_UPDATE) {
        if ($programsnew == 0 && $programsupdated == 0 && $programsskipped = 0) {
            echo '<h4> Programs skipped because records with those names are already exist.</h4>';
        }
    }
    if ($programsskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($programsskipped == 1)
            echo '<h4> Program skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $programsskipped . ' programs skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadprogramspreview', 'local_programs'));
// Current tab
$currenttab = 'upload';
//adding tabs
$myprogram->createtabview($currenttab);
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;
