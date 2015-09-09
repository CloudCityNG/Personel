<script>
function read()
{
 document.getElementById('readmore').style.display = 'block';
  document.getElementById('readless').style.display = 'none';
   document.getElementById('myDiv').style.display = 'none';
}
function loadXMLDoc(path)
{
//alert(path);
 document.getElementById('readmore').style.display = 'none';
   document.getElementById('myDiv').style.display = 'block';
var xmlhttp;
if (window.XMLHttpRequest)
  {
  xmlhttp=new XMLHttpRequest();
  }
else
  {
  xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
  }
xmlhttp.onreadystatechange=function()
  {
  if (xmlhttp.readyState==4 && xmlhttp.status==200)
    {
    document.getElementById("myDiv").innerHTML=xmlhttp.responseText;
    }
  }
xmlhttp.open("GET",path+"/blocks/academic_status/more.php",true);
xmlhttp.send();
}
</script>
<?php
require_once($CFG->dirroot . '/config.php');
global $CFG,$USER,$DB,$PAGE,$OUTPUT;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot.'/local/courseregistration/lib.php');
require_once($CFG->dirroot . '/local/myacademics/lib.php');
require_once($CFG->dirroot . '/local/classroomresources/lib.php');
$PAGE->requires->css('/local/courseregistration/styles.css');
function get_semslist() {
    global $DB, $CFG, $USER, $OUTPUT, $PAGE;
    $userid = $USER->id;
	
    $conf = new object();
    $totalgradepoints = 0;
    $totalcredits = 0;
    $systemcontext = context_system::instance();
    $string = '<div id="academic_status_tabs"><ul>';
	if(!is_siteadmin()) {
	$string.='<li><a href="#fragment-1"><span>Course Feeds</span></a></li>';
	}
	$string .='<li><a href="#fragment-2"><span>My Classes</span></a></li>
		       <li><a href="#fragment-3"><span>Scheduled Classes</span></a></li>
	           </ul>';
    $string.='<div id="fragment-1">';
    $today=date('Y-m-d');
        //Query to get user currnet semester
		$sql = "SELECT s.id , s.fullname 
		        FROM {local_semester} s, {local_user_semester} us 
	            WHERE us.userid ={$userid} AND us.semesterid=s.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate)) ";
        $query= $DB->get_records_sql($sql);
		if(!isset($query) || empty($query)){
        $string .= "No Active Semester";		
		}
		else{
		foreach($query as $sem) {
		//Query to get user latest course activities (which created when user is logout)
		$coursesql="SELECT cm.id AS id,c.id AS courseid,c.shortname as shortname,lc.shortname as classname,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid
					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND cm.module=m.id AND cm.added > {$USER->lastlogin}";
		$courses=$DB->get_records_sql($coursesql);
	    $new='(new)';
		if(empty($courses)) {
		//Query to get user latest top5 course activities (when there is new activities)
		$coursesql="SELECT cm.id AS id, c.id AS courseid,c.shortname as shortname, c.fullname AS coursename, lc.shortname as classname,lc.fullname AS classname,
		            c.fullname AS coursename,m.name AS modulename,m.id AS moduleid,cm.instance AS instanceid
					FROM {local_user_clclasses} AS uc,{local_clclasses} AS lc,
                    {course} AS c,{course_modules} AS cm,
                    {modules} AS m WHERE uc.semesterid={$sem->id} AND uc.userid={$userid} AND uc.registrarapproval=1 AND uc.classid=lc.id and lc.onlinecourseid=c.id AND c.id=cm.course AND
					cm.module=m.id LIMIT 5";
		$courses=$DB->get_records_sql($coursesql);
		$new=' ';
		}
		$user = $DB->get_record('user', array('id'=>$userid));
		if(!empty($courses)) {
		$string .= '<div>
				    <p style="font-size: 11px; color: gray;">The Course feeds added from your last login '.date('d M, Y', $user->lastlogin).' (recent 5 are shown)</p>
				    </div>';
		$string.='<table width="100%">';
		$string .= '<tr><th style="text-align:left;">Course</th><th style="text-align:left;">Feed Type</th><th style="text-align:left;">Feed Name</th></tr>';
		
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
		$string.='<a onclick="loadXMLDoc(\''.$CFG->wwwroot.'\')">Read More</a>';
		$string.='<div id="myDiv"></div>';
	$string.='</table>';
	}
	else
	{
	$string.='No Course Feeds are avaliable for classes you are enrolled!';
	}
		}
		}
		/*$string.='<div id="myDiv"></div>';
		
		$string.='<a id="readmore" onclick="loadXMLDoc(\''.$CFG->wwwroot.'\')" style="cursor:pointer;">Read More</a>';
	  */
$string.='</div>';
    
	$string.='<div id="fragment-2">';
    $today = date('Y-m-d');
	if(is_siteadmin())
	{
	$sqladmin="SELECT fullname,
	           FROM_UNIXTIME(startdate,'%D %M, %Y') as startdate,
	           FROM_UNIXTIME(enddate,'%D %M, %Y') as enddate FROM {local_semester} WHERE 
			   visible=1 AND enddate >= {$today} AND startdate <= {$today}";
	$adminsems= $DB->get_records_sql($sqladmin);
	$data = array();
	foreach($adminsems as $adminsem)
	{
	$result = array();
	$result[]=$adminsem->fullname;
	$result[]=$adminsem->startdate;
	$result[]=$adminsem->enddate;
	$data[] = $result;
	}
	$table = new html_table();
	$table->head = array(
                    get_string('semestername', 'local_semesters'),
                    get_string('startdate', 'local_semesters'),
                    get_string('enddate', 'local_semesters'));
	$table->size = array('24%', '15%', '15%');
    $table->align = array('left', 'center', 'center');
    $table->width = '99%';
    $table->data = $data;
    $string.=html_writer::table($table);
	}
	else
	{
    $select = 'SELECT p.id , p.fullname ';
    $from = " FROM {local_semester} p, {local_user_semester} us ";
    $where = " WHERE us.userid ={$userid} AND us.semesterid=p.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(p.startdate)) and  DATE(FROM_UNIXTIME(p.enddate)) ";
    $vv = $select . $from . $where;
    $tools = $DB->get_records_sql($vv);
    if (!isset($tools) || empty($tools)) {
        $string.="No Active Semester";
    } else {
        foreach ($tools as $tool) {
            $grades = student_academic_grades($tool->id);
            if (!isset($grades) || empty($grades)) {
               
                $string.="No Classes";
            } else {
                $data = array();
                foreach ($grades as $grade) {
                    $result = array();
                    $coursename = $DB->get_field_sql('SELECT lcc.fullname FROM {local_clclasses} AS lc,{local_cobaltcourses} AS lcc
	                                                                            WHERE lc.cobaltcourseid=lcc.id AND lc.id='.$grade->classid.'');
                    $classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $grade->classid));
                    $marks = $DB->get_record('local_user_classgrades', array('classid' => $grade->classid, 'userid' => $USER->id));
                   

                    $maxgrades = $DB->get_field('local_scheduledexams', 'grademax', array('classid' => $grade->classid, 'semesterid' => $grade->semesterid));
                   
					$online=$DB->get_record('local_clclasses',array('id' => $grade->classid));
					if($online->online==1)
					{
					 $result[] = html_writer::tag('a',$coursename, array('href' => ''.$CFG->wwwroot.'/course/view.php?id='.$online->onlinecourseid.''));
                     $result[] = $classname;
					 $result[] =html_writer::tag('a','Launch Class', array('href' => ''.$CFG->wwwroot.'/course/view.php?id='.$online->onlinecourseid.''));
					 $progressbar=online_progress($USER->id,$grade->semesterid,$grade->classid);
					 $result[]=$progressbar;
					}
					else
					{
					
					 $result[] = html_writer::tag('a',$coursename, array('href' => ''.$CFG->wwwroot.'/local/classes/view.php?id='.$grade->classid.''));
                     $result[] = $classname;
					 $result[] ='Offline Class';
					 $progressbar=offline_progress($USER->id,$grade->semesterid,$grade->classid);
					 $result[]=$progressbar;
					}
                    $data[] = $result;
                   }
                $table = new html_table();
                $table->head = array(
                    get_string('coursename', 'local_cobaltcourses'),
                    get_string('headername', 'local_clclasses'),
                    get_string('coursetype', 'local_cobaltcourses'),
                    get_string('progressbar', 'local_courseregistration')
                );
                $table->size = array('25%','15%', '15%', '25%');
                $table->align = array('left', 'center', 'center', 'left');
                $table->width = '99%';
                $table->data = $data;
                $sem = $DB->get_field('local_semester', 'fullname', array('id' => $grade->semesterid));
                $string.=html_writer::tag('div', '<h5><b>Semester Name - ' . $sem . '</b></h5>', array('style'=>'float: left;'));
		$string .= '<div style="float: right;">'.get_my_progress($USER->id, $grade->semesterid, 0).'</div>';
                $string.=html_writer::table($table);
            }
        }
    }
	}
    $string.='</div>';
    $string.='<div id="fragment-3">';
	
	$today = date('Y-m-d');
    $context = context_user::instance($USER->id);
	$systemcontext = context_system::instance();
    if(has_capability('local/classes:enrollclass', $context)) {
	$data = array();
	$select = "SELECT s.id,s.fullname FROM {local_semester} s, {local_user_semester} us 
		   WHERE us.userid ={$USER->id} AND us.semesterid=s.id  AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(s.startdate)) AND  DATE(FROM_UNIXTIME(s.enddate)) ";
$query= $DB->get_records_sql($select);
if(empty($query)) {
$string.="Semester Not Yet Started";
}
else {
foreach($query as $sem) {
$timetable=cobalt_student_timetable($sem->id);
if(empty($timetable)) {
$string.= "There are no classes scheduled for you!";
}
else {
foreach($timetable as $time) {
$view=array();
$record=$DB->get_record('local_classroom',array('id'=>$time->classroomid));
$building=$DB->get_field('local_building','fullname',array('id'=>$record->buildingid));
$floor=$DB->get_field('local_floor','fullname',array('id'=>$record->floorid));
$classroom=$record->fullname;
$view[]=$DB->get_field('local_clclasses','fullname',array('id'=>$time->classid));
$courseid=$DB->get_field('local_clclasses','cobaltcourseid',array('id'=>$time->classid));
$view[]=$DB->get_field('local_cobaltcourses','fullname',array('id'=>$courseid));
$view[]=$time->startdate;
$view[]=$time->enddate;
$view[]=$time->starttime.'-'.$time->endtime;
$view[]=$building.'-'.$floor.'-'.$classroom;
if($time->instructorid==null) {
$view[]='Yet to assign';
}
else {
$teacher=$DB->get_record('user',array('id'=>$time->instructorid));
$view[]=$teacher->firstname.' '.$teacher->lastname;
}
$data[]=$view;

}
$table = new html_table();
$table->head  = array(
get_string('classname','local_classroomresources'),
get_string('coursename','local_classroomresources'),
get_string('startdate','local_classroomresources'),
get_string('enddate','local_classroomresources'),
get_string('timings','local_classroomresources'),

get_string('roomname','local_classroomresources'),
get_string('instructor','local_classroomresources')
);
$table->size  = array('13%','14%','15%', '15%','12%', '15%','15%');
$table->align = array('left','left','left','left','left','left','left');
$table->width = '99%';
$table->data  = $data;
$string.=html_writer::table($table);
}
}
}

    }
    if(has_capability('local/classes:submitgrades', $systemcontext)) {
	$data = array();
$timetable=cobalt_get_my_timetable();
if(empty($timetable)) {
$string.='No Classes Scheduled for You';
}
else {
foreach($timetable as $time) {
$view=array();
$record=$DB->get_record('local_classroom',array('id'=>$time->classroomid));
$building=$DB->get_field('local_building','fullname',array('id'=>$record->buildingid));
$floor=$DB->get_field('local_floor','fullname',array('id'=>$record->floorid));
$classroom=$record->fullname;
$view[]=$DB->get_field('local_clclasses','fullname',array('id'=>$time->classid));
$courseid=$DB->get_field('local_clclasses','cobaltcourseid',array('id'=>$time->classid));
$view[]=$DB->get_field('local_cobaltcourses','fullname',array('id'=>$courseid));

$view[]=$time->startdate.' to '.$time->enddate;
$view[]=$time->starttime .' to '.$time->endtime;
$view[]=$building.'-'.$floor.'-'.$classroom;
$data[]=$view;
}
$table = new html_table();
$table->head  = array(
get_string('classname','local_classroomresources'),
get_string('courseid','local_classroomresources'),
get_string('date','local_classroomresources'),
get_string('timings','local_classroomresources'),
get_string('roomname','local_classroomresources')
);
$table->size  = array('10%', '27%', '20%','18%', '24%');
$table->align = array('left','left','left','left','left');
$table->width = '99%';
$table->data  = $data;
$string.= html_writer::table($table);
}
    }
$string.='</div>';
return $string . '</div>';
}
?>

