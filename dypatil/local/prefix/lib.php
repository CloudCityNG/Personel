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
 * @package    local
 * @subpackage  Creating prefix and suffix for the program...
 * @copyright  2012 Hemalatha arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\prefix as prefix;

//...class prefix_suffix ...contains list of all functions which is used to manage prefi x and suffix 
class prefix_suffix {

    private static $_singleton;

    //....used to avoid creating object from outside the class	 
    private function __construct() {
        
    }

    //....creating singleton object through member function(inside the class)... 
    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new prefix_suffix();
        }
        return self::$_singleton;
    }

    /**
     * @method pre_update_instance
     * @todo used to update the record of prefix_suffix (table) data from prefix _suffix form
     * @param $tool arry object(holds the prefix and suffix form data)
     * @return-- int $id-updated row id
     */
    function pre_update_instance($tool) {
        global $DB;
        $tool->id = $DB->update_record('local_prefix_suffix', $tool);
        return $tool->id;
    }

    /**
     * @method pre_add_instance
     * @todo used to add(insert) the record of prefix_suffix (table) data from prefix _suffix form(if data is exists it return -3)
     * @param $tool arry object(holds the prefix and suffix form data)
     * @return-- int $id-updated row id
     */
    function pre_add_instance($tool) {
        global $DB;
        $f = 0;
        $temp = $DB->get_records('local_prefix_suffix');
        foreach ($temp as $t) {
            if (($tool->entityid == $t->entityid) && ($tool->schoolid == $t->schoolid) && ($tool->programid == $t->programid)) {
                return false;
                $f = 1;
            }
        }
        if ($f == 0)
            $tool->id = $DB->insert_record('local_prefix_suffix', $tool);
        return $tool->id;
    }

    /**
     * @method pre_del_instance
     * @todo used to delete the record of prefix_suffix (table) based on id
     * @param $id type of int
     */
    function pre_del_ins($tool) {
        global $DB;
        if ($DB->delete_records('local_prefix_suffix', array('id' => $tool)))
            return true;
        else
            return false;
    }

    /**
     * @method prefix_tabs
     * @todo used to provide the static tabs only for prefix_suffix plugin
     * @param type of string $currenttab(it hold the active tab name)
     * @return $tab view
     */
    function prefix_tabs($currenttab, $id) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();
        $capabilities_array = array('local/prefix:manage', 'local/prefix:create');
        /* Bug report #329  -  School Settings>Prefix & Suffix>Edit Entity- Tab name
         * @author hemalatha c arun<hemalatha@eabyas.in> 
         * Resolved- changed edit tab name
         */
        if ($id > 0) {
            $update_cap = array('local/prefix:manage', 'local/prefix:update');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('create_prefix', new moodle_url('/local/prefix/prefix2.php'), get_string('editprefixsuffix', 'local_prefix'));
        }
        else {
            if (has_any_capability($capabilities_array, $systemcontext))
                $toprow[] = new tabobject('create_prefix', new moodle_url('/local/prefix/prefix2.php'), get_string('createprefixsuffix', 'local_prefix'));
        }

        $toprow[] = new tabobject('view', new moodle_url('/local/prefix/index.php'), get_string('view', 'local_prefix'));
        $toprow[] = new tabobject('create_entity', new moodle_url('/local/prefix/entity.php'), get_string('createentity', 'local_prefix'));
        $toprow[] = new tabobject('info', new moodle_url('/local/prefix/info.php'), get_string('info', 'local_prefix'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method success_error_msg
     * @todo used to provide confirmation message like success or failure(error)(in deleting and creating process)
     * @param type of int $output-used to move success or failure part
     * @param type of string $success-sucess message
     * @param type of string $error-error message
     * @param $currenturl it hold url...after confirmation...control move to this url
     * @return $tab view
     */
    function success_error_msg($output, $success, $error, $currenturl, $success_variable = null) {
        $hier = new hierarchy();
        if ($output) {
            $confirm_msg = get_string($success, 'local_prefix', $success_variable);
            $options = array('style' => 'notifysuccess');
        } else {
            $confirm_msg = get_string($error, 'local_prefix');
            $options = array('style' => 'notifyproblem');
        }
        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }

    /**
     * @method delete_entity
     * @todo used to delete the entity which is not in use..(it is in use it returns -3 else it call confirmation method)
     * @param type of int $id
     * @param $currenturl it hold url...after confirmation...control move to this url
     * @return $tab view
     */
    function delete_entity($id, $currenturl) {
        global $DB, $OUTPUT;
        $result = $DB->get_record('local_prefix_suffix', array('entityid' => $id));
        if ($result) {
            return false;
        } else {
            $res = $DB->delete_records('local_create_entity', array('id' => $id));
            $this->success_error_msg($res, 'success_del_entity', 'error_del_entity', $currenturl);
        }
    }

    /**
     * @method  check_loginuser_registrar_admin
     * @todo to display school list based logged in user (registrar, admin)     * 
     * @param boolean $schoolids_in_formofstring (used to get schoolids in the form of string)    
     * @return based on condition it returns array of objects or string type of data
     */
    public function check_loginuser_registrar_admin($schoolids_in_formofstring = false) {
        global $DB, $USER, $CFG;
        $hier1 = new hierarchy();
        //  checking of login user is admin..
        if (is_siteadmin($USER->id)) {
            $schoolid = $DB->get_records('local_school', array('visible' => 1));
           
        } else {
            //------------if registrar not assigned to any school it throws exception    
            $users = $hier1->get_manager();
            $schoolid = $hier1->get_assignedschools();
            
        } // end of else
        if (empty($schoolid)) {
            throw new schoolnotfound_exception();
        }
        if ($schoolids_in_formofstring) {
            foreach ($schoolid as $sid) {
                $temp[] = $sid->id;
            }
            $school_id = implode(',', $temp);
            return $school_id;
        } else
            return $schoolid;
    }

}

//end of class prefix_suffix

class notfoundException extends Exception {
    #code
}

class CustomException extends Exception {
    #code
}
