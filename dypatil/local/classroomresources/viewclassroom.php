<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/viewclassroom.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:view', $systemcontext)) {
    print_cobalterror('permissions_error','local_Collegestructure');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewclassroom.php'));
$PAGE->navbar->add(get_string('vroom', 'local_classroomresources'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('manage', 'local_classroomresources'));
$hierarchy = new hierarchy();
$currenttab = 'viewclassroom';
$resource = cobalt_resources::get_instance();

// error handling - while school not created yet 
$hierarchy->get_school_items();

$resource->class_tabs($currenttab, $id = -1);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('classroomview', 'local_classroomresources'));
}
$clases = $resource->cobalt_classroom();
if (empty($clases)) {
    echo get_string('noclsroom', 'local_classroomresources');
} else {
    $data = array();
    foreach ($clases as $class) {
        $result = array();
        $linkcss = $class->visible ? ' ' : 'class="dimmed" ';
        $result[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/classroomresources/classroomdetails.php?id=' . $class->id . '">' . format_string($class->fullname) . '</a>';

        $result[] = $class->shortname;
        $result[] = $class->floorname;
        $result[] = $class->buildingname;
        $result[] = $class->schoolname;
        if (has_capability('local/classroomresources:manage', $systemcontext))
            $result[] = $hierarchy->get_actions('classroomresources', 'classroom', $class->id, $class->visible);
        $data[] = $result;
    }
    $PAGE->requires->js('/local/classroomresources/js/class.js');
    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    $table = new html_table();
    $table->id = "class";
    $table->head = array(
        get_string('classroom', 'local_classroomresources'),
        get_string('shortname', 'local_classroomresources'),
        get_string('floor', 'local_classroomresources'),
        get_string('building', 'local_classroomresources'),
        get_string('schoolname', 'local_collegestructure'));

    if (has_capability('local/classroomresources:manage', $systemcontext))
        $table->head[] = get_string('action', 'local_classroomresources');

    $table->size = array('14%', '14%', '14%', '14%', '14%', '14%', '14%');
    $table->align = array('left', 'left', 'left', 'left', 'left', 'left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
?>
