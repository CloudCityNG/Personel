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
 * @subpackage users
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['msg_pwd_change'] = 'Hi {$a->username}<br>Your password changed successfully!';
$string['adduser'] = 'Add User';
$string['pluginname'] = 'Manage Users';
$string['selectrole'] = 'Select Role';
$string['assignrole'] = 'Assign Role';
$string['joiningdate'] = 'Joining Date';
$string['generaldetails'] = 'General Details';
$string['personaldetails'] = 'Personal Details';
$string['contactdetails'] = 'Contact Details';
$string['not_assigned'] = 'Not Assigned.';
$string['address'] = 'Address';
$string['table_head'] = '' . get_string('semester', 'local_semesters') . ' (Course enrolled in)';
$string['userpicture'] = 'User Picture';
$string['newuser'] = 'New User';
$string['createuser'] = 'Create User';
$string['edituser'] = 'Edit User';
$string['updateuser'] = 'Update User';
$string['role'] = 'Role Assigned';
$string['browseusers'] = 'Browse Users';
$string['browseuserspage'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/delete/inactivate) the users.';
$string['deleteuser'] = 'Delete User';
$string['delconfirm'] = 'Are you sure? you really want  to delete "{$a->name}" !';
$string['deletesuccess'] = 'User "{$a->name}" deleted successfully.';
$string['usercreatesuccess'] = 'User "{$a->name}" created Successfully.';
$string['userupdatesuccess'] = 'User "{$a->name}" updated Successfully.';
$string['addnewuser'] = 'Add New User';
$string['assignedschoolis'] = '{$a->label} is "{$a->value}"';
$string['emailexists'] = 'Email exists already.';
$string['givevaliddob'] = 'Give a valid Date of Birth';
$string['dateofbirth'] = 'Date of Birth';
$string['dateofbirth_help'] = 'User should have minimum 20 years age for today.';
$string['assignrole_help'] = 'Assign a role to the user in the selected School.';
$string['siteadmincannotbedeleted'] = 'Site Administrator can not be deleted.';
$string['youcannotdeleteyourself'] = 'You can not delete yourself.';
$string['siteadmincannotbesuspended'] = 'Site Administrator can not be suspended.';
$string['youcannotsuspendyourself'] = 'You can not suspend yourself.';
$string['users:manage'] = 'Manage Users';
$string['users:view'] = 'View Users';
$string['infohelp'] = 'Info/Help';
$string['report'] = 'Report';
$string['viewprofile'] = 'View Profile';
$string['myprofile'] = 'My Profile';
$string['adduserstabdes'] = 'This page allows you to add a new user. This can be one by filling up all the required fields and clicking on "submit" button.';
$string['edituserstabdes'] = 'This page allows you to modify details of the existing user.';
$string['helpinfodes'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['youcannoteditsiteadmin'] = 'You can not edit Site Admin.';
$string['suspendsuccess'] = 'User "{$a->name}" suspended Successfully.';
$string['unsuspendsuccess'] = 'User "{$a->name}" Unsuspended Successfully.';
$string['p_details'] = 'PERSONAL/ACADEMIC DETAILS';
$string['acdetails'] = 'Academic Details';
$string['manageusers'] = 'Manage Users';
$string['username'] = 'User Name';
$string['unameexists'] = 'Username Already exists';
$string['total_courses'] = 'Total number of Courses';
$string['enrolled'] = 'Number of Courses Enrolled';
$string['completed'] = 'Number of Courses Completed';
$string['signature'] = "Registrar's Signature";
$string['status'] = "Status";
$string['courses'] = "Courses";
$string['date'] = "Date";
$string['doj'] = 'Date of joining';
$string['hschool'] = 'High School';
$string['paddress'] = 'PERMANENT ADDRESS';
$string['caddress'] = 'PRESENT ADDRESS';
$string['invalidpassword'] = 'Invalid password';
$string['serviceid'] = 'Service ID';
$string['help_1'] = '<table border="1">
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Mandatory Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>schoolname</td><td>Enter existing School Name applying to.</td></tr>
<tr><td>username</td><td>Enter the username, avoid additional spaces.</td></tr>
<tr><td>password</td><td>Enter the password, avoid additional spaces.The password must have at least 8 characters, at least 1 digit(s), at least 1 lower case letter(s), at least 1 upper case letter(s), at least 1 non-alphanumeric character(s).</td></tr>
<tr><td>firstname</td><td>Enter the first name, avoid additional spaces.</td></tr>
<tr><td>lastname</td><td>Enter the last name, avoid additional spaces.</td></tr>
<tr><td>phone</td><td>All are digits only.Length should be in between 10 and 15.</td></tr>
<tr><td>email</td><td>Enter valid email(Must and Should).</td></tr>
<tr><td>city</td><td>Enter  city name.</td></tr>
<tr><td>country</td><td>Enter country code. Refer below dropdown for codes.';
$string['help_2'] = '</td></tr>
<tr><td>role</td><td>Enter name of existing roles.</td></tr>
<tr><td></td><td style="text-align:left;border-left:1px solid white;padding-left:50px;"><b>Normal Fields</b></td><tr>
<th>Field</th><th>Restriction</th>
<tr><td>middlename</td><td>Enter middlename</td></tr>
<tr><td>gender</td><td><b>MALE</b> or <b>FEMALE</b></td></tr>
<tr><td>dob</td><td>Enter  dob in format <b>mm/dd/yyyy</b></td></tr>
<tr><td>description</td><td>Description of user.</td></tr>
</table>';

$string['already_assignedstoschool']='{$a} already assigned to school. Please unassign from school to proceed further';
$string['already_instructor']='{$a} already assigned as instructor. Please unassign this user as instructor to proceed further';
$string['already_mentor']='{$a} already assigned as mentor. Please unassign this user as mentor to proceed further';
// ***********************Strings for bulk users**********************
$string['download'] = 'Download';
$string['csvdelimiter'] = 'CSV delimiter';
$string['encoding'] = 'Encoding';
$string['errors'] = 'Errors';
$string['nochanges'] = 'No changes';
$string['uploadusers'] = 'Upload Users';
$string['rowpreviewnum'] = 'Preview rows';
$string['uploaduser'] = 'Upload Users';
$string['back_upload'] = 'Back to Upload Users';
$string['uploaduser_help'] = ' The format of the file should be as follows:

* Each line of the file contains one record
* Each record is a series of data separated by commas (or other delimiters)
* The first record contains a list of fieldnames defining the format of the rest of the file';

$string['uploaduserspreview'] = 'Upload Users Preview';
$string['userscreated'] = 'Users created';
$string['usersskipped'] = 'Users skipped';
$string['usersupdated'] = 'Users updated';
$string['uuupdatetype'] = 'Existing users details';
$string['uuoptype'] = 'Upload type';
$string['uuoptype_addnew'] = 'Add new only, skip existing users';
$string['uuoptype_addupdate'] = 'Add new and update existing users';
$string['uuoptype_update'] = 'Update existing users only';
$string['uuupdateall'] = 'Override with file and defaults';
$string['uuupdatefromfile'] = 'Override with file';
$string['uuupdatemissing'] = 'Fill in missing from file and defaults';
$string['uploadusersresult'] = 'Uploaded Users Result';
$string['helpmanual'] = 'Download sample Excel sheet and fill the field values in the format specified below.';
$string['manual'] = 'Help Manual';
$string['info'] = 'Help';
$string['helpinfo'] = 'Browse user will show all the list of users with their details including their first and last access summary. Browse users also allows the user to add new users.';
$string['changepassdes'] = 'This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/delete/inactivate) the users.';
$string['changepassinstdes'] = 'This page allows you to update or modify the password at any point of time; provided the instructor must furnish the current password correctly.';
$string['changepassregdes'] = 'This page allows you to update or modify the password at any point of time; provided the registrar must furnish the current password correctly.';
$string['info_help'] = '<h1>Browse Users</h1>
This page allows the user to view the list of users with their profile details which also includes the login summary. Here you can also manage (edit/delete/inactivate) the users.
<h1>Add New/Create User</h1>
This page allows you to add a new user. This can be one by filling up all the required fields and clicking on ‘submit’ button.';
$string['enter_grades'] = 'Enter Grades';

