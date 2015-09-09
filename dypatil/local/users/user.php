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
 * Course Enrolment reports
 *
 * @package    local
 * @subpackage users
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/users/user_form.php');
require_once($CFG->dirroot . '/user/editlib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');
require_once($CFG->libdir . '/gdlib.php');
require_once($CFG->libdir . '/adminlib.php');

$id = optional_param('id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$visible = optional_param('visible', -1, PARAM_INT);
$confirm = optional_param('confirm', 0, PARAM_INT);
global $CFG;

$myuser = users::getInstance();
$hierarchy = new hierarchy();
$conf = new object();

$systemcontext =context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

if (!has_capability('local/collegestructure:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_url('/local/users/user.php');
$PAGE->set_title(get_string('users') . ': ' . get_string('createuser', 'local_users'));
if ($id > 0)
    $PAGE->set_title(get_string('users') . ': ' . get_string('edituser', 'local_users'));
//Header and the navigation bar
$PAGE->set_heading(get_string('newuser', 'local_users'));
$PAGE->navbar->add(get_string('manageusers', 'local_users'), new moodle_url('/local/users/index.php', array('id' => $id)));
$returnurl = new moodle_url('/local/users/index.php', array('id' => $id));

if ($delete) {
    $PAGE->url->param('delete', 1);
    $user = $DB->get_record('user', array('id' => $id));

   
    if (is_siteadmin($id)) {
        $message = get_string('siteadmincannotbedeleted', 'local_users');
        $hierarchy->set_confirmation($message, $returnurl);
    }
    if ($USER->id == $id) {
        $message = get_string('youcannotdeleteyourself', 'local_users');
        $hierarchy->set_confirmation($message, $returnurl);
    }
    $conf->name = $user->firstname . ' ' . $user->lastname;
    if ($confirm and confirm_sesskey()) {
        //delete user
        $myuser->cobalt_delete_user($id);
        $message = get_string('deletesuccess', 'local_users', $conf);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    
    $strheading = get_string('deleteuser', 'local_users');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    if($user) {
		if($DB->record_exists('local_dept_instructor',array('instructorid'=>$user->id))) {
            echo $confirm_msg = get_string('already_instructor', 'local_users', $user->firstname.' '.$user->lastname);
            echo $OUTPUT->continue_button(new moodle_url('/local/users/index.php'));
		} else if($DB->record_exists('local_assignmentor_tostudent',array('mentorid'=>$user->id))) {
			echo $confirm_msg = get_string('already_mentor', 'local_users', $user->firstname.' '.$user->lastname);
			echo $OUTPUT->continue_button(new moodle_url('/local/users/index.php'));
		} else if ($DB->record_exists('local_school_permissions',array('userid'=>$user->id))) {
            echo $confirm_msg = get_string('already_assignedstoschool', 'local_users', $user->firstname.' '.$user->lastname);
            echo $OUTPUT->continue_button(new moodle_url('/local/users/index.php'));
        } else {
			//display confirmation message to delete.
			$yesurl = new moodle_url('/local/users/user.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
			$message = get_string('delconfirm', 'local_users', $conf);
			echo $OUTPUT->confirm($message, $yesurl, $returnurl);
		}
		echo $OUTPUT->footer();
		die;
    }
} 
if ($visible >= 0 && $id && confirm_sesskey()) {
    $conf->name = fullname($DB->get_record('user', array('id' => $id)));
    if (is_siteadmin($id)) {
        $message = get_string('siteadmincannotbesuspended', 'local_users', $conf);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    if ($USER->id == $id) {
        $message = get_string('youcannotsuspendyourself', 'local_users', $conf);
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $DB->set_field('user', 'suspended', $visible, array('id' => $id));
    $string = ($visible) ? 'suspendsuccess' : 'unsuspendsuccess';
    $message = get_string($string, 'local_users', $conf);
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
$heading = ($id > 0) ? get_string('edituser', 'local_users') : get_string('createuser', 'local_users');
$PAGE->navbar->add($heading);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageusers', 'local_users'));

$currenttab = 'addnew';
$myuser->createtabview($currenttab, $id);

//for file starts----------------------------------------------------------------------------------------
$admin = false;
if ($id > 0) {
    if ($id != $USER->id && is_siteadmin($id)) {
        $message = get_string('youcannoteditsiteadmin', 'local_users');
        $options = array('style' => 'notifyproblem');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
    $sql = "SELECT u.*, lu.middlename, lu.gender, lu.dob, lu.dateofapplication FROM {user} u, {local_users} lu WHERE lu.userid = u.id AND u.id = {$id}";
    if (is_siteadmin($id)) {
        $admin = true;
        $sql = "SELECT * FROM {user} WHERE id = {$id}";
    }
    if (!$tool = $DB->get_record_sql($sql)) {
        print_error('invaliduserid');
    }
    if (!$admin) {
        $role = $DB->get_record_sql("SELECT * FROM {role_assignments} WHERE userid = {$tool->id} AND contextid = {$systemcontext->id} ORDER BY id ASC LIMIT 1");
        $tool->roleid = $role->roleid;
        $tool->role_name = ucwords($DB->get_field('role', 'shortname', array('id' => $tool->roleid)));

        $school = $DB->get_record_sql("SELECT * FROM {local_school_permissions} WHERE userid = {$tool->id} AND roleid = {$tool->roleid} ORDER BY id ASC LIMIT 1");
        $tool->schoolid = $school->schoolid;
        $tool->school_name = $DB->get_field('local_school', 'fullname', array('id' => $tool->schoolid));
    } else {
        $tool->role_name = 'Manager';
        $tool->school_name = 'All';
    }
    $usercontext = context_user::instance($tool->id);
    $editoroptions = array(
        'maxfiles' => EDITOR_UNLIMITED_FILES,
        'maxbytes' => $CFG->maxbytes,
        'trusttext' => false,
        'forcehttps' => false,
        'context' => $usercontext
    );

    $tool = file_prepare_standard_editor($tool, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
} else {
    $tool = new stdClass();
    $tool->id = $id;
    $usercontext = null;
    // This is a new user, we don't want to add files here
    $editoroptions = array(
        'maxfiles' => 0,
        'maxbytes' => 0,
        'trusttext' => false,
        'forcehttps' => false,
        'context' => context_system::instance()
    );
}
// Prepare filemanager draft area.
$draftitemid = 0;
$filemanagercontext = $editoroptions['context'];
$filemanageroptions = array('maxbytes' => $CFG->maxbytes,
    'subdirs' => 0,
    'maxfiles' => 1,
    'accepted_types' => 'web_image');
file_prepare_draft_area($draftitemid, $filemanagercontext->id, 'user', 'newicon', 0, $filemanageroptions);
$tool->imagefile = $draftitemid;
//for file ends----------------------------------------------------------------------------------------------------

$userform = new create_user(null, array('editoroptions' => $editoroptions,
    'filemanageroptions' => $filemanageroptions, 'id' => $id, 'admin' => $admin));

$userform->set_data($tool);
if ($userform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $userform->get_data()) {

    $conf->name = $data->firstname . ' ' . $data->lastname;
    if ($data->id > 0) {
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, $usercontext, 'user', 'profile', 0);
        useredit_update_picture($data, $userform, $filemanageroptions);
        //if(!empty($data->password)){
        if (!empty($data->newpassword)) {
            $data->password = hash_internal_user_password($data->newpassword);
            $myuser->email_to_user($data, $id);
        }

        $data->schoolid = $DB->get_field('local_school_permissions', 'schoolid', array('userid' => $data->id));
        $data->theme = $DB->get_field('local_school', 'theme', array('id' => $data->schoolid));

        $DB->update_record('user', $data);
        $data->id = $DB->get_field('local_users', 'id', array('userid' => $data->id));
        $DB->update_record('local_users', $data);
        $options = array('style' => 'notifysuccess');
        $message = get_string('userupdatesuccess', 'local_users', $conf);
    } else {
        $data = file_postupdate_standard_editor($data, 'description', $editoroptions, null, 'user', 'profile', null);

        if (empty($data->password)) {
            $data->password = generatePassword();
        }

        $data->password = hash_internal_user_password($data->newpassword);
        $data->timecreated = time();
        $data->theme = $DB->get_field('local_school', 'theme', array('id' => $data->schoolid));

        if ($data = $myuser->insert_newuser($data)) {
            $data->id = $data->userid;
            //print_object($data);

            useredit_update_picture($data, $userform, $filemanageroptions);
            $myuser->email_to_user($data, $id);
            $options = array('style' => 'notifysuccess');
        }
        $message = get_string('usercreatesuccess', 'local_users', $conf);
    }
    $hierarchy->set_confirmation($message, $returnurl, $options);
}
if ($id < 0)
    echo $OUTPUT->box(get_string('adduserstabdes', 'local_users'));
else
    echo $OUTPUT->box(get_string('edituserstabdes', 'local_users'));
$userform->display();
echo $OUTPUT->footer();
