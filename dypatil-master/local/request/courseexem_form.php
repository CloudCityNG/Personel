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
 * Version details.
 *
 * @package    local
 * @subpackage requsets(idcard)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class courseexem_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $definitionoptions = $this->_customdata['definitionoptions'];
        $attachmentoptions = $this->_customdata['attachmentoptions'];
        $request = new requests();
        $PAGE->requires->yui_module('moodle-local_request-schoolchooser', 'M.local_request.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $schools = $request->school();
        $ass_school = $request->assigned_school();
        $count = count($schools);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_RAW);
        // if on school assigned to a user condition
        if ($count == 1) {
            foreach ($schools as $school) {
                $value = $school->fullname;
                $key = $school->id;
            }
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'), $value);
            $mform->addElement('hidden', 'schoolid', $key);
            $mform->setType('schoolid', PARAM_INT);

            if ($semester = $request->currentsemester($key)) {
                $mform->addElement('static', 'semester_name', get_string('semester', 'local_semesters'), $semester->fullname);
                $mform->addElement('hidden', 'semesterid', $semester->id);
                $mform->setType('semesterid', PARAM_INT);
            }
        } else {
            //if user assigned to more than one school going to defination_after_data function
            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $ass_school);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $mform->addElement('hidden', 'addsemester');
            $mform->setType('addsemester', PARAM_RAW);
        }
        $mform->registerNoSubmitButton('chooseschool');
        $mform->addElement('submit', 'chooseschool', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addcourse');
        $mform->setType('addcourse', PARAM_RAW);
        $mform->addElement('editor', 'definition_editor', get_string('description'), null, $definitionoptions);
        $mform->setType('definition_editor', PARAM_RAW);
        $mform->addHelpButton('definition_editor', 'definition_editor', 'local_request');
        $mform->addElement('filemanager', 'attachment', get_string('attachment', 'local_request'), null, $attachmentoptions);
        $mform->addRule('attachment', get_string('required'), 'required', null, 'client');
        $mform->addHelpButton('attachment', 'attachment', 'local_request');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('submitbutton', 'local_request'));
    }

    function definition_after_data() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $request = new requests();
        $countvalue = $mform->getElementValue('count');
        $school = $mform->getElementValue('schoolid');
        if ($countvalue > 1) {
            $semester = $request->currentsemester($school[0]);
            $mysemester = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $semester->fullname);
            $mform->createElement('hidden', 'semesterid', $semester->id);
            $mform->setType('semesterid', PARAM_INT);
            $mform->insertElementBefore($mysemester, 'addsemester');
        }
        $course = array();
        $semester = $mform->getElementValue('semesterid');
        $course = $request->get_enrolledcourses($semester, $school);

        $mycourse = $mform->createElement('select', 'courseid', get_string('course'), $course);
        $mform->setType('courseid', PARAM_INT);
        $mform->insertElementBefore($mycourse, 'addcourse');
        $mform->addHelpButton('courseid', 'courseid', 'local_request');
        $mform->addRule('courseid', get_string('required'), 'required', null, 'client');
        $size = 'style="width:50px !important;"';
        $mygrade = array();
        $mygrade[] = &$mform->createElement('text', 'grades', '', $size);
        $mygrade[] = &$mform->createElement('static', 'outof', '', '100');
        $grade = $mform->createElement('group', 'mygrade', get_string('grades'), $mygrade, ' / ', false);
        $mform->insertElementBefore($grade, 'addcourse');
        $mform->addHelpButton('mygrade', 'mygrade', 'local_request');
        $mform->addRule('mygrade', get_string('required'), 'required', null, 'client');
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $marks = $data['grades'];
        if (!is_numeric($marks)) {
            $errors['mygrade'] = get_string('numeric', 'local_request');
        }
        if ($marks > 100) {
            $errors['mygrade'] = get_string('enter_valid_grades', 'local_request');
        }
        if ($marks == null || $marks == '') {
            $errors['mygrade'] = get_string('required');
        }

        return $errors;
    }

}

?>