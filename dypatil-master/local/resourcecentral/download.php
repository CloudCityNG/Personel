<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $USER, $PAGE, $DB, $CFG, $COURSE;

$id = required_param('id', PARAM_INT);
$courseid = optional_param('course', 0, PARAM_INT);
$request = $DB->get_record('local_resourcecentral', array('id' => $id));
$systemcontext = context_system::instance();
//$PAGE->set_pagelayout('admin');
//$PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_course::instance($courseid));
$courserecord = $DB->get_record('course', array('id' => $courseid));
$PAGE->set_course($courserecord);

$PAGE->requires->css('/local/resourcecentral/css/style.css');
//$PAGE->set_title(get_string('resourcecentral_title','local_resourcecentral'));
require_login();
$PAGE->set_url('/local/resourcecentral/download.php');
$PAGE->set_heading(get_string('pluginname', 'local_resourcecentral'));
$PAGE->navbar->add(get_string('pluginname', 'local_resourcecentral'), new moodle_url('/local/resourcecentral/index.php?id=' . $request->courseid . ''));
$PAGE->navbar->add(get_string('list_rs', 'local_resourcecentral'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_resourcecentral'));
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('list_desc', 'local_resourcecentral'));
}
$contextid = context_user::instance($request->usermodified);
$fs = get_file_storage();
$params = array($contextid->id, 'user', 'draft', '.');
$files = $fs->get_area_files($contextid->id, 'user', 'draft', $request->itemid);
$url = "{$CFG->wwwroot}/local/request/draftfile.php/$contextid->id/user/draft";
echo '<table class="generaltable">';
echo '<tr><td width="70px;" >Added By</td>
          <td>' . $DB->get_field('user', 'firstname', array('id' => $request->usermodified)) . '
            ' . $DB->get_field('user', 'lastname', array('id' => $request->usermodified)) . '</td>
      </tr>';
foreach ($files as $file) {
    $filename = $file->get_filename();
    $fileurl = $url . $file->get_filepath() . $file->get_itemid() . '/' . $filename;
    if ($filename != '.' && $filename != null) {
        $out = html_writer::link($fileurl, $filename);
        echo '<tr>
	      <td>Download</td>
	      <td>' . $out . '</td>
	     </tr>';
    }
}
echo '</table>';
