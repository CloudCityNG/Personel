<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE, $USER;
$schoid = $_REQUEST['schoid'];
$deptid = $_REQUEST['deptid'];
$a = $_REQUEST['cn'];
$b = $_REQUEST['sn'];
$c = $_REQUEST['cr'];
$cc = $_REQUEST['cc'];
/*
 * ###Bugreport #Training Management
 * @author hemalatha c arun<hemalatha@eabyas.in>
 * (Resolved) adding coursetype form field
 */
$ct = $_REQUEST['ct'];
$data = new stdClass();
$data->fullname = $a;
$data->shortname = $b;
$data->departmentid = $deptid;
$data->schoolid = $schoid;
$data->summary = '';
$data->coursetype = $ct;
$data->credithours = $c;
$data->coursecost = $cc;
$data->visible = 1;
$data->timecreated = time();
$data->timemodified = time();
$data->usermodified = $USER->id;
$b = $DB->insert_record('local_cobaltcourses', $data);

echo '<input type="hidden" value="' . $b . '" name="newcobaltcourseid1" id="newcobaltcourseid1" >';
?>

