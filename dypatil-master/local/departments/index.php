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
 * @subpackage  departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/departments/lib.php');
global $CFG, $DB;
$systemcontext =context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/departments/index.php');

 $capabilities_array = array('local/departments:manage', 'local/departments:delete', 'local/departments:update', 'local/departments:visible','local/departments:view');
//If the loggedin user have the capability of managing the batches allow the page
 if (!has_any_capability( $capabilities_array , $systemcontext)) {
   print_cobalterror('permissions_error', 'local_collegestructure');
 }



//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manage_dept', 'local_departments'), new moodle_url('/local/departments/index.php'));
$PAGE->navbar->add(get_string('deptlist', 'local_departments'));
echo $OUTPUT->header();

//-------------manage_dept heading------------------------
echo $OUTPUT->heading(get_string('dept_heading', 'local_departments'));

//adding tabs using prefix_tabs function
$currenttab = 'deptlist';
$dept_ob = manage_dept::getInstance();

// checking if login user is registrar or admin
$schoolid = $dept_ob->check_loginuser_registrar_admin();

$dept_ob->dept_tabs($currenttab);

try {

//  description of the  table -------------------- 
    echo $OUTPUT->box(get_string('view_dept_heading', 'local_departments'));

//----------display function get_table starts	   
    function get_table($tools) {
        global $PAGE, $USER, $DB, $OUTPUT, $CFG;
        $PAGE->requires->js('/local/departments/js/dept_test.js');
        $data = array();
        $capabilities_array = array('local/departments:manage', 'local/departments:delete', 'local/departments:update', 'local/departments:visible');
        foreach ($tools as $tool) {
            $line = array();
            //  $line[]=$tool->shortname;
            $linkcss = $tool->visible ? ' ' : 'class="dimmed" ';
            // $line[] = html_writer::tag('a', $tool->fullname, array('href' => ''.$CFG->wwwroot.'/local/departments/viewdept.php?id='.$tool->id.''));
            $line[] = '<a ' . $linkcss . ' href="' . $CFG->wwwroot . '/local/departments/viewdept.php?id=' . $tool->id . '">' . format_string($tool->fullname) . '</a>';
            $line[] = $tool->shortname;
            $schoolname = $DB->get_record('local_school', array('id' => $tool->schoolid));
            $line[] = $schoolname->fullname;
            $hier = new hierarchy();
            // ------------------- Edited by hema------------------------------

            $systemcontext = context_system::instance();
            if (has_any_capability($capabilities_array, $systemcontext)) {
               
                $buttons = $hier->get_actions('departments', 'departments', $tool->id, $tool->visible);
                $line[] = $buttons;
            }
            $data[] = $line;
        }
        echo "<div id='filter-box' >";
        echo '<div class="filterarea"></div></div>';

        //View Part starts
        //start the table
        $table = new html_table();
        $table->id = 'depttable';
        $table->head = array(
            get_string('deptfullname', 'local_departments'),
            get_string('deptid', 'local_departments'),
            get_string('schoolid', 'local_collegestructure'));
        // ------------------- Edited by hema------------------------------ 
        if (has_any_capability($capabilities_array, $systemcontext)) {
            array_push($table->head, get_string('action'));
        }
        $table->size = array('15%', '15%', '15%', '15%');
        $table->align = array('left', 'left', 'left', 'center');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
    }

//----end of get_table function--------------------------------------
//----only displaying registrar enroll school informations------------

    foreach ($schoolid as $sid) {
        if ($sid->id != null) {
            $temp[] = $sid->id;
        }
    }
    $school_id = implode(',', $temp);
    $sql = "SELECT * From {$CFG->prefix}local_department where schoolid in ($school_id)";
    $res = $DB->get_records_sql($sql);
    //  print_object($data);
    if (empty($res)) {
        $e = get_string('record_notfound', 'local_departments');
        throw new Exception($e);
    }
    get_table($res);
} //--------- end of try block--------------------------------------------
catch (Exception $e) {
    echo $e->getMessage();
}

//---------download button--------------
if($res){
echo '<form action="download_all.php?format=xls" method="post">
         <input type="submit" value="Download"  ></input>
	 </form>';
}   
echo $OUTPUT->footer();
?>




