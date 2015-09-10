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
 * List the tool provided in a course
 *
 * @package    assign mentor to student
 * @subpackage  list of all functions which is used in assign mentor plugin
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * class assign_mentortostudent contains list of functions
 * which is used by assign mentor to student plugin
 */
class assign_mentortostudent {

    private static $_singleton;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new assign_mentortostudent();
        }
        return self::$_singleton;
    }

    /**
     * @method assignmentor_tabs
     * @todo To create the tabs for assignmentor
     * @parameter string $currenttab Current tab
     */
    function assignmentor_tabs($currenttab = 'addnew', $dynamictab = null) {
        global $OUTPUT;
        $toprow = array();
        $systemcontext = context_system::instance();
        $toprow[] = new tabobject('view', new moodle_url('/local/assignmentor/index.php'), get_string('view', 'local_assignmentor'));

        //if(has_capability('local/assignmentor:manage', $systemcontext))
        $toprow[] = new tabobject('assignmentor', new moodle_url('/local/assignmentor/assign_mentor.php'), get_string('assign', 'local_assignmentor'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method  assign_mentorparent_tostudent
     * @todo it insert mentor and student list ...only if student not assigned to any mentor
     * @param array object $stulist(it holds the student list)
     * @return $returnurl
     */
    function assign_mentorparent_tostudent($stulist) {
        global $DB, $USER, $CFG;
        $tool = new stdClass();
        $tool->schoolid = $stulist->sid;
        $tool->programid = $stulist->pid;
        $tool->mentorid = $stulist->mentorid;
        $tool->timecreated = time();
        $tool->usermodified = $USER->id;
        $studentlist = $stulist->check;
        foreach ($studentlist as $stu_id) {
            $tool->studentid = $stu_id;
            $exists_inslist = $DB->get_record('local_assignmentor_tostudent', array('studentid' => $stu_id, 'schoolid' => $stulist->sid, 'programid' => $stulist->pid));
            if (!empty($exists_inslist)) {
                return 0;
            } else {
                $id = $DB->insert_record('local_assignmentor_tostudent', $tool);
            }
        }
        return $id;
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
        if (is_siteadmin($USER->id)) {
            $schoolid = $DB->get_records('local_school', array('visible' => 1));

        } else {
            /* ----if registrar not assigned to any school it throws exception--- */
            $users = $hier1->get_manager();
            $schoolid = $hier1->get_assignedschools();

        }
        if (empty($schoolid)) {    
             throw new schoolnotfound_exception();
        }
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

?>
