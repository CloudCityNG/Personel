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
 * Bulk school registration script from a comma separated file
 *
 * @package    local
 * @subpackage uploadschools
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('upload_schools_lib.php');
require_once('upload_schools_form.php');
require_once('lib.php');
require_once($CFG->dirroot . '/local/lib.php');
$iid = optional_param('iid', '', PARAM_INT);
$previewrows = optional_param('previewrows', 10, PARAM_INT);
@set_time_limit(60 * 60); // 1 hour should be enough
raise_memory_limit(MEMORY_HUGE);
require_login();
$strschoolupdated = get_string('schoolaccountupdated', 'local_collegestructure');
$strschooluptodate = get_string('schoolaccountuptodate', 'local_collegestructure');
$strschoolnotadded = get_string('schoolnotaddedregistered', 'local_collegestructure');
$strschooldeleted = get_string('schooldeleted', 'local_collegestructure');
$errorstr = get_string('error');
$stryes = get_string('yes');
$strno = get_string('no');
$stryesnooptions = array(0 => $strno, 1 => $stryes);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$returnurl = new moodle_url('/local/collegestructure/upload.php');
$PAGE->set_url('/local/collegestructure/upload.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_collegestructure');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_collegestructure'), new moodle_url('/local/collegestructure/index.php'));
$PAGE->navbar->add(get_string('uploadschools', 'local_collegestructure'));
global $USER;
/* ---array of all valid fields for validation--- */
$STD_FIELDS = array('fullname', 'parentid', 'type', 'description');
$PRF_FIELDS = array();
/* ---if variable $iid equal to zero,it allows enter into the form--- */
if (empty($iid)) {
    $mform1 = new admin_school_form1();
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploadschool');
        $cir = new csv_import_reader($iid, 'uploadschool');
        /* ---this class fromcsvlib.php(includes csv methods and clclasses)--- */
        $content = $mform1->get_file_content('schoolfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        /* ---test if columns ok(to validate the csv file content)--- */
        $filecolumns = uu_validate_school_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        /* ---continue to form2--- */
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading_with_help(get_string('uploadschools', 'local_collegestructure'), 'uploadschool', 'local_collegestructure');
        echo '<div style="float:right;"><a href="sample.php?format=xls"><button>Sample Excelsheet</button></a></div>';
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {
    /* ---if not empty of variable $iid,it get the content from the csv content--- */
    $cir = new csv_import_reader($iid, 'uploadschool');
    $filecolumns = uu_validate_school_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}
/* ---creating  object for second form--- */
$mform2 = new admin_school_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
/* ---If a file has been uploaded, then process it--- */
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadschoolsresult', 'local_collegestructure'));
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    /* ---verification moved to two places: after upload and into form2--- */
    $schoolsnew = 0;
    $schoolsupdated = 0;
    $schoolsuptodate = 0; /* ---not printed yet anywhere--- */
    $schoolserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $schoolsskipped = 0;
    /* ---init csv import helper--- */
    $cir->init();
    $linenum = 1; //column header is first line---*/
    /* ---init upload progress tracker------this class used to keeping track of code(each rows and columns)--- */
    $upt = new uu_progress_tracker();

    /* ---start table--- */
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;

        $school = new stdClass();
        /* ---add fields to school object--- */
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                /* ---this should not happen--- */
                continue;
            }
            $key = $filecolumns[$keynum];
            $school->$key = $value;
            if (in_array($key, $upt->columns)) {
                
            }
        }

        if (!isset($school->fullname)) {
            /* ---prevent warnings below--- */
            $school->fullname = '';
        }
        /* ---this is used to include only new schools--- */
        if ($optype == UU_SCHOOL_ADDNEW) {
            /* ---school creation is a special case - the schoolname may be constructed from templates using firstname and lastname--- */
            /* ---better never try this in mixed update types--- */
            $error = false;
            if (!isset($school->fullname) or $school->fullname === '') {
                $error = true;
            }
            if ($error) {
                $schoolserrors++;
                continue;
            }
        }

        /* ---make sure we really have schoolname--- */
        if (empty($school->fullname)) {
            $schoolserrors++;
            continue;
        }

        if ($existingschool = $DB->get_record('local_school', array('fullname' => $school->fullname))) {
            
        }

        /* ---add default values for remaining fields--- */
        $formdefaults = array();

        foreach ($STD_FIELDS as $field) {
            if (isset($school->$field)) {
                continue;
            }
            /* ---all validation moved to form2--- */
            if (isset($formdata->$field)) {
                /* ---process templates--- */
                $formdefaults[$field] = true;
                if (in_array($field, $upt->columns)) {
                    
                }
            }
        }

        foreach ($PRF_FIELDS as $field) {
            if (isset($school->$field)) {
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
            case UU_SCHOOL_ADDNEW:
                if ($existingschool) {
                    $schoolsskipped++;

                    $skip = true;
                }
                break;
            case UU_SCHOOL_ADD_UPDATE:
                break;
            case UU_SCHOOL_UPDATE:
                if (!$existingschool) {
                    $schoolsskipped++;

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
        if (!empty($existingschool)) {
            $school->id = $existingschool->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != UU_UPDATE_NOCHANGES) {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($school, $column) or ! property_exists($existingschool, $column)) {
                        /* ---this should never happen--- */
                        continue;
                    }
                    if ($updatetype == UU_UPDATE_MISSING) {
                        if (!is_null($existingschool->$column) and $existingschool->$column !== '') {
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
                    if ($existingschool->$column !== $school->$column) {
                        if (in_array($column, $upt->columns)) {
                            
                        }
                        $existingschool->$column = $school->$column;
                        $doupdate = true;
                    }
                }
            }
            if ($doupdate) {
                if (isset($school->parentid) && !empty($school->parentid)) {
                    $parentid = $DB->get_field('local_school', 'id', array('fullname' => $school->parentid));
                    if (empty($parentid)) {
                        echo 'Invalid parentid => ' . $parentid . ' on line => ' . $linenum . ' of uploaded Excelsheet.';
                        goto loop;
                    }
                } else {
                    $parentid = 0;
                }

                /* ---check type of school--- */
                if (strtolower($school->type) === 'campus')
                    $school->type = 1;
                elseif (strtolower($school->type) === 'university')
                    $school->type = 2;
                elseif (empty($school->type))
                    $school->type = 0;
                else {
                    echo 'Invalid Type of school=>' . $school->type . ' on line => ' . $linenum . ' of uploaded Excelsheet.';
                    goto loop;
                }


                $existingschool->fullname = $school->fullname;
                $existingschool->parentid = $parentid;
                $existingschool->type = $school->type;
                $existingschool->description = $school->description;
                $existingschool->visible = 1;
                $existingschool->usermodified = $USER->id;
                $existingschool->timemodified = time();
                $existingschool->childpermissions = 1;
                $hierarche = new hierarchy ();
                if ($existingschool->parentid == 0) {
                    $existingschool->depth = 1;
                    $existingschool->path = '';
                } else {
                    /* ---parent item must exist--- */
                    $parent = $DB->get_record('local_school', array('id' => $existingschool->parentid));
                    $existingschool->depth = $parent->depth + 1;
                    $existingschool->path = $parent->path;
                }
                if (!$sortthread = $hierarche->get_next_child_sortthread($existingschool->parentid, 'local_school')) {
                    return false;
                }
                $existingschool->sortorder = $sortthread;
                $DB->update_record('local_school', $existingschool);
                $schoolsupdated++;
            } else {
                $schoolsuptodate++;
            }
        } else { /* ---else start for [!empty($existingschool]--- */
            $data = new stdclass();
            /* ---check parentid of school--- */
            if (isset($school->parentid) && !empty($school->parentid)) {
                $parentid = $DB->get_field('local_school', 'id', array('fullname' => $school->parentid));
                if (empty($parentid)) {
                    echo 'Invalid parentid =>" ' . $parentid . ' "on line => ' . $linenum . ' of uploaded Excelsheet.';
                    goto loop;
                }
            } else {
                $parentid = 0;
            }

            /* ---check type of school--- */
            if (strtolower($school->type) === 'campus')
                $school->type = 1;
            elseif (strtolower($school->type) === 'university')
                $school->type = 2;
            elseif (empty($school->type))
                $school->type = 0;
            else {
                echo 'Invalid Type of school=>' . $school->type . ' on line => ' . $linenum . ' of uploaded Excelsheet.';
                goto loop;
            }

            $data->fullname = $school->fullname;
            $data->parentid = $parentid;
            $data->description = $school->description;
            $data->type = $school->type;
            $data->visible = 1;
            $data->timecreated = time();
            $data->usermodified = $USER->id;
            $data->childpermissions = 1;

            $hierarche = new hierarchy ();
            if ($data->parentid == 0) {
                $data->depth = 1;
                $data->path = '';
            } else {
                /* ---parent item must exist--- */
                $parent = $DB->get_record('local_school', array('id' => $data->parentid));
                $data->depth = $parent->depth + 1;
                $data->path = $parent->path;
            }
            if (!$sortthread = $hierarche->get_next_child_sortthread($data->parentid, 'local_school')) {
                return false;
            }
            $data->sortorder = $sortthread;
            $data->id = $DB->insert_record('local_school', $data);
            $DB->set_field('local_school', 'path', $data->path . '/' . $data->id, array('id' => $data->id));
            $data->id++;
            $schoolsnew++;
        }
    }
    $cir->cleanup(true);

    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != UU_SCHOOL_UPDATE) {
        echo get_string('schoolscreated', 'local_collegestructure') . ': ' . $schoolsnew . '<br />';
    }
    if ($optype == UU_SCHOOL_UPDATE or $optype == UU_SCHOOL_ADD_UPDATE) {
        echo get_string('schoolsupdated', 'local_collegestructure') . ': ' . $schoolsupdated . '<br />';
    }
    if ($schoolsskipped) {
        echo get_string('schoolsskipped', 'local_collegestructure') . ': ' . $schoolsskipped . '<br />';
    }
    echo get_string('errors', 'local_collegestructure') . ': ' . $schoolserrors . '</p>';
    echo $OUTPUT->box_end();
    if ($schoolsskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($schoolsskipped == 1)
            echo '<h4> School  skipped because record with that name is  already exists.</h4>';
        else
            echo '<h4>' . $schoolsskipped . ' schools are skipped because records with those names are already exist.</h4>';
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->footer();
    die;
}
/* ---Print the header--- */
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploadschoolspreview', 'local_collegestructure'));
/* ---Print the form if valid values are available--- */
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

