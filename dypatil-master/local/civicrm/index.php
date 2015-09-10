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
 * @subpackage Modules
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/civicrm/lib.php');
global $CFG, $DB;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$created = optional_param('created', 0, PARAM_INT);
$updated = optional_param('updated', 0, PARAM_INT);
$deleted = optional_param('deleted', 0, PARAM_INT);
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$PAGE->set_url('/local/civicrm/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('managecivicrm', 'local_civicrm'));
//echo $OUTPUT->header();
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('managecivicrm', 'local_civicrm'));
//Get the records from the database
$civicrmhost = $DB->get_records('local_civicrm');

// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_civicrm'));
}

$tools = $DB->get_records('local_civicrm');
$data = array();
foreach ($tools as $tool) {
    $line = array();
    $linkcss = $tool->visible ? ' ' : 'class="dimmed" ';
    $line[] = $tool->civihost;
    $line[] = $tool->civikeys;
    $line[] = $tool->civiapikeys;
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/civicrm/civicrm.php', array('id' => $tool->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/civicrm/civicrm.php', array('id' => $tool->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));

    if ($tool->visible) {
        $buttons[] = html_writer::link(new moodle_url('/local/civicrm/civicrm.php', array('id' => $tool->id, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'title' => get_string('inactive'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
    } else {
        $buttons[] = html_writer::link(new moodle_url('/local/civicrm/civicrm.php', array('id' => $tool->id, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
    }

    $line[] = implode(' ', $buttons);
    $data[] = $line;
}
$sql = "SELECT * FROM {local_civicrm} where visible=1";
$civicrmdetails = $DB->get_records_sql($sql);
if (empty($civicrmdetails)) {
    echo '<div id="createbutton">';
    echo $OUTPUT->single_button(new moodle_url('/local/civicrm/civicrm.php', array('id' => -1)), get_string('configurecivicrm', 'local_civicrm'));
    echo '</div>';
}

//start the table
$table = new html_table();
$table->head = array(
    get_string('headername', 'local_civicrm'), get_string('civikeys', 'local_civicrm'), get_string('civiapikeys', 'local_civicrm'), get_string('edit'));
$table->size = array('20%', '20%', '20%', '15%', '10%');
$table->align = array('left', 'left', 'left', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
