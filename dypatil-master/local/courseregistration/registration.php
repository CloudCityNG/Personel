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
 * @subpackage Faculty
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/courseregistration/lib.php');

$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$unapprove = optional_param('unapprove', 0, PARAM_BOOL);
$rapprove = optional_param('rapprove', 0, PARAM_BOOL);
$mapprove = optional_param('mapprove', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$addclass = optional_param('addenroll', 0, PARAM_TEXT);
$publish = optional_param('publish', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);
$schoolid = optional_param('schoolid', 0, PARAM_INT);
$session = optional_param('sesskey', 0, PARAM_ALPHANUM);
$ajax_response = optional_param('ajax_response', 0, PARAM_BOOL);

require_login();
/* ---checking the course exists or not--- */
$PAGE->set_url('/local/courseregistration/registration.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
/* ---this is the return url--- */
$strheading = get_string('manageregistration', 'local_courseregistration');
$returnurl = new moodle_url('/local/courseregistration/index.php', array('id' => $id, 'page' => $page));

/* ---function for unassigning the courses--- */
if ($unapprove) {
    $returnurl = new moodle_url('/local/courseregistration/index.php', array('id' => $id, 'semesterid' => $semid, 'batchid' => 1, 'sapprove' => $addclass));
    unapprove_registration_student($id, $semid, $addclass, $programid);
    redirect($returnurl);
}

/* ---function for assigning the courses from student--- */
if (!empty($addclass) && $addclass > 0 && !empty($session)) {
    if (isguestuser()) {
        $returnurl = new moodle_url('/login/index.php');
        redirect($returnurl);
    }
    $response = ($ajax_response == 1 ? '1' : '0');
    approve_registration_student($id, $semid, $addclass, $programid, $courseid, $schoolid, $response);
}
if ($addclass < 0 && !empty($session)) {
    unapprove_registration_student($id, $semid, $addclass, $programid, $courseid);
}

if ($publish) {
    $returnurl = new moodle_url('/local/courseregistration/registration.php', array('id' => $id, 'semesterid' => $semesterid, 'batchid' => 1));
    $PAGE->url->param('assign', 1);

    publish_courseregistration_instance($semesterid, $batchid);
    redirect($returnurl);
}

