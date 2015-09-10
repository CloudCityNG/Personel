<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$strheading = get_string('pluginname', 'local_scheduleexam') . ':' . get_string('manual', 'local_scheduleexam');
$PAGE->set_title($strheading);
$PAGE->set_url('/local/scheduleexam/help.php');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_scheduleexam'), new moodle_url('/local/scheduleexam/index.php'));
$PAGE->navbar->add(get_string('uploadexams', 'local_scheduleexam'), new moodle_url('/local/scheduleexam/upload.php'));
$PAGE->navbar->add(get_string('manual', 'local_scheduleexam'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_scheduleexam'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_scheduleexam'));
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('back_upload', 'local_scheduleexam') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('delimited', 'local_scheduleexam') . '</p>') . '</b>';
}
$country = get_string_manager()->get_list_of_countries();
$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}
echo get_string('help_string', 'local_scheduleexam');
echo $OUTPUT->footer();
?>
