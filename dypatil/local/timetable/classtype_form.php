<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class classtypes_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('classtypeheader', 'local_timetable'));

        $timetable_ob = manage_timetable::getInstance();
        $timetable_ob->school_formelement_condition($mform, false);

        $mform->addElement('text', 'classtype', get_string('classtype', 'local_timetable'));
        $mform->addRule('classtype', get_string('spaces', 'local_timetable'), 'regex', '/^[^\s]+[A-Za-z0-9-_\",\s]+$/', 'client');
        $mform->addRule('classtype', get_string('missing_classtype', 'local_timetable'), 'required', null, 'client');
        $mform->setType('classtype', PARAM_TEXT);

        $mform->addElement('checkbox', 'visible', get_string('publish', 'local_timetable'), '', array('checked' => 'checked', 'name' => 'my-checkbox', 'data-size' => 'small', 'data-on-color' => 'info', 'data-off-color' => 'warning', 'data-on-text' => 'Yes', 'data-switch-set' => 'size', 'data-off-text' => 'No', 'class' => 'btn btn-default'));
        $mform->addHelpButton('visible', 'publish', 'local_timetable');
        $mform->setDefault('visible', true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        // $i = get_string('submit', 'local_prefix');
        $this->add_action_buttons();
    }

}
