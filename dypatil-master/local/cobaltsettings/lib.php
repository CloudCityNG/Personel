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
 * @subpackage  provide the global settings...
 * @copyright  2012 Hemalatha arun <hemalatha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/lib.php');
defined('MOODLE_INTERNAL') or die;

use moodle\local\globalsettings as globalsettings;

/* ---class global_settings ...contains list of all functions which is used to manage global settings--- */

class global_settings {

    private static $_singleton;

    /* ---used to avoid creating object from outside the class--- */

    private function __construct() {
        
    }

    /* ---creating singleton object through member function(inside the class)--- */

    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new global_settings ();
        }
        return self::$_singleton;
    }

    /**
     * @method  globalsettings_tabs
     * @todo used to provide the static tabs only for prefix_suffix plugin
     * @param type of string $currenttab(it hold the active tab name)
     * @return $tab view
     */
    function globalsettings_tabs($currenttab, $category_tab, $id = 0) {
        global $OUTPUT;
        $toprow = array();
        $systemcontext = context_system::instance();
        if ($category_tab == 'entitysettings') {
            $nav_subhead = ($id > 0 ? 'editentity_level_settings' : 'entity_level');

            if (has_capability('local/cobaltsettings:manage', $systemcontext))
                $toprow[] = new tabobject('define_entitylevel', new moodle_url('/local/cobaltsettings/category_level.php'), get_string($nav_subhead, 'local_cobaltsettings'));
            $toprow[] = new tabobject('view_entitylevel', new moodle_url('/local/cobaltsettings/view_categorylevel.php'), get_string('view_entitylevel', 'local_cobaltsettings'));
            $toprow[] = new tabobject('default_entities', new moodle_url('/local/cobaltsettings/default_entities.php'), get_string('default_entity', 'local_cobaltsettings'));
        }
        if ($category_tab == 'gpasettings') {
            $nav_subhead = ($id > 0 ? 'editgpasettings' : 'gpasettings');
            if (has_capability('local/cobaltsettings:manage', $systemcontext))
                $toprow[] = new tabobject('gpa_settings', new moodle_url('/local/cobaltsettings/gpa_settings.php'), get_string($nav_subhead, 'local_cobaltsettings'));
            $toprow[] = new tabobject('view_gpa', new moodle_url('/local/cobaltsettings/view_gpasettings.php'), get_string('view_gpa', 'local_cobaltsettings'));
            $toprow[] = new tabobject('info', new moodle_url('/local/cobaltsettings/info.php'), get_string('info', 'local_cobaltsettings'));
        }
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method  success_error_msg
     * @todo providing valid success and error message based on condition
     * @param object $output resultent record
     * @param string $success Success message
     * @param string $error Error messgae
     * @param string $currenturl redirection url
     * @return printing valid message
     */
    function success_error_msg($output, $success, $error, $currenturl) {
        $hier = new hierarchy();
        if ($output) {
            $confirm_msg = get_string($success, 'local_cobaltsettings');
            $options = array('style' => 'notifysuccess');
        } else {
            $confirm_msg = get_string($error, 'local_cobaltsettings');
            $options = array('style' => 'notifyproblem');
        }
        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }    

    /**
     * @method  get_category_types
     * @todo to get settings types(like graduation,undergraduation etc)
     * @param int $categoryid Category ID.
     * @param boolean $select(if true sending in the form of array)
     * @return array (list of types)
     */
    public function get_category_types($categoryid, $select = false) {
        global $DB, $CFG;
        $scho = array();
        $cate_types = $DB->get_records('local_cobalt_subentities', array('entityid' => $categoryid));
        if ($select) {
            $scho[] = get_string('selectentity', 'local_cobaltsettings');
            foreach ($cate_types as $c) {
                $scho[$c->id] = $c->name;
            }
            return $scho;
        } else {
            return $cate_types;
        }
    }

    /**
     * @method  school_formelement_condition
     * @todo to display school field form element based on condition (if multiple school providing drop down box else static field)
     * @param object $mform    
     * @return array of objects(form elements)
     */
    public function school_formelement_condition($mform) {
        global $DB, $CFG, $USER;
        $hier = new hierarchy();
        if (is_siteadmin($USER->id)) {
            $schoolids = $DB->get_records('local_school', array('visible' => 1));
        } else
            $schoolids = $hier->get_assignedschools();
        if (!empty($schoolids)) {
            $count = sizeof($schoolids);
            if ($count > 1) {
                $parents = $hier->get_school_parent($schoolids, '', true);
                $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents);
                $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            } else {
                 foreach($schoolids as $sid){
                  $schoolid=$sid->id;
                  $schoolname=$sid->fullname;
                }          
                $mform->addElement('static', 'sid', get_string('schoolid', 'local_collegestructure'), $schoolname);
                $mform->addElement('hidden', 'schoolid', $schoolid);
            }
            $mform->setType('schoolid', PARAM_INT);
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
        /* ---checking of login user is admin--- */
        if (is_siteadmin($USER->id)) {
            $schoolid = $DB->get_records('local_school', array('visible' => 1));

        } else {
            /* ---if registrar not assigned to any school it throws exception--- */
            $users = $hier1->get_manager();
            $schoolid = $hier1->get_assignedschools();

        }
        if (empty($schoolid)) {   
           throw new schoolnotfound_exception();           
         }
        /* ---end of else--- */
        if ($schoolids_in_formofstring) {
            foreach ($schoolid as $sid) {
                if ($sid->id != null) {
                    $temp[] = $sid->id;
                }
            }
            $school_id = implode(',', $temp);
            return $school_id;
        } else
            return $schoolid;
    }

}

/*---end of class global settings---*/

