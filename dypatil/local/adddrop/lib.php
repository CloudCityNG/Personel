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
 * General plugin functions.
 *
 * @package    local
 * @subpackage Curriculum
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\courseregistrations as courseregistration;

require_once($CFG->dirroot . '/lib/enrollib.php');
/**
 * approve_adddrop_student function to drop a function for the student
 * @param1 int $courseid classid that a user want to drop
 * @param2 int $semesterid current semesterid that a user want to drop a class
 * @param3 int $status status(2) of his course dropping
 * @param4 int $programid program id of the user that he want to drop
 */

function approve_adddrop_student($classid, $semesterid, $status, $programid) {
    global $CFG, $DB, $USER;
    $out = array();

    $sql = "SELECT * FROM {local_course_adddrop} s where s.userid={$USER->id} AND s.classid={$classid} AND s.semesterid={$semesterid}";
    $publishlist = $DB->get_records_sql($sql);
    if ($publishlist) {
        foreach ($publishlist as $plist) {
            $publish = new stdClass();
            $publish->id = $plist->id;
            $publish->studentapproval = $status;
            $publish->mentorapproval = 0;
            $publish->registrarapproval = 0;
            $publish->modifiedid = $USER->id;
            $out = $DB->update_record('local_course_adddrop', $publish);
        }
    } else {
        $publish = new stdClass();
        $publish->userid = $USER->id;
        $publish->classid = $classid;
        $publish->semesterid = $semesterid;
        $publish->studentapproval = $status;
        $publish->programid = $programid;
        $publish->mentorapproval = 0;
        $publish->registrarapproval = 0;
        $publish->modifiedid = $USER->id;
        $out = $DB->insert_record('local_course_adddrop', $publish);
    }
    return $out;
}


/**
* @method get_adddrop_status
* @todo to get status information based on two conditions 
* one:stduent enroll to class throw training management interface, two: throw add and drop interface
* @param int $userid user id
* @param int $classid class id
* @param int $semid semester id 
* @return array of object(student information)
* */
function get_adddrop_status($userid, $classid, $semid) {
    global $DB, $CFG;
    $query = "SELECT * FROM {local_course_adddrop} cca WHERE cca.userid=$userid AND cca.classid=$classid AND cca.semesterid=$semid";
    $cexist = $DB->get_records_sql($query);
    if (!empty($cexist)) {
        foreach ($cexist as $cexist)
            $cexist->adddrop = 1;
        $response[] = $cexist;
    }
// this works when admin enrolled user to class  manually(thorw training management interface)
    if (empty($cexist)) {
        $cexist = $DB->get_records_sql("SELECT * FROM {local_user_clclasses} uc WHERE uc.userid=$userid AND uc.classid=$classid AND uc.semesterid=   $semid");
        if (!empty($cexist)) {
            foreach ($cexist as $cexist)
                $cexist->userclclasses = 1;
            $response[] = $cexist;
        }
    }

    return $response;
}

// end of funtion


/**
* @method get_adddropexist
* @todo used to check whether add and drop course exists or what 
* @param int $user user id
* @param int $classList class id
* @param int $semid semester id 
* @return array of object(student information)
* */
function get_adddropexist($user, $classList, $semid) {
    global $CFG, $DB;
    $query = "SELECT * FROM {local_user_clclasses} cca WHERE cca.userid=$user AND cca.classid=$classList AND cca.semesterid=$semid group by cca.classid";
    $cexist = $DB->get_records_sql($query);
    return $cexist;
}


/**
* @method registrarapprovedropcourse
* @todo used to update data when registrar approved drop course request(student request)
* @param int $userid user id
* @param int $classid class id
* @param int $status stats 
* @return display confirmation message
* */
function registrarapprovedropcourse($userid, $classid, $status) {
    global $DB, $USER;
    $hierarchy = new hierarchy();
    $publish = new stdClass();
    $dropcourseid = array();
    $dropcourseid = $DB->get_records('local_course_adddrop', array('userid' => $userid, 'classid' => $classid));
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $returnurl = new moodle_url('/local/adddrop/registrar.php?current=pending');


    foreach ($dropcourseid as $dropcourseids) {

        $dropcourse = new stdClass();
        $dropcourse->id = $dropcourseids->id;
        $dropcourse->registrarapproval = 1;
        $approvestudent = $DB->update_record('local_course_adddrop', $dropcourse);
        $userfrom = $DB->get_record('user', array('id' => $USER->id));
        $sql = "SELECT * FROM {local_user_semester} where userid={$userid} and semesterid={$dropcourseids->semesterid} and programid={$dropcourseids->programid}";
        $existSem = $DB->get_records_sql($sql);


        if (!$existSem) {
            $publish->semesterid = $dropcourseids->semesterid;
            $publish->curriculumid = 0;
            $publish->programid = 0;
            $publish->studentapproval = 1;
            $publish->mentorapproval = 0;
            $publish->registrarapproval = 1;
            $publish->modifiedid = $USER->id;
            $publish->timecreated = time();
            $publish->timemodified = time();
            $publish->usermodified = $USER->id;
            $publish->userid = $userid;

            $DB->insert_record('local_user_semester', $publish);
        }
        $classlist = $DB->get_record('local_clclasses', array('id' => $classid));
        $publish->cobaltcourseid = $classlist->cobaltcourseid;
        $publish->ecourseid = $classlist->onlinecourseid;
        $publish->classid = $classid;
        $publish->semesterid = $dropcourseids->semesterid;
        $publish->userid = $userid;
        $publish->programid = $dropcourseids->programid;
        $publish->timecreated = time();
        $publish->curriculumid = 0;
        $publish->usermodified = $USER->id;
        $publish->studentapproval = 1;
        $publish->mentorapproval = 1;
        $publish->modifiedid = 0;
        if ($status == 2) {
            $publish->registrarapproval = 1;
            $publish->event ='add drop';
            $out = $DB->insert_record('local_user_clclasses', $publish);
        } else {
            $dropclass = new stdClass();
            $updateclass = $DB->get_record('local_user_clclasses', array('userid' => $userid, 'classid' => $classid, 'semesterid' => $dropcourseids->semesterid));
            $dropclass->id = $updateclass->id;
            $dropclass->registrarapproval = 3;
            $out = $DB->update_record('local_user_clclasses', $dropclass);
        }
    }
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $usercourses = $DB->get_record('local_user_clclasses', array('userid' => $userid, 'classid' => $classid));

    if ($usercourses->ecourseid && $status == 2) {
        $manual = enrol_get_plugin('manual');
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $instance = $DB->get_record('enrol', array('courseid' => $usercourses->ecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $enrol = $manual->enrol_user($instance, $userid, $studentrole->id);

        if ($enrol) {
            $message = get_string('droppedsuccess', 'local_adddrop', $classid);
            $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
        }
    }
    if ($approvestudent) {
        $message = get_string('approvedsuccess', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}

// end of function
//}

/**
* @method registrarrejectdropcourse
* @todo used to update data when registrar rejected drop course request(student request)
* @param int $userid user id
* @param int $classid class id
* @return display confirmation message
* */
function registrarrejectdropcourse($userid, $classid) {
    global $DB;
    $returnurl = new moodle_url('/local/adddrop/registrar.php?current=pending');
    $usercourse = $DB->get_records('local_course_adddrop', array('userid' => $userid, 'classid' => $classid));
    $hierarchy = new hierarchy();
    foreach ($usercourse as $usercourses) {
        $publish = new stdClass();
        $publish->id = $usercourses->id;
        $publish->registrarapproval = 2;
        $out = $DB->update_record('local_course_adddrop', $publish);
    }
    if ($out) {
        $message = get_string('unappliedsuccess', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}



/**
* @method  print_adddroptabs
* @todo for printing tabs for add and drop
* @param string $current tab current tab name
* @param string $loginrole (used to enable tabes based on roles)
* @return display tab view
* */
function print_adddroptabs($currenttab, $loginrole) {
    global $OUTPUT;
    $toprow = array();
    if ($loginrole == "registrar") {
        $toprow[] = new tabobject('pending', new moodle_url('/local/adddrop/registrar.php?current=pending'), get_string('currentpendingadddrop', 'local_adddrop'));
        $toprow[] = new tabobject('completed', new moodle_url('/local/adddrop/registrar.php?current=completed'), get_string('completedadddrop', 'local_adddrop'));
    }
    if ($loginrole == "mentor") {
        $toprow[] = new tabobject('pending', new moodle_url('/local/adddrop/mentor.php?current=pending'), get_string('currentpendingadddrop', 'local_adddrop'));
        $toprow[] = new tabobject('completed', new moodle_url('/local/adddrop/mentor.php?current=completed'), get_string('completedadddrop', 'local_adddrop'));
    }
    if ($loginrole == "student") {
        $toprow[] = new tabobject('myadddropstatus', new moodle_url('/local/adddrop/approvestatus.php'), get_string('courseadddropstatus', 'local_adddrop'));
        $toprow[] = new tabobject('adddropperiod', new moodle_url('/local/adddrop/index.php'), get_string('courseadddrop', 'local_adddrop'));
    }

    echo $OUTPUT->tabtree($toprow, $currenttab);
}


/**
* @method mentorapprovaladddrop
* @todo used to update data when mentor approved drop course request(student request)
* @param int $userid user id
* @param int $classid class id
* @return display confirmation message
* */
function mentorapprovaladddrop($userid, $classid) {
    global $DB;

    $hierarchy = new hierarchy();
    $userclass = $DB->get_records('local_course_adddrop', array('userid' => $userid, 'classid' => $classid));
    $returnurl = new moodle_url('/local/adddrop/mentor.php?current=pending');

    foreach ($userclass as $userclclasses) {
        $publish = new stdClass();
        $publish->id = $userclclasses->id;
        $publish->mentorapproval = 1;
        $out = $DB->update_record('local_course_adddrop', $publish);
    }
    if ($out) {
        $message = get_string('approvedsuccess', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}


/**
* @method mentorrejectdropcourse
* @todo used to update data when mentor rejected drop course request(student request)
* @param int $userid user id
* @param int $courseid class id
* @return display confirmation message
* */
function mentorrejectdropcourse($userid, $courseid) {

    global $DB;
    $hierarchy = new hierarchy();
    $usercourse = $DB->get_records('local_course_adddrop', array('userid' => $userid, 'classid' => $courseid));
    $returnurl = new moodle_url('/local/adddrop/mentor.php?current=pending');
    foreach ($usercourse as $usercourses) {
        $publish = new stdClass();
        $publish->id = $usercourses->id;
        $publish->mentorapproval = 2;
        $out = $DB->update_record('local_course_adddrop', $publish);
    }
    if ($out) {
        $message = get_string('mentorrejectedcourse', 'local_courseregistration');
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}


/**
* @method print_adddropstudenttabs
* @todo for printing tabs for student view
* @param string $current tab current tab name
* @return display tab view
* */
function print_adddropstudenttabs($currenttab) {
    global $OUTPUT;
    $toprow = array();
    if (isloggedin()) {
        $toprow[] = new tabobject('myadddropstatus', new moodle_url('/local/adddrop/approvestatus.php'), get_string('courseadddropstatus', 'local_courseregistration'));
        $toprow[] = new tabobject('adddropperiod', new moodle_url('/local/adddrop/index.php'), get_string('courseadddrop', 'local_adddrop'));
    }
    $toprow[] = new tabobject('courseregistration', new moodle_url('/local/courseregistration/index.php'), get_string('register', 'local_courseregistration'));
    echo $OUTPUT->tabtree($toprow, $currenttab);
}


/**
* @method get_statusStrings
* @todo to get approval status role wise
* @param string $mentorStatus 
* @param string $registrarStatus 
* @param string $string
* @return array of status information
* */
function get_statusStrings($mentorStatus, $registratStatus, $string) {

    $line = array();
    $adddropstring = new object();
    $adddropstring->string = $string;
    if ($string == get_string('droping', 'local_adddrop')) {
        $adddropstring->dropstring = get_string('dropped', 'local_adddrop');
        $adddropstring->actionstring = get_string('dropp', 'local_adddrop');
    } else {
        $adddropstring->dropstring = get_string('added', 'local_adddrop');
        $adddropstring->actionstring = get_string('addd', 'local_adddrop');
    }
    if ($mentorStatus == 0 AND $registratStatus == 0) {
        $line[] = get_string('pendingapproval', 'local_adddrop');
        $line[] = get_string('dcwmap', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 0 AND $registratStatus == 1) {
        $line[] = get_string('approved', 'local_adddrop');
        $line[] = get_string('rapwomap', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 0 AND $registratStatus == 2) {
        $line[] = get_string('rejected', 'local_adddrop');
        $line[] = get_string('rRwomap', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 1 AND $registratStatus == 0) {

        $line[] = get_string('pendingapproval', 'local_adddrop');
        $line[] = get_string('dcapmwrap', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 1 AND $registratStatus == 1) {
        $line[] = get_string('approved', 'local_adddrop');
        $line[] = get_string('dcCr', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 1 AND $registratStatus == 2) {
        $line[] = get_string('rejected', 'local_adddrop');
        $line[] = get_string('dcRr', 'local_adddrop', $adddropstring);
    }
    else if ($mentorStatus == 2 AND $registratStatus == 0) {
        $line[] = get_string('pendingapproval', 'local_adddrop');
        $line[] = get_string('dcrmwrap', 'local_adddrop', $adddropstring);
    }
  
    else if ($mentorStatus == 2 AND $registratStatus == 2) {
        $line[] = get_string('rejected', 'local_adddrop');
        $line[] = get_string('dcR', 'local_adddrop', $adddropstring);
    }

   else if ($mentorStatus == 2 AND $registratStatus == 1) {
        $line[] = get_string('approved', 'local_adddrop');
        $line[] = get_string('dcrmapr', 'local_adddrop', $adddropstring);
    }
    else {
        if ( $registratStatus == 5){ 
        $line[] = get_string('unenrolledfromregistrar', 'local_adddrop');
        $line[] = get_string('unenrolledfromregistrar', 'local_adddrop');
        }
    }

    return $line;
}
