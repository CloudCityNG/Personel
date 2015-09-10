<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class admission_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = & $this->_form;
        $PAGE->requires->yui_module('moodle-local_admission-address', 'M.local_admission.init_address', array(array('formid' => $mform->getAttribute('id'))));

        $today = time();
        $date = date('d-M-Y');
        $schoolid = $this->_customdata['schoolid'];
        $programid = $this->_customdata['programid'];
        $ptype = $this->_customdata['ptype'];
        $atype = $this->_customdata['atype'];
        $stype = $this->_customdata['stype'];
        $student = $this->_customdata['previousstudent'];

        if ($programid != 0 || $programid != null) {
            $programname = $DB->get_field('local_program', 'fullname', array('id' => $programid));
        }

        //Name details
        $mform->addElement('header', 'moodle', get_string('nameheading', 'local_admission'));
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_admission'));
        $mform->addRule('firstname', get_string('firstname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('text', 'middlename', get_string('middlename', 'local_admission'));
        $mform->setType('middlename', PARAM_RAW);
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_admission'));
        $mform->addRule('lastname', get_string('lastname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('lastname', PARAM_RAW);
        //Gender details
        $mform->addElement('header', 'moodle', get_string('genderheading', 'local_admission'));
        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('female', 'local_admission'), 'Female');
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('male', 'local_admission'), 'Male');
        $mform->addGroup($radioarray, 'gender', 'Gender', array(' '), false);
        $mform->addRule('gender', get_string('gender_error', 'local_admission'), 'required', null, 'client');
        //Dob details
        $mform->addElement('header', 'moodle', get_string('dobheading', 'local_admission'));
        $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admission'));
        $mform->addHelpButton('dob', 'dob', 'local_admission');
        $mform->setType('dob', PARAM_RAW);
        //Country of Birth
        $mform->addElement('header', 'moodle', get_string('countryheading', 'local_admission'));
        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'birthcountry', get_string('country'), $country);
        $mform->addRule('birthcountry', get_string('missingcountry'), 'required', null, 'server');
        //Place of birth
        $mform->addElement('header', 'moodle', get_string('placeheading', 'local_admission'));
        $mform->addElement('textarea', 'birthplace', get_string('placecountrys', 'local_admission'), ' rows="3" cols="23"');
        $mform->addRule('birthplace', get_string('birthplace_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('birthplace', PARAM_RAW);
        //Current address
        $mform->addElement('header', 'moodle', get_string('addressheading', 'local_admission'));
        $mform->addElement('text', 'fathername', get_string('fathername', 'local_admission'));
        $mform->addRule('fathername', get_string('fathername_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('fathername', PARAM_RAW);
        $mform->addElement('text', 'pob', get_string('pob', 'local_admission'));
        $mform->addRule('pob', get_string('pob_error', 'local_admission'), 'required', null, 'client');

        $mform->setType('pob', PARAM_RAW);
        $mform->addElement('text', 'region', get_string('region', 'local_admission'));
        $mform->addRule('region', get_string('region_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('region', PARAM_RAW);
        $mform->addElement('text', 'town', get_string('town', 'local_admission'));
        $mform->addRule('town', get_string('town_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('town', PARAM_RAW);
        $mform->addElement('text', 'currenthno', get_string('hno', 'local_admission'));
        $mform->addRule('currenthno', get_string('currenthno_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('currenthno', PARAM_RAW);
        $mform->addElement('select', 'currentcountry', get_string('country'), $country);
        $mform->addRule('currentcountry', get_string('country_error', 'local_admission'), 'required', null, 'server');
        //personal details
        $mform->addElement('header', 'moodle', get_string('personalinfo', 'local_admission'));
        $mform->addElement('text', 'phone', get_string('phone', 'local_admission'));
        $mform->addRule('phone', get_string('phone_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('phone', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('phone', get_string('phoneminimum', 'local_admission'), 'minlength', 10, 'client');
        $mform->addRule('phone', get_string('phonemaximum', 'local_admission'), 'maxlength', 15, 'client');
        $mform->setType('phone', PARAM_RAW);
        $mform->addElement('text', 'email', get_string('email', 'local_admission'));
        $mform->addRule('email', get_string('email_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_admission'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);
        $mform->addElement('text', 'howlong', get_string('howlong', 'local_admission'));
        $mform->addRule('howlong', get_string('howlong_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('howlong', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('howlong', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
        $mform->setType('howlong', PARAM_INT);
        $same = array('Select', 'Yes', 'No');
        $mform->addElement('select', 'same', get_string('same', 'local_admission'), $same);
        $mform->addRule('same', get_string('same_error', 'local_admission'), 'required', null, 'client');
        //permanent details
        $mform->addElement('hidden', 'beforeaddress');
        $mform->setType('beforeaddress', PARAM_RAW);
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        //primary school

        $mform->addElement('header', 'moodle', get_string('primaryschool', 'local_admission'));
        $mform->addElement('text', 'primaryschoolname', get_string('primaryschoolname', 'local_admission'));
        $mform->addRule('primaryschoolname', get_string('psname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryschoolname', PARAM_RAW);
        $mform->addElement('text', 'primaryyear', get_string('primaryyear', 'local_admission'));
        $mform->addRule('primaryyear', get_string('py_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('primaryyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('primaryyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
        $mform->addRule('primaryyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
        $mform->setType('primaryyear', PARAM_INT);
        $mform->addElement('text', 'primaryscore', get_string('primaryscore', 'local_admission'));
        $mform->addHelpButton('primaryscore', 'primaryscore', 'local_admission');
        $mform->addRule('primaryscore', get_string('ps_error', 'local_admission'), 'required', null, 'client');
        //$mform->addRule('primaryscore', get_string('numeric','local_admission'), 'numeric', null,'client');
        $mform->addRule('primaryscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^\+?(0|[1-9]\d*)$/', 'client');
        $mform->setType('primaryscore', PARAM_RAW);

        $mform->addElement('text', 'primaryplace', get_string('pnc', 'local_admission'));
        $mform->addRule('primaryplace', get_string('pp_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryplace', PARAM_RAW);
        //undergraduate
        if ($ptype == 2 || $ptype == 3) {
            $mform->addElement('header', 'moodle', get_string('undergraduat', 'local_admission'));
            $mform->addElement('text', 'ugin', get_string('ugin', 'local_admission'));
            $mform->addRule('ugin', get_string('ug_error', 'local_admission'), 'required', null, 'client');
            $mform->setType('ugin', PARAM_RAW);
            $mform->addElement('text', 'ugname', get_string('ugname', 'local_admission'));
            $mform->addRule('ugname', get_string('ugname_error', 'local_admission'), 'required', null, 'client');
            $mform->setType('ugname', PARAM_RAW);
            $mform->addElement('text', 'ugyear', get_string('ugyear', 'local_admission'));
            $mform->addRule('ugyear', get_string('ugy_error', 'local_admission'), 'required', null, 'client');
            $mform->addRule('ugyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
            $mform->addRule('ugyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
            $mform->addRule('ugyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
            $mform->setType('ugyear', PARAM_INT);
            $mform->addElement('text', 'ugscore', get_string('ugscore', 'local_admission'));
            $mform->addHelpButton('ugscore', 'ugscore', 'local_admission');
            $mform->addRule('ugscore', get_string('ugs_error', 'local_admission'), 'required', null, 'client');
            $mform->addRule('ugscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^[0-9]\d*$/', 'client');
            $mform->setType('ugscore', PARAM_INT);
            $mform->addElement('text', 'ugplace', get_string('pnc', 'local_admission'));
            $mform->addRule('ugplace', get_string('ugp_error', 'local_admission'), 'required', null, 'client');
            $mform->setType('ugplace', PARAM_RAW);
        }
        //graduate need to write
        if ($ptype == 3) {
            $mform->addElement('header', 'moodle', get_string('graduatedetails', 'local_admission'));
            $mform->addElement('text', 'graduatein', get_string('graduatein', 'local_admission'));
            $mform->addRule('graduatein', get_string('required'), 'required', null, 'client');
            $mform->setType('graduatein', PARAM_RAW);
            $mform->addElement('text', 'graduatename', get_string('graduatename', 'local_admission'));
            $mform->addRule('graduatename', get_string('required'), 'required', null, 'client');
            $mform->setType('graduatename', PARAM_RAW);
            $mform->addElement('text', 'graduateyear', get_string('graduateyear', 'local_admission'));
            $mform->addRule('graduateyear', get_string('required'), 'required', null, 'client');
            $mform->addRule('graduateyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
            $mform->addRule('graduateyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
            $mform->addRule('graduateyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
            $mform->setType('graduateyear', PARAM_INT);
            $mform->addElement('text', 'graduatescore', get_string('graduatescore', 'local_admission'));
            $mform->addRule('graduatescore', get_string('required'), 'required', null, 'client');
            $mform->addRule('graduatescore', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
            $mform->setType('graduatescore', PARAM_INT);

            $mform->addElement('text', 'graduateplace', get_string('pnc', 'local_admission'));
            $mform->addRule('graduateplace', get_string('required'), 'required', null, 'client');
            $mform->setType('graduateplace', PARAM_RAW);
        }
        //international student
        if ($stype == 2) {
            $mform->addElement('header', 'moodle', get_string('entrance', 'local_admission'));
            $mform->addElement('text', 'examname', get_string('examname', 'local_admission'));
            $mform->addRule('examname', get_string('required'), 'required', null, 'client');
            $mform->setType('examname', PARAM_RAW);
            $mform->addElement('text', 'hallticketno', get_string('hallticketno', 'local_admission'));
            $mform->addRule('hallticketno', get_string('required'), 'required', null, 'client');
            $mform->addRule('hallticketno', get_string('alphanumeric', 'local_admission'), 'alphanumeric', null, 'client');
            $mform->setType('hallticketno', PARAM_RAW);
            $mform->addElement('text', 'score', get_string('score', 'local_admission'));
            $mform->addRule('score', get_string('required'), 'required', null, 'client');
            $mform->addRule('score', get_string('numeric'), 'numeric', null, 'client');
            $mform->setType('score', PARAM_RAW);
        }
        //mature student
        if ($stype == 3) {
            $mform->addElement('header', 'moodle', get_string('details', 'local_admission'));
            $mform->addElement('text', 'noofmonths', get_string('noofyears', 'local_admission'));
            $mform->addRule('noofmonths', get_string('required'), 'required', null, 'client');
            $mform->addRule('noofmonths', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
            $mform->addRule('noofmonths', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
            $mform->setType('noofmonths', PARAM_INT);
            $mform->addElement('text', 'reason', get_string('reason', 'local_admission'));
            $mform->addRule('reason', get_string('required'), 'required', null, 'client');
            $mform->setType('reason', PARAM_RAW);
            $mform->addElement('textarea', 'description', get_string('description', 'local_admission'), ' rows="3" cols="23"');
            $mform->addRule('description', get_string('required'), 'required', null, 'client');
            $mform->setType('description', PARAM_RAW);
        }
        //upload file
        $mform->addElement('header', 'moodle', get_string('fileheading', 'local_admission'));
        $help = $OUTPUT->help_icon('uploadfile', 'local_admission', get_string('file', 'local_admission'));
        
        //@ $mform->addElement('file', 'uploadfile', get_string('uploadfile', 'local_admission') . $help);
        //
        //$mform->setType('uploadfile', PARAM_RAW);
        //$mform->setType('MAX_FILE_SIZE', PARAM_RAW);
        //$mform->addRule('uploadfile', get_string('file_error', 'local_admission'), 'required', null, 'client');
        
              $filemanageroptions=   array(
             'maxfiles' =>2,        
            'subdirs' => 0,
            'accepted_types' => '*'
        );
        $mform->addElement('filemanager', 'uploadfile', get_string('uploadfile', 'local_admission'), null,   $filemanageroptions);
        
        

        $mform->addElement('hidden', 'schoolid', $schoolid);
        $mform->setType('schoolid', PARAM_INT);
        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);
        $mform->addElement('hidden', 'typeofprogram', $ptype);
        $mform->setType('typeofprogram', PARAM_INT);
        $mform->addElement('hidden', 'typeofapplication', $atype);
        $mform->setType('typeofapplication', PARAM_INT);

        $mform->addElement('hidden', 'typeofstudent', $stype);
        $mform->setType('typeofstudent', PARAM_INT);

        $mform->addElement('hidden', 'dateofapplication', $today);
        $mform->setType('dateofapplication', PARAM_INT);
        $mform->addElement('hidden', 'previousstudent', $student);
        $mform->setType('previousstudent', PARAM_INT);
        $payment = "SELECT * FROM {local_school_settings} WHERE schoolid={$schoolid} AND name='onlinepayment' AND value=1";
        $chkpayment = $DB->get_records_sql($payment);
        if (!empty($chkpayment)) {
            $paymentarray = array();
            $paymentarray[] = & $mform->createElement('radio', 'fundsbygovt', '', get_string('declaration_bygovt', 'local_admission'), '1');
            $paymentarray[] = & $mform->createElement('radio', 'fundsbygovt', '', get_string('declaration_notbygovt', 'local_admission'), '0');
            $mform->addGroup($paymentarray, 'fundsbygovt', 'Funds By Govt', array(' '), false);
            $mform->addRule('fundsbygovt', get_string('required'), 'required', null, 'client');
        }
        $this->add_action_buttons('false', 'Submit');
    }

    function definition_after_data() {
        global $DB, $CFG;

        $mform = $this->_form;
        $sid = $mform->getElementValue('same');
        if (isset($sid) && !empty($sid) && $sid[0] == 2) {
            $country = get_string_manager()->get_list_of_countries();
            $default_country[''] = get_string('selectacountry');
            $country = array_merge($default_country, $country);
            $one = $mform->createElement('select', 'pcountry', get_string('pcountry', 'local_admission'), $country);
            $mform->insertElementBefore($one, 'beforeaddress');
            $mform->addRule('pcountry', get_string('missingcountry'), 'required', null, 'server');

            $seven = $mform->createElement('text', 'permanenthno', get_string('hno', 'local_admission'));
            $mform->insertElementBefore($seven, 'pcountry');
            $mform->addRule('permanenthno', get_string('required'), 'required', null, 'client');
            $mform->setType('permanenthno', PARAM_RAW);

            $two = $mform->createElement('text', 'state', get_string('state', 'local_admission'));
            $mform->insertElementBefore($two, 'permanenthno');
            $mform->addRule('state', get_string('required'), 'required', null, 'client');
            $mform->setType('state', PARAM_RAW);
            $three = $mform->createElement('text', 'city', get_string('city', 'local_admission'));
            $mform->insertElementBefore($three, 'state');
            $mform->addRule('city', get_string('required'), 'required', null, 'client');
            $mform->setType('city', PARAM_RAW);
            $four = $mform->createElement('text', 'pincode', get_string('pincode', 'local_admission'));
            $mform->insertElementBefore($four, 'city');
            $mform->addRule('pincode', get_string('required'), 'required', null, 'client');
            $mform->setType('pincode', PARAM_RAW);
            $five = $mform->createElement('text', 'contactname', get_string('contactname', 'local_admission'));
            $mform->insertElementBefore($five, 'pincode');
            $mform->addRule('contactname', get_string('required'), 'required', null, 'client');
            $mform->setType('contactname', PARAM_RAW);

            $six = $mform->createElement('header', 'moodle', get_string('fulladdressheading', 'local_admission'));
            $mform->insertElementBefore($six, 'contactname');
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        $currentyear = date("Y");
        $dob = date('Y-m-d', $data['dob']);
        $dob_array = explode("-", $dob);
        $years = $currentyear - $dob_array[0];
        if ($dob_array[0] > $currentyear) {
            $errors['dob'] = 'Enter Valid Date of Birth';
        }
        if ($years < $data['howlong']) {
            $errors['howlong'] = 'Enter Valid Years';
        }
        if ($data['primaryyear'] > $currentyear) {
            $errors['primaryyear'] = 'Primary year cannot be greater than current year';
        }
        if ($data['primaryyear'] < $dob_array[0]) {
            $errors['primaryyear'] = 'Primary year cannot be less than Date of birth';
        }

        if (isset($data['ugyear']) && ($data['ugyear'] > $currentyear)) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Greaterthan Currentyear';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] < $dob_array[0])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] == $data['primaryyear'])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Equalto Primaryyear';
        }
        if (isset($data['ugyear']) && ($data['primaryyear'] > $data['ugyear'])) {
            $errors['ugyear'] = 'Primaryyear Shold Not Greaterthan Undergraduateyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] > $currentyear)) {
            $errors['graduateyear'] = 'Graduateyear Shold Not Greaterthan Currentyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] < $dob_array[0])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['graduateyear']) && (($data['primaryyear'] > $data['ugyear']) || ($data['ugyear'] > $data['graduateyear']))) {
            $errors['graduateyear'] = 'Enter Valid Passedoutyear values';
        }

        if (isset($data['graduateyear']) && ($data['graduateyear'] < $data['primaryyear'])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan primaryyear ';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] == $data['ugyear'])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Equalto Undergraduateyear ';
        }
        $sql = "select id from {local_admission} where email='{$data['email']}' and status!=2";
        $query = $DB->get_records_sql($sql);


        if (!empty($query)) {
            $errors['email'] = 'Email id already exists';
        }

        if ($data['same'] == 0) {
            $errors['same'] = 'Select';
        }
        /*if (isset($data['pob'])) {
            $value = numeric_validation($data['pob']);
            if ($value == 0) {
                $errors['pob'] = 'Enter Valid value';
            }
        }
        if (isset($data['pincode'])) {
            $value = numeric_validation($data['pincode']);
            if ($value == 0) {
                $errors['pincode'] = 'Enter Valid value';
            }
        }*/
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pob']);
		if (!empty($result)) 
		$errors['pob']='Special characters other than - / : are not allowed';
		
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pincode']);
		if (!empty($result)) 
		$errors['pincode']='Special characters other than - / : are not allowed';
		
        return $errors;
    }

}

class apply_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        $schoolid = $this->_customdata['schoolid'];
        $programid = $this->_customdata['programid'];
        $typeofprogram = $this->_customdata['ptype'];
        $admission = cobalt_admission::get_instance();
        $typeofapplication = $admission->type;
        $typeofstudent = $admission->level;
        $student = $admission->student;
        $mform->addElement('select', 'typeofapplication', get_string('admissiontype', 'local_admission'), $typeofapplication);
        $mform->addHelpButton('typeofapplication', 'typeofapplication', 'local_admission');
        $mform->addRule('typeofapplication', get_string('required'), 'required', null, 'client');
        $mform->addElement('select', 'typeofstudent', get_string('studenttype', 'local_admission'), $typeofstudent);
        $mform->addHelpButton('typeofstudent', 'typeofstudent', 'local_admission');
        $mform->addRule('typeofstudent', get_string('required'), 'required', null, 'client');
        $mform->addElement('select', 'previousstudent', get_string('previousstudent', 'local_admission'), $student);

        $mform->addRule('previousstudent', get_string('required'), 'required', null, 'client');

        $mform->addElement('hidden', 'schoolid', $schoolid);
        $mform->setType('schoolid', PARAM_INT);
        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);
        $mform->addElement('hidden', 'typeofprogram', $typeofprogram);
        $mform->setType('typeofprogram', PARAM_INT);
        $this->add_action_buttons('false', 'Submit');
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        if ($data['typeofapplication'] == 0) {
            $errors['typeofapplication'] = 'Select admission type';
        }
        if ($data['typeofstudent'] == 0) {
            $errors['typeofstudent'] = 'Select student type';
        }
        if ($data['previousstudent'] == 0) {
            $errors['previousstudent'] = 'Select';
        }
        return $errors;
    }

}

class applicationstatus_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        $mform->addElement('header', 'moodle', get_string('applicationheading', 'local_admission'));
        $mform->addElement('text', 'applicationid', get_string('applicationid', 'local_admission'));
        $mform->addHelpButton('applicationid', 'applicationid', 'local_admission');
        $mform->addRule('applicationid', get_string('required'), 'required', null, 'client');
        $mform->setType('applicationid', PARAM_RAW);
        $this->add_action_buttons('false', 'Submit');
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        $sql = "SELECT * FROM {local_admission} WHERE applicationid='{$data['applicationid']}'";
        $query = $DB->get_records_sql($sql);
        if (empty($query)) {
            $errors['applicationid'] = 'Invalid Application Id';
        }
        return $errors;
    }

}

class contact_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = & $this->_form;
        $id = $this->_customdata['id'];
        $applicant = $DB->get_record('local_admission', array('id' => $id));



        $class = 'class="box";style="border:none";readonly="readonly";';

        $mform->addElement('header', 'moodle', get_string('contactapplicant', 'local_admission'));
        if ($id > 0) {
            $mform->addElement('static', 'fullname', get_string('fullname', 'local_admission'), $applicant->firstname);
            $mform->setType('fullname', PARAM_RAW);
            $mform->addElement('static', 'email', get_string('email', 'local_admission'), $applicant->email);
            $mform->setType('email', PARAM_RAW);
        }
        $mform->addElement('text', 'subject', get_string('subject', 'local_admission'));
        $mform->addRule('subject', get_string('required'), 'required', null, 'client');
        $mform->setType('subject', PARAM_RAW);

        $mform->addElement('textarea', 'message', get_string("message", "local_admission"), ' rows="3" cols="23"');
        $mform->addRule('message', get_string('required'), 'required', null, 'client');
        $mform->setType('message', PARAM_RAW);
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons('false', 'Contact');
    }

}

class view_form extends moodleform {

    public function definition() {
        global $CFG, $DB;

        $mform = & $this->_form;
        $id = $this->_customdata['id'];
        $applicant = $DB->get_record('local_admission', array('id' => $id));

        $date = date('d/M/Y', $applicant->dateofapplication);
        $class = 'class="box";style="border:none";readonly="readonly";';

        $schoolname = $DB->get_field('local_school', 'fullname', array('id' => $applicant->schoolid));
        $programname = $DB->get_field('local_program', 'fullname', array('id' => $applicant->programid));
        //pgm details
        $mform->addElement('header', 'moodle', get_string('pgmheading', 'local_collegestructure'));
        $mform->addElement('static', 'schoolname', get_string('schoolid', 'local_collegestructure') . ' ' . '<b>:</b>', $schoolname);
        $mform->setType('schoolname', PARAM_RAW);
        $mform->addElement('static', 'programname', get_string('programname', 'local_programs') . ' ' . '<b>:</b>', $programname);
        $mform->setType('programname', PARAM_RAW);
        $atype = admission_type($applicant->typeofapplication);
        $stype = student_type($applicant->typeofstudent);
        $mform->addElement('static', 'typeofapplication', get_string('admissiontype', 'local_admission') . ' ' . '<b>:</b>', $atype);
        $mform->setType('typeofapplication', PARAM_RAW);
        $mform->addElement('static', 'typeofstudent', get_string('studenttype', 'local_admission') . ' ' . '<b>:</b>', $stype);
        $mform->setType('typeofstudent', PARAM_RAW);



        $mform->addElement('static', 'dateofapplication', get_string('doa', 'local_admission') . ' ' . '<b>:</b>', $date);
        $mform->setType('dateofapplication', PARAM_RAW);
        //code for name
        $mform->addElement('header', 'moodle', get_string('nameheading', 'local_admission'));
        $mform->addElement('static', 'firstname', get_string('firstname', 'local_admission') . ' ' . '<b>:</b>', $applicant->firstname);
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('static', 'middlename', get_string('middlename', 'local_admission') . ' ' . '<b>:</b>', $applicant->middlename);

        $mform->setType('middlename', PARAM_RAW);
        $mform->addElement('static', 'lastname', get_string('lastname', 'local_admission') . ' ' . '<b>:</b>', $applicant->lastname);

        $mform->setType('lastname', PARAM_RAW);

        //code for gender
        $mform->addElement('header', 'moodle', get_string('genderheading', 'local_admission'));
        $mform->addElement('static', 'gender', get_string('genderheading', 'local_admission') . ' ' . '<b>:</b>', $applicant->gender);

        $mform->setType('gender', PARAM_RAW);
        //dob
        $mform->addElement('header', 'moodle', get_string('dobheading', 'local_admission'));
        $mform->addElement('static', 'dob', get_string('dob', 'local_admission') . ' ' . '<b>:</b>', date('M-d-Y', $applicant->dob));

        $mform->setType('dob', PARAM_RAW);
        //country of birth
        $mform->addElement('header', 'moodle', get_string('countryheading', 'local_admission'));
        $mform->addElement('static', 'country', get_string('country') . ' ' . '<b>:</b>', get_string('' . $applicant->birthcountry . '', 'countries'));
        $mform->setType('country', PARAM_RAW);
        //place of birth
        $mform->addElement('header', 'moodle', get_string('placeheading', 'local_admission'));
        $mform->addElement('static', 'birthplace', get_string("placecountrys", "local_admission") . ' ' . '<b>:</b>', $applicant->birthplace);

        $mform->setType('birthplace', PARAM_RAW);
        //code for current address
        $mform->addElement('header', 'moodle', get_string('addressheading', 'local_admission'));

        $mform->addElement('static', 'fathername', get_string("fathername", "local_admission") . ' ' . '<b>:</b>', $applicant->fathername);

        $mform->setType('fathername', PARAM_RAW);
		
        $mform->addElement('static', 'pob', get_string("pob", "local_admission") . ' ' . '<b>:</b>', $applicant->pob);
        $mform->setType('pob', PARAM_RAW);
 
        $mform->addElement('static', 'region', get_string("region", "local_admission") . ' ' . '<b>:</b>', $applicant->region);

        $mform->setType('region', PARAM_RAW);
        $mform->addElement('static', 'town', get_string("town", "local_admission") . ' ' . '<b>:</b>', $applicant->town);

        $mform->setType('town', PARAM_RAW);
        $mform->addElement('static', 'currenthno', get_string("hno", "local_admission") . ' ' . '<b>:</b>', $applicant->currenthno);

        $mform->setType('currenthno', PARAM_RAW);
        $mform->addElement('static', 'currentcountry', get_string("country") . ' ' . '<b>:</b>', get_string('' . $applicant->currentcountry . '', 'countries'));

        $mform->setType('currentcountry', PARAM_RAW);
        //personal details
        $mform->addElement('header', 'moodle', get_string('personalinfo', 'local_admission'));
        $mform->addElement('static', 'phone', get_string("phone", "local_admission") . ' ' . '<b>:</b>', $applicant->phone);

        $mform->setType('phone', PARAM_RAW);
        $mform->addElement('static', 'email', get_string("email", "local_admission") . ' ' . '<b>:</b>', $applicant->email);

        $mform->setType('email', PARAM_RAW);
        $mform->addElement('static', 'howlong', get_string("howlong", "local_admission") . ' ' . '<b>:</b>', $applicant->howlong);

        $mform->setType('howlong', PARAM_RAW);
        //pertmanent address
        $mform->addElement('header', 'moodle', get_string('fulladdressheading', 'local_admission'));
        $mform->addElement('static', 'contactname', get_string("contactname", "local_admission") . ' ' . '<b>:</b>', $applicant->contactname);

        $mform->setType('contactname', PARAM_RAW);
        $mform->addElement('static', 'pincode', get_string("pincode", "local_admission") . ' ' . '<b>:</b>', $applicant->pincode);

        $mform->setType('pincode', PARAM_RAW);
        $mform->addElement('static', 'city', get_string("city", "local_admission") . ' ' . '<b>:</b>', $applicant->city);

        $mform->setType('city', PARAM_RAW);
        $mform->addElement('static', 'state', get_string("state", "local_admission") . ' ' . '<b>:</b>', $applicant->state);

        $mform->setType('state', PARAM_RAW);
        $mform->addElement('static', 'permanenthno', get_string("hno", "local_admission") . ' ' . '<b>:</b>', $applicant->permanenthno);

        $mform->setType('permanenthno', PARAM_RAW);
        $mform->addElement('static', 'pcountry', get_string("pcountry", "local_admission") . ' ' . '<b>:</b>', get_string('' . $applicant->pcountry . '', 'countries'));

        $mform->setType('pcountry', PARAM_RAW);
        //primary school
        $mform->addElement('header', 'moodle', get_string('primaryschool', 'local_admission'));
        $mform->addElement('static', 'primaryschoolname', get_string("primaryschoolname", "local_admission") . ' ' . '<b>:</b>', $applicant->primaryschoolname);

        $mform->setType('primaryschoolname', PARAM_RAW);
        $mform->addElement('static', 'primaryyear', get_string("primaryyear", "local_admission") . ' ' . '<b>:</b>', $applicant->primaryyear);

        $mform->setType('primaryyear', PARAM_RAW);
        $mform->addElement('static', 'primaryscore', get_string("primaryscore", "local_admission") . ' ' . '<b>:</b>', $applicant->primaryscore);

        $mform->setType('primaryscore', PARAM_RAW);

        $mform->addElement('static', 'primaryplace', get_string("pnc", "local_admission") . ' ' . '<b>:</b>', $applicant->primaryplace);
        $mform->setType('primaryplace', PARAM_RAW);
        //undergraduation

        if ($applicant->typeofprogram == 2 || $applicant->typeofprogram == 3) {
            $mform->addElement('header', 'moodle', get_string('undergraduat', 'local_admission'));
            $mform->addElement('static', 'ugin', get_string("ugin", "local_admission") . ' ' . '<b>:</b>', $applicant->ugin);

            $mform->setType('ugin', PARAM_RAW);
            $mform->addElement('static', 'ugname', get_string("ugname", "local_admission") . ' ' . '<b>:</b>', $applicant->ugname);

            $mform->setType('ugname', PARAM_RAW);
            $mform->addElement('static', 'ugyear', get_string("ugyear", "local_admission") . ' ' . '<b>:</b>', $applicant->ugyear);

            $mform->setType('ugyear', PARAM_RAW);
            $mform->addElement('static', 'ugscore', get_string("ugscore", "local_admission") . ' ' . '<b>:</b>', $applicant->ugscore);

            $mform->setType('ugscore', PARAM_RAW);

            $mform->addElement('static', 'ugplace', get_string("pnc", "local_admission") . ' ' . '<b>:</b>', $applicant->ugplace);
            $mform->setType('ugplace', PARAM_RAW);
        }
        //graduation
        if ($applicant->typeofprogram == 3) {
            $mform->addElement('header', 'moodle', get_string('graduatedetails', 'local_admission'));


            $mform->addElement('static', 'graduatein', get_string("graduatein", "local_admission") . ' ' . '<b>:</b>', $applicant->graduatein);

            $mform->setType('graduatein', PARAM_RAW);
            $mform->addElement('static', 'graduatename', get_string("graduatename", "local_admission") . ' ' . '<b>:</b>', $applicant->graduatename);

            $mform->setType('graduatename', PARAM_RAW);
            $mform->addElement('static', 'graduateyear', get_string("graduateyear", "local_admission") . ' ' . '<b>:</b>', $applicant->graduateyear);

            $mform->setType('graduateyear', PARAM_RAW);
            $mform->addElement('static', 'graduatescore', get_string("graduatescore", "local_admission") . ' ' . '<b>:</b>', $applicant->graduatescore);

            $mform->setType('graduatescore', PARAM_RAW);

            $mform->addElement('static', 'graduateplace', get_string("pnc", "local_admission") . ' ' . '<b>:</b>', $applicant->graduateplace);
            $mform->setType('graduateplace', PARAM_RAW);
        }

        if ($applicant->typeofstudent == 2) {
            $mform->addElement('static', 'examname', get_string("examname", "local_admission") . ' ' . '<b>:</b>', $applicant->examname);

            $mform->setType('examname', PARAM_RAW);
            $mform->addElement('static', 'hallticketno', get_string("hallticketno", "local_admission") . ' ' . '<b>:</b>', $applicant->hallticketno);

            $mform->setType('hallticketno', PARAM_RAW);
            $mform->addElement('static', 'score', get_string("score", "local_admission") . ' ' . '<b>:</b>', $applicant->score);

            $mform->setType('score', PARAM_RAW);
        }
        if ($applicant->typeofstudent == 3) {
            $mform->addElement('static', 'noofmonths', get_string("noofyears", "local_admission") . ' ' . '<b>:</b>', $applicant->noofmonths);

            $mform->setType('noofmonths', PARAM_RAW);
            $mform->addElement('static', 'reason', get_string("reason", "local_admission") . ' ' . '<b>:</b>', $applicant->reason);

            $mform->setType('reason', PARAM_RAW);
            $mform->addElement('static', 'description', get_string("description", "local_admission") . ' ' . '<b>:</b>', $applicant->description);

            $mform->setType('description', PARAM_RAW);
        }


        $mform->addElement('html', '<a href="viewapplicant.php"><input type="button" value="Back"   style="margin-left:165px;
        margin-top:23px;width:63px;"></a>');
    }

}

class readmission_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        $today = time();
        $date = date('d-M-Y');
        $schoolid = $this->_customdata['schoolid'];
        $programid = $this->_customdata['programid'];
        $ptype = $this->_customdata['ptype'];
        $atype = $this->_customdata['atype'];
        $stype = $this->_customdata['stype'];
        $student = $this->_customdata['previousstudent'];
        $mform->addElement('text', 'serviceid', get_string('serviceid', 'local_admission'));
        $mform->addRule('serviceid', get_string('required'), 'required', null, 'client');
        $mform->setType('serviceid', PARAM_RAW);

        $mform->addElement('hidden', 'schoolid', $schoolid);
        $mform->setType('schoolid', PARAM_INT);
        $mform->addElement('hidden', 'programid', $programid);
        $mform->setType('programid', PARAM_INT);
        $mform->addElement('hidden', 'typeofprogram', $ptype);
        $mform->setType('typeofprogram', PARAM_INT);
        $mform->addElement('hidden', 'typeofapplication', $atype);
        $mform->setType('typeofapplication', PARAM_INT);
        $mform->addElement('hidden', 'typeofstudent', $stype);
        $mform->setType('typeofstudent', PARAM_INT);
        $mform->addElement('hidden', 'dateofapplication', $today);
        $mform->setType('dateofapplication', PARAM_INT);
        $mform->addElement('hidden', 'previousstudent', $student);
        $mform->setType('previousstudent', PARAM_INT);

        $this->add_action_buttons('false', 'Submit');
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;

        $sql = "SELECT * FROM {local_userdata} WHERE serviceid='{$data['serviceid']}' ";

        $query = $DB->get_records_sql($sql);
        if (empty($query)) {
            $errors['serviceid'] = 'Invalid Serviceid Id';
        }
        return $errors;
    }

}

class transfer_applicant_approve extends moodleform {

    public function definition() {
        global $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
        $cid = $this->_customdata['curs'];
        $id = $this->_customdata['id'];
        $flag = $this->_customdata['flag'];
        $courses = transfer_get_cobalt_courses($cid);
        $default = 1;
        $repeatarray = array();
        $repeatedoptions = array();
        $i = 0;
        $help = $OUTPUT->help_icon('gradehelp', 'local_admission', get_string('file', 'local_admission'));
        $repeatarray[] = $mform->createElement('select', 'courseid', get_string('course', 'local_admission'), $courses);

        $repeatarray[] = $mform->createElement('text', 'grade', get_string('grade', 'local_admission') . $help, array('size' => 4));

        @ $nextel = $this->repeat_elements($repeatarray, $default, null, 'boundary_repeats', 'boundary_add_fields', 1, get_string('add'), true);

        $mform->addElement('hidden', 'curs', $cid);
        $mform->setType('curs', PARAM_RAW);
        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_RAW);
        $mform->addElement('hidden', 'flag', $flag);
        $mform->setType('flag', PARAM_RAW);
        $this->add_action_buttons('false', 'Submit');
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        foreach ($data['courseid'] as $course => $key) {
            if ($key == 0) {
                $errors['courseid[' . $course . ']'] = 'Select Course';
            }
        }
        foreach ($data['grade'] as $grade => $key) {

            if ($key == '' || !is_numeric($key)) {
                $errors['grade[' . $grade . ']'] = 'Enter Grade';
            }
        }
        return $errors;
    }

}

class uploaduser_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = & $this->_form;
        $today = time();
        $date = date('d-M-Y');
        $PAGE->requires->yui_module('moodle-local_admission-school', 'M.local_admission.init_school', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_admission-program', 'M.local_admission.init_program', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_admission-address', 'M.local_admission.init_address', array(array('formid' => $mform->getAttribute('id'))));
        $admission = cobalt_admission::get_instance();
        $pgm = $admission->pgm;
        $typeofstudent = $admission->level;
        $mform->addElement('header', 'moodle', get_string('details', 'local_admission'));
        $mform->addElement('select', 'typeofprogram', get_string('programlevel', 'local_programs'), $pgm);
        $mform->addRule('typeofprogram', get_string('typeofprogram_error', 'local_programs'), 'required', null, 'client');
        $mform->addElement('hidden', 'beforeschool');
        $mform->setType('beforeschool', PARAM_RAW);
        $mform->addElement('hidden', 'beforeprogram');
        $mform->setType('beforeprogram', PARAM_RAW);
        $mform->addElement('select', 'typeofstudent', get_string('studenttype', 'local_admission'), $typeofstudent);
        $mform->addHelpButton('typeofstudent', 'typeofstudent', 'local_admission');
        $mform->addRule('typeofstudent', get_string('required'), 'required', null, 'client');
        $mform->addElement('hidden', 'beforestudent');
        $mform->setType('beforestudent', PARAM_RAW);
        //Name details
        $mform->addElement('header', 'moodle', get_string('nameheading', 'local_admission'));
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_admission'));
        $mform->addRule('firstname', get_string('firstname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('text', 'middlename', get_string('middlename', 'local_admission'));
        $mform->setType('middlename', PARAM_RAW);
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_admission'));
        $mform->addRule('lastname', get_string('lastname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('lastname', PARAM_RAW);
        //Gender details
        $mform->addElement('header', 'moodle', get_string('genderheading', 'local_admission'));
        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('female', 'local_admission'), 'Female');
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('male', 'local_admission'), 'Male');
        $mform->addGroup($radioarray, 'gender', 'Gender', array(' '), false);
        $mform->addRule('gender', get_string('gender_error', 'local_admission'), 'required', null, 'client');
        //Dob details
        $mform->addElement('header', 'moodle', get_string('dobheading', 'local_admission'));
        $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admission'));
        $mform->addHelpButton('dob', 'dob', 'local_admission');
        $mform->setType('dob', PARAM_RAW);
        //Country of Birth
        $mform->addElement('header', 'moodle', get_string('countryheading', 'local_admission'));
        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'birthcountry', get_string('country'), $country);
        $mform->addRule('birthcountry', get_string('missingcountry'), 'required', null, 'server');
        //Place of birth
        $mform->addElement('header', 'moodle', get_string('placeheading', 'local_admission'));
        $mform->addElement('textarea', 'birthplace', get_string('placecountrys', 'local_admission'), ' rows="3" cols="23"');
        $mform->addRule('birthplace', get_string('birthplace_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('birthplace', PARAM_RAW);
        //Current address
        $mform->addElement('header', 'moodle', get_string('addressheading', 'local_admission'));
        $mform->addElement('text', 'fathername', get_string('fathername', 'local_admission'));
        $mform->addRule('fathername', get_string('fathername_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('fathername', PARAM_RAW);
        $mform->addElement('text', 'pob', get_string('pob', 'local_admission'));
        $mform->addRule('pob', get_string('pob_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('pob', PARAM_RAW);
		
        $mform->addElement('text', 'region', get_string('region', 'local_admission'));
        $mform->addRule('region', get_string('region_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('region', PARAM_RAW);
		
        $mform->addElement('text', 'town', get_string('town', 'local_admission'));
        $mform->addRule('town', get_string('town_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('town', PARAM_RAW);
		
        $mform->addElement('text', 'currenthno', get_string('hno', 'local_admission'));
        $mform->addRule('currenthno', get_string('currenthno_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('currenthno', PARAM_RAW);
		
        $mform->addElement('select', 'currentcountry', get_string('country'), $country);
        $mform->addRule('currentcountry', get_string('country_error', 'local_admission'), 'required', null, 'server');
		
        //personal details
        $mform->addElement('header', 'moodle', get_string('personalinfo', 'local_admission'));
        $mform->addElement('text', 'phone', get_string('phone', 'local_admission'));
        $mform->addRule('phone', get_string('phone_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('phone', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('phone', get_string('phoneminimum', 'local_admission'), 'minlength', 10, 'client');
        $mform->addRule('phone', get_string('phonemaximum', 'local_admission'), 'maxlength', 15, 'client');
        $mform->setType('phone', PARAM_RAW);
		
        $mform->addElement('text', 'email', get_string('email', 'local_admission'));
        $mform->addRule('email', get_string('email_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_admission'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);
		
        $mform->addElement('text', 'howlong', get_string('howlong', 'local_admission'));
        $mform->addRule('howlong', get_string('howlong_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('howlong', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('howlong', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
        $mform->setType('howlong', PARAM_INT);
		
        $same = array('Select', 'Yes', 'No');
        $mform->addElement('select', 'same', get_string('same', 'local_admission'), $same);
        $mform->addRule('same', get_string('same_error', 'local_admission'), 'required', null, 'client');
		
        //permanent details
        $mform->addElement('hidden', 'beforeaddress');
        $mform->setType('beforeaddress', PARAM_RAW);
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
		
        $mform->addElement('header', 'moodle', get_string('primaryschool', 'local_admission'));
        $mform->addElement('text', 'primaryschoolname', get_string('primaryschoolname', 'local_admission'));
        $mform->addRule('primaryschoolname', get_string('psname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryschoolname', PARAM_RAW);
		
        $mform->addElement('text', 'primaryyear', get_string('primaryyear', 'local_admission'));
        $mform->addRule('primaryyear', get_string('py_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('primaryyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('primaryyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
        $mform->addRule('primaryyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
        $mform->setType('primaryyear', PARAM_INT);
		
        $mform->addElement('text', 'primaryscore', get_string('primaryscore', 'local_admission'));
        $mform->addHelpButton('primaryscore', 'primaryscore', 'local_admission');
        $mform->addRule('primaryscore', get_string('ps_error', 'local_admission'), 'required', null, 'client');
        //$mform->addRule('primaryscore', get_string('numeric','local_admission'), 'numeric', null,'client');
        $mform->addRule('primaryscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^[0-9]\d*$/', 'client');
        $mform->setType('primaryscore', PARAM_RAW);
		
        $mform->addElement('text', 'primaryplace', get_string('pnc', 'local_admission'));
        $mform->addRule('primaryplace', get_string('pp_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryplace', PARAM_RAW);
		
        $mform->addElement('hidden', 'uploadfiles');
        $mform->setType('uploadfiles', PARAM_RAW);
		
        $mform->addElement('header', 'moodle', get_string('fileheading', 'local_admission'));
		
        $help = $OUTPUT->help_icon('uploadfile', 'local_admission', get_string('file', 'local_admission'));
        //@ $mform->addElement('file', 'uploadfile', get_string('uploadfile', 'local_admission') . $help);
        //$mform->setType('uploadfile', PARAM_RAW);
        //$mform->setType('MAX_FILE_SIZE', PARAM_RAW);
        //$mform->addRule('uploadfile', get_string('file_error', 'local_admission'), 'required', null, 'client');
                 $filemanageroptions=   array(
             'maxfiles' =>2,        
            'subdirs' => 0,
            'accepted_types' => '*'
        );
        $mform->addElement('filemanager', 'uploadfile', get_string('uploadfile', 'local_admission'), null,   $filemanageroptions);
		
        $value = 1;
        $mform->addElement('hidden', 'typeofapplication', $value);
        $mform->setType('typeofapplication', PARAM_RAW);
        $mform->addElement('hidden', 'dateofapplication', $today);
        $mform->setType('dateofapplication', PARAM_INT);
        $mform->addElement('hidden', 'previousstudent', $value);
        $mform->setType('previousstudent', PARAM_RAW);
        $this->add_action_buttons('false', 'Submit');
    }

    function definition_after_data() {
        global $DB, $CFG;
        $mform = $this->_form;
        $student = $mform->getElementValue('typeofstudent');
        if (isset($student) && !empty($student) && $student[0] > 0) {
            if ($student[0] == 2) {
                $score = $mform->createElement('text', 'score', get_string('score', 'local_admission'));
                $mform->insertElementBefore($score, 'beforestudent');
                $mform->addRule('score', get_string('required'), 'required', null, 'client');
                $mform->addRule('score', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->setType('score', PARAM_RAW);
                $halltkt = $mform->createElement('text', 'hallticketno', get_string('hallticketno', 'local_admission'));
                $mform->insertElementBefore($halltkt, 'score');
                $mform->addRule('hallticketno', get_string('required'), 'required', null, 'client');
                $mform->addRule('hallticketno', get_string('alphanumeric', 'local_admission'), 'alphanumeric', null, 'client');
                $mform->setType('hallticketno', PARAM_RAW);
                $exam = $mform->createElement('text', 'examname', get_string('examname', 'local_admission'));
                $mform->insertElementBefore($exam, 'hallticketno');
                $mform->addRule('examname', get_string('required'), 'required', null, 'client');
                $mform->setType('examname', PARAM_RAW);
                $sh = $mform->createElement('header', 'moodle', get_string('entrance', 'local_admission'));
                $mform->insertElementBefore($sh, 'examname');
            }
            if ($student[0] == 3) {
                $desc = $mform->createElement('textarea', 'description', get_string('description', 'local_admission'), ' rows="3" cols="23"');
                $mform->insertElementBefore($desc, 'beforestudent');
                $mform->addRule('description', get_string('required'), 'required', null, 'client');
                $mform->setType('description', PARAM_RAW);
                $reason = $mform->createElement('text', 'reason', get_string('reason', 'local_admission'));
                $mform->insertElementBefore($reason, 'description');
                $mform->addRule('reason', get_string('required'), 'required', null, 'client');
                $mform->setType('reason', PARAM_RAW);
                $noofm = $mform->createElement('text', 'noofmonths', get_string('noofyears', 'local_admission'));
                $mform->insertElementBefore($noofm, 'reason');
                $mform->addRule('noofmonths', get_string('required'), 'required', null, 'client');
                $mform->addRule('noofmonths', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->addRule('noofmonths', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
                $mform->setType('noofmonths', PARAM_INT);
                $gh = $mform->createElement('header', 'moodle', get_string('details', 'local_admission'));
                $mform->insertElementBefore($gh, 'noofmonths');
            }
        }
        $tid = $mform->getElementValue('typeofprogram');
        if (isset($tid) && !empty($tid) && $tid[0] > 0) {
            $program = get_school_program($tid[0]);
            $ones = $mform->createElement('select', 'programid', get_string('programname', 'local_programs'), $program);
            $mform->insertElementBefore($ones, 'beforeschool');
            $mform->addHelpButton('programid', 'programid', 'local_admission');
            if ($tid[0] == 2) {
                $ugp = $mform->createElement('text', 'ugplace', get_string('pnc', 'local_admission'));
                $mform->insertElementBefore($ugp, 'uploadfiles');
                $mform->addRule('ugplace', get_string('ugp_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugplace', PARAM_RAW);
                $ugs = $mform->createElement('text', 'ugscore', get_string('ugscore', 'local_admission'));
                $mform->insertElementBefore($ugs, 'ugplace');
                $mform->addHelpButton('ugscore', 'ugscore', 'local_admission');
                $mform->addRule('ugscore', get_string('ugs_error', 'local_admission'), 'required', null, 'client');
                $mform->addRule('ugscore', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->addRule('ugscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^[0-9]\d*$/', 'client');
                $mform->setType('ugscore', PARAM_INT);
                $ugy = $mform->createElement('text', 'ugyear', get_string('ugyear', 'local_admission'));
                $mform->insertElementBefore($ugy, 'ugscore');
                $mform->addRule('ugyear', get_string('ugy_error', 'local_admission'), 'required', null, 'client');
                $mform->addRule('ugyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->addRule('ugyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
                $mform->addRule('ugyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
                $mform->setType('ugyear', PARAM_INT);
                $ugn = $mform->createElement('text', 'ugname', get_string('ugname', 'local_admission'));
                $mform->insertElementBefore($ugn, 'ugyear');
                $mform->addRule('ugname', get_string('ugname_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugname', PARAM_RAW);
                $ugin = $mform->createElement('text', 'ugin', get_string('ugin', 'local_admission'));
                $mform->insertElementBefore($ugin, 'ugname');
                $mform->addRule('ugin', get_string('ug_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugin', PARAM_RAW);
                $uh = $mform->createElement('header', 'moodle', get_string('undergraduat', 'local_admission'));
                $mform->insertElementBefore($uh, 'ugin');
            }
        }
        if (isset($ones)) {
            $pid = $mform->getElementValue('programid');
            if (isset($pid) && !empty($pid) && $pid[0] > 0) {
                $cur = get_user_cur_admission($pid[0]);
                $twos = $mform->createElement('select', 'curriculumid', get_string('curriculum', 'local_curriculum'), $cur);
                $mform->insertElementBefore($twos, 'beforeprogram');
            }
        }
        $sid = $mform->getElementValue('same');
        if (isset($sid) && !empty($sid) && $sid[0] == 2) {
            $country = get_string_manager()->get_list_of_countries();
            $default_country[''] = get_string('selectacountry');
            $country = array_merge($default_country, $country);
            $one = $mform->createElement('select', 'pcountry', get_string('pcountry', 'local_admission'), $country);
            $mform->insertElementBefore($one, 'beforeaddress');
            $mform->addRule('pcountry', get_string('missingcountry'), 'required', null, 'server');
            $seven = $mform->createElement('text', 'permanenthno', get_string('hno', 'local_admission'));
            $mform->insertElementBefore($seven, 'pcountry');
            $mform->addRule('permanenthno', get_string('required'), 'required', null, 'client');
            $mform->setType('permanenthno', PARAM_RAW);
            $two = $mform->createElement('text', 'state', get_string('state', 'local_admission'));
            $mform->insertElementBefore($two, 'permanenthno');
            $mform->addRule('state', get_string('required'), 'required', null, 'client');
            $mform->setType('state', PARAM_RAW);
            $three = $mform->createElement('text', 'city', get_string('city', 'local_admission'));
            $mform->insertElementBefore($three, 'state');
            $mform->addRule('city', get_string('required'), 'required', null, 'client');
            $mform->setType('city', PARAM_RAW);
            $four = $mform->createElement('text', 'pincode', get_string('pincode', 'local_admission'));
            $mform->insertElementBefore($four, 'city');
            $mform->addRule('pincode', get_string('required'), 'required', null, 'client');
            $mform->setType('pincode', PARAM_RAW);
            $five = $mform->createElement('text', 'contactname', get_string('contactname', 'local_admission'));
            $mform->insertElementBefore($five, 'pincode');
            $mform->addRule('contactname', get_string('required'), 'required', null, 'client');
            $mform->setType('contactname', PARAM_RAW);
            $six = $mform->createElement('header', 'moodle', get_string('fulladdressheading', 'local_admission'));
            $mform->insertElementBefore($six, 'contactname');
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        $currentyear = date("Y");
        $dob = date('Y-m-d', $data['dob']);
        $dob_array = explode("-", $dob);
        $years = $currentyear - $dob_array[0];
        if ($dob_array[0] > $currentyear) {
            $errors['dob'] = 'Enter Valid Date of Birth';
        }
        if ($dob_array[0] >= $data['primaryyear']) {
            $errors['dob'] = get_string('doberror', 'local_admission');
        }
        if ($years < $data['howlong']) {
            $errors['howlong'] = 'Enter Valid Years';
        }
        if ($data['primaryyear'] > $currentyear) {
            $errors['primaryyear'] = 'Primary year cannot be greater than current year';
        }
        if ($data['primaryyear'] < $dob_array[0]) {
            $errors['primaryyear'] = 'Primary year cannot be less than Date of birth';
        }

        if (isset($data['ugyear']) && ($data['ugyear'] > $currentyear)) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Greaterthan Currentyear';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] < $dob_array[0])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] == $data['primaryyear'])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Equalto Primaryyear';
        }
        if (isset($data['ugyear']) && ($data['primaryyear'] > $data['ugyear'])) {
            $errors['ugyear'] = 'Primaryyear Shold Not Greaterthan Undergraduateyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] > $currentyear)) {
            $errors['graduateyear'] = 'Graduateyear Shold Not Greaterthan Currentyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] < $dob_array[0])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['graduateyear']) && (($data['primaryyear'] > $data['ugyear']) || ($data['ugyear'] > $data['graduateyear']))) {
            $errors['graduateyear'] = 'Enter Valid Passedoutyear values';
        }

        if (isset($data['graduateyear']) && ($data['graduateyear'] < $data['primaryyear'])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan primaryyear ';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] == $data['ugyear'])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Equalto Undergraduateyear ';
        }
        $sql = "select id from {local_admission} where email='{$data['email']}' and status!=2";
        $query = $DB->get_records_sql($sql);
        if (!empty($query)) {
            $errors['email'] = 'Email id already exists';
        }

        if ($data['same'] == 0) {
            $errors['same'] = 'Select';
        }
        //if(isset($data['pob'])) {
        //$value=numeric_validation ($data['pob']);
        //if($value==0) {
        //$errors['pob']='Enter Valid value';
        //}
        //}
        /*if (isset($data['pincode'])) {
            $value = numeric_validation($data['pincode']);
            if ($value == 0) {
                $errors['pincode'] = 'Enter Valid value';
            }
        }*/
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pob']);
		if (!empty($result)) 
		$errors['pob']='Special characters other than - / : are not allowed';
		
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pincode']);
		if (!empty($result)) 
		$errors['pincode']='Special characters other than - / : are not allowed';
		
        if ($data['typeofprogram'] == 0) {
            $errors['typeofprogram'] = 'Select Program Type';
        }
        if ($data['programid'] == 0) {
            $errors['programid'] = get_string('missingprogram', 'local_programs');
        }
        if ($data['typeofstudent'] == 0) {
            $errors['typeofstudent'] = 'Select Student Type';
        }
        if ($data['curriculumid'] == 0) {
            $errors['curriculumid'] = get_string('missingcurriculumname', 'local_curriculum');
        }
        return $errors;
    }

}

class newapplicant_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $OUTPUT;
        $mform = & $this->_form;
        $today = time();
        $date = date('d-M-Y');
        $value = 1;
        $PAGE->requires->yui_module('moodle-local_admission-school', 'M.local_admission.init_school', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_admission-student', 'M.local_admission.init_student', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_admission-address', 'M.local_admission.init_address', array(array('formid' => $mform->getAttribute('id'))));
        $admission = cobalt_admission::get_instance();
        $pgm = $admission->pgm;
        $typeofstudent = $admission->level;
        $admissiontype = $admission->type;

        $mform->addElement('header', 'moodle', get_string('details', 'local_admission'));
        $mform->addElement('select', 'typeofprogram', get_string('programlevel', 'local_programs'), $pgm);
        $mform->addRule('typeofprogram', get_string('typeofprogram_error', 'local_programs'), 'required', null, 'client');
        $mform->addElement('select', 'typeofapplication', get_string('typeofapplication', 'local_admission'), $admissiontype);
        $mform->addRule('typeofapplication', get_string('typeofapplication_error', 'local_admission'), 'required', null, 'client');
        $mform->addElement('select', 'typeofstudent', get_string('typeofstudent', 'local_admission'), $typeofstudent);
        $mform->addRule('typeofstudent', get_string('typeofstudent_error', 'local_admission'), 'required', null, 'client');
        $mform->addElement('hidden', 'beforeschool');
        $mform->setType('beforeschool', PARAM_RAW);
//Name details
        $mform->addElement('header', 'moodle', get_string('nameheading', 'local_admission'));
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_admission'));
        $mform->addRule('firstname', get_string('firstname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_RAW);
        $mform->addElement('text', 'middlename', get_string('middlename', 'local_admission'));
        $mform->setType('middlename', PARAM_RAW);
        $mform->addElement('text', 'lastname', get_string('lastname', 'local_admission'));
        $mform->addRule('lastname', get_string('lastname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('lastname', PARAM_RAW);
//Gender details
        $mform->addElement('header', 'moodle', get_string('genderheading', 'local_admission'));
        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('female', 'local_admission'), 'Female');
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('male', 'local_admission'), 'Male');
        $mform->addGroup($radioarray, 'gender', 'Gender', array(' '), false);
        $mform->addRule('gender', get_string('gender_error', 'local_admission'), 'required', null, 'client');
//Dob details
        $mform->addElement('header', 'moodle', get_string('dobheading', 'local_admission'));
        $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admission'));
        $mform->addHelpButton('dob', 'dob', 'local_admission');
        $mform->setType('dob', PARAM_RAW);
//Country of Birth
        $mform->addElement('header', 'moodle', get_string('countryheading', 'local_admission'));
        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'birthcountry', get_string('country'), $country);
        $mform->addRule('birthcountry', get_string('missingcountry'), 'required', null, 'server');
//Place of birth
        $mform->addElement('header', 'moodle', get_string('placeheading', 'local_admission'));
        $mform->addElement('textarea', 'birthplace', get_string('placecountrys', 'local_admission'), 'rows="3" cols="23"');
        $mform->addRule('birthplace', get_string('birthplace_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('birthplace', PARAM_RAW);
//Current address
        $mform->addElement('header', 'moodle', get_string('addressheading', 'local_admission'));
        $mform->addElement('text', 'fathername', get_string('fathername', 'local_admission'));
        $mform->addRule('fathername', get_string('fathername_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('fathername', PARAM_RAW);
        $mform->addElement('text', 'pob', get_string('pob', 'local_admission'));
        $mform->addRule('pob', get_string('pob_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('pob', PARAM_RAW);
        $mform->addElement('text', 'region', get_string('region', 'local_admission'));
        $mform->addRule('region', get_string('region_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('region', PARAM_RAW);
        $mform->addElement('text', 'town', get_string('town', 'local_admission'));
        $mform->addRule('town', get_string('town_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('town', PARAM_RAW);
        $mform->addElement('text', 'currenthno', get_string('hno', 'local_admission'));
        $mform->addRule('currenthno', get_string('currenthno_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('currenthno', PARAM_RAW);
        $mform->addElement('select', 'currentcountry', get_string('country'), $country);
        $mform->addRule('currentcountry', get_string('country_error', 'local_admission'), 'required', null, 'server');
//personal details
        $mform->addElement('header', 'moodle', get_string('personalinfo', 'local_admission'));
        $mform->addElement('text', 'phone', get_string('phone', 'local_admission'));
        $mform->addRule('phone', get_string('phone_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('phone', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('phone', get_string('phoneminimum', 'local_admission'), 'minlength', 10, 'client');
        $mform->addRule('phone', get_string('phonemaximum', 'local_admission'), 'maxlength', 15, 'client');
        $mform->setType('phone', PARAM_RAW);
        $mform->addElement('text', 'email', get_string('email', 'local_admission'));
        $mform->addRule('email', get_string('email_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_admission'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);
        $mform->addElement('text', 'howlong', get_string('howlong', 'local_admission'));
        $mform->addRule('howlong', get_string('howlong_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('howlong', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('howlong', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
        $mform->setType('howlong', PARAM_INT);
        $same = array('Select', 'Yes', 'No');
        $mform->addElement('select', 'same', get_string('same', 'local_admission'), $same);
        $mform->addRule('same', get_string('same_error', 'local_admission'), 'required', null, 'client');
//permanent details
        $mform->addElement('hidden', 'beforeaddress');
        $mform->setType('beforeaddress', PARAM_RAW);
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        $mform->addElement('header', 'moodle', get_string('primaryschool', 'local_admission'));
        $mform->addElement('text', 'primaryschoolname', get_string('primaryschoolname', 'local_admission'));
        $mform->addRule('primaryschoolname', get_string('psname_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryschoolname', PARAM_RAW);
        $mform->addElement('text', 'primaryyear', get_string('primaryyear', 'local_admission'));
        $mform->addRule('primaryyear', get_string('py_error', 'local_admission'), 'required', null, 'client');
        $mform->addRule('primaryyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('primaryyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
        $mform->addRule('primaryyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
        $mform->setType('primaryyear', PARAM_INT);
        $mform->addElement('text', 'primaryscore', get_string('primaryscore', 'local_admission'));
        $mform->addHelpButton('primaryscore', 'primaryscore', 'local_admission');
        $mform->addRule('primaryscore', get_string('ps_error', 'local_admission'), 'required', null, 'client');
//$mform->addRule('primaryscore', get_string('numeric','local_admission'), 'numeric', null,'client');
        $mform->addRule('primaryscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^[0-9]\d*$/', 'client');
        $mform->setType('primaryscore', PARAM_RAW);
        $mform->addElement('text', 'primaryplace', get_string('pnc', 'local_admission'));
        $mform->addRule('primaryplace', get_string('pp_error', 'local_admission'), 'required', null, 'client');
        $mform->setType('primaryplace', PARAM_RAW);
        $mform->addElement('hidden', 'beforestudent');
        $mform->setType('beforestudent', PARAM_RAW);
        $mform->addElement('hidden', 'uploadfiles');
        $mform->setType('uploadfiles', PARAM_RAW);
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        $mform->addElement('header', 'moodle', get_string('fileheading', 'local_admission'));
        
        $help = $OUTPUT->help_icon('uploadfile', 'local_admission', get_string('file', 'local_admission'));
        //@ $mform->addElement('file', 'uploadfile', get_string('uploadfile', 'local_admission') . $help);
        //$mform->setType('uploadfile', PARAM_RAW);
        //$mform->setType('MAX_FILE_SIZE', PARAM_RAW);
        //$mform->addRule('uploadfile', get_string('file_error', 'local_admission'), 'required', null, 'client');
        
         $filemanageroptions=   array(
             'maxfiles' =>2,        
            'subdirs' => 0,
            'accepted_types' => '*'
        );
        $mform->addElement('filemanager', 'uploadfile', get_string('uploadfile', 'local_admission'), null,   $filemanageroptions);
        
        $mform->addElement('hidden', 'dateofapplication', $today);
        $mform->setType('dateofapplication', PARAM_INT);
        $mform->addElement('hidden', 'previousstudent', $value);
        $mform->setType('previousstudent', PARAM_RAW);
        $this->add_action_buttons('false', 'Submit');
    }

    function definition_after_data() {
        global $DB, $CFG;
        $mform = $this->_form;
        $tid = $mform->getElementValue('typeofprogram');
        if (isset($tid) && !empty($tid) && $tid[0] > 0) {
            $program = get_school_program($tid[0]);
            $ones = $mform->createElement('select', 'programid', get_string('programname', 'local_programs'), $program);
            $mform->insertElementBefore($ones, 'beforeschool');
            $mform->addHelpButton('programid', 'programid', 'local_admission');
            if ($tid[0] == 2) {
                $ugp = $mform->createElement('text', 'ugplace', get_string('pnc', 'local_admission'));
                $mform->insertElementBefore($ugp, 'uploadfiles');
                $mform->addRule('ugplace', get_string('ugp_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugplace', PARAM_RAW);
                $ugs = $mform->createElement('text', 'ugscore', get_string('ugscore', 'local_admission'));
                $mform->insertElementBefore($ugs, 'ugplace');
                $mform->addHelpButton('ugscore', 'ugscore', 'local_admission');
                $mform->addRule('ugscore', get_string('ugs_error', 'local_admission'), 'required', null, 'client');
                $mform->addRule('ugscore', get_string('positivenumeric', 'local_admission'), 'regex', '/^[0-9]\d*$/', 'client');
                $mform->setType('ugscore', PARAM_INT);
                $ugy = $mform->createElement('text', 'ugyear', get_string('ugyear', 'local_admission'));
                $mform->insertElementBefore($ugy, 'ugscore');
                $mform->addRule('ugyear', get_string('ugy_error', 'local_admission'), 'required', null, 'client');
                $mform->addRule('ugyear', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->addRule('ugyear', get_string('minimum', 'local_admission'), 'minlength', 4, 'client');
                $mform->addRule('ugyear', get_string('maximum', 'local_admission'), 'maxlength', 4, 'client');
                $mform->setType('ugyear', PARAM_INT);
                $ugn = $mform->createElement('text', 'ugname', get_string('ugname', 'local_admission'));
                $mform->insertElementBefore($ugn, 'ugyear');
                $mform->addRule('ugname', get_string('ugname_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugname', PARAM_RAW);
                $ugin = $mform->createElement('text', 'ugin', get_string('ugin', 'local_admission'));
                $mform->insertElementBefore($ugin, 'ugname');
                $mform->addRule('ugin', get_string('ug_error', 'local_admission'), 'required', null, 'client');
                $mform->setType('ugin', PARAM_RAW);
                $uh = $mform->createElement('header', 'moodle', get_string('undergraduat', 'local_admission'));
                $mform->insertElementBefore($uh, 'ugin');
            }
        }
        $student = $mform->getElementValue('typeofstudent');
        if (isset($student) && !empty($student) && $student[0] > 0) {
            if ($student[0] == 2) {
                $score = $mform->createElement('text', 'score', get_string('score', 'local_admission'));
                $mform->insertElementBefore($score, 'beforestudent');
                $mform->addRule('score', get_string('required'), 'required', null, 'client');
                $mform->addRule('score', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->setType('score', PARAM_RAW);
                $halltkt = $mform->createElement('text', 'hallticketno', get_string('hallticketno', 'local_admission'));
                $mform->insertElementBefore($halltkt, 'score');
                $mform->addRule('hallticketno', get_string('required'), 'required', null, 'client');
                $mform->addRule('hallticketno', get_string('alphanumeric', 'local_admission'), 'alphanumeric', null, 'client');
                $mform->setType('hallticketno', PARAM_RAW);
                $exam = $mform->createElement('text', 'examname', get_string('examname', 'local_admission'));
                $mform->insertElementBefore($exam, 'hallticketno');
                $mform->addRule('examname', get_string('required'), 'required', null, 'client');
                $mform->setType('examname', PARAM_RAW);
                $sh = $mform->createElement('header', 'moodle', get_string('entrance', 'local_admission'));
                $mform->insertElementBefore($sh, 'examname');
            }
            if ($student[0] == 3) {
                $desc = $mform->createElement('textarea', 'description', get_string('description', 'local_admission'), ' rows="3" cols="23"');
                $mform->insertElementBefore($desc, 'beforestudent');
                $mform->addRule('description', get_string('required'), 'required', null, 'client');
                $mform->setType('description', PARAM_RAW);
                $reason = $mform->createElement('text', 'reason', get_string('reason', 'local_admission'));
                $mform->insertElementBefore($reason, 'description');
                $mform->addRule('reason', get_string('required'), 'required', null, 'client');
                $mform->setType('reason', PARAM_RAW);
                $noofm = $mform->createElement('text', 'noofmonths', get_string('noofyears', 'local_admission'));
                $mform->insertElementBefore($noofm, 'reason');
                $mform->addRule('noofmonths', get_string('required'), 'required', null, 'client');
                $mform->addRule('noofmonths', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
                $mform->addRule('noofmonths', get_string('howlongmaximum', 'local_admission'), 'maxlength', 2, 'client');
                $mform->setType('noofmonths', PARAM_INT);
                $gh = $mform->createElement('header', 'moodle', get_string('details', 'local_admission'));
                $mform->insertElementBefore($gh, 'noofmonths');
            }
        }
        $sid = $mform->getElementValue('same');
        if (isset($sid) && !empty($sid) && $sid[0] == 2) {
            $country = get_string_manager()->get_list_of_countries();
            $default_country[''] = get_string('selectacountry');
            $country = array_merge($default_country, $country);
            $one = $mform->createElement('select', 'pcountry', get_string('pcountry', 'local_admission'), $country);
            $mform->insertElementBefore($one, 'beforeaddress');
            $mform->addRule('pcountry', get_string('missingcountry'), 'required', null, 'server');
            $seven = $mform->createElement('text', 'permanenthno', get_string('hno', 'local_admission'));
            $mform->insertElementBefore($seven, 'pcountry');
            $mform->addRule('permanenthno', get_string('required'), 'required', null, 'client');
            $mform->setType('permanenthno', PARAM_RAW);
            $two = $mform->createElement('text', 'state', get_string('state', 'local_admission'));
            $mform->insertElementBefore($two, 'permanenthno');
            $mform->addRule('state', get_string('required'), 'required', null, 'client');
            $mform->setType('state', PARAM_RAW);
            $three = $mform->createElement('text', 'city', get_string('city', 'local_admission'));
            $mform->insertElementBefore($three, 'state');
            $mform->addRule('city', get_string('required'), 'required', null, 'client');
            $mform->setType('city', PARAM_RAW);
            $four = $mform->createElement('text', 'pincode', get_string('pincode', 'local_admission'));
            $mform->insertElementBefore($four, 'city');
            $mform->addRule('pincode', get_string('required'), 'required', null, 'client');
            $mform->setType('pincode', PARAM_RAW);
            $five = $mform->createElement('text', 'contactname', get_string('contactname', 'local_admission'));
            $mform->insertElementBefore($five, 'pincode');
            $mform->addRule('contactname', get_string('required'), 'required', null, 'client');
            $mform->setType('contactname', PARAM_RAW);
            $six = $mform->createElement('header', 'moodle', get_string('fulladdressheading', 'local_admission'));
            $mform->insertElementBefore($six, 'contactname');
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        $currentyear = date("Y");
        $dob = date('Y-m-d', $data['dob']);
        $dob_array = explode("-", $dob);
        $years = $currentyear - $dob_array[0];
        $datediff = $currentyear - 10;
        if ($dob_array[0] > $datediff) {
            $errors['dob'] = 'Enter Valid Date of Birth';
        }
        if ($years < $data['howlong']) {
            $errors['howlong'] = 'Enter Valid Years';
        }
        if ($data['primaryyear'] > $currentyear) {
            $errors['primaryyear'] = 'Primary year cannot be greater than current year';
        }
        if ($data['primaryyear'] < $dob_array[0]) {
            $errors['primaryyear'] = 'Primary year cannot be less than Date of birth';
        }

        if (isset($data['ugyear']) && ($data['ugyear'] > $currentyear)) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Greaterthan Currentyear';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] < $dob_array[0])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['ugyear']) && ($data['ugyear'] == $data['primaryyear'])) {
            $errors['ugyear'] = 'Undergraduateyear Should Not Equalto Primaryyear';
        }
        if (isset($data['ugyear']) && ($data['primaryyear'] > $data['ugyear'])) {
            $errors['ugyear'] = 'Primaryyear Shold Not Greaterthan Undergraduateyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] > $currentyear)) {
            $errors['graduateyear'] = 'Graduateyear Shold Not Greaterthan Currentyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] < $dob_array[0])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan Dateofbirth';
        }
        if (isset($data['graduateyear']) && (($data['primaryyear'] > $data['ugyear']) || ($data['ugyear'] > $data['graduateyear']))) {
            $errors['graduateyear'] = 'Enter Valid Passedoutyear values';
        }

        if (isset($data['graduateyear']) && ($data['graduateyear'] < $data['primaryyear'])) {
            $errors['graduateyear'] = 'Graduateyear Should Not Lessthan primaryyear';
        }
        if (isset($data['graduateyear']) && ($data['graduateyear'] == $data['ugyear'])) {
            $errors['graduateyear'] = 'Undergraduateyear Should Not Greaterthan Currentyear';
        }
        $sql = "select id from {local_admission} where email='{$data['email']}' and status!=2";
        $query = $DB->get_records_sql($sql);
        if (!empty($query)) {
            $errors['email'] = 'Email id already exists';
        }

        if ($data['same'] == 0) {
            $errors['same'] = 'Select';
        }
        /* if(isset($data['pob'])) {
          $value=numeric_validation ($data['pob']);
          if($value==0) {
          $errors['pob']='Enter Valid Value';
          }
          } 
        if (isset($data['pincode'])) {
            $value = numeric_validation($data['pincode']);
            if ($value == 0) {
                $errors['pincode'] = 'Enter Valid Value';
            }
        }*/
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pob']);
		if (!empty($result)) 
		$errors['pob']='Special characters other than - / : are not allowed';
		
		$result = preg_replace("/[\/\-:sA-Za-z0-9 ]/", "", $data['pincode']);
		if (!empty($result)) 
		$errors['pincode']='Special characters other than - / : are not allowed';
		
        if ($data['typeofprogram'] == 0) {
            $errors['typeofprogram'] = 'Select Program Type';
        }
        if ($data['programid'] == 0) {
            $errors['programid'] = 'Select Program';
        }
        if ($data['typeofstudent'] == 0) {
            $errors['typeofstudent'] = 'Select student type';
        }

        return $errors;
    }

}

class transfer_applicant_edit extends moodleform {

    public function definition() {
        global $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;

        $id = $this->_customdata['id'];
        $userid = $this->_customdata['userid'];
        $sid = $this->_customdata['sid'];
        $pid = $this->_customdata['pid'];
        $cid = $this->_customdata['cid'];
        $courses = transfer_get_cobalt_courses($cid);
        $default = $this->_customdata['count'];
        $repeatarray = array();
        $repeatedoptions = array();
        $i = 0;
        $help = $OUTPUT->help_icon('gradehelp', 'local_admission', get_string('file', 'local_admission'));
        $repeatarray[] = $mform->createElement('select', 'courseid', get_string('course', 'local_admission'), $courses);

        $repeatarray[] = $mform->createElement('text', 'grade', get_string('grade', 'local_admission') . $help, array('size' => 4));
        $mform->setType('grade', PARAM_RAW);
        @ $nextel = $this->repeat_elements($repeatarray, $default, null, 'boundary_repeats', 'boundary_add_fields', 1, get_string('add'), true);


        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_RAW);
        $mform->addElement('hidden', 'userid', $userid);
        $mform->setType('userid', PARAM_RAW);

        $mform->addElement('hidden', 'sid', $sid);
        $mform->setType('sid', PARAM_RAW);
        $mform->addElement('hidden', 'pid', $pid);
        $mform->setType('pid', PARAM_RAW);
        $mform->addElement('hidden', 'cid', $cid);
        $mform->setType('cid', PARAM_RAW);
        $this->add_action_buttons('false', 'Submit');
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        foreach ($data['courseid'] as $course => $key) {
            if ($key == 0) {
                $errors['courseid[' . $course . ']'] = 'Select Course';
            }
        }
        foreach ($data['grade'] as $grade => $key) {

            if ($key == '' || !is_numeric($key)) {
                $errors['grade[' . $grade . ']'] = 'Enter Grade';
            }
        }
        return $errors;
    }

}
