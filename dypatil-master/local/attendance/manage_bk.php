<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/renderer.php');
global $CFG,$DB,$PAGE;
$pageparams = new local_att_manage_page_params();
$sectionid    = optional_param('sectionid',0, PARAM_INT);
$id                         = optional_param('id',0, PARAM_INT);
if($id==0) {
$id=$DB->get_field('local_attendance','id',array('sectionid'=>$sectionid));
}
else {
    $sectionid=$DB->get_field('local_attendance','sectionid',array('id'=>$id));
}
$from                       = optional_param('from', null, PARAM_ALPHANUMEXT);
$pageparams->view           = optional_param('view', null, PARAM_INT);
$pageparams->curdate        = optional_param('curdate', null, PARAM_INT);
$pageparams->sectionid=$sectionid;
$att            = $DB->get_record('local_attendance', array('id' => 1), '*', MUST_EXIST);
require_login();
$att = new local_attendance($att,$PAGE->context, $pageparams);
$output = $PAGE->get_renderer('local_attendance');
$filtercontrols = new local_attendance_filter_controls($att);
$sesstable = new local_attendance_manage_data($att);
$PAGE->navbar->add(get_string('manageclclasses', 'local_clclasses'), 
new moodle_url('/local/clclasses/index.php'));
$PAGE->navbar->add(get_string('takeattendance', 'local_attendance'));
echo $output->header();
$course=$DB->get_field('local_clclasses','fullname',array('id'=>$sectionid));
echo $output->heading(get_string('attendanceforthecourse', 'local_attendance').' :: ' .$course);
$currenttab='manage';
local_attendance_tabs($currenttab,$sectionid,$flag=1);
echo $output->render($filtercontrols);
$today=date('Y-m-d');
switch ($pageparams->view) {
    case 1:
$sql="SELECT *,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates
FROM {local_attendance_sessions} WHERE attendanceid={$id} and
DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d'))='{$today}' AND duration > 0";
       
 
 break ;
case 2:
      $previousday=date("Y-m-d", strtotime("-7 day"));
$sql="SELECT *,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates
FROM {local_attendance_sessions} WHERE attendanceid={$id} and
DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d'))<='{$today}' and
DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d'))>='{$previousday}' AND duration > 0";
 
  break ;
case 3:
         $previousday=date("Y-m-d", strtotime("-30 day"));
         $sql="SELECT *,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates
FROM {local_attendance_sessions} WHERE attendanceid={$id} and
DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d'))<='{$today}' and
DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d'))>='{$previousday}' AND duration > 0";
 
  break ;
default:
   $sql="SELECT *,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates
FROM {local_attendance_sessions} WHERE attendanceid={$id} AND duration > 0";    
 
}
$records=$DB->get_records_sql($sql);
if(empty($records)) {
    echo "no attendance sessions";
}
else {
    
    $data = array();
    foreach ($records as $record) {
       $buttons = array();
       $result = array();
       $result[]=$record->sessdates.'('.date('D', $record->sessdate).')';

       $result[]=date('H:i', $record->sessdate).'-'.date('H:i', $record->sessdate + $record->duration);
       $buttons[]=html_writer::link(new moodle_url('/local/attendance/take.php',
array('id' => $id, 'sectionid' => $sectionid,'sessid'=>$record->id)),
html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('t/go'),
    'title' => get_string('takeattendance','local_attendance'), 'alt' => get_string('takeattendance','local_attendance'),
    'class' => 'iconsmall')));
       $buttons[]=html_writer::link(new moodle_url('/local/attendance/sessdelete.php',
    array('id'=>$record->id,'sectionid'=>$sectionid,'delete'=>1, 'sesskey'=>sesskey())),
html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/delete'),'title'=>get_string('delete'),
                                    'alt'=>get_string('delete'), 'class'=>'iconsmall')));
 $buttons[] =  html_writer::link(new moodle_url('/local/attendance/sessdelete.php',
array('id'=>$record->id,'sectionid'=>$sectionid,'sesskey'=>sesskey())),
html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/edit'),
'title'=>get_string('edit'),'alt'=>get_string('edit'), 'class'=>'iconsmall')));
    
 
        $result[] = implode(' ', $buttons);
       $data[] =$result;
    }
    $table = new html_table();

$table->head  = array(

get_string('date','local_attendance'),
get_string('time','local_attendance'),
get_string('action','local_attendance')
);
$table->size  = array('20%', '20%', '20%');
$table->align = array('left', 'left', 'left');
$table->width = '99%';
$table->data  = $data;
echo html_writer::table($table);
}
echo $output->footer();

