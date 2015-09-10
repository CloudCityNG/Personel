<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$strheading = get_string('pluginname', 'local_departments') . ' : ' . get_string('manual', 'local_departments');
$PAGE->set_title($strheading);
$PAGE->set_url('/local/departments/help.php');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('uploaddepartments', 'local_departments'), new moodle_url('/local/departments/upload.php'));
$PAGE->navbar->add(get_string('dept_manual', 'local_departments'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('dept_manual', 'local_departments'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('dept_helpmanual', 'local_departments'));
    /*
     * ###Bugreport #182-Help manual>Upload Course Libraries
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Changed string name to backupload to dept_backupload
     */
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('dept_back_upload', 'local_departments') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('delimited', 'local_scheduleexam') . '</p>') . '</b>';
}

echo get_string('dept_download_help', 'local_departments');
echo $OUTPUT->footer();
?>