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
 * @subpackage classes
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class duplicate_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $mform->addElement('header', 'settingsheader', get_string('duplicate', 'local_clclasses'));
        $hierarchy = new hierarchy();
        $mform->addElement('static', 'schoolid', get_string('schoolid', 'local_collegestructure'));
        $mform->setType('schoolid', PARAM_RAW);

        $mform->addElement('static', 'semesterid', get_string('semester', 'local_semesters'));
        $mform->addElement('static', 'departmentid', get_string('department', 'local_clclasses'));
        $mform->addElement('static', 'cobaltcourseid', get_string('cobaltcourse', 'local_clclasses'));
        $mform->addElement('static', 'fullname', get_string('classesname', 'local_clclasses'));
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('classesshortname', 'local_clclasses'));
        $mform->addRule('shortname', get_string('missingclassesshort', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);

        $mform->addElement('static', 'classlimit', get_string('classlimit', 'local_clclasses'));
        $mform->setType('classlimit', PARAM_RAW);

        $mform->addElement('static', 'type', get_string('classmode', 'local_clclasses'));
        $mform->setType('type', PARAM_INT);

        $mform->addElement('static', 'online', get_string('classtype', 'local_clclasses'));
        $mform->setType('online', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $actionbutton = get_string('duplicate', 'local_clclasses');

        $this->add_action_buttons($cancel = true, $actionbutton);
    }

}
