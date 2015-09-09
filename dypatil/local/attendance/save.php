<?php
require_once(dirname(__FILE__).'/../../config.php');
require_once(dirname(__FILE__).'/lib.php');
global $CFG,$DB,$USER,$PAGE;
echo $OUTPUT->header();
$sessid=$_POST['sessid'];
$id=$_POST['id'];
$classid=$_POST['classid'];
$sql="SELECT u.* FROM {user} u,{local_user_clclasses} lc
        WHERE u.id=lc.userid AND lc.classid=3";
        $users=$DB->get_records_sql($sql);
        foreach($users as $user) {
            $data=new stdclass();
            $data->sessionid=$sessid;
            $data->studentid=$user->id;
            $data->statusid=$_POST[$user->id];
            $DB->insert_record('local_attendance_log',$data);
           
        }
echo $OUTPUT->footer();
//$returnurl='';
//redirect($returnurl);
?>