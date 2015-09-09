<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_url('/local/classroomresources/buildingdetails.php');
$PAGE->set_pagelayout('admin');
require_login();
if (!has_capability('local/classroomresources:manage', $systemcontext)) {
    print_error('You dont have permissions');
}
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname', 'local_classroomresources'));
$PAGE->navbar->add(get_string('pluginname', 'local_classroomresources'), new moodle_url('/local/classroomresources/index.php'));
echo $OUTPUT->header();
$hierarchy = new hierarchy();
$resource = cobalt_resources::get_instance();
$list = $DB->get_record('local_building', array('id' => $id));
$floors = "SELECT * FROM {local_floor} where buildingid={$id} order by id";
$floor = $DB->get_records_sql($floors);
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
if (!empty($floor)) {
    foreach ($floor as $f) {
        $i = 0;
        $result.=$f->fullname . ':';
        $classrooms = "SELECT * FROM {local_classroom} WHERE floorid={$f->id} order by id";
        $classroom = $DB->get_records_sql($classrooms);
        if (!empty($classroom)) {
            foreach ($classroom as $c) {
                if ($i != 0) {
                    $result.=',';
                }
                $i++;
                $result.=$c->fullname;
            }
        } else {
            $result.='No' . substr(get_string('viewclassroom', 'local_classroomresources'), 5, 10);
            ;
        }
        $result.='<br/>';
    }
} else {
    $result = 'No' . substr(get_string('viewfloor', 'local_classroomresources'), 5, 6);
}
$table->data[] = array('<b>' . get_string('viewfloors', 'local_classroomresources') . '/' . get_string('classroom', 'local_classroomresources') . 's</b>', $result);
$table->data[] = array('<b>' . get_string('description', 'local_classroomresources') . '</b>', $cell);
echo html_writer::table($table);
echo $OUTPUT->footer();
?>