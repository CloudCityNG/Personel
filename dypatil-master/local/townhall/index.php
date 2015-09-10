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
 * @subpackage townhall
 * @copyright  2013 sreenivasula@eabyas.in
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
global $CFG, $DB;
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/townhall/lib.php');
$id = required_param('id', PARAM_INT);
$type = optional_param('type', null, PARAM_ALPHA);
$typnid = optional_param('typnid', -1, PARAM_INT);
$cmid = optional_param('cmid', -1, PARAM_INT);
$name = optional_param('name', null, PARAM_ALPHA);
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$row_id = optional_param('row_id', -1, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$per_page = 5;
$button = optional_param('button', 0, PARAM_INT);

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
$returnurl = new moodle_url('/local/townhall/index.php', array('id' => $id));
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_townhall') . ' : ' . get_string('managetownhall', 'local_townhall');
// $PAGE->set_title($string);
$PAGE->navbar->add(get_string('managetownhall', 'local_townhall'), new moodle_url('/local/townhall/index.php'));

$strheading = get_string('managetownhall', 'local_townhall');
// delete function 
if ($delete) {
    $PAGE->url->param('delete', 1);
    if ($confirm and confirm_sesskey()) {
        $result = $DB->delete_records('local_townhall', array('id' => $row_id));
        redirect($returnurl);
    }
    $strheading = get_string('delete_act', 'local_townhall');
    // $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    if ($exists = $DB->get_records('local_townhall', array('id' => $row_id))) {
        $yesurl = new moodle_url('/local/townhall/index.php?id=' . $id . '', array('row_id' => $row_id, 'delete' => 1, 'confirm' => 1, 'sesskey' => sesskey()));
        $message = get_string('delconfirm', 'local_townhall');
        echo $OUTPUT->box_start('generalbox');
        echo $OUTPUT->confirm($message, $yesurl, $returnurl);
        echo $OUTPUT->box_end();
    }
    echo $OUTPUT->footer();
    die;
}
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
$currenttab = "edit";
print_towntabs($currenttab, $id);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
$activity_types = activities_inuse($id);
echo HTML_WRITER::start_tag('div', array('class' => 'selfilterpos', 'id' => 'townhall'));
$select = new single_select(new moodle_url('/local/townhall/index.php?id=' . $id . ''), 'type', $activity_types, $type, null, 'switchcategory');
$select->set_label(get_string('activitytype', 'local_townhall') . ':');
echo $OUTPUT->render($select);
echo HTML_WRITER::end_tag('div');
if ($type == null)
    $activity_names = array(0 => array());
else
    $activity_names = event_information($type, $id);
echo HTML_WRITER::start_tag('div', array('class' => 'selfilterpos', 'id' => 'townhall', style => 'float:none'));

$select = new single_select(new moodle_url('/local/townhall/index.php?id=' . $id . ' &type=' . $type . ''), 'typnid', $activity_names[0], $typnid, null, 'switchcategory');
$select->set_label(get_string('activityname', 'local_townhall') . ':');
echo $OUTPUT->render($select);
echo HTML_WRITER::end_tag('div');
echo '<div class="town_button"><a href="../../local/townhall/index.php?id=' . $id . ' &cmid=' . $typnid . '&name=' . $type . '&button=1"><button >Submit</button></a></div>';
$data = new stdclass();
$data->courseid = $id;
$data->cmid = $cmid;
$data->modname = $name;
$data->userid = $USER->id;
if ($button == 1) {
    if (empty($name) and $cmid < 0)
        echo '<h4>' . get_string('error', 'local_townhall') . '</h4>';
}
if ($id > 0 and $name != null and $cmid > 0) {
    $record_exists = $DB->record_exists('local_townhall', array(cmid => $cmid, courseid => $id));
    if ($record_exists == true) {
        $OUTPUT->box_start();
        echo '<h4>' . get_string('already_added', local_townhall) . '</h4>';
        echo '<div class="town_button"><a href="../../local/townhall/index.php?id=' . $id . '"><button>Refresh</button></a></div>';
        $OUTPUT->box_end();
    } else
        $insert = $DB->insert_record('local_townhall', $data);
}
if ($insert) {
    $OUTPUT->box_start();
    echo '<h4>' . get_string('success', local_townhall) . '</h4>';
    echo '<div class="town_button"><a href="../../local/townhall/index.php?id=' . $id . '"><button>Refresh</button></a></div>';
    $OUTPUT->box_end();
}

$results = $DB->get_records_sql('select  * from {local_townhall}');
$count = count($results);
$start = $page * $per_page;
$results = $DB->get_records_sql("select  * from {local_townhall} limit $start, $per_page");
$data = array();
foreach ($results as $result) {
    $line = array();
    $line[] = $result->modname;
    $mod_name = get_coursemodule_from_id($result->modname, $result->cmid, 0, false, MUST_EXIST);
    $mod_name = $mod_name->name;
    $line[] = $mod_name;
    $buttons = html_writer::link(new moodle_url('/local/townhall/index.php?id=' . $id . '', array('row_id' => $result->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    $line[] = $buttons;
    $data[] = $line;
}
$table = new html_table();
$table->head = array(get_string('activitytype', 'local_townhall'), get_string('activityname', 'local_townhall'), get_string('action'));
$table->size = array('20%', '10%', '20%');
$table->align = array('center', 'center', 'center');
$table->width = '50%';
$table->data = $data;
echo get_string('yesactivities', 'local_townhall');
echo html_writer::table($table);
$baseurl = new moodle_url('/local/townhall/index.php?id=' . $id . '');
echo '<div class="paging_bar">';
echo $OUTPUT->paging_bar($count, $page, $per_page, $baseurl);
echo '</div>';
echo $OUTPUT->footer();
