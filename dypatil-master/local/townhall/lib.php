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
 * General plugin functions.
 *
 * @package    local
 * @subpackage townhall
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;
require_once("{$CFG->libdir}/completionlib.php");
/*
 * function to get activity types
 */

function activities_inuse($id) {
    static $modnames = null;
    global $DB, $CFG;
    if ($modnames === null) {
        $modnames = array(0 => array(), 1 => array());
        if ($allmods = $DB->get_records("modules")) {
            foreach ($allmods as $mod) {
                if (file_exists("$CFG->dirroot/mod/$mod->name/lib.php") && $mod->visible) {
                    $modnames[0][$mod->name] = get_string("modulename", "$mod->name");
                    $modnames[1][$mod->name] = get_string("modulenameplural", "$mod->name");
                }
            }
            collatorlib::asort($modnames[0]);
            collatorlib::asort($modnames[1]);
        }
    }
    $modnames = $modnames[(int) $plural];
    $modulesinuse = array();
    $modulesinuse[null] = '---------Select----------';
    $dbmanager = $DB->get_manager();
    foreach ($modnames as $module => $details) {
        if ($dbmanager->table_exists($module) && $DB->record_exists($module, array('course' => $id))) {
            $modulesinuse[$module] = $details;
        }
    }
    return $modulesinuse;
}

/*
 * function to get activity names
 */

function event_information($module, $id) {
    global $DB;
    $events = array();
    $numevents = 0;
    $numeventsconfigured = 0;
    $orderby = 'orderbycourse';
    $sections = $DB->get_records('course_sections', array('course' => $id), 'section', 'id,sequence');
    foreach ($sections as $section) {
        $section->sequence = explode(',', $section->sequence);
    }
    // Check if this type of module is used in the course, gather instance info.
    $records = $DB->get_records($module, array('course' => $id));
    $events = array();
    $events[0][null] = '--------Select----------';
    foreach ($records as $record) {
        $coursemodule = get_coursemodule_from_instance($module, $record->id, $id); // Get the course module info.
        // Check if the module is visible, and if so, keep a record for it.
        if ($coursemodule->visible == 1) {
            $events[0][$coursemodule->id] = format_string($record->name);
        }
    }
    sort($events);
    return $events;
}

/*
 * function to display tabs
 */

function print_towntabs($currenttab, $id) {
    global $OUTPUT;
    $toprow = array();
    $toprow[] = new tabobject('edit', new moodle_url('/local/townhall/index.php?id=' . $id . ''), get_string('managetownhall', 'local_townhall'));
    $toprow[] = new tabobject('lists', new moodle_url('/local/townhall/view.php?id=' . $id . ''), get_string('view', 'local_townhall'));
    $toprow[] = new tabobject('add', new moodle_url('/local/townhall/add_new.php?id=' . $id . ''), get_string('add', 'local_townhall'));
    echo $OUTPUT->tabtree($toprow, $currenttab);
}
