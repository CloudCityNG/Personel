<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * List the tool provided in a course
 *
 * @package    manage_departments
 * @subpackage  list of all functions which is used in departments plugin
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//  class manage_dept contsins list of functions....which is used by department plugin


function block_todays_timetable_get_studentclasslist(){
	global $DB , $CFG, $USER;
	$today = date('Y-m-d');
	$users =array();
	$users = $DB->get_records_sql("SELECT sc.*,cc.id AS courseid,
                     cc.fullname AS coursename, s.id as semid, s.fullname as sem,
                     cc.credithours AS credithours ,from_unixtime(sc.startdate,'%d-%m-%y') as ssstartdate  ,from_unixtime(sc.enddate,'%d-%m-%y') as ssenddate                 
                     FROM {local_user_clclasses} c
                     JOIN {local_clclasses} lc ON c.classid=lc.id
                     JOIN {local_scheduleclass} sc ON sc.classid=lc.id 
                     JOIN {local_cobaltcourses} cc ON cc.id=lc.cobaltcourseid 
                     JOIN {local_semester} s On s.id=c.semesterid
                     where c.userid={$USER->id} AND c.studentapproval=1 AND c.registrarapproval=1 AND '{$today}' BETWEEN DATE(FROM_UNIXTIME(sc.startdate)) and  DATE(FROM_UNIXTIME(sc.enddate))");             
	
	return $users;	
} // end of function


function block_todays_timetable_get_dayformat($day){
	global $DB , $CFG, $USER;
	 $response='';
	
			switch ($day) {
			 case "Mon":
				   $response= 'M';   
				 break;
			 case "Tue":
				  $response= 'TU';   
				 break;
			 case "Wed":
				 $response= 'W';   
				 break;
			 case "Thu":
				 $response= 'TH';   
				 break;
			 case "Fri":
				 $response= 'F';   
				 break;
			case "Sat":
				 $response= 'SA';   
				 break;
			case "Sun":
				  $response= 'SU';   
				 break;
		}
	
	return $response;
	
} // end of function

?>