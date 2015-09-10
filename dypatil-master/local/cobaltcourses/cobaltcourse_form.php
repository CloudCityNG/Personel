<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');
$hierarchy = new hierarchy();

class createcourse extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $disable = ($id > 0) ? 'disabled="disabled"' : '';
        $heading = ($id > 0) ? get_string('editcourse', 'local_cobaltcourses') : get_string('createcourse', 'local_cobaltcourses');
        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin())
            $school = $hierarchy->get_school_items();
        $parents = $hierarchy->get_school_parent($school);
        $count = count($school);

        $PAGE->requires->yui_module('moodle-local_cobaltcourses-courses', 'M.local_cobaltcourses.init_courses', array(array('formid' => $mform->getAttribute('id'))));

        $mform->addElement('header', 'settingsheader', $heading);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);

        if ($count == 1) {

            foreach ($school as $scl) {
                $key = $scl->id;
                $value = $scl->fullname;
            }

            $mform->addElement('static', 'schools', get_string('school', 'local_cobaltcourses'), $value);
            $mform->addElement('hidden', 'schoolid', $key);

            $department = $hierarchy->get_departments_forschool($key);
            $mform->addElement('select', 'departmentid', get_string('selectdepartment', 'local_cobaltcourses'), $department);
            $mform->addRule('departmentid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');
        } else {
            $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        }

        $mform->addElement('hidden', 'addcourselisthere1');
        $mform->setType('addcourselisthere1', PARAM_RAW);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('text', 'fullname', get_string('coursename', 'local_cobaltcourses'));
        $mform->addRule('fullname', get_string('missingcourse', 'local_cobaltcourses'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('courseid', 'local_cobaltcourses'));
        $mform->addRule('shortname', get_string('missingshort', 'local_cobaltcourses'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);

        $mform->addElement('editor', 'summary', get_string('description', 'local_cobaltcourses'));
        $mform->setType('summary', PARAM_RAW);

        $type = array(0 => get_string('general', 'local_cobaltcourses'), 1 => get_string('elective', 'local_cobaltcourses'));
        $mform->addElement('select', 'coursetype', get_string('coursetype', 'local_cobaltcourses'), $type);

        $size = 'style="width:50px !important;"';
        $mform->addElement('text', 'credithours', get_string('credithours', 'local_cobaltcourses'), $size);
        $mform->addRule('credithours', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('credithours', get_string('missingcredithours', 'local_cobaltcourses'), 'numeric', null, 'client');
        $mform->addRule('credithours', get_string('missinghours', 'local_cobaltcourses'), 'required', null, 'client');
        $mform->setType('credithours', PARAM_RAW);

        $mform->addElement('text', 'coursecost', get_string('coursecost', 'local_cobaltcourses'), $size);
        $mform->addRule('coursecost', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('coursecost', get_string('missingcredithours', 'local_cobaltcourses'), 'numeric', null, 'client');
        $mform->setType('coursecost', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('updatecourse', 'local_cobaltcourses') : get_string('createcourse', 'local_cobaltcourses');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $schoolvalue = $mform->getElementValue('schoolid');
        $countvalue = $mform->getElementValue('count');
        $department = array();
        if ($countvalue != 1 && is_array($schoolvalue) && !empty($schoolvalue) && $schoolvalue[0] != null) {
            $department = $hierarchy->get_departments_forschool($schoolvalue[0], false);
            $myselect = $mform->createElement('select', 'departmentid', get_string('selectdepartment', 'local_cobaltcourses'), $department);
            $mform->insertElementBefore($myselect, 'addcourselisthere1');

            $mform->addRule('departmentid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $id = $data['id'];
        /* Bug -id #269
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * Resolved- providing proper validation, When the user entered exists shortname  */
        // $shortname=mysql_real_escape_string($data['shortname']);
        $shortname = $data['shortname'];
        $compare_scale_clause = $DB->sql_compare_text("shortname")  . ' = ' . $DB->sql_compare_text(":sh");
        if ($id > 0) {
            $courseid = $DB->get_record_sql("SELECT * FROM {$CFG->prefix}local_cobaltcourses WHERE id != {$id} AND $compare_scale_clause ",array('sh'=>$shortname));
        } else {
            
            $courseid = $DB->get_record_sql("SELECT * FROM {$CFG->prefix}local_cobaltcourses WHERE  $compare_scale_clause",array('sh'=>$shortname));
            // AND id != $id       
        }
        if (!empty($courseid)) {
            $errors['shortname'] = get_string('courseidexists', 'local_cobaltcourses');
        }
        if ($data['credithours'] == 0) {
            $errors['credithours'] = get_string('creditscannotzero', 'local_cobaltcourses');
        }
        if ($data['credithours'] < 0) {
            $errors['credithours'] = get_string('numeric', 'local_admission');
        }
        if ($data['coursecost'] < 0) {
            $errors['coursecost'] = get_string('numeric', 'local_admission');
        }

        return $errors;
    }

}
