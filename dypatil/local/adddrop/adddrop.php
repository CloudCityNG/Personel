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
require_once($CFG->dirroot . '/local/adddrop/lib.php');
$id = optional_param('id', -1, PARAM_INT);    // user id; -1 if creating new tool
$unapprove = optional_param('unapprove', 0, PARAM_BOOL);
$sapprove = optional_param('sapprove', 0, PARAM_INT);
$rapprove = optional_param('rapprove', 0, PARAM_BOOL);
$mapprove = optional_param('mapprove', 0, PARAM_BOOL);
$confirm = optional_param('confirm', 0, PARAM_BOOL);
$publish = optional_param('publish', 0, PARAM_INT);
$page = optional_param('page', 0, PARAM_INT);
$courseid = optional_param('courseid', 0, PARAM_INT);
$semid = optional_param('semid', 0, PARAM_INT);
$programid = optional_param('programid', 0, PARAM_INT);
require_login();
$PAGE->set_url('/local/courseregistration/registration.php', array('id' => $id));
$systemcontext = context_system::instance();
$PAGE->set_context($systemcontext);
$strheading = get_string('manageregistration', 'local_courseregistration');
$returnurl = new moodle_url('/local/adddrop/index.php', array('id' => $id, 'page' => $page));
/* ---function for assigning the courses from student--- */
if ($sapprove) {
    $returnurl = new moodle_url('/local/adddrop/index.php', array('id' => $id, 'semesterid' => $sapprove, 'batchid' => 1, 'sapprove' => $sapprove));
    approve_adddrop_student($id, $semid, $sapprove, $programid);
    redirect($returnurl);
}

if ($publish) {
    $returnurl = new moodle_url('/local/adddrop/registration.php', array('id' => $id, 'semesterid' => $semesterid, 'batchid' => 1));
    $PAGE->url->param('assign', 1);
    publish_courseregistration_instance($semesterid, $batchid);
    redirect($returnurl);
}

