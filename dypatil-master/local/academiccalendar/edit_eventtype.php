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
 * Language strings
 *
 * @package    local
 * @subpackage Academic calendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/academiccalendar/lib.php');
require_once($CFG->dirroot . '/local/academiccalendar/create_eventtype_form.php');
$id = optional_param('id', -1, PARAM_INT);    // id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$hide = optional_param('hide', 0, PARAM_INT);
$show = optional_param('show', 0, PARAM_INT);
/* ---checking the Event type exists or not--- */
if ($id > 0) {
    if (!($tool = $DB->get_record('local_event_types', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_academiccalendar');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$PAGE->set_pagelayout('admin');
$PAGE->set_url('/local/academiccalendar/edit_eventtype.php', array('id' => $id));
$systemcontext =  context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_heading($SITE->fullname);
$returnurl = new moodle_url('/local/academiccalendar/view_eventtype.php', array('id' => $id));
$strheading = get_string('pluginname', 'local_academiccalendar') . ' : ' . get_string('Createelementtype', 'local_academiccalendar');
$PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/create_eventtype.php', array('id' => $id)));
$PAGE->navbar->add($strheading);
$PAGE->set_title($strheading);
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
if ($id > 0) {
    if (!($tool = $DB->get_record('local_event_types', array('id' => $id)))) {
        print_error('invalidtoolid', 'local_academiccalendar');
    }
} else {
    $tool = new stdClass();
    $tool->id = -1;
}
$returnurl = new moodle_url('/local/academiccalendar/view_eventtype.php');

/* ---Deleting unpublished Event Types--- */

if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $DB->delete_record('local_event_types', array('id' => $id));
        redirect($returnurl);
    }
    $strheading = get_string('deleteeventtype', 'local_academiccalendar');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $yesurl = new moodle_url('/local/academiccalendar/view_eventtype.php', array('id' => $id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
    $message = get_string('delconfirmt', 'local_academiccalendar');
    echo $OUTPUT->confirm($message, $yesurl, $returnurl);
    echo $OUTPUT->footer();
    die;
}

/* ---Get the records from the database--- */
$etdata = $DB->get_records('local_event_types');
$editoroptions = array('maxfiles' => EDITOR_UNLIMITED_FILES, 'maxbytes' => $CFG->maxbytes, 'trusttext' => false, 'noclean' => true);
$editform = new edit_form(null, array('editoroptions' => $editoroptions));
$editform->set_data($etdata);
if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    if ($data->id > 0) {
        eventtype_update_instance($data);
    } else {
        eventtype_add_instance($data);
    }
    redirect($returnurl);
}
$data = array();
foreach ($etdata as $ed) {
    $line = array();
    $line[] = $ed->eventtypename;
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/academiccalendar/view_eventtype.php', array('id' => $ed->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link('#', html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
/* --- Moodle 2.2 and onwards --- */
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('createeventtypedescription', 'local_academiccalendar'));
}
$table = new html_table();
$table->head = array(
    get_string('headername', 'local_academiccalendar'),
    get_string('edit'));
$table->size = array('20%', '5%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '30%';
$table->data = $data;
$editform->display();
echo html_writer::table($table);
$editform = new edit_form(null, array('editoroptions' => $editoroptions));
$editform->set_data($tool);

if ($editform->is_cancelled()) {
    redirect($returnurl);
} else if ($data = $editform->get_data()) {
    if ($data->id > 0) {
        /* ---Updating existing one--- */
        eventtype_update_instance($data);
    } else {
        /* ---Creating new one--- */
        eventtype_add_instance($data);
    }
    redirect($returnurl);
}

echo $OUTPUT->header();
$editform->display();
echo $OUTPUT->footer();
