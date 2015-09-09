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
 * @subpackage lecturetype
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/lecturetype/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

// get url variables
class edit_form extends moodleform {

// Define the form
    public function definition() {
        global $USER, $CFG, $PAGE;
        $id = optional_param('id', -1, PARAM_INT);
        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        if ($id > 0)
            $mform->addElement('header', 'settingsheader', get_string('edit', 'local_lecturetype'));
        else
            $mform->addElement('header', 'settingsheader', get_string('create', 'local_lecturetype'));
        $tools = array();
        $hierarchy = new hierarchy();
        $items = $hierarchy->get_school_items();
        $school = $hierarchy->get_school_parent($items);
        if ($id < 0) {
            $mform->addElement('select', 'schoolid', get_string('schoolname', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $mform->setType('schoolid', PARAM_RAW);
        }
        if ($id > 0) {
            $mform->addElement('static', 'school_name', get_string('schoolname', 'local_collegestructure'));
            $mform->addElement('hidden', 'schoolid');
            $mform->setType('schoolid', PARAM_RAW);
        }
        $mform->addElement('text', 'lecturetype', get_string('lecturetypename', 'local_lecturetype'), $tools);
        $mform->setType('lecturetype', PARAM_RAW);
        $mform->addRule('lecturetype', null, 'required', null, 'client');
        $mform->addElement('editor', 'description', get_string('description', 'local_lecturetype'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        if ($id < 0)
            $save = get_string('create', 'local_lecturetype');
        else
            $save = get_string('uedit', 'local_lecturetype');
        $this->add_action_buttons(true, $save);
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $id = optional_param('id', -1, PARAM_INT);
        $errors = parent::validation($data, $files);
        if ($data['schoolid'] == 0) {
            $errors['schoolid'] = get_string('schoolrequired', 'local_examtype');
        }
        if ($data['id'] < 0) {
            $check = $DB->get_records('local_lecturetype', array('lecturetype' => $data['lecturetype'], 'schoolid' => $data['schoolid']));
            if ($check)
                $errors['lecturetype'] = get_string('errorlecture', 'local_lecturetype');
        }

        if ($data['id'] > 0) {
            $exists = $DB->get_field('local_lecturetype', 'lecturetype', array('id' => $id));
            if (!($exists === $data['lecturetype'])) {
                $check = $DB->get_records('local_lecturetype', array('lecturetype' => $data['lecturetype'], 'schoolid' => $data['schoolid']));
                if ($check) {
                    $errors['lecturetype'] = get_string('errorlecture', 'local_lecturetype');
                }
            }
        }
        return $errors;
    }

}
