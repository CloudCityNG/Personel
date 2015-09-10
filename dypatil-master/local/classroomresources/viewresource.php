<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/viewresource.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:view', $systemcontext)) {
    print_cobalterror('permissions_error','local_collegestructure');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewresource.php'));
$PAGE->navbar->add(get_string('vresource', 'local_classroomresources'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$hierarchy = new hierarchy();
$currenttab = 'viewresource';
$resource = cobalt_resources::get_instance();
// error handling - while school not created yet 
$hierarchy->get_school_items();

$resource->resource_tabs($currenttab, $id = -1);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('resourceview', 'local_classroomresources'));
}
$resources = $resource->cobalt_resource();
if (empty($resources)) {
    echo get_string('noresource', 'local_classroomresources');
} else {
    $data = array();
    foreach ($resources as $resource) {
        $result = array();
        $linkcss = $resource->visible ? ' ' : 'dimmed';
        $result[] = '<span class=' . $linkcss . '>' . $resource->fullname . '</span>';
        $result[] = $resource->shortname;

        $result[] = $resource->schoolname;
        if (has_capability('local/classroomresources:manage', $systemcontext))
            $result[] = $hierarchy->get_actions('classroomresources', 'resource', $resource->id, $resource->visible);

        $data[] = $result;
    }
    $PAGE->requires->js('/local/classroomresources/js/resource.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "resource";
    $table->head = array(
        get_string('resource', 'local_classroomresources'),
        get_string('shortname', 'local_classroomresources'),
        get_string('schoolid', 'local_collegestructure'));

    if (has_capability('local/classroomresources:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_classroomresources');

    $table->size = array('20%', '20%', '20%', '20%', '19%');
    $table->align = array('left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>
