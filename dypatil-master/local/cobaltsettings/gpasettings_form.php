<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class gpasettings_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('gpa_settings', 'local_cobaltsettings'));
        $global_ob = global_settings::getInstance();

        $global_ob->school_formelement_condition($mform);

        $category_types = $global_ob->get_category_types(1, true);
        $mform->addElement('select', 'sub_entityid', get_string('category', 'local_cobaltsettings'), $category_types);
        $mform->addRule('sub_entityid', get_string('category', 'local_cobaltsettings'), 'required', null, 'client');
        $mform->setType('sub_entityid', PARAM_INT);

        $mform->addElement('text', 'gpa', get_string('semgpa', 'local_semesters'));
        $mform->addRule('gpa', get_string('misgpa', 'local_semesters'), 'required', null, 'client');
        $mform->addRule('gpa', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->setType('gpa', PARAM_FLOAT);

        $mform->addElement('text', 'cgpa', get_string('semcgpa', 'local_semesters'));
        $mform->addRule('cgpa', get_string('miscgpa', 'local_semesters'), 'required', null, 'client');
        $mform->addRule('cgpa', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->setType('cgpa', PARAM_FLOAT);

        $mform->addElement('text', 'probationgpa', get_string('probationgpa', 'local_cobaltsettings'));
        $mform->addRule('probationgpa', get_string('misprobationgpa', 'local_cobaltsettings'), 'required', null, 'client');
        $mform->addRule('probationgpa', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->setType('probationgpa', PARAM_FLOAT);

        $mform->addElement('text', 'dismissalgpa', get_string('dismissalgpa', 'local_cobaltsettings'));
        $mform->addRule('dismissalgpa', get_string('misdismissalgpa', 'local_cobaltsettings'), 'required', null, 'client');
        $mform->addRule('dismissalgpa', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->setType('dismissalgpa', PARAM_FLOAT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if ($id < 0)
            $save = get_string('addsettings', 'local_cobaltsettings');
        else
            $save = get_string('updatesettings', 'local_cobaltsettings');
        $this->add_action_buttons(true, $save);
    }

    function validation($data, $files) {
        global $CFG, $DB;
        $errors = parent::validation($data, $files);
        if (!empty($data['probationgpa'])) {
            if (($data['gpa'] <= $data['probationgpa']))
                $errors['probationgpa'] = get_string('probationgpa_error', 'local_cobaltsettings');
        }
        if ($data['dismissalgpa'] == $data['probationgpa']) {
            $errors['dismissalgpa'] = get_string('dismissalgpa_error', 'local_cobaltsettings');
        }
        if ($data['gpa'] < 0) {
            $errors['gpa'] = 'Enter Only Positive Values';
        }
        if ($data['cgpa'] < 0) {
            $errors['cgpa'] = 'Enter Only Positive Values';
        }
        if ($data['probationgpa'] < 0) {
            $errors['probationgpa'] = 'Enter Only Positive Values';
        }
        if ($data['dismissalgpa'] < 0) {
            $errors['dismissalgpa'] = 'Enter Only Positive Values';
        }
        if ($data['sub_entityid'] == 0) {
            $errors['sub_entityid'] = 'Required';
        }
        if ($data['id'] > 0) {
            $sql = "SELECT * FROM {local_cobalt_gpasettings} WHERE schoolid={$data['schoolid']} AND sub_entityid={$data['sub_entityid']} AND id!={$data['id']}";
            $query = $DB->get_records_sql($sql);
            $count = count($query);
            if ($count > 0) {
                $errors['sub_entityid'] = get_string('already_sem_gpa', 'local_cobaltsettings');
            }
        }
        if ($data['gpa'] == $data['probationgpa']) {
            $errors['gpa'] = 'semester GPA should be greater than Probation GPA';
        }
        if (($data['dismissalgpa'] > $data['probationgpa']) || ($data['dismissalgpa'] >= $data['gpa']))
            $errors['dismissalgpa'] = get_string('dismissalgpa_error', 'local_cobaltsettings');

        return $errors;
    }

}

?>