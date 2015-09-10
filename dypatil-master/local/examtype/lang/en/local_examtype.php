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
 * @subpackage examtype
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['examdate'] = 'Examdate';
$string['pluginname'] = 'Exam Types';
$string['manageexamtype'] = 'Manage Exam Types';
$string['examtype:manage'] = 'Manage Exam Types';
$string['examtype:view'] = 'View Exam Types';
$string['addeditexamtype'] = 'Create Exam Type';
$string['editexamtype'] = 'Edit Exam Type';
$string['updateexamtype'] = 'Update Exam Type';
$string['allowframembedding'] = 'The list of defined exam types for the various schools is given below. Apply filters for a better view based on the exam type, and school name. You can also manage (delete/edit/inactivate) the exam types.';
$string['createexamdesc'] = "This page allows you to create or define a new exam type under a specific school.";
$string['editexamdesc'] = "This page allows you to edit an exam type  under a specific school.";
$string['delconfirm'] = 'Do you really want to delete this Examtype?';
$string['delnotconfirm'] = "You can't delete this Examtype, as it is under use in Examination module. If you want to delete, first you need to unassign the examtype in Examination and come back here.";
$string['notedit'] = "Presently, you can't edit this Examtype, as it is under use in Examination module. If you want to edit, first you need to unassign the exam type in Examination and come back here.";
$string['deleteexamtype'] = 'Delete Examtype';
$string['schoolrequired'] = 'School field is mandatory';
$string['editop'] = 'Action';
$string['examtype'] = 'Exam Type';
$string['examexits'] = 'Exam Type already created';
$string['addnewexamtype'] = 'Add New Exam Type';
$string['examtypereq'] = "Please enter Exam type";
$string['new'] = 'Create Exam Type';
$string['lists'] = 'View Exam Types';
$string['success'] = 'Exam Type "{$a->examtype}" successfully {$a->visible}.';
$string['failure'] = 'You can not inactivate Exam Type.';
$string['error_up_exam'] = 'Error occurred while updating exam type "{$a->examtype}"';
$string['success_add_exam'] = 'Successfully added exam type "{$a->examtype}".';
$string['success_up_exam'] = 'Successfully updated exam type "{$a->examtype}".';
$string['error_add_exam'] = 'Error occurred while adding exam type "{$a->examtype}".';
$string['success_del_exam'] = 'Successfully deleted exam type.';
$string['error_del_exam'] = 'Error occurred while deleting exam type "{$a->examtype}".';
$string['description'] = 'Description';
$string['edit'] = 'Edit Exam Type';
$string['info'] = 'Help';
$string['helpinfo'] = 'The examinations conducted for the ' . strtolower(get_string('program', 'local_programs')) . 's are of various types. These types are defined under "Exam Type" tab. The defined exam types will be displayed in the list. The user will select the exam type while creating an examination.';
$string['info_help'] = "
<h1>View Exam Type</h1>
The list of defined exam types is given below. Apply filters for a better view based on the exam type, school name. You can also manage (delete/edit/inactivate) the exam types.
<dl><dt><h1>Create Exam Type</h1></dt>
This page allows you to create or define a new exam type under a specific school.
<dd><h4>Exam Type</h4>
Describes the name of the ‘exam type’ to be created.</dd>
</dl>";
