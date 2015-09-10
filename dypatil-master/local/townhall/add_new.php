<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/townhall/lib.php');
require_once('hall_form.php');
global $CFG, $DB, $USER;

$tid = optional_param('tid', -1, PARAM_INT);
$edit = optional_param('edit', -1, PARAM_INT);
$cid = optional_param('cid', -1, PARAM_INT);
$row_id = optional_param('row_id', -1, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$page = optional_param('page', 0, PARAM_INT);
$per_page = 5;
if ($cid != -1)
    $id = $cid;
else
    $id = optional_param('id', -1, PARAM_INT);

// get the admin layout
// $PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not
// $systemcontext = context_system::instance();;
// $PAGE->set_context($systemcontext);
$PAGE->set_pagelayout('course');
$PAGE->set_context(context_course::instance($id));
$cc = $DB->get_record('course', array('id' => $id));
$PAGE->set_course($cc);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/townhall/index.php');
$PAGE->requires->css('/local/townhall/styles.css');
$returnurl = new moodle_url('/local/townhall/add_new.php', array('id' => $id));
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_townhall') . ' : ' . get_string('add', 'local_townhall');
// $PAGE->set_title($string);
// delete function 
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $result = $DB->delete_records('local_townhall_topic', array('id' => $row_id));
        redirect($returnurl);
    }
    $strheading = get_string('delete_topic', 'local_townhall');
    $PAGE->navbar->add(get_string('managetownhall', 'local_townhall'), new moodle_url('/local/townhall/index.php', array('id' => $id)));
    $PAGE->navbar->add($strheading);
    // $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    if ($exists = $DB->get_records('local_townhall_topic', array('id' => $row_id))) {
        $yesurl = new moodle_url('/local/townhall/add_new.php?id=' . $id . '', array('row_id' => $row_id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_townhall');
        echo $OUTPUT->box_start('generalbox');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->footer();
    die;
}
if (is_siteadmin())
    $PAGE->navbar->add(get_string('managetownhall', 'local_townhall'), new moodle_url('/local/townhall/index.php'));
$PAGE->navbar->add(get_string('add', 'local_townhall'));
$strheading = get_string('add', 'local_townhall');
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$currenttab = "add";
print_towntabs($currenttab, $id);
if ($row_id > 0) {
    if (!($tool = $DB->get_record('local_townhall_topic', array('id' => $row_id)))) {
        print_error('invalidtoolid', 'local_townhall');
    }
    $tool->description = array('text' => $tool->description, 'format' => FORMAT_HTML);
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$mform = new hall_form(null, $id);
$mform->set_data($tool);

if ($mform->is_cancelled()) {
    redirect($returnurl);
} else if ($fdata = $mform->get_data()) {
    if ($fdata->id > 0) {
        $data->id = $fdata->id;
        $data->description = $fdata->description['text'];
        $data->courseid = $id;
        $data->userid = $USER->id;
        $data->topic = $fdata->topic;
        $data->publish = $fdata->publish;
        $DB->update_record('local_townhall_topic', $data);
    } else {
        $data->description = $fdata->description['text'];
        $data->courseid = $id;
        $data->topic = $fdata->topic;
        $data->userid = $USER->id;
        $data->publish = $fdata->publish;
        $DB->insert_record('local_townhall_topic', $data);
    }
    redirect($returnurl);
}

echo '<div class="single_button">' . $OUTPUT->single_button(new moodle_url('/local/townhall/add_new.php?edit=1&id=' . $id . ''), get_string('topic', 'local_townhall')) . '</div>';
if ($edit == 1) {
    $mform->display();
}
echo '<h3>' . get_string('addedtopics', 'local_townhall') . '</h3><hr>';
$results = $DB->get_records_sql('select  * from {local_townhall_topic}');
$count = count($results);
$start = $page * $per_page;
$results = $DB->get_records_sql("select  * from {local_townhall_topic} limit $start, $per_page");
foreach ($results as $result) {
    $line = array();
    $buttons = array();
    $line[] = $result->topic;
    if ($result->publish == 1)
        $line[] = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/clear'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));
    else
        $line[] = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('edit'), 'class' => 'iconsmall'));
    $buttons[] = html_writer::link(new moodle_url('/local/townhall/add_new.php?id=' . $id . '', array('row_id' => $result->id, 'edit' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/townhall/add_new.php?id=' . $id . '', array('row_id' => $result->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$table = new html_table();
$table->head = array('Topic', 'Published', 'Options');
$table->size = array('20%', '10%', '20%');
$table->align = array('center', 'center', 'center');
$table->width = '50%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo '<h4>NO RECORDS FOUND</h4>';
    echo $OUTPUT->box_end();
}
$baseurl = new moodle_url('/local/townhall/add_new.php?id=' . $id . '');
echo '<div class="paging_bar">';
echo $OUTPUT->paging_bar($count, $page, $per_page, $baseurl);
echo '</div>';
echo $OUTPUT->footer();
?>
