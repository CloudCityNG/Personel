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
 * Library of functions and constants for module attendance
 *
 * @package   mod_attendance
 * @copyright  2011 Artem Andreev <andreev.artem@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/attendance/locallib.php');

/**
 * Returns the information if the module supports a feature
 *
 * @see plugin_supports() in lib/moodlelib.php
 * @param string $feature FEATURE_xx constant for requested feature
 * @return mixed true if the feature is supported, null if unknown
 */
function local_attendance_supports($feature) {
    switch ($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_GROUPMEMBERSONLY:
            return true;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        // Artem Andreev: AFAIK it's not tested.
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        default:
            return null;
    }
}

function local_att_add_default_statuses($attid) {
    global $DB;

    $statuses = $DB->get_recordset('local_attendance_statuses', array('attendanceid' => 0), 'id');
    foreach ($statuses as $st) {
        $rec = $st;
        $rec->attendanceid = $attid;
        $DB->insert_record('local_attendance_statuses', $rec);
    }
    $statuses->close();
}

function local_attendance_add_instance($attendance) {
    global $DB;

    $attendance->timemodified = time();

    $attendance->id = $DB->insert_record('local_attendance', $attendance);

    local_att_add_default_statuses($attendance->id);

    local_attendance_grade_item_update($attendance);

    return $attendance->id;
}

function local_attendance_update_instance($attendance) {
    global $DB;

    $attendance->timemodified = time();
    $attendance->id = $attendance->instance;

    if (!$DB->update_record('local_attendance', $attendance)) {
        return false;
    }

    local_attendance_grade_item_update($attendance);

    return true;
}

function local_attendance_delete_instance($id) {
    global $DB;

    if (!$attendance = $DB->get_record('local_attendance', array('id' => $id))) {
        return false;
    }

    if ($sessids = array_keys($DB->get_records('local_attendance_sessions', array('attendanceid' => $id), '', 'id'))) {
        $DB->delete_records_list('local_attendance_log', 'sessionid', $sessids);
        $DB->delete_records('local_attendance_sessions', array('attendanceid' => $id));
    }
    $DB->delete_records('local_attendance_statuses', array('attendanceid' => $id));

    $DB->delete_records('local_attendance', array('id' => $id));

    local_local_attendance_grade_item_delete($attendance);

    return true;
}

function local_attendance_delete_course($course, $feedback = true) {
    global $DB;

    $attids = array_keys($DB->get_records('local_attendance', array('course' => $course->id), '', 'id'));
    $sessids = array_keys($DB->get_records_list('local_attendance_sessions', 'attendanceid', $attids, '', 'id'));
    if ($sessids) {
        $DB->delete_records_list('local_attendance_log', 'sessionid', $sessids);
    }
    if ($attids) {
        $DB->delete_records_list('local_attendance_statuses', 'attendanceid', $attids);
        $DB->delete_records_list('local_attendance_sessions', 'attendanceid', $attids);
    }
    $DB->delete_records('local_attendance', array('course' => $course->id));

    return true;
}

/**
 * Called by course/reset.php
 * @param $mform form passed by reference
 */
function local_attendance_reset_course_form_definition(&$mform) {
    $mform->addElement('header', 'attendanceheader', get_string('modulename', 'attendance'));

    $mform->addElement('static', 'description', get_string('description', 'attendance'), get_string('resetdescription', 'attendance'));
    $mform->addElement('checkbox', 'reset_attendance_log', get_string('deletelogs', 'attendance'));

    $mform->addElement('checkbox', 'reset_attendance_sessions', get_string('deletesessions', 'attendance'));
    $mform->disabledIf('reset_attendance_sessions', 'reset_attendance_log', 'notchecked');

    $mform->addElement('checkbox', 'reset_attendance_statuses', get_string('resetstatuses', 'attendance'));
    $mform->setAdvanced('reset_attendance_statuses');
    $mform->disabledIf('reset_attendance_statuses', 'reset_attendance_log', 'notchecked');
}

/**
 * Course reset form defaults.
 */
function local_attendance_reset_course_form_defaults($course) {
    return array('reset_attendance_log' => 0, 'reset_attendance_statuses' => 0, 'reset_attendance_sessions' => 0);
}

function local_attendance_reset_userdata($data) {
    global $DB;

    $status = array();

    $attids = array_keys($DB->get_records('local_attendance', array('course' => $data->courseid), '', 'id'));

    if (!empty($data->reset_attendance_log)) {
        $sess = $DB->get_records_list('local_attendance_sessions', 'attendanceid', $attids, '', 'id');
        if (!empty($sess)) {
            list($sql, $params) = $DB->get_in_or_equal(array_keys($sess));
            $DB->delete_records_select('local_attendance_log', "sessionid $sql", $params);
            list($sql, $params) = $DB->get_in_or_equal($attids);
            $DB->set_field_select('local_attendance_sessions', 'lasttaken', 0, "attendanceid $sql", $params);

            $status[] = array(
                'component' => get_string('modulenameplural', 'attendance'),
                'item' => get_string('attendancedata', 'attendance'),
                'error' => false
            );
        }
    }

    if (!empty($data->reset_attendance_statuses)) {
        $DB->delete_records_list('local_attendance_statuses', 'attendanceid', $attids);
        foreach ($attids as $attid) {
            local_att_add_default_statuses($attid);
        }

        $status[] = array(
            'component' => get_string('modulenameplural', 'attendance'),
            'item' => get_string('sessions', 'attendance'),
            'error' => false
        );
    }

    if (!empty($data->reset_attendance_sessions)) {
        $DB->delete_records_list('local_attendance_sessions', 'attendanceid', $attids);

        $status[] = array(
            'component' => get_string('modulenameplural', 'attendance'),
            'item' => get_string('statuses', 'attendance'),
            'error' => false
        );
    }

    return $status;
}

/*
 * Return a small object with summary information about what a
 *  user has done with a given particular instance of this module
 *  Used for user activity reports.
 *  $return->time = the time they did it
 *  $return->info = a short text description
 */

function local_attendance_user_outline($course, $user, $mod, $attendance) {

    global $CFG;
    require_once(dirname(__FILE__) . '/locallib.php');
    require_once($CFG->libdir . '/gradelib.php');

    $grades = grade_get_grades($course->id, 'local', 'attendance', $attendance->id, $user->id);

    $result = new stdClass();
    if (!empty($grades->items[0]->grades)) {
        $grade = reset($grades->items[0]->grades);
        $result->time = $grade->dategraded;
    } else {
        $result->time = 0;
    }
    if (has_capability('local/attendance:canbelisted', $mod->context, $user->id)) {
        $statuses = local_att_get_statuses($attendance->id);
        $grade = local_att_get_user_grade(local_att_get_user_statuses_stat($attendance->id, $course->startdate, $user->id, $mod), $statuses);
        $maxgrade = local_att_get_user_max_grade(local_att_get_user_taken_sessions_count($attendance->id, $course->startdate, $user->id, $mod), $statuses);

        $result->info = $grade . ' / ' . $maxgrade;
    }

    return $result;
}

/*
 * Print a detailed representation of what a  user has done with
 * a given particular instance of this module, for user activity reports.
 *
 */

function local_attendance_user_complete($course, $user, $mod, $attendance) {
    global $CFG;

    require_once(dirname(__FILE__) . '/renderhelpers.php');
    require_once($CFG->libdir . '/gradelib.php');

    if (has_capability('local/attendance:canbelisted', $mod->context, $user->id)) {
        echo construct_full_user_stat_html_table($attendance, $course, $user, $mod);
    }
}

function local_attendance_print_recent_activity($course, $isteacher, $timestart) {
    return false;
}

//-------- start of cron code--------------------
function local_attendance_cron() {
    global $CFG, $DB, $USER;
    $att_cron = new attendance_cron();
    $att_cron->addscheduledsessions_to_attendance();
}

class attendance_cron {

    public $currentdatesessionslist;
    public $currentday;
    // to get only specific class attendance sessions
    private $classid = null;

    function __construct($classid = null) {

        global $DB, $CFG, $USER;
        if ($classid) {
            $this->classid = $classid;
            $sql = "select * from  {local_scheduleclass}  where UNIX_TIMESTAMP(curdate()) between startdate and enddate and classid=$classid";
        } else {
            $sql = "select * from  {local_scheduleclass}  where UNIX_TIMESTAMP(curdate()) between startdate and enddate";
        }

        $this->currentdatesessionslist = $DB->get_records_sql($sql);
        $this->currentday = date('D', time());
    }

    public function addscheduledsessions_to_attendance() {
        global $DB, $CFG, $USER;

        switch ($this->currentday) {
            case 'Mon': $day = 'M';
                break;
            case 'Tue': $day = 'TU';
                break;
            case 'Wed': $day = 'W';
                break;

            case 'Thu': $day = 'TH';
                break;
            case 'Fri': $day = 'F';
                break;
            case 'Sat': $day = 'SA';
                break;
            case 'Sun': $day = 'SU';
                break;
            default: $day = 0;
        }

        $count = sizeof($this->currentdatesessionslist);

        if ($count > 0) {       

            foreach ($this->currentdatesessionslist as $record) {

                $availabledays = explode('-', $record->availableweekdays);

                if (in_array($day, $availabledays)) {

                    $attendance = $DB->get_record('local_attendance', array('classid' => $record->classid));
                    if ($attendance) {
                        $this->addinstance_attendance_sessions($attendance->id, $record);
                    } else {
                        $attendanceobject = $this->creating_attendance_object($record);
                        $newattendanceid = $DB->insert_record('local_attendance', $attendanceobject);
                        if ($newattendanceid) {
                            $this->add_attendance_status($newattendanceid);
                            $this->addinstance_attendance_sessions($newattendanceid, $record);
                        }
                    }
                }//  end of if day condition
            } // end of foreach
        } //end of count if
        //
        //if($this->classid){
        //    $attendancerecord =$DB->get_record('local_attendance',array('classid'=>$this->classid));
        //    return  $attendancerecord->id;
        // }
    }

// end of function

    public function addinstance_attendance_sessions($attendanceid, $record) {
        global $USER, $DB, $CFG;
        $today = date('Y-m-d');
        $starttimearray = array();
        $endtimearray = array();
        $starttimearray = explode(':', $record->starttime);
        $starttime = $starttimearray[0] * HOURSECS + $starttimearray[1] * MINSECS;
        $endtimearray = explode(':', $record->endtime);
        $endtime = $endtimearray[0] * HOURSECS + $endtimearray[1] * MINSECS;
        $duration = $endtime - $starttime;
        //   echo  $todaymidnt=usergetmidnight(time());
        //  echo  $dd =$todaymidnt+$starttime;       
        //   echo  $timet =strtotime($record->starttime);  
        //  $sessdate = usergetmidnight($record->startdate) + $starttime;
        $sessdate = strtotime($record->starttime);

        $sql = "select * from {local_attendance_sessions} where attendanceid=$attendanceid and sessdate=$sessdate";

        if (!$DB->record_exists_sql($sql)) {
            $tempobject = $this->creating_attendance_sessionsobject($record, $attendanceid);

            $insertedrecord = $DB->insert_record('local_attendance_sessions', $tempobject);
            if (empty($insertedrecord))
                print_error('error occured while adding attendance session, try again');
        }
    }

    public function creating_attendance_object($record) {
        global $USER, $DB, $CFG;

        $temp = new StdClass();
        $temp->classid = $record->classid;
        $classinfo = $DB->get_record('local_clclasses', array('id' => $record->classid));
        $temp->name = $classinfo->fullname;
        $temp->grade = 100;
        $temp->visible = 1;
        $temp->cmidnumber = 0;
        $temp->groupmode = 0;

        return $temp;
    }

// end of function

    public function creating_attendance_sessionsobject($record, $attendanceid) {
        global $USER, $DB, $CFG;
        $starttimearray = array();
        $endtimearray = array();
        $starttimearray = explode(':', $record->starttime);
        $starttime = $starttimearray[0] * HOURSECS + $starttimearray[1] * MINSECS;
        $endtimearray = explode(':', $record->endtime);
        $endtime = $endtimearray[0] * HOURSECS + $endtimearray[1] * MINSECS;
        $duration = $endtime - $starttime;

        // $sessdate =  usergetmidnight($record->startdate) + $starttime;
        $sessdate = strtotime($record->starttime);
        $temp = new StdClass();
        $temp->attendanceid = $attendanceid;
        $temp->groupid = 0;
        $temp->sessdate = $sessdate;
        $temp->duration = $duration;
        $temp->lasttaken = null;
        $temp->lasttakenby = 0;
        $temp->timemodified = time();

        return $temp;
    }

// end of function

    public function add_attendance_status($attendanceid) {
        global $CFG, $USER, $DB;
        $default = array("P" => 'Present', "L" => 'Late', "A" => 'Absent');
        foreach ($default as $key => $value) {
            $DB->insert_record('local_attendance_statuses', array('attendanceid' => $attendanceid, 'acronym' => $key, 'description' => $value));
        }
    }

// end of function
}

// end of class
// -------------end of cron code-------------------------------------------------




function local_attendance_update_grades($attendance, $userid = 0, $nullifnone = true) {
    // We need this function to exist so that quick editing of module name is passed to gradebook.
}

/**
 * Create grade item for given attendance
 *
 * @param object $attendance object with extra cmidnumber
 * @param mixed optional array/object of grade(s); 'reset' means reset grades in gradebook
 * @return int 0 if ok, error code otherwise
 */
function local_attendance_grade_item_update($attendance, $grades = null) {
    global $CFG, $DB;

    require_once('locallib.php');

    if (!function_exists('grade_update')) { // Workaround for buggy PHP versions.
        require_once($CFG->libdir . '/gradelib.php');
    }

    if (!isset($attendance->courseid)) {
        $attendance->courseid = $attendance->course;
    }
    if (!$course = $DB->get_record('course', array('id' => $attendance->course))) {
        error("Course is misconfigured");
    }

    if (!empty($attendance->cmidnumber)) {
        $params = array('itemname' => $attendance->name, 'idnumber' => $attendance->cmidnumber);
    } else {
        // MDL-14303.
        $cm = get_coursemodule_from_instance('attendance', $attendance->id);
        $params = array('itemname' => $attendance->name/* , 'idnumber'=>$attendance->id */);
    }

    if ($attendance->grade > 0) {
        $params['gradetype'] = GRADE_TYPE_VALUE;
        $params['grademax'] = $attendance->grade;
        $params['grademin'] = 0;
    } else if ($attendance->grade < 0) {
        $params['gradetype'] = GRADE_TYPE_SCALE;
        $params['scaleid'] = -$attendance->grade;
    } else {
        $params['gradetype'] = GRADE_TYPE_NONE;
    }

    if ($grades === 'reset') {
        $params['reset'] = true;
        $grades = null;
    }

    return grade_update('local/attendance', $attendance->courseid, 'local', 'attendance', $attendance->id, 0, $grades, $params);
}

/**
 * Delete grade item for given attendance
 *
 * @param object $attendance object
 * @return object attendance
 */
function local_attendance_grade_item_delete($attendance) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    if (!isset($attendance->courseid)) {
        $attendance->courseid = $attendance->course;
    }

    return grade_update('local/attendance', $attendance->courseid, 'local', 'attendance', $attendance->id, 0, null, array('deleted' => 1));
}

function local_attendance_get_participants($attendanceid) {
    return false;
}

/**
 * This function returns if a scale is being used by one attendance
 * it it has support for grading and scales. Commented code should be
 * modified if necessary. See book, glossary or journal modules
 * as reference.
 *
 * @param int $attendanceid
 * @param int $scaleid
 * @return boolean True if the scale is used by any attendance
 */
function local_attendance_scale_used($attendanceid, $scaleid) {
    return false;
}

/**
 * Checks if scale is being used by any instance of attendance
 *
 * This is used to find out if scale used anywhere
 *
 * @param int $scaleid
 * @return bool true if the scale is used by any book
 */
function local_attendance_scale_used_anywhere($scaleid) {
    return false;
}

/**
 * Serves the attendance sessions descriptions files.
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param string $filearea
 * @param array $args
 * @param bool $forcedownload
 * @return bool false if file not found, does not return if found - justsend the file
 */
function local_attendance_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }

    require_login($course, false, $cm);

    if (!$att = $DB->get_record('local_attendance', array('id' => $cm->instance))) {
        return false;
    }

    // Session area is served by pluginfile.php.
    $fileareas = array('session');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }

    $sessid = (int) array_shift($args);
    if (!$sess = $DB->get_record('local_attendance_sessions', array('id' => $sessid))) {
        return false;
    }

    $fs = get_file_storage();
    $relativepath = implode('/', $args);
    $fullpath = "/$context->id/local_attendance/$filearea/$sessid/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }
    send_stored_file($file, 0, 0, true);
}

function local_attendance_tabs($currenttab, $classid, $flag) {
    global $OUTPUT, $DB;
    $toprow = array();
    $toprow[] = new tabobject('create', new moodle_url('/local/attendance/modedit.php', array('classid' => $classid)), get_string('create', 'local_attendance'));
    if ($flag > 0) {
        $toprow[] = new tabobject('manage', new moodle_url('/local/attendance/manage.php', array('classid' => $classid)), get_string('manage', 'local_attendance'));
        $toprow[] = new tabobject('sessions', new moodle_url('/local/attendance/sessions.php', array('classid' => $classid))
                , get_string('sessions', 'local_attendance'));
        $toprow[] = new tabobject('report', new moodle_url('/local/attendance/report.php', array('classid' => $classid)), get_string('report', 'local_attendance'));
        $toprow[] = new tabobject('export', new moodle_url('/local/attendance/export.php', array('classid' => $classid)), get_string('export', 'local_attendance'));
    }
    echo $OUTPUT->tabtree($toprow, $currenttab);
}

function local_check_attendance($id) {
    global $OUTPUT, $DB, $CFG;
    $a = 0;
    $b = 1;
    $sql = "SELECT * FROM {local_attendance_sessions} WHERE attendanceid={$id}";
    $records = $DB->get_records_sql($sql);
    if (empty($records))
        return $a;
    else
        return $b;
}

function local_delete_attendance($id) {
    global $OUTPUT, $DB;
    $DB->delete_records('local_attendance', array('id' => $id));
    return true;
}

function local_check_attendance_session($id) {
    global $OUTPUT, $DB;
    $sql = "SELECT * FROM {local_attendance_log} WHERE sessionid={$id}";
    $record = $DB->get_records_sql($sql);
    if (empty($record))
        return 0;
    else
        return 1;
}

function local_delete_attendance_sess($id) {
    global $OUTPUT, $DB;
    $DB->delete_records('local_attendance_sessions', array('id' => $id));
    return true;
}

function local_get_attendance($userid, $sessid) {
    global $DB, $USER, $CFG;
    $id = $DB->get_record('local_attendance_log', 'statusid', array('sessionid' => $sessid, 'studentid' => $userid));
    $letter = $DB->get_record('local_attendance_statuses', 'acronym', array('id' => $id));
    return $letter;
}

function local_get_timetable_class($date, $hour, $minutes, $class) {
    global $DB, $USER, $CFG;


    $sql = "SELECT la.classid FROM {local_attendance} la,{local_attendance_sessions} ls WHERE
    la.id=ls.attendanceid
    AND la.classid in ($class)
    AND DATE(FROM_UNIXTIME(ls.sessdate,'%Y-%m-%d'))='{$date}'
    AND HOUR(FROM_UNIXTIME(ls.sessdate))={$hour}
    AND MINUTE(FROM_UNIXTIME(ls.sessdate))={$minutes}";
    $records = $DB->get_records_sql($sql);
    if (!empty($records)) {
        foreach ($records as $record) {
            $name = $DB->get_field('local_clclasses', 'fullname', array('id' => $record->classid));
            return $name;
        }
    } else {
        $name = "No class";
        return $name;
    }
}

function sessionformdata_to_attendanceformdata($formdata) {
    global $CFG, $USER, $DB;
    $temp_data = new stdClass();
    $temp_data->durtime = $formdata->duration;
    $temp_data->sessiontype = 0;
    $startdate = usergetmidnight($formdata->startdate);
    $enddate = $formdata->enddate;
    if (( $enddate - $startdate) > 0)
        $temp_data->addmultiply = 1;
    else
        $temp_data->addmultiply = 0;
    $temp_data->sessiondate = $formdata->startdate;
    $temp_data->sessionenddate = $formdata->enddate;
    $temp_data->sdays = $formdata->sdays;
    $temp_data->sdescription = array('text' => '', 'format' => '1', 'itemid' => 0);
    $temp_data->period = 1;

    return $temp_data;
}

function local_construct_sessions_data_for_add($formdata, $duration = null) {
    global $CFG;
    // print_object($formdata);

    if (empty($duration))
        $duration = $formdata->durtime['hours'] * HOURSECS + $formdata->durtime['minutes'] * MINSECS;

    $now = time();

    $sessions = array();
    if (isset($formdata->addmultiply)) {
        echo $startdate = $formdata->sessiondate;
        echo '</br>';
        echo $starttime = $startdate - usergetmidnight($startdate);
        echo '</br>';
        echo $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
        echo '</br>';
        if ($enddate < $startdate) {
            return null;
        }

        echo $days = (int) ceil(($enddate - $startdate) / DAYSECS);

        // Getting first day of week.
        $sdate = $startdate;
        $dinfo = usergetdate($sdate);
        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
        } else {
            $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
            $startweek = $startdate - ($wday - 1) * DAYSECS;
        }

        $wdaydesc = array(0 => 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');

        while ($sdate < $enddate) {
            if ($sdate < $startweek + WEEKSECS) {
                $dinfo = usergetdate($sdate);
                if (isset($formdata->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $formdata->sdays)) {
                    $sess = new stdClass();
                    $sess->sessdate = usergetmidnight($sdate) + $starttime;
                    $sess->duration = $duration;
                    // $sess->descriptionitemid = $formdata->sdescription['itemid'];
                    // $sess->description = $formdata->sdescription['text'];
                    // $sess->descriptionformat = $formdata->sdescription['format'];
                    $sess->timemodified = $now;
                    // print_object($sess);
                    local_fill_groupid($formdata, $sessions, $sess);
                }
                $sdate += DAYSECS;
            } else {
                $startweek += WEEKSECS * $formdata->period;
                $sdate = $startweek;
            }
        }

        //  print_object($formdata);
        //  print_object($sessions);
        //  print_object($sess);
    } else {
        $sess = new stdClass();
        $sess->sessdate = $formdata->sessiondate;
        $sess->duration = $duration;
        $sess->descriptionitemid = $formdata->sdescription['itemid'];
        $sess->description = $formdata->sdescription['text'];
        $sess->descriptionformat = $formdata->sdescription['format'];
        $sess->timemodified = $now;

        local_fill_groupid($formdata, $sessions, $sess);
    }
    // print_object($sessions);
    return $sessions;
}

function local_fill_groupid($formdata, &$sessions, $sess) {
    if ($formdata->sessiontype == 'common') {
        $sess = clone $sess;
        $sess->groupid = 0;
        $sessions[] = $sess;
    } else {
        foreach ($formdata->groups as $groupid) {
            $sess = clone $sess;
            $sess->groupid = $groupid;
            $sessions[] = $sess;
        }
    }
}

function local_attendance_insertion($sessionid) {
    global $CFG, $DB, $USER;
    $attendance_temp = new stdClass();
    $attendance_temp->sessionid = $sessionid;
    $attendance_temp->name = 'session' . $sessionid;
    $attendance_temp->grade = 0;
    $attendance_temp->visible = 1;
    $attendance_temp->cmidnumber = 0;
    $attendance_temp->groupmode = 0;

    $newattendanceid = $DB->insert_record('local_attendance', $attendance_temp);

    return $newattendanceid;
}

function local_attendance_addstatus_during_attendancecreation($attendanceid, $newsessionid) {
    global $CFG, $DB, $USER;
    $temp_attendancestatus = new stdClass();
    $sessioninfo = $DB->get_record('local_livecoursemapping', array('sessionid' => $newsessionid));

    $livecourseexist = $DB->get_record('local_attendance_statuses', array('livecourseid' => $sessioninfo->livecourseid));
    if ($livecourseexist)
        return true;
    else {
        $attendancestatus_array = array('P' => 'Present', 'A' => 'Absent', 'L' => 'Late', 'E' => 'Excused');
        foreach ($attendancestatus_array as $key => $value) {
            $temp_attendancestatus->attendanceid = $attendanceid;
            $temp_attendancestatus->acronym = $key;
            $temp_attendancestatus->grade = ($key == 'A' ? 1 : 0);
            $temp_attendancestatus->description = $value;
            $temp_attendancestatus->visible = 1;
            $temp_attendancestatus->deleted = 0;
            $temp_attendancestatus->livecourseid = $sessioninfo->livecourseid;
            $DB->insert_record('local_attendance_statuses', $temp_attendancestatus);
        }

        return true;
    }
}

// end of function
//function local_attendance_session_mapping($sessionformdata, $newsessionid) {
//
//    global $CFG, $DB, $USER;
//    $attendanceid = local_attendance_insertion($newsessionid);
//
//    // adding attendance statuses one set of statuses per subject
//     local_attendance_addstatus_during_attendancecreation($attendanceid,$newsessionid);
//    if ($attendanceid) {
//        $attendance_object = sessionformdata_to_attendanceformdata($sessionformdata);
//        //print_object($attendance_object);
//        $sessions = local_construct_sessions_data_for_add($attendance_object);
//
//        $att = $DB->get_record('local_attendance', array('id' => $attendanceid));
//        $att = new local_attendance($att);
//        $att->add_sessions($sessions);
//    }
//}



function local_attendance_created_attendancesession_whilcron_failure($classid, $time) {
    global $CFG, $USER, $DB, $PAGE, $OUTPUT;
    $strheading = get_string('attendancesessions', 'local_attendance');
    $PAGE->navbar->add($strheading);
    $PAGE->set_title($strheading);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strheading);
    $att_cron = new attendance_cron($classid);
    $attendanceid = $att_cron->addscheduledsessions_to_attendance();

    //  redirect(new moodle_url('/local/attendance/take.php', array('id'=>$attendanceid->id)));
    //echo $OUTPUT->continue_button(new moodle_url('/local/attendance/take.php', array('id'=>$attendanceid->id)));
    $id = $attendanceid->id;     
    echo $OUTPUT->box(get_string('attendancesession_content','local_attendance'));
    // echo $OUTPUT->continue_button(new moodle_url('/local/attendance/take.php', array('classid' => $classid, 'time' =>$time)));
   
   
   
    echo '<form  method="post" action="take.php" >';
    echo '<input type="hidden" name="classid" value='.$classid.'></input>';
    echo '<input type="hidden" name="time" value='.$time.'></input>';    
    echo '<input type="hidden" name="sesskey" value='.sesskey().'></input>'; 
    echo '<input type="submit" value= "continue">';
    echo '</form>';

    echo $OUTPUT->footer();
    die;
}
