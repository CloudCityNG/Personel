<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class audittrail_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('audittrail_form', 'local_audittrail'));
        $mform->addElement('text', 'reason', get_string('reason', 'local_audittrail'));
        $mform->addRule('reason', get_string('missing_r', 'local_audittrail'), 'required', null, 'client');
        $mform->setType('reason', PARAM_TEXT);
        $mform->addElement('textarea', 'description', get_string("description", "local_audittrail"), 'wrap="virtual" rows="6" cols="150"');
        $mform->addRule('description', get_string('missing_d', 'local_audittrail'), 'required', null, 'client');
        $mform->setType('description', PARAM_TEXT);
        $this->add_action_buttons(true, 'Submit');
    }

}

?>   