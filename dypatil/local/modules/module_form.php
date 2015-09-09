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
 * Edit a tool provided in a course
 *
 * @package    local
 * @subpackage Modules
 * @copyright  2013 Sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');

// get url variables
class module_form extends moodleform {

    // Define the form
    public function definition() {
        global $USER, $CFG, $PAGE;
        $id = optional_param('id', -1, PARAM_INT);
        $hierarchy = new hierarchy();
        $mform = $this->_form;
        if ($id < 0)
            $PAGE->requires->yui_module('moodle-local_modules-schoolchooser', 'M.local_modules.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $editoroptions = $this->_customdata['editoroptions'];
        $items = $hierarchy->get_school_items();
        $school = $hierarchy->get_school_parent($items);
        $editoroptions = $this->_customdata['editoroptions'];
        if ($id > 0)
            $mform->addElement('header', 'settingsheader', get_string('editmodule', 'local_modules'));
        else
            $mform->addElement('header', 'settingsheader', get_string('createmodule', 'local_modules'));
        if ($id < 0) {

            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $mform->setType('schoolid', PARAM_RAW);

            $mform->registerNoSubmitButton('updateschoolformat');
            $mform->addElement('submit', 'updateschoolformat', get_string('courseformatudpate'));
            $mform->addElement('hidden', 'addprogramslisthere');
            $mform->setType('addprogramslisthere', PARAM_RAW);
        }

        if ($id > 0) {
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'));
            $mform->addElement('hidden', 'schoolid');
            $mform->setType('schoolid', PARAM_RAW);
            $mform->addElement('static', 'program_name', get_string('programname', 'local_programs'));
            $mform->addElement('hidden', 'programid');
            $mform->setType('programid', PARAM_RAW);
        }
        $tools = array();
        $mform->addElement('text', 'fullname', get_string('modulename', 'local_modules'), $tools);
        $mform->addRule('fullname', get_string('missingmodulename', 'local_modules'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_modules'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('savemodule', 'local_modules') : get_string('createmodule', 'local_modules');
        $this->add_action_buttons($cancel = true, $submitlable);
        // $this->add_action_buttons();
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;
        $id = $mform->getElementValue('id');
        if ($id < 0) {
            $formatvalue = $mform->getElementValue('schoolid');
            $tools = array();
            $hier = new hierarchy();

            if ($formatvalue && $formatvalue[0] > 0)
               $tools = $hier->get_records_cobaltselect_menu('local_program', "schoolid=$formatvalue[0] AND visible=1", null, '', 'id,fullname', '--Select--');
            if (is_array($formatvalue) && !empty($formatvalue) && $formatvalue[0] > 0) {
                $newel = $mform->createElement('select', 'programid', get_string('programname', 'local_programs'), $tools);
                $mform->insertElementBefore($newel, 'addprogramslisthere');
                $mform->addRule('programid', get_string('missingfullname', 'local_programs'), 'required', null, 'client');
            }
        }
    }

    public function validation($data, $files) {
        global $DB;
        $id = optional_param('id', -1, PARAM_INT);
        $errors = parent::validation($data, $files);
        if ($data['id'] < 0) {
            $check = $DB->get_records('local_module', array('schoolid' => $data['schoolid'], 'programid' => $data['programid'], 'fullname' => $data['fullname']));
            if ($check) {
                $errors['fullname'] = get_string('errormodule', 'local_modules');
            }
        }
        if ($data['id'] > 0) {
            $exists = $DB->get_field('local_module', 'fullname', array('id' => $id));
            if (!($exists === $data['fullname'])) {
                $check = $DB->get_records('local_module', array('schoolid' => $data['schoolid'], 'programid' => $data['programid'], 'fullname' => $data['fullname']));
                if ($check) {
                    $errors['fullname'] = get_string('errormodule', 'local_modules');
                }
            }
        }
        return $errors;
    }

}
