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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Gradesubmission
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
require_once($CFG->dirroot . '/local/lib.php');
global $CFG, $DB, $USER;

$currenttab = optional_param('mode', 'viewgrades', PARAM_RAW);

$systemcontext = context_system::instance();
// get the admin layout
$PAGE->set_pagelayout('admin');
// check the context level of the user and check whether the user is login to the system or not

require_login();

$PAGE->set_context($systemcontext);
if (!has_capability('local/gradesubmission:view', $systemcontext)) {
    print_error('permissions_error','local_collegestructure');
}

$PAGE->set_url('/local/gradesubmission/index.php');
// Header and the navigation bar
$PAGE->set_heading($SITE->fullname);
$string = get_string('pluginname', 'local_gradesubmission');
$PAGE->set_title($string);
$PAGE->navbar->add(get_string('viewgradesubmission', 'local_gradesubmission'));
//$PAGE->navbar->add(get_string('view', 'local_graduation'));
$strheading = get_string('managegradesubmission', 'local_gradesubmission');
// echo $OUTPUT->header();
echo $OUTPUT->header();
echo $OUTPUT->heading($strheading);
// Heading of the page

// error handling when school not yet created or loogedin user not assigned to any school
$hierarchy = new hierarchy();
$hierarchy->get_school_items();

createtabview_gsub($currenttab);

if (has_capability('local/scheduleexam:manage', $systemcontext)) {
    $isadmin = is_siteadmin($USER);
    if ((!$isadmin) AND ( has_capability('local/collegestructure:manage', $systemcontext))) {
        $sql = "SELECT ue.* FROM {local_school_permissions} sp,{local_user_examgrades} ue WHERE sp.userid={$USER->id} AND sp.schoolid=ue.schoolid";
        $stugrades = $DB->get_records_sql($sql);
    } else {
        $stugrades = $DB->get_records('local_user_examgrades');
    }
} else {
    $instsql = "SELECT userg.* FROM {local_user_examgrades} userg
                JOIN {local_scheduleclass} sc ON sc.classid = userg.classid
                WHERE sc.instructorid ={$USER->id}";
    $stugrades = $DB->get_records_sql($instsql);
}

$data = array();
foreach ($stugrades as $stgrade) {
    $stuname = ($DB->get_field('user', 'firstname', array('id' => $stgrade->userid))) . ' ' . ($DB->get_field('user', 'lastname', array('id' => $stgrade->userid)));
    //examname
    $examtypeid = $DB->get_field('local_scheduledexams', 'examtype', array('id' => $stgrade->examid));
    $lecturetypeid = $DB->get_field('local_scheduledexams', 'lecturetype', array('id' => $stgrade->examid));
    /*
     * ###Bug report #169- Grade submission
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Added online examnames as activity name
     */
    if ($stgrade->source === 'offline')
        $examname = ($DB->get_field('local_examtypes', 'examtype', array('id' => $examtypeid))) . '-' . ($DB->get_field('local_lecturetype', 'lecturetype', array('id' => $lecturetypeid)));
    else
        $examname = $DB->get_field('grade_items', 'itemname', array('id' => $stgrade->examid));
    //classname
    $classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $stgrade->classid));
    //coursename
    $sql = 'SELECT co.fullname FROM {local_clclasses} c JOIN {local_cobaltcourses} co on c.cobaltcourseid=co.id where c.id=' . $stgrade->classid . '';
    $cobcourse = $DB->get_record_sql($sql);
    $line = array();
    $line[] = $DB->get_field('local_userdata', 'serviceid', array('userid' => $stgrade->userid));
    $line[] = $stuname;
    $line[] = $examname;
    $line[] = $stgrade->finalgrade;
    $line[] = $cobcourse->fullname . ' (' . $classname . '- class)';
    $line[] = $classname;
    $line[] = $DB->get_field('local_semester', 'fullname', array('id' => $stgrade->semesterid));
    $line[] = $DB->get_field('local_school', 'fullname', array('id' => $stgrade->schoolid));
    $data[] = $line;
}
//print_object($data);
// Moodle 2.2 and onwards
if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('pagedescription', 'local_gradesubmission'));
}
$PAGE->requires->js('/local/gradesubmission/js/gradesub.js');

echo "<div id='filter-box' >";
echo '<div class="filterarea"></div></div>';
//  View Part starts
$table = new html_table();
$table->id = "gradsubviewtable";
$table->head = array(
    get_string('studentid', 'local_gradesubmission'), get_string('studentname', 'local_gradesubmission'), get_string('examname', 'local_gradesubmission'), get_string('score', 'local_gradesubmission'), get_string('courseandclass', 'local_gradesubmission'), get_string('courseandclass', 'local_gradesubmission'), get_string('semester', 'local_semesters'), get_string('schoolid', 'local_collegestructure'));
$table->size = array('7%', '13%', '20%', '5%', '23%', '7%', '20%');
$table->align = array('left', 'left', 'left', 'left', 'center');
$table->width = '100%';
$table->data = $data;
echo html_writer::table($table);

if (empty($data)) {
    echo $OUTPUT->box_start('generalbox');
    echo get_string('no_records', 'local_request');
    echo $OUTPUT->box_end();
}
echo $OUTPUT->footer();
