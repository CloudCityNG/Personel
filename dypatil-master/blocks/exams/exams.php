<?php
require_once($CFG->dirroot.'/config.php');
require_once($CFG->dirroot.'/local/lib.php'); 
require_once($CFG->dirroot.'/local/scheduleexam/lib.php');

function get_exams() {
	global $CFG, $DB, $USER;
	$string='<div id="exam_tabs">
<ul>
<li><a href="#fragment-1"><span>Today Exams</span></a></li>
<li><a href="#fragment-2"><span>Upcoming Exams</span></a></li>
</ul>
<div id="fragment-1">';
 $sql ="select ex.*,c.fullname AS classname,s.fullname AS schoolname,sem.fullname AS semestername,extyp.examtype AS examtype,lectyp.lecturetype FROM {local_scheduledexams} ex 
        JOIN {local_school} s on ex.schoolid=s.id
        JOIN {local_clclasses} c on ex.classid=c.id
        JOIN {local_semester} sem on ex.semesterid=sem.id
        JOIN {local_examtypes} extyp on ex.examtype=extyp.id
        JOIN {local_lecturetype} lectyp on ex.lecturetype=lectyp.id";

// $exams = $DB->get_records_sql($sql);
$systemcontext = context_system::instance();
$today = date('Y-m-d');
$today = strtotime($today);

if (has_capability('local/scheduleexam:manage', $systemcontext)) {
	$instsql = "{$sql} JOIN {local_scheduleclass} sc on sc.classid=ex.classid and sc.semesterid=ex.semesterid
          where  sem.enddate>={$today} and ex.opendate ={$today}";
    $exams = $DB->get_records_sql($instsql);
 }
 
  $context = context_user::instance($USER->id);
  
 if(has_capability('local/clclasses:submitgrades', $context) && !is_siteadmin()) {
		$instsql = "{$sql} JOIN {local_scheduleclass} sc on sc.classid=ex.classid and sc.semesterid=ex.semesterid
          where sc.instructorid={$USER->id} and sem.enddate>={$today} and ex.opendate ={$today}";
		$exams = $DB->get_records_sql($instsql);
}
 
if(has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
    $sqlstu = "{$sql} JOIN {local_user_clclasses} uc on uc.semesterid=ex.semesterid 
                where uc.userid={$USER->id} and uc.registrarapproval=1 and sem.enddate>={$today} and ex.opendate ={$today}";
    $exams = $DB->get_records_sql($sqlstu);
 }
 if(is_siteadmin())
 {
 $sqladmin="SELECT * FROM {local_scheduledexams} WHERE opendate={$today}";
 $exams = $DB->get_records_sql($sqladmin);
 }
$data = array();
if($exams) {
    foreach ($exams as $exam) {
		$sql='SELECT co.fullname FROM {local_clclasses} c 
        JOIN {local_cobaltcourses} co on c.cobaltcourseid=co.id where c.id='.$exam->classid.'';
        $cobcourse = $DB->get_record_sql($sql);
        $line = array();
		$line[] = $exam->examtype.'-'.$exam->lecturetype;
		$line[] = $cobcourse->fullname;
        $exam->starttimehour = ($exam->starttimehour<10) ? '0'.$exam->starttimehour : $exam->starttimehour ;
        $exam->starttimemin = ($exam->starttimemin<10) ? '0'.$exam->starttimemin : $exam->starttimemin ;
        $exam->endtimehour = ($exam->endtimehour<10) ? '0'.$exam->endtimehour : $exam->endtimehour ;
        $exam->endtimemin = ($exam->endtimemin<10) ? '0'.$exam->endtimemin : $exam->endtimemin ;
		$line[]=$exam->starttimehour.':'.$exam->starttimemin.'  to  '.$exam->endtimehour.':'.$exam->endtimemin;
        $data[] = $line;
        }
}
else {
    $line = array(); 
    $line[]="No Exams scheduled today. ";
    $data[] = $line;
 }

$table = new html_table();
$table->head  = array(get_string('examname', 'block_exams'), get_string('cobcourse', 'block_exams'), get_string('time', 'block_exams'));
$table->size  = array('20%','20%','20%');
$table->align = array('left', 'left', 'left');
$table->width = '100%';
$table->data  = $data;
$string.= html_writer::table($table);
$string.='</div>';
$string.='<div id="fragment-2">';
$sql ="select ex.*,c.fullname AS classname,s.fullname AS schoolname,sem.fullname AS semestername,extyp.examtype AS examtype,lectyp.lecturetype FROM {local_scheduledexams} ex 
        JOIN {local_school} s on ex.schoolid=s.id
        JOIN {local_clclasses} c on ex.classid=c.id
        JOIN {local_semester} sem on ex.semesterid=sem.id
        JOIN {local_examtypes} extyp on ex.examtype=extyp.id
        JOIN {local_lecturetype} lectyp on ex.lecturetype=lectyp.id  ";	
$exams = $DB->get_records_sql($sql);
$data = array();
$today = date('m/d/Y');
$today = strtotime($today);
if (has_capability('local/scheduleexam:manage', $systemcontext)) {
    $instsql = "{$sql} JOIN {local_scheduleclass} sc on sc.classid=ex.classid and sc.semesterid=ex.semesterid
          where sem.enddate>={$today} and ex.opendate >{$today}";
	$exams = $DB->get_records_sql($instsql);
 }
  
$context = context_user::instance($USER->id);
  
 if(has_capability('local/clclasses:submitgrades', $context) && !is_siteadmin()) {
     $instsql = "{$sql} JOIN {local_scheduleclass} sc on sc.classid=ex.classid and sc.semesterid=ex.semesterid
          where sc.instructorid={$USER->id} and sem.enddate>={$today} and ex.opendate >{$today}";
     $exams = $DB->get_records_sql($instsql);
 }
 
 if(has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
      $sqlstu = "{$sql} JOIN {local_user_clclasses} uc on uc.semesterid=ex.semesterid 
                where uc.userid={$USER->id} and uc.registrarapproval=1 and sem.enddate>={$today} and ex.opendate >{$today}";
      $exams = $DB->get_records_sql($sqlstu);
 }
 if(is_siteadmin())
 {
 $sqladmin="SELECT * FROM {local_scheduledexams} WHERE opendate > {$today}";
 $exams = $DB->get_records_sql($sqladmin);
 }
if($exams) {
    foreach ($exams as $exam) {
		$sql='SELECT co.fullname FROM {local_clclasses} c 
                  JOIN {local_cobaltcourses} co on c.cobaltcourseid=co.id where c.id='.$exam->classid.'';
		$cobcourse = $DB->get_record_sql($sql);
		$line = array();
		$line[] = $exam->examtype.'-'.$exam->lecturetype;
		$line[] = $cobcourse->fullname;
		$exam->starttimehour = ($exam->starttimehour<10) ? '0'.$exam->starttimehour : $exam->starttimehour ;
		$exam->starttimemin = ($exam->starttimemin<10) ? '0'.$exam->starttimemin : $exam->starttimemin ;
		$exam->endtimehour = ($exam->endtimehour<10) ? '0'.$exam->endtimehour : $exam->endtimehour ;
		$exam->endtimemin = ($exam->endtimemin<10) ? '0'.$exam->endtimemin : $exam->endtimemin ;
		$line[]=date("m/d/Y", $exam->opendate).','.$exam->starttimehour.':'.$exam->starttimemin.'  to  '.$exam->endtimehour.':'.$exam->endtimemin;
		$data[] = $line;

    }
}
else {
    $line = array(); 
	$line[]="No upcoming exams scheduled.";
    $data[] = $line;
}
 
$table = new html_table();
$table->head  = array(get_string('examname', 'block_exams'), get_string('cobcourse', 'block_exams'), get_string('time', 'block_exams'));
$table->size  = array('20%','20%','20%');
$table->align = array('left', 'left', 'left');
$table->width = '100%';
$table->data  = $data;
$string.= html_writer::table($table);
$string.='</div>';
return $string.'</div>';
?>

<?php } ?>