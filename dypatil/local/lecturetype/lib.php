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
 * @subpackage lecturetype
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;
require_once($CFG->dirroot . '/local/modules/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

//use moodle\local\lecturetype as lecturetype;

class cobalt_lecturetype {

    /**
     * Function to add the data into the database
     * @param object $tool new lecturertype data to add
     * @retun int inserted record id.
     * 
     */
    function lecturetype_add_instance($tool) {
        global $DB;
        $tool->id = $DB->insert_record('local_lecturetype', $tool);
        return $tool->id;
    }

    /**
     * Update the lecturetype into the database
     * @param object $tool exist lecturertype data
     * @retun int updated record id.
     */
    function lecturetype_update_instance($tool) {
        global $DB;
        $tool->id = $DB->update_record('local_lecturetype', $tool);
        return $tool->id;
    }

    /**
     * Delete the lecturetype
     * @param object $tool exist lecturertype data
     * @retun void
     */
    function lecturetype_delete_instance($tool) {
        global $DB;
        $result = $DB->delete_records('local_lecturetype', array('id' => $tool));
        if ($result)
            return true;
    }

    /**
     * To display success and error message based on condition
     * @param  int $output it holds reultent value
     * @param string $success it holds the success message
     * @param string $error - error message
     * @param string $currenturl(display the confirmation message in this url)
     * @param object $data used to print dynamic string
     * @return display confirmation message
     */
    function success_error_msg($output, $success, $error, $currenturl, $data) {
        $hier = new hierarchy();
        if ($output)
            $confirm_msg = get_string($success, 'local_lecturetype', $data);
        else
            $confirm_msg = get_string($error, 'local_lecturetype', $data);
        $hier->set_confirmation($confirm_msg, $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * To print tabs  
     * @param string $currenttab currentab name
     * @param int $id used to change the tab name dynamically
     * @return print tab view
     */
    function print_lecturetabs($currenttab, $id) {
        global $OUTPUT;
        $systemcontext =context_system::instance();
        $toprow = array();
        if (has_capability('local/lecturetype:manage', $systemcontext)) {
            if ($id < 0)
                $toprow[] = new tabobject('create', new moodle_url('/local/lecturetype/edit.php', array('id' => -1)), get_string('create', 'local_lecturetype'));
            else
                $toprow[] = new tabobject('edit', new moodle_url('/local/lecturetype/edit.php'), get_string('edit', 'local_lecturetype'));
        }
        $toprow[] = new tabobject('view', new moodle_url('/local/lecturetype/index.php'), get_string('view', 'local_lecturetype'));
        $toprow[] = new tabobject('info', new moodle_url('/local/lecturetype/info.php'), get_string('info', 'local_lecturetype'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

}
