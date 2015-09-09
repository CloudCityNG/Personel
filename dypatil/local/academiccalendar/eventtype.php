<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage Academiccalendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/academiccalendar/lib.php');
require_once($CFG->dirroot . '/local/academiccalendar/create_eventtype_form.php');
global $CFG, $DB;
$id = optional_param('id', -1, PARAM_INT);    // id; -1 if creating new eventtype
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$acalendar = academiccalendar :: get_instatnce();
$PAGE->set_url('/local/academiccalendar/eventtype.php');
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();
require_login();
if (isguestuser()) {
    print_error('noguest');
}
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_academiccalendar') . '' . get_string('createeventtypenav', 'local_academiccalendar'));
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/index.php'));
$PAGE->navbar->add(get_string('createeventtypenav', 'local_academiccalendar'));
echo $OUTPUT->header();

/* ---checking the Event type exists or not--- */
if ($id > 0) {
    if (!($eventset = $DB->get_record('local_event_types', array('id' => $id)))) {
        print_error('invalideventsetid', 'local_academiccalendar');
    }
} else {
    $eventset = new stdClass();
    $eventset->id = -1;
}
$returnurl = new moodle_url('/local/academiccalendar/eventtype.php');
/* ---Deleting unpublished Event Types--- */
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        if ($id > 3) {
            $DB->delete_records('local_event_types', array('id' => $id));
        }
        $style = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation(get_string('eventtypedeletesuccess', 'local_academiccalendar'), $returnurl, $style);
    }
    $strheading = get_string('deleteeventtype', 'local_academiccalendar');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/academiccalendar/eventtype.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirmt', 'local_academiccalendar');
    $options = array('style' => 'notifysuccess');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl, $options);
    echo $OUTPUT->footer();
    die;
}
echo $OUTPUT->heading(get_string('pluginname', 'local_academiccalendar'));
/* ---Get the records from the database--- */
$eventsets = $DB->get_records('local_event_types');
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$eventtype_form = new eventtype_form(null, array('editoroptions' => $editoroptions));
$eventtype_form->set_data($eventset);
if ($eventtype_form->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $eventtype_form->get_data()) {
    if ($data->id > 0) {
        /* ---Updating existing one--- */
        $acalendar->eventtype_update_instance($data);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation(get_string('eventupdatedsuccess', 'local_academiccalendar'), $returnurl, $options);
    } else {
        /* ---Creating new one--- */
        $acalendar->eventtype_add_instance($data);
        $options = array('style' => 'notifysuccess');
        $hierarchy->set_confirmation(get_string('eventcreatedsuccess', 'local_academiccalendar'), $returnurl, $options);
    }
}
$data = array();
foreach ($eventsets as $eventset) {
    $line = array();
    $line[] = $eventset->eventtypename;
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/academiccalendar/eventtype.php', array('id' => $eventset->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    if ($eventset->id > 3) {
        $buttons[] = html_writer::link(new moodle_url('/local/academiccalendar/eventtype.php', array('id' => $eventset->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    }
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$table = new html_table();
$table->head = array(
    get_string('headername', 'local_academiccalendar'),
    get_string('edit'));
$table->size = array('20%', '5%');
$table->align = array('left', 'center');
$table->width = '30%';
$table->data = $data;
$currenttab = 'eventtype';
require('tabs.php');
/* ---Moodle 2.2 and onwards--- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('createeventtypedescription', 'local_academiccalendar'));
}
$eventtype_form->display();
echo $OUTPUT->heading(get_string('vieweventtypes', 'local_academiccalendar'));
echo $OUTPUT->box(get_string('manageeventtypedescription', 'local_academiccalendar'));
echo html_writer::table($table);
echo $OUTPUT->footer();
