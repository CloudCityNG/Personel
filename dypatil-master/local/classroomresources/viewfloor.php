<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/viewfloor.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:view', $systemcontext)) {
    print_cobalterror('permissions_error','college_structure');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewfloor.php'));
$PAGE->navbar->add(get_string('viewfloor', 'local_classroomresources'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$hierarchy = new hierarchy();
$currenttab = 'viewfloor';
$resource = cobalt_resources::get_instance();
// error handling - while school not created yet 
$hierarchy->get_school_items();

$resource->floor_tabs($currenttab);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('floorview', 'local_classroomresources'));
}
$floors = $resource->cobalt_floor();
if (empty($floors)) {
    echo get_string('noflor', 'local_classroomresources');
} else {
    $data = array();
    foreach ($floors as $floor) {
        $result = array();
        $linkcss = $floor->visible ? ' ' : 'class="dimmed" ';
        $result[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/classroomresources/floordetails.php?id=' . $floor->id . '">' . format_string($floor->fullname) . '</a>';

        $result[] = $floor->shortname;
        $result[] = $floor->buildingname;
        $result[] = $floor->schoolname;

        if (has_capability('local/classroomresources:manage', $systemcontext))
            $result[] = $hierarchy->get_actions('classroomresources', 'floor', $floor->id, $floor->visible);

        $data[] = $result;
    }
    $PAGE->requires->js('/local/classroomresources/js/floor.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "floor";
    $table->head = array(
        get_string('floor', 'local_classroomresources'),
        get_string('shortname', 'local_classroomresources'),
        get_string('building', 'local_classroomresources'),
        get_string('schoolid', 'local_collegestructure'));

    if (has_capability('local/classroomresources:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_classroomresources');

    $table->size = array('16%', '16%', '16%', '16%', '16%');
    $table->align = array('left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>
