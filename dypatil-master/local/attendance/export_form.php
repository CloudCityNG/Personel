<?php

require_once($CFG->libdir . '/formslib.php');

//Class to create export form
class local_attendance_export_form extends moodleform {

    public function definition() {

        global $USER;
        $mform = & $this->_form;
        $attendanceid = $this->_customdata['attendanceid'];
        $attendanceobject = $this->_customdata['attendanceobject'];
        $classid = $this->_customdata['classid'];
        $mform->addElement('header', 'general', get_string('export', 'local_attendance'));
        $grouplist[0] = get_string('allparticipants');
        $mform->addElement('select', 'group', get_string('group'), $grouplist);

        $ident = array();
        $ident[] = & $mform->createElement('checkbox', 'id', '', get_string('studentid', 'local_attendance'));
        $ident[] = & $mform->createElement('checkbox', 'uname', '', get_string('username'));
        $mform->addGroup($ident, 'ident', get_string('identifyby', 'local_attendance'), array('<br />'), true);
        $mform->setDefaults(array('ident[id]' => true, 'ident[uname]' => true));
        $mform->setType('id', PARAM_INT);
        $mform->setType('uname', PARAM_INT);

        $mform->addElement('checkbox', 'includeallsessions', get_string('includeall', 'local_attendance'), get_string('yes'));
        $mform->setDefault('includeallsessions', true);
        $mform->addElement('checkbox', 'includenottaken', get_string('includenottaken', 'local_attendance'), get_string('yes'));
        $mform->addElement('date_selector', 'sessionstartdate', get_string('startofperiod', 'local_attendance'));
        $mform->setDefault('sessionstartdate', $attendanceobject->semesterinfo->startdate);
        $mform->disabledIf('sessionstartdate', 'includeallsessions', 'checked');
        $mform->addElement('date_selector', 'sessionenddate', get_string('endofperiod', 'local_attendance'));
        $mform->disabledIf('sessionenddate', 'includeallsessions', 'checked');

        $mform->addElement('select', 'format', get_string('format'), array('csv' => 'Download in CSV Format')
        );

        $submit_string = get_string('ok');
        $mform->addElement('hidden', 'attendanceid', $attendanceid);
        $mform->setType('attendanceid', PARAM_RAW);
        $mform->addElement('hidden', 'classid', $classid);
        $mform->setType('classid', PARAM_RAW);
        $this->add_action_buttons(false, $submit_string);

        $mform->addElement('hidden', 'id', $attendanceid);
    }

}
