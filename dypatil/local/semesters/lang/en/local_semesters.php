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
 * Language strings
 *
 * @package    local
 * @subpackage semesters
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['memodesc'] = 'Semesters-Wise Details';
$string['semestername'] = 'Semester Name';
$string['semesters'] = 'Semesters';
$string['semester'] = 'Semester';
$string['semnotstart'] = 'No clclasses assigned to you';
$string['requestsem'] = 'Requested semester';
$string['missingfullname'] = 'Please Enter Semester Name';
$string['missingsemester'] = 'Please select the semester';
$string['semester_help'] = 'Only current and upcoming semester will be displayed';
$string['pluginname'] = 'Manage Semesters';
$string['semesterlist'] = 'Semester List';
$string['report'] = 'Report';
$string['startdate'] = 'Start Date';
$string['enddate'] = 'End Date';
$string['selectsemester'] = 'Select the Semester from the filter to view the list of ' . get_string('course', 'local_cobaltcourses') . 's';
$string['mincredits'] = 'Minimum Credit Hours';
$string['maxcredits'] = 'Maximum Credit Hours';
$string['missingmincredits'] = 'Please Enter Min. Credit Hours';
$string['missingmaxcredits'] = 'Please Enter Max. Credit Hours';
$string['createsemester'] = 'Create Semester';
$string['update'] = 'Update Semester';
$string['fullnameexists'] = 'Semester exists with this name. Please change.';
$string['enddategreater'] = 'End date should be greater than the Start date.';
$string['maxcreditscannotzero'] = 'Max. Credit Hours can not be zero.';
$string['maxcreditsgreater'] = 'Max. Credit Hours should be greater than Min Credit Hours.';
$string['numericmincredits'] = 'Credit Hours must be Numeric.';
$string['numericmaxcredits'] = 'Credit Hours must be Numeric.';
$string['description'] = 'Description';
$string['createsemtabdes'] = 'This page allows the user to create/add a semester under the respective School. To add a semester, select the School for which the semester has to be created along with the duration and the required credit hours';
$string['editsemtabdes'] = 'This page allows the user to modify details of a semester under the respective School.';
$string['viewsemesterspage'] = 'This page allows you to view the list of all semesters and also to manage them (delete/edit/inactivate). Apply filters for a better view of the semesters with the help of start and end dates.';
$string['viewcursemesterspage'] = 'The current on-going semesters with its respective details are listed below. Apply filters for a better view of the semesters based on their duration. You can manage (delete/edit/inactivate) the semesters. ';
$string['viewupsemesterspage'] = 'The list of upcoming semesters with the details can be viewed here and also can be managed (delete/edit/inactivate).';
$string['createsuccess'] = 'Semester "{$a->semester}" created successfully.';
$string['updatesuccess'] = 'Semester "{$a->semester}" updated successfully.';
$string['deletesuccess'] = 'Semester "{$a->semester}" deleted successfully.';
$string['inactivesuccess'] = 'Semester "{$a->semester}" inactivated successfully.';
$string['activesuccess'] = 'Semester "{$a->semester}" activated successfully.';
$string['editsemester'] = 'Edit Semester';
$string['success'] = 'Success.';
$string['deletesemester'] = 'Delete Semester';
$string['delconfirm'] = 'Are you sure? you really want to delete this Semester!';
$string['assignedclclassesdontdelete'] = 'Sorry! some Classes are assigned to this Semester "{$a->semester}", you can not delete it. <br/> Please delete the Class and come here.';
$string['eventenableddontdelete'] = 'Sorry! Registration for this Semester "{$a->semester}" is enabled, you can not delete it. <br/> Please Disable the Registration event for this Semester and come here.';
$string['eventenabledcantupdate'] = 'Registration for this Semester is enabled, changes were not saved.';
$string['lessthannow'] = 'Semester dates should be greater than the current date.';
$string['current'] = 'Current Semesters';
$string['coming'] = 'Upcoming Semesters';
$string['currentsem'] = 'Current Semester';
$string['completedsem'] = 'Completed Semester';
$string['complete'] = 'Completed Semesters';
$string['enrolled'] = 'Enrolled';
$string['completed'] = 'Completed';
$string['incomplete'] = 'In progress';
$string['viewsemesterreportspage'] = 'The information related to student enrollments to a semester, number of active students and number of students in a semester can be viewed in semester reports.';
$string['semgpa'] = 'Semester GPA';
$string['semcgpa'] = 'Semester CGPA';
$string['misgpa'] = 'Missing Semester GPA';
$string['miscgpa'] = 'Missing Semester GPA';
$string['unassign'] = 'Unassign';
$string['assignto'] = 'Assign To';
$string['unassignedschool'] = 'Semester: "{$a->semester}" is unassigned from the School: "{$a->school}" successfully.';
$string['assignedschool'] = 'Semester: "{$a->semester}" is assigned to the School: "{$a->school}" successfully.';
$string['help'] = 'Help';
$string['eventenableddonthide'] = 'Registration for this Semester: "{$a->semester}" is enabled, You can not hide.';
$string['semesters:manage'] = 'Manage Semesters';
$string['semesters:view'] = 'View Semesters';
$string['semesterreport'] = 'Semester Report';
$string['viewsemester'] = 'View Semester';
$string['viewsemesters'] = 'View Semesters';
$string['mincrhrs'] = 'Min. Credit Hours';
$string['maxcrhrs'] = 'Max. Credit Hours';
$string['school'] = 'Select School';
$string['school_help'] = 'A semester can be created under one or more schools. If \'All\' is selected Semester is assign to all the Schools.';
$string['startdate_help'] = 'Semester should start from tomorrow onwards.';
$string['semmincrhrs'] = 'Sem. Min. Cr. Hours';
$string['semmaxcrhrs'] = 'Sem. Max. Cr. Hours';
$string['semesters'] = 'Semesters';
$string['viewallsemesters'] = 'View All Semesters';
$string['viewcurrentsemesters'] = 'View Current Semesters';
$string['viewupcomingsemesters'] = 'View Upcoming Semesters';
$string['information'] = 'The academic year is divided into semesters. Each semester has predefined credit hours. The students are supposed to fulfill the credit hour requirements for all the semesters in order to get the degree awarded.';
$string['probated'] = 'Probated';
$string['dismissed'] = 'Dismissed';
$string['info_des'] = '<h1>View Semesters</h1>
	<p>This page allows you to view the list of all semesters and also to manage them (delete/edit/inactivate). Apply filters for a better view of the semesters with the help of start and end dates.</b></p>
<h1>Create Semester</h1>
<p>This page allows the user to create/add a semester under the respective School. To add a semester, select the School for which the semester has to be created along with the duration and the required credit hours.</b></p>
<ul><li style="display:block">
<h4>Semester Name </h4>
<p>Describes the name given to a particular semester for a specific School </b></p></li>
<li style="display:block"><h4>Start Date</h4>
<p>States the starting date of the Semester applications wherein students can apply and register for a semester.</b></p></li>
<li style="display:block"><h4>End Date</h4>
<p>States the last date of the Semester applications wherein students can apply and register for a semester.</b></p></li>
<li style="display:block"><h4>Minimum Credit Hours</h4>
<p>Denotes the minimum value of the unit (credit hour) required to complete a semester. </b></p></li>
<li style="display:block"><h4>Maximum Credit Hours</h4>
<p>Denotes the maximum value of the unit (credit hour) required to complete a semester. </b></p></li></ul>
<h1>Current Semesters</h1>
	<p>The current on-going semesters with its respective details are listed below. Apply filters for a better view of the semesters based on their duration. You can manage (delete/edit/inactivate) the semesters. </b></p>
<h1>Upcoming Semesters</h1>
	<p>The list of upcoming semesters with the details can be viewed here and also can be managed (delete/edit/inactivate).</b></p>
<h1>Semester Reports </h1>
	<p>The information related to student enrollments to a semester, number of active students and number of students in a semester can be viewed in semester reports.</b></p>';
$string['semestertranscript'] = 'Semester:';
$string['noactivesemester'] = 'No active semesters';
$string['semesternotstarted'] = 'Semester not started yet ';
$string['semesters:create'] = 'semesters:Create';
$string['semesters:delete'] = 'semesters:Delete';
$string['semesters:update'] = 'semesters:Update';
$string['semesters:visible'] = 'semesters:Visible';
