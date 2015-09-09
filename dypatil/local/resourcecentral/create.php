<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $USER, $DB;
require_once($CFG->dirroot . '/local/resourcecentral/resource_central_forms.php');
$id = optional_param('id', -1, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$systemcontext = context_system::instance();


if ($id > 0) {

    if (!($tool = $DB->get_record('local_resourcecentral', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_resourcecentral');
    } else {

        $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_course::instance($courseid));
$courserecord = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_course($courserecord);
require_login();

$PAGE->set_url('/local/resourcecentral/create.php');
$PAGE->set_heading(get_string('pluginname', 'local_resourcecentral'));
$PAGE->navbar->add(get_string('pluginname', 'local_resourcecentral'), new moodle_url('/local/resourcecentral/index.php?id=' . $courseid . ''));
$PAGE->navbar->add(get_string('createresource', 'local_resourcecentral'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_resourcecentral'));
$hierarchy = new hierarchy();
$admision = cobalt_admission::get_instance();
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('resource_create_desc', 'local_resourcecentral'));
}

$maxfiles = 99;
$maxbytes = $CFG->maxbytes;
$definitionoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes, 'context' => $context);
$attachmentoptions = array('subdirs' => false, 'maxfiles' => $maxfiles, 'maxbytes' => $maxbytes);
$context = context_system::instance();
$mform = new resource_central_form(null, array('definitionoptions' => $definitionoptions, 'attachmentoptions' => $attachmentoptions, 'id' => $id, 'courseid' => $courseid));
$mform->set_data($tool);
$data = $mform->get_data();
$mform->display();
if ($mform->is_cancelled()) {
    $returnurl = new moodle_url('/local/resourcecentral/index.php?id=' . $courseid . '');
    redirect($returnurl);
}

if ($data) {
    if ($data->id > 0) {
        $data->description = $data->description['text'];
        $data->timecreated = time();
        $data->usermodified = $USER->id;
        $data->id = $DB->update_record('local_resourcecentral', $data);
        $data = file_postupdate_standard_editor($data, 'definition', $definitionoptions, $context, 'user', 'draft', $data->id);
        $data = file_postupdate_standard_filemanager($data, 'itemid', $attachmentoptions, $context, 'user', 'draft', $data->id);
        $returnurl = new moodle_url('/local/resourcecentral/index.php?id=' . $data->courseid . '');
    } else {
        $data->description = $data->description['text'];
        $data->timecreated = time();
        $data->usermodified = $USER->id;
        $data->id = $DB->insert_record('local_resourcecentral', $data);
        $data = file_postupdate_standard_editor($data, 'definition', $definitionoptions, $context, 'user', 'draft', $data->id);
        $data = file_postupdate_standard_filemanager($data, 'itemid', $attachmentoptions, $context, 'user', 'draft', $data->id);
        $returnurl = new moodle_url('/local/resourcecentral/index.php?id=' . $data->courseid . '');
    }
    redirect($returnurl);
}
echo $OUTPUT->footer();
