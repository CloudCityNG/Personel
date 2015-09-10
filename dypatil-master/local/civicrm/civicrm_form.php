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
 * @subpackage civicrm
 * @copyright  2013 Niranjan<niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/lib/formslib.php');

/// get url variables
class civicrm_form extends moodleform {

    // Define the form
    public function definition() {
        global $USER, $CFG, $PAGE;

        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('configurecivicrm', 'local_civicrm'));
        $mform->addElement('text', 'civihost', get_string('civihost', 'local_civicrm'), 'maxlength="64" size="25"');
        $mform->setType('civihost', PARAM_MULTILANG);
        $mform->addElement('text', 'civikeys', get_string('civikeys', 'local_civicrm'), 'maxlength="64" size="25"');
        $mform->setType('civikeys', PARAM_MULTILANG);
        $mform->addElement('text', 'civiapikeys', get_string('civiapikeys', 'local_civicrm'), 'maxlength="64" size="25"');
        $mform->setType('civiapikeys', PARAM_MULTILANG);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;
        $errors = parent::validation($data, $files);
        return $errors;
    }

}
