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
 * @subpackage examtype
 * @copyright  2013 sreenivas <sreenudorasala@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

//use moodle\local\examtype as examtype;

class cobalt_examtype {

    /**
     * Add new tool.
     *
     * @param  object $tool
     * @return int
     */
    function examtype_add_instance($tool) {
        global $DB;
        $tool->id = $DB->insert_record('local_examtypes', $tool);
        return $tool->id;
    }

    /**
     * Update existing tool.
     * @param  object $tool
     * @return int
     */
    function examtype_update_instance($tool) {
        global $DB;
        $tool->id = $DB->update_record('local_examtypes', $tool);
        return $tool->id;
    }

    /**
     * Delete tool.
     * @param  object $tool
     * @return void
     */
    function examtype_delete_instance($tool) {
        global $DB;
        $result = $DB->delete_records('local_examtypes', array('id' => $tool));
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
        if ($output) {
            $confirm_msg = get_string($success, 'local_examtype', $data);
            $options = array('style' => 'notifysuccess');
        } else {
            $confirm_msg = get_string($error, 'local_examtype', $data);
            $options = array('style' => 'notifyproblem');
        }
        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }

    /**
     * To print tabs  
     * @param string $currenttab currentab name
     * @param int $id used to change the tab name dynamically
     * @return print tab view
     */
    function print_examtabs($currenttab, $id) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();
        if (has_capability('local/examtype:manage', $systemcontext)) {
            if ($id < 0)
                $toprow[] = new tabobject('new', new moodle_url('/local/examtype/edit.php', array('id' => -1)), get_string('new', 'local_examtype'));
            else
                $toprow[] = new tabobject('edit', new moodle_url('/local/examtype/edit.php'), get_string('edit', 'local_examtype'));
        }

        $toprow[] = new tabobject('lists', new moodle_url('/local/examtype/index.php'), get_string('lists', 'local_examtype'));
        $toprow[] = new tabobject('info', new moodle_url('/local/examtype/info.php'), get_string('info', 'local_examtype'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

}
