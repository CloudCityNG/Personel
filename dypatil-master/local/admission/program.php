<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/admission/lib.php');
$id = optional_param('id', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_url('/local/admission/program.php');
$PAGE->set_title(get_string('program_title', 'local_programs'));
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('apply', 'local_admission'), new moodle_url('/local/admission/index.php'));
$PAGE->navbar->add(get_string('programdetails', 'local_programs'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('programdetails', 'local_programs'));
$program = $DB->get_record('local_program', array('id' => $id));
$sql = "SELECT lcc.id,lcc.fullname FROM 
{local_curriculum} lc,
{local_curriculum_plancourses} lcpc,
{local_cobaltcourses} lcc
WHERE lc.programid={$id} AND
lc.id=lcpc.curriculumid AND lcpc.courseid=lcc.id ";
$courses = $DB->get_records_sql($sql);
$count = count($courses);
$allcourses = '';
$i = 1;
switch ($program->type) {
    case 1:
        $prerequisites = get_string('prerequisites1', 'local_admission');
        break;
    case 2:
        $prerequisites = get_string('prerequisites2', 'local_admission');
        break;
    case 3:
        $prerequisites = get_string('prerequisites3', 'local_admission');
        break;
}
$table = new html_table();
$data = array();
$table->align = array('left', 'left');
$table->size = array('35%', '35%');
$table->width = '100%';
$table->data[] = array('<b>' . get_string('programname', 'local_programs') . '</b>', $program->fullname);
$table->data[] = array('<b>' . get_string('duration', 'local_programs') . '</b>', $program->duration);
$table->data[] = array('<b>' . get_string('description', 'local_programs') . '</b>', $program->description);
$table->data[] = array('<b>' . get_string('prerequisites', 'local_admission') . '</b>', $prerequisites);
foreach ($courses as $course) {
    $allcourses.=html_writer::tag('a', $i . '.' . $course->fullname, array('href' => '' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->id . ''));
    $allcourses.='<br/>';
    $i++;
}
$table->data[] = array('<b>' . get_string('list_courses', 'local_admission') . '</b>', $allcourses);
echo html_writer::table($table);
echo $OUTPUT->footer();
?>