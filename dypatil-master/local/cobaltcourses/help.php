<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();

$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/cobaltcourses/help.php');
$PAGE->set_heading($SITE->fullname);
$PAGE->set_title(get_string('cobaltcourses', 'local_cobaltcourses') . ': ' . get_string('manual', 'local_cobaltcourses'));
$PAGE->navbar->add(get_string('pluginname', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/index.php'));
$PAGE->navbar->add(get_string('uploadcourses', 'local_cobaltcourses'), new moodle_url('/local/cobaltcourses/upload.php'));
$PAGE->navbar->add(get_string('manual', 'local_cobaltcourses'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_cobaltcourses'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_cobaltcourses'));
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('back_upload', 'local_cobaltcourses') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('delimited', 'local_scheduleexam') . '</p>') . '</b>';
}
$country = get_string_manager()->get_list_of_countries();


$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}

echo get_string('help_string', 'local_cobaltcourses');
echo $OUTPUT->footer();
?>
