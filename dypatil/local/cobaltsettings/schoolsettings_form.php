<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class schoolsettings_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('system_settings', 'local_cobaltsettings'));
        $hier1 = new hierarchy();
        $schoolids = $hier1->get_assignedschools();
        if (!empty($schoolids)) {
            $count = sizeof($schoolids);
            if ($count > 1) {
                $parents = $hier1->get_school_parent($schoolids, '', true);
                $attributes1 = 'style="height:25px; width:29%; "';
                $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents, $attributes1);
                $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            } else {
                $schoolname = $DB->get_record('local_school', array('id' => $schoolids[0]->id));
                $mform->addElement('static', 'sid', get_string('select', 'local_collegestructure'), $schoolname->fullname);
                $mform->addElement('hidden', 'schoolid', $schoolids[0]->id);
            }
        }
        $mform->addElement('advcheckbox', 'batch', null, 'Batch', array('group' => 1), array(0, 1));
        $mform->addElement('html', '<div style=" margin-left:265px;" class="form-description"><p>' . get_string('enable_batch', 'local_cobaltsettings') . '
			   </p></div>');
        $mform->addElement('advcheckbox', 'prefix_suffix', null, 'Prefix-Suffix', array('group' => 1), array(0, 1));
        $mform->addElement('html', '<div style=" margin-left:265px;" class="form-description"><p>' . get_string('create_batch', 'local_cobaltsettings') . '
			   </p></div>');
        $mform->addElement('advcheckbox', 'online_app', null, 'Online Applications', array('group' => 1), array(0, 1));
        $mform->addElement('html', '<div style=" margin-left:265px; color:light-grey" class="form-description"><p>' . get_string('enable_online', 'local_cobaltsettings') . '
			   </p></div>');
        $mform->addElement('advcheckbox', 'certificate', null, 'Issue certificates', array('group' => 1), array(0, 1));
        /* start of vijaya jan-29 */
        $mform->addElement('advcheckbox', 'onlinepayment', null, 'Online Payment', array('group' => 1), array(0, 1));
        /* end of vijaya jan-29 */
        $this->add_action_buttons(true, 'Submit');
    }

}

?>
 