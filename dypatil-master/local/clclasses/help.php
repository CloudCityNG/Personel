<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('admin');
$strheading = get_string('pluginname', 'local_clclasses') . ' : ' . get_string('manual', 'local_clclasses');
$PAGE->set_title($strheading);
$PAGE->set_url('/local/clclasses/help.php');
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_clclasses'), new moodle_url('/local/users/index.php'));
$PAGE->navbar->add(get_string('uploadclasss', 'local_clclasses'), new moodle_url('/local/users/upload.php'));
$PAGE->navbar->add(get_string('manual', 'local_clclasses'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_clclasses'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_clclasses'));
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('backbutton', 'local_clclasses') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('exelformat', 'local_clclasses') . '</p>') . '</b>';
}
$country = get_string_manager()->get_list_of_countries();
$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}
echo get_string('help_manual', 'local_clclasses');
echo $OUTPUT->footer();
?>
