<?php

require_once($CFG->dirroot . '/local/lib.php');

class semesters {

    private static $_semester;
    private $dbHandle;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_semester) {
            self::$_semester = new semesters();
        }
        return self::$_semester;
    }

    /**
     * @method assign_schoolsemester
     * @todo Assigns semester to the multiple schools
     * @param  $semesterid(int)
     * @param  $schoolid(int)
     * */
    function assign_schoolsemester($semesterid, $schoolid) {
        global $DB, $CFG;

        if (is_array($schoolid)) {
            $flag = false;
            $school = array();
            foreach ($schoolid as $sid) {
                if ($sid == 0) {
                    //If 'All' is selected get all the schools
                    $s = $DB->get_records('local_school', array('visible' => 1));
                    $flag = true;
                }
            }
            if ($flag) {
                foreach ($s as $s1) {
                    $school[] = $s1->id;
                }
            } else {
                //List of selected Schools
                $school = $schoolid;
            }
            foreach ($school as $scl) {
                $rec = new stdClass();
                $rec->id = -1;
                $rec->schoolid = $scl;
                $rec->semesterid = $semesterid;
                //If semester is not assigned to the school previously, Insert a new record.
                if (!$DB->record_exists('local_school_semester', array('schoolid' => $rec->schoolid, 'semesterid' => $rec->semesterid)))
                    $DB->insert_record('local_school_semester', $rec);
            }
            return true;
        } else {
            if (!$DB->record_exists('local_school_semester', array('schoolid' => $schoolid, 'semesterid' => $semesterid))) {
                $rec = new stdClass();
                $rec->id = -1;
                $rec->schoolid = $schoolid;
                $rec->semesterid = $semesterid;
                $DB->insert_record('local_school_semester', $rec);
            }
        }
    }

    /**
     * @method cobalt_insert_semester
     * @todo Inserts a new record
     * @param  $data(array)
     * @return Id of the inserted data
     * */
    function cobalt_insert_semester($data) {
        global $DB;
        $data->id = $DB->insert_record('local_semester', $data);
        $lastinserted = $this->assign_schoolsemester($data->id, $data->schoolid);
        return $lastinserted;
    }

    /**
     * @method cobalt_update_semester
     * @todo Update the details of the existing semesters
     * @param  $data(array)
     * */
    function cobalt_update_semester($data) {
        global $DB;
        $DB->update_record('local_semester', $data);
        /* $DB->delete_records('local_school_semester', array('semesterid'=>$data->id));
          $lastinserted = $this->assign_schoolsemester($data->id, $data->schoolid); */
        return $lastinserted;
    }

    /**
     * @method cobalt_delete_semester
     * @todo Delete the records from local_semester
     * @param  $id(int)
     * */
    function cobalt_delete_semester($id, $schoolid = 0) {
        global $DB;
        $assignedschoolist_count = $DB->count_records('local_school_semester', array('semesterid' => $id));
        if ($assignedschoolist_count > 1) {
            $DB->delete_records('local_school_semester', array('semesterid' => $id, 'schoolid' => $schoolid));
        } else {
            $DB->delete_records('local_semester', array('id' => $id));
            $DB->delete_records('local_school_semester', array('semesterid' => $id));
            $DB->delete_records('local_event_activities', array('semesterid' => $id, 'eventtypeid' => 2));
        }
    }

// end of function

    /**
     * @method get_dependency_list
     * @todo Checks the Semester dependency modules
     * @param  program(int)
     * @return int
     * */
    function get_dependency_list($semester) {
        global $DB, $CFG;
        $today = date('Y-m-d');
        //check whether any class is created under the semester.
        $class = $DB->get_records('local_clclasses', array('semesterid' => $semester));
        if (!empty($class))
            return 1;
        //check if registration is enabled for the semester.
        $sql = "SELECT * FROM {local_event_activities} WHERE eventtypeid = 2 AND semesterid = {$semester} AND {$today} BETWEEN from_unixtime(startdate, '%Y-%m-%d') AND from_unixtime(enddate, '%Y-%m-%d') AND publish = 1";
        $event = $DB->get_records_sql($sql);
        if (!empty($event))
            return 2;
        return 0;
    }

    /**
     * @method createtabview
     * @todo provides the tab view
     * @param  currenttab(string)
     * @return display tab view
     * */
    function createtabview($currenttab, $id = -1) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $tabs = array();
        // $string = ($id > 0) ? get_string('editsemester', 'local_semesters') : get_string('createsemester', 'local_semesters');
        if ($id > 0) {
            $semestercreate_cap = array('local/semesters:manage', 'local/semesters:update');
            if (has_any_capability($semestercreate_cap, $systemcontext))
                $tabs[] = new tabobject('create', new moodle_url('/local/semesters/semester.php'), get_string('editsemester', 'local_semesters'));
        }
        else {
            $semesterupdate_cap = array('local/semesters:manage', 'local/semesters:create');
            if (has_any_capability($semesterupdate_cap, $systemcontext))
                $tabs[] = new tabobject('create', new moodle_url('/local/semesters/semester.php'), get_string('createsemester', 'local_semesters'));
        }

        $tabs[] = new tabobject('all', new moodle_url('/local/semesters/index.php'), get_string('viewsemesters', 'local_semesters'));
        $tabs[] = new tabobject('current', new moodle_url('/local/semesters/index.php', array('mode' => 'current')), get_string('current', 'local_semesters'));
        $tabs[] = new tabobject('upcoming', new moodle_url('/local/semesters/index.php', array('mode' => 'upcoming')), get_string('coming', 'local_semesters'));
        $tabs[] = new tabobject('help', new moodle_url('/local/semesters/info.php'), get_string('help', 'local_semesters'));
        $tabs[] = new tabobject('report', new moodle_url('/local/semesters/report.php'), get_string('report', 'local_semesters'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /**
     * @method remove_assignedschool
     * @todo removes already assigned schools from dropdown list
     * @param  schools(array)
     * @param  semester(int)
     * @returns list of unassigned schools in the form of array
     * */
    function remove_assignedschool($schools, $semester) {
        global $CFG, $DB;
        foreach ($schools as $id => $school) {
            $check = $DB->get_record('local_school_semester', array('schoolid' => $id, 'semesterid' => $semester));
            if (!empty($check)) {
                unset($schools[$id]);
            }
        }
        return $schools;
    }

    /**
     * @method get_assignedschools
     * @todo get the assigned schools for the semester
     * @param  semester(int)
     * @param  hierarchy is the object for the 'hierarchy' class
     * @returns list of assigned schools in the form of array
     * */
    function get_assignedschools($semester, $hierarchy) {
        global $CFG, $DB;
        $schoolid = array();
        $schoolname = array();
        $schools = $DB->get_records('local_school_semester', array('semesterid' => $semester));
        foreach ($schools as $school) {
            $schoolid[$school->schoolid] = $school->schoolid;
            $schoolname[] = $DB->get_field('local_school', 'fullname', array('id' => $school->schoolid));
        }
        if (empty($hierarchy))
            return implode(', ', $schoolname) . '.';

        $totalschools = sizeof($hierarchy->get_school_items());
        $assignedschools = sizeof($schoolid);

        //if total no.of schools are equal to assigned schools for the semester
        //unset all the values and make 'All' active
        if ($totalschools == $assignedschools) {
            foreach ($schoolid as $k => $v) {
                unset($schoolid[$k]);
            }
            $schoolid[0] = 0;
        }
        return $schoolid;
    }

    /**
     * @method get_listofsemesters
     * @todo to get all semester list based on mode(current,upcoming and report)
     * @param  mode(string)
     * @param  schoolin(string)
     * @returns array of objects (semester list)
     * */
    function get_listofsemesters($mode, $schoolin) {
        global $DB;
        /* Bug report #270  -  Semesters>Multiple Schools>View and Filters- Not displayed
         * @author hemalatha c arun <hemalatha@eabyas.in> 
         * Resolved- fetching all semester school data, when single semester assigned to mutliple school
         */
        $today = date('Y-m-d');
        $mysql = "SELECT scl.* ,sem.fullname as fullname FROM {local_semester} AS sem
                        JOIN {local_school_semester} AS scl
                        ON scl.semesterid = sem.id";
        $where = " WHERE scl.schoolid IN ($schoolin)";
        if ($mode == 'current') {
            //get the list of current semesters
            $where .= " AND  '{$today}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' )";
        }
        if ($mode == 'upcoming') {
            //get the list of upcoming semesters
            $where .= "  AND '{$today}' < from_unixtime( sem.startdate,  '%Y-%m-%d' )";
        }
        if ($mode == 'report') {
            $where .= "  AND from_unixtime(startdate,  '%Y-%m-%d' ) <= '{$today}'";
        }
        $group = " group by scl.id ORDER BY sem.startdate DESC";
        // echo $mysql . $where . $group;
        $result = $DB->get_records_sql($mysql . $where . $group);
        //print_object($result);
        return $DB->get_records_sql($mysql . $where . $group);
    }

    /**
     * @method names
     * @todo to get school information
     * @param  list(object)
     * @returns object of school info
     * */
    function names($list) {
        global $DB, $CFG;
        $name = new stdClass();
        $name->school = $this->get_assignedschools($list->id, '');
        $name->startdate = date('d-M-Y', $list->startdate);
        $name->enddate = date('d-M-Y', $list->enddate);
        return $name;
    }

    /**
     * @method  program_capabilities
     * @todo used to provide default capabilities list
     * @param array $unsetlist(used to remove capability from default list)
     * @return array capabilities list
     * */
    function semester_capabilities($unsetlist = null) {
        global $DB, $CFG;
        $capabilities_array = array('local/semesters:manage', 'local/semesters:delete', 'local/semesters:update', 'local/semesters:visible', 'local/semesters:view', 'local/semesters:create');
        if ($unsetlist) {
            foreach ($unsetlist as $key => $value)
                $updatedunsetlist[] = 'local/semesters:' . $value;
            $capabilities_array = array_diff($capabilities_array, $updatedunsetlist);
        }

        return $capabilities_array;
    }

// end of function
}

// end of class
?>
