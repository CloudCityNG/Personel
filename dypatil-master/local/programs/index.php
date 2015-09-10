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
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $PAGE;
require_once($CFG->dirroot . '/local/programs/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

$currenttab = optional_param('mode', 'view', PARAM_RAW);
$myprogram = programs::getInstance();
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
//If the loggedin user have the capability of managing the batches allow the page
$capabilities_array =$myprogram->program_capabilities(); 
if (!has_any_capability($capabilities_array, $systemcontext)) {
    print_cobalterror('permissions_error', 'local_collegestructure');
}
$PAGE->set_url('/local/programs/index.php');
$PAGE->set_title(get_string('programs', 'local_programs') . ': ' . get_string('programlist', 'local_programs'));
//Header and the navigation bar
$PAGE->set_heading(get_string('programs', 'local_programs'));

$PAGE->navbar->add(get_string('pluginname', 'local_programs'), new moodle_url('/local/programs/index.php'));
$PAGE->navbar->add(get_string('programlist', 'local_programs'));
echo $OUTPUT->header();
//Heading of the page
echo $OUTPUT->heading(get_string('pluginname', 'local_programs'));
$hierarchy = new hierarchy();
$schoollist = $hierarchy->get_assignedschools();
try {

    if (is_siteadmin()) {
        $schoollist = $hierarchy->get_school_items();
    }
    $count = count($schoollist); //Count of schools to which registrar is assigned
    if ($count < 1) {
        throw new Exception(get_string('notassignedschool', 'local_collegestructure'));
    }

    $data = array();
    
//Only assigned school records can be shown to registrar.
    foreach ($schoollist as $school) {
        $programs = $DB->get_records('local_program', array('schoolid' => $school->id));

        foreach ($programs as $program) {
            $line = array();
            $name = $myprogram->name($program);
            $linkcss = $program->visible ? ' ' : 'class="dimmed" ';
            // $line[] = html_writer::tag('a', $program->fullname, array('href' => ''.$CFG->wwwroot.'/local/programs/view.php?id='.$program->id.''));
            $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/programs/view.php?id=' . $program->id . '">' . format_string($program->fullname) . '</a>';
            //$line[] = $program->fullname;
            // Edited by hema
            /* if($program->duration_format == 'Y')
              $duration_format='Years';
              else
              if ($program->duration_format == 'M')
              $duration_format='Months'; */
            $line[] = $program->duration . strtolower(get_string('example', 'local_programs'));
            //$line[] = $name->type;
            $line[] = $name->level;
            $line[] = $DB->get_field('local_school', 'fullname', array('id' => $program->schoolid));
            //---------checking capabilities---------------------------- 
             $capabilities_array =$myprogram->program_capabilities(array('create','view')); 
            if (has_any_capability($capabilities_array, $systemcontext)) {
                $line[] = $hierarchy->get_actions('programs', 'program', $program->id, $program->visible);
            }

            $data[] = $line;
        }
    }
//Tab view
    $myprogram->createtabview($currenttab);
//echo $OUTPUT->heading(get_string('programlist', 'local_programs'));
//Description for the page
    if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
        echo $OUTPUT->box(get_string('viewprogramspage', 'local_programs'));
    }

    $PAGE->requires->js('/local/programs/js/program.js');

    echo "<div id='filter-box' >";
    echo '<div class="filterarea"></div></div>';
    //View Part starts
    //start the table
    $table = new html_table();
    $table->id = "programtable";
    $head = array();
    //$head[] = get_string('shortname', 'local_programs');
    $head[] = get_string('programname', 'local_programs');
    $head[] = get_string('duration', 'local_programs');
    //$head[] = get_string('programtype', 'local_programs');
    $head[] = get_string('programlevel', 'local_programs');
    $head[] = get_string('schoolid', 'local_collegestructure');
    //---------checking capabilities---------------------------- 
    if (has_any_capability($capabilities_array, $systemcontext)) {
        array_push($head, get_string('action'));
    }
    $table->head = $head;
    //$table->size = array('30%', '10%', '10%', '15%', '25%', '10%');
    $table->align = array('left', 'left', 'left', 'left');
    $table->width = '100%';
    $table->data = $data;
// Display the table
    echo html_writer::table($table);
} catch (Exception $e) {
    echo $e->getMessage();
}

echo $OUTPUT->footer();
