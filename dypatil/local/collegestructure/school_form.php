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
 * @subpackage Collegestructure
 * @copyright  2012 Niranjan<niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');

class school_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $PAGE;
        $school = new school();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $schools = $this->_customdata['tool'];
        $editoroptions = $this->_customdata['editoroptions'];
        if ($id < 0)
            $mform->addElement('header', 'settingsheader', get_string('createschool', 'local_collegestructure'));
        else
            $mform->addElement('header', 'settingsheader', get_string('editschool', 'local_collegestructure'));
        $tools = array();
        $hierarchy = new hierarchy();
        $items = $hierarchy->get_school_items(true);
        $parents = $hierarchy->get_school_parent($items, $schools->id);
        if (count($parents) <= 1) {
            $mform->addElement('hidden', 'parentid', 0);
            $mform->setType('parentid', PARAM_RAW);
        } else {
            $mform->addElement('select', 'parentid', get_string('parent', 'local_collegestructure'), $parents);
            $mform->setType('parentid', PARAM_RAW);
        }
        $mform->addHelpButton('parentid', 'parent', 'local_collegestructure');
        $mform->addElement('text', 'fullname', get_string('schoolname', 'local_collegestructure'), $tools);
        $mform->setType('fullname', PARAM_TEXT);
        $mform->addRule('fullname', get_string('missingschoolname', 'local_collegestructure'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('description', 'local_collegestructure'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        $selecttype = array();
        $selecttype['1'] = get_string('campus', 'local_collegestructure');
        $selecttype['2'] = get_string('university', 'local_collegestructure');
        $selecttype['3'] = get_string('location', 'local_collegestructure');
        $mform->addElement('select', 'type', get_string('type', 'local_collegestructure'), $selecttype);
        $mform->addHelpButton('type', 'type', 'local_collegestructure');
        $mform->setType('type', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $now = date("d-m-Y");
        $now = strtotime($now);
        $mform->addElement('hidden', 'timecreated', $now);
        $mform->setType('timecreated', PARAM_RAW);
        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);
        $themelist = $school->cobalt_get_theme_list();
        $mform->addElement('select', 'theme', get_string('theme', 'local_collegestructure'), $themelist);
        $mform->setType('theme', PARAM_RAW);
        $mform->addRule('theme', get_string('missingtheme', 'local_collegestructure'), 'required', null, 'client');
        $submit = ($id > 0) ? get_string('update_school', 'local_collegestructure') : get_string('create', 'local_collegestructure');
        $this->add_action_buttons('false', $submit);
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
