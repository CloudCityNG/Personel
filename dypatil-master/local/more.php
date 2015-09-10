<?php

require_once(dirname(__FILE__) . '/../config.php');

global $CFG,$USER,$DB,$PAGE,$OUTPUT;

//echo "All Activities comes here";

//echo '<div >';



//echo '</div>';

$today=date('Y-m-d');



$sql = "SELECT s.id , s.fullname 

		        FROM {local_semester} s, {local_user_semester} us 

	            WHERE us.userid ={$USER->id} AND us.semesterid=s.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";

$query= $DB->get_records_sql($sql);

foreach($query as $sem) {

$coursesql="SELECT cm.id AS id,c.id AS courseid,c.shortname as shortname,lc.shortname as classname,lc.fullname AS classname,

		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid

					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,

                    {course} AS c,{course_modules} AS cm,

                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$USER->id} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id AND cm.added > {$USER->lastlogin}";

		$courses=$DB->get_records_sql($coursesql);

	    $new='(new)';

		if(empty($courses)) {

		//Query to get user latest top5 course activities (when there is new activities)

		$coursesql="SELECT cm.id AS id, c.id AS courseid,c.shortname as shortname, c.fullname AS coursename, lc.shortname as classname,lc.fullname AS classname,

		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid

					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,

                    {course} AS c,{course_modules} AS cm,

                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$USER->id} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND

					cm.module=m.id LIMIT 5,10";

		$courses=$DB->get_records_sql($coursesql);

		$new=' ';

		}

		$user = $DB->get_record('user', array('id'=>$USER->id));

		

		

		foreach($courses as $course) {

		$activityname = $DB->get_field($course->modulename, 'name', array('id'=>$course->instanceid));

		$string .= '<tr>';

		    $cname = $course->coursename;

		    if(strlen($course->coursename) > 30){

			$cname = substr($course->coursename, 0, 30).'...';

		    }

			$string .= '<td>'.$cname.'</td>

					<td>'.$course->modulename.'</td>';

					if($course->modulename=='assignment'){

					    $string .= '<td><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$course->id.'">'.$activityname.'</a></td>';

					} 

else {

$string .= '<td><a href="'.$CFG->wwwroot.'/mod/'.$course->modulename.'/view.php?id='.$course->id.'">'.$activityname.'</a></td>';

}

$string .= '</tr>';

		}

	

}
echo '<a  id="readless" onclick="read()" style="display:block;cursor:pointer;">Read Less</a>';
?>