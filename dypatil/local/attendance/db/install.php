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
// along with Moodle.  If not, see <http://eabyas.in>.

/**
 * post installation hook for adding data.
 *
 * @package    mod_attendance
 * @copyright  2011 Artem Andreev <vijaya@eabyas.in>
 * @license    http://eabyas.in
 */

/**
 * Post installation procedure
 */
defined('MOODLE_INTERNAL') || die;

function xmldb_local_attendance_install() {
    global $CFG, $OUTPUT, $DB;
  
    $default = array("P"=>'Present',"L"=>'Late',"A"=>'Absent');
    foreach($default as $key=>$value)
    {
        $DB->insert_record('local_attendance_statuses',array('acronym'=>$key,'description'=>$value));
    }
}
