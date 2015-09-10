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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');
global $CFG, $USER, $DB, $PAGE;
$PAGE->set_url('/local/courseregistration/stuclasses.php');
$PAGE->set_pagelayout('admin');
$systemcontext = context_system::instance();

$courseid = optional_param('id', 0, PARAM_INT);
$price = optional_param('price', false, PARAM_BOOL);
$semid = optional_param('semid', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
//get the admin layout
//check the context level of the user and check weather the user is login to the system or not
$PAGE->set_context($systemcontext);

require_login();

//$PAGE->set_url('/local/courseregistration/myclasses.php');
$context =context_user::instance($USER->id);
//Header and the navigation bar
$PAGE->set_heading($SITE->fullname);

if (!has_capability('local/courseregistration:view', $context)) {
    print_error('You don\'t have permissions');
}

if ($courseid) {
    if ($price) {
        redirect(new moodle_url('/course/view.php', array('id' => $courseid, 'sesskey' => sesskey())));
    } else {
        $coursecontext = context_course::instance($courseid);
        $role_assign = new stdClass();
        $role_assign->userid = $USER->id;
        $role_assign->roleid = 5;
        $role_assign->contextid = $coursecontext->id;
        $role_assign->timemodified = time();
        $role_assign->modifierid = $USER->id;
        if (!$DB->record_exists('role_assignments', array('userid' => $USER->id, 'contextid' => $coursecontext->id, 'roleid' => 5)))
            $DB->insert_record('role_assignments', $role_assign);

        $enrol = $DB->get_record('enrol', array('courseid' => $courseid, 'status' => 0));
        $user_enrol = new stdClass();
        $user_enrol->status = $enrol->status;
        $user_enrol->enrolid = $enrol->id;
        $user_enrol->userid = $USER->id;
        $user_enrol->modifierid = $USER->id;
        $user_enrol->timecreated = time();
        $user_enrol->timemodified = time();
        if (!$DB->record_exists('user_enrolments', array('userid' => $USER->id, 'enrolid' => $enrol->id)))
            $DB->insert_record('user_enrolments', $user_enrol);

        redirect(new moodle_url('/course/view.php', array('id' => $courseid, 'sesskey' => sesskey())));
    }
}

$heading = get_string('myacademics', 'local_courseregistration');
$PAGE->navbar->add(get_string('myacademics', 'local_courseregistration'));
echo $OUTPUT->header();
echo $OUTPUT->heading($heading);
$exams = new schedule_exam();
// Moodle 2.2 and onwards
$currenttab = 'mycurrentplan';
//adding tabs
$exams->studentside_tabs($currenttab);

if (isset($CFG->allowframembedding) and ! $CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('myclasstabdes', 'local_courseregistration'));
}
$today = date('Y-m-d');


$query = "SELECT lc.*,cc.id AS courseid,
                     cc.fullname AS coursename,
                     cc.credithours AS credithours
                FROM {local_user_clclasses} c JOIN {local_clclasses} lc ON c.classid=lc.id 
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                     JOIN {local_semester} s On s.id=c.semesterid
                        where c.userid={$USER->id} AND c.studentapproval=1 AND c.registrarapproval=1 AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate))";

$classList = $DB->get_records_sql($query);

$data = array();
$credit = 0;
foreach ($classList as $list) {
    $line = array();
    $line[] = '<a title="View Module" href="' . $CFG->wwwroot . '/local/cobaltcourses/viewcourse.php?id=' . $list->courseid . '&sesskey=' . sesskey() . '" target="_blank">' . $list->coursename . '</a>';
    $line[] = '<a title="View Class" href="' . $CFG->wwwroot . '/local/clclasses/view.php?id=' . $list->id . '&sesskey=' . sesskey() . '" target="_blank">' . $list->shortname . '</a>';

    $cost = $DB->get_record('local_classcost', array('classid' => $list->id));
    if ($cost) {
        if ($cost->classcost != 0)
            $classcost = '&pound ' . $cost->classcost;
        else if ($cost->credithourcost != 0)
            $classcost = '&pound ' . ($list->credithours * $cost->credithourcost);
    } else {
        $classcost = '&pound 0.00';
    }
    $line[] = $list->credithours;
    $line[] = $classcost;
    $accesscourse = get_string('launch', 'local_courseregistration');
    if ($list->online && $list->onlinecourseid) {
        if ($cost) {
            $sql = "SELECT i.* FROM {local_item} i JOIN {local_order} o ON i.orderid = o.id WHERE o.semesterid = $list->semesterid AND o.userid = {$USER->id} AND i.moduleid = {$list->id}";
            if (!$record = $DB->get_record_sql($sql)) {
                $line[] = '<a title="Pay Now" href="' . $CFG->wwwroot . '/local/onlinepayment/pendingpay.php" target="_blank"><button>Pay Now</button></a>';
            } else {
                $transaction = $DB->get_record('local_payment_transaction', array('orderid' => $record->orderid, 'userid' => $USER->id), '*', MUST_EXIST);
                if ($transaction->status == 'pending') {
                    $line[] = 'Your Transaction is pending.';
                } else if ($transaction->status == 'Success') {
                    $line[] = '<a title="' . $accesscourse . '" href="' . $CFG->wwwroot . '/course/view.php?id=' . $list->onlinecourseid . '&sesskey=' . sesskey() . '" target="_blank">' . $accesscourse . '</a>';
                }
            }
        } else {
            $line[] = '<a title="' . $accesscourse . '" href="' . $CFG->wwwroot . '/course/view.php?id=' . $list->onlinecourseid . '&sesskey=' . sesskey() . '" target="_blank">' . $accesscourse . '</a>';
        }
    } else {
        $line[] = 'Offline';
    }
    $credit = $credit + $list->credithours;
    $data[] = $line;
}
if ($data)
    $data[] = array('', '', 'Total: <b>' . $credit . '</b>', '', '');
$data[] = array('<h3>Mooc Modules</h3>', '', '', '', '');

$mooc_courses = $DB->get_records_select('course', 'id != 1');
$clclasses = $DB->get_records('local_clclasses');
foreach ($clclasses as $class) {
    if ($class->onlinecourseid) {
        unset($mooc_courses[$class->onlinecourseid]);
    }
}
$accesscourse = get_string('launch', 'local_courseregistration');
foreach ($mooc_courses as $course) {
    $line = array();
    $line[] = $course->fullname;
    $line[] = 'Online Module';
    $line[] = '-';
    $cost = $DB->get_record('local_classcost', array('courseid' => $course->id));
    if ($cost) {
        $coursecost = '&pound ' . $cost->coursecost;
    } else {
        $coursecost = '&pound 0.00';
    }
    $line[] = $coursecost;
    if ($cost) {
        $price = true;
        $sql = "SELECT i.* FROM {local_item} i JOIN {local_order} o ON i.orderid = o.id WHERE o.userid = {$USER->id} AND i.online_courseid = {$course->id}";
        if (!$record = $DB->get_record_sql($sql)) {
            $line[] = '<a title="Pay Now" href="' . $CFG->wwwroot . '/local/onlinepayment/pendingpay.php" target="_blank"><button>Pay Now</button></a>';
        } else {
            $transaction = $DB->get_record('local_payment_transaction', array('orderid' => $record->orderid, 'userid' => $USER->id), '*', MUST_EXIST);
            if ($transaction->status == 'pending') {
                $line[] = 'Your Transaction is pending.';
            } else if ($transaction->status == 'Success') {
                $line[] = '<a title="' . $accesscourse . '" href="' . $CFG->wwwroot . '/local/courseregistration/stuclasses.php?id=' . $course->id . '&price=' . $price . '" target="_blank">' . $accesscourse . '</a>';
            }
        }
    } else {
        $line[] = '<a title="' . $accesscourse . '" href="' . $CFG->wwwroot . '/local/courseregistration/stuclasses.php?id=' . $course->id . '&price=' . $price . '" target="_blank">' . $accesscourse . '</a>';
    }
    $data[] = $line;
}

$table = new html_table();
$table->head = array(get_string('coursename', 'local_courseregistration'),
    get_string('code', 'local_clclasses'),
    get_string('credithours', 'local_cobaltcourses'),
    get_string('price', 'local_onlinepayment'),
    get_string('launch', 'local_courseregistration'));
$table->size = array('25%', '15%', '15%', '20%', '20%');
$table->align = array('left', 'left', 'center', 'center', 'center');
$table->width = '99%';
$table->data = $data;
echo html_writer::table($table);
echo $OUTPUT->footer();
