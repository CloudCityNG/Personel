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
 * @subpackage classes
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/clclasses/lib.php');
$mode = optional_param('mode', 'current', PARAM_RAW);
global $CFG;
$systemcontext = context_system::instance();
/* ---get the admin layout--- */
$PAGE->set_pagelayout('admin');
/* ---check the context level of the user and check weather the user is login to the system or not--- */
$PAGE->set_context($systemcontext);
require_login();
$semclass = new schoolclasses();
$PAGE->set_url('/local/clclasses/index.php');
$roleid = $DB->get_field('role_assignments', 'roleid', array('userid' => $USER->id, 'contextid' => $systemcontext->id));
$role = $DB->get_record('role', array('id' => $roleid));
if ($role->shortname != 'instructor') {
    print_error('You dont have permissions');
}
/* ---Header and the navigation bar--- */
$PAGE->set_heading($SITE->fullname);
$title = get_string('mycourses', 'local_clclasses');
$PAGE->navbar->add($title);
echo $OUTPUT->header();
/* ---Heading of the page--- */
echo $OUTPUT->heading(get_string('mycourses', 'local_clclasses'));
if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('instviewpage', 'local_clclasses'));
}
$semclass->print_instructorviewtabs($mode);
$lists = array();
$today = date('Y-m-d');
if ($DB->record_exists('local_scheduleclass', array('instructorid' => $USER->id))) {
    /* ---Get the records from the database--- */
    $sql = "SELECT class.*, count(enroll.userid) AS enrolled
                FROM {local_clclasses} AS class
                JOIN {local_user_clclasses} AS enroll ON enroll.classid = class.id AND enroll.semesterid = class.semesterid
                JOIN {local_scheduleclass} AS sch ON sch.semesterid = class.semesterid AND sch.classid = class.id
                JOIN {local_semester} AS sem
                ON sem.id = class.semesterid
                WHERE sch.instructorid = {$USER->id} AND enroll.mentorapproval = 1 AND enroll.registrarapproval = 1 ";
    if ($mode == 'current') {
        /* ---get the list of current semesters--- */
        $sql .= " AND '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
    }
    if ($mode == 'upcoming') {
        /* ---get the list of upcoming semesters--- */
        $sql .= "  AND '{$today}' < from_unixtime( sem.startdate,  '%Y-%m-%d' )";
    }
    if ($mode == 'completed') {
        /* ---get the list of upcoming semesters--- */
        $sql .= "  AND '{$today}' > from_unixtime( sem.enddate,  '%Y-%m-%d' )";
    }

    $lists = $DB->get_records_sql($sql);
}

$data = array();
if (!empty($lists)) {
    foreach ($lists as $list) {
        $line = array();
        $line[] = html_writer::tag('a', $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $list->cobaltcourseid)), array('href' => '' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $list->cobaltcourseid . '&plugin=classes&page=instview&title=' . $title . ''));
        $line[] = html_writer::tag('a', $list->fullname, array('href' => '' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $list->id . '&inst=1'));
        $line[] = $list->enrolled;
        $data[] = $line;
    }
} else {
    $line = array();
    $line[] = "No records found.";
    $data[] = $line;
}
$table = new html_table();
$table->head = array(
    get_string('cobaltcourse', 'local_clclasses'), get_string('class', 'local_clclasses'), get_string('noofenrolled', 'local_clclasses'));
$table->size = array('40%', '40%', '20%');
$table->align = array('left', 'left', 'left');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
