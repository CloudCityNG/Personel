<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/batches/lib.php');
global $USER,$DB,$CFG;

$batchid = $_REQUEST['batchid'];
$costcenterid = $_REQUEST['costcenterid'];
$sql1 = "SELECT user.*
        FROM {local_userdata} AS udata 
        JOIN {user} AS user ON user.id = udata.userid
        WHERE udata.costcenterid = {$costcenterid}
        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$batchid})
        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1";
$sql2 = "SELECT user.*
        FROM {local_costcenter_permissions} AS cp
        JOIN {user} AS user ON user.id = cp.userid
        WHERE cp.costcenterid = {$costcenterid}
        AND user.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = {$batchid})
        AND user.id <> $USER->id
        AND user.deleted <> 1 AND user.confirmed = 1 AND user.id <> 1";
$users = $DB->get_records_sql("$sql1 UNION $sql2");

$return = array();
foreach ($users as $user) {
    $return[] = array('id'=>$user->id, 'name'=>fullname($user));
}
echo json_encode($return);