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
 * This file keeps track of upgrades to the ltiprovider plugin
 *
 * @package    local
 * @subpackage Curriculum
 * @copyright  2014 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

function xmldb_local_curriculum_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    $dbman = $DB->get_manager();

    if ($oldversion < 2013010706) {

        $table = new xmldb_table('local_curriculum');

        $field = new xmldb_field('enableplan', XMLDB_TYPE_INTEGER, 1, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, 0, 'completionnotify');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        upgrade_plugin_savepoint(true, 2013010706, 'local', 'curriculum');
    }

    return true;
}
