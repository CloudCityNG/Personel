<?php
//define('AJAX_SCRIPT', true);
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG,$DB;
require_once($CFG->dirroot.'/local/lib.php');

 $schoolid = required_param('schoolid',PARAM_INT);
 $type = required_param('type',PARAM_RAW);

 $table = new html_table();
switch($type){
 case 'program':
    $data = $DB->get_records('local_program',array('schoolid'=>$schoolid,'visible'=>1));
    $table->head = array('Programs Included');
 break;
 case 'department':
    $departments = $DB->get_records('local_department', array('schoolid' => $schoolid, 'visible' => 1));
    $depts = $DB->get_records_sql("SELECT d.* FROM {local_department} d, {local_assignedschool_dept} sd WHERE d.id = sd.deptid AND sd.assigned_schoolid = $schoolid AND d.visible=1");
    $data = $departments + $depts;
    $table->head = array('Departments Included');
 break;
 case 'semester':
    $data = $DB->get_records_sql("SELECT ls.id,ls.fullname
                                    FROM {local_school_semester} AS ss
                                    JOIN {local_semester} AS ls
                                      ON ss.semesterid=ls.id where ss.schoolid={$schoolid} AND ls.visible = 1 group by ls.id");
 $table->head = array('Semesters Included');
 break;
}
$tabledata = array();
foreach($data as $value){
 $line = array();
 $line[] = $value->fullname;
 switch($type){
 case 'program':
  $line[] = $DB->count_records('local_userdata', array('programid' => $value->id, 'schoolid' => $schoolid));
 break;
 case 'department':
  $line[] = $DB->count_records('local_cobaltcourses', array('departmentid' => $value->id));
 break;
 case 'semester':
  $line[] = $DB->count_records('local_user_semester', array('semesterid' => $value->id));
 break;
}
 $tabledata[] = $line;
}
    $table->head[] = 'Enrollments';
    $table->align = array('left', 'center');
    $table->data = $tabledata;
echo $output = html_writer::table($table);
