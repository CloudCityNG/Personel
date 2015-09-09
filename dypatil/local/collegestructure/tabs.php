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
 * List the tool provided in a course
 *
 * @package    local
 * @subpackage collegestructure
 * @copyright  2012 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$toprow = array();
$toprow[] = new tabobject('create', new moodle_url('/local/collegestructure/school.php'), get_string('create', 'local_collegestructure'));
$toprow[] = new tabobject('view', new moodle_url('/local/collegestructure/index.php'), get_string('view', 'local_collegestructure'));
$toprow[] = new tabobject('assignregistrar', new moodle_url('/local/collegestructure/assignusers.php'), get_string('assignregistrar', 'local_collegestructure'));
$toprow[] = new tabobject('info', new moodle_url('/local/collegestructure/info.php'), get_string('info', 'local_collegestructure'));
$toprow[] = new tabobject('reports', new moodle_url('/local/collegestructure/edit.php'), get_string('reports', 'local_collegestructure'));
echo $OUTPUT->tabtree($toprow, $currenttab);
?>