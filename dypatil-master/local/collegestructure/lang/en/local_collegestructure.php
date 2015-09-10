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
 * @subpackage School
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['missingtheme'] = 'Select Theme';
$string['theme'] = 'Theme Name';
$string['msg_del_reg_schl'] = 'Hi {$a->username}<br> You are un assigned from school {$a->schoolname}.';
$string['msg_add_reg_schl'] = 'Hi {$a->username}<br> You are assigned to school {$a->schoolname}.';
$string['assignrole_help'] = 'Assign a role to the user in the selected School/College.';
$string['assignedschool'] = 'Assigned School/College';
$string['assignschool_help'] = 'Assign this user to a School/College.';
$string['anyschool'] = 'Any School/College';
$string['campus'] = 'Campus';
$string['university'] = 'University';
$string['location'] = 'Location';
$string['schoollevel'] = 'School/College Level';
$string['assignedtoschools'] = 'Assigned to School/College';
$string['assignschool'] = 'Assign Schools/Colleges';
$string['notassignedschool'] = 'Sorry you are not assigned to any School/College.';
// $string['schoolname']='School / College Name';
$string['schoolscolleges'] = 'Schools/Colleges';
$string['schoolid'] = 'School/College';
$string['schoolrequired'] = 'School field is mandatory';
$string['missingschool'] = 'Please select the school/college';
$string['select'] = 'Select School/College';
$string['schoolname'] = 'School/College Name';
$string['universitysettings'] = 'University Settings';
$string['cobaltLMSentitysettings'] = 'CobaltLMS Entity Settings';
$string['schoolsettings'] = 'School/College Settings';
$string['GPA/CGPAsettings'] = 'GPA/CGPA Settings';
$string['PrefixandSuffix'] = 'Prefix and Suffix';
$string['assignregistrar_title'] = 'College Structure : Assign Registrars';
$string['pluginname'] = 'College Structure';
$string['manageschools'] = 'Manage College Structure';
$string['allowframembedding'] = 'This page allows you to manage (delete/edit) the schools that are defined under this institution.';
$string['description'] = 'Description';
$string['deleteschool'] = 'Delete School/College';
$string['delconfirm'] = 'Do you really want to delete this School/College?';
$string['createschool'] = 'Create College/College';
$string['editschool'] = 'Edit School/College';
$string['missingschoolname'] = 'Please Enter School Name';
$string['viewschool'] = 'View School/College';
$string['top'] = 'Top';
$string['parent'] = 'Parent';
$string['parent_help'] = "To create a New school/college at Parent Level, please select 'Parent' ";
$string['department'] = 'Department';
$string['assignusers'] = 'Assign Managers';
$string['viewusers'] = 'View Users';
$string['unassign'] = 'Un assign';
$string['username'] = 'Manager Name';
$string['noprogram'] = 'No program is assigned';
$string['nodepartment'] = 'No department is assigned';
$string['selectschool'] = 'TOP Level';
$string['createsuccess'] = 'School/College with name "{$a->school}" created successfully';
$string['updatesuccess'] = 'School/College with name "{$a->school}" updated successfully';
$string['deletesuccess'] = 'Deleted Successfully';
$string['type'] = 'Type';
$string['type_help'] = 'Please select your School/College Type. If it is "University" please select University as Type. If it is "Campus"  select Campus as Type.';
$string['chilepermissions'] = 'Do we need to allow the manager to see child courses of this school.';
$string['create'] = 'Create School/College';
$string['update_school'] = 'Update School/College';
$string['view'] = 'View Schools/Colleges';
$string['assignregistrar'] = 'Assign Managers';
$string['info'] = 'Help';
$string['reports'] = 'Reports';
$string['alreadyassigned'] = 'Already user is assigned to selected school/college "{$a->school}"';
$string['assignedsuccess'] = 'Successfully assigned manager to school/college.';
$string['permissions'] = 'Permissions';
$string['permissions_help'] = 'Do we need to allow the manager to see child courses of this school/college.';
$string['programname'] = 'Program Name';
$string['unassignregistrar'] = "Are you sure? You really want to unassign Manager?";
$string['unassingheading'] = 'Unassign Manager';
$string['unassignedsuccess'] = 'Successfully Unassigned Manager from school/college';
$string['problemunassignedsuccess'] = 'There is a problem in Unassigning manager from school/college';
$string['assignedfailed'] = 'Error in assigning a user';
$string['cannotdeleteschool'] = 'As the school "{$a->scname}" has program/department, you cannot delete it. Please delete the assigned departments or programs first and come back here. ';
$string['nousersyet'] = 'No User is having Manager Role';
$string['departmentname'] = 'Department Name';
$string['saction'] = 'Action';
$string['assignregistrartxt'] = "Assign the manager to a school by selecting the respective manager, next selecting the respective organizations and then clicking on 'Assign Manager' ";
$string['collegestructure:manage'] = 'collegestructure:manage';
$string['collegestructure:view'] = 'collegestructure:view';
$string['nopermissions'] = 'Sorry, You dont have Permissions ';
$string['errormessage'] = 'Error Message';
$string['assign_school'] = 'Assigned Schools/Colleges';
$string['programsanddepartments'] = "<h3>Programs and departments Assigned to this School/College</h3>";
$string['success'] = 'School "{$a->school}" successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate School.';
/* * **strings for bulk upload*** */
$string['allowdeletes'] = 'Allow deletes';
$string['csvdelimiter'] = 'CSV delimiter';
$string['defaultvalues'] = 'Default values';
$string['deleteerrors'] = 'Delete errors';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploadschools'] = 'Upload schools';
$string['uploadschool_help'] = ' The format of the file should be as follows:
* Please download sample excelsheet through button provided .
* Enter the values based upon the information provided in Information/help tab';
$string['uploadschoolspreview'] = 'Upload schools preview';
$string['uploadschoolsresult'] = 'Upload schools results';
$string['schoolaccountupdated'] = 'schools updated';
$string['schoolaccountuptodate'] = 'schools up-to-date';
$string['schooldeleted'] = 'school deleted';
$string['schoolscreated'] = 'schools created';
$string['schoolsdeleted'] = 'schools deleted';
$string['schoolsskipped'] = 'schools skipped';
$string['schoolsupdated'] = 'schools updated';
$string['uubulk'] = 'Select for bulk school actions';
$string['uubulkall'] = 'All schools';
$string['uubulknew'] = 'New schools';
$string['uubulkupdated'] = 'Updated schools';
$string['uucsvline'] = 'CSV line';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing schools';
$string['uuoptype_addupdate'] = 'Add new and update existing schools';
$string['uuoptype_update'] = 'Update existing schools only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uuupdatetype'] = 'Existing school details';
$string['uploadschools'] = 'Upload schools';
$string['uploadschool'] = 'Upload schools';
$string['schoolnotaddedregistered'] = 'schools not added, Already manager';
$string['newschool'] = 'New program created';
$string['parentid'] = 'Parentid';
$string['uploadschoolspreview'] = 'Uploaded schools preview';
$string['visible'] = 'Visible';
$string['duration'] = 'Duration';
$string['timecreated'] = 'Time Created';
$string['timemodified'] = 'Time modofied';
$string['schoolmodified'] = 'school modified';
$string['description'] = 'Description';
$string['uploadschoolspreview'] = 'Uploaded schools Preview';
$string['uploadschools'] = 'Upload schools';
$string['schools'] = 'Schools';
$string['no_user'] = "No user is assigned till now";
$string['information'] = 'A School in Cobalt Learning Management System is defined as college/institution that offers program(s). The School(s) is instructed/disciplined by Instructor(s). A School has its own programs and departments. ';
$string['addschooltabdes'] = 'This page allows you to create/define a new school/college.<br> 
Fill in the following details and click on  create college to create a new college.';
$string['editschooltabdes'] = 'This page allows you to edit school/college.<br> 
Fill in the following details and click on  Update School/College.';
$string['asignregistrartabdes'] = 'This page allows you to assign manager(s) to the respective school(s). ';
$string['eventlevel_help'] = '<b style="color:red;">Note: </b>Global level is a default event level <br />
                                             We have four levels of events
                                            <ul><li><b>Global:</b> Site level events</li><li><b>School:</b> Events for particular school<li><b>program:</b>Events for particular program</li><li><b>Semester:</b> Events for particular semester</li></ul>';
$string['list'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the school <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Apply Now </i> button to submit online application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['lists'] = '
<p style="text-align:justify;">We are accepting online application for the program <i>{$a->pfn}</i>
under the school <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Apply Now </i> button to submit online application. Click <a href="program.php?id={$a->pid}">here</a> for more details.</p>';
$string['graduatelist'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the school <i>{$a->sfn}</i>.
Last date for online submissions is <i>{$a->ed} </i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['graduatelists'] = '
<p style="text-align:justify;">Online applications will be accepted from <i>{$a->sd}</i> under the school <i>{$a->sfn}</i>. Click 
<a href="program.php?id={$a->pid}">here </a>for more details.Click on <i>Apply Now</i> button to submit the online application.</p>';
$string['offlist'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the school <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Last date for online submission is <i>{$a->ed}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offlists'] = '
<p style="text-align:justify;">We are accepting applications for the program <i>{$a->pfn}</i>
under the school <i>{$a->sfn}</i> from <i>{$a->sd}</i>. Please click on below <i>Download </i> button to download application.  <a href="program.php?id={$a->pid}">Readmore</a> for details.</p>';
$string['offgraduatelist'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the school <i>{$a->sfn}</i>.
Last date for application submissions is <i>{$a->ed} </i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download </i> button to download the application.</p>';
$string['offgraduatelists'] = '
<p style="text-align:justify;">Applications will be accepted from <i>{$a->sd}</i> under the school <i>{$a->sfn}</i>.  
<a href="program.php?id={$a->pid}">Readmore </a>for details.Click on <i>Download</i> button to download the application.</p>';
$string['applydesc'] = 'Thank you for your interest!<br>
To be a part of this school, please fill in the following details and complete the admission process.<br>
You are applying to-<br>
School Name :<b style="margin-left:5px;font-size:15px;margin-top:5px;">{$a->school}</b><br>
Program Name :<b style="margin-left:5px;font-size:15px;">{$a->pgm}</b><br/>
Date of Application :<b style="margin-left:5px;font-size:15px;">{$a->today}</b>';
$string['pgmheading'] = 'School & Program Details';
$string['reportdes'] = 'The list of accepted applicants is given below along with the registered school name, program name, admission type, student type, and the status of the application.
<br>Apply filters to customize the view of applicants based on the application type, program type, school, program, student type, and status.';
$string['viewapplicantsdes'] = 'The list of registered applicants is given below so as to view their applications and confirm their admission. Applicants whose details furnished do not meet the requirement can be rejected based on the rules and regulations. 
<br>Using the filters, customize the view of applicants based on the admission type, program type, school, program and curriculum.
';
$string['help_des'] = '<h1>View Schools</h1>
<p>This page allows you to manage (delete/edit) the schools that are defined under this institution.</b></p>

<h1>Add New</h1>
<p>This page allows you to create/define a new school. </b></p>
<p>Fill in the following details and click on save changes to create a new school.</p>
<ul>
<li style="display:block"><h4>Parent</h4>
<p>Parent denotes the main institution that can be categorized into different schools, campus, universities etc. It can have one or multiple (child) sub-institutions.</b></p> 
<p>Select the top level or the parent school under which the new school has to be created. </p>
<p><b>Note*:</b> Select \'Top Level\', if the new school will be the parent school or the highest level under this institution.</p></li>
<li style="display:block"><h4>Type</h4> 
<p>Defines the type of institution or the naming convention you would like to apply for the above mentioned institution.</b></p>
<p><b>Campus -</b> A designation given to an educational institution that covers a large area including library, lecture halls, residence halls, student centers, parking etc.</p>
<p><b>University -</b> A designation given to an educational institution that grants graduation degrees, doctoral degrees or research certifications along with the undergraduate degrees. <Need to check/confirm></p>
<p><b>College -</b> An educational institution or a part of collegiate university offering higher or vocational education. It may be interchangeable with University. It may also refer to a secondary or high school or a constituent part of university.</p></li></ul>
<h1>Assign Manager</h1>
<p>This page allows you to assign manager(s) to the respective school(s). </b></p>
<p>To assign manager(s), select the manager(s) by clicking on the checkbox, then select the school from the given list and finally click on \'Assign Manager\'.</p>
';
$string['collegestructure:create'] = 'collegestructure:Create';
$string['collegestructure:update'] = 'collegestructure:Update';
$string['collegestructure:visible'] = 'collegestructure:Visible';
$string['collegestructure:delete'] = 'collegestructure:delete';
$string['collegestructure:assignregistrar'] = 'collegestructure:Assign Registrar to School';
$string['permissions_error']='Sorry! You dont have permission to access';
$string['notassignedschool_ra']='Sorry! You are not assigned to any school/organization, Please click continue button to Assign.';
$string['notassignedschool_otherrole']='Sorry! You are not assigned to any school/organization, Please inform authorized user(Admin or Registrar) to Assign.';
$string['schoolnotfound_admin']='Sorry! School not created yet, Please click continue button to create.';
$string['schoolnotfound_otherrole']='Sorry! School not created yet, Please inform authorized user(Admin or Registrar) to Crete School';
$string['schoolnotcreated']='Sorry! School not created yet, Please click continue button to create or go to create school/organization tab.';
$string['navigation_info']='Presently no data is available, Click here to ';