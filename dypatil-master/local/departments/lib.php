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
 * @package    manage_departments
 * @subpackage  list of all functions which is used in departments plugin
 * @copyright  2012 Hemalatha arun <Hemaltha@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');

//  class manage_dept contsins list of functions....which is used by department plugin

class manage_dept {

    private static $_singleton;

    //----constructor not called by outside of the class...only possible with inside the class	 
    private function __construct() {
        
    }

    //----used to crate a object---when the first time of usage of this function ..its create object
    //--by the next time its link to the same object(single tone object)instead of creating new object...
    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new manage_dept();
        }
        return self::$_singleton;
    }

    /**
     * @method dept_tabs
     * @todo it provides the tab view(particularly for this plugin) 
     * @param string $currentab by default it hold the first tab name
     * @param string $dynamictab by default its null ,if passes the parameter it creates dynamic tab
     * @return--it displays the tab
     */
    function dept_tabs($currenttab = 'addnew', $dynamictab = null, $edit_label = null) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();

        // if($edit_label)
        //  $edit_e=($edit_label!='edit_label')? get_string('edit_department','local_departments'):get_string('addnew','local_departments'); 

        if ($edit_label) {
            $create_cap = array('local/departments:manage', 'local/departments:update');
            if (has_any_capability( $create_cap, $systemcontext))
                $toprow[] = new tabobject('addnew', new moodle_url('/local/departments/departments.php'), get_string('edit_department', 'local_departments'));
        }
        else {
            $update_cap = array('local/departments:manage', 'local/departments:create');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('addnew', new moodle_url('/local/departments/departments.php'), get_string('addnew', 'local_departments'));
        }

        $toprow[] = new tabobject('deptlist', new moodle_url('/local/departments/index.php'), get_string('deptlist', 'local_departments'));
        if (!empty($dynamictab)) {
            if ($dynamictab == "view")
                $toprow[] = new tabobject('view', new moodle_url('/local/departments/viewdept.php'), get_string('view', 'local_departments'));
        }
        $toprow[] = new tabobject('display_instructor', new moodle_url('/local/departments/display_instructor.php'), get_string('display_instructor', 'local_departments'));

        if (!empty($dynamictab) && $dynamictab != "view") {
            $assigninstructor_cap = array('local/departments:manage', 'local/departments:assigninstructor');
            if (has_any_capability($assigninstructor_cap, $systemcontext))
                $toprow[] = new tabobject('assign_instructor', new moodle_url('/local/departments/assign_instructor.php'), get_string('assign_instructor', 'local_departments'));
        }

        $assignschool_cap = array('local/departments:manage', 'local/departments:assignschool');
        if (has_any_capability($assignschool_cap, $systemcontext))
            $toprow[] = new tabobject('assign_school', new moodle_url('/local/departments/assign_school.php'), get_string('assignschool', 'local_collegestructure'));


        $toprow[] = new tabobject('upload', new moodle_url('/local/departments/upload.php'), get_string('uploaddepartments', 'local_departments'));
        $toprow[] = new tabobject('help', new moodle_url('/local/departments/info.php'), get_string('help', 'local_departments'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method add_department
     * @todo create a new department...if the same name department alreday exists ...cannot create again
     * @param array object $data (form submitted data)
     * @return-- array object
     */
    function add_department($data) {
        global $DB, $CFG;
        //print_object($data);
        $deptname = $DB->get_records('local_department', array('schoolid' => $data->schoolid));
        foreach ($deptname as $dn) {
            //check if name is already used ...   if its so...redirect back to view page
            if ($data->fullname == strtolower($dn->fullname)) {
                return '-3';
            }
        }
        $re = $DB->insert_record('local_department', $data);
        return $re;
    }

    /**
     * @method remove_defaultschool
     * @todo it remove defaults school and also already assigned school from drop down list (used in assigned school)
     * @param array $temp it holds the school list of login registrar
     * @return array of school list
     */
    function remove_defaultschool($temp = null, $deptid) {
        global $CFG, $DB;
        $tempsidlist = $temp;
        foreach ($tempsidlist as $s => $s_value) {
            $default_school = $DB->get_record('local_department', array('id' => $deptid));
            if ($s_value->id == $default_school->schoolid)
                unset($tempsidlist[$s]);
            $exists_assignee = $DB->get_records('local_assignedschool_dept', array('deptid' => $deptid));
            foreach ($exists_assignee as $exists) {
                if ($s_value->id == $exists->assigned_schoolid)
                    unset($tempsidlist[$s]);
            }
        }

        return $tempsidlist;
    }

    /**
     * @method delete_update_department
     * @todo it delete and update departmnet...based on some condition ...if its in already use..it simply redirect
     * @param  int $id it holds id of the dept
     * @param  int $del it helps move delete part
     * @param  int $update it helps to move update part
     * @param  array object $data it holds the form submitted data
     * @return $returnurl
     */
    function delete_update_department($id, $del, $update, $data = null, $currenturl, $checking_exists = false) {
        echo $checking_exists;

        global $DB, $USER, $CFG;
        $hier1 = new hierarchy();
        $ta = array('local_assignedschool_dept', 'local_dept_instructor', 'local_cobaltcourses');
        $table = '';
        $i = 1;
        $tt[1] = 't1.deptid=' . $id;
        $tt[2] = 't2.departmentid=' . $id;
        $tt[3] = 't3.departmentid=' . $id;
        foreach ($ta as $ts) {
            $tables = $this->check_table_exists($ts);
            if (!empty($tables))
                $table.="{$CFG->prefix}" . $tables . ' as t' . $i . ', ';
            else
                $tt[$i] = 0;
            $i++;
        }
        $table = substr($table, 0, -2);
        $where = '';
        foreach ($tt as $w)
            $where.=$w . ' or ';
        $where = substr($where, 0, -3);
        if (!empty($update)) { //updation part
            $data->timemodified = time();
            $res = $DB->update_record('local_department', $data);
            return $res;
        }
        if (!empty($table)) {
            $table_string = $table;
            if (stristr($table_string, 'local_assignedschool_dept') === TRUE) {
                $sql = "select distinct deptid from  $table where $where";
            } else {
                $sql = "select distinct t2.departmentid from  $table where $where";
            }
            $used_dept = $DB->get_records_sql($sql);
        } else
            $used_dept = 0;
        if (!empty($used_dept)) {
            return '-3';
        } else {
            if (!empty($del) && ($checking_exists == false)) {//deleting part
                $res = $DB->delete_records('local_department', array('id' => $id));
                return $res;
            }
        }
    }

//end of function

    /**
     * @method assign_instructor_department
     * @todo to assign instructor to department
     * @param array $inslist instructor list    
     * @return int newly inserted record ID.
     */
    function assign_instructor_department($inslist) {
        global $DB, $USER, $CFG;
        //print_object($inslist);
        //  exit();
        $tool = new stdClass();
        $tool->departmentid = $inslist->moveto;
        $tool->schoolid = $inslist->ins_schoolid;
        $tool->timecreated = time();
        $tool->timemodified = time();
        $tool->usermodified = $USER->id;
        $insid = $inslist->check;
        foreach ($insid as $ins_id) {
            $tool->instructorid = $ins_id;
            $exists_inslist = $DB->get_record('local_dept_instructor', array('instructorid' => $ins_id, 'schoolid' => $inslist->ins_schoolid));
            if (!empty($exists_inslist)) {
                return 0;
                //return false;
            } else {
                //  print_object($tool);
                $id = $DB->insert_record('local_dept_instructor', $tool);
                /* ---start of vijaya--- */
                $conf = new object();
                $conf->username = $DB->get_field('user', 'username', array('id' => $ins_id));
                $conf->deptname = $DB->get_field('local_department', 'fullname', array('id' => $tool->departmentid));
                $message = get_string('msg_add_ins_dept', 'local_departments', $conf);
                $userfrom = $DB->get_record('user', array('id' => $USER->id));
                $userto = $DB->get_record('user', array('id' => $ins_id));
                /* Bug report #253  -  Invoice Messages
                 * @author hemalatha c arun <hemalatha@eabyas.in> 
                 * Resolved - changed the message format
                 */
                $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
                /* ---end of vijaya--- */
            }
        }
        return $id;
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
    function success_error_msg($output, $success, $error, $currenturl, $dynamic_name1 = false) {
        $hier = new hierarchy();
        if ($output) {
            if ($dynamic_name1)
                $confirm_msg = get_string($success, 'local_departments', $dynamic_name1);
            else
                $confirm_msg = get_string($success, 'local_departments');

            $options = array('style' => 'notifysuccess');
        }
        else {
            $confirm_msg = get_string($error, 'local_departments');
            $options = array('style' => 'notifyproblem');
        }

        $hier->set_confirmation($confirm_msg, $currenturl, $options);
    }

    /**
     * @method  check_table_exists
     * @todo to check whether table is exist or what
     * @param string $tablename table name    
     * @return boolean or string, if exists it returns table name else 0
     */
    function check_table_exists($tablename) {
        global $DB, $CFG;
        $res = $DB->get_records($tablename);
        if ($res)
            return $tablename;
        else
            return 0;
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
                $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
                $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            } else {
                $schoolname = $DB->get_record('local_school', array('id' => $schoolids[0]->id));
                $mform->addElement('static', 'sid', get_string('schoolid', 'local_collegestructure'), $schoolname->fullname);
                $mform->addElement('hidden', 'schoolid', $schoolids[0]->id);
            }
            $mform->setType('schoolid', PARAM_INT);
        }
    }

    /*
     * function to get shortnames of departments
     */
    function get_snames() {
        global $DB;
        $results = $DB->get_records('local_department');
        return $results;
    }

}

?>
