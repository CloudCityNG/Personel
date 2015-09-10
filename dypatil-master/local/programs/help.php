<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_url('/local/programs/help.php');
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_title(get_string('programs', 'local_programs') . ': ' . get_string('manual', 'local_programs'));
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
$PAGE->navbar->add(get_string('uploadprograms', 'local_programs'), new moodle_url('/local/programs/upload.php'));
$PAGE->navbar->add(get_string('manual', 'local_programs'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_programs'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_programs'));
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('back_upload', 'local_programs') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('delimited', 'local_scheduleexam') . '</p>') . '</b>';
}
$country = get_string_manager()->get_list_of_countries();


$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}

echo get_string('help_tab', 'local_programs');
echo $OUTPUT->footer();

