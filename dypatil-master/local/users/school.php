<?php

require_once($CFG->dirroot . '/user/filters/lib.php');
$hierarchy = new hierarchy();

/**
 * User filter based on global roles.
 */
class user_filter_school extends user_filter_type {

    /**
     * Constructor
     * @param string $name the name of the filter instance
     * @param string $label the label of the filter instance
     * @param boolean $advanced advanced form element flag
     */
    function user_filter_school($name, $label, $advanced) {
        parent::user_filter_type($name, $label, $advanced);
    }

    /**
     * Returns an array of available schools
     * @return array of availble schools
     */
    function get_school_list() {
        global $hierarchy;
        $schoollist = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $schoollist = $hierarchy->get_school_items();
        }
        $schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = false, $all = false);
        $schools = array(0 => get_string('anyschool', 'local_collegestructure')) + $schoollist;
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

        if (array_key_exists($field, $formdata) and ! empty($formdata->$field)) {
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
        global $CFG, $DB;
        $value = (int) $data['value'];

        $timenow = round(time(), 100);

        $users = $DB->get_records_sql("SELECT userid FROM {local_school_permissions} WHERE schoolid={$value}");
        $students = $DB->get_records_sql("SELECT userid FROM {local_userdata} WHERE schoolid={$value}");
        $useridin = array();
        foreach ($users as $k => $v) {
            $useridin[$k] = $k;
        }
        foreach ($students as $key => $val) {
            $useridin[$key] = $key;
        }
        $useridin = implode(',', $useridin);

        /* Bug report #277  -  Manage Users>Browse Users>Filters- Error Reading
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * Resolved - added proper condition when userlist is empty
         */
        if (empty($useridin))
            $useridin = 0;

        $sql = "id IN ($useridin)";
        return array($sql, array());
    }

    /**
     * Returns a human friendly description of the filter used as label.
     * @param array $data filter settings
     * @return string active filter label
     */
    function get_label($data) {
        global $DB;

        $school = $DB->get_record('local_school', array('id' => $data['value']));
        $a = new stdClass();
        $a->label = $this->_label;
        $a->value = $DB->get_field('local_school', 'fullname', array('id' => $data['value']));
        return get_string('assignedschoolis', 'local_users', $a);
    }

}

class active_filter_form extends moodleform {

    function definition() {
        global $SESSION; // this is very hacky :-(

        $mform = & $this->_form;
        $fields = $this->_customdata['fields'];
        $extraparams = $this->_customdata['extraparams'];

        if (!empty($SESSION->filtering)) {
            // add controls for each active filter in the active filters group
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
