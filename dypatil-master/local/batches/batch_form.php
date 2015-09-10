<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * batch related management functions, this file needs to be included manually.
 *
 * @package    core_batch
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');
$hierarchy = new hierarchy();

class local_batches_edit_form extends moodleform {

    /**
     * Define the batch edit form
     */
    public function definition() {
        global $DB, $USER, $PAGE, $hierarchy;
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        $batch = $this->_customdata['data'];

        $PAGE->requires->yui_module('moodle-local_batches-batches', 'M.local_batches.init_batches', array(array('formid' => $mform->getAttribute('id'))));

        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $school = $hierarchy->get_school_items();
        }
        $parents = $hierarchy->get_school_parent($school);
        // $mform->addElement('header', 'settingsheader', get_string('createbatch', 'local_batches'));
        $count = count($school);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);
        //if ($count == 1) {
        //    /* ---registrar is assigned to only one school, display as static--- */
        //    foreach ($school as $scl) {
        //        $key = $scl->id;
        //        $value = $scl->fullname;
        //    }
        //    $mform->addElement('static', 'schools', get_string('select', 'local_collegestructure'), $value);
        //    $mform->addElement('hidden', 'schoolid', $key);
        //    $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$key AND visible=1", null, '', 'id,fullname', '--Select--');
        //    $mform->addElement('select', 'programid', get_string('selectprogram', 'local_programs'), $program);
        //    $mform->addRule('programid', get_string('missingprogram', 'local_programs'), 'required', null, 'client');
        //} else {
        $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        // }   

        $mform->addElement('hidden', 'addprogram');
        $mform->setType('addprogram', PARAM_RAW);

        $mform->addElement('hidden', 'addcurriculum');
        $mform->setType('addcurriculum', PARAM_RAW);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        for ($year = 2000; $year <= 2050; $year++) {
            $years[NULL] = "---Select---";
            $years[$year] = $year;
        }
        $mform->addElement('select', 'academicyear', get_string('selectacademicyear', 'local_batches'), $years);
        $mform->addRule('academicyear', get_string('missingacademicyear', 'local_batches'), 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('name', 'cohort'), 'maxlength="254" size="50"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);


        $mform->addElement('text', 'idnumber', get_string('idnumber', 'local_batches'), 'maxlength="254" size="50"');
        $mform->setType('idnumber', PARAM_RAW); // Idnumbers are plain text, must not be changed.
        $mform->addRule('idnumber', get_string('required'), 'required', null, 'client');

        $mform->addElement('advcheckbox', 'visible', get_string('visible'));
        $mform->setDefault('visible', 1);
        $mform->addHelpButton('visible', 'visible', 'local_batches');

        $mform->addElement('editor', 'description_editor', get_string('description', 'local_cobaltcourses'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        $mform->addElement('hidden', 'contextid', context_system::instance()->id);
        $mform->setType('contextid', PARAM_INT);

        $mform->addElement('hidden', 'timecreated', time());
        $mform->setType('timecreated', PARAM_INT);

        $mform->addElement('hidden', 'timemodified', time());
        $mform->setType('timemodified', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $DB;
        global $hierarchy, $mybatch;
        $mform = $this->_form;
        // $id = $this->_customdata['id'];

        $schoolvalue = $mform->getElementValue('schoolid');

        $program = array();
        if (isset($schoolvalue) && $schoolvalue[0] > 0) {
            $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$schoolvalue[0] AND visible=1", null, '', 'id,fullname', '--Select--');
            $myselect = $mform->createElement('select', 'programid', get_string('program', 'local_programs'), $program);
            $mform->insertElementBefore($myselect, 'addprogram');
            $mform->addRule('programid', get_string('missingprogram', 'local_programs'), 'required', null, 'client');
            $programvalue = $mform->getElementValue('programid');
        }

        // --------- displaying curriculums drop down----------------------------
        if (isset($programvalue) && $programvalue[0] > 0) {
            $curriculumlist = $hierarchy->get_program_curriculum($programvalue[0], $schoolvalue[0]);
            $curriculum_select = $mform->createElement('select', 'curriculumid', get_string('curriculum', 'local_curriculum'), $curriculumlist);
            $mform->insertElementBefore($curriculum_select, 'addcurriculum');
            $mform->addRule('curriculumid', get_string('missingcurriculum', 'local_curriculum'), 'required', null, 'client');
            // $programvalue = $mform->getElementValue('programid');
        }
    }

// end of definition_after_data

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);
        //
        $idnumber = trim($data['idnumber']);
        //if ($idnumber === '') {
        //    // Fine, empty is ok.
        //
        //} else
        if (($data['startdate'] > 0 && $data['enddate'] > 0 ) && ( $data['enddate'] < $data['startdate'])) {
            $errors['enddate'] = get_string('startdategreaterenddate', 'block_learning_plan');
        }
        if ($batch = $DB->get_record('cohort', array('idnumber' => $idnumber), '*', IGNORE_MULTIPLE)) {
            if (empty($data['id']) || $batch->id != $data['id']) {
                $errors['idnumber'] = get_string('shortnametaken', '', $batch->name);
            }
        }

        return $errors;
    }

// end of validation function

    protected function get_category_options($currentcontextid) {
        global $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $displaylist = coursecat::make_categories_list('moodle/cohort:manage');
        $options = array();
        $syscontext = context_system::instance();
        if (has_capability('moodle/cohort:manage', $syscontext)) {
            $options[$syscontext->id] = $syscontext->get_context_name();
        }
        foreach ($displaylist as $cid => $name) {
            $context = context_coursecat::instance($cid);
            $options[$context->id] = $name;
        }
        // Always add current - this is not likely, but if the logic gets changed it might be a problem.
        if (!isset($options[$currentcontextid])) {
            $context = context::instance_by_id($currentcontextid, MUST_EXIST);
            $options[$context->id] = $syscontext->get_context_name();
        }
        return $options;
    }

// end of category options
}

// end of class

