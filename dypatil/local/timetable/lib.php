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

class manage_timetable {

    private static $_singleton;

    //----constructor not called by outside of the class...only possible with inside the class	 
    private function __construct() {
        
    }

    //----used to crate a object---when the first time of usage of this function ..its create object
    //--by the next time its link to the same object(single tone object)instead of creating new object...
    public static function getInstance() {
        if (!self::$_singleton) {
            self::$_singleton = new manage_timetable();
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
    function timetable_tabs($currenttab = 'addnew', $dynamictab = null, $edit_label = null) {
        global $OUTPUT;
        $systemcontext =  context_system::instance();
        $toprow = array();
   
        $toprow[] = new tabobject('view_timings', new moodle_url('/local/timetable/index.php'), get_string('setstandardview_timings', 'local_timetable'));
        $toprow[] = new tabobject('set_timings', new moodle_url('/local/timetable/settimings.php'), get_string('setstandard_timings', 'local_timetable'));

        $toprow[] = new tabobject('set_classtype', new moodle_url('/local/timetable/classtype.php'), get_string('setclasstypes', 'local_timetable'));
        $toprow[] = new tabobject('scheduleclassview', new moodle_url('/local/timetable/scheduleclassview.php'), get_string('scheduleclass_timetable', 'local_timetable'));

        if ($currenttab == 'schedule_class')
            $toprow[] = new tabobject('scheduleclassview', new moodle_url('/local/timetable/scheduleclassview.php'), get_string('scheduleclassview', 'local_timetable'));

        $toprow[] = new tabobject('calendar_view', new moodle_url('/local/timetable/calendarview.php'), get_string('calendar_view', 'local_timetable'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method add_timeintervals
     * @todo used to set the time intervals for school and semesters
     * @param array object $data (form submitted data)
     * @return-- array object
     */
    function add_timeintervals($data) {
        global $DB, $CFG, $USER;

        $temp = new stdClass();
        $temp->schoolid = $data->schoolid;
        $temp->semesterid = $data->semesterid;
        $temp->visible = $data->visible;
        $temp->timecreated = time();
        $temp->timemodified = time();
        $temp->usermodified = $USER->id;
        for ($i = 0; $i < $data->section_repeats; $i++) {
            if (!empty($data->starthours[$i])  && !empty($data->endhours[$i])) {
                $starttime = sprintf("%02d", $data->starthours[$i]) . ':' . sprintf("%02d", $data->startminutes[$i]) . $data->start_td[$i];


                $temp->starttime = date('H:i:s', strtotime($starttime));
                $endtime = sprintf("%02d", $data->endhours[$i]) . ':' . sprintf("%02d", $data->endminutes[$i]) . $data->end_td[$i];
                $temp->endtime = date('H:i:s', strtotime($endtime));
                //$temp->endtime= sprintf("%02d",$data->endhours[$i]).':'.sprintf("%02d",$data->endminutes[$i]);
                // print_object($temp);

                $insertedrecord = $DB->insert_record('local_timeintervals', $temp);
            }
        }


        return $insertedrecord;
    }

    /**
     * @method timetable_converting_timeformat
     * @todo used to convert timeformat to suitable array format(which helpful to edit the timings in form)
     * @param object (it holds the edited value)
     * @return-- object (which suitable to form)
     */
    function timetable_converting_timeformat($tool) {
        global $CFG, $DB, $USER;

        $records = $DB->get_records('local_timeintervals', array('schoolid' => $tool->schoolid, 'semesterid' => $tool->semesterid));
        $repeatedelement_count = sizeof($records);
        foreach ($records as $record) {
            $starttime = date('h:i a', strtotime($record->starttime));
            $starttime_first = explode(' ', $starttime);
            // print_object($starttime_first);
            $start_td[] = $starttime_first[1];
            $starttime_second = explode(':', $starttime_first[0]);
            // print_object($starttime_second);
            $starthours[] = $starttime_second[0];
            $startminutes[] = $starttime_second[1];

            $endtime = date('h:i a', strtotime($record->endtime));
            $endtime_first = explode(' ', $endtime);
            $end_td[] = $endtime_first[1];
            $endtime_second = explode(':', $endtime_first[0]);
            $endhours[] = $endtime_second[0];
            $endminutes[] = $endtime_second[1];
            $rid[] = $record->id;
        }

        $tool->starthours = $starthours;
        $tool->startminutes = $startminutes;
        $tool->start_td = $start_td;
        $tool->endhours = $endhours;
        $tool->endminutes = $endminutes;
        $tool->end_td = $end_td;
        $tool->rid = $rid;
        $tool->section_repeats = $repeatedelement_count;

        return $tool;
    }

// end of function

    public function timetable_get_listofclasses($schoolid, $semesterid) {
        global $CFG, $OUTPUT, $USER, $DB;
        $res = array(0 => 'Select Class');
        $classlist = $DB->get_records_sql_menu("select id,fullname from  {local_clclasses} 
                where schoolid=$schoolid and semesterid=$semesterid");

        return ($res + $classlist);
    }

    public function timetable_update_timeintervals($data) {
        global $CFG, $OUTPUT, $USER, $DB;
        $temp = new stdClass();
        $temp->schoolid = $data->schoolid;
        $temp->semesterid = $data->semesterid;
        $temp->visible = $data->visible;
        $temp->timecreated = time();
        $temp->timemodified = time();
        $temp->usermodified = $USER->id;
        for ($i = 0; $i < $data->section_repeats; $i++) {
            if( ($data->starthours[$i] > 0 ) && $data->endhours[$i] > 0  ){
            $starttime = sprintf("%02d", $data->starthours[$i]) . ':' . sprintf("%02d", $data->startminutes[$i]) . $data->start_td[$i];
            $temp->starttime = date('H:i:s', strtotime($starttime));
            $endtime = sprintf("%02d", $data->endhours[$i]) . ':' . sprintf("%02d", $data->endminutes[$i]) . $data->end_td[$i];
            $temp->endtime = date('H:i:s', strtotime($endtime));
            //$temp->endtime= sprintf("%02d",$data->endhours[$i]).':'.sprintf("%02d",$data->endminutes[$i]);
            // print_object($temp);
            if ($data->rid[$i] > 0) {
                $temp->id = $data->rid[$i];
                $recordid = $DB->update_record('local_timeintervals', $temp);
            } else {
                $recordid = $DB->insert_record('local_timeintervals', $temp);
            }
            
            } // end of if statement
        } // end of foreach


        return $recordid;
    }

    public function timetable_delete_timeintervals($id) {
        global $CFG, $OUTPUT, $USER, $DB;
        $used = 0;
        $existsrecord = $DB->get_record('local_timeintervals', array('id' => $id));
        $recordslist = $DB->get_records('local_timeintervals', array('schoolid' => $existsrecord->schoolid, 'semesterid' => $existsrecord->semesterid));

        foreach ($recordslist as $record) {
            if ($DB->record_exists('local_scheduleclass', array('timeintervalid' => $record->id))) {
                $used = 2;
                break;
            }

            $list[] = $record->id;
        }
        if ($used) {
            return $used;
        } else {
            if ($DB->delete_records_list('local_timeintervals', 'id', $list))
                return true;
            else
                return false;
        }
    }

    public function timetable_delete_single_timeintervals($id) {
        global $CFG, $OUTPUT, $USER, $DB;
        $used = 0;

        if ($DB->record_exists('local_scheduleclass', array('timeintervalid' => $id))) {
            $used = 2;
        }

        if ($used) {
            return $used;
        } else {
            if ($DB->delete_records('local_timeintervals', array('id' => $id)))
                return true;
            else
                return false;
        }
    }

    public function timetable_hideshow_timeintervals($id, $hide, $show, $from = 0) {
        global $CFG, $OUTPUT, $USER, $DB;
        $used = 0;
        $result = 0;
        $hier = new hierarchy();
        $exist = $DB->get_record('local_timeintervals', array('id' => $id));
        $records = $DB->get_records('local_timeintervals', array('schoolid' => $exist->schoolid, 'semesterid' => $exist->semesterid));
        // if $from is set its from  single timeintervalid (toggle view)
        if ($from == 0) {
            foreach ($records as $record) {
                if ($hide) {
                    // before hiding the timings, checking that, is used any class(while scheduling)
                    $visible = 0;
                    if ($DB->record_exists('local_scheduleclass', array('timeintervalid' => $record->id))) {
                        $used = 1;
                        break;
                    }
                }
                if ($show)
                    $visible = 1;

                $result = $DB->set_field('local_timeintervals', 'visible', $visible, array('id' => $record->id));
            }
        }
        else {
            if ($hide) {
                // before hiding the timings, checking that, is used any class(while scheduling)
                $visible = 0;
                if ($DB->record_exists('local_scheduleclass', array('timeintervalid' => $id))) {
                    $used = 1;
                }
            }
            if ($show)
                $visible = 1;

            $result = $DB->set_field('local_timeintervals', 'visible', $visible, array('id' => $id));
        }


        if ($hide) {
            $data = 'Inactivated';
        } else {
            if ($show)
                $data = 'Activated';
        }
        if ($result) {
            $message = get_string('publish_success', 'local_timetable', $data);
            $style = array('style' => 'notifysuccess');
        } else {
            $message = get_string('publish_failure', 'local_timetable', $data);
            $style = array('style' => 'notifyproblem');
        }
        if ($used) {
            $message = get_string('publish_used', 'local_timetable');
            $style = array('style' => 'notifyproblem');
        }
        $returnurl = $CFG->wwwroot . '/local/timetable/index.php';
        $hier->set_confirmation($message, $returnurl, $style);
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
                $confirm_msg = get_string($success, 'local_timetable', $dynamic_name1);
            else
                $confirm_msg = get_string($success, 'local_timetable');

            $options = array('style' => 'notifysuccess');
        }
        else {
            $confirm_msg = get_string($error, 'local_timetable');
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
    public function school_formelement_condition($mform, $nosubmitbutton = true) {
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
                if ($nosubmitbutton) {
                    $mform->registerNoSubmitButton('updatecourseformat');
                    $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
                }
            } else {
                  foreach($schoolids as $school)
                $schoolname = $DB->get_record('local_school', array('id' => $school->id));
                $mform->addElement('static', 'sid', get_string('schoolid', 'local_collegestructure'), $schoolname->fullname);
                $mform->addElement('hidden', 'schoolid', $school->id);
            }
            $mform->setType('schoolid', PARAM_INT);
        }
    }

    /**
     * @method delete_classtype
     * @todo used to delete the classtype which is not in use..
     * @param type of int $id
     * @param $currenturl it hold url...after confirmation...control move to this url
     * @return $tab view
     */
    function timetable_delete_classtype($id, $currenturl) {
        global $DB, $OUTPUT;
        $result = $DB->record_exists('local_scheduleclass', array('classtypeid' => $id));
        if ($result) {
            return false;
        } else {
            $res = $DB->delete_records('local_class_scheduletype', array('id' => $id));
            $this->success_error_msg($res, 'success_del_classtype', 'error_del_classtype', $currenturl);
        }
    }

    /**
     * @method timetable_addinstructor_field
     * @todo used to get the instructors in the array format
     * @param type of int $instructors
     * @return type(array) instructorslist
     */
    function timetable_addinstructor_field($instructors) {
        global $DB, $CFG;
        if ($instructors) {
            $sql = "SELECT u.id ,concat(u.firstname,'',u.lastname) as fullname
                    FROM  {user} AS u  where u.id in ( $instructors )";
            $instructors = $DB->get_records_sql_menu($sql);
            $count = sizeof($instructors);

            if ($count > 0) {
                $instructors = array('Select instructor') + $instructors;
            }
            return $instructors;
        } else
            return array();
    }

// end of function

    function timetable_get_timeformat($data, $clname) {
        global $DB, $CFG, $USER;

        if (isset($data[$clname]['othertimeinterval'])) {
            $customtimeintervals = $data[$clname]['customtimeintervals'];

            $starthours = $customtimeintervals['starthours'];
            $startminutes = $customtimeintervals['startminutes'];
            $endhours = $customtimeintervals['endhours'];
            $endminutes = $customtimeintervals['endminutes'];
            $start_td = $customtimeintervals['start_td'];
            $end_td = $customtimeintervals['end_td'];

            if ($starthours != 0 && $endhours != 0) {

                $starttime = sprintf("%02d", $starthours) . ':' . sprintf("%02d", $startminutes) . $start_td;
                $endtime = sprintf("%02d", $endhours) . ':' . sprintf("%02d", $endminutes) . $end_td;
                $start_timestamp = strtotime($starttime);
                $end_timestamp = strtotime($endtime);

                $final = array('starttime' => $start_timestamp, 'endtime' => $end_timestamp);
            }
        } else {

            $timintervalid = $data[$clname]['timeinterval'];
            if ($timintervalid) {
                $timings = $DB->get_record('local_timeintervals', array('id' => $timintervalid));
                // making to 12 hour format
                $starttime_12format = date('h:i a', strtotime($timings->starttime));
                $endtime_12format = date('h:i a', strtotime($timings->endtime));
                $start_timestamp = strtotime($starttime_12format);
                $end_timestamp = strtotime($endtime_12format);

                $final = array('starttime' => $start_timestamp, 'endtime' => $end_timestamp);
            }
        }


        if ($final)
            return $final;
        else
            return array();
    }

// end of function

    function timetable_get_availabledays_format($data, $clname) {
        global $CFG, $DB;

        $days_arrayname = $clname . 'days';
        $mon = $data[$days_arrayname]['mon'];
        $tue = $data[$days_arrayname]['tue'];
        $wed = $data[$days_arrayname]['wed'];
        $thu = $data[$days_arrayname]['thu'];
        $fri = $data[$days_arrayname]['fri'];
        $sat = $data[$days_arrayname]['sat'];
        $sun = $data[$days_arrayname]['sun'];

        $mon ? $availabledates[] = 'M' : null;
        $tue ? $availabledates[] = 'TU' : null;
        $wed ? $availabledates[] = 'W' : null;
        $thu ? $availabledates[] = 'TH' : null;
        $fri ? $availabledates[] = 'F' : null;
        $sat ? $availabledates[] = 'SA' : null;
        $sun ? $availabledates[] = 'SU' : null;

        return $availabledates;
    }

// end of funtcion

    function timetable_availabledays_objectformat_tostring($formavailabledates) {
        global $CFG, $DB, $USER;

        $formavailabledates['mon'] ? $availabledates[] = 'M' : null;
        $formavailabledates['tue'] ? $availabledates[] = 'TU' : null;
        $formavailabledates['wed'] ? $availabledates[] = 'W' : null;
        $formavailabledates['thu'] ? $availabledates[] = 'TH' : null;
        $formavailabledates['fri'] ? $availabledates[] = 'F' : null;
        $formavailabledates['sat'] ? $availabledates[] = 'SA' : null;
        $formavailabledates['sun'] ? $availabledates[] = 'SU' : null;
        $availabledates = implode('-', $availabledates);
        return $availabledates;
    }

// end of function

    function timetable_timeintervals_formformat($formtimeintervaldata, $schoolid, $semesterid) {
        global $CFG, $DB, $USER;
        $result = array();
        if ( isset( $formtimeintervaldata['othertimeinterval']) ) {
            $data = $formtimeintervaldata['customtimeintervals'];
            $starttime = sprintf("%02d", $data['starthours']) . ':' . sprintf("%02d", $data['startminutes']) . $data['start_td'];
            $temp->starttime = date('H:i:s', strtotime($starttime));
            $endtime = sprintf("%02d", $data['endhours']) . ':' . sprintf("%02d", $data['endminutes']) . $data['end_td'];
            $temp->endtime = date('H:i:s', strtotime($endtime));
            $temp->schoolid = $schoolid;
            $temp->semesterid = $semesterid;
            $temp->visible = 1;
            $temp->usermodified = $USER->id;
            $temp->timecreated = time();
            $temp->timemodified = time();
            $insertedrecord = $DB->insert_record('local_timeintervals', $temp);

            $result['timeintervalid'] = $insertedrecord;
            $result['starttime'] = $temp->starttime;
            $result['endtime'] = $temp->endtime;
        } else {
            if ($formtimeintervaldata['timeinterval'] > 0) {
                $interval = $DB->get_record('local_timeintervals', array('id' => $formtimeintervaldata['timeinterval']));
                $result['timeintervalid'] = $interval->id;
                $result['starttime'] = $interval->starttime;
                $result['endtime'] = $interval->endtime;
            }
        }

        return $result;
    }

// end of function

    /**
     * @method session_addclassroom_resources
     * @Todo used to insert and update classroom resources , which is created from sessionform
     * @param object $formdata  
     * @retun int classroomid or zero based on condition 
     * */
    function timetable_addclassroom_resources($formclassroomres, $id, $schoolid) {
        global $DB, $CFG, $USER;
        $classroomid = 0;

        if ($formclassroomres['customclroom'] && !isset($formtimeintervaldata['classroomid'])) {
            $formdata = $formclassroomres['customclroom'];
            // insertion part
            $temp_building = new stdClass();
            $temp_building->fullname = $formdata['building'];
            $temp_building->shortname = $formdata['building'];
            $temp_building->visible = 1;
            $temp_building->schoolid = $schoolid;
            $new_buildingid = $DB->insert_record('local_building', $temp_building);
            if ($new_buildingid) {
                $temp_floor = new stdClass();
                $temp_floor->buildingid = $new_buildingid;
                $temp_floor->fullname = $formdata['floor'];
                $temp_floor->shortname = $formdata['floor'];
                $temp_floor->visible = 1;
                $temp_floor->schoolid = $schoolid;
                $new_floorid = $DB->insert_record('local_floor', $temp_floor);
            }
            if ($new_floorid) {
                $temp_classroom = new stdClass();
                $temp_classroom->buildingid = $new_buildingid;
                $temp_classroom->floorid = $new_floorid;
                $temp_classroom->fullname = $formdata['classroom'];
                $temp_classroom->shortname = $formdata['classroom'];
                $temp_classroom->visible = 1;
                $temp_classroom->schoolid = $schoolid;
                $new_classroomid = $DB->insert_record('local_classroom', $temp_classroom);
            }

            $classroomid = $new_classroomid;
        } else {
      
            if ($formclassroomres->classroomid ) {
                $classroomid = $formclassroomres->classroomid;
            }
            // used while editing classroom
            else{
                if(isset($formclassroomres['classroomid']))
                $classroomid = $formclassroomres['classroomid'];
            }        
 
            
        }

        return $classroomid;
    }

// end of function

    function timetable_addscheduleclass_instance($data, $classtype) {
        global $DB, $CFG, $USER, $PAGE;
        $temp = new stdClass();
        $classtypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $classtype));
        $clname = $classtypeinfo->classtype;
        $availabledays = $clname . 'days';
        $classtypeid = $classtypeinfo->id;
        $temp->id = $data->id;
        $temp->classtypeid = $classtypeid;
        $temp->availableweekdays = $this->timetable_availabledays_objectformat_tostring($data->$availabledays);
        $startdate = $clname . 'dates[startdate]';
        $enddate = $clname . 'dates[enddate]';
        $temp->startdate = $data->$startdate;
        $temp->enddate = $data->$enddate;
        $insname = $clname . 'instructor';
        $insarray = $data->$insname;
        $instructorid = ($insarray['instructorid'] ? $insarray['instructorid'] : 0);
        $temp->instructorid = $instructorid;
        $timeintervalinfo = $this->timetable_timeintervals_formformat($data->$clname, $data->schoolid, $data->semesterid);
        $temp->timeintervalid = $timeintervalinfo['timeintervalid'];
        $temp->starttime = $timeintervalinfo['starttime'];
        $temp->endtime = $timeintervalinfo['endtime'];
        $classinfo = $DB->get_record('local_clclasses', array('id' => $data->classid));
        if ($classinfo->online != 1) {
            $classroomname = $clname . 'classroom';
            $classroomid = $this->timetable_addclassroom_resources($data->$classroomname, $data->id, $data->schoolid);
            $temp->classroomid = $classroomid;
        }

        $temp->schoolid = $data->schoolid;
        $temp->semesterid = $data->semesterid;
        $temp->classid = $data->classid;
        $temp->courseid = $data->courseid;
        $temp->departmentinid = $data->departmentid;
        $temp->usermodified = $USER->id;
        $temp->timecreated = time();
        $temp->timemodified = time();

        $sql = ("select * from {local_scheduleclass} where startdate =$temp->startdate and enddate= $temp->enddate
                   and  starttime = '$temp->starttime' and endtime = '$temp->endtime' and availableweekdays ='$temp->availableweekdays' and id != $data->id" );

        $exists = $DB->get_records_sql($sql);
       $count=sizeof($exists);
         
        if ($count > 0){
          
            return false;
        }
        else{
           
            return $temp;
        }
    }

// end of function

    function timetable_converting_dbdata_toeditform($tool) {
        global $DB, $CFG, $USER;
        if ($tool->classtypeid) {
            $classtype = array($tool->classtypeid);
            $tool->classtype = $classtype;
        }
        foreach ($classtype as $cltype) {
            $classtypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $cltype));
            $clname = $classtypeinfo->classtype;
            $startdate = $clname . 'dates[startdate]';
            $tool->$startdate = $tool->startdate;
            $enddate = $clname . 'dates[enddate]';
            $tool->$enddate = $tool->enddate;
            $days = $clname . 'days';
            $a = array();

            $availabledate = explode('-', $tool->availableweekdays);
            in_array('M', $availabledate) ? $a['mon'] = 1 : $a['mon'] = 0;
            in_array('TU', $availabledate) ? $a['tue'] = 1 : $a['tue'] = 0;
            in_array('W', $availabledate) ? $a['wed'] = 1 : $a['wed'] = 0;
            in_array('TH', $availabledate) ? $a['thu'] = 1 : $a['thu'] = 0;
            in_array('F', $availabledate) ? $a['fri'] = 1 : $a['fri'] = 0;
            in_array('SA', $availabledate) ? $a['sat'] = 1 : $a['sat'] = 0;
            in_array('SU', $availabledate) ? $a['sun'] = 1 : $a['sun'] = 0;
            $tool->$days = $a;
            $timeinterval = $clname . '[timeinterval]';
            $tool->$timeinterval = $tool->timeintervalid;
            $classroom = $clname . 'classroom';
            $tool->$classroom = array('classroomid' => $tool->classroomid);
            $instructorfield = $clname . 'instructor';
            $tool->$instructorfield = array('instructorid' => $tool->instructorid);

            //  print_object($tool);

            return $tool;
        }
    }

    /**
     * @method timetable_delete_scheduleclass
     * @todo used to delete the scheduled classinfo
     * @param type of int $id
     * @param $currenturl it hold url...after confirmation...control move to this url
     * @return $tab view
     */
    function timetable_delete_scheduleclass($id, $currenturl) {
        global $DB, $OUTPUT;
        $res = $DB->delete_records('local_scheduleclass', array('id' => $id));
        $this->success_error_msg($res, 'success_del_scheduleclass', 'error_del_scheduleclass', $currenturl);
    }

    function timetable_display_instructorname($classob) {
        global $DB, $OUTPUT, $USER, $CFG;
        $instructor_string = array();
        $res_instructor_string = ' ';
        if(isset($classob->instructor))
         $instructors = $classob->instructor;
        
        if ($instructors) {
            $instructorsinfo = $DB->get_records_sql("SELECT u.id ,concat(u.firstname,'',u.lastname) as fullname
                    FROM  {user} AS u  where u.id in ( $instructors )");

            foreach ($instructorsinfo as $ins) {
                $instructor_string[] = $ins->fullname;
            }
            $res_instructor_string = implode(' , ', $instructor_string);
        }



        return $res_instructor_string;
    }

// end of function

    /**
     * @method check_conflicts
     * @todo To check the classes sheduling time conflicts
     * @param int $startdate Class Startdate
     * @param int $enddate Class Enddate
     * @param int $starttime Class Starttime
     * @param int $endtime Class endtime
     * @param int $schoolid SchoolID
     * @param int $insid Instructor ID
     * @param int $classroomid Class room ID
     * @return boolean true or false
     */
    function check_conflicts($startdate, $enddate, $starttime, $endtime, $schoolid, $classroomid, $id = -1, $availabledates) {
        global $CFG, $DB;

        $st = $starttime;
        $et = $endtime;
        /* Bug report #254 
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * Resolved- (provided proper validation)while scheduling classroom -eventhough classroom is free it showing busy.
         */

        $sql = "SELECT * FROM {local_scheduleclass} 
			         WHERE id!=$id AND schoolid={$schoolid} 
					 AND 
					( startdate BETWEEN '{$startdate}' AND '{$enddate}' OR 
					 enddate BETWEEN '{$startdate}' AND '{$enddate}' )AND 
                     (starttime BETWEEN '{$st}' AND '{$et}' OR 
			         endtime BETWEEN '{$st}' AND '{$et}') ";
        if ($classroomid) {
            $sql = " AND classroomid={$classroomid} ";
        }

        $classrooms = $DB->get_records_sql($sql);
        // Edited by hema

        $presentavailabledates = $availabledates;
        $flag = 0;
        foreach ($classrooms as $class) {
            $existavailabledates = explode('-', $class->availableweekdays);
            $result = array_intersect($presentavailabledates, $existavailabledates);
            if (empty($result))
                $result = array_intersect($existavailabledates, $presentavailabledates);
            //print_object($result);
            if (!empty($result)) {
                $flag = 1;
                break;
            } else
                continue;
        }

        if (empty($classrooms) && $flag == 0) {
            return 0;
        } elseif (!empty($classrooms) && $flag == 0)
            return 0;
        else {
            return 1;
        }
    }

}

// end of class
?>
