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
 * @subpackage programs
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['undergraduate'] = 'Undergraduate Programs';
$string['graduate'] = 'Graduate Programs';
$string['program_title'] = 'Admissions : Program Details';
$string['ugdesc'] = '<b>Welcome to admissions for Undergraduate degree programs!</b><br>
<p>For more details on the admission requirements, program criteria etc visit our admissions page.<br>
Current offerings under Undergraduate programs are mentioned below:</p>';
$string['graduatedesc'] = '<b>Welcome to admissions for Graduate degree programs!</b><br>
<p>For more details on the admission requirements, program criteria etc visit our admissions page.<br>
Current offerings under Graduate programs are mentioned below:</p>';
$string['programdetails'] = 'Program Details';
$string['typeofprogram_error'] = 'Select Program Type';
$string['programname'] = 'Program Name';
$string['missingfullname'] = 'Please Enter Program Name';
$string['missingprogram'] = 'Please select the program';
$string['selectprogram'] = 'Select Program';
$string['program'] = 'Program';
$string['pluginname'] = 'Manage Programs';
$string['programlist'] = 'View Programs';
$string['report'] = 'Reports';
$string['assignedprogram'] = 'Assigned Program';
$string['selectdepartment'] = 'Select department';
$string['shortname'] = 'Short Name';
$string['programduration'] = 'Program Duration';
$string['description'] = 'Description';
$string['missingdepartment'] = 'Please Select department';
$string['missingshortname'] = 'Please Enter Program Shortname';
$string['missingduration'] = 'Please Enter Program Duration';
$string['createprogram'] = 'Create Program';
$string['editprogram'] = 'Edit Program';
$string['viewprogramspage'] = 'This page allows the user to view the list of all programs and also to manage (delete/edit/inactivate) the programs.';
$string['shortnameexists'] = 'Shortname already exists for another program.';
$string['numericduration'] = 'Duration must be in numeric';
$string['example'] = ' Years';
$string['suggestion'] = '(Assign Program to this department.)';
$string['deleteprogram'] = 'Delete Program';
$string['delconfirm'] = 'Are you sure? You really want to delete this program!';
$string['assignedcurriculum'] = 'Sorry! Curriculums are created under this Program "{$a->program}". You can not delete it. <br/> Please Delete the Curriculum and come here.';
$string['assignedmodule'] = 'Sorry! Modules are created under this Program "{$a->program}". You can not delete it. <br/> Please Delete the Module and come here.';
$string['assigneduser'] = 'Sorry! Users are Enrolled to this Program "{$a->program}". You can not delete it.';
$string['assignedbatch'] = 'Sorry! Batches are created under this Program "{$a->program}". You can not delete it. Please delete the Batch and come here.';
$string['createsuccess'] = 'Program "{$a->program}" created successfully.';
$string['updatesuccess'] = 'Program "{$a->program}" updated successfully.';
$string['deletesuccess'] = 'Program "{$a->program}" deleted successfully.';
$string['success'] = 'Success';
$string['programs:manage'] = 'Manage Programs';
$string['programs:view'] = 'View Programs';
$string['userassignedcannotupdate'] = 'Users are enrolled to the program "{$a->program}", you can not update.';
$string['fullnameexists'] = 'Program exists with this name. Please change.';
$string['programlevel'] = 'Program Level';
$string['dd'] = 'Download Details';
$string['programtype'] = 'Program Type';
$string['programlevel'] = 'Program Level';
$string['nonzeroduration'] = 'Duration can not be zero.';
$string['eventenabledcantupdate'] = 'Admission event is enabled for this program: "{$a->program}". Changes were not saved.';
$string['eventenabledcantdelete'] = 'Sorry! Admission event is enabled for this Program "{$a->program}", You can not delete. <br/> Please Disable the Admission event and come here.';
$string['eventenableddonthide'] = 'Admission event is enabled for this Program "{$a->program}", You can not Hide it.';
$string['userassigneddonthide'] = 'Users are enrolled to the Program "{$a->program}", you can not Hide it.';
$string['duration'] = 'Duration';
$string['programreport'] = 'Program Report';
$string['programs'] = 'Programs';
$string['back_upload'] = 'Back to Upload Programs';
$string['programdetails'] = 'Program Details';
$string['viewprogram'] = 'View Program';
$string['batchname'] = 'Batch';
$string['uploaddes'] = 'Registrar can bulk upload the list of programs provided the document (file) 
is in the given format.<br>For sample course sheet, click on the "Sample Excel Sheet" that downloads 
a sample excel sheet format to upload programs.';
$string['createprogramspage'] = 'This page allows you to create/define programs under an institution.<br>
To create a program - select the institution, define the program name and enter the program short name (code), 
duration of the program, program type, and program level. Provide the description for the program (if required) 
and then click on "Create Program".';
$string['editprogramspage'] = 'This page allows you to modify existing  program details under an institution.';
$string['createprogram_help'] = '<b>To create a program</b> - Select the institution, define the program name and enter the program short name (code), duration of the program, program type, and program level. <p>Provide the description for the program (if required) and then click on "Create Program".</p>';
$string['programname_help'] = 'Defines the name of the program to be created under an institution.';
$string['shortname_help'] = 'A unique set of characters used to denote the program throughout the site.';
$string['programtype_help'] = 'Set the type of program using the 2 options � Offline or Online. 
<p><b>Note*:</b> The default value is Online. It can be changed based on the program. </p>
<p><b>Online: </b>The program will be offered online via internet. Students can attend the 
clclasses through virtual classrooms.</p><p><b>Offline: </b>The program will NOT be offered online. Students have to attend the clclasses.</p>';
$string['programlevel_help'] = 'Denotes the level of program to be created.
<p><b>Undergraduate:</b> Post-secondary education usually involves 4 years of studies like bachelor\'s degree. </p>
<p><b>Graduate:</b> Post-secondary education usually involves 2 years of studies like master\'s degree, doctorate etc. </p>
<p><b>Note*:</b> The level of program depends on the type of institution and can be changed or defined at any point of time. The default value is "Undergraduate".</p>';
$string['updateprogram'] = 'Update Program';
$string['modulename'] = 'Module';
$string['assigntodepartment'] = 'Assign to department';
$string['viewprogramreportspage'] = 'Program Reports';
$string['noofactive'] = 'No. of Active Students';
$string['noofconcluding'] = 'No. of Students Concluding Program';
$string['uploadprograms'] = 'Upload Programs';
$string['help'] = 'Help';
$string['help_tab'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<tr><th>Field</th><th>Restriction</th></tr>
<tr><td>schoolname</td><td>Enter the existing ' . get_string('schoolid', 'local_collegestructure') . ' Name (without additional spaces) under which you want to create new program.</td></tr>
<tr><td>fullname</td><td>Enter the Program Name without additional spaces.</td></tr>
<tr><td>shortname</td><td>Enter the Program Shortname without additional spaces.</td></tr>
<tr><td>duration</td><td>Enter  Duration of the program (in numeric values only).</td></tr>
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<tr><th>Field</th><th>Restriction</th></tr>
<tr><td>type</td><td>Enter any number as given below. 1 - Online</br>2 - Offline</td></tr>
<tr><td>programlevel</td><td>Enter any number as given below. 1 - Undergraduate</br>2 - Graduate</td></tr>
<tr><td>description</td><td>Enter program description</td></tr>
</table>';
$string['no_curriculum'] = "<h3>No Curriculums Assigned.</h3>";
$string['no_batches'] = "<h3>No Batches Assigned.</h3>";
$string['no_modules'] = "<h3>No Modules Assigned.</h3>";
$string['curr_module'] = "<br/><h4>Curriculums and Modules Assigned to this Program</h4>";
/* * ***********************************************************strings for bulk upload************************************ */
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploadprogram_help'] = ' The format of the file should be as follows:
* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';
$string['uploadprogramspreview'] = 'Upload Programs Preview';
$string['uploadprogramsresult'] = 'Upload Programs Result';
$string['programscreated'] = 'Programs created';
$string['programsskipped'] = 'Programs skipped';
$string['programsupdated'] = 'Programs updated';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing programs';
$string['uuoptype_addupdate'] = 'Add new and update existing programs';
$string['uuoptype_update'] = 'Update existing programs only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing program details';
$string['uploadprograms'] = 'Upload Programs';
$string['activesuccess'] = 'Program "{$a->program}" Activated Successfully.';
$string['inactivesuccess'] = 'Program "{$a->program}" Inactivated Successfully.';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['helpinfo'] = 'A program is defined as an academic qualification that is awarded to enrolled students for satisfying the completion criteria. Colleges offer both undergraduate and graduate programs which in turn have various courses.';
$string['alreadyexistadmission'] = 'Admission already opened for this program without enddate';
$string['help_desc'] = '
<h1>View programs</h1>
This page allows the user to view the list of all programs and also to manage (delete/edit/inactivate) the programs.</b>

<h1>Create Program</h1> 
<p>This page allows you to create/define programs under an institution.</b></p>
<p><b>Note:*</b>To create a program - select the institution, define the program name and enter the program short name (code), duration of the program, program type, and program level. Provide the description for the program (if required) and then click on "Create Program".</p>
<ul style="diplay:block">
<li style="display:block"><h4>Program Name</h4>
<p>Defines the name of the program to be created under an institution</b></p></li>
<li style="display:block"><h4>Short Name</h4>
<p>A unique set of characters used to denote the program throughout the site</b></p></li>
<li style="display:block"><h4>Program Type</h4>
<p>Set the type of program using the 2 options - Offline or Online</b></p> 
<p><b>Note*:</b> The default value is Online. It can be changed based on the program.</p>
<p><b>Online - </b> The program will be offered online via internet. Students can attend the clclasses through virtual classrooms.</p>
<p><b>Offline - </b> The program will NOT be offered online. Students have to attend the clclasses. </p></li>
<li style="display:block"><h4>Program Level</h4> 
<p>Denotes the level of program to be created</b></p>
<p><b>Undergraduate - </b> Post-secondary education usually involves 4 year of studies like bachelor\'s degree</p>
<p><b>Graduate - </b>Post-secondary education usually involves 2 years of studies like master\'s degree, doctorate etc.</p>
<p><b>Note*:</b> The level of program depends on the type of institution and can be changed or defined at any point of time. The default value is \'Undergraduate\'.</p></li>
<li style="display:block"><h4>Select department</h4>
<p>Select the department under which the program has to be defined. 
The default value is \'None\'. 
Text to display in the form - Assign Program to this department</p></li></ul>';
$string['programs:create'] = 'programs:Create';
$string['programs:delete'] = 'programs:Delete';
$string['programs:update'] = 'programs:Update';
$string['programs:visible'] = 'programs:Visible';
$string['static_prgtypeinfo'] = '<span style="color:green;">If selected <b>Online</b>: Please create admission event using <b>Events Calendar</b> link in University structure block to open admission for students</span>';
