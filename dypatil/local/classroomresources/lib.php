<?php

require_once($CFG->dirroot . '/local/lib.php');
/*
 * Resources class contains library functions
 * */

class cobalt_resources {

    //Declaring static object variable
    private static $resource;

    private function __construct() {
        
    }

    /**
     * We are using singleton for this class 
     * @method get_instance
     * @todo get object for cobalt_resources class
     * @return object of this class
     * */
    public static function get_instance() {
        if (!self :: $resource) {
            self :: $resource = new cobalt_resources();
        }
        return self :: $resource;
    }

    /**
     * @method cobalt_building
     * @to get list of buildings of schools (particular logged in registrar)
     * @return array of objects(list of buildings)
     * */
    public function cobalt_building() {
        global $DB, $USER;
        if (is_siteadmin()) {
            $sql = "SELECT b.*,s.fullname as schoolname FROM {local_school} AS s,{local_building} AS b
	  WHERE b.schoolid=s.id";
        } else {
            $sql = "SELECT b.*,s.fullname as schoolname FROM {local_school_permissions} AS sp
	        INNER JOIN {local_school} AS s 
			ON sp.schoolid=s.id 
			INNER JOIN {local_building} AS b
			ON b.schoolid=s.id where sp.userid={$USER->id} AND sp.value=1 AND s.visible=1
	        ";
        }
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * @method cobalt_building_delete
     * @param  $id(int)
     * @to delete building(if building will deleted at the same time delete floors,classrooms under particular building)
     * @return boolean type of value
     * */
    public function cobalt_building_delete($id) {
        global $DB, $USER;
        $DB->delete_records('local_building', array('id' => $id));
        $DB->delete_records('local_floor', array('buildingid' => $id));
        $DB->delete_records('local_classroom', array('buildingid' => $id));
        $DB->delete_records('local_classroomresources', array('buildingid' => $id));
        return true;
    }

    /**
     * @method cobalt_floor
     * @to get list of floors of schools (particular logged in registrar)
     * @return array, floor list
     * */
    public function cobalt_floor() {
        global $DB, $USER;
        if (is_siteadmin()) {
            $sql = "SELECT f.*,s.fullname as schoolname,b.fullname as buildingname 
	           FROM {local_school} AS s,{local_building} AS b,{local_floor} AS f
			   WHERE  b.schoolid=s.id AND b.id=f.buildingid ";
        } else {
            $sql = "SELECT f.*,s.fullname as schoolname,b.fullname as buildingname 
	        FROM {local_school_permissions} AS sp
	        INNER JOIN {local_school} AS s 
			ON sp.schoolid=s.id 
			INNER JOIN {local_building} AS b
			ON b.schoolid=s.id INNER JOIN {local_floor} AS f
			ON b.id=f.buildingid
			where sp.userid={$USER->id} AND sp.value=1 AND s.visible=1
	        ";
        }
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * @method cobalt_floor_delete
     * @param  $id(int)
     * @to delete floor(if floor will deleted at the same time delete classrooms under that floor)
     * */
    public function cobalt_floor_delete($id) {
        global $DB, $USER;
        $DB->delete_records('local_floor', array('id' => $id));
        $DB->delete_records('local_classroom', array('floorid' => $id));
        $DB->delete_records('local_classroomresources', array('floorid' => $id));
        return true;
    }

    /**
     * @method cobalt_classroom
     * @todo get list of classrooms of school(logged in registrar)
     * */
    public function cobalt_classroom() {
        global $DB, $USER;
        if (is_siteadmin()) {
            $sql = "SELECT c.*,s.fullname as schoolname,b.fullname as buildingname,f.fullname as floorname
	        FROM {local_school} AS s,{local_building} AS b,{local_floor} AS f,{local_classroom} AS c
			WHERE  s.id=b.schoolid AND
			b.id=f.buildingid AND
		    f.id=c.floorid ";
        } else {
            $sql = "SELECT c.*,s.fullname as schoolname,b.fullname as buildingname,f.fullname as floorname
	        FROM {local_school_permissions} AS sp
	        INNER JOIN {local_school} AS s 
			ON sp.schoolid=s.id 
			INNER JOIN {local_building} AS b
			ON b.schoolid=s.id INNER JOIN {local_floor} AS f
			ON b.id=f.buildingid INNER JOIN {local_classroom} AS c
			ON f.id=c.floorid
			where sp.userid={$USER->id} AND sp.value=1 AND s.visible=1
	        ";
        }
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * @method cobalt_class_delete
     * @param id(int)
     * @todo delete classroom(if classroom deleted resouces assigned to classroom also deleted)
     * */
    public function cobalt_class_delete($id) {
        global $DB, $USER;
        $DB->delete_records('local_classroom', array('id' => $id));
        $DB->delete_records('local_classroomresources', array('classroomid' => $id));
        return true;
    }

    /**
     * @method cobalt_resource
     * @to get list of resources created under schools(based on logged in registrar)
     * */
    public function cobalt_resource() {
        global $DB, $USER;
        if (is_siteadmin()) {
            $sql = "SELECT r.*,s.fullname as schoolname FROM 
	        {local_school} AS s,{local_resource} AS r WHERE
			r.schoolid=s.id ";
        } else {
            $sql = "SELECT r.*,s.fullname as schoolname FROM {local_school_permissions} AS sp
	        INNER JOIN {local_school} AS s 
			ON sp.schoolid=s.id 
			INNER JOIN {local_resource} AS r
			ON r.schoolid=s.id where sp.userid={$USER->id} AND sp.value=1 AND s.visible=1";
        }
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * @method cobalt_resource_delete
     * @param id(int)
     * @to delete resource
     * */
    public function cobalt_resource_delete($id) {
        global $DB, $USER;
        $DB->delete_records('local_resource', array('id' => $id));
        return true;
    }

    /**
     * @method assignresource_tabs
     * @param currenttab(string)
     * @to get tabs 
     * */
    public function assignresource_tabs($currenttab) {
        global $OUTPUT, $DB;
        $toprow = array();
        $toprow[] = new tabobject('creater', new moodle_url('/local/classroomresources/assignresource.php'), get_string('creater', 'local_classroomresources'));
        $toprow[] = new tabobject('viewr', new moodle_url('/local/classroomresources/view.php'), get_string('viewr', 'local_classroomresources'));


        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method assignedresourcelist
     * @to get list of resources assigned to classroom(based on logged in registrar)
     * @return array, resources list
     * */
    public function assignedresourcelist() {
        global $DB, $USER;
        if (is_siteadmin()) {
            $sql = "SELECT * FROM {local_classroomresources} ";
        } else {
            $sql = "SELECT cr.* FROM {local_school_permissions} AS sp
	        INNER JOIN {local_classroomresources} AS cr 
	        ON sp.schoolid=cr.schoolid WHERE sp.userid={$USER->id} AND sp.value=1";
        }
        $result = $DB->get_records_sql($sql);
        return $result;
    }

    /**
     * @method get_resource_name
     * @to get list of resource names 
     * */
    public function get_resource_name($id) {
        global $DB, $CFG;
        $i = 0;
        $resourceid = array();
        $resourceid = explode(",", $id);
        foreach ($resourceid as $rid) {
            $sql = "SELECT fullname from {local_resource} WHERE id={$rid} ";
            $query = $DB->get_records_sql($sql);
            foreach ($query as $q) {
                $resourceid[$i] = $q->fullname;
            }
            $i++;
        }
        $result = implode(",", $resourceid);
        return $result;
    }

    /**
     * @method cobalt_delete_list
     * @param classroomid(int)
     * @to get list of resources assigned to classroom
     * */
    public function cobalt_delete_list($id) {
        global $DB, $CFG;
        $DB->delete_records('local_classroomresources', array('id' => $id));
        return true;
    }

    /**
     * @method building_tabs
     * @param currenttab(string)
     * @to get building tabs 
     * */
    public function building_tabs($currenttab, $id) {

        global $OUTPUT, $DB;
        $toprow = array();
        $systemcontext = context_system::instance();
        $string = ($id > 0) ? get_string('editbuilding', 'local_classroomresources') : get_string('create', 'local_classroomresources');
        if (has_capability('local/classroomresources:manage', $systemcontext))
            $toprow[] = new tabobject('create', new moodle_url('/local/classroomresources/building.php'), $string);
        $toprow[] = new tabobject('view', new moodle_url('/local/classroomresources/index.php'), get_string('view', 'local_classroomresources'));
        $toprow[] = new tabobject('info', new moodle_url('/local/classroomresources/infobuilding.php'), get_string('info', 'local_classroomresources'));


        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method floor_tabs
     * @param currenttab(string)
     * @to get floor tabs 
     * */
    public function floor_tabs($currenttab, $id) {
        global $OUTPUT, $DB;
        $toprow = array();
        $systemcontext = context_system::instance();
        $string = ($id > 0) ? get_string('editfloor', 'local_classroomresources') : get_string('createfloor', 'local_classroomresources');
        if (has_capability('local/classroomresources:manage', $systemcontext))
            $toprow[] = new tabobject('createfloor', new moodle_url('/local/classroomresources/floor.php'), $string);
        $toprow[] = new tabobject('viewfloor', new moodle_url('/local/classroomresources/viewfloor.php'), get_string('viewfloor', 'local_classroomresources'));
        $toprow[] = new tabobject('info', new moodle_url('/local/classroomresources/infofloor.php'), get_string('info', 'local_classroomresources'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method class_tabs
     * @param currenttab(string)
     * @to get class tabs 
     * */
    public function class_tabs($currenttab, $id) {
        global $OUTPUT, $DB;
        $systemcontext = context_system::instance();
        $toprow = array();
        if (has_capability('local/classroomresources:manage', $systemcontext)) {
            if ($id < 0)
                $toprow[] = new tabobject('createclassroom', new moodle_url('/local/classroomresources/classroom.php'), get_string('createclassroom', 'local_classroomresources'));
            else
                $toprow[] = new tabobject('createclassroom', new moodle_url('/local/classroomresources/classroom.php'), get_string('editclassroom', 'local_classroomresources'));
        }
        $toprow[] = new tabobject('viewclassroom', new moodle_url('/local/classroomresources/viewclassroom.php'), get_string('viewclassroom', 'local_classroomresources'));
        $toprow[] = new tabobject('info', new moodle_url('/local/classroomresources/infoclassroom.php'), get_string('info', 'local_classroomresources'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method resource_tabs
     * @param currenttab(string)
     * @to get resource tabs 
     * */
    public function resource_tabs($currenttab, $id) {
        global $OUTPUT, $DB;
        $systemcontext = context_system::instance();
        $toprow = array();
        if (has_capability('local/classroomresources:manage', $systemcontext)) {
            if ($id < 0)
                $toprow[] = new tabobject('createresource', new moodle_url('/local/classroomresources/resource.php'), get_string('createresource', 'local_classroomresources'));
            else
                $toprow[] = new tabobject('createresource', new moodle_url('/local/classroomresources/resource.php'), get_string('editresource', 'local_classroomresources'));
        }
        $toprow[] = new tabobject('viewresource', new moodle_url('/local/classroomresources/viewresource.php'), get_string('viewresource', 'local_classroomresources'));
        $toprow[] = new tabobject('creater', new moodle_url('/local/classroomresources/assignresource.php'), get_string('creater', 'local_classroomresources'));
        $toprow[] = new tabobject('viewr', new moodle_url('/local/classroomresources/view.php'), get_string('viewr', 'local_classroomresources'));
        $toprow[] = new tabobject('info', new moodle_url('/local/classroomresources/inforesource.php'), get_string('info', 'local_classroomresources'));


        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

}

/*
 * scheduleclass class contains library functions
 * */

class cobalt_scheduleclass {

    //Declaring static object variable
    private static $scheduleclass;

    private function __construct() {
        
    }

    /**
     * We are using singleton for this class 
     * @method get_instance
     * @todo get object for cobalt_scheduleclass class
     * @return object of this class
     * */
    public static function get_instance() {
        if (!self :: $scheduleclass) {
            self :: $scheduleclass = new cobalt_scheduleclass();
        }
        return self :: $scheduleclass;
    }

    /*
     * @This function returns hours as array
     * */

    public function hour() {
        $hour = array();
        $hour[null] = 'Hour';
        for ($i = 0; $i <= 24; $i++) {
            if ($i < 10) {
                $hour['0' . $i] = '0' . $i;
            } else {
                $hour[$i] = $i;
            }
        }
        return $hour;
    }

    /*
     * @sThis function returns minutes as array
     * */

    public function min() {
        $min = array();
        $min[null] = 'Min';
        for ($i = 0; $i <= 59; $i++) {
            if ($i < 10) {
                $min['0' . $i] = '0' . $i;
            } else {
                $min[$i] = $i;
            }
        }
        return $min;
    }

    /**
     * @This function takes startdate,enddate,starttime,endtime,schoolid
     * @This function returns list of classrooms which are avaliable in that school at that particular timings
     * */
    public function classroomlist($startdate, $enddate, $starttime, $endtime, $schoolid, $id) {
        global $CFG, $DB, $USER;


        $room = array();
        $allrooms = array();
        $i = 0;
        $j = 0;
        if ($startdate['day'][0] <= 9) {
            $startdate['day'][0] = '0' . $startdate['day'][0];
        }
        if ($enddate['day'][0] <= 9) {
            $enddate['day'][0] = '0' . $enddate['day'][0];
        }
        $startdate = $startdate['day'][0] . '-' . $startdate['month'][0] . '-' . $startdate['year'][0];
        $enddate = $enddate['day'][0] . '-' . $enddate['month'][0] . '-' . $enddate['year'][0];
        if ($starttime['starthour'][0] == null) {
            $starttime['starthour'][0] = 0;
        }
        if ($starttime['startmin'][0] == null) {
            $starttime['startmin'][0] = 0;
        }
        if ($endtime['endhour'][0] == null) {
            $endtime['endhour'][0] = 0;
        }
        if ($endtime['endmin'][0] == null) {
            $endtime['endmin'][0] = 0;
        }
        $st = $starttime['starthour'][0] . ':' . $starttime['startmin'][0];
        $et = $endtime['endhour'][0] . ':' . $endtime['endmin'][0];
        //To get busy classrooms
        $sql = "SELECT ls.id,ls.classroomid,ls.instructorid,
		          FROM_UNIXTIME(ls.startdate,'%d-%c-%Y') as startdate,
		          FROM_UNIXTIME(ls.enddate,'%d-%c-%Y') as enddate
		          FROM {local_scheduleclass} AS ls INNER JOIN {local_classroom} AS c ON ls.classroomid=c.id
			      WHERE c.schoolid={$schoolid} AND
                  starttime BETWEEN '{$st}' AND '{$et}' OR 
			      endtime BETWEEN '{$st}' AND '{$et}' ";
        $classrooms = $DB->get_records_sql($sql);
        foreach ($classrooms as $classroom) {


            if ((($classroom->startdate <= $startdate) && ($classroom->enddate >= $startdate)) ||
                    (($classroom->startdate >= $enddate) && ($classroom->enddate <= $enddate))) {

                $room[$i] = $classroom->classroomid;
                $i++;
            }
        }

        //To get all classrooms
        $classroom = "SELECT id FROM {local_classroom} WHERE schoolid={$schoolid}";
        $classrooms = $DB->get_records_sql($classroom);
        foreach ($classrooms as $cms) {
            $allrooms[$j] = $cms->id;
            $j++;
        }

        $result = array_diff($allrooms, $room);
        $list = array();
        if ($id < 0) {
            $list[null] = get_string('slclassroom', 'local_classroomresources');
        }

        foreach ($result as $id) {
            $name = $DB->get_record('local_classroom', array('id' => $id));
            $building = $DB->get_field('local_building', 'fullname', array('id' => $name->buildingid));
            $floor = $DB->get_field('local_floor', 'fullname', array('id' => $name->floorid));
            $list[$id] = $building . '/' . $floor . '/' . $name->fullname;
        }
        return $list;
    }

}

/**
 * @Tabs for schedule class
 * @This function takes currenttab as parameter
 * @This function returns tabs
 * */
function scheduleclass_tabs($currenttab) {
    global $OUTPUT;
    $toprow = array();
    $toprow[] = new tabobject('create', new moodle_url('/local/clclasses/classes.php'), get_string('create', 'local_clclasses'));
    $toprow[] = new tabobject('view', new moodle_url('/local/clclasses/index.php'), get_string('view', 'local_clclasses'));
    $toprow[] = new tabobject('assignmodules', new moodle_url('/local/clclasses/assignmodules.php'), get_string('assigninstructor', 'local_clclasses'));
    $toprow[] = new tabobject('scheduleclass', new moodle_url('/local/classroomresources/scheduleclass.php'), get_string('scheduleclass', 'local_classroomresources'));
    $toprow[] = new tabobject('info', new moodle_url('/local/clclasses/info.php'), get_string('info', 'local_clclasses'));
    $toprow[] = new tabobject('reports', new moodle_url('/local/clclasses/report.php'), get_string('reports', 'local_clclasses'));
    echo $OUTPUT->tabtree($toprow, $currenttab);
}

/**
 * @method scheduleclass_tabs
 * @param string $currenttab For Active tab
 * @return Renders the scheduleclass tabs
 * */
function schedule_class_delete($id) {
    global $CFG, $USER, $DB;
    $DB->delete_records('local_scheduleclass', array('id' => $id));
}

/**
 * @method cobalt_get_my_timetable
 * @return Classes time tables upto present date for logged in instructor
 */
function cobalt_get_my_timetable() {
    global $CFG, $USER, $DB;
    $today = strtotime(date('Y-m-d'));
    $sql = "SELECT id,starttime,endtime,classid,classroomid,
	      FROM_UNIXTIME(startdate,'%d-%M-%Y') as startdate,
	      FROM_UNIXTIME(enddate,'%d-%M-%Y') as enddate 
 	      FROM {local_scheduleclass} WHERE instructorid={$USER->id} AND visible=1 AND enddate >={$today}";
    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method cobalt_student_timetable
 * @param int $semid SemesterID
 * @return Classes time tables upto present date for particular user and semester
 */
function cobalt_student_timetable($semid) {
    global $CFG, $USER, $DB;
    $todays = date('Y-m-d');
    $today = strtotime(date('Y-m-d'));


    $sql = "SELECT s.id,s.starttime,s.endtime,s.classid,s.classroomid,
	      FROM_UNIXTIME(s.startdate,'%d-%b-%Y') as startdate,
	      FROM_UNIXTIME(s.enddate,'%d-%b-%Y') as enddate,
		  s.instructorid
       	  FROM {local_user_clclasses} AS uc 
	      INNER JOIN {local_scheduleclass} AS s 
		  ON uc.classid=s.classid WHERE 
		  uc.userid={$USER->id} AND s.visible=1 AND s.enddate >={$today}
		  AND uc.registrarapproval=1 AND uc.semesterid={$semid}";

    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method class_overview
 * @param int $classid ClassID
 * @param int $semid SemesterID
 * @param int $schoolid SchoolID
 * @retrun string renders teh schedule time
 */
function class_overview($classid, $semid, $schoolid) {
    global $CFG, $USER, $DB, $OUTPUT;
    $sql = "SELECT id,FROM_UNIXTIME(startdate,'%d-%m-%Y') as startdate,
	      FROM_UNIXTIME(enddate,'%d-%m-%Y') as enddate,starttime,endtime,classroomid,visible FROM {local_scheduleclass} WHERE classid={$classid}";
    $records = $DB->get_records_sql($sql);
    if (empty($records)) {
        $sheduletime = '';
        $sheduletime.=html_writer::tag('a', get_string('scheduleclass', 'local_classroomresources'), array('href' => '' . $CFG->wwwroot . '/local/classroomresources/scheduleclass.php?classid=' . $classid . '&semid=' . $semid . '&schoolid=' . $schoolid . ''));
        return $sheduletime;
    } else {
        foreach ($records as $rec) {
            $room = $DB->get_field('local_classroom', 'fullname', array('id' => $rec->classroomid));

            $sheduletime = '';
            $sheduletime .='<p>' . $rec->startdate . '-' . $rec->enddate . '</p>';
            $sheduletime .=$rec->starttime . '-' . $rec->endtime;
            $sheduletime .=$room;

            $sheduletime .= html_writer::link(new moodle_url('/local/classroomresources/scheduleclass.php', array('id' => $rec->id, 'sesskey' => sesskey())), html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/edit'), 'title' => get_string('edit'), 'alt' => get_string('edit'), 'class' => 'iconsmall')));
        }
        return $sheduletime;
    }
}

/**
 * @method check_classroom
 * @param int $classroomid Classroom ID
 * @return boolean Class scheduled or not
 */
function check_classroom($classroomid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT id FROM {local_scheduleclass} WHERE classroomid={$classroomid}";
    $query = $DB->get_records_sql($sql);
    if (empty($query)) {
        $value = 0;
    } else {
        $value = 1;
    }
    return $value;
}

/**
 * @method check_floor
 * @param int $floorid Floor ID
 * @return boolean Class scheduled or not for floor
 */
function check_floor($floorid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT s.id FROM {local_classroom} AS c,{local_scheduleclass} AS s 
	      WHERE c.floorid={$floorid} AND c.id=s.classroomid";
    $query = $DB->get_records_sql($sql);
    if (empty($query)) {
        $value = 0;
    } else {
        $value = 1;
    }
    return $value;
}

/**
 * @method check_building
 * @param int $buildingid Building ID
 * @return boolean Class scheduled or not for building
 */
function check_building($buildingid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT s.id FROM {local_classroom} AS c,{local_scheduleclass} AS s 
	      WHERE c.buildingid={$buildingid} AND c.id=s.classroomid";
    $query = $DB->get_records_sql($sql);
    if (empty($query)) {
        $value = 0;
    } else {
        $value = 1;
    }
    return $value;
}

/**
 * @method check_resource
 * @param int $id Resourse ID
 * @return boolean Class scheduled or not for resource
 */
function check_resource($id) {
    global $CFG, $DB, $USER;
    $sql = "SELECT s.id FROM {local_classroomresources} AS cr,{local_scheduleclass} As s
	      WHERE cr.id={$id} AND cr.classroomid=s.classroomid";
    $query = $DB->get_records_sql($sql);
    if (empty($query)) {
        $value = 0;
    } else {
        $value = 1;
    }
    return $value;
}
