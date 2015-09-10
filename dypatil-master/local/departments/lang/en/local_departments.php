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
 * @package    manage_departments
 * @subpackage language strings
 * @copyright  2013 hemalatha arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['msg_add_ins_dept'] = 'Hi {$a->username}  you are assigned to department {$a->deptname}.';
$string['msg_del_ins_dept'] = 'Hi {$a->username} you are un assigned from department {$a->deptname}.';
$string['create_department'] = 'Create Department';
$string['edit_department'] = 'Edit Department';
$string['update_department'] = 'Update Department';
$string['deptfullname'] = 'Department Name';
$string['deptid'] = 'Department ID';
$string['manage_dept'] = 'Manage Departments';
$string['deptlist'] = 'View Departments';
$string['assign_instructor'] = 'Assign Instructor';
$string['assigned_school'] = 'Schools assigned';
$string['dept'] = 'Department';
$string['belongs'] = 'Belongs to';
$string['createdept'] = 'Create Department';
$string['listdepts'] = 'List of Departments';
$string['pluginname'] = 'Manage Departments';
$string['display_instructor'] = 'View Instructors';
$string['assigned_instructor'] = 'Instructors assigned';
$string['deletedept'] = 'Delete Department';
$string['suuccessfully_assigneds'] = 'Successfully assigned {$a->department} to {$a->school}';
$string['error_assigneds'] = 'Error occured while assigning School to Department';
$string['exists'] = 'Already exists';
$string['unassign_dept_msg'] = 'Successfully unassigned {$a->department} from {$a->school}';
$string['error_unassign_msg'] = 'Already in use..so not able to unassign School from Department';
$string['sassign_ins'] = 'Successfully assigned Instructor to Department';
$string['eassign_ins'] = 'Error occurred while assigning Instructor to Department';
$string['error_unassign_ins'] = 'Already in use..so not able to unassign {$a->instructorname} from {$a->departmentname}';
$string['success_unassign_ins'] = 'Successfully unassigned {$a->instructorname} from {$a->departmentname}';
$string['select_instructor'] = 'Please select Instructor before assigning to Department';
$string['success_ins_dept'] = 'Successfully added new Department';
$string['error_ins_dept'] = 'Error occured while adding a new department';
$string['usedsamename'] = 'Already Department is their with same name. Please use some other naming convention';
$string['success_upd_dept'] = 'Successfully updated Department';
$string['error_upd_dept'] = 'Error occured while updating Department';
$string['success_er_dept'] = 'Successfully deleted';
$string['error_er_dept'] = 'error occured while deleting';
$string['useddept'] = 'As the Department {$a} has list of Courses and Schools, you cannot delete it. Please
                    delete the assigned School, Coures first. and come back here';

$string['department'] = 'Departments ';
$string['view_dept_heading'] = 'This page allows you to view the list of all Departments defined under this institution and also to manage (delete/edit/inactivate) the departments.';
$string['create_dept_heading'] = 'This page allows you to create/define Departments under an Institution.
<br>Note*: Departments can be defined at the top level of an institution or for a part of an institution. 
<br>To create a new department select the institution, define the department by entering the following fields like - Name, Id, Description and click on "Create Department". ';
$string['up_dept_heading'] = 'This page allows you to update/define Departments under an Institution.
<br>Note*: Departments can be defined at the top level of an institution or for a part of an institution. 
<br>To update a new department select the institution, define the department by entering the following fields like - Name, Id, Description and click on "Update Department".';
$string['assigninsttabdes'] = 'To assign a new instructor for a department, select the School to view the list of instructors under a particular institution. From the given list of instructors select the instructor that has to be assigned to a department, and then choose from the options of departments given. ';

$string['unassign_school'] = 'Unassigning School';

$string['view_dept'] = 'View Department';
$string['record_notfound'] = 'Record Not Found';
$string['unassign_msg'] = 'Cannot be Unassigned as this School is default for the department';
$string['deptnote'] = 'Note';
$string['deptnote_help'] = 'Departments can be defined at the top level of an institution or for a part of an institution.<br /> 
To create a new Department select the institution, define the Department by entering the following fields like - Name, Id, Description and click on \' Create Department \' 
';
$string['fullname_note'] = 'Department Name ';
$string['fullname_note_help'] = 'Denotes the name of the department to be created under an institution';
$string['shortname_note'] = 'Department ID';
$string['shortname_note_help'] = 'A unique set of characters that is used to represent the department for further references';

$string['dept_ins_desc'] = 'The list of instructors assigned to different departments can be viewed here. To assign a new instructor(s) to a particular department(s), click on \' Assign new instructor(s)\'. To un-assign or delete the instructor from a department, click on \'x\' next to the instructor.';
$string['assign_ins_heading'] = 'Select from the list of view available instructors and assign them to a department.';
$string['assign_school_description'] = 'This page displays the list of Schools assigned to a particular department. The registrar can assign a department to multiple Schools. To assign the department to School(s), click on the dropdown to view list of Schools and select the School name to be assigned to a particular department.
                                          // <p>Note*: In order to delete (un-assign) the School from the department, click on the \' x \' icon next to the School name. </p>';
$string['dept_heading'] = 'Manage Departments';

$string['school_already_inuse_withdept'] = 'As the School {$a->school} has list of Courses and Departments, you cannot delete it. Please delete the assigned Coures first. and come back here';
$string['delconfirm_unassignschool'] = 'Do you really want to Unassign the  School from Department?';
$string['unassign_instructor'] = 'Unassign Instructor from Department';
$string['instructor_assigrned_toclass'] = '{$a->instructorname} is assigned to some class ,so not able to unassign {$a->instructorname} from {$a->departmentname}';
$string['delconfirm_unassignsinstructor'] = 'Do you really want to Unassign the  {$a->instructorname} from {$a->departmentname}?';

$string['secondaryschool'] = 'Secondary Schools';
$string['defaultschool'] = 'Default Schools';


$string['missing_deptfullname'] = 'Please Enter Department Name';
$string['missing_deptid'] = 'Please Enter Department ID';
$string['uploaddepartment_help'] = ' The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
 * The first record contains a list of fieldnames defining the format of the rest of the file';
$string['departmentscreated'] = 'Departments created';
$string['departmentsskipped'] = 'Departments skipped';
$string['departmentsupdated'] = 'Departments updated';

$string['uploaddepartments'] = 'Upload Departments';
$string['uploaddepartmentspreview'] = 'Uploaded Departments Preview';
$string['uploaddepartmentsresult'] = 'Upload Departments Result';

$string['sample_excel'] = "Sample Excel Sheet";
$string['dept_unassign'] = 'Unassign';
$string['dept_description'] = 'Description';
$string['dept_assignto'] = 'Assign to';
$string['dept_assigntodept'] = 'Assign to Department';
$string['dept_help'] = 'Help';
$string['dept_delconfirm'] = 'Do you really want to delete the Department?';
$string['dept_addnew'] = 'Create Department';
$string['dept_failure'] = 'You can not inactivate Department.';
$string['dept_success'] = 'Department "{$a->department}" successfully {$a->visible}.';
$string['dept_dd'] = 'Download Details';
$string['dept_helpinfo'] = 'A department is division of the School that has got specifications based on the Schools. Departments are assigned to School(s). Departments will have instructor(s) assigned.';
$string['dept_errors'] = 'Errors';
$string['dept_view'] = 'View';
$string['dept_helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['dept_manual'] = 'Help Manual';
$string['dept_nochanges'] = 'No changes';
$string['dept_uuoptype'] = 'Upload type';
$string['dept_uuoptype_addnew'] = 'Add new only, skip existing departments';
$string['dept_uuoptype_addupdate'] = 'Add new and update existing departments';
$string['dept_uuoptype_update'] = 'Update existing departments only';

$string['dept_uuupdateall'] = 'Override with file and defaults';
$string['dept_uuupdatefromfile'] = 'Override with file';
$string['dept_uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['dept_uuupdatetype'] = 'Existing department details';
$string['dept_csvdelimiter'] = 'CSV delimiter';
$string['dept_encoding'] = 'Encoding';
$string['dept_rowpreviewnum'] = 'Preview rows';
$string['dept_rnot_assigned'] = 'Registrar not Assigned any School so not able to do further proceesing';
$string['dept_assign_course'] = 'Courses assigned';
$string['dept_uploaddes'] = 'Registrar can bulk upload the list of departments provided the document (file) is in the given format.<br>
For sample course sheet, click on the "Sample Excel Sheet" that downloads a sample excel sheet format to upload departments.
';
$string['info_help'] = "
<h1>View Departments</h1>
This page allows you to view the list of all departments defined under this institution and also to manage (delete/edit/inactivate) the departments.
<dl><dt><h1>Add New/Create Department</h1></dt>
This page allows you to create/define departments under an institution. </br>
<b>Note*:</b> Departments can be defined at the top level of an institution or for a part of an institution. </br>
To create a new department - select the institution, define the department by entering the following fields like - Name, Id, Description and click on 'Create Department'.</br>
<dd><b>Department Name</b></br>
Denotes the name of the department to be created under an institution.</dd>
<dd><b>Department ID</b></br>
A unique set of characters that is used to represent the department for further references.</dd></dl>
<h1>View Instructors list</h1>
The list of instructors assigned to different departments can be viewed here. To assign a new instructor(s) to a particular department(s), click on 'Assign new instructor(s)'. To un-assign or delete the instructor from a department, click on 'x' next to the instructor.
<h1>Assign Instructor</h1>
To assign a new instructor for a department, select the School to view the list of instructors under a particular institution. From the given list of instructors select the instructor that has to be assigned to a department, and then choose from the options of departments given. 
<h1>Schools:</h1>
This page displays the list of Schools assigned to a particular department. The registrar can assign a department to multiple Schools. To assign the department to School(s), click on the dropdown to view list of Schools and select the School name to be assigned to a particular department. </br>
<b>Note*:</b> In order to delete (un-assign) the School from the department; click on the 'x' icon next to the School name.

<h1>Upload Departments</h1>
Registrar can bulk upload the list of departments provided the document (file) is in the given format.</br>
For sample course sheet, click on the 'Sample Excel Sheet' that downloads a sample excel sheet format to upload departments.";
$string['dept_download_help'] = '<table border="1">

<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>schoolname</td><td>Enter the existing School Name (without additional spaces) under which you want to create new department.</td></tr>
<tr><td>fullname</td><td>Enter the fullname of Department without additional spaces.</td></tr>
<tr><td>shortname</td><td>Enter the shortname of the Department without additional spaces.</td></tr>

<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<th>Field</th><th>Restriction</th>

<tr><td>description</td><td>Enter description of Department.</td></tr>
</table><br/>';
$string['dept_back_upload'] = 'Back to Upload Departments';
$string['addnew'] = 'Add';
$string['help'] = 'Help';
$string['description'] = 'Description';
$string['assign_course'] = 'Assign Course';
$string['helpinfo'] = 'Help Info';
$string['instructor'] = 'Instructor';
$string['firstname'] = 'First Name';
$string['lastname'] = 'Last Name';
$string['email'] = 'Email';
$string['dept_formatname'] = 'Department name display format';
$string['dept_formatname_help'] = 'Department name will be displayed in the format of (department name - parent school name)';
$string['departments:create'] = 'departments:Create';
$string['departments:delete'] = 'departments:Delete';
$string['departments:update'] = 'departments:Update';
$string['departments:visible'] = 'departments:Visible';
$string['departments:assigninstructor'] = 'departments:Assign instructor to department';
$string['departments:assignschool'] = 'departments:Assign school to department';
$string['departments:manage']='departments:manage';
$string['departments:view']='departments:view';
$string['nodata_assigninstructorpage']='Presently no instructor is available. Click here to ';
$string['linkname_assigninstructorpage']='Add instructor to Department';

