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
 * @subpackage townhall
 * @copyright  2013 sreenivas <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

// get url variables
class hall_form extends moodleform {

    // Define the form
    public function definition() {
        global $USER, $CFG, $PAGE;
        $mform = $this->_form;
        $id = optional_param('id', -1, PARAM_INT);
        $mform->addElement('header', 'settingsheader', get_string('pluginname', 'local_townhall'));
        $mform->addElement('text', 'topic', get_string('topic', 'local_townhall'));
        $mform->setType('topic', PARAM_TEXT);
        $mform->addRule('topic', null, 'required', null, 'client');
        //$mform->addRule('topic', null, 'alphanumeric', null, 'client');
        $mform->addElement('editor', 'description', get_string('description', 'local_townhall'));
        $mform->setType('description', PARAM_RAW);
        $mform->addRule('description', null, 'required', null, 'client');
        $mform->addElement('advcheckbox', 'publish', get_string('assign', 'local_townhall'), 'Tick to add', array('group' => 1), array(0, 1));
        $mform->addElement('hidden', 'cid', $id);
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
