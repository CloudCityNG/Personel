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
 * Gradeletter Plugin - to define the gradeletter and the point for a given range of Marks for a course
 *
 * @package    local
 * @subpackage pramod
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/gradeletter/lib.php');
global $CFG, $USER;

$currenttab = optional_param('mode', 'view', PARAM_RAW);
$gletters = graded_letters::getInstance();
$systemcontext = context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
if (!has_capability('local/gradeletter:view', $systemcontext)) {
      print_cobalterror('permissions_error','local_collegestructure');
}

$PAGE->set_url('/local/gradeletter/index.php');

//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginheading', 'local_gradeletter'));
$PAGE->navbar->add(get_string('view', 'local_gradeletter'));
$PAGE->set_title(get_string('pluginheading', 'local_gradeletter') . ': ' . get_string('view', 'local_gradeletter'));

echo $OUTPUT->header();

//Heading of the page
echo $OUTPUT->heading(get_string('pluginheading', 'local_gradeletter'));


$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();
$schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}

$gletters->createtabview_gl($currenttab);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_gradeletter'));
}

$schoolidin = implode(',', array_keys($schoollist));
$sql = "SELECT * FROM {$CFG->prefix}local_gradeletters WHERE schoolid IN ($schoolidin)";
$grade_letters = $DB->get_records_sql($sql);

$data = array();
foreach ($grade_letters as $gradelet) {
    $school_name = $DB->get_record('local_school', array('id' => $gradelet->schoolid));
    $line = array();
    $greycss = $gradelet->visible ? ' ' : 'dimmed';
    /*
     * ###Bugreport #137-providing link
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * (Resolved) providing view page links to college(organization) name
     */
    $line[] = '<a href="' . $CFG->wwwroot . '/local/collegestructure/view.php?id=' . $school_name->id . '" class="' . $greycss . '">' . $school_name->fullname . '</a>';
    $line[] = $gradelet->letter;
    $line[] = $gradelet->markfrom;
    $line[] = $gradelet->markto;
    $line[] = $gradelet->gradepoint;
    if (has_capability('local/gradeletter:manage', $systemcontext)) {
        $buttons = $hierarchy->get_actions('gradeletter', 'edit', $gradelet->id, $gradelet->visible);
        $line[] = $buttons;
    }
    $data[] = $line;
}


$PAGE->requires->js('/local/gradeletter/letter.js');

echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//View Part starts
//start the table
$table = new html_table();
$table->id = "gradelettertable";
$table->head = array(get_string('schoolname', 'local_collegestructure'), get_string('lettergrades', 'local_gradeletter'), get_string('markfrom', 'local_gradeletter'), get_string('markto', 'local_gradeletter'), get_string('gradepoint', 'local_gradeletter'));
if (has_capability('local/gradeletter:manage', $systemcontext))
    $table->head[] = get_string('editop', 'local_examtype');
$table->size = array('30%', '15%', '15%', '20', '10%', '10%');
$table->align = array('left', 'left', 'left', 'left', 'left', 'left');
$table->width = '100%';
$table->data = $data;

echo html_writer::table($table);
echo $OUTPUT->footer();
