<?php
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot.'/local/batches/lib.php');
global $USER,$DB,$CFG;

$batchid = $_REQUEST['batchid'];
$costcenterid = $_REQUEST['costcenterid'];

$sql = "SELECT * FROM {course}
        WHERE costcenter = {$costcenterid}
        AND id NOT IN (select courseid FROM {local_batch_courses} WHERE batchid = {$batchid})
        AND visible = 1";

$courses = $DB->get_records_sql($sql);

$return = array();
foreach ($courses as $course) {
    $return[] = array('id'=>$course->id, 'name'=>$course->fullname);
}
echo json_encode($return);