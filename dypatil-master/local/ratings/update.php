<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/ratings/lib.php');
global $USER, $DB, $CFG;

$courseid = $_REQUEST['courseid'];
$activityid = $_REQUEST['activityid'];
$itemid = $_REQUEST['itemid'];
$ratearea = $_REQUEST['ratearea'];
$rating = $_REQUEST['rating'];
$heading = $_REQUEST['heading'];
if (!$course = $DB->get_record("course", array("id" => $courseid))) {
    print_error("Course ID not found");
}
$rate = new stdClass;
$rate->courseid = $courseid;
$rate->activityid = $activityid;
$rate->itemid = $itemid;
$rate->ratearea = $ratearea;
$rate->userid = $USER->id;
$rate->rating = $rating;
$rate->time = time();
if (!$DB->record_exists('local_rating', array('courseid' => $courseid, 'activityid' => $activityid, 'itemid' => $itemid, 'ratearea' => $ratearea, 'userid' => $USER->id)))
    $rate->id = $DB->insert_record('local_rating', $rate);
$numstars = $rating * 2;
$return_values = array();

$avgratings = get_rating($courseid, $activityid, $itemid, $ratearea);
$res = "<img title='" . ($avgratings->avg / 2) . " out of 5' src='" . $CFG->wwwroot . "/local/ratings/pix/star" . ($avgratings->avg) . ".png'  onclick='fnViewAllRatings($courseid, $activityid, $itemid, \"$ratearea\", \"$CFG->wwwroot\", \"$heading\")'/>";
$return_values[] = $res;
$return_values[] = $avgratings->count;
//$return_values[] = '(you) - '.userdate($rate->time);
echo implode('!@', $return_values);
?>


