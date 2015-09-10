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
 * Prints navigation tabs
 *
 * @package    local
 * @subpackage cobaltcourses
 * @copyright  2013 Vinodkumar <avinod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$tabs = array();
$tabs[] = new tabobject('create', new moodle_url('/local/cobaltcourses/cobaltcourse.php'), get_string('addnew', 'local_cobaltcourses'));
$tabs[] = new tabobject('view', new moodle_url('/local/cobaltcourses/index.php'), get_string('courselist', 'local_cobaltcourses'));
$tabs[] = new tabobject('prerequisite', new moodle_url('/local/cobaltcourses/prerequisite.php'), get_string('prerequisite', 'local_cobaltcourses'));
$tabs[] = new tabobject('equivalent', new moodle_url('/local/cobaltcourses/equivalent.php'), get_string('equivalent', 'local_cobaltcourses'));
$tabs[] = new tabobject('information', '', get_string('information', 'local_cobaltcourses'));
$tabs[] = new tabobject('report', new moodle_url('/local/cobaltcourses/report.php'), get_string('report', 'local_cobaltcourses'));
echo $OUTPUT->tabtree($tabs, $currenttab);
