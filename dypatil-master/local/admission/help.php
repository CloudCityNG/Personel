<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/admission/help.php');
$PAGE->set_pagelayout('admin');
$string = get_string('manage', 'local_admission') . ' : ' . get_string('manual', 'local_admission');
$PAGE->set_title($string);
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage', 'local_admission'), new moodle_url('/local/admission/upload.php'));
$PAGE->navbar->add(get_string('uploadadmissions', 'local_admission'), new moodle_url('/local/admission/upload.php'));
$PAGE->navbar->add(get_string('manual', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manual', 'local_admission'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('helpmanual', 'local_admission'));
    echo '<div style="float:right;"><a href="upload.php"><button>' . get_string('back_upload', 'local_admission') . '</button></a></div>';
}
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo '<b >' . $OUTPUT->box('<p style="color:red;">' . get_string('delimited', 'local_scheduleexam') . '</p>') . '</b>';
}
$country = get_string_manager()->get_list_of_countries();
$countries = array();
foreach ($country as $key => $value) {
    $countries[] = $key . ' => ' . $value;
}
echo get_string('help_table1', 'local_admission');
$select = new single_select(new moodle_url('#'), 'proid', $countries, null, '');
$select->set_label('');
echo $OUTPUT->render($select);
echo get_string('help_table2', 'local_admission');
$select = new single_select(new moodle_url('#'), 'proid', $countries, null, '');
$select->set_label('');
echo $OUTPUT->render($select);
echo get_string('help_table3', 'local_admission');
$select = new single_select(new moodle_url('#'), 'proid', $countries, null, '');
$select->set_label('');
echo $OUTPUT->render($select);
echo get_string('help_table4', 'local_admission');
echo $OUTPUT->footer();
?>
