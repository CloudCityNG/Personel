<?php
/**
 * script for downloading users with attendance
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $DB,$CFG,$USER;
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->dirroot . '/lib/csvlib.class.php');
$all= $_POST['includeallsessions'];
$not= $_POST['includenottaken'];
$start= $_POST['sessionstartdate'];
$end=$_POST['sessionenddate'];
$att=$_POST['attendanceid'];
$classid=$_POST['classid'];
if($all=1) {
    if($not==1) {
        $sql="SELECT id,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates,duration FROM
        {local_attendance_sessions} WHERE attendanceid={$att}";
        
    }
    else {
    $sql="SELECT distinct(ls.id),DATE(FROM_UNIXTIME(ls.sessdate,'%Y-%m-%d')) as sessdates,ls.duration FROM {local_attendance_sessions} ls,{local_attendance_log} la
    WHERE
    ls.attendanceid={$att} AND ls.id=la.sessionid";
    
    }
    
   }
   else {
    if($not==1) {
    $sql="SELECT id,DATE(FROM_UNIXTIME(sessdate,'%Y-%m-%d')) as sessdates,duration FROM {local_attendance_sessions} WHERE attendanceid={$data->attendanceid}
    AND sessdate > {$data->start} AND sessdate < {$end}";
        
    }
    else {
    $sql="SELECT distinct(ls.id),DATE(FROM_UNIXTIME(ls.sessdate,'%Y-%m-%d')) as sessdates,ls.duration FROM {local_attendance_sessions} ls,{local_attendance_log} la
    WHERE
    ls.attendanceid={$att} AND ls.id=la.sessionid
    AND 
    ls.sessdate > {$start} AND ls.sessdate < {$end}";
    }
    
   }
    $records=$DB->get_records_sql($sql);
    $fields = array('studentname'=> 'studentname');
    $sessions=array();
    foreach ($records as $record) {
    $fields[$record->sessdates]=  $record->sessdates;
    $sessions[$record->id]=$record->id;
    }
    
    $usersql="SELECT u.id,u.firstname,u.lastname FROM {user} u,{local_user_clclasses} c WHERE u.id=c.userid
    AND c.classid={$classid}";
    $userlists=$DB->get_records_sql($usersql);
    
    $workbook = new csv_export_writer('');
    $filename = clean_filename('userlist');
    $workbook->set_filename($filename);
    $worksheet = array();
    $worksheet[0] = $workbook->add_data($fields);
    $sheetrow = 1;
    foreach ($userlists as $userlist) {
            $post = array();
      
            $post[] = $userlist->firstname.$userlist->lastname;
            
            foreach($sessions as $key=>$v) {
            //sessid-userid  
            $post[]=$key.'-'.$userlist->id;
                
            }
	    $workbook->add_data($post);
            $sheetrow++;
    }
    $workbook->download_file();
   