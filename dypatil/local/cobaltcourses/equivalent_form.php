<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');

$hierarchy = new hierarchy();

class equivalent_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-local_cobaltcourses-equivalent', 'M.local_cobaltcourses.init_equivalent', array(array('formid' => $mform->getAttribute('id'))));
        /* ---the schools which are assigned to the registrar--- */
        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin())
            $school = $hierarchy->get_school_items();
        $parents = $hierarchy->get_school_parent($school);
        $count = count($school);

        $mform->addElement('header', 'settingsheader', get_string('equicourse', 'local_cobaltcourses'));
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);
        if ($count == 1) {
            /* ---if only one school is assigned show the school as static--- */
            foreach ($school as $scl) {
                $key = $scl->id;
                $value = $scl->fullname;
            }
            $mform->addElement('static', 'schools', get_string('schoolid', 'local_collegestructure'), $value);
            $mform->addElement('hidden', 'schoolid', $key);
            $mform->setType('schoolid', PARAM_INT);
            $department = $hierarchy->get_departments_forschool($key);
            $mform->addElement('select', 'departmentid', get_string('selectdepartment', 'local_cobaltcourses'), $department);
            $mform->addRule('departmentid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');
            $mform->addElement('hidden', 'addcourselisthere1');
            $mform->setType('addcourselisthere1', PARAM_RAW);
            $mform->addElement('select', 'equivalentdeptid', get_string('equidept', 'local_cobaltcourses'), $department);
            $mform->addRule('equivalentdeptid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');
            $mform->addElement('hidden', 'addcourselisthere2');
            $mform->setType('addcourselisthere2', PARAM_RAW);
        } else {
            $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', 0, 'client');
        }

        $mform->addElement('hidden', 'addcourselisthere3');
        $mform->setType('addcourselisthere3', PARAM_RAW);
        $mform->addElement('hidden', 'addcourselisthere4');
        $mform->setType('addcourselisthere4', PARAM_RAW);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addcourselisthere5');
        $mform->setType('addcourselisthere5', PARAM_RAW);
        $mform->addElement('hidden', 'addcourselisthere6');
        $mform->setType('addcourselisthere6', PARAM_RAW);

        $submitlable = get_string('equicourse', 'local_cobaltcourses');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB;
        global $hierarchy;
        $mform = $this->_form;

        $countvalue = $mform->getElementValue('count');
        /* ---department dropdowns--- */
        if ($countvalue != 1) {
            $schoolvalue = $mform->getElementValue('schoolid');
            if (is_array($schoolvalue) && !empty($schoolvalue) && $schoolvalue[0] != 0) {
                if ($schoolvalue)
                    $dept = $hierarchy->get_departments_forschool($schoolvalue[0]);
                $dep1 = $mform->createElement('select', 'departmentid', get_string('selectdepartment', 'local_cobaltcourses'), $dept);
                $mform->insertElementBefore($dep1, 'addcourselisthere3');
                $mform->addRule('departmentid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');

                $dep2 = $mform->createElement('select', 'equivalentdeptid', get_string('equidept', 'local_cobaltcourses'), $dept);
                $mform->insertElementBefore($dep2, 'addcourselisthere5');
                $mform->addRule('equivalentdeptid', get_string('missingdepartment', 'local_cobaltcourses'), 'required', null, 'client');

                $formatvalue1 = $mform->getElementValue('departmentid');
                $formatvalue2 = $mform->getElementValue('equivalentdeptid');
            }
        }



        if ($countvalue == 1) {
            $schoolvalue[0] = $mform->getElementValue('schoolid');
            $formatvalue1 = $mform->getElementValue('departmentid');
            $formatvalue2 = $mform->getElementValue('equivalentdeptid');
        }
        /* ---course dropdowns--- */
        if ($schoolvalue[0] > 0) {
            if (is_array($formatvalue1) && !empty($formatvalue1) && $formatvalue1[0] != 0) {
                if ($formatvalue1[0])
                    $course = get_courses_department($formatvalue1[0], $schoolvalue[0]);
                $newel = $mform->createElement('select', 'courseid', get_string('selectcourse', 'local_cobaltcourses'), $course);
                if ($countvalue == 1)
                    $mform->insertElementBefore($newel, 'addcourselisthere1');
                else
                    $mform->insertElementBefore($newel, 'addcourselisthere4');
                $mform->addRule('courseid', get_string('missingcourse', 'local_cobaltcourses'), 'required', null, 'client');
                $formatvalue3 = $mform->getElementValue('courseid');
            }
        }
        if (isset($formatvalue3)) {
            if (is_array($formatvalue2) && !empty($formatvalue2) && $formatvalue2[0] != 0) {
                if ($formatvalue2)
                    $courses = get_courses_department($formatvalue2[0], $schoolvalue[0], $formatvalue3[0]);
                $newe2 = $mform->createElement('select', 'equivalentcourseid', get_string('equicourse', 'local_cobaltcourses'), $courses);
                $newe2->setMultiple(true);
                if ($countvalue == 1)
                    $mform->insertElementBefore($newe2, 'addcourselisthere2');
                else
                    $mform->insertElementBefore($newe2, 'addcourselisthere6');
                $mform->addRule('equivalentcourseid', get_string('missingcourse', 'local_cobaltcourses'), 'required', null, 'client');
            }
        }
    }

    /* ---validations for the created data--- */

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $courseid = $data['courseid'];
        $nextcourse = $data['equivalentcourseid'];
        $courses = array();

        foreach ($nextcourse as $course) {
            $courses[] = $DB->get_record_sql("SELECT * FROM {$CFG->prefix}local_course_equivalent WHERE courseid = $courseid AND equivalentcourseid LIKE '%$course%'");
        }

        $courses = array_filter($courses);
        if (!empty($courses)) {
            $errors['equivalentcourseid'] = get_string('equivalentrecordexists', 'local_cobaltcourses');
        }
        if ($data['departmentid'] == '0') {
            $errors['departmentid'] = 'Select Department Name';
        }
        if ($data['equivalentdeptid'] == '0') {
            $errors['equivalentdeptid'] = 'Select Equivalent Department Name';
        }
        return $errors;
    }

}

?>