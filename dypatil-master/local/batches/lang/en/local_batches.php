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
 * Strings for component 'local_ratings', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package    local plugin
 * @subpackage ratings
 * @copyright  2013 eabyas
 * @license    http://www.eabyas.in 
 *
 * Code was Rewritten for Moodle 2.X By Atar + Plus LTD for Comverse LTD.
 * @copyright &copy; 2013 eAbyas info solutions.
 * @license eAbyas info solutions.
 */
$string['pluginname'] = 'Batches';
$string['viewall'] = 'View All';
$string['assign'] = 'Add Members';
$string['addcourse'] = 'Add Courses';
$string['addcoursesto'] = 'Add Courses to \'{$a}\'';
$string['idnumber'] = 'Batch ID';
$string['editbatch'] = 'Edit Batch';
$string['addbatch'] = 'Add New Batch';
$string['delcohort'] = 'Delete Batch';
$string['unassignuser'] = 'Unassign User';
$string['unassigncourse'] = 'Unassign Course';
$string['unassignuserconfirmation'] = 'Do you really want to unassign this user? - User will be unassigned from this batch.';
$string['unassigncourseconfirmation'] = 'Do you really want to unassign this course? - All users of this batch will be unassigned from this course. This will not be effected users learning plan.';
$string['delconfirm'] = 'Do you really want to delete batch \'{$a}\'?';
$string['assignusers'] = 'Assign Users';
$string['assignto'] = 'Batch \'{$a}\' members';
$string['removeuserwarning'] = 'Removing users from a Batch may result in unenrolling of users from multiple courses assigned to batch, and this does not affect on student Learning plan.';
$string['backtobatches'] = 'Back to Batches';
$string['assigncostcenter'] = 'Cost Center';
$string['assigncostcenter_help'] = 'Batches can be created under selected cost center. Please select a cost center to create a batch under it.';
$string['shortnamebatches'] = 'Short name';
$string['bulkenrol'] = 'Bulk enrolments for batches';
$string['enroll_batch'] = 'Enrol them to batch';
$string['invalidschool_ub']='Invalid school  \'{$a->schoolname}\' entered at line no {$a->linenum} of excelsheet.';
$string['invalidschoolpermission_ub']='Sorry you are not assigned to this school  \'{$a->schoolname}\' entered at line no.{$a->linenum} of uploaded excelsheet.';
$string['emptytypeofprogram_ub']='Please enter value for "typeofprogram" field in line no. \'{$a->linenum}\' of uploaded excelsheet.';
$string['invalidtypeofprogram_ub']='You have entered invalid typeofprogram \'{$a->typeofprogram}\' at line no. \'{$a->linenum}\' of uploaded excelsheet.';
$string['emptytypeofapplication_ub']='Please enter value for "typeofapplication" field in line no. \'{$a->linenum}\' of uploaded excelsheet.';
$string['invalidtypeofapplication_ub']='You have entered invalid typeofapplication \'{$a->typeofapplication}\' at line no. \'{$a->linenum}\' of uploaded excelsheet.';
$string['invalidprogramname_ub']='Invalid program name \'{$a->programname}\' entered at line no. \'{$a->linenum}\' of excelsheet.';
$string['invalidmapprogramname_ub']='Program {$a->programname} is not under given School {$a->schoolname} or not under given typeofprogram  \'{$a->typeofprogram}\'  at line no. \'{$a->linenum}\' of excelsheet.';
$string['nousersassigned']='No user assigned yet';
$string['mass_enroll']='Mass Enroll';
$string['mass_enroll_help']='Mass Enroll';
$string['batches']='Batch';
$string['missingbatches']='Select Batch to proceed further.';
$string['missingcurriculum']='Select Curriculum to proceed further.';
$string['uploadnewstudentstobatch']='Upload New Students To Batch';
$string['uploadexiststudentstobatch']='Upload Existing Students To Batch';
$string['batchnotunderanysp']='Prsently Batch is not under any school or program, To get exists user, Map batch to school and  program.';
$string['nousersavailable']='No users available';
$string['useremailidexists_ub']='User already exits with same EmailID, Please use another EmailID in line number {$a->linenum}';
$string['selectacademicyear']='Academicyear';
$string['idnumber']='Shortname';
$string['visible_help']='Used to activate the Batch.';
$string['visible']='Visible';
$string['missingacademicyear']='Please select the academicyear, To proceed further';
$string['batch']='Batch';
$string['mailreport']='Email Notification';
$string['helpmanual']='Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual']='Help Manual';
$string['transferapplicantdes'] = "The list of students, who had sought for transfer admission type, will be displayed here. This page allows the registrar to accept/reject/contact/ the students and also can verify the uploaded documents by clicking on ?View Files?. "; 
$string['uploadtabdes'] = 'This page allows you to bulk upload the list of applicants whose applications have been received or accepted or rejected based on the type of document.
<br>Note*: The upload document must satisfy the requirements format, file size etc.';
$string['gradehelp_help']='Enter total gained score value for course';

$string['batcheshelp_table1']= '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>serviceid</td><td>Enter serviceid of applicant, if previousstudent = 2.</td></tr>
<tr><td>typeofapplication</td><td>Enter  number.</br>1 - new applicant </br> 2 - transfer applicant </td></tr>
<tr><td>firstname</td><td>Enter the firstname, avoid additional spaces.</td></tr>
<tr><td>lastname</td><td>Enter the lastname, avoid additional spaces..</td></tr>
<tr><td>gender</td><td><b>male</b> or <b>female</b></td></tr>
<tr><td>dob</td><td>Enter  dob in format <b>mm/dd/yyyy</b></td></tr>

<tr><td>current_country</td><td>Enter country code for current address country. Refer below dropdown for codes.';
$string['batcheshelp_table2']= '</td></tr>

<tr><td>phone</td><td>All are digits only.</td></tr>
<tr><td>email</td><td>Enter valid email.</td></tr>

<tr><td>city</td><td>Enter  city.</td></tr>

<tr><td>address</td><td> Full address of the student.</td></tr>
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>middlename</td><td>Enter middlename</td></tr>
<tr><td>cast</td><td>Cast of the student</td></tr>
<tr><td>category</td><td>Categoryu of the student</td></tr>
<tr><td>fatheremail</td><td>Father Email-ID</td></tr>
<tr><td>otherphone</td><td>Other phone numbers of the student separated by the spaces</td></tr>
<tr><td>pincode</td><td>Enter  postbox number.</td></tr>
</table>';

$string['existsserviceid']='Already student exists with same serviceid. Try with another one';
$string['numberofenrolled'] ='Number of enrolled students: {$a}';
$string['rollid']='Roll ID';
$string['batch_delconfirm']='Do you really want to delete Batch " {$a} "';
$string['enrollexistinguser_warnings']='This interface used to enroll existing users to Batch';
$string['nousersassigned']='No users assigned yet';
$string['cannotassign']='Cannot assign to batches';
$string['success_assign_student']='successfully assigned existing students to batch';
$string['numberofskippedusers']='Number of skipped rows: {$a}';




