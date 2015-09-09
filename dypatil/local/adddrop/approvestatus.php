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
 * @subpackage Add/Drop Courses
 * @copyright  2013 Niranjan <niranjan@cobaltlms.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/adddrop/lib.php');
require_once($CFG->dirroot . '/local/myacademics/lib.php');
global $CFG, $USER, $DB;
$systemcontext =context_system::instance();
$cid = optional_param('id', 0, PARAM_INT);
$semid = optional_param('semesterid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$PAGE->set_url('/local/adddrop/approvestatus.php');
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$PAGE->navbar->add(get_string('drop', 'local_adddrop'));
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('drop', 'local_adddrop'));
/* ---Moodle 2.2 and onwards--- */
$currenttab = 'myadddropstatus';
/* ---adding tabs--- */
print_adddroptabs($currenttab, 'student');
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('myclasstabdes', 'local_courseregistration'));
}
$context =context_user::instance($USER->id);
if (has_capability('local/clclasses:enrollclass', $context)) {
    $semester = student_semesters($USER->id,'adddrop');
    if(empty($semester))
        print_cobalterror('notenrolledanyclass','local_adddrop');
    echo '<div class="selfilterposition" style="text-align:center;margin:20px;">';
    $select = new single_select(new moodle_url('/local/adddrop/approvestatus.php'), 'semesterid', $semester, $semid, null);
    $select->set_label(get_string('semester', 'local_semesters'));
    echo $OUTPUT->render($select);
    echo '</div>';
    $today = date('Y-m-d');
    if ($semid) {
        $query = "SELECT lc.*,cc.id AS courseid,
                     cc.fullname AS coursename,
                     c.registrarapproval AS rapproval,
                     c.mentorapproval AS mapproval,
                     c.studentapproval AS sapproval,
                     cc.credithours AS credithours
                      
                FROM {local_course_adddrop} c JOIN {local_clclasses} lc ON c.classid=lc.id 
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid where c.userid={$USER->id} AND c.semesterid={$semid}";

        $classList = $DB->get_records_sql($query);
        $data = array();

        $credit = 0;

        if ($classList) {
            foreach ($classList as $list) {
                $mentorStatus = $list->mapproval;
                $approveStatus = $list->sapproval;
                $registratStatus = $list->rapproval;
                $line = array();
                $linkcss = $list->visible ? ' ' : 'class="dimmed" ';
                $line[] = $list->coursename;
                $line[] = '<a  href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $list->id . '">' . $list->shortname . '</a>';
                if ($approveStatus == 1) {
                    $line[] = get_string('dropedcourse', 'local_adddrop');
                    $string = get_string('droping', 'local_adddrop');
                    $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
                    $line[] = $statusString[0];
                    $line[] = $statusString[1];
                }
                if ($approveStatus == 2) {
                    $line[] = get_string('addedcourse', 'local_adddrop');
                    $string = get_string('adding', 'local_adddrop');
                    $statusString = get_statusStrings($mentorStatus, $registratStatus, $string);
                    $line[] = $statusString[0];
                    $line[] = $statusString[1];
                }

                $data[] = $line;
            }
        } else {
            $line[] = "No Records Found";
            $data[] = $line;
        }
        $table = new html_table();
        $table->head = array(
            get_string('coursename', 'local_courseregistration'),
            get_string('code', 'local_clclasses'),
            get_string('requestfor', 'local_courseregistration'), 
            get_string('status', 'local_courseregistration'),
            get_string('remarks', 'local_courseregistration'));
        $table->size = array('15%', '20%', '10%', '15%', '20%');
        $table->align = array('left', 'center', 'center', 'center', 'center');
        $table->width = '99%';
        $table->data = $data;
        echo html_writer::table($table);
    }
} else {
    $returnurl = new moodle_url('/local/courseregistration/mentor.php?current=completed');
    redirect($returnurl);
}
echo $OUTPUT->footer();
