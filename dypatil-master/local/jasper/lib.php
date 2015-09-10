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
 * @subpackage Jasper
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\module as module;

/*
 * Function to add the data into the database
 */

function jasper_add_instance($data) {
    global $DB;

    $data->id = $DB->insert_record('local_jasper', $data);
    return $data->id;
}

/**
 * Update the Faculty into the database
 */
function jasper_update_instance($tool) {
    global $DB;

    $DB->update_record('local_jasper', $tool);
}

/**
 * Delete the faculty
 */
function jasper_delete_instance($tool) {
    global $DB;

    $DB->delete_records('local_jasper', array('id' => $tool));
}
