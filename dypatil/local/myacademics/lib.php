<?php

require_once($CFG->dirroot . '/local/lib.php');

/**
 * @method total_grade_points
 * @todo to calculate total grade points of a class
 * @param  int $gradepoint grade point
 * @param int $classid class id
 * @return int, total grade points
 * */
function total_grade_points($gradepoint, $classid) {
    global $CFG, $DB, $USER;
    $courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
    $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
    $total = $gradepoint * $credithours;
    return $total;
}

/**
 * @method total_grade_credits
 * @todo to get credit hour of a class(indirectly mapped to cobalt course)   
 * @param int $classid class id
 * @return int, credithour of a class
 * */
function total_grade_credits($classid) {
    global $CFG, $DB, $USER;
    $courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
    $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
    return $credithours;
}

/**
 * @method student_enrolled_semesters
 * @todo to get list of student enrolled semester 
 * @param int $userid user id
 * @return array, list of semesters 
 * */
function student_enrolled_semesters($userid) {
    global $CFG, $DB;
    $sems = array();
    $today = date('Y-m-d');
    $superfix = '';
    $sems[NULL] = "---Select---";
    $sql = "SELECT ls.id,ls.fullname,FROM_UNIXTIME(ls.startdate,'%Y-%m-%d') as startdate,
	      FROM_UNIXTIME(ls.enddate,'%Y-%m-%d') as enddate 
	      FROM {local_user_semester} AS us,{local_semester} AS ls 
		  WHERE us.userid={$userid} AND 
		  us.registrarapproval=1 AND 
		  us.semesterid=ls.id AND 
		  ls.visible=1 ";
    $semesters = $DB->get_records_sql($sql);
    foreach ($semesters as $semester) {
        if ($semester->startdate <= $today && $semester->enddate >= $today) {
            $superfix = '<sup style="color:red;">*</sup>';
        }
        $sems[$semester->id] = $semester->fullname . $superfix;
    }
    return $sems;
}

function student_academic_grades($semid) {
    global $CFG, $DB, $USER;

    $sql = "SELECT * FROM {local_user_clclasses} 
          WHERE userid={$USER->id} 
		  AND semesterid={$semid}
          AND registrarapproval=1 ";

    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method get_cobalt_course
 * @todo to get cobalt course name of a class
 * @param int $classid class id
 * @return string, cobalt course fullname
 * */
function get_cobalt_course($classid) {
    global $CFG, $DB;
    $sql = "SELECT lcc.fullname FROM {local_clclasses} AS lc,{local_cobaltcourses} AS lcc
	      WHERE lc.cobaltcourseid=lcc.id AND lc.id={$classid}";
    $courses = $DB->get_records_sql($sql);
    foreach ($courses as $cou) {
        $coursename = $cou->fullname;
    }
    return $coursename;
}

/**
 * @method cobalt_student_activities
 * @todo to print student online course activities of a student in the form of table 
 * @param int $userid class id
 * @return prints table format, activities info
 * */
function cobalt_student_activities($userid) {
    global $CFG, $DB, $USER;
    $i = 0;
    $today = date('Y-m-d');
    //Query to get user currnet semester
    $sql = "SELECT s.id , s.fullname 
		        FROM {local_semester} s, {local_user_semester} us 
	            WHERE us.userid ={$userid} AND us.semesterid=s.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";
    $query = $DB->get_records_sql($sql);
    if (!isset($query) || empty($query)) {
        $string .= "No Active Semester";
    } else {
        foreach ($query as $sem) {
            //Query to get user latest course activities (which created when user is logout)
            $coursesql = "SELECT c.id AS courseid,c.shortname as shortname,lc.shortname as classname,count(cm.id) AS counts,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid,cm.id AS id
					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id AND cm.added > {$USER->lastlogin} order by cm.id desc";
            $courses = $DB->get_records_sql($coursesql);

            $new = '(new)';
            if (!isset($courses) || empty($courses) || $courses->counts == 0) {
                //Query to get user latest top5 course activities (when there is new activities)
                $coursesql = "SELECT c.id AS courseid,c.shortname as shortname,lc.shortname as classname,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid,cm.id AS id
					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id order by cm.id desc limit 5";
                $courses = $DB->get_records_sql($coursesql);
                $new = ' ';
            }


            foreach ($courses as $course) {

                /* Switch starts */
                switch ($course->modulename) {

                    //Assign Activities
                    case 'assign':
                        $activityname = $DB->get_field('assign', 'name', array('id' => $course->instanceid));
                        echo $string .= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new assignment named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Assignment Activities
                    case 'assignment':
                        $activityname = $DB->get_field('assignment', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new assignment named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/assign/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Book Activities
                    case 'book':
                        $activityname = $DB->get_field('book', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new book named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/book/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Chat Activities
                    case 'chat':
                        $activityname = $DB->get_field('chat', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new chat named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/chat/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Choice Activities
                    case 'choice':
                        $activityname = $DB->get_field('choice', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new choice named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/choice/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Data Activities
                    case 'data':
                        $activityname = $DB->get_field('data', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new data named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/data/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Feedback Activities
                    case 'feedback':
                        $activityname = $DB->get_field('feedback', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new feedback named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/feedback/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td></tr>';
                        break;
                    //Folder Activities
                    case 'folder':
                        $activityname = $DB->get_field('folder', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new folder named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/folder/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Forum Activities
                    case 'forum':
                        echo $activityname = $DB->get_field('forum', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new forum named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/forum/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Glossary Activities
                    case 'glossary':
                        $activityname = $DB->get_field('glossary', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new glossary named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/glossary/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Imscp Activities
                    case 'imscp':
                        $activityname = $DB->get_field('imscp', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new imscp named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/imscp/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Label Activities
                    case 'label':
                        $activityname = $DB->get_field('label', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new label named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/label/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Lesson Activities
                    case 'lesson':
                        $activityname = $DB->get_field('lesson', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new lesson named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/lesson/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Lti Activities
                    case 'lti':
                        $activityname = $DB->get_field('lti', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new lti named ' . $activityname . ' has been created</td>
        <td><a href="' . $CFG->wwwroot . '/mod/lti/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Page Activities
                    case 'page':
                        $activityname = $DB->get_field('page', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new page named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/page/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Quiz Activities
                    case 'quiz':
                        $activityname = $DB->get_field('quiz', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new quiz named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/quiz/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Resources Activities
                    case 'resource':
                        $activityname = $DB->get_field('resource', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new resource named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/resource/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Scorm Activities
                    case 'scorm':
                        $activityname = $DB->get_field('scorm', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new scorm named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/scorm/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Survey Activities
                    case 'survey':
                        $activityname = $DB->get_field('survey', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new survey named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/survey/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Url Activities
                    case 'url':
                        $activityname = $DB->get_field('url', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new url named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/url/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Wiki Activities
                    case 'wiki':
                        $activityname = $DB->get_field('wiki', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new wiki named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/wiki/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                    //Workshop Activities
                    case 'workshop':
                        $activityname = $DB->get_field('workshop', 'name', array('id' => $course->instanceid));
                        echo $string.= '<tr>
		<td>For class</td>
		<td><b>' . $course->classname . '-' . '</b></td>
		<td>A new workshop named ' . $activityname . ' has been created</td>
		<td><a href="' . $CFG->wwwroot . '/mod/workshop/view.php?id=' . $course->id . '">Click here to view' . $new . '</a></td>
		</tr>';
                        break;
                }

                /* Switch ends */
            }
        }
    }
}

?>