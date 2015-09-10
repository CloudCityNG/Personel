<?php

/**
 * @method insert_cobaltcourses
 * @todo Inserts the details of the Cobalt Courses in the Table
 * @param  $data(array)
 * @return Id of the inserted data
 * */
function insert_cobaltcourses($data) {
    global $DB;
    return $DB->insert_record('local_cobaltcourses', $data);
}

/**
 * @method update_cobaltcourses
 * @todo Update the details of the existing Cobalt Courses in the Table
 * @param  $data(object)
 * @return updated record ID
 * */
function update_cobaltcourses($data) {
    global $DB;
    return $DB->update_record('local_cobaltcourses', $data);
}

/**
 * @method delete_cobaltcourses
 * @todo Delete the details Cobalt Courses in the Table
 * @param  $id(int)
 * @return void
 * */
function delete_cobaltcourses($id) {
    global $DB;
    return $DB->delete_records('local_cobaltcourses', array('id' => $id));
}

/**
 * @method get_course_curriculum
 * @todo Get the records of a course is assigned to a curriculum
 * @param $course(int)
 * @return count of curriculums to which course is assigned
 * */
function get_course_curriculum($course) {
    global $CFG, $DB, $USER;
    $sql = "SELECT cm.* FROM {$CFG->prefix}local_cobaltcourses AS cc
                INNER JOIN {$CFG->prefix}local_module_course AS mc
                ON mc.courseid = cc.id
                INNER JOIN {$CFG->prefix}local_curriculum_modules AS cm
                ON cm.moduleid = mc.moduleid
                WHERE cc.id = $course";
    $curriculums = $DB->get_records_sql($sql);
    return sizeof($curriculums);
}

/**
 * @method get_course_dependencies
 * @todo to check course is assigned to any curriculum
 * @param $course(int)
 * @return int based on condition.
 * */
function get_course_dependencies($course) {
    global $DB, $CFG;
    /* ---Check if any student is enrolled to this course in current semester--- */
    $today = date('Y-m-d');
    $semesters = $DB->get_records_select('local_semester', "{$today} BETWEEN from_unixtime(startdate, '%Y-%m-%d') AND from_unixtime(enddate, '%Y-%m-%d')");
    if ($semesters) {
        $semesterin = implode(',', array_keys($semesters));
        if ($clclasses = $DB->get_records_select('local_clclasses', "cobaltcourseid = {$course} AND semesterid IN ($semesterin)")) {
            $classin = implode(',', array_keys($clclasses));
            $enrolled = $DB->get_records_select('local_user_clclasses', "classid IN ($classin) AND registrarapproval=1");
            if ($enrolled)
                return 3;
        }
    }

    $curriculums = $DB->get_records('local_curriculum_plancourses', array('courseid' => $course));
    if (!empty($curriculums))
        return 1;

    $clclasses = $DB->get_records('local_clclasses', array('cobaltcourseid' => $course));
    if (!empty($clclasses))
        return 2;

    return 0;
}

/**
 * @method get_courses_department
 * @todo Get the records of a course is assigned to a department
 * @param $department(int), $school(int)
 * @return list of courses in the format of array
 * */
function get_courses_department($department, $school, $multi = 0) {
    global $CFG, $DB, $USER;
    $hierarchy = new hierarchy();    
    if ($multi) {
        $courselist = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', "departmentid = $department AND schoolid = $school AND visible = 1 AND id != $multi", null, '', 'id,fullname');
    } else {
        $courselist = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', "departmentid = $department AND schoolid = $school AND visible = 1", null, '', 'id,fullname', 'Select Course');
    }    
    return $courselist;
}

/**
 * @method insert_equivalentcourse
 * @todo Inserts the details of the Equivalent courses for Cobaltcourses in the Table
 * @param  $data(array)
 * @return Id of the inserted data
 * */
function insert_equivalentcourse($data) {
    global $DB;
    $data->id = $DB->insert_record('local_course_equivalent', $data);
    return $data->id;
}

/**
 * @method delete_equivalentcourse
 * @todo Delete the details Equivalent Courses for the cobalt courses
 * @param  $id(int)
 * */
function delete_equivalentcourse($id) {
    global $DB;
    return $DB->delete_records('local_course_equivalent', array('id' => $id));
}

/**
 * @method insert_prerequisitecourse
 * @todo Inserts the details of the Prerequisite courses for Cobaltcourses in the Table
 * @param  $data(array)
 * @return Id of the inserted data
 * */
function insert_prerequisitecourse($data) {
    global $DB;
    $data->id = $DB->insert_record('local_course_prerequisite', $data);
    return $data->id;
}

/**
 * @function delete_prerequisitecourse
 * @todo Delete the details Prerequisite Courses for the cobalt courses
 * @param  $id(int)
 * */
function delete_prerequisitecourse($id) {
    global $DB;
    return $DB->delete_records('local_course_prerequisite', array('id' => $id));
}

/*
  Getting departments for given schoolid
 */
function get_departments($id) {
    global $DB;
    $results = $DB->get_records('local_department', array('schoolid' => $id));
    return $results;
}

function get_snames() {
    global $DB;
    $results = $DB->get_records('local_cobaltcourses');
    return $results;
}

/**
 * @method createtabview
 * @todo provides the tab view
 * @param  currenttab(string)
 * */
function createtabview($currenttab, $id = -1) {
    global $OUTPUT;
    $systemcontext = context_system::instance();
    $tabs = array();
//---------------------Edited by hema-------------------------------------------------------
//$string = ($id > 0) ? get_string('editcourse', 'local_cobaltcourses') : get_string('createcourse', 'local_cobaltcourses') ;
    if ($id > 0) {
        $update_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:update');
        if (has_any_capability($update_cap, $systemcontext))
            $tabs[] = new tabobject('create', new moodle_url('/local/cobaltcourses/cobaltcourse.php'), get_string('editcourse', 'local_cobaltcourses'));
    }
    else {
        $create_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:create');
        if (has_any_capability($create_cap, $systemcontext))
            $tabs[] = new tabobject('create', new moodle_url('/local/cobaltcourses/cobaltcourse.php'), get_string('createcourse', 'local_cobaltcourses'));
    }

    $tabs[] = new tabobject('view', new moodle_url('/local/cobaltcourses/index.php'), get_string('courselist', 'local_cobaltcourses'));

    $prerequisite_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:courseprerequisiteassign');
    if (has_any_capability($prerequisite_cap, $systemcontext))
        $tabs[] = new tabobject('prerequisite', new moodle_url('/local/cobaltcourses/prerequisite.php'), get_string('prerequisite', 'local_cobaltcourses'));

    $equivalent_cap = array('local/cobaltcourses:manage', 'local/cobaltcourses:courseequivalentassign');
    if (has_any_capability($equivalent_cap, $systemcontext))
        $tabs[] = new tabobject('equivalent', new moodle_url('/local/cobaltcourses/equivalent.php'), get_string('equivalent', 'local_cobaltcourses'));

    $tabs[] = new tabobject('upload', new moodle_url('/local/cobaltcourses/upload.php'), get_string('uploadcourses', 'local_cobaltcourses'));
    $tabs[] = new tabobject('information', new moodle_url('/local/cobaltcourses/info.php'), get_string('help', 'local_cobaltcourses'));
    $tabs[] = new tabobject('report', new moodle_url('/local/cobaltcourses/report.php'), get_string('report', 'local_cobaltcourses'));

    echo $OUTPUT->tabtree($tabs, $currenttab);
}

/**
 * @function get_enroll_reports
 * @todo gets total no.of enrollments for the particular cobalt course
 * @return array of objects(list enrolled user)
 * */
function get_enroll_reports() {
    global $DB, $CFG;
    $sql = "SELECT course.*, count(user.userid) AS count
                    FROM {local_user_clclasses} AS user
                    JOIN {local_clclasses} AS class ON class.id = user.classid
                    JOIN {local_cobaltcourses} AS course ON course.id = class.cobaltcourseid
                    WHERE user.registrarapproval = 1 GROUP BY class.cobaltcourseid";
    $sql1 = "SELECT class.cobaltcourseid, course.shortname, course.fullname, count(user.userid) AS count, course.departmentid
                FROM {local_user_clclasses} AS user
                INNER JOIN {local_clclasses} AS class
                ON class.id = user.classid
                INNER JOIN {local_cobaltcourses} AS course
                ON course.id = class.cobaltcourseid
                WHERE user.registrarapproval=1
                GROUP BY  class.cobaltcourseid";
    return $DB->get_records_sql($sql);
}

/**
 * @method get_programdetails
 * @param int $course CourseID
 * @todo get program details for the particular course(Separates the total no.of course enrolments programwise )
 * */
function get_programdetails($course) {
    global $DB, $CFG;
    $u = array();
    $mysql = "SELECT user.id, user.userid FROM {local_user_clclasses} AS user
                        JOIN {local_clclasses} AS class ON class.id = user.classid
                        WHERE user.registrarapproval = 1 AND class.cobaltcourseid = {$course}";
    $enrolled_users = $DB->get_records_sql($mysql);
    foreach ($enrolled_users as $enrolled_user) {
        $u[$enrolled_user->userid] = $enrolled_user->userid;
    }
    $useridin = array_keys($u);
    list($usql, $params) = $DB->get_in_or_equal($useridin);
    $programs = $DB->get_records_sql("SELECT data.*, count(data.userid) AS size FROM {local_userdata} AS data
                                              JOIN {local_curriculum_plancourses} AS plan ON plan.curriculumid = data.curriculumid
                                              WHERE plan.courseid = {$course} AND data.userid $usql GROUP BY data.programid", $params);
    $pro = '';
    $i = 1;
    if (empty($programs))
        $pro = 'Out of Program';
    else
        foreach ($programs as $program) {
            $pro .= '<div>' . $DB->get_field('local_program', 'fullname', array('id' => $program->programid)) . ' (' . $program->size . ')';
            $pro .= ($i == sizeof($programs)) ? '' : ', ';
            $pro .= '</div>';
            $i++;
        }
    return $pro;
}

/**
 * @method related_courses
 * @param string $table Table name
 * @param int $course Course ID
 * @todo gets total no.of enrollments for the particular cobalt course
 * */
function related_courses($table, $course) {
    global $DB;
    $lists = $DB->get_records($table, array('courseid' => $course->id, 'schoolid' => $course->schoolid));
    foreach ($lists as $list) {
        $records = isset($list->precourseid) ? explode(',', $list->precourseid) : explode(',', $list->equivalentcourseid);
        ;
        $crs = array();
        foreach ($records as $record) {
            $crs[] = $DB->get_record_sql("SELECT CONCAT('<b>', shortname, ':</b> ', fullname) AS name FROM {local_cobaltcourses} WHERE id = {$record}");
        }
        foreach ($crs as $cr) {
            foreach ($cr as $k => $v) {
                $c[$v] = $v;
            }
        }
    }
    return implode(', ', $c);
}

/**
 * @method departmentfilter
 * @param object $records
 * @param string $table Table name
 * @todo Gets the department select menu filtering
 * */
function departmentfilter($records, $table) {
    global $DB, $CFG;
    list($usql, $params) = $DB->get_in_or_equal($records);
    $records = $DB->get_records_sql("SELECT * FROM {{$table}} WHERE schoolid $usql GROUP BY courseid", $params);
    $d = array('---Select---');
    foreach ($records as $record) {
        $d[$record->departmentid] = $DB->get_field('local_department', 'fullname', array('id' => $record->departmentid));
    }
    return $d;
}

/**
 * @method coursefilter
 * @param object $records
 * @param string $table Table name
 * @todo Gets the course select menu filtering
 * */
function coursefilter($records, $table) {
    global $DB, $CFG;
    list($usql, $params) = $DB->get_in_or_equal($records);
    $records = $DB->get_records_sql("SELECT * FROM {{$table}} WHERE schoolid $usql GROUP BY courseid", $params);
    $c = array('---Select---');
    foreach ($records as $record) {
        $c[$record->courseid] = $DB->get_field('local_cobaltcourses', 'fullname', array('id' => $record->courseid));
    }
    return $c;
}

?>
