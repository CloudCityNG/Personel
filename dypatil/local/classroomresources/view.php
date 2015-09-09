<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/view.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:view', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/view.php'));
$PAGE->navbar->add(get_string('rview', 'local_classroomresources'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$hierarchy = new hierarchy();
$currenttab = 'viewr';
$resource = cobalt_resources::get_instance();

$resource->resource_tabs($currenttab, $id = -1);
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('assign', 'local_classroomresources'));
}
$list = $resource->assignedresourcelist();
if (empty($list)) {
    echo get_string('resounotassinanysch', 'local_classroomresources');
} else {
    $data = array();
    foreach ($list as $resour) {
        $result = array();
        $result[] = $DB->get_field('local_school', 'fullname', array('id' => $resour->schoolid));
        $result[] = $DB->get_field('local_building', 'fullname', array('id' => $resour->buildingid));
        $result[] = $DB->get_field('local_floor', 'fullname', array('id' => $resour->floorid));
        $result[] = $DB->get_field('local_classroom', 'fullname', array('id' => $resour->classroomid));
        $name = $resource->get_resource_name($resour->resourceid);
        $result[] = $name;
        if (has_capability('local/classroomresources:manage', $systemcontext))
            $result[] = $hierarchy->get_actions('classroomresources', 'assignresource', $resour->id, $resour->visible);

        $data[] = $result;
    }
    $PAGE->requires->js('/local/classroomresources/js/resourcelist.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "resourcelist";
    $table->head = array(
        get_string('schoolid', 'local_collegestructure'),
        get_string('building', 'local_classroomresources'),
        get_string('floor', 'local_classroomresources'),
        get_string('classroom', 'local_classroomresources'),
        get_string('resources', 'local_classroomresources'));

    if (has_capability('local/classroomresources:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_classroomresources');

    $table->size = array('10%', '15%', '15%', '15%', '24%', '15%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>
