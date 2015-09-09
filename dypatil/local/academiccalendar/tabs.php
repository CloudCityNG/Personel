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
 * @package    core_group
 * @copyright  2010 Petr Skoda (http://moodle.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $PAGE;
$systemcontext = context_system::instance();
$create_cap = array('local/academiccalendar:manage', 'local/academiccalendar:create');
$update_cap = array('local/academiccalendar:manage', 'local/academiccalendar:update');

$toprow = array();
if (isset($id) && $id > 0) {
    if (has_any_capability($update_cap, $systemcontext))
        $toprow[] = new tabobject('edit', new moodle_url('/local/academiccalendar/edit_event.php'), get_string('edit', 'local_academiccalendar'));
} else {
    if (has_any_capability($create_cap, $systemcontext))
        $toprow[] = new tabobject('create', new moodle_url('/local/academiccalendar/edit_event.php'), get_string('create', 'local_academiccalendar'));
}
$toprow[] = new tabobject('view', new moodle_url('/local/academiccalendar/index.php'), get_string('view', 'local_academiccalendar'));

$toprow[] = new tabobject('eventtype', new moodle_url('/local/academiccalendar/eventtype.php'), get_string('eventtype', 'local_academiccalendar'));
$toprow[] = new tabobject('info', new moodle_url('/local/academiccalendar/info.php'), get_string('info', 'local_academiccalendar'));
echo '<div id="page-tabs">' . $OUTPUT->tabtree($toprow, $currenttab) . '</div>';
