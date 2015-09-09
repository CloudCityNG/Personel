<?php
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB,$PAGE,$USER;
require_once($CFG->dirroot.'/local/attendance/lib.php');
$systemcontext =context_system::instance();
$PAGE->set_url('/local/attendance/timetable.php');
$PAGE->set_pagelayout('admin');
require_login();
/*if (!has_capability('local/classroomresources:manage', $systemcontext)) {
    print_error('You dont have permissions');
}*/
$PAGE->set_context($systemcontext);
$PAGE->set_heading(get_string('pluginname','local_attendance'));
$PAGE->navbar->add(get_string('pluginname','local_attendance'));
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_attendance'));

if (isset($CFG->allowframembedding) and !$CFG->allowframembedding) {
    echo $OUTPUT->box(get_string('local_attendance', 'local_attendance'));
}

$today=date('Y-m-d');
$school=$DB->get_records_sql("SELECT t.* FROM {local_userdata} u,{local_time_settings} t WHERE u.schoolid=t.schoolid
                             AND u.userid={$USER->id}");
if(empty($school)) {
echo "no school for you";
}
else {

//
$classsql = "SELECT uc.id,uc.classid FROM {local_user_semester} us,{local_semester} s,{local_user_clclasses} uc WHERE
us.semesterid=s.id AND s.id=uc.semesterid AND uc.userid={$USER->id} AND
'{$today}'  BETWEEN DATE(FROM_UNIXTIME(s.startdate)) and  DATE(FROM_UNIXTIME(s.enddate))";
    
    $clclasses= $DB->get_records_sql($classsql);
    if (empty($tools)) {
        $string="No Active Academic Year";
    }
    else {
       foreach(  $clclasses as   $clas) {
     $temp[]=$clas->classid;
      }
 $c=implode(',',$temp); 
    }
$time=$DB->get_record('local_time_settings',array('schoolid'=>1));
$head[0]='Time';
$datelist[0]='Date';
$date = date('Y-m-d');
// parse about any English textual datetime description into a Unix timestamp
$ts = strtotime($date);
// find the year (ISO-8601 year number) and the current week
$year = date('o', $ts);
$week = date('W', $ts);
// print week for the current date
for($i = 1; $i <= 7; $i++) {
    // timestamp from ISO week date format
    $ts = strtotime($year.'W'.$week.$i);
    $head[$i]=date("l Y-m-d ", $ts) ;
    $datelist[$i]=date("Y-m-d ", $ts) ;
    }
    $data = array();
   
    for ($j=$time->starthours;$j<=$time->endhours;$j++) {
       
    $result = array();
    $result[]=$j.'-'.$time->startminutes;
    $result[]=local_get_timetable_class($datelist[1],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[2],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[3],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[4],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[5],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[6],$j,$time->startminutes,$c);
    $result[]=local_get_timetable_class($datelist[7],$j,$time->startminutes,$c);
    $data[] =$result;
    }
$table = new html_table();
$table->head  = $head;

$table->data  = $data;
echo html_writer::table($table);
}

echo $OUTPUT->footer();
?>