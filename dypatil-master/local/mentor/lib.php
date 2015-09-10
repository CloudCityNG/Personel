<?php

require_once($CFG->dirroot . '/local/lib.php');

class mentor {

    private static $_mentor;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_mentor) {
            self::$_mentor = new mentor();
        }
        return self::$_mentor;
    }

    /**
     * @method get_assigned_students
     * @todo to get list of students, those assigned to mentor or parent based on condition
     * @param  int $mentor (this flag variable is set,get list of all student which assigned to mentor not to parent)
     * @return array of objects (student list) 
     * */
    function get_assigned_students($mentor) {
        global $DB, $CFG, $USER;
        if ($mentor)
            return $DB->get_records('local_assignmentor_tostudent', array('mentorid' => $USER->id));
        return $DB->get_records('local_assignmentor_tostudent', array('parentid' => $USER->id));
    }

    /**
     * @method get_student_details
     * @todo to get particular student information in the form of object
     * @param  int $student student id
     * @return object, student information
     * */
    function get_student_details($student) {
        global $DB, $CFG;
        return $DB->get_record('user', array('id' => $student));
    }

    /**
     * @method student_semester
     * @todo to get student semester info based on mode(current, completed) of a user
     * @param  int $userid student id, int $programid programid
     * @param string $mode(based on mode(current,completed), to get student semester info)
     * @return array of object, student information
     * */
    function student_semester($userid, $programid, $mode) {
        global $DB, $CFG;
        $today = date('Y-m-d');
        $mysql = "SELECT sem.* FROM {local_user_semester} AS user
                            JOIN {local_semester} AS sem
                            ON sem.id = user.semesterid
                            WHERE
                            user.registrarapproval=1 AND
                            user.userid = {$userid} ";

        if ($mode == 'completed') {
            $mysql .= " AND from_unixtime(sem.enddate, '%Y-%m-%d') < '{$today}' ";
        }
        if ($mode == 'current') {
            $mysql .= " AND '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' ) ";
        }
        $mysql .= "ORDER BY sem.startdate DESC";
        return $DB->get_records_sql($mysql);
    }

    /**
     * @method semester_courses
     * @todo to get semester courses list of a user
     * @param  int $userid student id, int $semesterid semester id    
     * @return array of object,course list
     * */
    function semester_courses($semesterid, $userid) {
        global $DB, $CFG;
        $sql = "SELECT class.id, class.cobaltcourseid AS cid, class.fullname, class.shortname, course.fullname AS coursename, course.credithours, course.shortname AS courseid, course.coursetype, CONCAT(us.firstname, ' ', us.lastname) AS instname
                    FROM {local_user_clclasses} AS user
                    JOIN {local_clclasses} AS class
                    ON class.id = user.classid AND class.semesterid = user.semesterid
                    JOIN {local_cobaltcourses} AS course
                    ON course.id = class.cobaltcourseid
                    JOIN {local_scheduleclass} AS schedule
                    ON schedule.classid = class.id AND schedule.semesterid = class.semesterid
                    JOIN {user} AS us
                    ON us.id = schedule.instructorid
                    WHERE
                    user.semesterid = {$semesterid} AND user.userid = {$userid}";
        return $DB->get_records_sql($sql);
    }

    /**
     * @method createtabview
     * @todo provides the tab view
     * @param  currenttab(string)
     * */
    function createtabview($mode, $id) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('completed', new moodle_url('/local/mentor/student.php', array('id' => $id, 'mode' => 'completed')), get_string('completedsem', 'local_semesters'));
        $tabs[] = new tabobject('current', new moodle_url('/local/mentor/student.php', array('id' => $id, 'mode' => 'current')), get_string('currentsem', 'local_semesters'));
        echo $OUTPUT->tabtree($tabs, $mode);
    }

}

?>
