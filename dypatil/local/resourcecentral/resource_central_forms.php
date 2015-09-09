<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class resource_central_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = & $this->_form;


        $attachmentoptions = $this->_customdata['attachmentoptions'];
        $id = $this->_customdata['id'];
        $courseid = $this->_customdata['courseid'];

        $mform->addElement('header', 'moodle', get_string('heading', 'local_resourcecentral'));


        $mform->addElement('text', 'title', get_string('title', 'local_resourcecentral'));
        $mform->addRule('title', get_string('required'), 'required', null, 'client');
        $mform->setType('title', PARAM_RAW);

        $mform->addElement('editor', 'description', get_string('description', 'local_resourcecentral'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('filemanager', 'itemid', get_string('contentfile', 'local_resourcecentral'), null, $attachmentoptions);
        $mform->addRule('itemid', get_string('required'), 'required', null, 'client');


        $mform->addElement('url', 'url', get_string('externalurl', 'url'), array('size' => '60'), array('usefilepicker' => true));
        $mform->setType('url', PARAM_URL);
        //$mform->addRule('url', null, 'required', null, 'client');

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_RAW);

        $submit = 'Submit';
        $this->add_action_buttons('false', $submit);
    }

}
