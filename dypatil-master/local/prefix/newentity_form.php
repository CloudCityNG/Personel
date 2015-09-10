<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class newentity_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('newentityh', 'local_prefix'));
        $tools = array();
        $mform->addElement('text', 'entity_name', get_string('new_entity', 'local_prefix'), $tools);
        $mform->addRule('entity_name', get_string('spaces', 'local_prefix'), 'regex', '/^[^\s]+[A-Za-z0-9-_\",\s]+$/', 'client');
        $mform->addRule('entity_name', get_string('new_entity', 'local_prefix'), 'required', null, 'client');
        $mform->setType('entity_name', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $i = get_string('submit', 'local_prefix');
        $this->add_action_buttons($i);
    }

}
