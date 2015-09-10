<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/admission/lib.php');
require_once($CFG->dirroot . '/local/admission/application_form.php');
$id = optional_param('id', 0, PARAM_INT);
$i = optional_param('i', 0, PARAM_INT);
$download = optional_param('download', 0, PARAM_INT);
$file = optional_param('file', 0, PARAM_RAW);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('viewfile_title', 'local_admission'));
require_login();
$PAGE->set_url('/local/admission/test.php');
$PAGE->set_heading(get_string('pluginname', 'local_admission'));
$PAGE->navbar->add(get_string('pluginname', 'local_admission'), new moodle_url('/local/admission/viewapplicant.php'));
$PAGE->navbar->add(get_string('viewfile', 'local_admission'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manage', 'local_admission'));
$admision = cobalt_admission::get_instance();
if ($id > 0) {
    $firstname = ucwords($DB->get_field('local_admission', 'firstname', array('id' => $id)));
    $lastname = ucwords($DB->get_field('local_admission', 'lastname', array('id' => $id)));
    $name = $firstname . ' ' . $lastname;
    echo "<h4><b>" . get_string('view_attachfile', 'local_admission') . " : $name</b></h4>";
    $filelist =local_admission_display_data($id);
    $data = array();
    foreach ($filelist as $file) {
        $result= array();
        $result[] =  $file;  
        $data[] = $result;
    }
    $table = new html_table();
    $table->head = array(
        get_string('filename', 'local_admission')
    
    );
    $table->size = array('90%');
    $table->align = array('left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo html_writer::tag('a', 'Back', array('href' => '' . $CFG->wwwroot . '/local/admission/viewapplicant.php'));

echo $OUTPUT->footer();
?>
