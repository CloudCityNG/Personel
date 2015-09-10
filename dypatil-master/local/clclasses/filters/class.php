<?php

require_once($CFG->dirroot . '/user/filters/lib.php');

$hierarchy = new hierarchy();

/**
 * User filter based on global roles.
 */
class class_filter_school extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function class_filter_school($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    /**
     * Returns an array of available schools
     * @return array of availble schools
     */
    function get_school_list() {
        global $hierarchy;
        if (isloggedin())
            $schools = array(0 => get_string('anyschool', 'local_collegestructure')) + $hierarchy->get_myschools(true);
        else
            $schools = array(0 => get_string('anyschool', 'local_collegestructure')) + $hierarchy->get_records_cobaltselect_menu('local_school', 'visible=1', null, '', 'id,fullname');

        return $schools;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $obj = & $mform->addElement('select', $this->_name, $this->_label, $this->get_school_list());
        $mform->setDefault($this->_name, 0);
        if ($this->_advanced) {
            $mform->setAdvanced($this->_name);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field = $this->_name;

        if (array_key_exists($field, $formdata) and !empty($formdata->$field)) {
            return array('value' => (int) $formdata->$field);
        }
        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    function get_sql_filter($data) {
        global $CFG;
        $value = (int) $data['value'];

        $timenow = round(time(), 100);

        $sql = "c.id IN (SELECT id
                            FROM {local_cobaltcourses} s
                            WHERE s.schoolid=$value)";
        return array($sql, array());
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB, $USER;
        $school = new stdClass();
        $school->label = $this->_label;
        $school->value = $DB->get_field('local_school', 'fullname', array('id' => $data['value']));
        return get_string('schoolselected', 'local_clclasses', $school);
    }

}

class department_filter extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function course_filter($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    /**
     * get all courses
     */
    function get_department_list() {
        global $hierarchy, $USER;
        if (isloggedin())
            $courses = array(0 => get_string('alldepartments', 'local_clclasses')) + $hierarchy->get_alldepartments($USER->id);
        else
            $courses = array(0 => get_string('alldepartments', 'local_clclasses')) + $hierarchy->get_alldepartments($userid = NULL);
        return $courses;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $obj = & $mform->addElement('select', $this->_name, $this->_label, $this->get_department_list());
        $mform->setDefault($this->_name, 0);
        if ($this->_advanced) {
            $mform->setAdvanced($this->_name);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field = $this->_name;

        if (array_key_exists($field, $formdata) and !empty($formdata->$field)) {
            return array('value' => (int) $formdata->$field);
        }
        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    function get_sql_filter($data) {
        global $CFG;
        $value = (int) $data['value'];

        $timenow = round(time(), 100);

        $sql = "c.id IN (SELECT id
                            FROM {local_cobaltcourses} s
                            WHERE s.departmentid=$value)";
        return array($sql, array());
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;
        $department = $DB->get_record('local_department', array('id' => $data['value']));
        $dept = new stdClass();
        $dept->label = $this->_label;
        $dept->value = $DB->get_field('local_department', 'fullname', array('id' => $data['value']));
        return get_string('departmentsfilter', 'local_clclasses', $dept);
    }

}

class semester_filter extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function semester_filter($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    /**
     * Returns an array of available schools
     * @return array of availble schools
     */
    function get_activesemester_list() {
        global $hierarchy, $USER;
        $userid = $USER->id;
        if (!isloggedin())
            $courses = array(0 => get_string('allsemester', 'local_clclasses')) + $hierarchy->get_allmyactivesemester($userid = NULL);
        else
            $courses = array(0 => get_string('allsemester', 'local_clclasses')) + $hierarchy->get_allmyactivesemester($userid);

        return $courses;
    }

    /**
     * Adds controls specific to this filter in the form.
     * @param object $mform a MoodleForm object to setup
     */
    function setupForm(&$mform) {
        $obj = & $mform->addElement('select', $this->_name, $this->_label, $this->get_activesemester_list());
        //   $obj =& $mform->addRule($this->_name, get_string('missingsemester','local_clclasses'), 'required', null, 'client');

        $mform->setDefault($this->_name, 0);
        if ($this->_advanced) {
            $mform->setAdvanced($this->_name);
        }
    }

    /**
     * Retrieves data from the form data
     * @param object $formdata data submited with the form
     * @return mixed array filter data or false when filter not set
     */
    function check_data($formdata) {
        $field = $this->_name;

        if (array_key_exists($field, $formdata) and !empty($formdata->$field)) {
            return array('value' => (int) $formdata->$field);
        }
        return false;
    }

    /**
     * Returns the condition to be used with SQL where
     * @param array $data filter settings
     * @return array sql string and $params
     */
    function get_sql_filter($data) {
        global $CFG;
        $value = (int) $data['value'];
        $timenow = round(time(), 100);
        $sql = "lc.semesterid=$value";
        return array($sql, array());
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;

        $school = $DB->get_record('local_semester', array('id' => $data['value']));
        $a = new stdClass();
        $a->label = $this->_label;
        $a->value = $DB->get_field('local_semester', 'fullname', array('id' => $data['value']));
        return get_string('semesterselected', 'local_clclasses', $a);
    }

}

/* End of the semester Reoports */

class clactive_filter_form extends moodleform {

    function definition() {
        global $SESSION; // this is very hacky :-(

        $mform = & $this->_form;
        $fields = $this->_customdata['fields'];
        $extraparams = $this->_customdata['extraparams'];

        if (!empty($SESSION->filtering)) {
            /* ---add controls for each active filter in the active filters group--- */
            $mform->addElement('header', 'actfilterhdr', get_string('actfilterhdr', 'filters'));

            foreach ($SESSION->filtering as $fname => $datas) {
                if (!array_key_exists($fname, $fields)) {
                    continue; // filter not used
                }
                $field = $fields[$fname];
                foreach ($datas as $i => $data) {
                    $description = $field->get_label($data);
                    $mform->addElement('checkbox', 'filter[' . $fname . '][' . $i . ']', null, $description);
                }
            }

            if ($extraparams) {
                foreach ($extraparams as $key => $value) {
                    $mform->addElement('hidden', $key, $value);
                    $mform->setType($key, PARAM_RAW);
                }
            }

            $objs = array();
            $objs[] = &$mform->createElement('submit', 'removeselected', get_string('removeselected', 'filters'));
            $objs[] = &$mform->createElement('submit', 'removeall', get_string('removeall', 'filters'));
            $mform->addElement('group', 'actfiltergrp', '', $objs, ' ', false);
        }
    }

}