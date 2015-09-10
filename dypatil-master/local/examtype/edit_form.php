<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');

class edit_form extends moodleform {

    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $id = optional_param('id', -1, PARAM_INT);
        $hierarchy = new hierarchy();
        $instance = new cobalt_examtype();
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        if ($id > 0)
            $mform->addElement('header', 'settingsheader', get_string('editexamtype', 'local_examtype'));
        else
            $mform->addElement('header', 'settingsheader', get_string('addeditexamtype', 'local_examtype'));
        $items = $hierarchy->get_school_items();
        $school = $hierarchy->get_school_parent($items);
        if ($id < 0) {
            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $mform->setType('schoolid', PARAM_RAW);
        }
        if ($id > 0) {
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'));
            $mform->addElement('hidden', 'schoolid');
            $mform->setType('schoolid', PARAM_RAW);
        }
        $mform->addElement('text', 'examtype', get_string('examtype', 'local_examtype'));
        $mform->addRule('examtype', get_string('examtypereq', 'local_examtype'), 'required', null, 'client');
        $mform->setType('examtype', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_examtype'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if ($id < 0)
            $save = get_string('addeditexamtype', 'local_examtype');
        else
            $save = get_string('updateexamtype', 'local_examtype');
        $this->add_action_buttons(true, $save);
    }
    // end of function definition
    // perform some extra moodle validation
    function validation($data, $files) {
        global $DB, $CFG;
        $id = optional_param('id', -1, PARAM_INT);
        $errors = array();
        $errors = parent::validation($data, $files);
        if ($data['schoolid'] == 0) {
            $errors['schoolid'] = get_string('schoolrequired', 'local_collegestructure');
        }
        if ($data['id'] < 0) {
            $examtypes1 = $DB->get_record('local_examtypes', array('schoolid' => $data['schoolid'], 'examtype' => $data['examtype']));
            $exam1 = core_text::strtolower($examtypes1->examtype);
            $exam2 = core_text::strtolower($data['examtype']);
            if ($exam1 == $exam2) {
                $errors['examtype'] = get_string('examexits', 'local_examtype');
            }
        }

        if ($data['id'] > 0) {
            $exists = $DB->get_field('local_examtypes', 'examtype', array('id' => $id));
            if (!($exists === $data['examtype'] )) {
                $examtypes1 = $DB->get_record('local_examtypes', array('schoolid' => $data['schoolid'], 'examtype' => $data['examtype']));
                $exam1 = core_text::strtolower($examtypes1->examtype);
                $exam2 = core_text::strtolower($data['examtype']);
                if ($exam1 == $exam2) {
                    $errors['examtype'] = get_string('examexits', 'local_examtype');
                }
            }
        }
        return $errors;
    }

// end of function validation
}

// end of class
