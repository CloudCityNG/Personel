<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
require_once($CFG->dirroot . '/local/attendance/lib.php');
require_once($CFG->dirroot . '/local/attendance/mod_form.php');
$classid = optional_param('classid', 0, PARAM_INT);
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
require_login();
if (!has_capability('local/clclasses:manage', $systemcontext) && !is_siteadmin()) {
    $returnurl = new moodle_url('/local/error.php');
    redirect($returnurl);
}
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url('/local/clclasses/index.php');

$PAGE->navbar->add(get_string('manageclclasses', 'local_clclasses'), new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('takeattendance', 'local_attendance'));
echo $OUTPUT->header();
$record = $DB->get_record('local_clclasses', array('id' => $classid));
$class = new stdClass();
$class->fullname = $record->fullname;
echo $OUTPUT->heading(get_string('addattendance', 'local_attendance', $class));
$attendance = $DB->get_record('local_attendance', array('classid' => $classid));
$currenttab = 'create';
local_attendance_tabs($currenttab, $classid, $attendance->id);

if (empty($attendance)) {
    $mform = new local_attendance_form(null, array('classid' => $classid));
    $mform->display();
    if ($mform->is_cancelled()) {
        $returnurl = new moodle_url('/local/clclasses/index.php');
        redirect($returnurl);
    } else if ($data = $mform->get_data()) {
        $DB->insert_record('local_attendance', $data);
        $returnurl = new moodle_url('/local/attendance/modedit.php', array('classid' => $data->classid));
        redirect($returnurl);
    }
} else {
    //echo "Show attendance record";
    $data = array();
    $buttons = array();

    $result = array();
    $result[] = $attendance->name;
    $buttons[] = html_writer::link(new moodle_url('/local/attendance/delete.php', array('id' => $attendance->id, 'classid' => $classid, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/attendance/delete.php', array('id' => $attendance->id, 'classid' => $classid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));

    if ($attendance->visible) {
        $buttons[] = html_writer::link(new moodle_url('/local/attendance/delete.php', array('id' => $attendance->id, 'classid' => $classid, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
    } else {
        $buttons[] = html_writer::link(new moodle_url('/local/attendance/delete.php', array('id' => $attendance->id, 'classid' => $classid, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
    }
    $result[] = implode(' ', $buttons);
    $data[] = $result;
    $table = new html_table();

    $table->head = array(
        get_string('attendance', 'local_attendance'),
        get_string('action', 'local_attendance')
    );
    $table->size = array('20%', '20%');
    $table->align = array('left', 'left');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
}
echo $OUTPUT->footer();
