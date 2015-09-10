<?php
defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->libdir . '/gradelib.php');
//require_once(dirname(__FILE__).'/renderhelpers.php');

define('LOCAL_ATT_VIEW_DAYS', 1);
define('LOCAL_ATT_VIEW_WEEKS', 2);
define('LOCAL_ATT_VIEW_MONTHS', 3);
define('LOCAL_LOCAL_ATT_VIEW_ALLPAST', 4);
define('LOCAL_ATT_VIEW_ALL', 5);

define('LOCAL_ATT_SORT_LASTNAME', 1);
define('LOCAL_ATT_SORT_FIRSTNAME', 2);

class local_att_preferences_page_params {
    const ACTION_ADD              = 1;
    const ACTION_DELETE           = 2;
    const ACTION_HIDE             = 3;
    const ACTION_SHOW             = 4;
    const ACTION_SAVE             = 5;

    /** @var int view mode of taking attendance page*/
    public $action;

    public $statusid;

    public function local_get_significant_params() {
        $params = array();

        if (isset($this->action)) {
            $params['action'] = $this->action;
        }
        if (isset($this->statusid)) {
            $params['statusid'] = $this->statusid;
        }

        return $params;
    }
}
class local_attendance {
    const SESSION_COMMON        = 0;
    const SESSION_GROUP         = 1;

    /** @var stdclass course module record */
   // public $cm;

    /** @var stdclass course record */
    //public $course;
public $classid;
public $cmidnumber;
public $visible;
    /** @var stdclass context object */
    public $context;

    /** @var int attendance instance identifier */
    public $id;

    /** @var string attendance activity name */
    public $name;

    /** @var float number (10, 5) unsigned, the maximum grade for attendance */
    public $grade;

    /** current page parameters */
    public $pageparams;

    /** @var attendance_permissions permission of current user for attendance instance*/
    public $perm;

    private $groupmode;

    private $statuses;

    // Array by sessionid.
    private $sessioninfo = array();

    // Arrays by userid.
    private $usertakensesscount = array();
    private $userstatusesstat = array();

    /**
     * Initializes the attendance API instance using the data from DB
     *
     * Makes deep copy of all passed records properties. Replaces integer $course attribute
     * with a full database record (course should not be stored in instances table anyway).
     *
     * @param stdClass $dbrecord Attandance instance data from {attendance} table
     * @param stdClass $cm       Course module record as returned by {@link get_coursemodule_from_id()}
     * @param stdClass $course   Course record from {course} table
     * @param stdClass $context  The context of the workshop instance
     */
    public function __construct(stdclass $dbrecord, stdclass $context=null, $pageparams=null) {
       print_object($dbrecord);
        foreach ($dbrecord as $field => $value) {
            if (property_exists('local_attendance', $field)) {
                $this->{$field} = $value;
            } else {
                throw new coding_exception('The attendance table has a field with no property in the attendance class');
            }
        }
      
            $this->context = $context;
       

        $this->pageparams = $pageparams;

        $this->perm = new local_attendance_permissions($this->context);
    }

    
    public function get_current_sessions() {
        global $DB;

        $today = time(); // Because we compare with database, we don't need to use usertime().

        $sql = "SELECT *
                  FROM {local_attendance_sessions}
                 WHERE :time BETWEEN sessdate AND (sessdate + duration)
                   AND attendanceid = :aid";
        $params = array(
                'time'  => $today,
                'aid'   => $this->id);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns today sessions for this attendance
     *
     * Fetches data from {attendance_sessions}
     *
     * @return array of records or an empty array
     */
    public function get_today_sessions() {
        global $DB;

        $start = usergetmidnight(time());
        $end = $start + DAYSECS;

        $sql = "SELECT *
                  FROM {local_attendance_sessions}
                 WHERE sessdate >= :start AND sessdate < :end
                   AND attendanceid = :aid";
        $params = array(
                'start' => $start,
                'end'   => $end,
                'aid'   => $this->id);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns today sessions suitable for copying attendance log
     *
     * Fetches data from {attendance_sessions}
     *
     * @return array of records or an empty array
     */
    public function get_today_sessions_for_copy($sess) {
        global $DB;

        $start = usergetmidnight($sess->sessdate);

        $sql = "SELECT *
                  FROM {ocal_attendance_sessions}
                 WHERE sessdate >= :start AND sessdate <= :end AND
                       (groupid = 0 OR groupid = :groupid) AND
                       lasttaken > 0 AND attendanceid = :aid";
        $params = array(
                'start'     => $start,
                'end'       => $sess->sessdate,
                'groupid'   => $sess->groupid,
                'aid'       => $this->id);

        return $DB->get_records_sql($sql, $params);
    }

    /**
     * Returns count of hidden sessions for this attendance
     *
     * Fetches data from {attendance_sessions}
     *
     * @return count of hidden sessions
     */
    public function get_hidden_sessions_count() {
        global $DB;

        $where = "attendanceid = :aid AND sessdate < :csdate";
        $params = array(
                'aid'   => $this->id,
                'csdate'=> $this->course->startdate);

        return $DB->count_records_select('local_attendance_sessions', $where, $params);
    }

    public function get_filtered_sessions() {
        global $DB;

        if ($this->pageparams->startdate && $this->pageparams->enddate) {
            $where = "attendanceid = :aid AND sessdate >= :csdate AND sessdate >= :sdate AND sessdate < :edate";
        } else {
            $where = "attendanceid = :aid AND sessdate >= :csdate";
        }
        if ($this->pageparams->get_current_sesstype() > local_att_page_with_filter_controls::SESSTYPE_ALL) {
            $where .= " AND groupid=:cgroup";
        }
        $params = array(
                'aid'       => $this->id,
                'csdate'    => $this->course->startdate,
                'sdate'     => $this->pageparams->startdate,
                'edate'     => $this->pageparams->enddate,
                'cgroup'    => $this->pageparams->get_current_sesstype());
        $sessions = $DB->get_records_select('local_attendance_sessions', $where, $params, 'sessdate asc');
        foreach ($sessions as $sess) {
            if (empty($sess->description)) {
                $sess->description = get_string('nodescription', 'attendance');
            } else {
                $sess->description = file_rewrite_pluginfile_urls($sess->description,
                        'pluginfile.php', $this->context->id, 'local_attendance', 'session', $sess->id);
            }
        }

        return $sessions;
    }

    /**
     * @return moodle_url of manage.php for attendance instance
     */
    public function url_manage($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/manage.php', $params);
    }

    /**
     * @return moodle_url of sessions.php for attendance instance
     */
    public function url_sessions($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/sessions.php', $params);
    }

    /**
     * @return moodle_url of report.php for attendance instance
     */
    public function url_report($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/report.php', $params);
    }

    /**
     * @return moodle_url of export.php for attendance instance
     */
    public function url_export() {
        $params = array('id' => $this->cm->id);
        return new moodle_url('/mod/attendance/export.php', $params);
    }

    /**
     * @return moodle_url of attsettings.php for attendance instance
     */
    public function url_preferences($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/preferences.php', $params);
    }

    /**
     * @return moodle_url of attendances.php for attendance instance
     */
    public function url_take($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/take.php', $params);
    }

    public function url_view($params=array()) {
        $params = array_merge(array('id' => $this->cm->id), $params);
        return new moodle_url('/mod/attendance/view.php', $params);
    }

    public function add_sessions($sessions) {
        global $DB;

        foreach ($sessions as $sess) {
            $sess->attendanceid = $this->id;

            $sess->id = $DB->insert_record('attendance_sessions', $sess);
            $description = file_save_draft_area_files($sess->descriptionitemid,
                        $this->context->id, 'mod_attendance', 'session', $sess->id,
                        array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
                        $sess->description);
            $DB->set_field('attendance_sessions', 'description', $description, array('id' => $sess->id));
        }

        $info_array = array();
        $maxlog = 7; // Only log first 10 sessions and last session in the log info. as we can only store 255 chars.
        $i = 0;
        foreach ($sessions as $sess) {
            if ($i > $maxlog) {
                $lastsession = end($sessions);
                $info_array[] = '...';
                $info_array[] = construct_session_full_date_time($lastsession->sessdate, $lastsession->duration);
                break;
            } else {
                $info_array[] = construct_session_full_date_time($sess->sessdate, $sess->duration);
            }
            $i++;
        }
        add_to_log($this->course->id, 'attendance', 'sessions added', $this->url_manage(),
            implode(',', $info_array), $this->cm->id);
    }

    public function update_session_from_form_data($formdata, $sessionid) {
        global $DB;

        if (!$sess = $DB->get_record('attendance_sessions', array('id' => $sessionid) )) {
            print_error('No such session in this course');
        }

        $sess->sessdate = $formdata->sessiondate;
        $sess->duration = $formdata->durtime['hours']*HOURSECS + $formdata->durtime['minutes']*MINSECS;
        $description = file_save_draft_area_files($formdata->sdescription['itemid'],
                                $this->context->id, 'mod_attendance', 'session', $sessionid,
                                array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0), $formdata->sdescription['text']);
        $sess->description = $description;
        $sess->descriptionformat = $formdata->sdescription['format'];
        $sess->timemodified = time();
        $DB->update_record('attendance_sessions', $sess);

        $url = $this->url_sessions(array('sessionid' => $sessionid, 'action' => att_local_sessions_page_params::ACTION_UPDATE));
        $info = construct_session_full_date_time($sess->sessdate, $sess->duration);
        add_to_log($this->course->id, 'attendance', 'session updated', $url, $info, $this->cm->id);
    }

    public function take_from_form_data($formdata) {
        global $DB, $USER;
        // TODO: WARNING - $formdata is unclean - comes from direct $_POST - ideally needs a rewrite but we do some cleaning below.
        $statuses = implode(',', array_keys( (array)$this->get_statuses() ));
        $now = time();
        $sesslog = array();
        $formdata = (array)$formdata;
        foreach ($formdata as $key => $value) {
            if (substr($key, 0, 4) == 'user') {
                $sid = substr($key, 4);
                if (!(is_numeric($sid) && is_numeric($value))) { // Sanity check on $sid and $value.
                     print_error('nonnumericid', 'attendance');
                }
                $sesslog[$sid] = new stdClass();
                $sesslog[$sid]->studentid = $sid; // We check is_numeric on this above.
                $sesslog[$sid]->statusid = $value; // We check is_numeric on this above.
                $sesslog[$sid]->statusset = $statuses;
                $sesslog[$sid]->remarks = array_key_exists('remarks'.$sid, $formdata) ?
                                                      clean_param($formdata['remarks'.$sid], PARAM_TEXT) : '';
                $sesslog[$sid]->sessionid = $this->pageparams->sessionid;
                $sesslog[$sid]->timetaken = $now;
                $sesslog[$sid]->takenby = $USER->id;
            }
        }

        $dbsesslog = $this->get_session_log($this->pageparams->sessionid);
        foreach ($sesslog as $log) {
            if ($log->statusid) {
                if (array_key_exists($log->studentid, $dbsesslog)) {
                    $log->id = $dbsesslog[$log->studentid]->id;
                    $DB->update_record('attendance_log', $log);
                } else {
                    $DB->insert_record('attendance_log', $log, false);
                }
            }
        }

        $rec = new stdClass();
        $rec->id = $this->pageparams->sessionid;
        $rec->lasttaken = $now;
        $rec->lasttakenby = $USER->id;
        $DB->update_record('attendance_sessions', $rec);

        if ($this->grade != 0) {
            $this->update_users_grade(array_keys($sesslog));
        }

        $params = array(
                'sessionid' => $this->pageparams->sessionid,
                'grouptype' => $this->pageparams->grouptype);
        $url = $this->url_take($params);
        add_to_log($this->course->id, 'attendance', 'taken', $url, '', $this->cm->id);

        redirect($this->url_manage(), get_string('attendancesuccess', 'attendance'));
    }

    /**
     * MDL-27591 made this method obsolete.
     */
    public function get_users($groupid = 0) {
        global $DB;

        // Fields we need from the user table.
        $userfields = user_picture::fields('u').',u.username';

        if (isset($this->pageparams->sort) and ($this->pageparams->sort == LOCAL_ATT_SORT_FIRSTNAME)) {
            $orderby = "u.firstname ASC, u.lastname ASC";
        } else {
            $orderby = "u.lastname ASC, u.firstname ASC";
        }

        $users = get_enrolled_users($this->context, 'local/attendance:canbelisted', $groupid, $userfields, $orderby);

        // Add a flag to each user indicating whether their enrolment is active.
        if (!empty($users)) {
            list($usql, $uparams) = $DB->get_in_or_equal(array_keys($users), SQL_PARAMS_NAMED, 'usid0');

            // CONTRIB-3549.
            $sql = "SELECT ue.userid, ue.status, ue.timestart, ue.timeend
                      FROM {user_enrolments} ue
                      JOIN {enrol} e ON e.id = ue.enrolid
                     WHERE ue.userid $usql
                           AND e.status = :estatus
                           AND e.courseid = :courseid
                  GROUP BY ue.userid, ue.status, ue.timestart, ue.timeend";
            $params = array_merge($uparams, array('estatus'=>ENROL_INSTANCE_ENABLED, 'courseid'=>$this->course->id));
            $enrolmentsparams = $DB->get_records_sql($sql, $params);

            foreach ($users as $user) {
                $users[$user->id]->enrolmentstatus = $enrolmentsparams[$user->id]->status;
                $users[$user->id]->enrolmentstart = $enrolmentsparams[$user->id]->timestart;
                $users[$user->id]->enrolmentend = $enrolmentsparams[$user->id]->timeend;
            }
        }

        return $users;
    }

    public function get_user($userid) {
        global $DB;

        $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);

        $sql = "SELECT ue.userid, ue.status, ue.timestart, ue.timeend
                  FROM {user_enrolments} ue
                  JOIN {enrol} e ON e.id = ue.enrolid
                 WHERE ue.userid = :uid
                       AND e.status = :estatus
                       AND e.courseid = :courseid
              GROUP BY ue.userid, ue.status, ue.timestart, ue.timeend";
        $params = array('uid' => $userid, 'estatus'=>ENROL_INSTANCE_ENABLED, 'courseid'=>$this->course->id);
        $enrolmentsparams = $DB->get_record_sql($sql, $params);

        $user->enrolmentstatus = $enrolmentsparams->status;
        $user->enrolmentstart = $enrolmentsparams->timestart;
        $user->enrolmentend = $enrolmentsparams->timeend;

        return $user;
    }

    public function get_statuses($onlyvisible = true) {
        if (!isset($this->statuses)) {
            $this->statuses = local_att_get_statuses($this->id, $onlyvisible);
        }

        return $this->statuses;
    }

    public function get_session_info($sessionid) {
        global $DB;

        if (!array_key_exists($sessionid, $this->sessioninfo)) {
            $this->sessioninfo[$sessionid] = $DB->get_record('attendance_sessions', array('id' => $sessionid));
        }
        if (empty($this->sessioninfo[$sessionid]->description)) {
            $this->sessioninfo[$sessionid]->description = get_string('nodescription', 'attendance');
        } else {
            $this->sessioninfo[$sessionid]->description = file_rewrite_pluginfile_urls($this->sessioninfo[$sessionid]->description,
                        'pluginfile.php', $this->context->id, 'mod_attendance', 'session', $this->sessioninfo[$sessionid]->id);
        }
        return $this->sessioninfo[$sessionid];
    }

    public function get_sessions_info($sessionids) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($sessionids);
        $sessions = $DB->get_records_select('attendance_sessions', "id $sql", $params, 'sessdate asc');

        foreach ($sessions as $sess) {
            if (empty($sess->description)) {
                $sess->description = get_string('nodescription', 'attendance');
            } else {
                $sess->description = file_rewrite_pluginfile_urls($sess->description,
                            'pluginfile.php', $this->context->id, 'mod_attendance', 'session', $sess->id);
            }
        }

        return $sessions;
    }

    public function get_session_log($sessionid) {
        global $DB;

        return $DB->get_records('local_attendance_log', array('sessionid' => $sessionid), '', 'studentid,statusid,remarks,id');
    }

    public function get_user_stat($userid) {
        $ret = array();
        $ret['completed'] = $this->get_user_taken_sessions_count($userid);
        $ret['statuses'] = $this->get_user_statuses_stat($userid);

        return $ret;
    }

    public function get_user_taken_sessions_count($userid) {
        if (!array_key_exists($userid, $this->usertakensesscount)) {
            $this->usertakensesscount[$userid] = local_att_get_user_taken_sessions_count($this->id, $this->course->startdate, $userid);
        }
        return $this->usertakensesscount[$userid];
    }

    public function get_user_statuses_stat($userid) {
        global $DB;

        if (!array_key_exists($userid, $this->userstatusesstat)) {
            $qry = "SELECT al.statusid, count(al.statusid) AS stcnt
                      FROM {attendance_log} al
                      JOIN {attendance_sessions} ats
                        ON al.sessionid = ats.id
                     WHERE ats.attendanceid = :aid AND
                           ats.sessdate >= :cstartdate AND
                           al.studentid = :uid
                  GROUP BY al.statusid";
            $params = array(
                    'aid'           => $this->id,
                    'cstartdate'    => $this->course->startdate,
                    'uid'           => $userid);

            $this->userstatusesstat[$userid] = $DB->get_records_sql($qry, $params);
        }

        return $this->userstatusesstat[$userid];
    }

    public function get_user_grade($userid) {
        return local_att_get_user_grade($this->get_user_statuses_stat($userid), $this->get_statuses());
    }

    // For getting sessions count implemented simplest method - taken sessions.
    // It can have error if users don't have attendance info for some sessions.
    // In the future we can implement another methods:
    // * all sessions between user start enrolment date and now;
    // * all sessions between user start and end enrolment date.
    // While implementing those methods we need recalculate grades of all users
    // on session adding.
    public function get_user_max_grade($userid) {
        return local_att_get_user_max_grade($this->get_user_taken_sessions_count($userid), $this->get_statuses());
    }

    public function update_users_grade($userids) {
        $grades = array();

        foreach ($userids as $userid) {
            $grades[$userid] = new stdClass();
            $grades[$userid]->userid = $userid;
            $grades[$userid]->rawgrade = local_att_calc_user_grade_fraction($this->get_user_grade($userid),
                                                                      $this->get_user_max_grade($userid)) * $this->grade;
        }

        return grade_update('mod/attendance', $this->course->id, 'mod', 'attendance',
                            $this->id, 0, $grades);
    }

    public function get_user_filtered_sessions_log($userid) {
        global $DB;

        if ($this->pageparams->startdate && $this->pageparams->enddate) {
            $where = "ats.attendanceid = :aid AND ats.sessdate >= :csdate AND
                      ats.sessdate >= :sdate AND ats.sessdate < :edate";
        } else {
            $where = "ats.attendanceid = :aid AND ats.sessdate >= :csdate";
        }

        $sql = "SELECT ats.id, ats.sessdate, ats.groupid, al.statusid
                  FROM {attendance_sessions} ats
                  JOIN {attendance_log} al
                    ON ats.id = al.sessionid AND al.studentid = :uid
                 WHERE $where
              ORDER BY ats.sessdate ASC";

        $params = array(
                'uid'       => $userid,
                'aid'       => $this->id,
                'csdate'    => $this->course->startdate,
                'sdate'     => $this->pageparams->startdate,
                'edate'     => $this->pageparams->enddate);
        $sessions = $DB->get_records_sql($sql, $params);

        return $sessions;
    }

    public function get_user_filtered_sessions_log_extended($userid) {
        global $DB;

        // All taked sessions (including previous groups).

        $groups = array_keys(groups_get_all_groups($this->course->id, $userid));
        $groups[] = 0;
        list($gsql, $gparams) = $DB->get_in_or_equal($groups, SQL_PARAMS_NAMED, 'gid0');

        if ($this->pageparams->startdate && $this->pageparams->enddate) {
            $where = "ats.attendanceid = :aid AND ats.sessdate >= :csdate AND
                      ats.sessdate >= :sdate AND ats.sessdate < :edate";
            $where2 = "ats.attendanceid = :aid2 AND ats.sessdate >= :csdate2 AND
                      ats.sessdate >= :sdate2 AND ats.sessdate < :edate2 AND ats.groupid $gsql";
        } else {
            $where = "ats.attendanceid = :aid AND ats.sessdate >= :csdate";
            $where2 = "ats.attendanceid = :aid2 AND ats.sessdate >= :csdate2 AND ats.groupid $gsql";
        }

        $sql = "SELECT ats.id, ats.groupid, ats.sessdate, ats.duration, ats.description, al.statusid, al.remarks
                  FROM {attendance_sessions} ats
                RIGHT JOIN {attendance_log} al
                    ON ats.id = al.sessionid AND al.studentid = :uid
                 WHERE $where
            UNION
                SELECT ats.id, ats.groupid, ats.sessdate, ats.duration, ats.description, al.statusid, al.remarks
                  FROM {attendance_sessions} ats
                LEFT JOIN {attendance_log} al
                    ON ats.id = al.sessionid AND al.studentid = :uid2
                 WHERE $where2
             ORDER BY sessdate ASC";

        $params = array(
                'uid'       => $userid,
                'aid'       => $this->id,
                'csdate'    => $this->course->startdate,
                'sdate'     => $this->pageparams->startdate,
                'edate'     => $this->pageparams->enddate,
                'uid2'       => $userid,
                'aid2'       => $this->id,
                'csdate2'    => $this->course->startdate,
                'sdate2'     => $this->pageparams->startdate,
                'edate2'     => $this->pageparams->enddate);
        $params = array_merge($params, $gparams);
        $sessions = $DB->get_records_sql($sql, $params);

        foreach ($sessions as $sess) {
            if (empty($sess->description)) {
                $sess->description = get_string('nodescription', 'attendance');
            } else {
                $sess->description = file_rewrite_pluginfile_urls($sess->description,
                        'pluginfile.php', $this->context->id, 'mod_attendance', 'session', $sess->id);
            }
        }

        return $sessions;
    }

    public function delete_sessions($sessionsids) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($sessionsids);
        $DB->delete_records_select('attendance_log', "sessionid $sql", $params);
        $DB->delete_records_list('attendance_sessions', 'id', $sessionsids);
        add_to_log($this->course->id, 'attendance', 'sessions deleted', $this->url_manage(),
            get_string('sessionsids', 'attendance').implode(', ', $sessionsids), $this->cm->id);
    }

    public function update_sessions_duration($sessionsids, $duration) {
        global $DB;

        $now = time();
        $sessions = $DB->get_records_list('attendance_sessions', 'id', $sessionsids);
        foreach ($sessions as $sess) {
            $sess->duration = $duration;
            $sess->timemodified = $now;
            $DB->update_record('attendance_sessions', $sess);
        }
        add_to_log($this->course->id, 'attendance', 'sessions duration updated', $this->url_manage(),
            get_string('sessionsids', 'attendance').implode(', ', $sessionsids), $this->cm->id);
    }

    public function remove_status($statusid) {
        global $DB;

        $DB->set_field('attendance_statuses', 'deleted', 1, array('id' => $statusid));
    }

    public function add_status($acronym, $description, $grade) {
        global $DB;

        if ($acronym && $description) {
            $rec = new stdClass();
            $rec->courseid = $this->course->id;
            $rec->attendanceid = $this->id;
            $rec->acronym = $acronym;
            $rec->description = $description;
            $rec->grade = $grade;
            $DB->insert_record('attendance_statuses', $rec);

            add_to_log($this->course->id, 'attendance', 'status added', $this->url_preferences(),
                $acronym.': '.$description.' ('.$grade.')', $this->cm->id);
        } else {
            print_error('cantaddstatus', 'attendance', $this->url_preferences());
        }
    }

    public function update_status($statusid, $acronym, $description, $grade, $visible) {
        global $DB;

        $updated = array();

        $status = new stdClass();
        $status->id = $statusid;
        if ($acronym) {
            $status->acronym = $acronym;
            $updated[] = $acronym;
        }
        if ($description) {
            $status->description = $description;
            $updated[] = $description;
        }
        if (isset($grade)) {
            $status->grade = $grade;
            $updated[] = $grade;
        }
        if (isset($visible)) {
            $status->visible = $visible;
            $updated[] = $visible ? get_string('show') : get_string('hide');
        }
        $DB->update_record('attendance_statuses', $status);

        add_to_log($this->course->id, 'attendance', 'status updated', $this->url_preferences(),
            implode(' ', $updated), $this->cm->id);
    }
}

class local_attendance_permissions {
    private $canview;
    private $canviewreports;
    private $cantake;
    private $canchange;
    private $canmanage;
    private $canchangepreferences;
    private $canexport;
    private $canbelisted;
    private $canaccessallgroups;

    private $cm;
    private $context;

    public function __construct($cm, $context) {
        $this->cm = $cm;
        $this->context = $context;
    }

    public function can_view() {
        if (is_null($this->canview)) {
            $this->canview = has_capability('mod/attendance:view', $this->context);
        }

        return $this->canview;
    }

    public function require_view_capability() {
        require_capability('mod/attendance:view', $this->context);
    }

    public function can_view_reports() {
        if (is_null($this->canviewreports)) {
            $this->canviewreports = has_capability('mod/attendance:viewreports', $this->context);
        }

        return $this->canviewreports;
    }

    public function require_view_reports_capability() {
        //require_capability('mod/attendance:viewreports', $this->context);
        $systemcontext = context_system::instance();
        require_capability('local/classroomresources:manage',$systemcontext);
    }

    public function can_take() {
        if (is_null($this->cantake)) {
            $this->cantake = has_capability('mod/attendance:takeattendances', $this->context);
        }

        return $this->cantake;
    }

    public function can_take_session($groupid) {
        if (!$this->can_take()) {
            return false;
        }

        if ($groupid == attendance::SESSION_COMMON
            || $this->can_access_all_groups()
            || array_key_exists($groupid, groups_get_activity_allowed_groups($this->cm))) {
            return true;
        }

        return false;
    }

    public function can_change() {
        if (is_null($this->canchange)) {
            $this->canchange = has_capability('mod/attendance:changeattendances', $this->context);
        }

        return $this->canchange;
    }

    public function can_manage() {
        if (is_null($this->canmanage)) {
            $this->canmanage = has_capability('local/classroomresources:manage', $this->context);
        }

        return $this->canmanage;
    }

    public function require_manage_capability() {
        require_capability('mod/attendance:manageattendances', $this->context);
    }

    public function can_change_preferences() {
        if (is_null($this->canchangepreferences)) {
            $this->canchangepreferences = has_capability('mod/attendance:changepreferences', $this->context);
        }

        return $this->canchangepreferences;
    }

    public function require_change_preferences_capability() {
        require_capability('mod/attendance:changepreferences', $this->context);
    }

    public function can_export() {
        if (is_null($this->canexport)) {
            $this->canexport = has_capability('mod/attendance:export', $this->context);
        }

        return $this->canexport;
    }

    public function require_export_capability() {
        require_capability('mod/attendance:export', $this->context);
    }

    public function can_be_listed() {
        if (is_null($this->canbelisted)) {
            $this->canbelisted = has_capability('mod/attendance:canbelisted', $this->context, null, false);
        }

        return $this->canbelisted;
    }

    public function can_access_all_groups() {
        if (is_null($this->canaccessallgroups)) {
            $this->canaccessallgroups = has_capability('moodle/site:accessallgroups', $this->context);
        }

        return $this->canaccessallgroups;
    }
}
function local_add_sessions($sessions,$attendanceid) {
        global $DB;
    
        print_object($sessions);
        foreach ($sessions as $sess) {
            $sess->attendanceid = $attendanceid;
           echo "inside the local session";
         echo     $sess->id = $DB->insert_record('local_attendance_sessions', $sess);
            //$description = file_save_draft_area_files($sess->descriptionitemid,
            //            $this->context->id, 'mod_attendance', 'session', $sess->id,
            //            array('subdirs' => false, 'maxfiles' => -1, 'maxbytes' => 0),
            //            $sess->description);
            $DB->set_field('local_attendance_sessions', 'description', $description, array('id' => $sess->id));
        }

        //$info_array = array();
        //$maxlog = 7; // Only log first 10 sessions and last session in the log info. as we can only store 255 chars.
        //$i = 0;
        //foreach ($sessions as $sess) {
        //    if ($i > $maxlog) {
        //        $lastsession = end($sessions);
        //        $info_array[] = '...';
        //        $info_array[] = construct_session_full_date_time($lastsession->sessdate, $lastsession->duration);
        //        break;
        //    } else {
        //        $info_array[] = construct_session_full_date_time($sess->sessdate, $sess->duration);
        //    }
        //    $i++;
        //}
        //add_to_log($this->course->id, 'attendance', 'sessions added', $this->url_manage(),
        //    implode(',', $info_array), $this->cm->id);
    }
    class local_att_manage_page_params extends local_att_page_with_filter_controls {
    public function  __construct() {
        $this->selectortype = local_att_page_with_filter_controls::SELECTOR_SESS_TYPE;
    }

    public function get_significant_params() {
        return array();
    }
}
class local_att_page_with_filter_controls {
    const SELECTOR_NONE         = 1;
    const SELECTOR_GROUP        = 2;
    const SELECTOR_SESS_TYPE    = 3;

    const SESSTYPE_COMMON       = 0;
    const SESSTYPE_ALL          = -1;
    const SESSTYPE_NO_VALUE     = -2;

    /** @var int current view mode */
    public $view;

    /** @var int $view and $curdate specify displaed date range */
    public $curdate;

    /** @var int start date of displayed date range */
    public $startdate;

    /** @var int end date of displayed date range */
    public $enddate;

    public $selectortype        = self::SELECTOR_NONE;

    protected $defaultview      = LOCAL_ATT_VIEW_WEEKS;

    private $cm;

    private $sessgroupslist;

    private $sesstype;

    public function init($cm) {
        $this->cm = $cm;
        $this->init_view();
        $this->init_curdate();
        $this->init_start_end_date();
    }

    private function init_view() {
        global $SESSION;

        if (isset($this->view)) {
            $SESSION->attcurrentattview[$this->cm->course] = $this->view;
        } else if (isset($SESSION->attcurrentattview[$this->cm->course])) {
            $this->view = $SESSION->attcurrentattview[$this->cm->course];
        } else {
            $this->view = $this->defaultview;
        }
    }

    private function init_curdate() {
        global $SESSION;

        if (isset($this->curdate)) {
            $SESSION->attcurrentattdate[$this->cm->course] = $this->curdate;
        } else if (isset($SESSION->attcurrentattdate[$this->cm->course])) {
            $this->curdate = $SESSION->attcurrentattdate[$this->cm->course];
        } else {
            $this->curdate = time();
        }
    }

    public function init_start_end_date() {
        global $CFG;

        // HOURSECS solves issue for weeks view with Daylight saving time and clocks adjusting by
        //one hour backward.
        $date = usergetdate($this->curdate + HOURSECS);
        $mday = $date['mday'];
        $wday = $date['wday'] - $CFG->calendar_startwday;
        if ($wday < 0) {
            $wday += 7;
        }
        $mon = $date['mon'];
        $year = $date['year'];

        switch ($this->view) {
            case LOCAL_ATT_VIEW_DAYS:
                $this->startdate = make_timestamp($year, $mon, $mday);
                $this->enddate = make_timestamp($year, $mon, $mday + 1);
                break;
            case LOCAL_ATT_VIEW_WEEKS:
                $this->startdate = make_timestamp($year, $mon, $mday - $wday);
                $this->enddate = make_timestamp($year, $mon, $mday + 7 - $wday) - 1;
                break;
            case LOCAL_ATT_VIEW_MONTHS:
                $this->startdate = make_timestamp($year, $mon);
                $this->enddate = make_timestamp($year, $mon + 1);
                break;
            case LOCAL_LOCAL_ATT_VIEW_ALLPAST:
                $this->startdate = 1;
                $this->enddate = time();
                break;
            case LOCAL_ATT_VIEW_ALL:
                $this->startdate = 0;
                $this->enddate = 0;
                break;
        }
    }

    private function calc_sessgroupslist_sesstype() {
        global $SESSION;

        if (!array_key_exists('attsessiontype', $SESSION)) {
            $SESSION->attsessiontype = array($this->cm->course => self::SESSTYPE_ALL);
        } else if (!array_key_exists($this->cm->course, $SESSION->attsessiontype)) {
            $SESSION->attsessiontype[$this->cm->course] = self::SESSTYPE_ALL;
        }

        $group = optional_param('group', self::SESSTYPE_NO_VALUE, PARAM_INT);
        if ($this->selectortype == self::SELECTOR_SESS_TYPE) {
            if ($group > self::SESSTYPE_NO_VALUE) {
                $SESSION->attsessiontype[$this->cm->course] = $group;
                if ($group > self::SESSTYPE_ALL) {
                    // Set activegroup in $SESSION.
                    groups_get_activity_group($this->cm, true);
                } else {
                    // Reset activegroup in $SESSION.
                    unset($SESSION->activegroup[$this->cm->course][VISIBLEGROUPS][$this->cm->groupingid]);
                    unset($SESSION->activegroup[$this->cm->course]['aag'][$this->cm->groupingid]);
                    unset($SESSION->activegroup[$this->cm->course][SEPARATEGROUPS][$this->cm->groupingid]);
                }
                $this->sesstype = $group;
            } else {
                $this->sesstype = $SESSION->attsessiontype[$this->cm->course];
            }
        } else if ($this->selectortype == self::SELECTOR_GROUP) {
            if ($group == 0) {
                $SESSION->attsessiontype[$this->cm->course] = self::SESSTYPE_ALL;
                $this->sesstype = self::SESSTYPE_ALL;
            } else if ($group > 0) {
                $SESSION->attsessiontype[$this->cm->course] = $group;
                $this->sesstype = $group;
            } else {
                $this->sesstype = $SESSION->attsessiontype[$this->cm->course];
            }
        }

        if (is_null($this->sessgroupslist)) {
            $this->calc_sessgroupslist();
        }
        // For example, we set SESSTYPE_ALL but user can access only to limited set of groups.
        if (!array_key_exists($this->sesstype, $this->sessgroupslist)) {
            reset($this->sessgroupslist);
            $this->sesstype = key($this->sessgroupslist);
        }
    }

    private function calc_sessgroupslist() {
        global $USER, $PAGE;

        //$this->sessgroupslist = array();
        //$groupmode = groups_get_activity_groupmode($this->cm);
        //if ($groupmode == NOGROUPS) {
        //    return;
        //}
        //
        //if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $PAGE->context)) {
        //    $allowedgroups = groups_get_all_groups($this->cm->course, 0, $this->cm->groupingid);
        //} else {
        //    $allowedgroups = groups_get_all_groups($this->cm->course, $USER->id, $this->cm->groupingid);
        //}
        //
        //if ($allowedgroups) {
        //    if ($groupmode == VISIBLEGROUPS or has_capability('moodle/site:accessallgroups', $PAGE->context)) {
        //        $this->sessgroupslist[self::SESSTYPE_ALL] = get_string('all', 'attendance');
        //    }
        //    if ($groupmode == VISIBLEGROUPS) {
        //        $this->sessgroupslist[self::SESSTYPE_COMMON] = get_string('commonsessions', 'attendance');
        //    }
        //    foreach ($allowedgroups as $group) {
        //        $this->sessgroupslist[$group->id] = format_string($group->name);
        //    }
        //}
    }

    public function get_sess_groups_list() {
        if (is_null($this->sessgroupslist)) {
            $this->calc_sessgroupslist_sesstype();
        }

        return $this->sessgroupslist;
    }

    public function get_current_sesstype() {
        if (is_null($this->sesstype)) {
            $this->calc_sessgroupslist_sesstype();
        }

        return $this->sesstype;
    }

    public function set_current_sesstype($sesstype) {
        $this->sesstype = $sesstype;
    }
}
class local_att_report_page_params extends local_att_page_with_filter_controls {
    public $group;
    public $sort;

    public function  __construct() {
        $this->selectortype = self::SELECTOR_GROUP;
    }

    public function init($cm) {
        parent::init($cm);

        if (!isset($this->group)) {
            $this->group = $this->get_current_sesstype() > 0 ? $this->get_current_sesstype() : 0;
        }
        if (!isset($this->sort)) {
            $this->sort = LOCAL_ATT_SORT_LASTNAME;
        }
    }

    public function get_significant_params() {
        $params = array();

        if ($this->sort != LOCAL_ATT_SORT_LASTNAME) {
            $params['sort'] = $this->sort;
        }

        return $params;
    }
}
function local_attendance_tabs($currenttab,$classid,$flag) 
{
global $OUTPUT,$DB;
$toprow = array();
$toprow[] = new tabobject('create', new moodle_url('/local/attendance/modedit.php',array('classid'=>$classid)),
                          get_string('create','local_attendance'));
if($flag > 0) {
$toprow[] = new tabobject('manage', new moodle_url('/local/attendance/manage.php',array('classid'=>$classid)),
                          get_string('manage','local_attendance'));
$toprow[] = new tabobject('sessions', new moodle_url('/local/attendance/sessions.php',array('classid'=>$classid))
                          , get_string('sessions','local_attendance'));
$toprow[] = new tabobject('report', new moodle_url('/local/attendance/report.php',array('classid'=>$classid)),
                          get_string('report','local_attendance'));
$toprow[] = new tabobject('export', new moodle_url('/local/attendance/export.php',array('classid'=>$classid)),
                        get_string('export','local_attendance'));
}
echo $OUTPUT->tabtree($toprow,$currenttab);
}
function local_check_attendance($id) {
global $OUTPUT,$DB,$CFG;
$a=0;
$b=1;
$sql="SELECT * FROM {local_attendance_sessions} WHERE attendanceid={$id}";
$records=$DB->get_records_sql($sql);
if(empty($records))
return $a;
else
return $b;
}
function  local_delete_attendance($id) {
global $OUTPUT,$DB;
$DB->delete_records('local_attendance', array('id'=>$id));
return true;
}
function local_check_attendance_session($id)
{
  global $OUTPUT,$DB;
  $sql="SELECT * FROM {local_attendance_log} WHERE sessionid={$id}";
  $record=$DB->get_records_sql($sql);
  if(empty($record))
  return 0;
  else
  return 1;
}
function local_delete_attendance_sess($id)
{
  global $OUTPUT,$DB;
  $DB->delete_records('local_attendance_sessions', array('id'=>$id));
  return true;
}
function local_get_attendance($userid,$sessid)
{
    global $DB,$USER,$CFG;
    $id=$DB->get_record('local_attendance_log','statusid',array('sessionid'=>$sessid,'studentid'=>$userid));
    $letter=$DB->get_record('local_attendance_statuses','acronym',array('id'=>$id));
    return $letter;
}
function local_get_timetable_class($date,$hour,$minutes,$class)
{
    global $DB,$USER,$CFG;
    

    $sql="SELECT la.classid FROM {local_attendance} la,{local_attendance_sessions} ls WHERE
    la.id=ls.attendanceid
    AND la.classid in ($class)
    AND DATE(FROM_UNIXTIME(ls.sessdate,'%Y-%m-%d'))='{$date}'
    AND HOUR(FROM_UNIXTIME(ls.sessdate))={$hour}
    AND MINUTE(FROM_UNIXTIME(ls.sessdate))={$minutes}";
    $records=$DB->get_records_sql($sql);
    if(!empty($records)) {
    foreach($records as $record) {
        $name=$DB->get_field('local_clclasses','fullname',array('id'=>$record->classid));
    return $name;
    }
    }
    else {
        $name="No class";
        return $name;
    }
}



function converting_sectionscheduletime_to_attendancesessions($data){
   global $CFG,$USER,$DB;
   $temp_data=new stdClass();   
   //$starttime = new DateTime($data->starthour.':'.$data->startmin.':00');
   //$endtime = new DateTime($data->endhour.':'.$data->endmin.':00');
   //$interval = $starttime->diff($endtime);
   //$duration=$interval->format('%s');
    $starttime = strtotime($data->starthour.':'.$data->startmin.':00');
    $endtime = strtotime($data->endhour.':'.$data->endmin.':00');
    $duration = $endtime - $starttime;
   $temp_data->duration = $duration;
  //  $now = time();
     $temp_data->sessiontype=0;
    if(($data->enddate-$data->startdate)>0)
    $temp_data->addmultiply = 1;
    else
    $temp_data->addmultiply = 0;
    
    // converting unixtimestamp to date
    $datetime = date('Y-m-d', $data->startdate);
    $datetime_array=explode('-',$datetime); 
     
    //adding start time  + date to unixtimestamp
    echo $startdatetime_unixtime = make_timestamp($datetime_array[0], $datetime_array[1], $datetime_array[2],$data->starthour, $data->startmin);
    $temp_data->sessiondate = $startdatetime_unixtime;
    $temp_data->sessionenddate = $data->enddate;
    $temp_data->sdays = $data->sdays;
    $temp_data->sdescription = array('text' => '', 'format' => '1', 'itemid' => 0);
    $temp_data->period = $data->period;
    
    return $temp_data;
    
}


function local_construct_sessions_data_for_add($formdata,$duration=null ) {
    global $CFG;
   // print_object($formdata);
    
    if(empty($duration))    
    $duration = $formdata->durtime['hours']*HOURSECS + $formdata->durtime['minutes']*MINSECS;
    
    $now = time();

    $sessions = array();
    if (isset($formdata->addmultiply)) {
      echo  $startdate = $formdata->sessiondate;
      echo '</br>';
      echo   $starttime = $startdate - usergetmidnight($startdate);
      echo '</br>';
       echo  $enddate = $formdata->sessionenddate + DAYSECS; // Because enddate in 0:0am.
        echo '</br>';
        if ($enddate < $startdate) {
            return null;
        }

        echo $days = (int)ceil(($enddate - $startdate) / DAYSECS);

        // Getting first day of week.
        $sdate = $startdate;
        $dinfo = usergetdate($sdate);
        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            $startweek = $startdate - $dinfo['wday'] * DAYSECS; // Call new variable.
        } else {
            $wday = $dinfo['wday'] === 0 ? 7 : $dinfo['wday'];
            $startweek = $startdate - ($wday-1) * DAYSECS;
        }

        $wdaydesc = array(0=>'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat');
        
        while ($sdate < $enddate) {
            if ($sdate < $startweek + WEEKSECS) {
                $dinfo = usergetdate($sdate);
                if (isset($formdata->sdays) && array_key_exists($wdaydesc[$dinfo['wday']], $formdata->sdays)) {
                    $sess = new stdClass();
                    $sess->sessdate =  usergetmidnight($sdate) + $starttime;
                    $sess->duration = $duration;
                    $sess->descriptionitemid = $formdata->sdescription['itemid'];
                    $sess->description = $formdata->sdescription['text'];
                    $sess->descriptionformat = $formdata->sdescription['format'];
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
