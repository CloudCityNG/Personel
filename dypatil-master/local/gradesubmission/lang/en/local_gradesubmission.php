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
 * Language strings for Gradesubmission Plugin
 *
 * @package    local
 * @subpackage gradesubmission
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['msg_stu_grade'] = 'Hi {$a->username}<br> Grades submitted for class {$a->classname}.';
$string['finalgrade'] = 'Final Grade';
$string['gradeletter'] = 'Grade Letter';
$string['gradepoints'] = 'Grade Points';
$string['examname'] = 'Exam Name';
$string['pluginname'] = 'Grade submission';
$string['viewgradesubmission'] = 'View Grade Submissions';
$string['managegradesubmission'] = 'Manage Grade Submissions';
$string['allowframembedding'] = 'Here the user can submit grades to the students. To do this the Registrar/Instructor has to select ' . get_string('semester', 'local_semesters') . ', select the class, enter the marks and has to click on submit button.';
$string['errormessage'] = 'Please enter the grades below Maximum grade that can be given for any course';
$string['insertmessage'] = 'Grades Submitted Succesfully';
$string['updatemessage'] = 'Grades Updated Succesfully';
$string['error'] = 'Error occured while submitting grades.';
$string['local/gradesubmission:manage'] = 'Manage Grade Submissions';
$string['local/gradesubmission:view'] = 'View Grade Submissions';
$string['coursereq'] = 'Please select the course';
$string['submitgrades'] = 'Submit Grades';
$string['viewgrades'] = 'View Grades';
$string['nousers'] = 'No Users are Enrolled for this course.';
$string['noexam'] = 'No Exam is selected in Class Completion criteria for this course';
$string['examnotcompleted'] = 'All Exams are not yet completed for this course';
$string['stuview'] = 'Grades';
$string['studentid'] = 'Student Service ID';
$string['studentname'] = 'Student Name';
$string['score'] = 'Score';
$string['courseandclass'] = 'Course & Class';
$string['pagedescription'] = 'Here the user can submit grades to the students. To do this the Instructor has to select ' . get_string('semester', 'local_semesters') . ', select the class, enter the marks and has to click on submit button.';

/* * ***********************************************************strings for bulk upload************************************ */
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['rowpreviewnum'] = 'Preview rows';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['uploadgrades_help'] = ' The format of the file should be as follows:
* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';
$string['gradescreated'] = 'Grades Entered';
$string['gradesskipped'] = 'Grades skipped';
$string['gradesupdated'] = 'Grades updated';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing grades';
$string['uuoptype_addupdate'] = 'Add new and update existing grades';
$string['uuoptype_update'] = 'Update existing grades only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing grades details';
$string['uploadgrades'] = 'Upload Grades';
$string['back_upload'] = 'Back to Upload Grades';
$string['uploadgradespreview'] = 'Uploaded Grades Preview';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['uploaddes'] = 'Registrar can bulk upload the list of grades provided the document (file) is in the given format.<br>
For sample course sheet, click on the "Sample Excel Sheet" that downloads a sample excel sheet format to upload grades.';
$string['uploadgradessresult'] = 'Uploaded Grades Result';
$string['info'] = 'Help';
$string['info_help'] = "
<h1>View Grade Letters</h1>
Defines grade point settings at school level, where registrar is involved in setting the values for letter grades, mark interval, grade point, can manage (edit/delete/modify) recent settings.
<dl><dt><h1>Manage Grade Letters</h1></dt>
This page allows you to create or define the grade letters at school level. To add a grade letter, select the school and define the details required to create a grade letter.
<dd><h4>Letter Grade</h4>
Describes the scores in the format of letters or alphabets.</dd>
<dd><h4>Mark from </h4>
The minimum value held by the grade letter.</dd>
<dd><h4>Mark to </h4>
The maximum value held by the grade letter.</dd>
<dd><h4>Grade Point</h4>
The value given to the grade letter; used to score at the time of evaluations.</dd></dl>";
$string['help_tab'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>schoolname</td><td>Enter the existing School Name (without additional spaces) to which student belongs.</td></tr>
<tr><td>semestername</td><td>Enter the ' . get_string('semester', 'local_semesters') . ' name without additional spaces.</td></tr>
<tr><td>classname</td><td>Enter the Class name without additional spaces.</td></tr>
<tr><td>studentname</td><td>Enter the studentname (combination of firstname and lastname) without additional spaces.</td></tr>
<tr><td>examtypes</td><td>Enter grades to corresponding ' . strtolower(get_string('examtype', 'local_examtype')) . 's.</td></tr>
</table>';
$string['not_defined'] = 'Not Defined';
$string['gradesubmission:manage'] = 'gradesubmission:manage';
$string['gradesubmission:view'] = 'gradesubmission:view';
$string['noactive_semester']='Currently No active semester is available';
$string['not_assigned_dept']= 'Presently not assigned to any department.Please contact authorized user(Registrar or admin)';
$string['noactive_sem']='Presently No active semester is available.Please contact authorized user(Registrar or admin)';
