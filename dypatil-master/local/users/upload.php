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
 * Bulk user registration script from a comma separated file
 *
 * @package    tool
 * @subpackage user
 * @copyright  2004 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require('../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/csvlib.class.php');
require_once('upload_users_lib.php');
require_once('upload_users_form.php');
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

global $USER, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);

$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/users/upload.php');
$PAGE->set_heading($SITE->fullname);
$strheading = get_string('pluginname', 'local_users') . ' : ' . get_string('uploadusers', 'local_users');
$PAGE->set_title($strheading);
$PAGE->navbar->add(get_string('pluginname', 'local_users'), new moodle_url('/local/user/index.php'));
$PAGE->navbar->add(get_string('uploadusers', 'local_users'));
$returnurl = new moodle_url('/local/users/index.php');
$myuser = users::getInstance();

// array of all valid fields for validation
$STD_FIELDS = array('username', 'password', 'firstname', 'middlename', 'lastname', 'gender', 'city', 'dob', 'country', 'phone', 'email', 'description', 'schoolname', 'address', 'role');

$PRF_FIELDS = array();
//-------- if variable $iid equal to zero,it allows enter into the form-----------------------------------
if (empty($iid)) {
    $mform1 = new admin_user_form1();
    if ($mform1->is_cancelled()) {
        redirect($returnurl);
    }
    if ($formdata = $mform1->get_data()) {
        $iid = csv_import_reader::get_new_iid('uploaduser');
        $cir = new csv_import_reader($iid, 'uploaduser'); //this class fromcsvlib.php(includes csv methods and clclasses)
        $content = $mform1->get_file_content('userfile');
        $readcount = $cir->load_csv_content($content, $formdata->encoding, $formdata->delimiter_name);
        unset($content);
        if ($readcount === false) {
            print_error('csvloaderror', '', $returnurl);
        } else if ($readcount == 0) {
            print_error('csvemptyfile', 'error', $returnurl);
        }
        // test if columns ok(to validate the csv file content)
        $filecolumns = uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
        // continue to form2
    } else {
        echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('pluginname', 'local_users'));

        // Current tab
        $currenttab = 'upload';
        //adding tabs
        $myuser->createtabview($currenttab);
        echo '<div style="float:right;"><a href="sample.php?format=csv"><button>' . get_string('sample_excel', 'local_departments') . '</button></a></div>';
        echo '<div style="float:right;"><a href="help.php"><button>' . get_string('dept_manual', 'local_departments') . '</button></a></div>';
        $mform1->display();
        echo $OUTPUT->footer();
        die;
    }
} else {//if not empty of variable $iid,it get the content from the csv content
    $cir = new csv_import_reader($iid, 'uploaduser');
    $filecolumns = uu_validate_user_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
}

//---creating  object for second form---------------------- 
$mform2 = new admin_user_form2(null, array('columns' => $filecolumns, 'data' => array('iid' => $iid, 'previewrows' => $previewrows)));
// If a file has been uploaded, then process it
if ($formdata = $mform2->is_cancelled()) {
    $cir->cleanup(true);
    $returnurl = new moodle_url('/local/users/upload.php');
    redirect($returnurl);
} else if ($formdata = $mform2->get_data()) {
    // Print the header
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('uploadusersresult', 'local_users'));
    $currenttab = 'upload';
    //adding tabs
    $myuser->createtabview($currenttab);
    $optype = $formdata->uutype;
    $updatetype = isset($formdata->uuupdatetype) ? $formdata->uuupdatetype : 0;
    // verification moved to two places: after upload and into form2
    $usersnew = 0;
    $usersupdated = 0;
    $usersuptodate = 0; //not printed yet anywhere
    $userserrors = 0;
    $deletes = 0;
    $deleteerrors = 0;
    $usersskipped = 0;
    // init csv import helper
    $cir->init();
    $linenum = 1; //column header is first line
    // init upload progress tracker------this class used to keeping track of code(each rows and columns)-------------
    $upt = new uu_progress_tracker();
    // $upt->start(); // start table
    $data = new stdclass();
    loop:
    while ($line = $cir->next()) {
        $upt->flush();
        $linenum++;
        // $upt->track('line', $linenum);
        $user = new stdClass();
        // add fields to user object
        foreach ($line as $keynum => $value) {
            if (!isset($filecolumns[$keynum])) {
                // this should not happen
                continue;
            }
            $key = $filecolumns[$keynum];
            $user->$key = $value;
        }
        if (!isset($user->email) && !isset($user->username)) {
            // prevent warnings below
            $user->email = '';
            $user->username = '';
        }
        //--------------------------------this is used to include only new users-----------------
        if ($optype == 'UU_USER_ADDNEW') {
            // user creation is a special case - the username may be constructed from templates using firstname and lastname
            // better never try this in mixed update types
            $error = false;
            if (!isset($user->email) or $user->email === '' or ! isset($user->username) or $user->username === '') {
                $error = true;
            }
            if ($error) {
                $userserrors++;
                continue;
            }
        }
        // make sure we really have username
        if (empty($user->email) && empty($user->username)) {

            $userserrors++;
            continue;
        }
        $uname = trim($user->username);
        $user->email = trim($user->email);
        $existinguser = $DB->get_record_sql("select * from {user} where email = '$user->email' ");
        $user->password = hash_internal_user_password($user->password);
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($user->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($user->$field)) {
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
            case 'UU_USER_ADDNEW':
                if ($existinguser) {
                    $usersskipped++;
                    $skip = true;
                }
                break;
            case 'UU_USER_ADD_UPDATE':
                break;
            case 'UU_USER_UPDATE':
                if (!$existinguser) {
                    $usersskipped++;
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
        if (!empty($existinguser)) {
            $user->id = $existinguser->id;
            $doupdate = false;
            $dologout = false;
            if ($updatetype != 'UU_UPDATE_NOCHANGES') {
                $allcolumns = array_merge($STD_FIELDS, $PRF_FIELDS);
                foreach ($allcolumns as $column) {
                    if (!property_exists($user, $column) or ! property_exists($existinguser, $column)) {
                        // this should never happen
                        continue;
                    }
                    if ($updatetype == 'UU_UPDATE_MISSING') {
                        if (!is_null($existinguser->$column) and $existinguser->$column !== '') {
                            continue;
                        }
                    } else if ($updatetype == 'UU_UPDATE_ALLOVERRIDE') {
                        // we override everything
                    } else if ($updatetype == 'UU_UPDATE_FILEOVERRIDE') {
                        if (!empty($formdefaults[$column])) {
                            // do not override with form defaults
                            continue;
                        }
                    }
                    if ($existinguser->$column !== trim($user->$column)) {
                        $existinguser->$column = $user->$column;
                        $doupdate = true;
                    }
                }
            }

            if ($doupdate) {
                // check username
                if (empty($user->username)) {
                    echo '<h3 style="color:red;">Please enter username in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $sql = "select * from {user} where email = '$user->email' and username = '$user->username' ";
                $result = $DB->get_records_sql($sql);
                if (empty($result)) {
                    $usernames = $DB->get_records_sql('select username from {user} ');
                    $c = 0;
                    foreach ($usernames as $username) {
                        $str1 = trim($username->username);
                        $str2 = trim($user->username);
                        $compare = strcasecmp($str1, $str2);
                        if ($compare == 0) {
                            ++$c;
                            break;
                        }
                    }
                    if ($c != 0) {
                        echo '<h3 style="color:red;">User name "' . $user->username . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                        goto loop;
                    }
                }
                // end
                // check schoolname
                $fac = trim($user->schoolname);
                $school = $DB->get_record('local_school', array('fullname' => $fac));
                if (empty($school)) {
                    echo '<h3 style="color:red;">Invalid School name "' . $user->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $hier = new hierarchy();
                $schools = $hier->get_assignedschools();
                if (is_siteadmin()) {
                    $schools = $hier->get_school_items();
                }
                $scid = $school->id;
                $c = 0;
                foreach ($schools as $scl) {
                    if ($scid == $scl->id) {
                        ++$c;
                        break;
                    }
                }
                if ($c == 0) {
                    echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $user->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                // check dob
                $year = date("m/d/Y");
                $year1 = strtotime($year);
                if (!empty($user->dob)) {
                    $d = explode('/', $user->dob);
                    if (!checkdate($d[0], $d[1], $d[2])) {
                        echo '<h3 style="color:red;">Invalid  dob "' . $user->dob . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                        goto loop;
                    }
                    if (strtotime($user->dob) >= $year1) {
                        echo '<h3 style="color:red;">Invalid  dob "' . $user->dob . '" entered at line no. "' . $linenum . '" of uploaded excelsheet. dob should be less than present date.</h3>';
                        goto loop;
                    }
                }
                // check roleid
                $userrole = trim($user->role);
                $userroleid = $DB->get_field('role', 'id', array('shortname' => $userrole));
                if (empty($userroleid)) {
                    echo '<h3 style="color:red;">Enter valid role in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                $sql = "SELECT r.* FROM {role} r, {role_context_levels} rc WHERE r.id = rc.roleid AND rc.contextlevel = " . CONTEXT_SYSTEM . "";
                if (!is_siteadmin()) {
                    $sql .= " AND r.id <> 1";
                }
                $sql .= " ORDER BY r.sortorder ASC";
                $roles = $DB->get_records_sql($sql);
                $r = 0;
                foreach ($roles as $roles) {
                    if ($roles->id == $userroleid) {
                        ++$r;
                        break;
                    }
                }
                if ($r == 0) {
                    echo '<h3 style="color:red;">Sorry you can not assign role "' . $user->role . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                // check password
                $errmsg = '';
                $policy = check_password_policy($user->password, $errmsg);
                if ($policy == false) {
                    echo '<h3 style="color:red;">Enter password in line no. "' . $linenum . '" of uploaded excelsheet according to password policy mentioned in Help Mnual .</h3>';
                    goto loop;
                }
                if (empty($uname)) {
                    echo '<h3 style="color:red;">Please enter usename in line no. "' . $linenum . '" of uploaded excelsheet..</h3>';
                    goto loop;
                }
                // check country
                $country = get_string_manager()->get_list_of_countries();
                if (!empty($user->country)) {
                    if (!array_key_exists($user->country, $country)) {
                        echo '<h3 style="color:red;">Please enter valid code for country in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                        goto loop;
                    }
                }
                // check firstname 
                if (empty($user->firstname)) {
                    echo '<h3 style="color:red;">Please enter firstname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                // check lastname
                if (empty($user->lastname)) {
                    echo '<h3 style="color:red;">Please enter lastname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
                // check phonenumber
                $phone_length = strlen($user->phone);
                if ($phone_length > 15 || $phone_length < 10) {
                    echo '<h3 style="color:red;">Enter valid phone number in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }

                $existinguser->username = $uname;
                $existinguser->password = $user->password;
                $existinguser->firstname = $user->firstname;
                $existinguser->middlename = $user->middlename;
                $existinguser->lastname = $user->lastname;
                $existinguser->gender = $user->gender;
                $existinguser->address = $user->address;
                $existinguser->city = $user->city;
                $dob = strtotime($user->dob);
                $existinguser->dob = $dob;
                $existinguser->country = $user->country;
                $existinguser->phone1 = $user->phone;
                $existinguser->email = $user->email;
                $existinguser->schoolid = $scid;
                $existinguser->roleid = $userroleid;
                $existinguser->description = $user->description;
                $data->timemodified = time();
                $DB->update_record('user', $existinguser);
                $existinguser->id = $DB->get_field('local_users', 'id', array('userid' => $existinguser->id));
                $DB->update_record('local_users', $existinguser);
                $usersupdated++;
            } else {

                $usersuptodate++;
            }
        } else {     // else start for [!empty($existinguser]
            // check username
            if (empty($user->username)) {
                echo '<h3 style="color:red;">Please enter username in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $usernames = $DB->get_records_sql("select username from {user} ");
            $c = 0;
            foreach ($usernames as $username) {
                $str1 = trim($username->username);
                $str2 = trim($user->username);
                $compare = strcasecmp($str1, $str2);
                if ($compare == 0) {
                    ++$c;
                    break;
                }
            }
            if ($c != 0) {
                echo '<h3 style="color:red;">User name "' . $user->username . '" entered at line no. "' . $linenum . '" of uploaded excelsheet is already exists.</h3>';
                goto loop;
            }
            // check  schools
            $fac = trim($user->schoolname);
            $school = $DB->get_record('local_school', array('fullname' => $fac));
            if (empty($school)) {
                echo '<h3 style="color:red;">Invalid School name "' . $user->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $scid = $school->id;
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
                echo '<h3 style="color:red;">Sorry you are not assigned to this school "' . $user->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // check dob
            $year = date("m/d/Y");
            $year1 = strtotime($year);
            if (!empty($user->dob)) {
                $d = explode('/', $user->dob);
                if (!checkdate($d[0], $d[1], $d[2])) {
                    echo '<h3 style="color:red;">Invalid  dob "' . $user->dob . '" entered at line no. "' . $linenum . '" of uploaded excelsheet. </h3>';
                    goto loop;
                }
                if (strtotime($user->dob) >= $year1) {
                    echo '<h3 style="color:red;">Invalid  dob "' . $user->dob . '" entered at line no. "' . $linenum . '" of uploaded excelsheet. dob should be less than present date.</h3>';
                    goto loop;
                }
            }
            $country = get_string_manager()->get_list_of_countries();
            // check country
            $country = get_string_manager()->get_list_of_countries();
            if (!empty($user->country)) {
                if (!array_key_exists($user->country, $country)) {
                    echo '<h3 style="color:red;">Please enter valid code for country in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                    goto loop;
                }
            }
            // check firstname 
            if (empty($user->firstname)) {
                echo '<h3 style="color:red;">Please enter firstname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // check lastname
            if (empty($user->lastname)) {
                echo '<h3 style="color:red;">Please enter lastname in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // check roleid 
            if (empty($user->role)) {
                echo '<h3 style="color:red;">Please enter role in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            // check phonenumber
            //$phone_length = strlen($user->phone);
            //if ($phone_length > 15 || $phone_length < 10) {
            //    echo '<h3 style="color:red;">Enter valid phone number in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
            //    goto loop;
            //}
            // check roleid
            $userrole = trim($user->role);
            $userroleid = $DB->get_field('role', 'id', array('shortname' => $userrole));
            if (empty($userroleid)) {
                echo '<h3 style="color:red;">Enter valid role in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }
            $sql = "SELECT r.* FROM {role} r, {role_context_levels} rc WHERE r.id = rc.roleid AND rc.contextlevel = " . CONTEXT_SYSTEM . "";
            if (!is_siteadmin()) {
                $sql .= " AND r.id <> 1";
            }
            $sql .= " ORDER BY r.sortorder ASC";
            $roles = $DB->get_records_sql($sql);
            $r = 0;
            foreach ($roles as $roles) {
                if ($roles->id == $userroleid) {
                    ++$r;
                    break;
                }
            }
            if ($r == 0) {
                echo '<h3 style="color:red;">Sorry you are not assign role "' . $user->role . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
                goto loop;
            }

            // check password
            $errmsg = '';
            $policy = check_password_policy($user->password, $errmsg);
            if ($policy == false) {
                echo '<h3 style="color:red;">Enter password in line no. "' . $linenum . '" of uploaded excelsheet according to password policy mentioned in Help Mnual .</h3>';
                goto loop;
            }
            if (empty($uname)) {
                echo '<h3 style="color:red;">Please enter usename in line no. "' . $linenum . '" of uploaded excelsheet..</h3>';
                goto loop;
            }
            $data->username = $uname;
            $data->password = $user->password;
            $data->firstname = $user->firstname;
            $data->middlename = $user->middlename;
            $data->lastname = $user->lastname;
            $data->gender = $user->gender;
            $data->address = $user->address;
            $data->city = $user->city;
            $dob = strtotime($user->dob);
            $data->dob = $dob;
            $data->country = $user->country;
            $data->phone1 = $user->phone;
            $data->email = $user->email;
            $data->schoolid = $scid;
            $data->roleid = $userroleid;
            $data->description = $user->description;
            $data->timecreated = time();
            $data->id = insert_newuser($data);
            $data->id ++;
            $usersnew++;
        }
    }
    // $upt->close(); // close table
    // $cir->close();
    $cir->cleanup(true);
    echo $OUTPUT->box_start('boxwidthnarrow boxaligncenter generalbox', 'uploadresults');
    echo '<p>';
    if ($optype != 'UU_USER_UPDATE') {
        echo get_string('userscreated', 'local_users') . ': ' . $usersnew . '<br />';
    }
    if ($optype == 'UU_USER_UPDATE' or $optype == 'UU_USER_ADD_UPDATE') {
        echo get_string('usersupdated', 'local_users') . ': ' . $usersupdated . '<br />';
    }
    if ($usersskipped) {
        echo get_string('usersskipped', 'local_users') . ': ' . $usersskipped . '<br />';
    }
    if ($userserrors)
        echo get_string('errors', 'local_users') . ': ' . $userserrors;echo'</p>';
    if ($userserrors) {
        echo '<h4>Please fill the sheet without any errors. Refer Help Manual for assistance.</h4>';
    }
    echo $OUTPUT->box_end();
    if ($optype == 'UU_USER_UPDATE' or $optype == 'UU_USER_ADD_UPDATE') {
        if ($usersnew == 0 && $usersupdated == 0 && $usersskipped < 1) {
            echo '<h4> Users skipped because records with those names are already exist.</h4>';
        }
    }
    if ($usersskipped) {
        echo $OUTPUT->box_start('generalbox');
        if ($usersskipped == 1)
            echo '<h4> User skipped because record with that email or username is  already exists.</h4>';
        else
            echo '<h4>' . $usersskipped . ' users skipped because records with that email or username are already exists.</h4>';
        echo $OUTPUT->box_end();
    }
    echo '<div style="margin-left:35%;"><a href="index.php"><button>Continue</button></a></div>';
    echo $OUTPUT->footer();
    die;
}
// Print the header
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('uploaduserspreview', 'local_users'));
$currenttab = 'upload';
//adding tabs
$myuser->createtabview($currenttab);
// Print the form if valid values are available
$noerror = true;
if ($noerror) {
    $mform2->display();
}
echo $OUTPUT->footer();
die;

