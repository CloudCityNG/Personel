<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/index.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:view', $systemcontext)) {
    print_cobalterror('permissions_error', 'local_collegestructure');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/index.php'));
$PAGE->navbar->add(get_string('viewbuilding', 'local_classroomresources'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$currenttab = 'view';
$hierarchy = new hierarchy();
$resource = cobalt_resources::get_instance();
// error handling - while school not created yet 
$hierarchy->get_school_items();

$resource->building_tabs($currenttab);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('buildinglist', 'local_classroomresources'));
}
$buildings = $resource->cobalt_building();

if (empty($buildings)) {
    echo get_string('nobuilcreate', 'local_classroomresources');
} else {
    $data = array();
    foreach ($buildings as $building) {
        $result = array();
        $linkcss = $building->visible ? ' ' : 'class="dimmed" ';
        $result[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/classroomresources/buildingdetails.php?id=' . $building->id . '">' . format_string($building->fullname) . '</a>';

        $result[] = $building->shortname;
        $result[] = $building->schoolname;
        if (has_capability('local/classroomresources:manage', $systemcontext)) {
            $result[] = $hierarchy->get_actions('classroomresources', 'building', $building->id, $building->visible);
        }
        $data[] = $result;
    }
    $PAGE->requires->js('/local/classroomresources/js/building.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "building";
    $table->head = array(
        get_string('building', 'local_classroomresources'),
        get_string('shortname', 'local_classroomresources'),
        get_string('schoolid', 'local_collegestructure'));
    if (has_capability('local/classroomresources:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_classroomresources');
    $table->size = array('20%', '20%', '20%', '20%');
    $table->align = array('left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>
