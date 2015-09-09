<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/onlinepayment/paytax_form.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

global $DB;
$id = required_param('id', PARAM_INT);

$hierarchy = new hierarchy();
$tax = tax::getInstance();
$context =  context_course::instance($id);

//get the records from the table to edit
if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidid', 'local_onlinepayment');
}

//If the loggedin user have the required capability allow the page
//if (!has_capability('local/payment:createtax', $systemcontext)) {
//  print_error('You dont have permissions');
//}
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($context);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_title($course->shortname . ': ' . $course->fullname);
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('modcostsettings', 'local_onlinepayment'), new moodle_url('/local/onlinepayment/modcost.php', array('id' => $id)));
$PAGE->navbar->add($course->fullname);
$PAGE->set_url('/local/onlinepayment/moocview.php', array('id' => $id));

//display the page
echo $OUTPUT->header();
echo $OUTPUT->heading($course->fullname);
echo '<h3>' . $course->fullname . '</h3>';
echo $OUTPUT->box($course->summary);
echo $OUTPUT->footer();
