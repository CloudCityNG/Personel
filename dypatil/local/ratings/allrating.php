<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/ratings/lib.php');
global $USER, $DB, $CFG;

$courseid = $_REQUEST['courseid'];
$activityid = $_REQUEST['activityid'];
$itemid = $_REQUEST['itemid'];
$ratearea = $_REQUEST['ratearea'];
$heading = $_REQUEST['heading'];
if (!$course = $DB->get_record("course", array("id" => $courseid))) {
    print_error("Course ID not found");
}
if ($courseid == SITEID)
    $context = context_system::instance();
else
    $context = context_course::instance($course->id);

$PAGE->set_context($context);

if (!isloggedin() || isguestuser()) {
    $return = "<div>You need to login to rate this $ratearea</div>";
} else if ($currentuserrating = $DB->get_record('local_rating', array('courseid' => $courseid, 'activityid' => $activityid, 'itemid' => $itemid, 'ratearea' => $ratearea, 'userid' => $USER->id))) {
    $return = get_existing_rates($currentuserrating);
} else {
    $return = ask_for_rating($courseid, $activityid, $itemid, $ratearea, $heading);
}
echo $return;
?>