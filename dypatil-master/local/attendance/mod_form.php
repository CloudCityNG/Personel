<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot . '/course/moodleform_mod.php');

//class to create attendance form
class local_attendance_form extends moodleform {

    public function definition() {
        $mform = & $this->_form;
        $classid = $this->_customdata['classid'];

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name'), array('size' => '64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->setDefault('name', get_string('modulename', 'local_attendance'));

        $mform->addElement('modgrade', 'grade', get_string('grade'));
        $mform->setDefault('grade', 100);


        $mform->addElement('header', 'modstandardelshdr', get_string('modstandardels', 'form'));

        $mform->addElement('modvisible', 'visible', get_string('visible'));
        $mform->addElement('text', 'cmidnumber', get_string('idnumbermod'));
        $mform->setType('cmidnumber', PARAM_RAW);
        $mform->addHelpButton('cmidnumber', 'idnumbermod');
        $options = array(NOGROUPS => get_string('groupsnone'),
            SEPARATEGROUPS => get_string('groupsseparate'),
            VISIBLEGROUPS => get_string('groupsvisible'));
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $options, NOGROUPS);
        $mform->addHelpButton('groupmode', 'groupmode', 'group');
        $mform->addElement('hidden', 'classid', $classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

}

//class to create  school timings
class local_schooltime_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        if ($count > 1) {
            $school = $hierarchy->get_school_parent($scho);
            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
        } else {
            $school = $DB->get_record('local_school', array('visible' => 1));
            $mform->addElement('static', 'school', get_string('schoolid', 'local_collegestructure'), $school->fullname);
            $mform->setType('school', PARAM_RAW);

            $mform->addElement('hidden', 'schoolid', $school->id);
            $mform->setType('schoolidid', PARAM_INT);
        }

        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i+=5) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $durtime = array();
        $durtime[] = & $mform->createElement('select', 'starthours', get_string('hour', 'form'), $hours, false, true);
        $durtime[] = & $mform->createElement('select', 'startminutes', get_string('minute', 'form'), $minutes, false, true);
        $mform->addGroup($durtime, 'starttime', get_string('starttime', 'local_attendance'), array(' '), true);


        $durtimes = array();
        $durtimes[] = & $mform->createElement('select', 'endhours', get_string('hour', 'form'), $hours, false, true);
        $durtimes[] = & $mform->createElement('select', 'endminutes', get_string('minute', 'form'), $minutes, false, true);
        $mform->addGroup($durtimes, 'endtime', get_string('orgendtime', 'local_attendance'), array(' '), true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submit = ($this->_customdata['id'] > 0) ? 'Update' : 'Create';
        $this->add_action_buttons('false', $submit);
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();

        if ($data['starttime']['starthours'] > $data['endtime']['endhours']) {
            $errors['starttime'] = 'Enter Valid time';
        }

        return $errors;
    }

}
