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
 * Change password page.
 *
 * @package    core
 * @subpackage auth
 * @copyright  1999 onwards Martin Dougiamas  http://dougiamas.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/users/change_password_form.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->libdir . '/authlib.php');
$PAGE->https_required();
$id = optional_param('id', SITEID, PARAM_INT); // current course
$return = optional_param('return', 0, PARAM_BOOL); // redirect after password change
$PAGE->set_url('/local/users/change_password.php');
$hierarchy = new hierarchy();

$PAGE->set_context(context_system::instance());
$returnurl = new moodle_url('/local/users/profile.php');
$strparticipants = get_string('participants');
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');

// do not require change own password cap if change forced
if (!get_user_preferences('auth_forcepasswordchange', false)) {
    require_capability('moodle/user:changeownpassword', $systemcontext);
}

// do not allow "Logged in as" users to change any passwords
if (\core\session\manager::is_loggedinas()) {
    print_error('cannotcallscript');
}

if (is_mnet_remote_user($USER)) {
    $message = get_string('usercannotchangepassword', 'mnet');
    if ($idprovider = $DB->get_record('mnet_host', array('id' => $USER->mnethostid))) {
        $message .= get_string('userchangepasswordlink', 'mnet', $idprovider);
    }
    print_error('userchangepasswordlink', 'mnet', '', $message);
}

// load the appropriate auth plugin
$userauth = get_auth_plugin($USER->auth);

if (!$userauth->can_change_password()) {
    print_error('nopasswordchange', 'auth');
}

if ($changeurl = $userauth->change_password_url()) {
    // this internal scrip not used
    redirect($changeurl);
}

$mform = new login_change_password_form();

if ($mform->is_cancelled()) {
    redirect($CFG->wwwroot);
} else if ($data = $mform->get_data()) {

    if (!$userauth->user_update_password($USER, $data->newpassword1)) {
        print_error('errorpasswordupdate', 'auth');
    } else {
        $conf = new object();
        $conf->username = $DB->get_field('user', 'username', array('id' => $USER->id));
        $messages = get_string('msg_pwd_change', 'local_users', $conf);
        $userfrom = $DB->get_record('user', array('id' => $USER->id));
        $userto = $DB->get_record('user', array('id' => $USER->id));
        $message_post_message = message_post_message($userfrom, $userto, $messages, FORMAT_HTML);
        $message = get_string('passwordchange', 'local_users');
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation($message, $returnurl, $options);
    }
}

// make sure we really are on the https page when https login required
$PAGE->verify_https_required();

$strchangepassword = get_string('changepassword');

$fullname = fullname($USER, true);
$myprofile = get_string('myprofile', 'local_users');

$PAGE->navbar->add($myprofile);
$PAGE->navbar->add($strchangepassword);
$PAGE->set_title($strchangepassword);
$PAGE->set_heading($COURSE->fullname);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('changepassword'));
$systemcontext = context_system::instance();
$usercontext = context_user::instance($USER->id);
if (has_capability('local/clclasses:enrollclass', $usercontext) && !is_siteadmin()) {

    echo $OUTPUT->box(get_string('changepassdes', 'local_users'));
}if (has_capability('local/clclasses:submitgrades', $systemcontext) && !is_siteadmin()) {

    echo $OUTPUT->box(get_string('changepassinstdes', 'local_users'));
}

if (has_capability('local/collegestructure:manage', $systemcontext)) {
    echo $OUTPUT->box(get_string('changepassregdes', 'local_users'));
}

if (get_user_preferences('auth_forcepasswordchange')) {
    echo $OUTPUT->notification(get_string('forcepasswordchangenotice'));
}

$mform->display();
echo $OUTPUT->footer();
