<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('view_title', 'local_admission'));
require_login();
$PAGE->set_url('/local/admission/view.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('viewapplicantprofile', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$admision = cobalt_admission::get_instance();
$firstname = ucwords($DB->get_field('local_admission', 'firstname', array('id' => $id)));
$lastname = ucwords($DB->get_field('local_admission', 'lastname', array('id' => $id)));
$name = $firstname . ' ' . $lastname;
echo "<h4><b>" . get_string('viewprofile', 'local_admission') . ": $name</b></h4>";
$mform = new view_form(null, array('id' => $id));
$mform->display();
echo $OUTPUT->footer();
?>
