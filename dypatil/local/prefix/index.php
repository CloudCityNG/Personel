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
 * @subpackage  Creating prefix and suffix
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/prefix/lib.php');
global $CFG, $DB;
$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}

$PAGE->set_url('/local/prefix/index.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('prefix_suffix', 'local_prefix'), new moodle_url('/local/prefix/index.php'));
$PAGE->navbar->add(get_string('viewprefix_suffix', 'local_prefix'));

echo $OUTPUT->header();
//===========prefix and suffix heading------------------------
echo $OUTPUT->heading(get_string('prefixs', 'local_prefix'));


//adding tabs using prefix_tabs function
$currenttab = 'view';
$prefix = prefix_suffix::getInstance();
$schoolid = $prefix->check_loginuser_registrar_admin();
$prefix->prefix_tabs($currenttab);
//  description of the  table -------------------- 
echo $OUTPUT->box(get_string('prefixviewtabdes', 'local_prefix'));

$capabilities_array = array('local/prefix:manage', 'local/prefix:delete', 'local/prefix:update', 'local/prefix:visible');

try {
//------------if registrar not assigned to any school it throws exception    
    $hier1 = new hierarchy();   
//----------display function get_table starts	   
    function get_table($tools) {
        global $PAGE, $USER, $DB, $OUTPUT;
        $systemcontext = context_system::instance();
        $capabilities_array = array('local/prefix:manage', 'local/prefix:delete', 'local/prefix:update', 'local/prefix:visible');
        $PAGE->requires->js('/local/prefix/test.js');
        $data = array();
        foreach ($tools as $tool) {
            $line = array();
            $temp1 = $DB->get_record('local_create_entity', array('id' => $tool->entityid));
            $line[] = $temp1->entity_name;
            $temp3 = $DB->get_record('local_school', array('id' => $tool->schoolid));
            $line[] = $temp3->fullname;
            $temp2 = $DB->get_record('local_program', array('id' => $tool->programid));
            $line[] = $temp2->fullname;
            $line[] = $tool->sequence_no;
            $line[] = $tool->prefix;
            $line[] = $tool->suffix;
            $hier = new hierarchy();
            // ------------------- Edited by hema------------------------------
           
            if (has_any_capability($capabilities_array, $systemcontext)) {
                $buttons = $hier->get_actions('prefix', 'prefix2', $tool->id, $tool->visible);
                $line[] = $buttons;
            }
            $data[] = $line;
        }
        // Moodle 2.2 and onwards
        if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
            echo $OUTPUT->box(get_string('phead', 'local_prefix'));
        }

        echo "<div id='filter-box' >";
        echo '<div class="filterarea"></div></div>';

        //View Part starts
        //start the table
        $table = new html_table();
        $table->id = 'cooktable';
        $table->head = array(
            get_string('e_name', 'local_prefix'),
            get_string('schoolid', 'local_collegestructure'),
            get_string('program', 'local_programs'),
            get_string('sequence', 'local_prefix'),
            get_string('prefix', 'local_prefix'),
            get_string('suffix', 'local_prefix'));
        // ------------------- Edited by hema------------------------------ 
        if (has_any_capability($capabilities_array, $systemcontext)) {
            array_push($table->head, get_string('editop', 'local_examtype'));
        }

        $table->size = array('15%', '15%', '15%', '15%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '99%';
        $table->data = $data;

        echo html_writer::table($table);
    }

//----end of get_table function-------------------------

    foreach ($schoolid as $sid) {
        $temp[] = $sid->id;
    }
    $school_id = implode(',', $temp);
    $sql = "SELECT * From {$CFG->prefix}local_prefix_suffix where schoolid in ($school_id)";
    $res = $DB->get_records_sql($sql);
    if (empty($res)) {
        $e = get_string('notyet_craeted', 'local_prefix');
        throw new Exception($e);
    }
    get_table($res);
} //--------- end of try block-----------------------------
catch (Exception $e) {
    echo $e->getMessage();
}


echo $OUTPUT->footer();
?>




