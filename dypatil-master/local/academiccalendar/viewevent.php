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
 * @subpackage Academiccalendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
$id = required_param('id', PARAM_INT);
global $CFG, $DB;
$PAGE->set_url('/local/academiccalendar/viewevent.php');
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('pluginname', 'local_academiccalendar'));
if (isloggedin()) {
    $PAGE->navbar->add(get_string('pluginname', 'local_academiccalendar'), new moodle_url('/local/academiccalendar/index.php'));
    $PAGE->navbar->add(get_string('vieweventnav', 'local_academiccalendar'));
}
echo $OUTPUT->header();
$ev = $DB->get_record('local_event_activities', array('id' => $_REQUEST['id']));
$activitytype = $DB->get_field('local_event_types', 'eventtypename', array('id' => $ev->eventtypeid));
$startdate = date('D, d-M-Y', $ev->startdate);
$ev->enddate != 0 && $ev->enddate != NULL ? $enddate = date('D, d-M-Y', $ev->enddate) : $enddate = '';
if (!empty($ev->schoolid)) {
    $school_inst = $DB->get_record('local_school', array('id' => $ev->schoolid));
    $schoolname = $school_inst->fullname;
}
if (!empty($ev->programid)) {
    $program_inst = $DB->get_record('local_program', array('id' => $ev->programid));
    $programname = $program_inst->fullname;
}
if (!empty($ev->semesterid)) {
    $semester_inst = $DB->get_record('local_semester', array('id' => $ev->semesterid));
    $semestername = $semester_inst->fullname;
}
$eventls = array('1' => 'Global',
    '2' => 'Organization',
    '3' => 'Program',
    '4' => 'Course Offering');
$table = new html_table();
$line = array();
$line[] = array('<b>Event Level</b>', $eventls[$ev->eventlevel]);
$line[] = array('<b>Activity Name</b>', $activitytype);
$line[] = array('<b>Event Title</b>', $ev->eventtitle);
$line[] = array('<b>Description</b>', $ev->description);
$line[] = array('<b>Start Date</b>', $startdate);
$enddate != NULL ? $line[] = array('<b>End Date</b>', $enddate) : null;
if (empty($ev->eventlevel)) {
    $line[] = array('<b>Organization</b>', $schoolname);
    $line[] = array('<b>Course Offering</b>', $semestername);
} else {
    switch ($ev->eventlevel) {
        case 2:
            $line[] = array('<b>Organization</b>', $schoolname);
            if ($ev->eventtypeid == 1 && $ev->eventlevel == 2) {
                $line[] = array('<b>Program</b>', $programname);
            } else if (($ev->eventtypeid == 2 || $ev->eventtypeid == 3) && $ev->eventlevel == 2) {
                $line[] = array('<b>Course Offering</b>', $semestername);
            }
            break;
        case 3:
            $line[] = array('<b>Organization</b>', $schoolname);
            $line[] = array('<b>Program</b>', $programname);
            break;
        case 4:
            $line[] = array('<b>Organization</b>', $schoolname);
            $line[] = array('<b>Course Offering</b>', $semestername);
            break;
    }
}
$table->data = $line;
$table->size = array('25%', '75%');
echo $OUTPUT->heading(get_string('viewevent', 'local_academiccalendar', $ev));
echo html_writer::table($table);
echo $OUTPUT->footer();
?>
