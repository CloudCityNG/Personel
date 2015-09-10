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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage academiccalendar
 * @copyright  2012 Naveen<naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once('../lib.php');
require_once 'lib.php';
/* ---Class for Creating events--- */

class createevent_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $PAGE, $DB;
        $mform = $this->_form;
        $acalendar = academiccalendar :: get_instatnce();
        $eid = $this->_customdata['id'];
        if ($eid > 0) {
            $mform->addElement('header', 'settingsheader', get_string('editevent', 'local_academiccalendar'));
        } else {
            $mform->addElement('header', 'settingsheader', get_string('Createevent', 'local_academiccalendar'));
        }

        for ($year = 2000; $year <= 2050; $year++) {
            $years[NULL] = "---Select---";
            $years[$year] = $year;
        }
        $mform->addElement('select', 'academicyear', get_string('selectacademicyear', 'local_academiccalendar'), $years);
        $mform->addRule('academicyear', get_string('missingacademyear', 'local_academiccalendar'), 'required', null, 'client');
        if ($eid > 0) {
            $evelevel = $DB->get_record('local_event_activities', array('id' => $eid));
            $mform->addElement('static', 'activityname', get_string('seleventtype', 'local_academiccalendar'));
            if ($evelevel->eventlevel != NULL) {
                $mform->addElement('static', 'eventlevelname', get_string('seleventlevel', 'local_academiccalendar'));
            }
            $mform->addElement('text', 'eventtitle', get_string('eventtitle', 'local_academiccalendar'));
            $mform->addRule('eventtitle', get_string('missingeventtitle', 'local_academiccalendar'), 'required', null, 'client');
            $mform->setType('eventtitle', PARAM_RAW);
            if (($evelevel->eventlevel > 1) || ($evelevel->eventlevel == 1 && $evelevel->eventtypeid <= 3) || $evelevel->eventlevel == NULL) {
                $mform->addElement('static', 'schoolname', get_string('schoolid', 'local_collegestructure'));
            }
            if ($evelevel->eventlevel == 3 || ($evelevel->eventlevel == 1 && $evelevel->eventtypeid == 1)) {
                $mform->addElement('static', 'programname', get_string('program', 'local_programs'));
            }
            if ($evelevel->eventlevel == 4 || ($evelevel->eventlevel == 1 && ($evelevel->eventtypeid == 2 || $evelevel->eventtypeid == 3)) || $evelevel->eventlevel == NULL) {
                $mform->addElement('static', 'semestername', get_string('semester', 'local_semesters'));
            }
            $mform->addElement('hidden', 'eveid');
            $mform->setType('eveid', PARAM_INT);
            $mform->addElement('hidden', 'eventtypeid');
            $mform->setType('eventtypeid', PARAM_RAW);
        } else {

            $PAGE->requires->yui_module('moodle-local_academiccalendar-selectfaculty', 'M.local_academiccalendar.init_selectfaculty', array(array('formid' => $mform->getAttribute('id'))));

            $eventts = $acalendar->get_eventtypes();
            $mform->addElement('select', 'eventtypeid', get_string('seleventtype', 'local_academiccalendar'), $eventts);
            $mform->addRule('eventtypeid', get_string('missingeventtype', 'local_academiccalendar'), 'required', null, 'client');
            $mform->addElement('text', 'eventtitle', get_string('eventtitle', 'local_academiccalendar'));
            $mform->addElement('hidden', 'eventlevelpos');
            $mform->addHelpButton('eventtypeid', 'eventtypeid', 'local_academiccalendar');
            $mform->setType('eventlevelpos', PARAM_RAW);
            $mform->addRule('eventtitle', get_string('missingeventtitle', 'local_academiccalendar'), 'required', null, 'client');
            $mform->setType('eventtitle', PARAM_RAW);
            $mform->addElement('hidden', 'addeventtitle');
            $mform->setType('addeventtitle', PARAM_RAW);
            $mform->addElement('hidden', 'addschoollisthere');
            $mform->setType('addschoollisthere', PARAM_RAW);
            $mform->addElement('hidden', 'addprogramslisthere');
            $mform->setType('addprogramslisthere', PARAM_RAW);
            $mform->addElement('hidden', 'addsemesterslisthere');
            $mform->setType('addsemesterslisthere', PARAM_RAW);
            $mform->registerNoSubmitButton('updatecourseformat');
            $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        }
        $mform->addElement('editor', 'description', get_string('eventdescription', 'local_academiccalendar'), null, null);
        $mform->setType('description', PARAM_RAW);
        // used to provide static date description while slecting dates for adddrop and registration event
           $academiccalendar_object =academiccalendar ::get_instatnce();    
          if ($eid > 0) 
            $academiccalendar_object->static_description_dateselection($mform, $evelevel->semesterid,$evelevel->eventtypeid,$evelevel->schoolid, NULL,1);
         else{
           // adding static description , which is easy to select the startdate and enddate 
           $mform->addElement('hidden', 'dateselector_description');
           $mform->setType('dateselector_description', PARAM_RAW);
        }
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_academiccalendar'));
        $mform->addRule('startdate', get_string('missingstartdate', 'local_academiccalendar'), 'required', null, 'client');
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        $mform->addHelpButton('enddate', 'enddate', 'local_academiccalendar');
        $mform->addElement('advcheckbox', 'publish', get_string('publish', 'local_academiccalendar'), null, null, array(0, 1));
        $mform->addElement('html', '<div id="err"></div>');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if ($eid > 0) {
            $this->add_action_buttons('false', 'Update Event');
        } else {
            $this->add_action_buttons('false', 'Create Event');
        }
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $eid = $this->_customdata['id'];
        $errors = array();
        if ($eid > 0) {
            $active_rec = $DB->get_record('local_event_activities', array('id' => $eid));
            isset($data['eventlevel']) ? $evelevel = $data['eventlevel'] : $evelevel = $active_rec->eventlevel;
            $eve_sem = $active_rec->semesterid;
        }
        if (isset($data['semesterid'])) {
            $eve_sem = $data['semesterid'];
        }
        isset($data['schoolid']) ? $eve_school = $data['schoolid'] : $eve_school = $active_rec->schoolid;
        // if(isset($data['schoolid'])){
        // $eve_school =  $data['schoolid'];
        // }
        isset($eve_sem) ? $present_sem = $DB->get_record('local_semester', array('id' => $eve_sem)) : null;
        if ($data['startdate'] != 0 && $data['enddate'] != 0) {
            $date1_val = date('d/m/Y', $data['startdate']);
            list( $day1, $month1, $year1) = explode('/', $date1_val);
            $d1 = $day1;
            $m1 = $month1;
            $y1 = $year1;
            $date2_val = date('d/m/Y', $data['enddate']);
            list( $day2, $month2, $year2) = explode('/', $date2_val);
            $d2 = $day2;
            $m2 = $month2;
            $y2 = $year2;
            if ($y1 != $data['academicyear'] || $y2 < $data['academicyear']) {
                $errors['academicyear'] = get_string('pleaseselectyear', 'local_academiccalendar');
            }
        }

        if ($data['startdate'] != 0 && $data['enddate'] != 0 && ($data['startdate'] > $data['enddate'])) {
            $errors['enddate'] = get_string('timeoverstartingtime', 'local_academiccalendar');
        }
        if ($eid <= 0) {
            if (isset($data['startdate']) && isset($data['programid']) && $data['eventtypeid'] == 1) {
                if (isset($data['enddate']) && $data['enddate'] == 0) {
                    $exe_admission = $DB->get_records_sql('select * from {local_event_activities} where programid=' . $data["programid"] . ' AND  enddate = 0');
                } else {
                    $ex_admission = $DB->get_records_sql('select * from {local_event_activities} where programid=' . $data["programid"] . ' AND  startdate<= ' . $data["startdate"] . ' AND enddate>=' . $data["enddate"] . '');
                }

                if (!empty($ex_admission)) {
                    $errors['startdate'] = get_string('alreadyexistafterstartdate', 'local_academiccalendar');
                }
                if (!empty($exe_admission)) {
                    $errors['programid'] = get_string('alreadyexistadmission', 'local_programs');
                }
            }
        }
        if ($data['eventtypeid'] == 2) {
            $presentregestration = $DB->get_record('local_event_activities', array('semesterid' => $eve_sem, 'schoolid' => $eve_school, 'eventtypeid' => 2));
            if ($eid < 0) {
                if (!isset($presentregestration) || !empty($presentregestration)) {
                    $presentregestration->startdate = date('d M Y', $presentregestration->startdate);
                    $presentregestration->enddate = date('d M Y', $presentregestration->enddate);
                    $errors['semesterid'] = get_string('regalreadyexist', 'local_academiccalendar', $presentregestration);
                }
            }
            if ($data['enddate'] > $present_sem->enddate) {
                $present_sem->startdate = date('d M Y', $present_sem->startdate);
                $present_sem->enddate = date('d M Y', $present_sem->enddate);
                $eid > 0 ?
                                $errors['semestername'] = get_string('regoutofrangesem', 'local_academiccalendar', $present_sem) :
                                $errors['semesterid'] = get_string('regoutofrangesem', 'local_academiccalendar', $present_sem);
            }
        }
        if ($data['eventtypeid'] == 3) {
            $presentregestration = $DB->get_record('local_event_activities', array('semesterid' => $eve_sem, 'schoolid' => $eve_school, 'eventtypeid' => 2));
            $presentaddanddrop = $DB->get_record('local_event_activities', array('semesterid' => $eve_sem, 'schoolid' => $eve_school, 'eventtypeid' => 3));
            if (!empty($presentaddanddrop)) {
                $presentaddanddrop->startdate = date('d M Y', $presentaddanddrop->startdate);
                $presentaddanddrop->enddate = date('d M Y', $presentaddanddrop->enddate);
            }
      
            
            if ($eid < 0) {

                if (isset($presentaddanddrop) && !empty($presentaddanddrop)) {

                    $errors['semesterid'] = get_string('adddropalreadyexist', 'local_academiccalendar', $presentaddanddrop);
                }
            }
            if ($data['enddate'] > $present_sem->enddate) {
                $present_sem->startdate = date('d M Y', $present_sem->startdate);
                $present_sem->enddate = date('d M Y', $present_sem->enddate);
                $eid > 0 ?
                                $errors['startdate'] = get_string('adddropoutofrangesem', 'local_academiccalendar', $present_sem) :
                                $errors['startdate'] = get_string('adddropoutofrangesem', 'local_academiccalendar', $present_sem);
            } elseif ($presentregestration->enddate >= $data['startdate']) {
                  // used to check when registration event ends before start of add/ drop events
                $presentregestration->enddate = date('d M Y', $presentregestration->enddate);
                $eid > 0 ?
                                $errors['startdate'] = get_string('adddropoutofrangereg', 'local_academiccalendar', $presentregestration) :
                                $errors['startdate'] = get_string('adddropoutofrangereg', 'local_academiccalendar', $presentregestration);
            }
        }
        if (isset($evelevel) && $evelevel == 4 && $data['eventtypeid'] != 2) {
            $semdates = $DB->get_record('local_semester', array('id' => $eve_sem));
            $sem_startdate = $semdates->startdate;
            $sem_enddate = $semdates->enddate;
            if ($data['startdate'] < $sem_startdate) {
                $errors['startdate'] = get_string('betweensemesterstart', 'local_academiccalendar');
            }
            if ($data['enddate'] != 0 && $data['enddate'] > $sem_enddate) {
                $errors['enddate'] = get_string('betweensemesterend', 'local_academiccalendar');
            }
        }
        if ($data['eventtypeid'] == 2 || $data['eventtypeid'] == 3) {
            if ($data['enddate'] == 0) {
                $errors['enddate'] = get_string('requiredforregandadddrop', 'local_academiccalendar');
            }
        }
        return $errors;
    }

    function definition_after_data() {
        global $DB;
        $eid = $this->_customdata['id'];
        $mform = $this->_form;
        $hierarchy = new hierarchy();
        $acalendar = academiccalendar :: get_instatnce();
        if ($eid <= 0) {
            $eventtype = $mform->getElementValue('eventtypeid');
        }
        if (isset($eventtype) && $eventtype[0]) {
            $evelevel = array();
            if ($eventtype[0] == 1) {
                $evelevel = array('1' => 'Global', '2' => 'Organization', '3' => 'Program');
            } elseif ($eventtype[0] == 2 || $eventtype[0] == 3) {
                $evelevel = array('4' => 'Course Offering');
            } else {
                $evelevel = $acalendar->eventls;
            }
            $evel = $mform->createElement('select', 'eventlevel', get_string('selecteventlevel', 'local_academiccalendar'), $evelevel);
            $mform->insertElementBefore($evel, 'eventlevelpos');
            $eventlevel = $mform->getElementValue('eventlevel');
            $mform->addHelpButton('eventlevel', 'eventlevel', 'local_academiccalendar');
        }
        if ($eventtype[0] == 2 || $eventtype[0] == 3) {
            $get_value = $acalendar->ac_hierarchyelements($mform, 'addprogramslisthere', 'addsemesterslisthere', 4, $eventtype[0]);
        } else

        if ($eid <= 0) {
            if (isset($eventlevel) && $eventlevel[0]) {
                $get_value = $acalendar->ac_hierarchyelements($mform, 'addprogramslisthere', 'addsemesterslisthere', $eventlevel[0]);
            }
        }
    }

}
