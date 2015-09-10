<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/floordetails.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/viewfloor.php'));
echo $OUTPUT->header();
$hierarchy = new hierarchy();
$resource = cobalt_resources::get_instance();
$list = $DB->get_record('local_floor', array('id' => $id));
$classrooms = "SELECT * FROM {local_classroom} where floorid={$id} order by id";
$classroom = $DB->get_records_sql($classrooms);
echo $OUTPUT->heading($list->fullname);
$result = '';
$table = new html_table();
$data = array();
$table->align = array('left', 'left');
$table->size = array('35%', '35%');
$table->width = '100%';
$cell = new html_table_cell();
$cell->text = $list->description;
$cell->colspan = 2;
$table->data[] = array('<b>' . get_string('shortname', 'local_classroomresources') . '</b>', $list->shortname);
$table->data[] = array('<b>' . get_string('fullnames', 'local_classroomresources') . '</b>', $list->fullname);
$table->data[] = array('<b>' . get_string('fullname', 'local_classroomresources') . '</b>', $DB->get_field('local_building', 'fullname', array('id' => $list->buildingid)));
$i = 0;
if (!empty($classroom)) {
    foreach ($classroom as $c) {
        if ($i != 0) {
            $result.=',';
        }
        $result.=$c->fullname;
        $i++;
    }
} else {
    $result = 'No' . strtolower(get_string('classroom', 'local_classroomresources')) . 's';
}
$table->data[] = array('<b>' . get_string('lclass', 'local_classroomresources') . '</b>', $result);
$table->data[] = array('<b>' . get_string('description', 'local_classroomresources') . '</b>', $cell);
echo html_writer::table($table);
echo $OUTPUT->footer();
?>