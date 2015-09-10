<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once(dirname(__FILE__) . '/../../course/lib.php');
global $CFG, $DB, $PAGE, $USER;
@error_reporting(0);
@ini_set('html_errors', 'off');
$schoid = $_REQUEST['schoid'];
$deptid = $_REQUEST['deptid'];
$a = $_REQUEST['cn'];
$b = $_REQUEST['sn'];

/*
 * ###Bugreport # classroom management
 * @author hemalatha c arun<hemalatha@eabyas.in>
 * (Resolved)adding proper sortorder for the creation of moodle course and  adding proper count value to course category throw classroom management interface. 
 */
$categoryid = 1;
$category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);
$catcontext = context_coursecat::instance($category->id);

$sortorderlist = $DB->get_records('course', array('category' => 1));
foreach ($sortorderlist as $sortlist)
    $sortorder = $sortlist->sortorder;
if ($sortorder)
    $sortorder = $sortorder++;
else
    $sortorder = 0;
$data = new stdClass();
$data->category = 1;
$data->sortorder = $sortorder;
$data->fullname = $a;
$data->shortname = $b;
$data->idnumber = 0;
$data->summary = ' ';
$data->summaryformat = 1;
$data->format = 'weeks';
$data->showgrades = 1;
//$data->sectioncache=' ';
//$data->modinfo=' ';
$data->newsitems = 5;
$data->startdate = time();
$data->marker = 0;
$data->maxbytes = 0;
$data->legacyfiles = 0;
$data->showreports = 0;
$data->visible = 1;
$data->visibleold = 1;
$data->groupmode = 0;
$data->groupmodeforce = 0;
$data->defaultgroupingid = 0;
$data->lang = ' ';
$data->theme = ' ';
$data->timecreated = time();
$data->timemodified = time();
$data->requested = 0;
$data->enablecompletion = 1;
$data->completionnotify = 0;
$data->coursetype = 0;
$b = create_course($data);
update_course($b);
//$b=$DB->insert_record('course', $data);
$context = context_course::instance($b->id, MUST_EXIST);
rebuild_course_cache($b->id);
if ($b) {
    $count = $DB->get_record('course_categories', array('id' => $data->category));
    $count->coursecount = ($count->coursecount + 1);
    $DB->update_record('course_categories', $count);
}
echo '<input type="hidden" value="' . $b->id . '" name="newonlinecourseid1">';
?>
