<?php
function cobalt_student_activities_block($userid) {
global $CFG,$DB;
$i=0;
$today=date('Y-m-d');
        //Query to get user currnet semester
		$sql = "SELECT s.id , s.fullname 
		        FROM {local_semester} s, {local_user_semester} us 
	            WHERE us.userid ={$userid} AND us.semesterid=s.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";
        $query= $DB->get_records_sql($sql);
		if(!isset($query) || empty($query)){
        echo "No Active Semester";		
		}
		else{
		foreach($query as $sem) {
		//Query to get user latest course activities (which created when user is logout)
		$coursesql="SELECT c.id AS courseid,count(cm.id) AS counts,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid,cm.id AS id
					FROM {local_user_classes} AS uc,{local_classes} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id AND cm.added > {$USER->lastlogin} order by cm.id desc";
		$courses=$DB->get_records_sql($coursesql);
		$new='(new)';
		if(!isset($courses) || empty($courses) || $courses->counts==0) {
		//Query to get user latest top5 course activities (when there is new activities)
		$coursesql="SELECT c.id AS courseid,count(cm.id) AS counts,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid,cm.id AS id
					FROM {local_user_classes} AS uc,{local_classes} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id order by cm.id desc limit 5";
		$courses=$DB->get_records_sql($coursesql);
		$new=' ';
		}
		
		
		foreach($courses as $course) {
		if($i==0) {
		if(!empty($new)) {
		echo '<p>There are '.$course->counts.' new activities since your last login</p>';
		}
		else {
		echo '<p>There are 0 new activities since your last login</p>';
		}
	    $i++;
		}
		/*Switch starts*/ 
		switch($course->modulename) {
		//Assign Activities
		case 'assign':
		$activityname=$DB->get_field('assign','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Assignment Activities
		case 'assignment':
		$activityname=$DB->get_field('assignment','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/assign/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Book Activities
		case 'book':
		$activityname=$DB->get_field('book','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/book/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Chat Activities
		case 'chat':
		$activityname=$DB->get_field('chat','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/chat/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Choice Activities
		case 'choice':
		$activityname=$DB->get_field('choice','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/choice/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Data Activities
		case 'data':
		$activityname=$DB->get_field('data','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/data/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Feedback Activities
		case 'feedback':
		$activityname=$DB->get_field('feedback','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/feedback/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Folder Activities
		case 'folder':
		$activityname=$DB->get_field('folder','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/folder/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Forum Activities
		case 'forum':
		$activityname=$DB->get_field('forum','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/forum/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Glossary Activities
		case 'glossary':
		$activityname=$DB->get_field('glossary','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/glossary/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Imscp Activities
		case 'imscp':
		$activityname=$DB->get_field('imscp','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/imscp/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Label Activities
		case 'label':
		$activityname=$DB->get_field('label','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/label/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Lesson Activities
		case 'lesson':
		$activityname=$DB->get_field('lesson','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/lesson/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Lti Activities
		case 'lti':
		$activityname=$DB->get_field('lti','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/lti/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Page Activities
		case 'page':
		$activityname=$DB->get_field('page','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/page/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Quiz Activities
		case 'quiz':
		$activityname=$DB->get_field('quiz','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/quiz/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Resources Activities
		case 'resource':
		$activityname=$DB->get_field('resource','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/resource/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Scorm Activities
		case 'scorm':
		$activityname=$DB->get_field('scorm','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/scorm/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Survey Activities
		case 'survey':
		$activityname=$DB->get_field('survey','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/survey/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Url Activities
		case 'url':
		$activityname=$DB->get_field('url','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/url/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Wiki Activities
		case 'wiki':
		$activityname=$DB->get_field('wiki','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/wiki/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		//Workshop Activities
		case 'workshop':
		$activityname=$DB->get_field('workshop','name',array('id'=>$course->instanceid));
		echo '<tr><td><a href="'.$CFG->wwwroot.'/mod/workshop/view.php?id='.$course->id.'">'.$activityname.$new.'</a></td></tr>';
		break;
		}
		/*Switch ends*/
		}
		
		}
		}
}
?>