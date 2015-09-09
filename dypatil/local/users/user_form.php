<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/users/lib.php');
$hierarchy = new hierarchy();
$myuser = users::getInstance();

class create_user extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy, $myuser;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $filemanageroptions = $this->_customdata['filemanageroptions'];
        $admin = $this->_customdata['admin'];
        $mform->addElement('header', 'moodle', get_string('generaldetails', 'local_users'));

        if (!$admin) {
            $mform->addElement('date_selector', 'dateofapplication', get_string('joiningdate', 'local_users'));
        }

        if (is_siteadmin($USER->id)) {
            $school = $DB->get_records('local_school', array('visible' => 1));
        } else {
            $school = $hierarchy->get_assignedschools();
        }

        $parents = $hierarchy->get_school_parent($school, '', $top = true, $all = false);
        $count = count($school);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);
        if ($id > 0) {
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'));
        //    $mform->addElement('static', 'role_name', get_string('role', 'local_users'));
        } else {
            if ($count == 1) {
                //registrar is assigned to only one school, display as static
                foreach ($school as $scl) {
                    $key = $scl->id;
                    $value = $scl->fullname;
                }
                $mform->addElement('static', 'schools', get_string('schoolid', 'local_collegestructure'), $value);
                $mform->addElement('hidden', 'schoolid', $key);
                $mform->setType('schoolid', PARAM_INT);
            } else {
                $school = $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
                $mform->addHelpButton('schoolid', 'assignschool', 'local_collegestructure');
                $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
            }
        }
            $systemroles = $myuser->systemroles_custom();
            $mform->addElement('select', 'roleid', get_string('selectrole', 'local_users'), $systemroles);
            $mform->addHelpButton('roleid', 'assignrole', 'local_users');
            $mform->addRule('roleid', get_string('required'), 'required', null, 'client');
        

        $mform->addElement('text', 'username', get_string('username', 'local_users'));
        $mform->addRule('username', get_string('required'), 'required', null, 'client');
        $mform->setType('username', PARAM_RAW);

        if (!empty($CFG->passwordpolicy)) {
            $mform->addElement('static', 'passwordpolicyinfo', '', print_password_policy());
        }
        $mform->addElement('passwordunmask', 'newpassword', get_string('newpassword'), 'size="20"');
        $mform->addHelpButton('newpassword', 'newpassword');
        $mform->setType('newpassword', PARAM_RAW);
        if ($id < 0)
            $mform->addRule('newpassword', get_string('required'), 'required', null, 'client');

        $mform->addElement('header', 'moodle', get_string('personaldetails', 'local_users'));
        $mform->addElement('text', 'firstname', get_string('firstname', 'local_admission'));
        $mform->addRule('firstname', get_string('required'), 'required', null, 'client');
        $mform->setType('firstname', PARAM_RAW);

        $mform->addElement('text', 'middlename', get_string('middlename', 'local_admission'));
        $mform->setType('middlename', PARAM_RAW);

        $mform->addElement('text', 'lastname', get_string('lastname', 'local_admission'));
        $mform->addRule('lastname', get_string('required'), 'required', null, 'client');
        $mform->setType('lastname', PARAM_RAW);

        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('male', 'local_admission'), 'Male');
        $radioarray[] = & $mform->createElement('radio', 'gender', '', get_string('female', 'local_admission'), 'Female');
        $mform->addGroup($radioarray, 'gender', 'Gender', array(' '), false);
        $mform->setDefault('gender', 'Male');

        if (!$admin) {
            $mform->addElement('date_selector', 'dob', get_string('dob', 'local_admission'));
            $mform->addHelpButton('dob', 'dateofbirth', 'local_users');
        }
        $mform->addElement('header', 'moodle', get_string('contactdetails', 'local_users'));
        $mform->addElement('text', 'phone1', get_string('phone', 'local_admission'));
        $mform->addRule('phone1', get_string('required'), 'required', null, 'client');
        $mform->addRule('phone1', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('phone1', get_string('phoneminimum', 'local_admission'), 'minlength', 10, 'client');
        $mform->addRule('phone1', get_string('phonemaximum', 'local_admission'), 'maxlength', 15, 'client');
        $mform->setType('phone1', PARAM_RAW);

        $mform->addElement('text', 'email', get_string('email', 'local_admission'));
        $mform->addRule('email', get_string('required'), 'required', null, 'client');
        $mform->addRule('email', get_string('emailerror', 'local_admission'), 'email', null, 'client');
        $mform->setType('email', PARAM_RAW);

        $mform->addElement('text', 'city', get_string('city'));
        $mform->addRule('city', get_string('required'), 'required', null, 'client');
        $mform->setType('city', PARAM_RAW);

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);
        $mform->addRule('country', get_string('missingcountry'), 'required', null, 'client');

        $mform->addElement('textarea', 'address', get_string('address', 'local_users'));
        $mform->addElement('editor', 'description_editor', get_string('userdescription'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_CLEANHTML);
        $mform->addHelpButton('description_editor', 'userdescription');

        $mform->addElement('header', 'moodle', get_string('userpicture', 'local_users'));

        $mform->addElement('static', 'currentpicture', get_string('currentpicture'));
        $mform->addElement('checkbox', 'deletepicture', get_string('delete'));
        $mform->setDefault('deletepicture', 0);
        $mform->addElement('filemanager', 'imagefile', get_string('newpicture'), '', $filemanageroptions);
        $mform->addHelpButton('imagefile', 'newpicture');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submitlable = ($id > 0) ? get_string('updateuser', 'local_users') : get_string('createuser', 'local_users');
        $this->add_action_buttons(true, 'Submit');
    }

    function definition_after_data() {
        global $USER, $CFG, $DB, $OUTPUT;
        $mform = & $this->_form;
        if ($userid = $mform->getElementValue('id')) {
            $user = $DB->get_record('user', array('id' => $userid));
        } else {
            $user = false;
        }
        // print picture
        if (empty($USER->newadminuser)) {
            if ($user) {
                $context = context_user::instance($user->id, MUST_EXIST);
                $fs = get_file_storage();
                $hasuploadedpicture = ($fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.png') || $fs->file_exists($context->id, 'user', 'icon', 0, '/', 'f2.jpg'));

                if (!empty($user->picture) && $hasuploadedpicture) {
                    $imagevalue = $OUTPUT->user_picture($user, array('courseid' => SITEID, 'size' => 64));
                } else {
                    $imagevalue = get_string('none');
                }
            } else {
                $imagevalue = get_string('none');
            }
            $imageelement = $mform->getElement('currentpicture');
            $imageelement->setValue($imagevalue);

            if ($user && $mform->elementExists('deletepicture') && !$hasuploadedpicture) {
                $mform->removeElement('deletepicture');
            }
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $email = $data['email'];
        $id = $data['id'];
        $uname = $data['username'];
        $email_record = $DB->get_record_sql("SELECT * FROM {user} WHERE email = :email AND id <> :id AND deleted <> :del", array('email' => $email, 'id' => $id, 'del' => 1));
        if ($email_record) {
            $errors['email'] = get_string('emailexists', 'local_users');
        }
        $uname_record = $DB->get_record_select('user', 'username LIKE :uname AND id <> :id AND deleted <> :del', array('uname' => "$uname", 'id' => $id, 'del' => 1));
        if ($uname_record) {
            $errors['username'] = get_string('unameexists', 'local_users');
        }

        if (!empty($data['newpassword'])) {
            $errmsg = ''; //prevent eclipse warning
            if (!check_password_policy($data['newpassword'], $errmsg)) {
                $errors['newpassword'] = $errmsg;
            }
        }

        $years = (time() - $data['dob']) / (60 * 60 * 24 * 365);
        if ($years <= 20) {
            $errors['dob'] = get_string('givevaliddob', 'local_users');
        }
        return $errors;
    }

}
