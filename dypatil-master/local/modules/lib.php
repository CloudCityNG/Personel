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
 * @subpackage Module
 * @copyright  2013 Sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\module as module;

require_once($CFG->dirroot . '/local/lib.php');
/* Function to add the data into the database  */

class cobalt_modules {
    
    /**
     * @method module_add_instance
     * @todo To create new module(storing in database)
     * @param object $tool it holds the module data
     * @return int of inserted row ID(module ID)
     * */
    function module_add_instance($tool, $userid) {
        global $DB;
        $tool->timecreated = time();
        $tool->usermodified = $userid;
        $tool->id = $DB->insert_record('local_module', $tool);
        return $tool->id;
    }
    
    /**
     * @method module_update_instance
     * @todo To update existing module(storing in database)
     * @param object $tool it holds the module data
     * @param int $userid it holds User ID 
     * @return int of updated row ID(module ID).
     * */
    function module_update_instance($tool, $userid) {
        global $DB;
        $tool->timemodified = time();
        $tool->usermodified = $userid;
        $tool->id = $DB->update_record('local_module', $tool);
        return $tool->id;
    }

    /**
     * @method module_delete_instance
     * @todo To delete existing module(storing in database)
     * @param int $tool it holds the module ID.
     * @return boolean type of data or void
     * */ 
    function module_delete_instance($tool) {
        global $DB;
        $result = $DB->delete_records('local_module', array('id' => $tool));
        if ($result)
            return true;
    }
    

    /**
     * @method unassign_courses_instance
     * @todo To delete course of a module
     * @param int $tool it holds the module ID.
     * @param int $courseid it holds Course ID.
     * @return boolean type of data
     * */ 
    function unassign_courses_instance($tool, $courseid) {
        global $DB;
        $data = $DB->delete_records('local_module_course', array('moduleid' => $tool, 'courseid' => $courseid));
        return $data;
    }  
    
    
     /**
     * @method add_courses
     * @todo To add course to particular module.
     * @param array $courseids it holds list of courses.
     * @param int $moduleid Module ID.
     * @return boolean type of value.
     * */ 
    function add_courses($courseids, $moduleid) {
        global $CFG, $DB, $OUTPUT, $USER;
        if (empty($courseids)) {
            return;
        }
        if (!$module = $DB->get_record('local_module', array('id' => $moduleid))) {
            return false;
        }
        $courseids = array_reverse($courseids);
        foreach ($courseids as $courseid) {
            $cate = $DB->get_record('local_cobaltcourses', array('id' => $courseid), 'id, departmentid');
            if ($cate) {
                $course = new stdClass();
                $course->courseid = $courseid;
                $course->moduleid = $moduleid;
                $course->departmentid = $cate->departmentid;
                $now = date("d-m-Y");
                $course->timecreated = strtotime($now);
                $course->usermodified = $USER->id;
                $DB->insert_record('local_module_course', $course);
            }
        }
        // fix_course_sortorder();
        return true;
    }


    /**
     * @method get_coursename
     * @todo To get coursename of a course .
     * @param int $id Cobaltcourse ID.    
     * @return string coursename.
     * */ 
    function get_coursename($id) {
        global $CFG, $DB;
        $out = array();
        $modulelists = $DB->get_records('local_cobaltcourses', array('id' => $id));
        foreach ($modulelists as $modulelist) {
            $out = format_string($modulelist->fullname);
        }
        return $out;
    }

    function get_programname($id) {
        global $CFG, $DB;
        //  $out = array();
        $programlists = $DB->get_records('local_program', array('id' => $id, 'visible' => 1));
        foreach ($programlists as $programlist) {
            $out = $programlist->fullname;
            return $out;
        }
    }

     /**
     * @method success_error_msg
     * @todo used to provide valid success and error message .
     * @param object $output resultent object.
     * @param string $success string
     * @param string $error string
     * @param string $currenturl it holds the url
     * @param string $data (dynamic data for language strings)     
     * @return print html format text.
     * */ 
    function success_error_msg($output, $success, $error, $currenturl, $data) {
        $hier = new hierarchy();
        if ($output) {
            $confirm_msg = get_string($success, 'local_modules', $data);
            $options = array('style' => 'notifysuccess');
        } else {
            $confirm_msg = get_string($error, 'local_modules', $data);
            $options = array('style' => 'notifyproblem');
        }
        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }

    
     /**
     * @method print_tabs
     * @todo To generate tab view.
     * @param string $currenttab tab name.    
     * @return print tab tree.
     * */ 
    function print_tabs($currenttab, $id) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();
        if ($id < 0) {
            $create_cap = array('local/modules:manage', 'local/modules:create');
            if (has_any_capability($create_cap, $systemcontext))
                $toprow[] = new tabobject('new', new moodle_url('/local/modules/module.php', array('id' => -1)), get_string('new', 'local_modules'));
        }
        else {
            $update_cap = array('local/modules:manage', 'local/modules:update');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('edit', new moodle_url('/local/modules/module.php'), get_string('editmodule', 'local_modules'));
        }
        $toprow[] = new tabobject('lists', new moodle_url('/local/modules/index.php'), get_string('viewmodules', 'local_modules'));
        $toprow[] = new tabobject('info', new moodle_url('/local/modules/info.php'), get_string('info', 'local_modules'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

}
