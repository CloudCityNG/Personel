<?php

require_once('../../config.php');
require_once($CFG->dirroot . "/lib/weblib.php");

if (!isset($currenttab)) {
    $currenttab = 'details';
}
$toprow = array();
$toprow[] = new tabobject('overview', $CFG->wwwroot . '/edit.php?id=' . $id, overview);
if (substr($currenttab, 0, 7) == 'overview') {
    $activated[] = 'overview';
}
$toprow[] = new tabobject('details', $url, details);
if (substr($currenttab, 0, 7) == 'details') {
    $activated[] = 'details';
}
$toprow[] = new tabobject('content', $CFG->wwwroot . '/edit_content.php?id=' . $id, content);
if (substr($currenttab, 0, 7) == 'content') {
    $activated[] = 'content';
}
$tabs = array($toprow);
if (!$id) {
    $inactive .= array('overview', 'content', 'assignments', 'messages');
}

print_tabs($tabs, $currenttab, $inactive, $activated);

/* start of query */

$sql = "SELECT u.firstname ,u.lastname AS 'Last Name',c.fullname AS 'Course', 
FROM_UNIXTIME(ue.timecreated, '%m/%d/%Y') AS 'Enrolled',
IFNULL((SELECT DATE_FORMAT(MIN(FROM_UNIXTIME(log.time)),'%m/%d/%Y')
   FROM {log} log
   WHERE log.course=c.id
   AND log.userid=u.id), 'Never') AS 'First Access',
 
(SELECT IF(ue.STATUS=0, ' ', 'Withdrawn')) AS 'Withdrawn',
IFNULL((SELECT DATE_FORMAT(FROM_UNIXTIME(la.timeaccess), '%m/%d/%Y')
FROM {user_lastaccess} la
WHERE la.userid=u.id
AND la.courseid=c.id),'Never') AS 'Last Access',
 
IFNULL((SELECT COUNT(DISTINCT FROM_UNIXTIME(log.time, '%m/%d/%Y'))
FROM {log} log
WHERE log.course=c.id
AND log.userid=u.id
AND log.action='view'
AND log.module='course'
GROUP BY u.id
),'0') AS '# Days Accessed',
 
IFNULL((SELECT COUNT(gg.finalgrade) 
  FROM {grade_grades} AS gg 
  JOIN {grade_items} AS gi ON gg.itemid=gi.id
  WHERE gi.courseid=c.id
   AND gg.userid=u.id
   AND gi.itemtype='mod'
   GROUP BY u.id,c.id),'0') AS 'Activities Completed'
,
 
IFNULL((SELECT COUNT(gi.itemname) 
  FROM {grade_items} AS gi 
  WHERE gi.courseid = c.id
   AND gi.itemtype='mod'), '0') AS 'Activities Assigned'
,
 
(SELECT IF(`Activities Assigned`!='0', (SELECT IF((`Activities Completed`)=(`Activities Assigned`), 

(SELECT CONCAT('100% completed ',FROM_UNIXTIME(MAX(log.time),'%m/%d/%Y'))
FROM {log} log
WHERE log.course=c.id
AND log.userid=u.id), 

(SELECT CONCAT(IFNULL(ROUND((`Activities Completed`)/(`Activities Assigned`)*100,0), '0'),'% complete')))), 'n/a')) AS '% of Course Completed'
,
 
IFNULL(CONCAT(ROUND((SELECT (IFNULL((SELECT SUM(gg.finalgrade)
  FROM {grade_grades} AS gg 
  JOIN {grade_items} AS gi ON gi.id=gg.itemid
  WHERE gg.itemid=gi.id
   AND gi.courseid=c.id
   AND gi.itemtype='mod'
   AND gg.userid=u.id
   GROUP BY u.id,c.id),0)/(SELECT SUM(gi.grademax)
  FROM {grade_items} AS gi
  JOIN {grade_grades} AS gg ON gi.id=gg.itemid
  WHERE gg.itemid=gi.id
   AND gi.courseid=c.id
   AND gi.itemtype='mod'
   AND gg.userid=u.id
   AND gg.finalgrade IS NOT NULL
   GROUP BY u.id,c.id))*100),0),'%'),'n/a')
  AS 'Quality of Work to Date',
 
(SELECT IF(`Activities Assigned`!='0',CONCAT(IFNULL(ROUND(((SELECT gg.finalgrade/gi.grademax
FROM {grade_items} AS gi
JOIN {grade_grades} AS gg ON gg.itemid=gi.id
WHERE gi.courseid=c.id
AND gg.userid=u.id
AND gi.itemtype='course'
GROUP BY 'gi.courseid')*100),0),'0'),'%'),'n/a')) AS 'Final Score (incl xtra credit)'
 
 
FROM {user} u
JOIN {user_enrolments} ue ON ue.userid=u.id
JOIN {enrol} e ON e.id=ue.enrolid
JOIN {course} c ON c.id = e.courseid
JOIN {context} AS ctx ON ctx.instanceid = c.id
JOIN {role_assignments} AS ra ON ra.contextid = ctx.id
JOIN {role} AS r ON r.id = e.roleid
 
WHERE ra.userid=u.id
AND ctx.instanceid=c.id
AND ra.roleid='5' 
AND c.visible='1' 
GROUP BY u.id, c.id
ORDER BY u.lastname, u.firstname, c.fullname";




/* end of query */



$query = $DB->get_records_sql($sql);

foreach ($query as $mysql) {

    echo "this is niranjan";
    echo $mysql->firstname;
}
?>