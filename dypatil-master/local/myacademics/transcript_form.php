<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class transcriptform extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT, $USER;
        $mform = & $this->_form;
        $PAGE->requires->yui_module('moodle-local_myacademics-semester', 'M.local_myacademics.init_semester', array(array('formid' => $mform->getAttribute('id'))));

        $program = student_enrolled_program($USER->id);
        if (count($program) > 2) {
            $mform->addElement('select', 'programid', get_string('program', 'local_programs'), $program);
            $mform->addRule('programid', get_string('required'), 'required', null, 'client');
            $mform->addElement('hidden', 'beforesem');
            $mform->setType('beforesem', PARAM_RAW);
            $mform->addElement('hidden', 'beforecourse');
            $mform->setType('beforecourse', PARAM_RAW);
            $mform->registerNoSubmitButton('updatecourseformat');
            $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        } else {
            $program = get_student_program($USER->id);
            $pname = $DB->get_field('local_program', 'fullname', array('id' => $program));
            $mform->addElement('static', 'program', get_string('program', 'local_programs'), $pname);
            $mform->addElement('hidden', 'programid', $program);
            $mform->setType('programid', PARAM_RAW);
            $sem = student_enrolled_sem($program);
            $mform->addElement('select', 'semesterid', get_string('semester', 'local_semesters'), $sem);
            $mform->addRule('semesterid', get_string('required'), 'required', null, 'client');
        }
        $this->add_action_buttons('false', 'Submit');
    }

    function definition_after_data() {
        global $DB, $CFG, $USER;
        $mform = $this->_form;
        $program = student_enrolled_program($USER->id);
        if (count($program) > 2) {
            $pid = $mform->getElementValue('programid');
            if (isset($pid) && !empty($pid) && $pid[0] > 0) {
                $sem = student_enrolled_sem($pid[0]);
                $one = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $sem);
                $mform->insertElementBefore($one, 'beforesem');
                $mform->addRule('semesterid', get_string('required'), 'required', null, 'client');
            }
        }
    }

}
