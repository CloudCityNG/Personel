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
 * @subpackage  Departments
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/timetable/lib.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$delete = optional_param('delete', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$visible = optional_param('visible', -1, PARAM_INT);
$scid = optional_param('scid', 0, PARAM_INT);
$view = optional_param('view', '', PARAM_TEXT);

global $CFG, $DB;
$systemcontext =  context_system::instance();

//get the admin layout
$PAGE->set_pagelayout('admin');

//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

$PAGE->set_url('/local/timetable/index.php');
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('pluginname', 'local_timetable'), new moodle_url('/local/timetable/index.php'));
$PAGE->navbar->add(get_string('setstandardview_timings', 'local_timetable'), new moodle_url('/local/timetable/index.php'));

//this is the return url 
$returnurl = new moodle_url('/local/timetable/index.php', array('id' => $id));
$currenturl = "{$CFG->wwwroot}/local/timetable/index.php";

// calling manage_dept class instance.....
$timetable_ob = manage_timetable::getInstance();
$hier1 = new hierarchy();

//for edit purpose  //
//if ($id > 0)
//    $PAGE->navbar->add(get_string('edit_department', 'local_departments'));
//else
//    $PAGE->navbar->add(get_string('createdept', 'local_departments'));        

echo $OUTPUT->header();
//----------------manage dept heading------------------------
echo $OUTPUT->heading(get_string('pluginname', 'local_timetable'));


// checking if login user is registrar or admin
$schoolid = $timetable_ob->check_loginuser_registrar_admin();


//adding tabs using manage_tabs function
$currenttab = 'view_timings';
//for edit purpose  //
if ($id > 0)
    $timetable_ob->timetable_tabs($currenttab, null, 'edit_label');
else
    $timetable_ob->timetable_tabs($currenttab);


echo "<a href=$CFG->wwwroot/local/timetable/settimings.php target='_blank' id='settiming_buttton'>" . get_string('setstandard_timings', 'local_timetable') . "</a>";
$output = $PAGE->get_renderer('local_timetable');

$tmobject = new timetable($schoolid);
echo $output->render($tmobject);

echo $output->footer();
//echo $OUTPUT->footer();
?>