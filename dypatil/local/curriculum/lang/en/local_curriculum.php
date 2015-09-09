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
 * @subpackage Curriculum
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['error_graduated'] = 'You can not assign courses because user already graduated';
$string['headername'] = 'Curriculum Name';
$string['missingcurriculumname'] = 'Please enter Curriculum Name';
$string['selectcurriculum'] = 'Select Curriculum';
$string['curriculum'] = 'Curriculum';
$string['curriculums'] = 'Curriculums';
$string['creatcurriculum'] = '	This page allows you to define a new curriculum under a program.<br>To create a new curriculum the user has to select the school and program under which the curriculum has to be defined. Then, provide the necessary fields like curriculum name, short name and the description. Select the deadline for the curriculum, define the maximum credit hours and click on "Create Curriculum". ';
$string['pluginname'] = 'Curriculums';
$string['managecurriculum'] = 'Manage Curriculums';
$string['allowframembedding'] = 'This page allows you to view the curriculums defined under a program along with its duration. You can also manage (delete/edit/inactivate) the status of the curriculum.
<br>Note*: Click on Manage Plan to edit the settings or to create a new plan for a particular curriculum.
';
$string['addcurriculumdes'] = 'This page allows you to define a new curriculum under a program. ';
$string['editcurriculumdes'] = 'This page allows you to edit curriculum under a program. ';
$string['createcurriculum'] = 'Create Curriculum';
$string['editcurriculum'] = 'Edit Curriculum';
$string['updatecurriculum'] = 'Update Curriculum';
$string['curriculumname'] = 'Curriculum Name';
$string['description'] = 'Description';
$string['delcurriculumconfirm'] = 'Are you sure? You really want to delete curriculum "{$a->scname}"!';
$string['deletecurriculum'] = 'Delete Curriculum';
$string['addcurriculum'] = 'Add Curriculum';
$string['startdate'] = 'Start Date';
$string['validtill'] = 'Valid Till';
$string['missingstartdate'] = 'Please enter Startdate';
$string['missingenddate'] = 'Please enter Enddate';
$string['addcuplan'] = 'Manage Plan';
$string['curstatus'] = 'Curriculum Status';
$string['curriculumshortname'] = 'Short Name';
$string['missingcurriculumshort'] = 'Please enter Curriculum Shortname';
$string['assigncuplan'] = 'Curriculum Plan';
$string['cuplan'] = 'Curriculum Plan';
$string['planname'] = 'Curriculum Plan Name';
$string['viewcurriculum'] = 'View Curriculum';
$string['unassigncurriculum'] = 'Unassign Modules from the curriculum';
$string['nomodulessyet'] = 'No modules are present in the program assigned to the curriculum.Please create modules and assing them to program.';
$string['action'] = 'Action';
$string['create'] = 'Create Curriculum';
$string['view'] = 'View Curriculums';
$string['info'] = 'Help';
$string['reports'] = 'Reports';
$string['missingvalidtilldate'] = 'Valid Till date is missing';
$string['credithours'] = 'Credit Hours';
$string['missingcredithours'] = 'Please enter Credit Hours';
$string['replacement'] = 'Course Replacement';
$string['curriculumsettings'] = 'Curriculum Settings';
$string['curriculumcriteria'] = 'To achieve this curriculum you need to obtain Min.Credit hours of &nbsp; - &nbsp;';
$string['minch'] = 'Min.Credit Hours';
$string['addnewplan'] = 'Add New Plan';
$string['managecurriculumsetting'] = 'Manage Curriculum Setting';
$string['updatecuformat'] = 'Update Curriculum';
$string['settingone'] = 'Setting One';
$string['missingtotalch'] = 'Missing total Credit Hours';
$string['no_child_plan'] = 'No. of Child Plans';
$string['no_course_plan'] = 'No .of Courses assigned';
$string['no_modules'] = "No modules is assigned till now";
$string['listmodules'] = "Here you will have the list of all the modules and the assigned to";

$string['missingcurriculum'] = 'Missing Curriculum';
$string['manageplan'] = 'Manage Plan';
$string['cannotdelete'] = 'As the Curriculum "{$a->scname}" has plans/student assigned, you cannot delete it. Please delete the assigned plans or enrolled users first and come back here. ';
$string['curriculumplan'] = 'Curriculum Plan';
$string['enrollsem'] = 'Enrolled Semester';
$string['createplan'] = 'Create Plan';
$string['editplan'] = 'Edit Plan';
$string['success'] = 'Curriculum "{$a->curriculum}" successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate Curriculum.';
$string['missingcurriculum'] = 'Missing Curriculum';
$string['parentplan'] = 'Parent Plan';
$string['planname'] = 'Plan Name';
$string['missingfullname'] = 'Plan Name Required.';
$string['plantype'] = 'Plan Type';
$string['description'] = 'Description';
$string['createsuccess'] = 'Curriculum plan "{$a->plan}" created Succesfully.';
$string['createcurricsuccess'] = 'Curriculum with name "{$a->curriculum}" created Successfully';
$string['updatecurricsuccess'] = 'Curriculum with name "{$a->curriculum}" updated Successfully';
$string['updatesuccess'] = 'Curriculum plan "{$a->plan}" updated Succesfully.';
$string['deletesuccess'] = 'Curriculum plan "{$a->plan}" deleted Succesfully.';
$string['deletedcurricsuccess'] = 'Curriculum deleted successfully';
$string['managepluginname'] = 'Manage Curriculum Plan';
$string['viewplanspage'] = '<b>Description: </b>Plans are created under a curriculum.';
$string['deleteplan'] = 'Delete Plan';
$string['delplanconfirm'] = 'Are you sure? You really want to delete this plan!';
$string['assignedcoursesdelete'] = 'Courses are assigned to this curriculum plan, you can not delete it.';
$string['containschilddontdelete'] = 'This curriculum plan has child plans, you can not delete it.';
$string['viewall'] = 'View Plans';
$string['addnew'] = 'Add New';
$string['viewdetails'] = 'View Details';
$string['assigncourses'] = 'Assign Courses';
$string['nocoursesassigned'] = 'No courses assigned yet.';
$string['unassign'] = 'Un Assign';
$string['descforassign'] = '<b>Note: </b>Here you can assign the courses to <b>{$a->fullname}</b>, from the Modules of same or another program (OR) you can directly assign from the department.';
$string['nocoursesmodule'] = 'No Courses available in <b>{$a}</b>.';
$string['cobaltcourse'] = 'Cobalt Courses';
$string['pleaseselectcourse'] = 'Please Select atleast one Course to assign.';
$string['assignedsuccess'] = 'Courses assigned to "{$a}" successfully.';
$string['unassigncourse'] = 'Un assign Course';
$string['confirmunassign'] = 'Are you sure? You really want to unassign this course!';
$string['unassignsuccess'] = 'Course "{$a->course}" unassigned successfully.';
$string['haschildcantassigncourse'] = '<h4>This plan "{$a->plan}" has child plans, You can not assign courses to this.</h4>';
$string['curriculumplan:manage'] = 'Manage Curriculum Plan';
$string['curriculumplan:view'] = 'View Curriculum Plan';
$string['moduleassignedsuccess'] = 'All courses from the module "{$a->module}" are assigned Successfully.';
$string['noteforassignmodule'] = '<b>Note:</b> Click on "Assign Module" to assign all the Courses (OR) Select Courses from list to assign and click on "Assign Courses".';
$string['noteforassigndept'] = '<b>Note:</b> Select Courses from list to assign and click on "Assign Courses".';
$string['containschilddonthide'] = 'This plan contains Child plans. You can not hide it.';
$string['viewplancreationpage'] = 'This page allows you to define a new plan based on your interest and will be not be considered under the defined curriculum.
<p>To create a curriculum plan, curriculum, select parent plan, define plan name, plan type, define description and then click on "Create". </p>';
$string['createplan_help'] = '<b>Note*:</b> The Registrar has to confirm the new plan so as to consider the credits gained in this plan, at the time of assistance. ';
$string['viewmanageplanpage'] = 'Displays the curriculum plan (Year- wise & Semester- wise) defined for a program.
<p>You can manage (delete/edit/change the status) curriculum; assign specific courses based on the program and/or 
create a new plan according to your interest.</p><b>Note*:</b> The New Plan created will not be the defined curriculum
plan and is visible only to the individual.';
$string['viewassigncoursetoplanpage'] = 'This page allows you to assign courses to a particular curriculum. To assign courses, select the Module to view the courses, and then select the courses that have to be assigned to the curriculum and click on "Assign Courses".<br>Note*: To add the complete Module; select the Module and click on "Assign Module".';
$string['addplan'] = 'Add Plan';
$string['helpinfo'] = 'Curriculum is the program of study that has the list of courses for a program assigned in the form of modules. Each program has its own curriculum. A curriculum may have more than one module assigned.';
$string['curriculum:manage'] = 'Manage Curriculums';
$string['settingtwo'] = 'Setting Two';
$string['curriculum:view'] = 'View Curriculums';
$string['viewcurricullum'] = 'This page allows you to view the curriculums defined under a program along with its duration. You can also manage (delete/edit/inactivate) the status of the curriculum.';
$string['freshmancrhr'] = 'Freshman Cr.hours';
$string['sophomorecrhr'] = 'Sophomore Cr.hours';
$string['juniorcrhr'] = 'Junior Cr.hours';
$string['seniorcrhr'] = 'Senior Cr.hours';
$string['validtillvalidation'] = 'Valid till date should be  greater than current date';
$string['mycurriculumdec'] = 'The prescribed academia plan of a student for an academic year is given below. The status of the courses listed according to the year-wise and semester-wise plan is also mentioned.';
$string['missingcurriculum'] = 'Please select the curriculum';
$string['mycurriculum'] = 'My Curriculum';
$string['mycurriculumplan'] = 'My Curriculum Plan';
$string['curriculumlevel'] = 'Curriculum Level';
$string['info_help'] = '<h1>View Curriculums</h1>
This page allows you to view the curriculums defined under a program along with its duration. You can also manage (delete/edit/inactivate) the status of the curriculum.</br>
<b>Note*:</b>Click on Manage Plan to edit the settings or to create a new plan for a particular curriculum.
Table Fields: 
Curriculum Name, School Name, Program Name, Valid Till, Edit Curriculum Actions
<h1>Create Curriculum </h1>
	This page allows you to define a new curriculum under a program. </br>
To create a new curriculum the user has to select the school and program under which the curriculum has to be defined. Then, provide the necessary fields like curriculum name, short name and the description. Select the deadline for the curriculum, define the maximum credit hours and click on ‘Create Curriculum’.
<h1>Manage Plan</h1>
This page displays the prescribed curriculum (Year- wise & Semester- wise) for a particular program; although the learner can create a new plan based on his interests and choice. You can manage (delete/edit/change the status) curriculum; assign specific courses based on the program and/or create a new plan according to your interest.</br>
<b>Note*:</b> The New Plan created will not be the defined curriculum plan and is visible only to the individual.
<h1>Create New Plan</h1>
This page allows you to define a new plan based on your interest and will be not be considered under the defined curriculum. </br>
<b>Note*:</b> The Registrar has to confirm the new plan so as to consider the credits gained in this plan, at the time of assistance. </br>
To create a curriculum plan, curriculum, select parent plan, define plan name, plan type, define description and then click on ‘Create’. 
<h1>Assign Courses</h1>
This page allows you to assign courses to a particular curriculum. To assign courses, select the Module to view the courses, and then select the courses that have to be assigned to the curriculum and click on ‘Assign Courses’.</br>
<b>Note*:</b> To add the complete Module, select the Module and click on ‘Assign Module’.
';
//VIN-27012014
$string['enableplan'] = 'Enable Plan';
$string['planenabledcurriculum'] = 'Plans are enabled for this curriculum "{$a}", you can not assign the courses directly to it.';
$string['planscreateddontchange'] = 'Plans created for this Curriculum, you can\'t change it.';
$string['coursesassigned'] = 'Courses are assigned to the plan under this Curriculum, you can\'t change it.';
$string['assignedcourses'] = 'Assigned Courses';
$string['assign'] = 'Assign';
$string['cantcreateplan'] = 'Plans are not enabled to this curriculm "{$a}"';
$string['curriculum'] = 'Curriculum';
$string['shortnameexists'] = 'Entered Shortname exist. Please provide proper shortname';
$string['curriculum:delete'] = 'curriculum:Delete';
$string['curriculum:update'] = 'curriculum:Update';
$string['curriculum:visible'] = 'curriculum:Visible';
$string['curriculum:create'] = 'curriculum:Create';
$string['assigncoursetomodule'] = '"Assign Courses to Modules"';