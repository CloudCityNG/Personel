<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB, $tool2, $tool;
require_once($CFG->dirroot . '/local/profilechange/changeprofile_form.php');
require_once($CFG->dirroot . '/local/lib.php');
$id = optional_param('id', $USER->id, PARAM_INT);
$flag = optional_param('flag', 0, PARAM_INT);
$confirmreq = optional_param('confirm', 0, PARAM_INT);
$rec_id = optional_param('recid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/profilechange/changeprofile.php');
$PAGE->set_title(get_string('pluginname', 'local_profilechange'));
$PAGE->set_heading(get_string('pluginname', 'local_profilechange'));
$hierarchy = new hierarchy();
$PAGE->set_pagelayout('admin');

require_login();
$PAGE->navbar->add(get_string('myprofile', 'local_profilechange'));
$PAGE->navbar->add(get_string('pluginname', 'local_profilechange'));

$params = array();
if ($flag) {
    $params['flag'] = $flag;
}
if ($confirmreq && $rec_id) {
    $params['confirm'] = $confirmreq;
    $params['recid'] = $rec_id;
    $id = $DB->get_field('local_request_profile_change', 'studentid', array('id' => $rec_id));
}
$mform = new changeprofile_form(null, $params);
$sql = "SELECT * FROM {user} u, {local_users} lu WHERE lu.userid = u.id AND u.id = {$id}";
if (!isloggedin() || isguestuser()) {
    print_error('You dont have permission');
} else {
    if (!$tool = $DB->get_record_sql($sql)) {
        redirect($CFG->wwwroot . "/user/edit.php?id=$id");
    }
}
$tool->id = $tool->userid;

/* Bug report #185  -  Profile Request
 * @author hemalatha c arun <hemalatha@eabyas.in>
 * Resolved- while editing profile change added condition to show gender
 */
$tool->gender = strtolower($tool->gender);
$mform->set_data($tool);

$nexturl = '../../local/profilechange/changeprofile.php';
$homeurl = $CFG->wwwroot;
if ($flag) {
    $nexturl = new moodle_url($CFG->wwwroot . '/local/users/index.php');
}if ($confirmreq) {
    $nexturl = new moodle_url($CFG->wwwroot . '/local/request/approval_profile.php');
}
//Form processing and displaying is done here

if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
    redirect($nexturl);
} else if ($fromform = $mform->get_data()) {

    //In this case you process validated data. $mform->get_data() returns data posted in form.
    $data = $mform->get_data();
    //print_object($data);
    $DB->update_record('user', $data);
    $data->id = $DB->get_field('local_users', 'id', array('userid' => $data->userid));
    $DB->update_record('local_users', $data);
    $data->applicantid = $DB->get_field('local_users', 'applicantid', array('userid' => $data->userid));
    if($data->applicantid){
        $data->id = $data->applicantid;
        $DB->update_record('local_admission', $data);
    }
    $conf = new object();
    $conf->name = $data->firstname . ' ' . $data->lastname;
    $message = get_string('updatedata', 'local_profilechange');
    if ($flag) {
        $message = get_string('userupdatesuccess', 'local_users', $conf);
    }if ($confirmreq) {
        $message = get_string('profileupdatesuccess', 'local_request');
        $DB->set_field('local_request_profile_change', 'reg_approval', '1', array('id' => $rec_id));
        $DB->set_field('local_request_profile_change', 'regapproval_date', time(), array('id' => $rec_id));
    }
    $nexturl = new moodle_url($CFG->wwwroot . '/local/users/profile.php?id=' . $data->userid . '');
    $options = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $nexturl, $options);
} else {
    
}
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('pluginname', 'local_profilechange'));
if (!$flag && !$confirmreq)
    if (!has_capability('local/collegestructure:manage', $systemcontext) && !is_siteadmin()) {
        $context = context_user::instance($USER->id);
        if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
            echo $OUTPUT->box(get_string('profile_change_desc', 'local_profilechange'));
        }
    } else {
        echo $OUTPUT->box(get_string('profilechangedes', 'local_profilechange'));
    }
$mform->display();

echo $OUTPUT->footer();
?>
