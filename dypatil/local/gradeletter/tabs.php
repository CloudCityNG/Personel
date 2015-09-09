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
 * @package    local
 * @subpackage gradeletter
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$toprow = array();

$toprow[] = new tabobject('create', new moodle_url('/local/gradeletter/edit.php'), get_string('create', 'local_gradeletter'));
$toprow[] = new tabobject('view', new moodle_url('/local/gradeletter/index.php'), get_string('view', 'local_gradeletter'));
$toprow[] = new tabobject('info', new moodle_url('/local/gradeletter/info.php'), get_string('info', 'local_gradeletter'));

echo $OUTPUT->tabtree($toprow, $currenttab);
