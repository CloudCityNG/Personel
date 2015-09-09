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
 * @package    local
 * @subpackage examtype
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/examtype/lib.php');
$schoolid = optional_param('schoolid', 0, PARAM_INT); // School ID
global $CFG;
$systemcontext = context_system::instance();
//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
if (!has_capability('local/examtype:view', $systemcontext)) {
    print_cobalterror('permissions_error','local_collegestructure');
}
$PAGE->set_url('/local/examtype/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_examtype') . ':' . get_string('lists', 'local_examtype');
$PAGE->set_title($string);
$PAGE->navbar->add(get_string('manageexamtype', 'local_examtype'));
$PAGE->navbar->add(get_string('lists', 'local_examtype'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('manageexamtype', 'local_examtype')); //Heading of the page
$currenttab = "lists";
// require_once('tabs.php');
$instance = new cobalt_examtype();

$hier = new hierarchy();
$schools = $hier->get_assignedschools();
$schools = $hier->get_school_parent($schools, $selected = array(), $inctop = false, $all = false);
if (is_siteadmin()) {
    $schools = $hier->get_school_items();
}

$instance->print_examtabs($currenttab, $id = -1);

// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_examtype'));
    echo '</br>';
}

$schoollist_string = implode(',', array_keys($schools));
if (empty($schoollist_string)) {
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->footer();
    die;
}
$exam_type = $DB->get_records_sql('select * from {local_examtypes} where schoolid in (' . $schoollist_string . ')');
$data = array();
foreach ($exam_type as $examtyp) {
    $school_name = $DB->get_record('local_school', array('id' => $examtyp->schoolid));
    $line = array();
    $greycss = $examtyp->visible ? ' ' : 'dimmed';
    $line[] = '<span class="' . $greycss . '">' . $examtyp->examtype . '</span>';
    if (count($schools) != 1) {
        $line[] = $school_name->fullname;
    }
    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/examtype/edit.php', array('id' => $examtyp->id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/examtype/edit.php', array('id' => $examtyp->id, 'scid' => $examtyp->schoolid, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
    if ($examtyp->visible) {
        $buttons[] = html_writer::link(new moodle_url('/local/examtype/edit.php', array('id' => $examtyp->id, 'hide' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
    } else {
        $buttons[] = html_writer::link(new moodle_url('/local/examtype/edit.php', array('id' => $examtyp->id, 'show' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
    }

    if (has_capability('local/examtype:manage', $systemcontext))
        $line[] = implode(' ', $buttons);

    $data[] = $line;
}
$PAGE->requires->js('/local/examtype/js/examtype.js');
echo '<div class="filterarea"></div>';
//View Part starts
//start the table

$table = new html_table();
$table->id = "examtable";
if (count($schools) == 1) {
    $table->head = array(
        get_string('examtype', 'local_examtype'));
    $table->size = array('50%', '50%');
    $table->align = array('left', 'left');
} else {
    $table->head = array(
        get_string('examtype', 'local_examtype'),
        get_string('schoolname', 'local_collegestructure'));
    $table->size = array('35%', '35%', '30%');
    $table->align = array('left', 'left', 'left');
}
if (has_capability('local/examtype:manage', $systemcontext))
    $table->head[] = get_string('editop', 'local_examtype');

$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);
if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
