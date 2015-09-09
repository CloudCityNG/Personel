<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/infobuilding.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/index.php'));
$PAGE->navbar->add(get_string('managebuildings', 'local_classroomresources'));
$PAGE->navbar->add(get_string('info', 'local_classroomresources'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$currenttab = 'info';
$hierarchy = new hierarchy();
$resource = cobalt_resources::get_instance();
$resource->building_tabs($currenttab);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('buildinginfo', 'local_classroomresources'));
}
$content = get_string('buildinginfo_des', 'local_classroomresources');
echo '<div class="help_cont">' . $content . '<div>';
echo $OUTPUT->footer();
?>