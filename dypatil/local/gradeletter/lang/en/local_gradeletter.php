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
 * Language strings for gradeletter plugin
 *
 * @package    local
 * @subpackage gradeletter
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$string['lettersonly'] = 'Enter Letters Only';
$string['pluginname'] = 'Grade Letters';
$string['pluginheading'] = 'Manage Grade Letters';
$string['addeditgradeletter'] = 'Manage Grade Letter';
$string['allowframembedding'] = 'Defines grade point settings at school level, where registrar is involved in setting the values for letter grades, mark interval, grade point, can manage (edit/delete/modify) recent settings.';
$string['managegradelettertabdes'] = 'This page allows you to create or define the grade letters at school level. To add a grade letter, select the school and define the details required to create a grade letter.';
$string['lettergrades'] = 'Letter Grade';
$string['gradepoint'] = 'Grade Point';
$string['delconfirm'] = 'Do you really want to delete this Grade Letter?';
$string['deletegradeletter'] = 'Delete Grade Letter';
$string['markfrom'] = 'Mark from';
$string['markto'] = 'Mark to';
$string['editop'] = 'Action';
$string['view'] = 'View Grade Letters';
$string['create'] = 'Create Grade Letters';
$string['creategradeletter'] = 'Create Grade Letter';
$string['updategradeletter'] = 'Update Grade Letter';
$string['programrequired'] = 'Please select program';
$string['markslengthmin'] = 'Marks should be minimum 2 digits';
$string['maxmarks'] = 'Marks should be below 100';
$string['letterreq'] = 'Please enter Grade Letter';
$string['marksfromreq'] = 'Please enter Grade "from" value';
$string['reqmarktoval'] = 'Please enter Grade "to" value';
$string['gradepointreq'] = 'Please enter Grade Point value';
$string['letterexists'] = 'Grade Letter is already defined for this program';
$string['markexits'] = 'Grade boundary is already defined for this program';
$string['gradepointexits'] = 'Grade point range is already defined for this program';
$string['gradepointupperlimit'] = 'Grade point exceeded the upper limit defined for this program';
$string['missingmarks'] = 'You are missing {$a} marks from the last defined lowest point';
$string['gradeletter:manage'] = 'Managing Grade Letters';
$string['gradeletter:view'] = 'View Grade Letters';
$string['info'] = 'Help';
$string['helpinfo'] = 'Grade letters are assigned to the grade points. Grade point is calculated by the predefined marks range. Students are required to get a minimum grade letter to attain the degree.';
$string['createdgradeletter'] = 'Grade letter "{$a->letter}" for the school "{$a->schoolname}" created successfully.';
$string['updatedgradeletter'] = 'Grade letter "{$a->letter}" for the school "{$a->schoolname}" updated successfully.';
$string['deletedgradeletter'] = 'Grade letter "{$a->letter}" for the school "{$a->schoolname}" deleted successfully.';
$string['activegradeletters'] = 'Grade letter "{$a->letter}" for the school "{$a->schoolname}" activated successfully.';
$string['inactivegradeletters'] = 'Grade letter "{$a->letter}" for the school "{$a->schoolname}" inactivated successfully.';
