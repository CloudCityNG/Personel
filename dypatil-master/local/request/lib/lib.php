<?php

require_once($CFG->dirroot . '/local/lib.php');

class requests {
    /* finding the records of the logged in user */

    /**
     * @method current_user
     * @todo to get logged in user information
     * @return object, user info     
     */
    function current_user() {
        global $USER, $DB;
        $users = $DB->get_record('user', array('id' => $USER->id));
        return $users;
    }

    /**
     * @method users
     * @todo to get user details based on school and loggedin user
     * @param int $school school id
     * @param boolean $id
     * @return object, user info     
     */
    function users($value, $id = false) {

        /* based on the schoolid fetching the user info that are assigned to the user logged in user */
        global $USER, $DB;
        $id = $id ? $id : $USER->id;
        $useres = $DB->get_record_sql("SELECT lu.id,lu.email,CONCAT(lu.firstname,' ',lu.lastname) as fullname 
                                        FROM {local_user_clclasses} lus 
					INNER JOIN {local_userdata} lud ON lus.userid = lud.userid 
					INNER JOIN {user} lu ON lud.userid = lu.id and lud.userid = {$id} 
					where lud.schoolid = {$value} group by lu.id");
        return $useres;
    }

    /**
     * @method service
     * @todo fetching the serviceid of the user based on the schoolid
     * @param int $value school id
     * @param boolean $id
     * @param int $program program id
     * @return object, user serviceid detail    
     */
    function service($value, $program, $id = false) {
        global $USER, $DB;
        $id = $id ? $id : $USER->id;
        $serviceid = $DB->get_record_sql("SELECT lud.serviceid FROM {local_userdata} lud 
					  where userid = {$id} and lud.schoolid = {$value} and lud.programid = {$program} group by lud.schoolid");
        return $serviceid;
    }

    /**
     * @method school
     * @todo fetching the schools that are assigned to the logged in user
     * @param boolean $id (if true, going to use parameter value else it takes loggedin user id)  
     * @return array of object, school list  */

      function school($id = false) {
      // fetching the schools that are assigned to the logged in user 
      global $USER, $DB;
      $id = $id ? $id : $USER->id;
      $schools = $DB->get_records_sql("SELECT ls.id,ls.fullname FROM {local_school} ls
      INNER JOIN {local_userdata} lud ON lud.schoolid = ls.id
      where lud.userid = {$id} group by ls.id");
      return $schools;
      } 

    /**
     * @method assigned_school
     * @todo A particular school that is assigned to the user
     * @return array $list Schools select menu
     */
    function assigned_school() {
       
        global $USER, $DB;
        $param = array($USER->id);
        $list = $DB->get_records_sql_menu("SELECT ls.id,ls.fullname FROM {local_school} ls
					 INNER JOIN {local_userdata} lud ON lud.schoolid = ls.id 
					 where lud.userid = ? group by ls.id", $param);
        return $list;
    }

    /**
     * @method program
     * @todo Programs that are under the given school
     * @param int $schoolid School ID
     * @parm int $id User ID
     * @return object $programs Programs list
     */
    function program($value, $id = false) {
       
        global $USER, $DB;
        $id = $id ? $id : $USER->id;
        $programs = $DB->get_records_sql("SELECT lp.id,lp.fullname FROM {local_userdata} lud 
					  INNER JOIN {local_program} lp ON lud.programid = lp.id and lud.userid = {$id} 
					  where lud.schoolid = {$value} and lp.schoolid = {$value} group by lp.id");
        return $programs;
    }

    /**
     * @method semester
     * @todo Fetching the semester according to the program and school
     * @param int $schoolid School ID
     * @param int $programid Program ID
     * @return object $sems Semseters  
     */
    function semester($value, $value1) {
        
        global $USER, $DB;
        $time = time();
        /* current running semester */

        $sems = $DB->get_records_sql("SELECT ls.* FROM {local_user_clclasses} luc 
				      INNER JOIN {local_userdata} lud ON lud.userid = luc.userid   
				      INNER JOIN {local_semester} ls ON luc.semesterid = ls.id 
				      where lud.schoolid = {$value} and ls.startdate<= {$time} and {$time}<=ls.enddate and lud.programid = {$value1} and lud.userid = {$USER->id} group by luc.userid");
        if (!empty($sems))/* checking that any semester is in progress and that is assigned to the logged in user */ {
            return $sems;
        } else {
            /* if not a semester is in progress then show the previous semester that the logged in user is assigned */
            $sems1 = $DB->get_records_sql("SELECT ls.* FROM {local_user_clclasses} luc 
					   INNER JOIN {local_userdata} lud ON lud.userid = luc.userid
					   INNER JOIN {local_semester} ls ON luc.semesterid = ls.id 
					   where lud.schoolid = {$value}  and lud.programid = {$value1} and {$time} > ls.enddate and lud.userid = {$USER->id} group by luc.userid order by luc.id DESC");
            return $sems1;
        }
    }

    /**
     * @method current_sem
     * @todo To get present running semester for particular school and program
     * @param int $schoolid School ID
     * @param int $programid Program ID
     * @return object $sems Semesters
     */
    function current_sem($value, $value1) {
        global $USER, $DB;
        $time = time();
        $sems = $DB->get_records_sql("SELECT ls.* FROM {local_user_clclasses} luc 
				      INNER JOIN {local_userdata} lud ON lud.userid = luc.userid  
				      INNER JOIN {local_semester} ls ON luc.semesterid = ls.id 
				      where lud.schoolid = {$value} and ls.startdate<= {$time} and {$time}<=ls.enddate and lud.programid = {$value1} and lud.userid = {$USER->id} group by ls.id");
        return $sems;
    }

    /**
     * @method school_programs
     * @todo To get the programs for particular school
     * @param int $schoolid School ID
     * @return object $sch_pro Programs list
     */
    function school_programs($value) {
        global $USER, $DB;
        $sch_pro = $DB->get_records('local_program', array('schoolid' => $value));
        return $sch_pro;
    }

    /**
     * @method allassinge_sems
     * @todo To get assigned semesters for logged in user of particular school and program
     * @param int $schoolid School ID
     * @param int $programid Program ID
     * @return object $semesters Semesters list
     */
    function allassinged_sems($value, $value1) {
        global $USER, $DB;

        $sems1 = $DB->get_records_sql("SELECT ls.* FROM {local_user_clclasses} luc 
					   INNER JOIN {local_userdata} lud ON lud.userid = luc.userid 
					   INNER JOIN {local_semester} ls ON luc.semesterid = ls.id 
					   where lud.schoolid = {$value}  and lud.programid = {$value1} and lud.userid = {$USER->id} group by ls.id");
        return $sems1;
    }

    /**
     * @method previoussemsofuser
     * @todo To get previously assigned semesters for logged in user of particular school and program
     * @param int $schoolid School ID
     * @param int $programid Program ID
     * @return object $semesters Semesters list
     */
    function previoussemsofuser($value, $value1) {
        global $USER, $DB;
        $time = time();
        $sems1 = $DB->get_records_sql("SELECT ls.* FROM {local_user_clclasses} luc 
					   INNER JOIN {local_userdata} lud ON lud.userid = luc.userid 
					   INNER JOIN {local_semester} ls ON luc.semesterid = ls.id 
					   where lud.schoolid = {$value}  and lud.programid = {$value1} and lud.userid = {$USER->id} and ls.enddate<{$time} group by ls.id");
        return $sems1;
    }

    /* ----------print tab view----------------- */

    function requesttabview($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('request', new moodle_url('/local/request/requestprofile.php'), get_string('newrequest', 'local_request'));
        $tabs[] = new tabobject('view', new moodle_url('/local/request/request_profile.php'), get_string('view'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* ----------- print tab view----------------- */

    function requestidtabview($currenttab) {
        global $OUTPUT, $DB, $USER;
        $check = $DB->get_record_sql("SELECT * FROM {local_request_idcard} WHERE studentid = {$USER->id} AND reg_approval = 0");
        $tabs = array();
        if (empty($check)) {

            $tabs[] = new tabobject('request', new moodle_url('/local/request/requestid.php'), get_string('newrequest', 'local_request'));
            $tabs[] = new tabobject('view', new moodle_url('/local/request/request_id.php'), get_string('view'));
        }
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* -------------- print tab view------------------- */

    function requesttransfertabview($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('request', new moodle_url('/local/request/requesttransfer.php'), get_string('newrequest', 'local_request'));
        $tabs[] = new tabobject('view', new moodle_url('/local/request/request_transfer.php'), get_string('view'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* ---------------- print tab view----------------- */

    function requesttranscripttabview($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('request', new moodle_url('/local/request/requesttranscript.php'), get_string('newrequest', 'local_request'));
        $tabs[] = new tabobject('view', new moodle_url('/local/request/request_transcript.php'), get_string('view'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

//course exemption-----------------------------------------------------
    /**
     * @method enrolledprograms
     * @todo To get Enrolled programs
     * @param int $schoolid School ID
     * @return array Program select menu
     */
    function enrolledprograms($schoolid) {
        global $DB, $CFG, $USER;
        $programs = $DB->get_records('local_userdata', array('userid' => $USER->id, 'schoolid' => $schoolid));
        $out = array();
        foreach ($programs as $pro) {
            $out[$pro->programid] = $DB->get_field('local_program', 'fullname', array('id' => $pro->programid));
        }
        return $out;
    }

    /**
     * @method currentsemester
     * @todo To fetch loggedin user current semester
     * @param int $schoolid School ID
     * @return object, semester list
     */
    function currentsemester($schoolid) {
        global $DB, $CFG, $USER;
        $today = date('Y-m-d');
        return $DB->get_record_sql("SELECT ls.*, from_unixtime(ls.startdate, '%Y-%m-%d'), from_unixtime(ls.enddate, '%Y-%m-%d')
			    FROM {local_semester} ls,
			    {local_school_semester} lss,
			    {local_user_clclasses} luc
			    WHERE
			    ls.id = lss.semesterid AND
			    ls.id = luc.semesterid AND
			    lss.schoolid = {$schoolid} AND
			    luc.userid = {$USER->id} AND
			    '{$today}' BETWEEN from_unixtime(ls.startdate, '%Y-%m-%d') AND from_unixtime(ls.enddate, '%Y-%m-%d') AND luc.registrarapproval = 1 
			    GROUP BY
			    luc.semesterid");
    }

    /**
     * @method get_enrolledcourses
     * @todo To get Enrolled courses
     * @param int $semester Semester ID
     * @param int $schoolid School ID
     * @return array Course select menu
     */
    function get_enrolledcourses($semester, $school) {
        global $DB, $CFG, $USER;
        $param = array($school, $semester, $USER->id);
        $out = $DB->get_records_sql_menu("SELECT course.id,course.fullname FROM {local_user_clclasses} AS luc
		      JOIN {local_clclasses} AS class
		      ON class.id = luc.classid
		      JOIN {local_cobaltcourses} AS course
		      ON course.id = class.cobaltcourseid
		      WHERE
		      class.schoolid = ? AND class.semesterid = ? AND luc.userid = ? AND luc.registrarapproval = 1
		      ", $param);
        return $out;
    }

    /* -------------to print tab view-------------- */

    function requestcourseexemtabs($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('request', new moodle_url('/local/request/courseexem.php'), get_string('newrequest', 'local_request'));
        $tabs[] = new tabobject('view', new moodle_url('/local/request/course_exem.php'), get_string('view', 'local_request'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* -------------to print tab view-------------- */

    function statusview($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('pending', new moodle_url('/local/request/approval_id.php', array('mode' => 'pending')), get_string('pending', 'local_request'));
        $tabs[] = new tabobject('approved', new moodle_url('/local/request/approval_id.php', array('mode' => 'approved')), get_string('approved', 'local_request'));
        $tabs[] = new tabobject('rejected', new moodle_url('/local/request/approval_id.php', array('mode' => 'rejected')), get_string('rejectedc', 'local_request'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* -------------to print tab view-------------- */

    function transfertabs($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('pending', new moodle_url('/local/request/approval_transfer.php', array('mode' => 'pending')), get_string('pending', 'local_request'));
        $tabs[] = new tabobject('approved', new moodle_url('/local/request/approval_transfer.php', array('mode' => 'approved')), get_string('approved', 'local_request'));
        $tabs[] = new tabobject('rejected', new moodle_url('/local/request/approval_transfer.php', array('mode' => 'rejected')), get_string('rejected', 'local_request'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /* -------------to print tab view-------------- */

    function courseexemptiontabview($currenttab) {
        global $OUTPUT;
        $tabs = array();
        $tabs[] = new tabobject('pending', new moodle_url('/local/request/approveexem.php', array('mode' => 'pending')), get_string('pending', 'local_request'));
        $tabs[] = new tabobject('approved', new moodle_url('/local/request/approveexem.php', array('mode' => 'approved')), get_string('approvedc', 'local_request'));
        $tabs[] = new tabobject('rejected', new moodle_url('/local/request/approveexem.php', array('mode' => 'rejected')), get_string('rejectedc', 'local_request'));
        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

    /**
     * @method requestedstudent
     * @todo To get list of requisition for course exemption of a user
     * @param object $student student detail  
     * @return object courseexemption requisition info
     */
    function requestedstudent($student) {
        global $DB, $CFG;
        return $DB->get_record_sql("SELECT rce.*, lud.serviceid, lud.programid, CONCAT(user.firstname, ' ', user.lastname) AS student, course.fullname AS course, sem.fullname AS semester
                                    FROM {local_request_courseexem} AS rce
                                    JOIN {local_userdata} AS lud ON lud.userid = rce.studentid AND lud.schoolid = rce.schoolid
                                    JOIN {user} AS user ON user.id = lud.userid
                                    JOIN {local_cobaltcourses} AS course ON course.id = rce.courseid
                                    JOIN {local_semester} AS sem ON sem.id = rce.semesterid
                                    WHERE rce.id = {$student->id} GROUP BY lud.schoolid");
    }

    /**
     * @method insert_courseexemgrades
     * @todo To insert local class grades
     * @param int $id Course exemption ID
     * @param array $post
     * @return int Recently created instance ID
     */
    function insert_courseexemgrades($id, $post) {
        global $DB, $CFG, $USER;
        $course = new stdClass();
        $exem = $DB->get_record('local_request_courseexem', array('id' => $id));
        $student = $this->requestedstudent($exem);
        $course->userid = $exem->studentid;
        $course->schoolid = $exem->schoolid;
        $course->programid = $student->programid;
        $course->semesterid = $exem->semesterid;
        $course->classid = $exem->studentid;
        $course->courseid = $exem->courseid;
        $course->source = 'exe';
        $course->coursetotal = $post['grades'];
        $course->percentage = $post['grades'];
        $grades = $DB->get_record_sql("SELECT * FROM {local_gradeletters} WHERE {$course->percentage} BETWEEN markfrom AND markto");
        $course->gradeletter = $grades->letter;
        $course->gradepoint = $grades->gradepoint;
        $course->timecreated = time();
        $course->timemodified = time();
        $course->usermodified = $USER->id;
        $DB->insert_record('local_user_classgrades', $course);
        return $DB->insert_record('local_user_clsgrade_hist', $course);
    }

//mytranscripts

    /**
     * @method student_enrolled_sem
     * @todo To get Enrolled semesters for particular program
     * @param int $pid  Program ID
     * @return array Semester select menu
     */
    function student_enrolled_program($userid) {
        global $CFG, $DB;
        //$pro = array();
        //$pro[NULL] = "---Select---";
        $sql = "SELECT p.id,p.fullname FROM {local_userdata} AS u,{local_program} AS p 
	      WHERE u.userid=? AND u.programid=p.id AND p.visible=1 ";
        $param = array($userid);
        $pro = $DB->get_records_sql_menu($sql, $param);
        return $pro;
    }

    /**
     * @method get_student_program
     * @todo To fetch student enrolled programid
     * @param int $userid user ID
     * @return int program id.
     */
    function get_student_program($userid) {
        global $CFG, $DB;
        $pid = $DB->get_field('local_userdata', 'programid', array('userid' => $userid));
        return $pid;
    }

    /**
     * @method student_enrolled_sem
     * @todo To fetch student enrolled semester lists
     * @param int $pid program id
     * @return array, semesters list
     */
    function student_enrolled_sem($pid) {
        global $CFG, $DB, $USER;
        $sql = "SELECT ls.id,ls.fullname 
	      FROM {local_user_semester} AS us,{local_semester} AS ls 
		  WHERE us.userid=? AND 
		  us.programid=? AND 
		  us.registrarapproval=1 AND 
		  us.semesterid=ls.id AND 
		  ls.visible=1 ";
        $param = array($USER->id, $pid);
        $sem = $DB->get_records_sql_menu($sql, $param);
        return $sem;
    }

    /**
     * @method myacademics_grades
     * @todo To get academic gradesfor particular program and semester
     * @param int $pid  Program ID
     * @param int $semid Semester ID
     * @return object Grades data
     */
    function myacademics_grades($pid, $semid) {
        global $CFG, $DB, $USER;
        $sql = "SELECT lug.* FROM {local_user_clclasses} AS luc,{local_user_classgrades} AS lug
          WHERE luc.userid={$USER->id} 
          AND luc.semesterid={$semid}
          AND luc.programid={$pid}
          AND luc.registrarapproval=1
          AND luc.classid=lug.classid ";
        $query = $DB->get_records_sql($sql);
        return $query;
    }

    /* ------calculating total grade points of a class----------------- */

    function total_grade_points($gradepoint, $classid) {
        global $CFG, $DB, $USER;
        $courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
        $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
        $total = $gradepoint * $credithours;
        return $total;
    }

    /* ------fetching credit hour of a class----------------- */

    function total_grade_credits($classid) {
        global $CFG, $DB, $USER;
        $courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
        $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
        return $credithours;
    }

    public function array_unique_multidimensional($input) {
        $serialized = array_map('serialize', $input);
        $unique = array_unique($serialized);
        return array_values(array_intersect_key($input, $unique));
    }

    /**
     * @method all_student_requests_count
     * @todo To get all the requests
     * @param int $id  User ID
     * @return int Number of requests
     */
    public function all_student_requests_count($id) {
        global $DB;
        $others_school = $DB->get_records('local_school_permissions', array('userid' => $id));
        $countids = 0;
        $countpros = 0;
        $counttrans = 0;
        $countcoursx = 0;
        foreach ($others_school as $school) {
            $schoolid = $school->schoolid;
            $idreqforreg = $DB->get_records_sql("SELECT * FROM {local_request_idcard} where reg_approval = 0 and school_id = $schoolid");
            $proreqforreg = $DB->get_records_sql("SELECT * FROM {local_request_profile_change} where reg_approval = 0 and schoolid = $schoolid");
            $trareqforreg = $DB->get_records_sql("SELECT * FROM {local_request_transfer} where approvalstatus = 0 and schoolid = $schoolid");
            $courreqforreg = $DB->get_records_sql("SELECT * FROM {local_request_courseexem} where registrarapproval = 0 and schoolid = $schoolid");
            $countids = $countids + sizeof($idreqforreg);
            $countpros = $countpros + sizeof($proreqforreg);
            $counttra = sizeof($trareqforreg);
            $countcoursx = $countcoursx + sizeof($courreqforreg);
        }
        $requests = $DB->get_records('local_request_transcript', array('reg_approval' => '0'));
        foreach ($requests as $request) {
            $list = array();
            $details = $DB->get_record_sql("select lud.schoolid,luc.semesterid,lud.userid,lud.serviceid,lud.programid
                                                    from {local_userdata} lud
                                                    INNER JOIN {local_user_clclasses} luc 
                                                    ON lud.userid = luc.userid AND lud.userid = {$request->studentid}
                                                    AND luc.semesterid = {$request->req_semester} group by lud.schoolid");
            if ($reqtrans = $DB->get_records_sql("SELECT * FROM {local_school_permissions} where userid = '" . $id . "' and schoolid='" . $details->schoolid . "'")) {
                $counttrans = $counttrans + sizeof($reqtrans);
            }
        }
        $requestcount = ($counttrans + $countcoursx + $countpros + $countids);
        if ($requestcount == 0) {
            return 0;
        } else {
            return $requestcount;
        }
    }

    /**
     * @method all_approved_requests
     * @todo To get all the approvals
     * @param int $id  User ID
     * @return int Number of approvals
     */
    public function all_approved_requests($id) {
        global $DB, $USER;
        $user = $DB->get_record('user', array('id' => $id));
        $idcardapprov = $DB->get_records_sql("select * from {local_request_idcard} where studentid = $id and reg_approval = 1  and regapproved_date > $user->lastlogin");
        $profilechangeapprov = $DB->get_records_sql("select * from {local_request_profile_change} where studentid = $id and reg_approval = 1  and regapproval_date > $user->lastlogin");
        $transcriptapprov = $DB->get_records_sql("select * from {local_request_transcript} where studentid = $id and reg_approval = 1  and regapproval_date > $user->lastlogin");
        $transferapprov = $DB->get_records_sql("select * from {local_request_transfer} where studentid = $id and approvalstatus = 1  and regapproval_date > $user->lastlogin");
        $approvalcount = sizeof($idcardapprov) + sizeof($profilechangeapprov) + sizeof($transcriptapprov) + sizeof($transferapprov);
        if ($approvalcount == 0) {
            return 0;
        } else {
            return $approvalcount;
        }
    }

}

?>