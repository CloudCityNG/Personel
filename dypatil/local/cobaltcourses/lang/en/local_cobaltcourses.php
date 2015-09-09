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
 * @subpackage managecobaltcourses
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['enroll']='Enroll';
$string['coursename'] = 'Course Name';
$string['course'] = 'Course';
$string['pluginname'] = 'Manage Courses';
$string['createcourse'] = 'Create Course';
$string['editcourse'] = 'Edit Course';
$string['selectdepartment'] = 'Select department';
$string['missingdepartment'] = 'Please Select a department';
$string['courseid'] = 'Course ID';
$string['missingshort'] = 'Please Enter Course ID';
$string['missingcourse'] = 'Please Enter Course name';
$string['description'] = 'Description';
$string['coursetype'] = 'Course Type';
$string['credithours'] = 'Credit Hours';
$string['coursecost'] = 'Course Cost (in dollars)';
$string['back_upload'] = 'Back to Upload Courses';
$string['deletecourse'] = 'Delete Course';
$string['curriculum'] = 'Curriculum';
$string['deleteconfirm'] = 'Do you really want to Delete this Course?';
$string['create'] = 'Create Course';
$string['department'] = 'Department';
$string['type'] = 'Type';
$string['descriptionforviewpage'] = 'This page displays the list of all courses defined under a department. You can also manage (delete/edit/inactivate) the courses using the tools given under "actions".Apply filters for a better view of the courses based on the course type and department.<br>Note*: Download courses button allows you to download a copy of all the courses defined for the different department.';
$string['insertequivalentsuccess'] = 'Equivalent course for the course: "{$a->fullname}" assigned successfully.';
$string['deleteequivalentsuccess'] = 'Equivalent course for the course: "{$a->fullname}" unassigned successfully.';
$string['insertprerequisitesuccess'] = 'Prerequisite course for the course: "{$a->fullname}" assigned successfully.';
$string['deleteprerequisitesuccess'] = 'Prerequisite course for the course: "{$a->fullname}" unassigned successfully.';
$string['action'] = 'Action';
$string['missingcredithours'] = 'You must supply a number here';
$string['missinghours'] = 'Please Enter Credit Hours';
$string['missingcost'] = 'Enter Course Cost';
$string['delconfirm'] = 'Are you sure? You really want to Delete this course?';
$string['courseidexists'] = 'Course exists with same Course ID';
$string['viewdetails'] = 'View Course Details';
$string['coursecreatepage'] = 'This page allows the user to create/add a course under the respective department. To add a course, the user has to select the school, select the department, enter all the required fields and then has to click on "Create".';
$string['courseeditpage'] = 'This page allows the user to edit a course under the respective department.';
$string['addnew'] = 'Add New';
$string['courselist'] = 'View Courses';
$string['report'] = 'Reports';
$string['usersenrolleddelete'] = 'Sorry! users are enrolled to this course "{$a->course}", You can not delete it.';
$string['assignedtocurriculumdelete'] = 'Sorry! this course "{$a->course}" is assigned to a curriculum, You can not delete it. <br/> Please unassign this course from the curriculum and come here.';
$string['assignedtoclassdelete'] = 'Sorry! this course "{$a->course}" is assigned to a Class, You can not delete it. <br/> Please delete the Class and come here.';
$string['enrollments'] = 'Enrollments';
$string['enrollcourse'] = 'Enroll into course';
$string['gotocourse'] = 'Go to Course';
$string['nodes'] = 'There is no description added for this Author.Please add description in user profile.';
$string['viewreportpage'] = 'The report of the no. of students enrolled to the Cobalt courses in the particular program.';
$string['equivalent'] = 'Equivalent';
$string['prerequisite'] = 'Prerequisite';
$string['courseequivalentpage'] = 'Course equivalents are courses that relate or are similar to courses offered by two different institutions. Such courses can be defined here. 
<br>To add a prerequisite for a course, select the course from the department and/or school for which the equivalence has to be defined. Then select the course(s) from a department as an equivalent course and click 
on "Equivalent Course".';
$string['courseprerequisitepage'] = 'Course prerequisites are the conditions or requirements that have to be satisfied in order to take a particular course. Such conditions can be defined here. <br>
To add a prerequisite for a course, select the course from the department and/or school for which the prerequisite has to be defined. Then select the course(s) from a department as a prerequisite and click on "Prerequisite Course".	<br>
Note*: Multiple courses can be selected as a course prerequisite for a particular course.';
$string['helpdestab'] = 'Registrar can bulk upload the list of courses using files or documents provided the document (file) is in the given prescribed format.<br>For sample course sheet, click on the "Sample Excel Sheet" that downloads a sample excel sheet format to upload courses.';
$string['helpinfo'] = 'A course in CobaltLMS is defined as the smallest unit of teaching. Courses are added to departments that are under schools. A cobalt course may have the prerequisite criteria and equivalents.';
$string['courseequivalent'] = 'Course Equivalent';
$string['courseprerequisite'] = 'Course Pre-requisite';
$string['equicourse'] = 'Equivalent Course';
$string['equidept'] = 'Department (of Equivalent Course)';
$string['selectcourse'] = 'Select Course';
$string['missingcourse'] = 'Please Enter Course Name';
$string['deleteequivalent'] = 'Unassign Equivalent Course';
$string['deleteequivalentconfirm'] = 'Are you sure? You really want to unassign this equivalent course!';
$string['equivalentrecordexists'] = 'Record exists already in the table.';
$string['prerecordexists'] = 'Record exists already in the table.';
$string['precourse'] = 'Prerequisite Course';
$string['predept'] = 'Department(of Prerequisite Course)';
$string['deleteprerequisite'] = 'Unassign Prerequisite Course';
$string['deleteprerequisiteconfirm'] = 'Are you sure? You really want to unassign this prerequisite course!';
$string['help'] = 'Help';
$string['classtype'] = 'Class type';
$string['instructor'] = 'Instructor';
$string['usersenrolledupdate'] = 'Users are enrolled to this course "{$a->fullname}", you can not edit.';
$string['cobaltcourses:manage'] = 'Manage Cobalt Courses';
$string['cobaltcourses:view'] = 'View Cobalt Courses';
$string['creditscannotzero'] = 'Credit Hours can not be zero.';
$string['usersenrolledhide'] = 'Users are enrolled to this course "{$a->fullname}", you can not Hide it.';
$string['assignedtoclasshide'] = 'This Course "{$a->fullname}" is assigned to a Class. You can not Hide it.';
$string['success'] = 'Success';
$string['deletesuccess'] = 'Course "{$a->course}" deleted successfully.';
$string['assignedtocurriculumhide'] = 'Course "{$a->fullname}" is assigned to a curriculum, You can not Hide it.';
$string['assignedtoclassupdate'] = 'Course "{$a->course}" is assigned to a Class, Changes were not saved.';
$string['updatesuccess'] = 'Course "{$a->course}" updated Successfully.';
$string['updatefails'] = 'Sorry! Course "{$a->course}" is not updated.';
$string['createsuccess'] = 'Course "{$a->course}" created Successfully.';
$string['createfails'] = 'Sorry! Course "{$a->course}" is not Created.';
$string['coursereport'] = 'Course Report';
$string['action'] = 'Action';
$string['dd'] = 'Download Details';
$string['activatesuccess'] = 'Course "{$a->course}" Activated Successfully.';
$string['inactivatesuccess'] = 'Course "{$a->course}" Inactivated Successfully.';
$string['updatecourse'] = 'Update Course';
$string['coursetype_help'] = 'This setting determines type of the course appears in the list of courses.';
$string['general'] = 'General';
$string['elective'] = 'Elective';
$string['author_introduction'] ="Author Introduction";
$string['shortname'] = 'Shortname';
/*---strings for bulk upload---*/
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['rowpreviewnum'] = 'Preview rows';
$string['nochanges'] = 'No changes';
$string['uploadcourses'] = 'Upload Courses';
$string['uploadcourses_help'] = ' The format of the file should be as follows:
* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';
$string['uploadcoursespreview'] = 'Upload Courses preview';
$string['uploadcoursesresult'] = 'Upload Courses result';
$string['courseaccountupdated'] = 'Courses updated';
$string['courseaccountuptodate'] = 'Courses up-to-date';
$string['coursescreated'] = 'Courses created';
$string['coursesskipped'] = 'Courses skipped';
$string['coursesupdated'] = 'Courses updated';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing courses';
$string['uuoptype_addupdate'] = 'Add new and update existing courses';
$string['uuoptype_update'] = 'Update existing courses only';
$string['uupasswordcron'] = 'Generated in cron';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing course details';
$string['uploadcoursespreview']='Uploaded courses preview';
$string['uploadcourses']='Upload Courses';
$string['upload'] = 'Upload ';
$string['download_all'] = 'Download Courses';
$string['cobaltcourses'] = 'Courses';
$string['helpmanual']='Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual']='Help Manual';
$string['info_help'] = '<h1>View Courses</h1>
This page displays the list of all courses defined under a department. You can also manage (delete/edit/inactivate) the courses using the tools given under ‘actions’. Apply filters for a better view of the courses based on the course type and department. </br>
<b>Note*:</b> Download courses button allows you to download a copy of all the courses defined for the different departments.
<dl><dt><h1>Create Course</h1></dt>
This page allows the user to create/add a course under the respective department. To add a course, the user has to select the school, select the department, enter all the required fields and then has to click on ‘Create’.
<dd><h4>Course Name </h4>
The name given to a particular course based on its concept/context defined under a specific department.</dd>
<dd><h4>Course ID</h4>
The unique set of characters defined for a course that can be used for further access. </dd>
<dd><h4>Course Type</h4>
The type of the course defined/created under a department.</br>
<b>General /Core</b> – Courses that build knowledge base under the guidance of Subject Matter Experts (SMEs) and are offered throughout the college.</br>
<b>Electives</b> – Educational Courses that are NOT included in the curriculum and often relate to personal interest or experiences.</dd>
<dd><h4>Credit Hours</h4>
A unit that holds a value for the course provided it satisfies the requirements.</dd>
<dd><h4>Offered As</h4>
Choose from the option given. Select Free if the course is offered for Free; otherwise select Paid. Provide the cost of the paid course in the currency that best suits the course offering.</dd></dl>
<dt><h1>Prerequisites</h1></dt>
<dd>Course prerequisites are the conditions or requirements that have to be satisfied in order to take a particular course. Such conditions can be defined here.</dd> 
<dd>To add a prerequisite for a course, select the course from the department and/or school for which the prerequisite has to be defined. Then select the course(s) from a department as a prerequisite and click on ‘Prerequisite Course’.	</dd>
<dd><b>Note*:</b> Multiple courses can be selected as a course prerequisite for a particular course.</dd></dl>
<dl><dt><h1>Equivalent</h1></dt>
<dd>Course equivalents are courses that relate or are similar to courses offered by two different institutions. Such courses can be defined here.</dd> 
<dd>To add a prerequisite for a course, select the course from the department and/or school for which the equivalence has to be defined. Then select the course(s) from a department as an equivalent course and click on ‘Equivalent Course’.
</dd>
</dl>
<dl><dt><h1>Upload Courses</h1></dt>
<dd>Registrar can bulk upload the list of courses using files or documents provided the document (file) is in the given prescribed format.
For sample course sheet, click on the ‘Sample Excel Sheet’ that downloads a sample excel sheet format to upload courses.</dd>';
$string['help_string'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>schoolname</td><td>Enter the existing school name (without additional spaces) under which you want to create new course.</td></tr>
<tr><td>departmentname</td><td>Enter the existing department name (without additional spaces) under which you want to create new course.</td></tr>
<tr><td>fullname</td><td>Enter the fullname of course without additional spaces.</td></tr>
<tr><td>shortname</td><td>Enter the shortname of the course without additional spaces.</td></tr>
<tr><td>credithours</td><td>Enter the credithours for course (in numeric values only).</td></tr>
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>coursetype</td><td>Enter any number as given below. </br>0 - General</br>1 - Elective</td></tr>
<tr><td>coursecost</td><td>Enter coursecost (in numeric values only).</td></tr>
<tr><td>description</td><td>Enter course description.</td></tr>
</table>';
$string['authorintro'] = 'Author Introduction';
$string['authorintronav'] = 'Overview Cobalt Course';
$string['coursesum'] = 'Course Summary';
$string['authorintronavbar'] = 'Overview of Course';
$string['cobaltcourses:update']='cobaltcourses:Update';
$string['cobaltcourses:create']='cobaltcourses:Create';
$string['cobaltcourses:delete']='cobaltcourses:Delete';
$string['cobaltcourses:visible']='cobaltcourses:Visible';
$string['cobaltcourses:courseprerequisiteassign']='cobaltcourses:Assign cobaltcourse prerequisite';
$string['cobaltcourses:courseprerequisiteunassign']='cobaltcourses:Unassign cobaltcourse Prerequisite';
$string['cobaltcourses:courseequivalentassign']='Assign cobaltcourse equivalent';
$string['cobaltcourses:courseequivalentunassign']='Unassign cobaltcourse equivalent';

