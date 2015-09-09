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
 * @subpackage semesters
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/semesters/lib.php');
$mode = optional_param('mode', 'all', PARAM_RAW);
global $CFG;
$systemcontext = context_system::instance();
$PAGE->set_pagelayout('admin');

$PAGE->set_context($systemcontext);
require_login();
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$mysemester = semesters::getInstance();
 $capabilities_array = $mysemester->semester_capabilities();
 
if (!has_any_capability($capabilities_array, $systemcontext)) {
    print_error('permissions_error','local_collegestructure');
}
$PAGE->set_url('/local/semesters/index.php');

//Header and the navigation bar

if ($mode == 'all')
    $title = get_string('semesters', 'local_semesters') . ': ' . get_string('viewsemesters', 'local_semesters');
else if ($mode == 'current')
    $title = get_string('semesters', 'local_semesters') . ': ' . get_string('viewcurrentsemesters', 'local_semesters');
else if ($mode == 'upcoming')
    $title = get_string('semesters', 'local_semesters') . ': ' . get_string('viewupcomingsemesters', 'local_semesters');
$PAGE->set_title($title);
$PAGE->set_heading(get_string('semesters', 'local_semesters'));
$PAGE->navbar->add(get_string('pluginname', 'local_semesters'), new moodle_url('/local/semesters/index.php'));
if ($mode == 'all') {
    $PAGE->navbar->add(get_string('viewsemesters', 'local_semesters'));
} else if ($mode == 'current') {
    $PAGE->navbar->add(get_string('viewcurrentsemesters', 'local_semesters'));
} else if ($mode == 'upcoming') {
    $PAGE->navbar->add(get_string('viewupcomingsemesters', 'local_semesters'));
}
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_semesters'));
$hierarchy = new hierarchy();

$schoollist = $hierarchy->get_assignedschools();

if (is_siteadmin()) {
    $schoollist = $hierarchy->get_school_items();
}

$parent = $hierarchy->get_school_parent($schoollist, '', false, false);
$count = count($schoollist); //Count of schools to which registrar is assigned
$schoolid = implode(',', array_keys($parent));

$data = array();
$capabilities_array = $mysemester->semester_capabilities(array('view','create'));
$semesters = $mysemester->get_listofsemesters($mode, $schoolid);

foreach ($semesters as $semester) {
    /* Bug report #270  -  Semesters>Multiple Schools>View and Filters- Not displayed
     * @author hemalatha c arun <hemalatha@eabyas.in> 
     * Resolved- fetching all semester school data, when single semester assigned to mutliple school
     */
    $sem = $DB->get_record('local_semester', array('id' => $semester->semesterid));
    $line = array();
    $name = $mysemester->names($sem);
    $linkcss = $sem->visible ? ' ' : 'class="dimmed" ';
    $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/semesters/view.php?id=' . $sem->id . '">' . format_string($sem->fullname) . '</a>';
    $line[] = $DB->get_field('local_school', 'fullname', array('id' => $semester->schoolid));
    $line[] = $name->startdate;
    $line[] = $name->enddate;
    //   $line[] = $semester->mincredit;
    //   $line[] = $semester->maxcredit;
    //get action buttons for crud operations
    //---------checking capabilities---------------------------- 
   
    if (has_any_capability($capabilities_array, $systemcontext)) {
        $line[] = $hierarchy->get_actions('semesters', 'semester', $semester->semesterid, $sem->visible, $mode, $semester->schoolid);
    }
    $data[] = $line;
}
//Tab view
$mysemester->createtabview($mode);

//Description for the page
if ($mode == 'current') {
    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('viewcursemesterspage', 'local_semesters'));
    }
} elseif ($mode == 'upcoming') {
    echo $OUTPUT->box(get_string('viewupsemesterspage', 'local_semesters'));
} else {

    echo $OUTPUT->box(get_string('viewsemesterspage', 'local_semesters'));
}
$PAGE->requires->js('/local/semesters/semesterjs.js');
echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//View Part starts
//start the table
$table = new html_table();
$table->id = "semestertable";
$head = array();
$head[] = get_string('semestername', 'local_semesters');
$head[] = get_string('schoolname', 'local_collegestructure');
$head[] = get_string('startdate', 'local_semesters');
$head[] = get_string('enddate', 'local_semesters');
//$head[] = get_string('semmincrhrs', 'local_semesters');
//$head[] = get_string('semmaxcrhrs', 'local_semesters');
//$head[] = get_string('action');
//---------checking capabilities---------------------------- 
if (has_any_capability($capabilities_array, $systemcontext)) {
    array_push($head, get_string('action'));
}
$table->head = $head;
$table->size = array('27%', '23%', '15%', '15%', '10%');
$table->align = array('center', 'center', 'center', 'center', 'center', 'center');
$table->width = '100%';
$table->data = $data;
// Display the table
echo html_writer::table($table);
echo $OUTPUT->footer();
