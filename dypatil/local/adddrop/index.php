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
 * @subpackage Adddrop courses
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $USER, $DB;
$systemcontext = context_system::instance();
$page = optional_param('page', 0, PARAM_INT);
$semid = optional_param('semesterid', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
require_login();
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
if ($CFG->forcelogin) {
    require_login();
} else {
    user_accesstime_log();
}
$today = date('Y-m-d');
$PAGE->set_url('/local/adddrop/index.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('manageadddrop', 'local_adddrop'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('manageadddrop', 'local_adddrop'));
/* ---Moodle 2.2 and onwards--- */
$currenttab = 'adddropperiod';
/* ---adding tabs--- */
print_adddroptabs($currenttab, 'student');
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('allowframembedding', 'local_adddrop'));
}

$context = context_user::instance($USER->id);
if (has_capability('local/clclasses:enrollclass', $context)) {
    $semester = student_semesters($USER->id);
    $query = "SELECT distinct(e.semesterid) as semesterid,
			   e.programid 
			   FROM {user} u,
			   {local_event_activities} e,
			   {local_event_types} le 
			   WHERE e.eventtypeid=le.id AND e.eventtypeid=3 AND e.publish=1 AND '{$today}' BETWEEN from_unixtime( e.startdate,'%Y-%m-%d' ) AND from_unixtime( e.enddate,'%Y-%m-%d' ) ";

    $tools = $DB->get_records_sql($query);
    // enable buttin only when add/drop event opened
    if ($tools) {
        echo '<div id="createbutton" style="text-align:right">';
        echo $OUTPUT->single_button(new moodle_url('/local/courseregistration/index.php'), get_string('courseregistration', 'local_adddrop'));
        echo '</div>';
    }


    $data = array();
    if ($tools) {
        foreach ($tools as $tool) {
            $semid = $tool->semesterid;
            $programid = $tool->programid;
            $query = "SELECT distinct(lc.id) as class,lc.*,c.fullname as coursename,c.credithours,cs.classid 
                     FROM {local_user_clclasses} cs,{local_clclasses} lc,{local_cobaltcourses} c
                     where cs.userid={$USER->id} AND lc.id=cs.classid AND lc.cobaltcourseid=c.id AND cs.registrarapproval=1 AND cs.semesterid={$semid}  ";
            $cList = $DB->get_records_sql($query);
            foreach ($cList as $list) {
                $line = array();
                $line[] = $list->coursename;
                $line[] = '<a  href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $list->id . '">' . $list->shortname . '</a>';
                $line[] = $list->credithours;
                $checkExist = get_adddropexist($USER->id, $list->classid, $semid);
                //print_object($checkExist);
                //if(!empty($checkExist)){
                foreach ($checkExist as $exists) {
                    $approveStatus = $exists->studentapproval;
                    $getstatus = get_adddrop_status($USER->id, $list->classid, $semid);
                    if (!empty($getstatus)) {
                        foreach ($getstatus as $status) {
                            // use of $target status variable to change the value of studnt approvals.(consider value from adddrop and manual registration.)
                            if (isset($status->adddrop)) {
                                if ($status->adddrop == 1)
                                    $target_status = 2;
                            }
                            else {
                                if ($status->userclclasses == 1)
                                    $target_status = 1;
                            }

                            $approveStatus = $status->studentapproval;
                            $mentorStatus = $status->mentorapproval;
                            $registratStatus = $status->registrarapproval;
                            if ($approveStatus == $target_status) {
                                $string = get_string('adding', 'local_adddrop');
                                $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
                                $line[] = $statusString[1];
                            } else {
                                if ($approveStatus == 1) {
                                    $string = get_string('droping', 'local_adddrop');
                                    $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
                                    $line[] = $statusString[1];
                                }
                            }
                        }
                    } else {
                        $line[] = get_string('noaction', 'local_adddrop');
                    }
                    if ($approveStatus == $target_status)
                        $line[] = html_writer::link(new moodle_url('/local/adddrop/adddrop.php', array('id' => $list->id, 'semid' => $semid, 'page' => $page, 'sapprove' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'title' => get_string('dropcourse', 'local_adddrop'), 'alt' => get_string('dropcourse', 'local_adddrop'), 'class' => 'iconsmall')));
                    else
                        $line[] = get_string('reqcoursedroped', 'local_adddrop');
                }
                //}
                $data[] = $line;
            }
        }
    }
    else {
        $cell = new html_table_cell(get_string('noaction', 'local_adddrop'));
        $cell->colspan = 5;
        $row = new html_table_row(array($cell));
        $data[] = $row;
    }

    // when table data is empty provide message
    if (empty($data)) {
        $cell = new html_table_cell(get_string('noaction', 'local_adddrop'));
        $cell->colspan = 5;
        $row = new html_table_row(array($cell));
        $data[] = $row;
    }


    $table = new html_table();
    $table->id = "cooktable";
    $table->head = array(
        get_string('coursename', 'local_cobaltcourses'),
        get_string('code', 'local_clclasses'),
        get_string('credithours', 'local_adddrop'),
        get_string('status', 'local_courseregistration'),
        get_string('dropcourse_label', 'local_adddrop'));
    $table->size = array('25%', '10%', '10%', '25%', '10%');
    $table->align = array('center', 'center', 'center', 'center', 'center');
    $table->width = '99%';
    $table->data = $data;
    echo html_writer::table($table);
} else {
    $returnurl = new moodle_url('/local/courseregistration/mentor.php?current=completed');
    redirect($returnurl);
}
echo $OUTPUT->footer();
