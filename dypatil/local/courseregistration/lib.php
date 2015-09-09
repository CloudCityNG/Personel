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

require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

/**
 * @method approve_registration_student
 * @todo used when registrar approve ,enroll student to class
 * @param int $classid Class id, int $semesterid Semester id
 * @param int $status holds student approval status
 * @param int $programid program id, int $courseid course id
 * @param int $schoolid school id,int $ajax_response (if need ajax response set to 1)
 * @return-- display confirmation message
 */
function approve_registration_student($classid, $semesterid, $status, $programid, $courseid, $schoolid, $ajax_response = 0) {

    global $CFG, $DB, $USER;
    $out = array();
    $hierarchy = new hierarchy();
    $returnurl = new moodle_url('/local/courseregistration/viewclasses.php', array('id' => $courseid, 'semid' => $semesterid));
    $today = date('Y-m-d');
    $events = "SELECT ea.eventtypeid FROM {local_event_activities} ea where ea.semesterid={$semesterid} AND ea.schoolid={$schoolid} AND ea.eventtypeid IN(2,3) AND '{$today}' BETWEEN from_unixtime( startdate,'%Y-%m-%d' ) AND from_unixtime( enddate,'%Y-%m-%d' ) ";

    //  print_object($events);

    $eventid = $DB->get_record_sql($events);
    if (empty($eventid)) {
        $rurl = new moodle_url('/local/courseregistration/index.php');
        $message = get_string('eventdateexpired', 'local_courseregistration');
        $hierarchy->set_confirmation($message, $rurl, array('style' => 'notifyproblem'));
        return false;
    }
    if ($eventid->eventtypeid == 2)
        $tableid = "local_user_clclasses";
    if ($eventid->eventtypeid == 3)
        $tableid = "local_course_adddrop";


    $sql = "SELECT s.id FROM {$CFG->prefix}{$tableid} s where s.userid={$USER->id} AND s.classid={$classid} AND s.semesterid={$semesterid}";

    $publishlist = $DB->get_records_sql($sql);
    // print_object($publishlist);
    if ($publishlist) {
        foreach ($publishlist as $plist) {
            $publish = new stdClass();
            $publish->id = $plist->id;
            $out = $DB->update_record("$tableid", $publish);
        }
    } else {

        $classlist = $DB->get_record('local_clclasses', array('id' => $classid));
        $publish = new stdClass();
        $publish->userid = $USER->id;
        $publish->classid = $classid;
        $publish->semesterid = $semesterid;
        if ($eventid->eventtypeid == 2) {
            $publish->cobaltcourseid = $classlist->cobaltcourseid;
            $publish->ecourseid = $classlist->onlinecourseid;
            $publish->studentapproval = $status;
            $publish->event = 'registration';
        }
        if ($eventid->eventtypeid == 3)
            $publish->studentapproval = 2;

        $publish->semesterid = $semesterid;

        $publish->programid = $programid;
        $publish->mentorapproval = 0;
        $publish->registrarapproval = 0;
        $publish->modifiedid = 0;
        $publish->timecreated = time();
        $publish->timemodified = time();


        $out = $DB->insert_record("$tableid", $publish);
        if ($out) {
            if ($ajax_response) {
                echo $out;
            } else {
                $message = get_string('appliedsuccess', 'local_courseregistration', $classid);
                $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
            }
        } else {
            if ($ajax_response)
                echo 0;
            else {
                $message = get_string('appliedfailed', 'local_courseregistration', $classid);
                $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifyproblem'));
            }
        }
    }
}

// end of function

/**
 * @method unapprove_registration_student
 * @todo used when registrar unapprove or rejects the student request, student is removed from class
 * @param int $classid Class id, int $semesterid Semester id
 * @param int $status holds student approval status
 * @param int $programid program id, int $courseid course id
 * @return-- display confirmation message
 */
function unapprove_registration_student($classid, $semesterid, $status, $programid, $courseid) {
    global $CFG, $DB, $USER;
    $out = array();
    $hierarchy = new hierarchy();
    $returnurl = new moodle_url('/local/courseregistration/myclasses.php');

    $sql = "SELECT * FROM {local_user_clclasses} s where s.userid={$USER->id} AND s.classid={$classid} AND s.semesterid={$semesterid}";

    $publishlist = $DB->get_records_sql($sql);

    if ($publishlist) {
        foreach ($publishlist as $plist) {
            $publish = new stdClass();
            $publish->id = $plist->id;
            $publish->studentapproval = $status;
            $out = $DB->delete_records('local_user_clclasses', array('id' => $publish->id));

            if ($out) {
                $message = get_string('unappliedsuccess', 'local_courseregistration', $classid);
                $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
            } else {
                $message = get_string('unappliedfailed', 'local_courseregistration', $classid);
                $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifyproblem'));
            }
        }
    }
}

/**
 * @method mentorapprovecourse
 * @todo used when mentor approved the student request( for course enrollment), status will be updated.
 * @param int $classid Class id, int $userid user id
 * @return-- display confirmation message
 */
function mentorapprovecourse($userid, $classid) {
    global $DB;
    $hierarchy = new hierarchy();
    $userclass = $DB->get_records('local_user_clclasses', array('userid' => $userid, 'classid' => $classid));
    $returnurl = new moodle_url('/local/courseregistration/mentor.php?current=pending');

    foreach ($userclass as $userclclasses) {

        $publish = new stdClass();
        $publish->id = $userclclasses->id;
        $publish->mentorapproval = 1;
        $out = $DB->update_record('local_user_clclasses', $publish);
    }
    if ($out) {
        $message = get_string('approvedsuccess', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}

/**
 * @method mentorrejectcourse
 * @todo used when mentor rejected the student request( for course enrollment), status will be updated.
 * @param int $courseid Course id, int $userid user id
 * @return-- display confirmation message
 */
function mentorrejectcourse($userid, $courseid) {

    global $DB;
    $hierarchy = new hierarchy();
    $usercourse = $DB->get_records('local_user_clclasses', array('userid' => $userid, 'classid' => $courseid));
    $returnurl = new moodle_url('/local/courseregistration/mentor.php?current=pending');
    foreach ($usercourse as $usercourses) {

        $publish = new stdClass();
        $publish->id = $usercourses->id;
        $publish->mentorapproval = 2;
        $out = $DB->update_record('local_user_clclasses', $publish);
    }
    if ($out) {
        $message = get_string('mentorrejectedcourse', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}

/**
 * @method registrarapprovecourse
 * @todo used when registrar approved the student request( for course enrollment), student will enroll to semester as well as moodle course.
 * @param int $courseid Course id, int $userid user id
 * @return-- display confirmation message
 */
function registrarapprovecourse($userid, $classid) {
    global $DB, $USER;
    $hierarchy = new hierarchy();
    $usercourse = $DB->get_records('local_user_clclasses', array('userid' => $userid, 'classid' => $classid));
    $sqle = "SELECT * FROM {local_clclasses} where id={$classid}";
    $onlinecourse = $DB->get_records_sql($sqle);
    // print_object($onlinecourse);
    // exit;
    $returnurl = new moodle_url('/local/courseregistration/registrar.php?current=pending');
    foreach ($usercourse as $usercourses) {

        $publish = new stdClass();
        $publish->id = $usercourses->id;
        $publish->registrarapproval = 1;
        $approvestudent = $DB->update_record('local_user_clclasses', $publish);

        $publish->semesterid = $usercourses->semesterid;
        $publish->userid = $userid;
        $publish->programid = $usercourses->programid;
        $publish->timecreated = time();
        $publish->curriculumid = 0;
        $publish->usermodified = $USER->id;
        $userfrom = $DB->get_record('user', array('id' => $USER->id));

        $sql = "SELECT * FROM {local_user_semester} where userid={$userid} and semesterid={$usercourses->semesterid} and programid={$usercourses->programid}";
        $existSem = $DB->get_records_sql($sql);
        if (!$existSem)
            $DB->insert_record('local_user_semester', $publish);
        if ($usercourses->ecourseid) {
            $manual = enrol_get_plugin('manual');
            $studentrole = $DB->get_record('role', array('shortname' => 'student'));
            $instance = $DB->get_record('enrol', array('courseid' => $usercourses->ecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
            $enrol = $manual->enrol_user($instance, $userid, $studentrole->id);
            if ($enrol) {
                $message = get_string('approvedsuccess', 'local_courseregistration');
                $message_post_message = message_post_message($userfrom, $userid, $message, FORMAT_HTML);
                $message = get_string('approvedsuccess', 'local_courseregistration', $classid);
                $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
            }
        }
    }
    if ($approvestudent) {
        $message = get_string('approvedsuccess', 'local_courseregistration');
        $message_post_message = message_post_message($userfrom, $userid, $message, FORMAT_HTML);
        $message = get_string('approvedsuccess', 'local_courseregistration', $classid);
        $hierarchy->set_confirmation($message, $returnurl, array('style' => 'notifysuccess'));
    }
}

/**
 * @method registrarrejectcourse
 * @todo used when registrar rejected the student request( for course enrollment), particular student status will be updated.
 * @param int $courseid Course id, int $userid user id
 * @return-- display confirmation message
 */
function registrarrejectcourse($userid, $classid) {

    global $DB, $USER;
    $returnurl = new moodle_url('/local/courseregistration/registrar.php?current=pending');
    $usercourse = $DB->get_records('local_user_clclasses', array('userid' => $userid, 'classid' => $classid));

    foreach ($usercourse as $usercourses) {

        $publish = new stdClass();
        $publish->id = $usercourses->id;
        $publish->registrarapproval = 2;
        $out = $DB->update_record('local_user_clclasses', $publish);
    }
    /* ---start of vijaya--- */
    $conf = new object();
    $hierarchy = new hierarchy();
    $conf->username = $DB->get_field('user', 'username', array('id' => $userid));
    $conf->classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $classid));
    $message = get_string('msg_rej_cls_req', 'local_courseregistration', $conf);
    $userfrom = $DB->get_record('user', array('id' => $USER->id));
    $userto = $DB->get_record('user', array('id' => $userid));
    $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
    /* ---end of vijaya--- */
    $message = get_string('reject_success', 'local_courseregistration', $conf);
    $style = array('style' => 'notifysuccess');
    $hierarchy->set_confirmation($message, $returnurl, $style);
    //return $returnurl;
}

/* function get_userbatch($userid) {
  global $DB, $USER;
  $sql = "SELECT b.name FROM {user} u,{local_batches} b where u.id={$userid} AND u.batchid=b.id";
  $batch = $DB->get_records_sql($sql);
  if ($batch) {
  foreach ($batch as $batches) {
  return $batches->name;
  }
  }
  }

  function get_batch($userid) {
  global $DB, $USER;
  $sql = "SELECT b.name FROM {local_batches} b,{user} u where u.id={$userid} AND u.batchid=b.id";

  $batch = $DB->get_records_sql($sql);

  if ($batch) {
  foreach ($batch as $batches) {

  return $batches->name;
  }
  }
  } */

/**
 * @method get_userdetails
 * @todo To get user fullname
 * @param  int $id user id
 * @return-- string, user fullname
 */
function get_userdetails($id) {
    global $DB;
    $sql = "SELECT u.firstname,u.lastname FROM {user} u where u.id={$id} ";
    $user = $DB->get_records_sql($sql);
    if ($user) {
        foreach ($user as $userlist) {
            return $userlist->firstname . '&nbsp;' . $userlist->lastname;
        }
    }
}

/**
 * @method print_mentortabs
 * @todo used to print mentor tab view
 * @param  string $currenttab is the current tab name
 * @return-- display tab view
 */
function print_mentortabs($currenttab) {
    global $OUTPUT;
    $toprow = array();

    $toprow[] = new tabobject('pending', new moodle_url('/local/courseregistration/mentor.php?current=pending'), get_string('currentpending', 'local_courseregistration'));
    $toprow[] = new tabobject('completed', new moodle_url('/local/courseregistration/mentor.php?current=completed'), get_string('completed', 'local_courseregistration'));
    $toprow[] = new tabobject('info', new moodle_url('/local/courseregistration/info.php'), get_string('info', 'local_courseregistration'));


    echo $OUTPUT->tabtree($toprow, $currenttab);
}

/**
 * @method print_registrationtabs
 * @todo used to print registrar tab view
 * @param  string $currenttab is the current tab name
 * @return-- display tab view
 */
function print_registrationtabs($currenttab) {
    global $OUTPUT;
    $toprow = array();
    $toprow[] = new tabobject('pending', new moodle_url('/local/courseregistration/registrar.php?current=pending'), get_string('currentpending', 'local_courseregistration'));
    $toprow[] = new tabobject('completed', new moodle_url('/local/courseregistration/registrar.php?current=completed'), get_string('completed', 'local_courseregistration'));
    $toprow[] = new tabobject('info', new moodle_url('/local/courseregistration/info.php'), get_string('info', 'local_courseregistration'));
    echo $OUTPUT->tabtree($toprow, $currenttab);
}

/**
 * @method display_curriculum_paths
 * @todo to print curriculum 
 * @param object $record it holds the curriculum data
 * @parm boolean $indicate_depth (used to hold the depth(levels) of currculum)
 * @return-- print curriculum based on depth
 */
function display_curriculum_paths($record, $indicate_depth = true) {
    global $OUTPUT;

    // never indent more than 10 levels as we only have 10 levels of CSS styles
    // and the table format would break anyway if indented too much
    $itemdepth = ($indicate_depth) ? 'depth' . min(4, $record->depth) : 'depth1';
    //print_object($itemdepth);
    // @todo get based on item type or better still, don't use inline styles :-(
    $itemicon = $OUTPUT->pix_url('/i/item');
    // $cssclass = !$record->type ? 'dimmed' : '';
    $out = html_writer::start_tag('span', array('class' => 'hierarchyitem ' . $itemdepth, 'style' => 'background-image: url("' . $itemicon . '")'));
    // $out .="School: &nbsp;&nbsp;";
    $out .= '<span style="font-weight:bold">' . format_string($record->fullname) . '</span>';
    $out .= html_writer::end_tag('span');
    return $out;
}

/**
 * @method get_course_enrolledstatus
 * @todo to get course enroll status(means waiting, approved, rejected...) of a particular user and course
 * @param int $courseid course id, int $userdi user id 
 * @return-- array of status and grade information
 */
function get_course_enrolledstatus($courseid, $userid) {
    global $DB, $CFG;
    $status = 'Not Enrolled';
    $grade = null;
    // student is enrolled for the class, but waiting for the approvals
    $approvals = $DB->get_record_sql("SELECT cls.* FROM {local_user_clclasses} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 0");

    if ($approvals) {
        $status = 'waiting';
    } else if (empty($approvals)) {
        $adddrop_approvals = $DB->get_record_sql("SELECT cls.* FROM {local_course_adddrop} AS ad JOIN {local_clclasses} AS cls ON cls.id = ad.classid AND cls.cobaltcourseid = {$courseid} AND ad.userid = {$userid} AND ad.registrarapproval = 0");
    }

    if ($adddrop_approvals)
        $status = 'waiting';
    else {
        $rejected = $DB->get_record_sql("SELECT cls.* FROM {local_user_clclasses} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 2");
        if ($rejected) {
            $status = 'Rejected';
        }
        // ---------------------------------------------Edited by hema------------------
        // -----------in case requesting for add or drop course through add and drop event
        /* Bug report #323  -  Student>Course Registration>Enroll>Reject- Status
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved- added valid condition to change the status in course registration page and curriculum page
         */ else {
            if (empty($rejected)) {
                $rejected_adddrop = $DB->get_record_sql("SELECT cls.* FROM {local_course_adddrop} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 2");
            }
            if ($rejected_adddrop)
                $status = 'Rejected';
        }
        //--------------------------------------------------------------------------------
        // when registrar approved ,status turn into under progress 
        $enrolled = $DB->get_record_sql("SELECT cls.* FROM {local_user_clclasses} AS uc JOIN {local_clclasses} AS cls ON cls.id = uc.classid AND cls.cobaltcourseid = {$courseid} AND uc.userid = {$userid} AND uc.registrarapproval = 1");
        if (!empty($enrolled)) {
            $status = 'Enrolled (Inprogress)';
            $completed = $DB->get_record_sql("SELECT * FROM {local_user_classgrades} WHERE userid = {$userid} AND classid = {$enrolled->id}");
            if (!empty($completed)) {
                $status = 'Completed';
                $grade = $completed->gradeletter;
            }
        }
    }// end of else

    return array($status, $grade);
}

// end of function

/**
 * @method diplay_plancourse
 * @todo to display courses of particular curriculum plan with valis status
 * @param int $planid curriculum plan id 
 * @return-- array of objects, course list
 */
function diplay_plancourse($planid) {
    global $DB, $USER;
    $users = users::getInstance();
    $sql = "select * FROM {local_curriculum_plancourses} cpc,{local_cobaltcourses} c where cpc.planid={$planid} AND cpc.courseid=c.id";
    $courses = $DB->get_records_sql($sql);

    foreach ($courses as $course) {
        $course->id = $course->courseid;
        list($course->status, $course->grade) = get_course_enrolledstatus($course->id, $USER->id);

        if ($course->status != 'Not Enrolled') {
            $course->semester = $users->get_coursestatus($course->courseid, $USER->id, true);
        }
        if ($course->status == 'Completed')
            $course->status = '<span class="completed_color" style="color:blue;">' . $course->status . '</span>';
        else if ($course->status == 'Not Enrolled')
            $course->status = '<span class="notenrolled_color" style="color:red;">' . $course->status . '</span>';
        else if ($course->status == 'Rejected')
            $course->status = '<span class="notenrolled_color" style="color:red;">' . $course->status . '</span>';
        else if ($course->status == 'waiting')
            $course->status = '<span class="inprogress_color" style="color:orange;">' . ucwords($course->status) . '</span>';
        else
            $course->status = '<span class="inprogress_color" style="color:green;">' . $course->status . '</span>';
    }
    return $courses;
}

/**
 * @method print_studenttabs
 * @todo to print tab view for student
 * @param string $currenttab current tab name
 * @return-- display tab view
 */
function print_studenttabs($currenttab) {
    global $OUTPUT;
    $toprow = array();
    if (isloggedin()) {
        $toprow[] = new tabobject('myapprovalstatus', new moodle_url('/local/courseregistration/approvestatus.php'), get_string('courseapprovalstatus', 'local_courseregistration'));
    }
    $toprow[] = new tabobject('courseregistration', new moodle_url('/local/courseregistration/index.php'), get_string('register', 'local_courseregistration'));

    echo $OUTPUT->tabtree($toprow, $currenttab);
}

/**
 * @method get_class_actions
 * @todo to display action buttons for the calss view
 * @param string $plaugin plugin name,int $page page number, int $id
 * @param int $visible to make active or inactive
 * @return-- action buttons
 */
function get_class_actions($plugin, $page, $id, $visible) {
    global $DB, $USER, $OUTPUT;

    $buttons = array();
    $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'delete' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/delete'), 'alt' => get_string('delete'), 'class' => 'iconsmall')));
    $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'flag' => 1, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));

    if ($visible) {
        $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'visible' => $visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/hide'), 'alt' => get_string('hide'), 'class' => 'iconsmall')));
    } else {
        $buttons[] = html_writer::link(new moodle_url('/local/' . $plugin . '/' . $page . '.php', array('id' => $id, 'visible' => $visible, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/show'), 'alt' => get_string('show'), 'class' => 'iconsmall')));
    }
    return implode('', $buttons);
}

/**
 * @method get_class_listing
 * @todo to get class list
 * @param string $sort holds field name used in sorting
 * @param string $dir it holds the soeting order, int $recordsperpage
 * @param string $search 
 * @param string $extraparams it holds extra values
 * @param string $extraselect it holds the extra condition
 * @return-- array of objects(class list)
 */
function get_class_listing($sort = 'lastaccess', $dir = 'ASC', $page = 0, $recordsperpage = 10, $search = '', $firstinitial = '', $lastinitial = '', $extraselect = '', array $extraparams = null, $extracontext = null) {
    global $DB, $CFG, $USER;

    $fullname = $DB->sql_fullname();
    $select = "c.visible <> 0 ";
    $params = array('guestid' => $CFG->siteguest);
    if (!empty($search)) {
        $search = trim($search);
        $select .= " AND $search";
    }
    if ($extraselect) {
        $select .= " AND $extraselect";
        $params = $params + (array) $extraparams;
    }
    if (sizeof($params) == 1)
        $params = '';
    else
        $params = $params . ',';

    if ($sort) {
        $sort = " ORDER BY $sort $dir";
    }
    $extrafields = '';

    if (isloggedin()) {

        $sql = " JOIN {local_semester} sem ON lc.semesterid=sem.id JOIN {local_school_semester} lss ON lss.semesterid=sem.id";
        $where = " AND lc.schoolid IN (select u.schoolid from {local_userdata} u where u.userid={$USER->id}) ";
    }

    return $DB->get_records_sql("SELECT DISTINCT(c.id),c.*,lc.semesterid $extrafields
                                       FROM {local_cobaltcourses} c JOIN {local_clclasses} lc ON lc.cobaltcourseid=c.id $sql
                                      WHERE $select $where
                                      $sort $params LIMIT $page,$recordsperpage");
}

/**
 * @method get_classinst
 * @todo to get class instructor  information
 * @param int $classid class id
 * @return-- array of instructor information
 */
function get_classinst($classid) {
    global $DB, $CFG;

    $instructor=$DB->get_field('local_clclasses','instructor',array('id'=>$classid ,'visible'=>1 ));
    if($instructor){
     $instructorlist =explode(',',$instructor);
      foreach($instructorlist as $ins){
           $sql = "select u.firstname,u.id,u.lastname  from {user} as u where u.id =$ins";
            $inslist[]= $DB->get_record_sql($sql);
      }
     
    
    }
    //$sql = "select u.firstname,u.id,u.lastname FROM {local_scheduleclass} as lsc,{user} as u where lsc.instructorid=u.id AND lsc.classid={$classid}";
    //
    //$instructorlists = $DB->get_records_sql($sql);
    $data = array();
    if (isset( $inslist)) {
        $count = 0;
        foreach ( $inslist as $instructorr) {
            if ($count != 0) {
                $char = ',';
            } else {
                $char = '';
            }
            $data[] = $char . html_writer::link(new moodle_url('/local/cobaltcourses/view_author.php', array('id' => $instructorr->id, 'sesskey' => sesskey())), $instructorr->firstname . '&nbsp' . $instructorr->lastname);
            $count++;
        }
    } else
        $data[] = 'Not Assigned';

    return $data;
}

/**
 * @method get_classcount
 * @todo to get class count based on condition used for pagination
 * @param string $extraselect (it holds some sql condition )
 * @return-- int, class count value
 */
function get_classcount($extraselect = '', array $extraparams = null) {
    global $DB, $CFG;

    if ($extraselect) {
        $select = "  $extraselect";
    }
    $users = $DB->get_records_sql("SELECT DISTINCT(c.id)
                                       FROM {local_cobaltcourses} c,{local_clclasses} lc
                                      WHERE c.id=lc.cobaltcourseid and $select");
    return sizeof($users);
}

/**
 * @method student_semesters
 * @todo to get student active semester information
 * @param int $userid student id
 * @param string $from used to hold table name(to indicate from reagistration , add/drop event)
 * @return-- array, student information
 */
function student_semesters($userid, $from = null) {
    global $CFG, $DB;
    if ($from == "adddrop")
        $table = '{local_course_adddrop}';
    else
        $table = '{local_user_clclasses}';

    if (empty($from))
        $table = '{local_user_semester}';
    //$sems = array();
    // $sems[NULL] = "---Select---";
    $result = false;
    $sql = "SELECT distinct(ls.id),ls.fullname 
	      FROM $table AS us,{local_semester} AS ls 
		  WHERE us.userid=? AND 
		  us.semesterid=ls.id AND 
		  ls.visible=1 ";
    $sems = $DB->get_records_sql_menu($sql, array('userid' => $userid));
    if (!empty($sems))
        $result = array(null => 'Select') + $sems;
    return $result;
}

class classfiltering {

    var $_fields;
    var $_addform;
    var $_activeform;

    /**
     * Contructor
     * @param array array of visible user fields
     * @param string base url used for submission/return, null if the same of current page
     * @param array extra page parameters
     */
    function classfiltering($fieldnames = null, $baseurl = null, $extraparams = null) {
        global $SESSION;

        if (!isset($SESSION->filtering)) {
            $SESSION->filtering = array();
        }

        if (empty($fieldnames)) {
            $fieldnames = array('semester' => 0, 'assignedschool' => 0, 'departments' => 0);
        }

        $this->_fields = array();

        foreach ($fieldnames as $fieldname => $advanced) {
            if ($field = $this->get_field($fieldname, $advanced)) {
                $this->_fields[$fieldname] = $field;
            }
        }
        // fist the new filter form
        $this->_addform = new class_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($adddata = $this->_addform->get_data()) {
            foreach ($this->_fields as $fname => $field) {
                $data = $field->check_data($adddata);
                if ($data === false) {
                    continue; // nothing new
                }
                if (!array_key_exists($fname, $SESSION->filtering)) {
                    $SESSION->filtering[$fname] = array();
                }
                $SESSION->filtering[$fname][] = $data;
            }
            // clear the form
            $_POST = array();
            $this->_addform = new class_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        }
        // now the active filters
        $this->_activeform = new clactive_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        if ($adddata = $this->_activeform->get_data()) {
            if (!empty($adddata->removeall)) {
                $SESSION->filtering = array();
            } else if (!empty($adddata->removeselected) and ! empty($adddata->filter)) {
                foreach ($adddata->filter as $fname => $instances) {
                    foreach ($instances as $i => $val) {
                        if (empty($val)) {
                            continue;
                        }
                        unset($SESSION->filtering[$fname][$i]);
                    }
                    if (empty($SESSION->filtering[$fname])) {
                        unset($SESSION->filtering[$fname]);
                    }
                }
            }
            // clear+reload the form
            $_POST = array();
            $this->_activeform = new clactive_filter_form($baseurl, array('fields' => $this->_fields, 'extraparams' => $extraparams));
        }
    }

    /**
     * Creates known user filter if present
     * @param string $fieldname
     * @param boolean $advanced
     * @return object filter
     */
    function get_field($fieldname, $advanced) {
        global $USER, $CFG, $DB, $SITE;

        switch ($fieldname) {

            //  case 'realname':    return new user_filter_text('realname', get_string('fullnameuser'), $advanced, $DB->sql_fullname());
            case 'semester': return new semester_filter('semester', get_string('semester', 'local_semesters'), $advanced);
            case 'assignedschool': return new class_filter_school('assignedschool', get_string('schoolid', 'local_collegestructure'), $advanced);
            case 'departments': return new department_filter('departments', get_string('departments', 'local_clclasses'), $advanced);


            default: return null;
        }
    }

    /**
     * Returns sql where statement based on active user filters
     * @param string $extra sql
     * @param array named params (recommended prefix ex)
     * @return array sql string and $params
     */
    function get_sql_filter($extra = '', array $params = null) {
        global $SESSION;

        $sqls = array();
        if ($extra != '') {
            $sqls[] = $extra;
        }
        $params = (array) $params;

        if (!empty($SESSION->filtering)) {
            foreach ($SESSION->filtering as $fname => $datas) {
                if (!array_key_exists($fname, $this->_fields)) {
                    continue; // filter not used
                }
                $field = $this->_fields[$fname];
                foreach ($datas as $i => $data) {
                    list($s, $p) = $field->get_sql_filter($data);
                    $sqls[] = $s;
                    $params = $params + $p;
                }
            }
        }

        if (empty($sqls)) {
            return array('', array());
        } else {
            $sqls = implode(' AND ', $sqls);

            return array($sqls, $params);
        }
    }

    /**
     * Print the add filter form.
     */
    function display_add() {
        $this->_addform->display();
    }

    /**
     * Print the active filter form.
     */
    function display_active() {
        $this->_activeform->display();
    }

}

/* class filter ends */

/**
 * @method count_course_approval_from_registrar
 * @todo to get course approval count which is approved by registrar
 * @param int $userid student id
 * @return- int count of course approval
 */
function count_course_approval_from_registrar($id) {
    global $DB, $USER;
    $user = $DB->get_record('user', array('id' => $id));
    $courseapprov = $DB->get_records_sql("select * from {local_request_idcard} where studentid = $id and reg_approval = 1 or reg_approval = 2 and regapproved_date>$user->lastlogin");
    $courseapprovalcount = sizeof($courseapprov);
    if ($courseapprovalcount == 0) {
        return 0;
    } else {
        return $courseapprovalcount;
    }
}

/**
 * @method prerequisite_courses(
 * @todo to get course prerequisties of a particular course
 * @param int $courseid course id
 * @return- string, course fullname
 */
function prerequisite_courses($id) {
    global $DB, $USER;
    $course = ' ';
    $sql = "SELECT c.id,c.fullname FROM {local_course_prerequisite} p ,{local_cobaltcourses} c WHERE p.courseid={$id} AND p.precourseid=c.id ";
    $query = $DB->get_records_sql($sql);
    if (!empty($query)) {
        foreach ($query as $cou) {
            $course.=$cou->fullname;
            $course.='<br/>';
        }
    } else {
        $course.='No Courses';
    }
    return $course;
}

/**
 * @method equivalent_courses
 * @todo to get course equivalent of a particular course
 * @param int $courseid course id
 * @return- string, course fullname
 */
function equivalent_courses($id) {
    global $DB, $USER;
    $course = ' ';
    $sql = "SELECT c.id,c.fullname FROM {local_course_equivalent} e,{local_cobaltcourses} c WHERE e.courseid={$id} AND e.equivalentcourseid=c.id ";
    $query = $DB->get_records_sql($sql);
    if (!empty($query)) {
        foreach ($query as $cou) {
            $course.=$cou->fullname;
            $course.='<br/>';
        }
    } else {
        $course.='No Courses';
    }
    return $course;
}

/**
 * @method get_courseregistrationstatus
 * @todo to get course registration status of a student based mentor,registrar and student status
 * @param int $approveStatus student status, int $mentorStatus mentor status,int $registratStatus registrar status  
 * @return- string, status message
 */
function get_courseregistrationstatus($approveStatus, $mentorStatus, $registratStatus) {

    if ($approveStatus == 1 AND $mentorStatus == 0)
        $line = get_string('waitmentorapproval', 'local_courseregistration');
    if ($approveStatus == 1 AND $mentorStatus == 1)
        $line = get_string('waitregistrarapproval', 'local_courseregistration');
    if ($approveStatus == 1 AND $mentorStatus == 2)
        $line = get_string('mregapproval', 'local_courseregistration');

    return $line;
}

/**
 * @method get_courseregistration_message
 * @todo to get course registration status of a student based mentor,registrar and student status
 * @param int $approveStatus student status, int $mentorStatus mentor status,int $registratStatus registrar status  
 * @return- string, status message
 */
function get_courseregistration_message($approveStatus, $mentorStatus, $registratStatus) {

    if ($registratStatus == 0 && $mentorStatus == 1)
        $line = get_string('mentorapproved_withoutregistrarapproval', 'local_courseregistration');
    elseif ($registratStatus == 0 && $mentorStatus == 2)
        $line = get_string('mentorrejected_withoutregistrarapproval', 'local_courseregistration');
    elseif ($registratStatus == 1 && $mentorStatus == 0)
        $line = get_string('registrarapproved_withoutmentor', 'local_courseregistration');
    elseif ($registratStatus == 1 && $mentorStatus == 1)
        $line = get_string('registrarmentor_approved', 'local_courseregistration');
    elseif ($registratStatus == 1 && $mentorStatus == 2)
        $line = get_string('registraraprroved_mentorrejection', 'local_courseregistration');
    elseif ($registratStatus == 2 && $mentorStatus == 0)
        $line = get_string('registrarrejected_withoutmentor', 'local_courseregistration');
    elseif ($registratStatus == 2 && $mentorStatus == 1)
        $line = get_string('mentorapproved_registrarrejected', 'local_courseregistration');
    else
        $line = get_string('mentorregistrar_rejected', 'local_courseregistration');

    return $line;
}

/**
 * @method previous_semsof_instructor
 * @todo to get previous semester instructor based on dates
 * @return- array,instructor previous semester information
 */
function previous_semsof_instructor() {
    global $CFG, $DB, $USER;
    $today = date('Y-m-d');
    $sem = array();
    $sql = "SELECT ls.id,s.id as semid,s.fullname FROM {local_scheduleclass} ls,{local_semester} s WHERE ls.instructorid={$USER->id} AND ls.semesterid=s.id
 AND DATE(FROM_UNIXTIME(s.enddate))<={$today} ";
    $semesters = $DB->get_records_sql($sql);
    if (empty($semesters)) {
        $sem = 0;
    } else {
        foreach ($semesters as $semester) {
            $sem[$semester->semid] = $semester->fullname;
        }
    }
    return $sem;
}

/**
 * @method previous_class_instructor
 * @todo to get class instructor of particular instructor , semester and loggedin userid 
 * @param int $semid semesterid
 * @return- array,instructor previous semester information
 */
function previous_class_instructor($semid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT lc.id,lc.fullanme FROM {local_scheduleclass} ls,{local_clclasses} lc WHERE WHERE ls.semesterid=? AND ls.instructorid=? AND  ls.classid=lc.id";
    $param = array($semid, $USER->id);
    $cls = $DB->get_records_sql_menu($sql, $param);
    return $cls;
}

/**
 * @method student_progress_tabs
 * @todo to print student progressbar tab view
 * @param string $currentab tab name
 * @param int $id (used in link)
 * @return- display tab view
 */
function student_progress_tabs($currenttab, $id = 0) {
    global $OUTPUT, $DB;
    $toprow = array();
    /*
     * ###Bugreport #105- My students
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) Added ID to the present student list URL
     */
    $toprow[] = new tabobject('present_student_list', new moodle_url('/local/courseregistration/mystudents.php', array('id' => $id)), get_string('present_student_list', 'local_courseregistration'));
    $toprow[] = new tabobject('previous_student_list', new moodle_url('/local/courseregistration/previousstudents.php', array('cid' => $id)), get_string('previous_student_list', 'local_courseregistration'));
    echo $OUTPUT->tabtree($toprow, $currenttab);
}

/**
 * @method offline_progress
 * @todo to display offline class progress bar
 * @param int $semid semesterid, int $userid, int $calssid
 * @return- display progress bar
 */
function offline_progress($userid, $semid, $classid) {
    global $CFG, $DB;
    $width = 20;
    $class = $DB->get_record('local_clclasses', array('id' => $classid));
    $content = '';

    $sql = "SELECT * FROM {local_class_completion} where semesterid=$semid AND classid=$classid AND schoolid=$class->schoolid and source='offline' group by examid";
    $query = $DB->get_records_sql($sql);
    $totalcount = count($query);
    if (empty($query)) {
        $content.="<div style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'></div>";
        $content.="<span style='float: right;    
    margin-top: -18px;    
    width: 50px;'>0%</span>";
    } else {
        $gradesql = "SELECT * FROM {local_user_examgrades} as exam JOIN {local_class_completion} as cc ON cc.examid=exam.examid  WHERE exam.userid={$userid} AND exam.classid={$classid} AND exam.semesterid={$semid}";
        $gradeqry = $DB->get_records_sql($gradesql);
        $count = count($gradeqry);
        $result = ($count / $totalcount) * 100;
        $newwid = 99 - $result;
        $content.="<div style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'><div style='width:$result% !important;max-width:100%;background:green;min-height:15px;border-radius:5px;'></div><div style='width:$newwid% !important;'></div></div>";
        if ($result > 0) {
            $result > 100 ? $result = 100 : $result;
            $content.="<span style='float: right;margin-top: -18px;width: 53px;'><b>" . $result . "%" . "</b></span>";
        } else {
            $content.="<span style='float: right;margin-top: -18px;width: 53px;'>" . $result . "%" . "</span>";
        }
    }
    return $content;
}

/**
 * @method online_progress
 * @todo to display online class progress bar
 * @param int $semid semesterid, int $userid, int $calssid
 * @return- print progress bar
 */
function online_progress($userid, $semid, $classid) {
    global $CFG, $DB;
    $class = $DB->get_record('local_clclasses', array('id' => $classid));
    //echo  $sql="SELECT lse.*,le.examtype as examname FROM {local_class_completion} cmp,{local_scheduledexams} lse,{local_examtypes} le WHERE lse.semesterid={$semid} AND lse.classid={$classid} AND lse.schoolid={$class->schoolid} AND cmp.examid=lse.id
    // AND lse.examtype=le.id";
    // $query=$DB->get_records_sql($sql);
    // $offline_sql = $sql="SELECT lse.*,le.examtype as examname FROM {local_user_examgrades} cmp,{local_scheduledexams} lse,{local_examtypes} le WHERE lse.semesterid={$semid} AND lse.classid={$classid} AND lse.schoolid={$class->schoolid} AND cmp.examid=lse.id
    // AND lse.examtype=le.id";
    // $query2 = $DB->get_records_sql($offline_sql);
    // echo 'totalcount'.$totalcount=count($query)+count($query2);

    $sql = "SELECT * FROM {local_class_completion} where semesterid=$semid AND classid=$classid AND schoolid=$class->schoolid group by examid ";
    $query = $DB->get_records_sql($sql);
    $totalcount = count($query);
    if ($totalcount == 0) {
        $content.="<div style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'></div>";
        $content.="<span style='float: right;  margin-top: -18px; width: 50px;'>0%</span>";
    } else {
        $gradesql = "SELECT lcc.*,g.id as gid,g.itemname,g.itemmodule,gg.finalgrade FROM {local_class_completion} lcc,{grade_items} g,{grade_grades} gg WHERE lcc.classid={$classid} AND lcc.semesterid={$semid} AND lcc.source='online' AND lcc.examid=g.id AND g.id=gg.itemid AND gg.userid={$userid}";
        $gradeqry = $DB->get_records_sql($gradesql);
        $offlinegrade = $DB->get_records_sql("SELECT ueg.* FROM {local_user_examgrades} ueg
                  WHERE ueg.classid={$classid}
		 AND ueg.semesterid={$semid} AND ueg.source='offline' AND ueg.userid={$userid}");
        $count = count($gradeqry);
        $offlinecount = count($offlinegrade);

        $count = $count + $offlinecount;
        $result = ($count / $totalcount) * 100;
        $newwid = 99 - $result;
        $content.="<div style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'><div style='width:$result% !important;background:green;min-height:15px;border-radius:5px;'></div><div style='width:$newwid% !important;'></div></div>";
        if ($result > 0) {
            $content.="<span style='float: right;margin-top: -18px;width: 50px;'><b>" . $result . "%" . "</b></span>";
        } else {
            $content.="<span style='float: right;margin-top: -18px;width: 50px;'>" . $result . "%" . "</span>";
        }
    }
    return $content;
}

/**
 * @method get_my_progress
 * @todo to print particular student progress bar
 * @param int $semid semesterid semesterid , int $userid userid, int $cid curriculum id
 * @return- print progress bar
 */
function get_my_progress($userid, $semid = 0, $cid = 0) {
    global $DB, $CFG;
    if ($semid) {
        $cclclasses = $DB->get_records('local_user_clclasses', array('userid' => $userid, 'semesterid' => $semid, 'registrarapproval' => 1));
        $totalcount = sizeof($cclclasses);
    } else if ($cid) {
        $cclclasses = array();
        $curcourses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $cid));
        $totalcount = sizeof($curcourses);
        foreach ($curcourses as $curcourse) {
            $cclclasses[] = $DB->get_record_sql("SELECT luc.* FROM {local_user_clclasses} luc, {local_clclasses} lc
			WHERE luc.classid = lc.id AND lc.cobaltcourseid = {$curcourse->courseid}
			AND luc.userid = {$userid} AND luc.registrarapproval = 1");
        }
        $cclclasses = array_filter($cclclasses);
    }
    $count = 0;
    foreach ($cclclasses as $cclass) {
        $class = $DB->get_record('local_clclasses', array('id' => $cclass->classid));
        $params = array('userid' => $userid, 'classid' => $cclass->classid);
        if ($semid) {
            $params['semesterid'] = $semid;
        }
        if ($DB->record_exists('local_user_classgrades', $params)) {
            $count = $count + 1;
        }
    }
    //$completed_count = count($countarray);
    $done = ($count / $totalcount) * 100;
    $done = round($done);
    //$done = 22;
    $notdone = 99 - $done;
    $out = '<div style="float: left; width: 200px; height: 15px; border: 1px solid lightgray;border-radius:10px;">
		    <div style="width: ' . $done . '%;background-color: green;float: left; height: 15px;border-radius:10px;></div>
		    <div style="width: ' . $notdone . '%; float: right;></div>    
		</div>';
    $out .= '&nbsp;&nbsp;' . $done . ' %';
    return $out;
}

/**
 * @method student_currculum_progress
 * @todo to print student curriculum progress bar
 * @param int $curid curriculum id, int $schoid schoolid
 * @return- print progress bar
 */
function student_currculum_progress($curid, $schoid) {

    global $CFG, $DB, $USER;
    $myuser = users::getInstance();
    $courses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $curid));
    $course_count = sizeof($courses);
    $enrolled = 0;
    $completed = 0;
    foreach ($courses as $course) {
        $status = $myuser->get_coursestatus($course->courseid, $USER->id);
        if ($status != 'Not Enrolled') {
            $enrolled++;
            if ($status != 'Enrolled (Inprogress)')
                $completed++;
        }
    }

    $curculum = $DB->get_record('local_curriculum', array('id' => $curid));
    $program = $DB->get_record('local_program', array('id' => $curculum->programid));
    $grades = $DB->get_record('local_cobalt_gpasettings', array('schoolid' => $schoid, 'sub_entityid' => $program->programlevel));
    $cgpa = $DB->get_field('local_graduation', 'finalcgpa', array('userid' => $USER->id, 'schoolid' => $schoid, 'curriculumid' => $curid));
    if (empty($cgpa)) {
        $cgpa = 'Not Graded';
    }
    $totalcredit = $DB->get_field('local_level_settings', 'mincredithours', array('schoolid' => $schoid, 'levelid' => $curculum->programid, 'level' => 'PL'));
    if (empty($totalcredit)) {
        $totalcredit = 'Not Applicable';
    }
    $usercredits = "SELECT sum(totcredithours) as total FROM {local_user_sem_details} WHERE userid={$USER->id} AND curriculumid={$curid}";
    $usercredit = $DB->get_record_sql($usercredits);
    $gpasql = "SELECT * FROM {local_user_sem_details} WHERE userid={$USER->id} AND curriculumid={$curid}";
    $gpaqry = $DB->get_records_sql($gpasql);
    $gpacount = sizeof($gpaqry);
    if ($completed < $enrolled || $enrolled == 0) {
        $one = "<img src='pix/unsuccess.png'>";
    } else {
        $one = "<img src='pix/success.png'>";
    }
    if ($totalcredit <= $usercredit->total) {
        $two = "<img src='pix/success.png'>";
    } else {
        $two = "<img src='pix/unsuccess.png'>";
    }
    /*
     * ###Bugreport#181-Curriculum
     * @author Naveen Kumar<naveen@eabyas.in>
     * (Resolved) checking both empty and less than or equal to cgpa 
     */
    if (round($grades->cgpa) <= $cgpa && !empty($grades->cgpa)) {
        $three = "<img src='pix/success.png'>";
    } else {
        $three = "<img src='pix/unsuccess.png'>";
    }
    $table = new html_table();
    $table->align = array('left', 'left', 'left', 'left');
    $table->size = array('20%', '36%', '32%', '12%');
    $table->width = '100%';
    $cell = new html_table_cell();
    $cell->text = 'Graduation will be given if following conditions are met<br> ' . $one . ' Need to complete all enrolled courses in curriculum .<br> ' . $two . ' Credithours should be greater than or equal to required credit hours.<br> 
		' . $three . ' CGPA achieved is equal or greater than to required CGPA.<br>  ';
    $cell->colspan = 3;
    $table->data[] = array('Program Name', '<b>' . $program->fullname . '</b>', 'Total Number of Courses', '<b>' . $course_count . '</b>');
    $table->data[] = array('Curriculum Name', '<b>' . $curculum->fullname . '</b>', 'Total Number of Enroled Courses', '<b>' . $enrolled . '</b>');
    $table->data[] = array('Required Credithours', '<b>' . $totalcredit . '</b>', 'Total Number of Completed Courses', '<b>' . $completed . '</b>');
    $table->data[] = array('Credithours Achieved', '<b>' . $usercredit->total . '</b>', 'Required CGPA', '<b>' . round($grades->cgpa) . '</b>');

    $table->data[] = array('Progress', get_my_progress($USER->id, 0, $curid), 'CGPA Achieved', '<b>' . round($cgpa) . '</b>');
    $table->data[] = array('Description', $cell);

    echo html_writer::table($table);
}

function stu_online_ins_progress($userid, $semid, $classid) {
    global $CFG, $DB;
    $class = $DB->get_record('local_clclasses', array('id' => $classid));

    $sql1 = "SELECT * FROM {local_class_completion} where semesterid=$semid AND classid=$classid AND schoolid=$class->schoolid group by examid";
    $query = $DB->get_records_sql($sql1);
    $totalcount = count($query);

    $newwidth = 75 / $totalcount;
    $content = '';
    if (empty($query)) {
        $content.="<div class='button tipped' data-title='No Exams For This Class' data-tipper-options='{'direction':'bottom'}' style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'></div>";
    } else {
        //$content.="<div style='width:75%;border:1px solid lightgray;min-height:15px;border-radius:10px;'>";
        foreach ($query as $exam) {

            /* Checking online exam or not */
            if ($exam->source == 'online') {
                $result = $DB->get_field('grade_grades', 'finalgrade', array('userid' => $userid, 'itemid' => $exam->examid));

                if (empty($result)) {
                    $content.="<span class='button tipped' data-title='Your Score is 0' data-tipper-options='{'direction':'bottom'}' style='width:$newwidth% !important;background:red;min-height:15px;border-radius:5px;float:left;'></span>";
                } else {
                    $content.="<span class='button tipped' data-title='$result' data-tipper-options='{'direction':'bottom'}' style='width:$newwidth% !important;background:green;min-height:15px;border-radius:5px;float:left;'></span>";
                }
            }
            /**/ else {

                $result = $DB->get_field('local_user_examgrades', 'finalgrade', array('userid' => $userid, 'classid' => $classid, 'examid' => $exam->examid));
                if (empty($result)) {
                    $content.="<span class='button tipped' data-title='your score is 0' data-tipper-options='{'direction':'bottom'}' style='width:$newwidth% !important;background:red;min-height:15px;border-radius:5px;float:left;'></span>";
                } else {

                    $content.="<span class='button tipped' data-title='$result' data-tipper-options='{'direction':'bottom'}' style='width:$newwidth% !important;background:green;min-height:15px;border-radius:5px;float:left;'></span>";
                }
            }
        }// end of foreach
    }// end of else
    return $content;
}

function count_ins_progress($userid, $semid, $classid) {
    global $CFG, $DB;
    $max = 0;
    $class = $DB->get_record('local_clclasses', array('id' => $classid));
    $sql1 = "SELECT * ,(if(source='offline',(select grademax from {local_scheduledexams} where  id=cc.examid),
   (select grademax from {grade_items} where  id=cc.examid))) as maxtotal
   FROM mdl_local_class_completion as cc where cc.semesterid=$semid AND cc.classid=$classid AND cc.schoolid=$class->schoolid group  by cc.examid";

    $query1 = $DB->get_records_sql($sql1);
    //$total=$query1->maxtotal;

    $total = 0;
    //print_object($query1);
    foreach ($query1 as $que) {
        $total = $total + $que->maxtotal;
    }

    if (empty($query1)) {
        $per = 0;
    } else {
        foreach ($query1 as $exam) {
            //print_object($query1);
            if ($exam->source == 'online') {
                $result = $DB->get_field('grade_grades', 'finalgrade', array('userid' => $userid, 'itemid' => $exam->examid));
                if (!empty($result))
                    $max = $max + $result;
            }
            else {
                $result = $DB->get_field('local_user_examgrades', 'finalgrade', array('userid' => $userid, 'classid' => $classid, 'examid' => $exam->examid));
                if (!empty($result))
                    $max = $max + $result;
            }
        }// end of foreach
        $per = round(($max / $total) * 100);
    }// end of else 

    return $per . '%';
}

/**
 * @method retrieve_listofclclasses_ofcourse()
 * @todo used to display list of clclasses of particular course and current semester(heplful to enroll class)
 * @param $cid (holds courseid), $semid(holds semesterid)
 * @return-- table format output
 */
function retrieve_listofclclasses_ofcourse($cid, $semid, $schoolid = 0) {
    global $CFG, $DB, $USER;
    $str = '';
    $precourses = prerequisite_courses($cid);
    $equcourses = equivalent_courses($cid);
    $data = array();
    if ($schoolid > 0)
        $schoolcondition = " c.schoolid='{$schoolid}' AND ";
    else
        $schoolcondition = "";

    $query = "SELECT c.*,cc.fullname as coursename,cc.credithours credithours,cc.shortname courseshortname           

             FROM {local_clclasses} c,
                  {local_cobaltcourses} cc 
             where c.semesterid={$semid} AND c.cobaltcourseid={$cid}  AND {$schoolcondition} c.cobaltcourseid=cc.id";

    $classList = $DB->get_records_sql($query);
    // if(empty($classList)) {
    // echo "There are no clclasses in this course to enroll";
    // }
    $listof_class = array();
    foreach ($classList as $clid) {

        $listof_class[] = number_format($clid->id);
    }

    $listcl = json_encode($listof_class);
    $listclass = str_replace('"', '', $listcl);
    if (empty($classList)) {
        $str.='Presently no clclasses for this course is offered. Please check back ';
    } else {
        foreach ($classList as $list) {
            $classtype = $list->online == 1 ? 'Online' : 'Offline';
            //if ($list->classroom) {
            //    $classroom = array();
            //    $classroom = explode(':', $list->classroom);
            //    if (!is_null($classroom)) {
            //        $classroomName = $classroom[0];
            //        $floor = $classroom[1];
            //        $building = $classroom[2];
            //    }
            //} else {
            //    $classroomName = get_string('not_defined', 'local_request');
            //    $floor = get_string('not_defined', 'local_request');
            //    $building = get_string('not_defined', 'local_request');
            //}
            //if ($list->timing)
            //    $timings = $list->timing;
            //else
            //    $timings = get_string('not_defined', 'local_request');
            //if ($list->availableweekdays)
            //    $availableweekdays = $list->availableweekdays;
            //else
            //    $availableweekdays = '';
    
             // if registrarapproval status =5 means, registrar or admin unenrolled student from class , so student can enroll other class of same course
            $sql = "SELECT * FROM {local_clclasses} c,{local_user_clclasses} uc where c.cobaltcourseid={$cid} AND uc.userid={$USER->id} AND c.id=uc.classid AND uc.semesterid={$semid} and uc.registrarapproval != 5";

            $exist = $DB->record_exists_sql($sql);
            if (empty($exist)) {
                $sql = "SELECT * FROM {local_clclasses} c,{local_course_adddrop} ad where c.cobaltcourseid={$cid} AND ad.userid={$USER->id} AND c.id=ad.classid AND ad.semesterid={$semid}";
                $exist = $DB->record_exists_sql($sql);
            }
            $enrollcount = $DB->count_records('local_user_clclasses', array('classid' => $list->id, 'semesterid' => $semid, 'registrarapproval' => 1));
            $existingseats = $list->classlimit - $enrollcount;
            if ($enrollcount > $list->classlimit) {
                $existingseats = 0;
            }
            $str.='<table id="classenroll" cellspacing="0" cellpadding="3" border="0" style="font-size:12px;border:1px solid #cccccc;line-height:24px">
        <tbody><tr>';
            $str .= '<td align="left" style="font-size:12px;background:#dddddd !important;" colspan="2">
                         <b><span style="color:#0088CC;text-decoration:none;cursor:pointer;">' . $list->courseshortname . ':&nbsp;</span></b> <b>' . $list->coursename . '</b>
                             <span style="color:#333333;" id="spanhonors"></span> 
             </td>';
            $str .='<td align="right" style="font-size:12px;background:#dddddd !important;">';
            $str .='<table id="inner_curtable" cellspacing="0" cellpadding="0" >
                    <tbody>
        <tr>
            <td align="left"> </td>
            <td align="right"> <span style="font-weight:bold;color:black;">' . strtoupper(get_string('class', 'local_clclasses')) . ':</span><span style="font-weight:bold;margin-right:20px;color:#0088CC;">&nbsp;' . $list->fullname . '</span>';
            $today = date('Y-m-d');
            $events = "SELECT ea.eventtypeid FROM {local_event_activities} ea where ea.semesterid={$semid} AND ea.eventtypeid IN(2,3) AND '{$today}' BETWEEN from_unixtime( startdate,'%Y-%m-%d' ) AND from_unixtime( enddate,'%Y-%m-%d' ) ";

            $eventid = $DB->get_record_sql($events);
            if ($eventid->eventtypeid == 3)
                $existclass = $DB->record_exists('local_course_adddrop', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));
            else
                $existclass = $DB->record_exists('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid));

            if ($existclass){
                $unenrolled=$DB->record_exists('local_user_clclasses', array('userid' => $USER->id, 'classid' => $list->id, 'semesterid' => $semid,'registrarapproval'=>5));
                if($unenrolled)
                $str .= get_string('unenolled', 'local_adddrop');
                else
                $str .= get_string('waitingapproval', 'local_courseregistration');
            }
            else if (($exist) || ($list->classlimit == 0) || ($existingseats == 0))
                $str .="Cannot Enroll";
            else {


                $str .='<span id="result' . $list->id . '"><a  onclick ="enrollclass_ajax(' . $list->id . ',' . $semid . ',' . $cid . ',' . $list->schoolid . ',' . $listclass . ')"   >  <button value="39543" title="Enroll to class." class="enrollme" type="button" id="enroll"  style="height: 30px;" ><span class="enroll-text">' . get_string('enroll', 'local_cobaltcourses') . '</span></button> </a></span>';
            }
            $str.='</td></tr></tbody></table></td> </tr>';

            $str .=' <tr>
                 <td valign="top" align="right" style="padding-left:20px;">';
            $str .='<table cellpadding="1" border="0">
                      <tbody>
                            <tr id="instucttr">
				<td><b>' . get_string('instructor', 'local_cobaltcourses') . ':&nbsp;</b></td>
				<td>';
            $instructor = array();
            $instructor[] = get_classinst($list->id);

            $str .=implode(' ', $instructor[0]);

            $str .='</td>
		            </tr>
				
                              <tr>
                                  <td style="text-align:right">
                                      <b>Type:</b>
                                      </td><td>';

            $str .=$classtype;
            $str .='</td>
                              </tr>
                               </tbody></table>';

            $str .='</td>
                                        <td valign="top" align="left" style="width:155px;">
                                            <table cellpadding="1" border="0">
                                                <tbody><tr id="tr1">
					<td><b>' . get_string('credithours', 'local_cobaltcourses') . ':&nbsp;</b></td>
					<td>' . $list->credithours . '</td>
				    </tr>
				
                                                <tr>
                                                    <td align="right"><b>' . get_string('max_seats', 'local_courseregistration') . ':</b></td><td>' . $list->classlimit . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="right"><b>' . get_string('left_seats', 'local_courseregistration') . ':</b></td><td>' . $existingseats . '</td>
                                                </tr>
                                             
                                            </tbody></table>
                                        </td> ';
            $str .=' <td valign="top" align="right">
                                            <div>';
					  
					
                         $str .= table_multiplescheduled_view( $list->id);
				   $str .= '</div>
			           </td>
                                    </tr>';
            $str .=' <tr>
                                        <td valign="top" align="left" style="padding-left:20px;" colspan="3">
                                            <table  cellspacing="4" cellpadding="0" border="0" style="width:100%;">
                                                <tbody>';


            $str .=' <tr style="">
                                                    <td >

                                                    </td>
                                                </tr> ';
            $str .=' <tr style="">';
            $str .=' </tbody></table>
                                        </td>
                                    </tr>
                                </tbody></table></br>';
            //echo $str;		
        }
    }// end of foreach
    return $str;
}

// end of function

/**
 * @method display_curriculum_courselist()
 * @todo used to display list of courses of curriculum associated with user(student)
 * @param $cid (holds curriculumid), $userid(holds student ,current login student)
 * @return-- table format output
 */
function display_curriculum_courselist($cid, $userid) {
    global $CFG, $USER, $DB;
    // -----used get to cuurent active semesterid--------------------------------
    $hier = new hierarchy();
    $semesterid = $hier->get_allmyactivesemester($userid);
    foreach ($semesterid as $key => $value)
        $semid_getclass = $key;
    //--------------------------------------------------------------------------


    $query = "SELECT cp.* FROM {local_userdata} u JOIN {local_curriculum} cp ON cp.id=u.curriculumid where u.userid={$userid} and u.curriculumid = {$cid}";
    $currlicList = $DB->get_records_sql($query);
    foreach ($currlicList as $list) {
        $table = new html_table();
        $table->head = array(get_string('course', 'local_cobaltcourses') . 's', get_string('status'), get_string('grades'), get_string('enrollsem', 'local_curriculum'));
        $table->size = array('40%', '20%', '20%', '20%');
        $table->align = array('left', 'center', 'center', 'center');
        $data = array();
        $out = '';
        if ($list->enableplan) {
            $curriculumpaths = $DB->get_records('local_curriculum_plan', array('curriculumid' => $list->id), $sort = 'sortorder');
            $hds = array();
            $rws = array();
            $grps = array();
            $i = 0;
            $j = 0;
            $out = '<table id="cur_customplan" width="100%" class="generaltable">';
            $out .= '<tr>
                                <th size="40%">' . get_string('course', 'local_cobaltcourses') . 's' . '</th>
                                <th size="20%">' . get_string('status') . '</th>
                                <th size="20%">' . get_string('grades') . '</th>
                                <th size="20%">' . get_string('enrollsem', 'local_curriculum') . '</th>
                        </tr>';


            foreach ($curriculumpaths as $plans) {
                $showdepth = 1;
                $plancourses = diplay_plancourse($plans->id);
                $countofrecord = sizeof($plancourses);

                $out .= '<tr align="center" class="header' . $i . '" onClick="fnslidetoggle(' . $i . ',' . $countofrecord . ')"><td colspan="3">' . display_curriculum_paths($plans, $showdepth) . '</td>
                        <td style="text-align: right;">';
                if ($i == 0)
                    $out .= '<img class="smallimg" src="pix/expanded.svg" />';
                else
                    $out .= '<img class="smallimg" src="pix/collapsed.svg" />';
                $out .= '</td></tr>';

                $indicate_depth = true;
                $itemdepth = ($indicate_depth) ? 'coursedepth' . min(4, $plans->depth) : 'coursedepth1';
                $k = 0;
                $m = 0;

                foreach ($plancourses as $courses) {
                    $style = $i == 0 ? '' : 'style="display: none;"';
                    $idname = "innerrow";
                    $out .= '<tr ' . $style . ' class="row' . $i . '"  id="outerrow' . $m . '">';
                    $out .= '<td size="40%"><div  class="' . $itemdepth . '"><a href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $courses->id . '&sesskey=' . sesskey() . '">' . $courses->shortname . ' </a>: ' . $courses->fullname . ' </div></td>';

                    if (strip_tags($courses->status) == "Not Enrolled" or strip_tags($courses->status) == "waiting") {
                        $sta = strip_tags($courses->status);
                        if ($sta == 'waiting')
                            $course_status = 'Waiting for Approval';
                        else
                            $course_status = 'Not Enrolled';
                        $out .= '<td size="20%" onClick="innerslidetoggle(' . $k . ')" class="notenrolled_color"  style="cursor:pointer;" >' . $course_status . '<img class="simg' . $k . '" src="pix/collapsed.svg"></img></td>';
                    } else
                        $out .= '<td size="20%" >' . $courses->status . '</td>';

                    $grd = isset($courses->grade) ? '<b>' . $courses->grade . '</b>' : '<b>-</b>';
                    $out .= '<td size="20%" style="text-align: center;">' . $grd . '</td>';
                    $sem = isset($courses->semester) ? $courses->semester : '-';
                    $out .= '<td size="20%">' . $sem . '</td>';
                    $out .= '</tr>';
                    if (strip_tags($courses->status) == "Not Enrolled" or ( strip_tags($courses->status)) == "waiting") {
                        $out .='<tr style="display: none;" class="custom_row "row' . $i . '" r1 "  id="innerrow' . $k . '" >';
                        if ($semid_getclass)
                            $out .= '<td  colspan="4">' . retrieve_listofclclasses_ofcourse($courses->courseid, $semid_getclass) . '</td>';
                        else
                            $out .= '<td  colspan="4">NO Active Semester is Available, To enroll the class. or Add and drop , Registration period is closed </td>';
                        $out .= '</tr>';
                    }

                    $k++;
                    $m++;
                }
                $i++;
            }
            $out .= '</table>';
        } else {
            $ccourses = $DB->get_records('local_curriculum_plancourses', array('curriculumid' => $cid, 'planid' => 0));
            $index = 0;
            foreach ($ccourses as $ccourse) {
                $flag = 0;
                $course = $DB->get_record('local_cobaltcourses', array('id' => $ccourse->courseid));
                $line = array();
                $line[] = '<a href="' . $CFG->wwwroot . '/local/cobaltcourses/view.php?id=' . $course->id . '&sesskey=' . sesskey() . '">' . $course->shortname . ' </a>: ' . $course->fullname;
                list($course->status, $course->grade) = get_course_enrolledstatus($course->id, $userid);
                if ($course->status == 'Completed')
                    $course->status = '<span class="completed_color"  >' . $course->status . '</span>';
                else if (($course->status == 'Not Enrolled') or ( $course->status == 'waiting')) {
                    if ($course->status == 'waiting')
                        $course_status = 'Waiting for Approval';
                    else
                        $course_status = 'Not Enrolled';
                    $flag = 1;
                    $idname = 'innerdiv';
                    $course->status = '<span class="notenrolled_color" id="outerrow' . $index . '" onclick="plantoggle(' . $index . ')"  style="cursor:pointer;">' . $course_status . '<img class="simg' . $index . '" src="pix/collapsed.svg"></img></span>';
                } else
                    $course->status = '<span class="inprogress_color" >' . $course->status . '</span>';
                $line[] = $course->status;
                if ($course->status != 'Not Enrolled') {
                    $course->semester = $users->get_coursestatus($course->id, $userid, true);
                }
                $line[] = isset($course->grade) ? '<b>' . $course->grade . '</b>' : '<b>-</b>';
                $line[] = isset($course->semester) ? $course->semester : '-';
                $data[] = $line;
                if ($flag == 1) {
                    if ($semid_getclass)
                        $msg = new html_table_cell(retrieve_listofclclasses_ofcourse($ccourse->courseid, $semid_getclass));
                    else
                        $msg = new html_table_cell('NO Active Semester is Available, To enroll the class ,or Add and drop , Registration period is closed');
                    $msg->colspan = 4;
                    $row = new html_table_row(array($msg));
                    $row->id = "innerdiv$index";
                    $row->attributes['class'] = "customrow";
                    $data[] = $row;
                    $index++;
                }
            }
        } // end of else
        // print_object($data); 

        $table->data = $data;
        $table->id = "cur_customplan";
        echo '<div style="border:0px solid red" id="hierarchy-index">';
        echo $out;
        if ($data) {

            echo html_writer::table($table);
        }
        echo '</div>';
        echo '<br/>';
    }// end of foreach
}

// end of function

/**
 * @method get_student_class
 * @todo to get class list of a semester and course, student with status
 * @param int $courseid course id, int $semid semester id,string $status
 * @return-- it print class it
 */
function get_student_class($courseid, $semid, $status) {
    global $CFG, $DB, $USER;
    $str = '';

    $precourses = prerequisite_courses($courseid);
    $equcourses = equivalent_courses($courseid);
    /* Bug report #315  -  Student>Curriculum- Credit Hours
     * @author hemalatha c arun <hemalatha@eabyas.in>
     * Resolved - overided class crdithours with cobaltcourse credithours
     */

    if (strip_tags($status) == 'Enrolled (Inprogress)') {
        $clsqry = "SELECT luc.id,luc.*, lc.*,lcc.fullname as fname,lcc.shortname as sname, lcc.credithours as credithours FROM {local_cobaltcourses} lcc,
         {local_clclasses} lc,
		 {local_user_clclasses} luc 
		 WHERE lcc.id=lc.cobaltcourseid AND 
		 lc.id=classid AND  
		 lc.semesterid={$semid} AND 
		 lc.cobaltcourseid={$courseid} AND 
		 luc.userid={$USER->id} ";
    } else {
        $clsqry = "SELECT luc.*,lc.*,lcc.fullname as fname,lcc.shortname as sname, lcc.credithours as credithours FROM {local_cobaltcourses} lcc,
         {local_clclasses} lc,
		 {local_user_clclasses} luc 
		 WHERE lcc.id=lc.cobaltcourseid AND 
		 lc.id=classid AND  
		 
		 lc.cobaltcourseid={$courseid} AND 
		 luc.userid={$USER->id}";
    }
    /* Bug report #323  -  Student>Course Registration>Enroll>Reject- Status
     * @author hemalatha c arun <hemalatha@eabyas.in>
     * Resolved- added valid condition to change the status in course registration page and curriculum page
     */


    if (strip_tags($status) == 'Rejected' || strip_tags($status) == 'Waiting') {
        if (strip_tags($status) == 'Rejected')
            $registrar_status = 2;
        else
            $registrar_status = 0;
        $clases = $DB->get_records_sql($clsqry);
        if (empty($clases)) {
            $clsqry = "SELECT lc.*,lcc.fullname as fname,lcc.shortname as sname, lcc.credithours as credithours,luc.* FROM {local_cobaltcourses} lcc,
         {local_clclasses} lc,
		 {local_course_adddrop} luc 
		 WHERE lcc.id=lc.cobaltcourseid AND 
		 lc.id=classid AND  
		  lc.semesterid={$semid} AND 
		 lc.cobaltcourseid={$courseid} AND 
		 luc.userid={$USER->id} AND luc.registrarapproval= $registrar_status";
        }
    }// end of if 
    $clases = $DB->get_records_sql($clsqry);   
    //print_object($clases);
    foreach ($clases as $class) {
        $classtype = $class->online == 1 ? 'Online' : 'Offline';
        if (strip_tags($status) == 'Enrolled (Inprogress)') {
            $enrollcount = $DB->count_records('local_user_clclasses', array('classid' => $class->id, 'semesterid' => $semid, 'registrarapproval' => 1));
            $existingseats = $class->classlimit - $enrollcount;
          //  $schedule = $DB->get_record('local_scheduleclass', array('classid' => $class->id, 'semesterid' => $semid));
        } else {
            $enrollcount = $DB->count_records('local_user_clclasses', array('classid' => $class->id, 'semesterid' => $class->semesterid, 'registrarapproval' => 1));
            $existingseats = $class->classlimit - $enrollcount;
         //   $schedule = $DB->get_record('local_scheduleclass', array('classid' => $class->id, 'semesterid' => $class->semesterid));
        }
        if ($status != 'Completed') {

            if ($class->registrarapproval == 1)
                $status = '<span class="inprogress_color" style="color:green;">Enrolled (Inprogress)</span>';
            elseif ($class->registrarapproval == 2)
                $status = '<span class="notenrolled_color" style="color:red;">Rejected</span>';
            elseif ($class->registrarapproval == 5)
                $status = '<span class="notenrolled_color" style="color:red;">'.get_string('unenolled','local_adddrop').'</span>';
            else {
                if ($class->registrarapproval == 0)
                    $status = '<span class="inprogress_color" style="color:orange;">Waiting</span>';
            }
        }


        $str.='<table id="classenroll" cellspacing="0" cellpadding="3" border="0" style="font-size:12px;border:1px solid #cccccc;line-height:24px">
        <tbody><tr>';
        $str .= '<td align="left" style="font-size:12px;background:#dddddd !important;" colspan="1">
                         <b><span style="color:#0088CC;text-decoration:none;cursor:pointer;">' . $class->sname . ':&nbsp;</span></b> <b>' . $class->fname . '</b>
                             <span style="color:#333333;" id="spanhonors"></span> 
             </td>';
        $str .='<td align="right" style="font-size:12px;background:#dddddd !important;">';
        $str .='<table id="inner_curtable" cellspacing="0" cellpadding="0" >
                    <tbody>
        <tr>
            <td align="left"> </td>
            <td align="right"> <span style="font-weight:bold;color:black;">' . strtoupper(get_string('class', 'local_clclasses')) . ':</span><span style="font-weight:bold;margin-right:20px;color:#0088CC;">&nbsp;' . $class->fullname . '</span>';
        $str .= $status;
        $str.='</td></tr></tbody></table></td> </tr>';

        $str .=' <tr>
                 <td valign="top" align="right" style="padding-left:20px;">';
        $str .='<table cellpadding="1" border="0">
                      <tbody>
                            <tr id="instucttr">
				<td><b>' . get_string('instructor', 'local_cobaltcourses') . ':&nbsp;</b></td>
				<td>';
        $instructor = array();
        $instructor[] = get_classinst($class->id);
        $str .=implode(' ', $instructor[0]);
        $str .='</td> </tr>
        <tr>
                                  <td style="text-align:right">
<b>Type:</b>
                                      
                                      </td><td>';


        $str .=$classtype;

        $str .='</td>
                              </tr>
                              <tr id="tr1">
					<td><b>' . get_string('credithours', 'local_cobaltcourses') . ':&nbsp;</b></td>
					<td>' . $class->credithours . '</td>
				    </tr>
				
                                                <tr>
                                                    <td align="right"><b>' . get_string('max_seats', 'local_courseregistration') . ':</b></td><td>' . $class->classlimit . '</td>
                                                </tr>
                                                <tr>
                                                    <td align="right"><b>' . get_string('left_seats', 'local_courseregistration') . ':</b></td>
													<td>' . $existingseats . '</td>
                                                </tr>
                              
                              
                               </tbody></table>';
        $str .='</td>';
                                       

        $str .=' <td valign="top" align="right">
                                            <div>';
                                              $str .= table_multiplescheduled_view( $class->id);

		$str .='</div>
                                        </td>
                                    </tr>';

        $str .=' </tbody></table></br>';
    }
    return $str;
}

function get_stu_previous_sem($courseid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT luc.id, ls.fullname FROM {local_clclasses} lc,{local_user_clclasses} luc,{local_semester} ls WHERE lc.cobaltcourseid={$courseid} AND lc.id=luc.classid AND luc.semesterid=ls.id AND luc.userid={$USER->id} ";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $sem) {
        $semname = $sem->fullname;
    }
    return $semname;
}

/**
 * @method get_activesemester_withoutevents
 * @todo to get active semester of particular user without considering registration events or add/drop events
 * @param int $userid
 * @param int $schoolid
 * @return-- int holds active semesterid
 */
function get_activesemester_withoutevents($userid, $schoolid) {
    global $DB, $CFG, $USER;
    $today = date('Y-m-d');

    $sql = "select  ss.id,ss.* from {local_semester} s 
               JOIN {local_school_semester} ss ON ss.semesterid=s.id        
               WHERE s.visible=1 AND ss.schoolid=$schoolid 
                AND '{$today}' BETWEEN from_unixtime( s.startdate,'%Y-%m-%d' ) AND from_unixtime( s.enddate,'%Y-%m-%d' )";
    $semester = $DB->get_record_sql($sql);
    return $semester->semesterid;
}

 function table_multiplescheduled_view($classid){
    global $USER, $CFG, $DB, $OUTPUT;
        $output ='';
    $schedulerecords = $DB->get_records('local_scheduleclass', array('classid'=>$classid,'visible'=>1));
 
    
        if((sizeof($schedulerecords)) > 0){
            
        foreach ($schedulerecords as  $record) {
                    $line=array();
                    $scheduletypename=$DB->get_field('local_class_scheduletype', 'classtype', array('id'=>$record->classtypeid));
                    
                    $line[] = $scheduletypename;                    
                    
                    $date = date('d M ',$record->startdate) .' - '. date('d M ',$record->enddate);
                    $time = date('h:i a', strtotime($record->starttime)).' - '.date('h:i a', strtotime($record->endtime));              
                    $datetime_inner ='<ul id="courseregistration_viewclass"><li>'.($date?$date:'------------------' ).'</li>';                   
                    $datetime_inner.='<li>'.($time?$time:'------------------' ).'</li></ul>';
                    $line[] = $datetime_inner;                    
                //   $datetime_inner .='<li>'.get_string('availableweekdays','local_timetable').': <b>'.($weekdays?$weekdays:'------------------' ).'</b></li></ul>';
                
                    $line[]=$record->availableweekdays;
                    if($record->classroomid > 0){
                    $sql ="select  c.*, b.fullname as building, f.fullname as floor, c.fullname as classroom from {local_classroom} as c
                           JOIN {local_building} as b ON b.id=c.buildingid
                           JOIN {local_floor} as f ON f.id=c.floorid                         
                           where c.id=$record->classroomid";                    
                     $roominfo=$DB->get_record_sql($sql);
                    }
                    
                     if( isset($roominfo )&& $record->classroomid > 0){
                      
                         $line[]=$roominfo->building;
                         $line[]=$roominfo->floor;
                         $line[]=$roominfo->classroom;
                     }
                     else{
                      $line[]=  '---';
                      $line[]=  '---';
                       $line[]= '---';
                     } 
              //  $line[] ='<a class="table-action-deletelink" href="deletedata.php?id='.$list->id.'">Delete</a>';
                $row = new html_table_row();
                $row->cells =$line;
                $row->id='courseregistration_viewclasstr';
                $data[]=$row;
              //  $i++;
            }
            
        }
        else{            
            $row = new html_table_row();                 
            $optioncell = new html_table_cell('Not Scheduled yet');
            $optioncell->colspan = 6;   
            $row ->cells[] = $optioncell;
           $data[]=$row;
            
        }        
    
        $table = new html_table();
       // $table->attributes = array('class'=>'custom_toggleview');
        $table->attributes = array('style'=>'font-size:10px;width:486px;border-collapse:collapse;');
        $table->id = 'GridView1';  
        $table->head = array(get_string('class_type', 'local_courseregistration'),
                             get_string('times', 'local_courseregistration'),
                              get_string('days', 'local_courseregistration'),
                             get_string('bldg', 'local_courseregistration'),
                              get_string('floor', 'local_courseregistration'),
                            get_string('room', 'local_courseregistration'));
      
        
        $table->size = array('10%', '23%','15%','20%','12%','10%');
        $table->align = array('center', 'center','center','center','center');
        $table->width = '99%';
        $table->data = $data;

        $output = html_writer::table($table);
        return $output;
    
   } // end of function
