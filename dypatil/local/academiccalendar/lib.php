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
 * Language strings
 *
 * @package    local
 * @subpackage Academic calendar
 * @copyright  2012 Naveen <naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');
/* ---Creates object for global lib hierarchy class--- */
$hierarchy = new hierarchy();
/*
 * Academic calendar class contains library functions
 * which useful in academic calendar plugin
 */

class academiccalendar {
    /* ---Declaring static object variable--- */

    private static $acalendar;
    public $eventls = array('1' => 'Global',
        '2' => 'Organization',
        '3' => 'Program',
        '4' => 'Course Offering');
    public $eventls_filter = array('0' => 'All',
        '1' => 'Global',
        '2' => 'Organization',
        '3' => 'Program',
        '4' => 'Course Offering');

    /* ---Declaring construct as private for singleton--- */

    private function __construct() {
        
    }

    /**
     * We are using singleton for this class 
     * @method get_instance
     * @todo get object for academic calendar class
     * @return object of this class
     */
    public static function get_instatnce() {
        if (!self :: $acalendar) {
            self :: $acalendar = new academiccalendar();
        }
        return self :: $acalendar;
    }

    /**
     * @method eventtype_add_instance
     * @todo Creates new event type
     * @param object $etdata (event data object)
     * @return created event type id
     */
    public function eventtype_add_instance($etdata) {
        global $DB;
        $etdata->id = $DB->insert_record('local_event_types', $etdata);
        return $etdata->id;
    }

    /**
     * @method eventtype_update_instance
     * @param object $etdata (event data object)
     * @todo Updates eventtype
     */
    public function eventtype_update_instance($etdata) {
        global $DB;
        $DB->update_record('local_event_types', $etdata);
    }

    /**
     * @method eventtype_delete_instance
     * @param int eventtype ID
     * @todo Deletes particular eventtype
     */
    public function eventtype_delete_instance($etid) {
        global $DB;
        $DB->delete_records('local_event_types', array('id' => $etid));
        $DB->delete_records('event', array('uuid' => $etid));
    }

    /**
     * @method get_eventtypes
     * @todo Get all event types
     * @return event types array
     */
    public function get_eventtypes($sem = false) {
        global $CFG, $DB;
        $hierarchy = new hierarchy();
        if ($sem == true)
            $eventtype = $hierarchy->get_records_cobaltselect_menu('local_event_types', 'id!=1', null, '', 'id,eventtypename', '--Select--');
        else
            $eventtype = $hierarchy->get_records_cobaltselect_menu('local_event_types', '', null, '', 'id,eventtypename', '--Select--');
        return $eventtype;
    }

    /**
     * Note: $edata for event data instance
     * @method event_add_instance
     * @todo To add new event
     * @return event types array
     */
    public function event_add_instance($edata) {
        global $DB;
        $edat = $DB->insert_record('local_event_activities', $edata);
        return $edat;
    }

    /**
     * @method event_update_instance
     * @todo To update particular event
     */
    public function event_update_instance($edata) {
        global $DB;
        $DB->update_record('local_event_activities', $edata);
    }

    /**
     * @method event_delete_instance
     * @todo Deletes particular event
     */
    public function event_delete_instance($id) {
        global $DB;
        $DB->delete_records('local_event_activities', array('id' => $id));
    }

    /**
     * *************Note: This function  creates default moodle event with acadamiccalendar event data***** 
     * @method devent_add
     * @todo Create default moodle event
     * @param object $edata (events data object)
     * @return created event ID
     */
    public function devent_add($edata) {
        global $DB;
        $devent = $DB->insert_record('event', $edata);
        return $devent;
    }

    /**
     * @method devent_update
     * @param int evet Id
     * @todo Updates particular event (default moodle)
     */
    public function devent_update($edata) {
        global $DB;
        $DB->update_record('event', $edata);
    }  

    
    /**
     * @method ac_hierarchyelements
     * @todo creates hierarchy elements in form (school,program and semester)
     * @param1 object $mform  (form object)
     * @param2 string $place1 (element1 position)
     * @param3 string $place2 (element2 position)
     * @param4 int $eventlevel (event level ID)
     * @param5 int $eventtypeid (event type ID)
     * @return form elments add to form 
     */
    public function ac_hierarchyelements($mform, $place1, $place2, $eventlevel, $eventtype) {
        global $USER, $DB;
        $hierarchy = new hierarchy();
        if ($eventlevel != 1) {
            $isadmin = is_siteadmin($USER);
            if ($isadmin) {
                $school = $hierarchy->get_records_cobaltselect_menu('local_school', 'visible=1', null, '', 'id,fullname', '--Select--');
            } else {
                $faculties = $hierarchy->get_assignedschools();
                $school = $hierarchy->get_school_parent($faculties, null, true, false);
            }
            $newel = $mform->createElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->insertElementBefore($newel, $place1);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $school_value = $mform->getElementValue('schoolid');
        }
        /* ---Creating program element after getting the school value--- */
        if (isset($school_value) && !empty($school_value) && $school_value[0] > 0 && $eventlevel == 3) {
            $school_id = $school_value[0];
            $programs = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$school_value[0] AND visible=1", null, '', 'id,fullname', '--Select--');
            $newel2 = $mform->createElement('select', 'programid', get_string('program', 'local_programs'), $programs);
            $mform->insertElementBefore($newel2, $place2);
            $mform->addRule('programid', get_string('missingfullname', 'local_programs'), 'required', null, 'client');
            $program_value = $mform->getElementValue('programid');
            return $program_value;
        }
        if (isset($school_value) && !empty($school_value) && $school_value[0] > 0 && $eventlevel == 4) {
            $school_id = $school_value[0];
            //if ($eventtype == 2)
            //    $semesters = $hierarchy->get_upcoming_school_semesters($school_id);
            //else
                $semesters = $hierarchy->get_school_semesters($school_id);
            $newel2 = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $semesters);
            $mform->insertElementBefore($newel2, $place2);
            $mform->addRule('semesterid', get_string('semester_help', 'local_semesters'), 'required', null, 'client');
            $sem_value = $mform->getElementValue('semesterid');
            if($sem_value[0]){
               $this->static_description_dateselection($mform, $sem_value[0],$eventtype,$school_id,1);
            }
            return $sem_value;
        }
    }
    
    
      /**
     * @method static_description_dateselection
     * @todo to provide date discription it make easy to end user to select date
     * @param1 object $mform  (form object)
     * @param2 int $semesterid 
     * @param3 int $eventtype  
     * @param4 int $schoolid
     * @param5 int $create(used to indicate creating new event)
     * @param5 int $edit(used to indicate edit event)
     * @return form elments add to form 
     */    
    public function  static_description_dateselection($mform, $semesterid, $eventtype, $schoolid = NULL, $create = NULL, $edit = NULL ){
        global  $DB, $USER;
           $date_description = new stdClass();
          if($semesterid && ($eventtype == 2 || $eventtype == 3 )){
              // registration event 
                if($eventtype == 2)
                 $datestring= 'academicc_registrationdescription';
             else{
                // add/drop event 
                if($schoolid)
                 $presentregestration = $DB->get_record('local_event_activities', array('semesterid' => $semesterid, 'schoolid' => $schoolid, 'eventtypeid' => 2));
                if($presentregestration){
                    
                    $date_description->registrationstartdate =  date("d M Y",$presentregestration->startdate);
                    $date_description->registrationenddate =  date("d M Y",$presentregestration->enddate);
                    $datestring = 'bothsemester_registrationdate';
                }
                else
                 // only add/ drop event for specific to semester and school
                 $datestring= 'academicc_adddropdescription';
             } 
            $seminfo=$DB->get_record('local_semester',array('id'=>$semesterid)); 
            $date_description->startdate= date("d M Y",$seminfo->startdate);
            $date_description->enddate= date("d M Y",$seminfo->enddate);
            if($edit)
            $mform->addElement('static', 'datedescription', '', get_string($datestring, 'local_academiccalendar',$date_description ));
            else{
             $staticdate_description =$mform->createElement('static', 'datedescription', '', get_string($datestring, 'local_academiccalendar',$date_description ));
             $mform->insertElementBefore($staticdate_description , 'dateselector_description');
            }
           
            }        
        
    }

    /**
     * @method event_whereclause
     * @todo To define the where condition for getting event activities
     * @param1 int $evl  (Event level)
     * @param2 boolean $day (Day wise or not)
     * @param3 boolean $week (Week wise or not)
     * @param4 boolean $month (Month wise or not)
     * @param5 boolean $year (Year wise or not)
     * @return where clause for sql query 
     */
    public function event_whereclause($day = false, $week = false, $month = false, $year = false) {
        $duration = 0;
        $ptime = date('Y-m-d');
        $pweek = date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 1 week"));
        $pmonth = date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 1 month"));
        $pyear = date('Y-m-d', strtotime(date("Y-m-d", time()) . " + 1 year"));
        $start_string = '(DATE(FROM_UNIXTIME(startdate))';
        $end_string = 'DATE(FROM_UNIXTIME(enddate))';
        $sql = "";
        if ($day) {
            $sql .="$start_string= '$ptime' OR $end_string='$ptime')";
        } else if ($week) {
            $duration = $pweek;
        } else if ($month) {
            $duration = $pmonth;
        } else if ($year) {
            $duration = $pyear;
        }
        if ($duration > 0) {
            $sql .= "$start_string between '$ptime' and '$duration' OR $end_string between '$ptime' and '$duration')";
        }
        return $sql;
    }

    /**
     * @todo To filter the event activities for particular day or next Week from present day or next month and next year
     * @method render_duration_filters
     * @return renders the filter elements
     */
    public function render_duration_filters() {
        global $OUTPUT, $PAGE, $CFG, $day, $week, $month, $year;
        $url = $PAGE->url;
        $filterids = array('df', 'wf', 'mf', 'yf');
        $filters = '<ul style="list-style:none;display:block;">';
        if ($day) {
            $filters .= '<li style="float:left;" id="active">';
        } else {
            $filters .= '<li style="float:left;">';
        }
        $filters .='' . html_writer::link(new moodle_url($url, array('df' => '1')), html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/academiccalendar/images/df.png', 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall_df'))) . '</li>';
        if ($week) {
            $filters .= '<li style="float:left;" id="active">';
        } else {
            $filters .= '<li style="float:left;">';
        }
        $filters .= '' . html_writer::link(new moodle_url($url, array('wf' => '1')), html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/academiccalendar/images/wf.png', 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall_wf'))) . '</li>';
        if ($month) {
            $filters .= '<li style="float:left;" id="active">';
        } else {
            $filters .= '<li style="float:left;">';
        }
        $filters .= '' . html_writer::link(new moodle_url($url, array('mf' => '1')), html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/academiccalendar/images/mf.png', 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall_mf'))) . '</li>';
        if ($year) {
            $filters .= '<li style="float:left;" id="active">';
        } else {
            $filters .= '<li style="float:left;">';
        }
        $filters .= '' . html_writer::link(new moodle_url($url, array('yf' => '1')), html_writer::empty_tag('img', array('src' => $CFG->wwwroot . '/local/academiccalendar/images/yf.png', 'title' => get_string('active'), 'alt' => get_string('show'), 'class' => 'iconsmall_yf'))) . '</li>';
        $filters .='</ul>';
        return $filters;
    }

    /**
     * @method get_programs
     * @todo to get list of programs of a school
     * @param int $id achool Id.   
     * @return array of objects(program list)
     */
    function get_programs($id) {
        global $DB;
        $results = $DB->get_records('local_program', array('schoolid' => $id, 'visible' => 1));
        return $results;
    }

    /**
     * @method get_sems
     * @todo to get list of semesters of a school
     * @param int $id achool Id.   
     * @return array of objects(Semester list)
     */
    function get_sems($id) {
        global $DB;
        $results = $DB->get_records('local_semester', array('schoolid' => $id, 'visible' => 1));
        return $results;
    }

    /**
     * @method get_rolename
     * @todo get the user role id based on userid
     * @return string rolename
     */
    public function get_rolename() {
        global $DB, $USER;
        $systemcontext = context_user::instance($USER->id);
        $userroleid = $DB->get_field('role_assignments', 'roleid', array('contextid' => $systemcontext->id));
        $regrolename = $DB->get_field('role', 'shortname', array('id' => $userroleid));
        return $regrolename;
    }

    /**
     * @method is_registrar
     * @todo To check that  present logged in user had registrar role
     * @return boolean 
     */
    public function is_registrar() {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $userroleid = $DB->get_field('role_assignments', 'roleid', array('contextid' => $systemcontext->id, 'userid' => $USER->id));
        $regrolename = $DB->get_field('role', 'shortname', array('id' => $userroleid));
        if ($regrolename == 'registrar') {
            return true;
        }
    }

    /**
     * @method is_student
     * @todo To check that  present logged in user had student role
     * @return boolean 
     */
    public function is_student() {
        $rolename = $this->get_rolename();
        if ($rolename == 'student') {
            return true;
        }
    }

    /**
     * @method is_instructor
     * @todo To check that  present logged in user had instructor role
     * @return boolean 
     */
    public function is_instructor() {
        global $DB, $USER;
        $systemcontext = context_system::instance();
        $userroleid = $DB->get_field('role_assignments', 'roleid', array('contextid' => $systemcontext->id, 'userid' => $USER->id));
        $regrolename = $DB->get_field('role', 'shortname', array('id' => $userroleid));
        if ($regrolename == 'instructor') {
            return true;
        }
    }

}
