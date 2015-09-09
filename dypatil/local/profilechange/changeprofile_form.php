<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class changeprofile_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $USER;

        $mform = & $this->_form;
        $class = 'class="box";style="border:none";readonly="readonly";';
        //code for name
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if (isset($this->_customdata['flag'])) {
            $class = '';
            $mform->addElement('hidden', 'flag', $this->_customdata['flag']);
            $mform->setType('flag', PARAM_INT);
        }
        if (isset($this->_customdata['confirm'])) {
            $class = '';
            $mform->addElement('hidden', 'confirm', $this->_customdata['confirm']);
            $mform->setType('confirm', PARAM_INT);
            $mform->addElement('hidden', 'recid', $this->_customdata['recid']);
            $mform->setType('confirm', PARAM_INT);
        }
        $systemcontext = context_system::instance();
        if (has_capability('local/collegestructure:manage', $systemcontext) || has_capability('local/clclasses:submitgrades', $systemcontext) || has_capability('local/clclasses:approvemystudentclclasses', $systemcontext)) {
            $class = '';
        }
        $mform->addElement('hidden', 'userid');
        $mform->setType('userid', PARAM_INT);
        if (has_capability('local/collegestructure:manage', $systemcontext) || has_capability('local/clclasses:submitgrades', $systemcontext) || has_capability('local/clclasses:approvemystudentclclasses', $systemcontext) && !is_siteadmin()) {
            $mform->addElement('header', 'moodle', get_string('username', 'local_users'));
            $mform->addElement('text', 'username', get_string('username', 'local_users'));
            $mform->addRule('username', get_string('required'), 'required', null, 'client');
            $mform->setType('username', PARAM_RAW);
        }
        $mform->addElement('header', 'name', get_string('name', 'local_request'));
        if (!has_capability('local/collegestructure:manage', $systemcontext)) {
            $mform->addHelpButton('name', 'nameheading', 'local_profilechange');
        }
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_admission'), $class);
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('text', 'middlename', get_string('middlename', 'local_admission'), $class);
        $mform->setType('middlename', PARAM_RAW);
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_admission'), $class);
        $mform->addRule('lastname', get_string('required'), 'required', null, 'client');
        $mform->setType('lastname', PARAM_RAW);
        //Gender details
        $mform->addElement('header', 'moodle', get_string('genderheading', 'local_admission'));
        /*
         * ###Bugreport #185- Profile Request
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Changed the gender group name
         */
        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('female', 'local_admission'), 'female');
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('male', 'local_admission'), 'male');
        $mform->addGroup($radioarray, 'gender', 'Gender', array(' '), false);
        $mform->addRule('gender', get_string('required'), 'required', null, 'client');
        //Dob details
        $mform->addElement('header', 'moodle', get_string('dobheading', 'local_admission'));
        $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admission'));
        $mform->setType('dob', PARAM_RAW);
        //Country of Birth
        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $context = context_user::instance($USER->id);
        if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
            $mform->addElement('header', 'moodle', get_string('countryheading', 'local_admission'));
            $country = get_string_manager()->get_list_of_countries();
            $default_country[''] = get_string('selectacountry');
            $country = array_merge($default_country, $country);
            $mform->addElement('select', 'birthcountry', get_string('country'), $country);
            $mform->addRule('birthcountry', get_string('missingcountry'), 'required', null, 'server');
            //Place of birth
            $mform->addElement('header', 'moodle', get_string('placeheading', 'local_admission'));
            $mform->addElement('textarea', 'birthplace', get_string('placecountrys', 'local_admission'), ' rows="3" cols="23"');
            $mform->addRule('birthplace', get_string('required'), 'required', null, 'client');

            $mform->setType('birthplace', PARAM_RAW);
        }
        //Current address
        $mform->addElement('header', 'moodle', get_string('addressheading', 'local_admission'));
        $mform->addElement('text', 'fathername', get_string('fathername', 'local_admission'));

        $mform->setType('fathername', PARAM_RAW);
        $mform->addElement('text', 'pob', get_string('pob', 'local_admission'));

        $mform->addRule('pob', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
		$mform->addRule('pob', get_string('alphanumeric', 'local_admission'), 'alphanumeric', null, 'client');
        $mform->setType('pob', PARAM_INT);
        $mform->addElement('text', 'region', get_string('region', 'local_admission'));

        $mform->setType('region', PARAM_RAW);
        $mform->addElement('text', 'town', get_string('town', 'local_admission'));

        $mform->setType('town', PARAM_RAW);
        $mform->addElement('text', 'currenthno', get_string('hno', 'local_admission'));

        $mform->setType('currenthno', PARAM_RAW);
        $mform->addElement('select', 'currentcountry', get_string('country'), $country);

        //permanent address
        $context = context_user::instance($USER->id);
        if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
            $mform->addRule('currentcountry', get_string('missingcountry'), 'required', null, 'server');
            $mform->addRule('currenthno', get_string('required'), 'required', null, 'client');
            $mform->addRule('town', get_string('required'), 'required', null, 'client');
            $mform->addRule('region', get_string('required'), 'required', null, 'client');
            $mform->addRule('pob', get_string('required'), 'required', null, 'client');
            $mform->addRule('fathername', get_string('required'), 'required', null, 'client');
            $mform->addElement('header', 'moodle', get_string('fulladdressheading', 'local_admission'));
            $mform->addElement('text', 'contactname', get_string('contactname', 'local_admission'));
            $mform->addRule('contactname', get_string('required'), 'required', null, 'client');
            $mform->setType('contactname', PARAM_RAW);
            $mform->addElement('text', 'pincode', get_string('pincode', 'local_admission'));
            $mform->addRule('pincode', get_string('required'), 'required', null, 'client');
            $mform->setType('pincode', PARAM_RAW);
            $mform->addElement('text', 'city', get_string('city', 'local_admission'));
            $mform->addRule('city', get_string('required'), 'required', null, 'client');
            $mform->setType('city', PARAM_RAW);
            $mform->addElement('text', 'state', get_string('state', 'local_admission'));
            $mform->addRule('state', get_string('required'), 'required', null, 'client');
            $mform->setType('state', PARAM_RAW);
            $mform->addElement('text', 'permanenthno', get_string('hno', 'local_admission'));
            $mform->addRule('permanenthno', get_string('required'), 'required', null, 'client');
            $mform->setType('permanenthno', PARAM_RAW);
            $mform->addElement('select', 'country', get_string('country'), $country);
            $mform->addRule('country', get_string('missingcountry'), 'required', null, 'server');
        }
        // $mform->insertElementBefore($six,'contactname');
        //personal details
        $mform->addElement('header', 'email_phone', get_string('personalinfo', 'local_admission'));
        if (!has_capability('local/collegestructure:manage', $systemcontext)) {
            $mform->addHelpButton('email_phone', 'personalinfo', 'local_profilechange');
        }
        $mform->addElement('text', 'phone1', get_string('phone', 'local_admission'));
        $mform->addRule('phone1', get_string('required'), 'required', null, 'client');
        $mform->addRule('phone1', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('phone1', get_string('phoneminimum', 'local_admission'), 'minlength', 10, 'client');
        $mform->addRule('phone1', get_string('phonemaximum', 'local_admission'), 'maxlength', 15, 'client');
        $mform->setType('phone1', PARAM_RAW);
        $mform->addElement('text', 'email', get_string('email', 'local_admission'), $class);
        $mform->addRule('email', get_string('required'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_admission'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);

        $this->add_action_buttons(true, 'Submit');
    }

	public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pob']);
		if (!empty($result)) 
		$errors['pob']='Special characters other than - / : are not allowed';
		
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pincode']);
		if (!empty($result)) 
		$errors['pincode']='Special characters other than - / : are not allowed';
		return $errors;
	}
}
