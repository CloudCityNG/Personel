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
 * @subpackage scheduleexam
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['msg_stu_exam'] = 'Hi {$a->username}<br> exam scheduled for class {$a->classname}.';
$string['pluginname'] = 'Examinations';
$string['pageheading'] = "Manage Examinations";
$string['view'] = 'View Scheduled Exams';
$string['info'] = 'Help';
$string['reports'] = 'Reports';
$string['createexamheader'] = 'Create Exam';
$string['createexamheader_help'] = 'Create an examination for a particular class on the basis of semester/program/ school.';
$string['editexamheader'] = 'Edit Exam';
$string['editexamheader_help'] = 'Edit an examination for a particular class on the basis of semester/program/ school.';
$string['viewscheduledexams'] = 'View Scheduled Exams';
$string['allowframembedding'] = 'This page displays the list of exams scheduled for a particular course under a Program, School and can be viewed or managed (delete/edit/inactivate) using the tools under Actions.';
$string['allowframembedding1'] = 'The lists of examinations scheduled under a particular semester are listed below.  Using the filters, customize the view of examinations based on exam, course, date, and semester.';
$string['allowframembedding'] = 'This page displays the list of exams scheduled for a particular course under a program/School and can be viewed or managed (delete/edit/inactivate) using the tools under Actions.';
$string['allowframembedding1'] = 'The lists of examinations scheduled under a particular semester are listed below.  Using the filters, customize the view of examinations based on exam, course, date, and semester.';
$string['delconfirm'] = 'Do you really want to delete this exam?';
$string['deletescheduleexam'] = 'Delete Exam';
$string['examname'] = 'Exam';
$string['examtypereq'] = "Please select " . get_string('examtype', 'local_examtype') . "";
$string['lecturetype'] = 'Mode of Exam';
$string['lecturetypereq'] = "Please select Mode of Exam";
$string['opendate'] = 'Exam Date';
$string['opendatereq'] = "Please select Open date";
$string['starttimehour'] = 'Start Time Hour';
$string['starttimehourreq'] = "Exam Start time required";
$string['endtimehour'] = 'End Time Hour';
$string['endtimehourreq'] = "Exam End time required";
$string['examstatus'] = 'Exam Status';
$string['examstatusreq'] = 'Please select Exam Status';
$string['grademin'] = 'Minimum Grade';
$string['grademinreq'] = 'Please enter Minimum Grade';
$string['grademinnum'] = 'Minimum Grade should be a numeric value';
$string['grademax'] = 'Maximum Grade';
$string['grademaxreq'] = 'Please enter Maximum Grade';
$string['grademaxnum'] = 'Maximum Grade should be a numeric value';
$string['examweightage'] = 'Exam Weightage (%)';
$string['examweightagereq'] = 'Please enter Exam Weightage';
$string['examweightagenum'] = 'Exam Weightage should be a numeric value';
$string['scheduledexams'] = 'Scheduled Exams';
$string['opendateandtime'] = 'Exam Date and Time';
$string['examexits'] = 'Exam already Scheduled';
$string['datevalidation'] = 'Exam date should be in between the semester dates ({$a->startdate} - {$a->enddate})';
$string['starttime'] = 'Start time';
$string['endtime'] = 'End time';
$string['missingstarttimehour'] = 'Missing Start time';
$string['missingendtimehour'] = 'Missing End time';
$string['timevalidation'] = 'End time should be greater than Start time';
$string['editop'] = 'Action';
$string['scheduleexam:manage'] = 'Manage Scheduling Exams';
$string['scheduleexam:view'] = 'View Scheduled Exams';
$string['examdetails'] = 'Exam Details';
$string['success'] = 'Exam successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate Exam.';
$string['helpinfo'] = 'Students of various programs are assessed based on the examinations. Examinations are of different types. Students have to fulfill all the examination requirements for completing the Program.';
$string['createexamdes'] = 'This page allows you to schedule an examination for a particular class on the basis of Semester/Program/ School by filling the necessary details like mode of exam, exam type, duration of the exam and grades. Click on "Schedule Exam" to schedule an exam for a class.';
$string['editexamdes'] = 'This page allows you to modify an examination for a particular class on the basis of Semester/Program/ School by filling the necessary details like mode of exam, exam type, duration of the exam and grades. Click on "Update Exam" to update details of an exam for a class.';
$string['updatesuccess'] = '"{$a->exam_name}" exam of "{$a->classname}" class under "{$a->sem_name}" is updated successfully.';
$string['createsuccess'] = '"{$a->exam_name}" exam for "{$a->classname}" class under "{$a->sem_name}" is created successfully.';
$string['deletesuccess'] = '"{$a->exam_name}" exam of "{$a->classname}" class under "{$a->sem_name}" is deleted successfully.';
/* * ***********************************************************strings for bulk upload************************************ */
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['rowpreviewnum'] = 'Preview rows';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['uploadexam_help'] = ' The format of the file should be as follows:
* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';
$string['examscreated'] = 'Exams created';
$string['examsskipped'] = 'Exams skipped';
$string['examsupdated'] = 'Exams updated';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing exams';
$string['uuoptype_addupdate'] = 'Add new and update existing exams';
$string['uuoptype_update'] = 'Update existing exams only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing exam details';
$string['uploadexams'] = 'Upload Exams';
$string['uploadexamspreview'] = 'Uploaded Exams Preview';
$string['uploadexamsresult'] = 'Upload Exams Result';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['uploaddes'] = 'Registrar can bulk upload the list of exams provided the document (file) is in the given format.<br>
For sample course sheet, click on the "Sample Excel Sheet" that downloads a sample excel sheet format to upload exams.';
$string['dd'] = 'Download Details';
$string['exam'] = 'Exams';
$string['programreq'] = 'Please select the program';
$string['info_help'] = "
<h1>View Scheduled Exams</h1>
This page displays the list of exams scheduled for a particular course under a Program/School and can be viewed or managed (delete/edit/inactivate) using the tools under Actions.
<dl><dt><h1>Create Exam</h1></dt>
This page allows you to schedule an examination for a particular class on the basis of Semester/Program/ School by filling the necessary details like mode of exam, exam type, duration of the exam and grades.
<dd><h4>Exam Type</h4>
Select the exam types that are defined and are listed below.</dd>
<dd><h4>Exam Mode</h4>	
Select the mode of exam i.e. how it has to be conducted for the particular class.</br>
<b>Theory</b> - Select if it is a written (theory) exam.</br>
<b>Laboratory</b> - Select if it is a Lab exam.</br>
<b>Seminar</b> - Select if it has to be conducted as a seminar/group activity exam.</br>
<b>Workshop</b> - Select if it has to be conducted as a workshop activity exam.</br></dd>
<dd><h4>Exam Date</h4>
States the date of examination in order to conduct the examination for the particular class.</dd>
<dd><h4>Start – End Time</h4>
Defines the duration of the examination for that particular date.</dd>
<dd><h4>Minimum-Maximum Grades</h4>
The values that are used to score the particular exam/course at the time of evaluation.</dd>";
$string['message'] = '<h5>Grades are already submitted for this exam, you cannot delete this Exam</h5>';
$string['help_string'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>schoolname</td><td>Enter the existing school Name (without additional spaces).</td></tr>
<tr><td>semestername</td><td>Enter the semester name without additional spaces.</td></tr>
<tr><td>classname</td><td>Enter the class name without additional spaces.</td></tr>
<tr><td>lecturetype</td><td>Enter the mode of exam without additional spaces.</td></tr>
<tr><td>examtype</td><td>Enter the ' . get_string('examtype', 'local_examtype') . ' without additional spaces.</td></tr>
<tr><td>opendate</td><td>Enter commencement date of exam. Format should be mm/dd/yyyy</td></tr>
<tr><td>starttimehour</td><td>Enter starttimehour of exam. Range varies from 00 to 23.</td></tr>
<tr><td>endtimehour</td><td>Enter endtimehour of exam. Range varies from 00 to 23.</td></tr>
<tr><td>starttimemin</td><td>Enter starttimemin of exam. Range varies from 00 to 59.</td></tr>
<tr><td>endtime min</td><td>Enter endtimemin of exam. Range varies from 00 to 59.</td></tr>
</table>';
$string['delimited'] = 'Excelsheet should be saved in CSV (Comma delimited) format.';
$string['back_upload'] = 'Back to Upload Exams';
$string['class_datevalidation'] = 'Exam date should be greater than class scheduled date ({$a->startdate})';
$string['scheduleexam:create'] = 'scheduleexam:Create';
$string['scheduleexam:update'] = 'scheduleexam:Update';
$string['scheduleexam:delete'] = 'scheduleexam:Delete';
$string['scheduleexam:visible'] = 'scheduleexam:Visible';
