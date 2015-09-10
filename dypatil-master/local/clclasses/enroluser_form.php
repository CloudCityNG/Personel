<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class enroluser_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;
        $mform = & $this->_form;
        $classid = $this->_customdata['id'];
        $semid = $this->_customdata['semid'];
        $attribute = array();
        $today = time();
        $date = date('Y-m-d');
        //=======
        $schoolin = $DB->get_field('local_clclasses', 'schoolid', array('id' => $classid));
        $ss = $DB->get_records_sql("SELECT scl.semesterid FROM {local_semester} AS sem
					JOIN {local_school_semester} AS scl
                   ON scl.semesterid = sem.id WHERE scl.schoolid IN ($schoolin) AND (
					('{$date}' < from_unixtime( sem.startdate,  '%Y-%m-%d' )) OR 
					('{$date}' BETWEEN from_unixtime( sem.startdate,  '%Y-%m-%d' ) AND from_unixtime( sem.enddate,  '%Y-%m-%d' ))
					)
					group by scl.id ORDER BY sem.startdate DESC");
        $dd = array_keys($ss);

        if (array_search($semid, $dd) == false) {
            $message = get_string('noactivesemester', 'local_clclasses');
            $attribute = array('disabled' => 'disabled');
            $atrb = array('disabled' => 'disabled', 'size' => 15);
        } else {
            $atrb = array('size' => 15);
        }
        // $registration = $DB->get_record_select('local_event_activities', 'eventtypeid = 2 AND semesterid = '.$semid.' AND '.$today.' BETWEEN startdate AND enddate AND publish = 1');
        // $adddrop = $DB->get_record_select('local_event_activities', 'eventtypeid = 3 AND semesterid = '.$semid.' AND '.$today.' BETWEEN startdate AND enddate AND publish = 1');
        // if(!$registration && !$adddrop){
        // $enablelink = '<a target="_blank" href="'.$CFG->wwwroot.'/local/academiccalendar/index.php">Enable Here</a>';
        // $message = get_string('noeventisenabled', 'local_clclasses', $enablelink);
        // $attribute = array('disabled'=>'disabled');
        // }
        echo $message;
        //======
        $achoices = array();
        $schoices = array();
        $ausers = & $this->_customdata['susers'];
        $achoices = userselector($classid);
        //$schoices[0] = get_string('allselectedusers', 'bulkusers');
        $susers = potentialuserselector($classid);
        $schoices = $susers;

        $PAGE->requires->yui_module('moodle-local_clclasses-enrol', 'M.local_clclasses.init_enrol', array(array('formid' => $mform->getAttribute('id'))));


        $objs = array();
        $objs[0] = & $mform->createElement('selectgroups', 'ausers', get_string('available', 'bulkusers'), $achoices, 'size="15"');
        $objs[0]->setMultiple(true);
        $objs[1] = & $mform->createElement('submit', 'addall', get_string('enroluser', 'local_clclasses'), $attribute);
        $objs[2] = & $mform->createElement('selectgroups', 'susers', get_string('selected', 'bulkusers'), $schoices, $atrb);
        $objs[2]->setMultiple(true);
        $objs[3] = & $mform->createElement('cancel', 'cancel', get_string('cancel'), 'style="margin-left: -225px;
        margin-top: 80px;"');
        $grp = & $mform->addElement('group', 'usersgrp', get_string('users', 'bulkusers'), $objs, ' ', false);
        $mform->addHelpButton('usersgrp', 'users', 'bulkusers');

        $mform->addElement('hidden', 'id', $classid);
        $mform->setType('id', PARAM_RAW);
        $mform->addElement('hidden', 'semid', $semid);
        $mform->setType('semid', PARAM_RAW);
        // $mform->addElement('hidden', 'activesemid', $activesemesterid);
        $mform->setType('activesemid', PARAM_INT);
    }

}
