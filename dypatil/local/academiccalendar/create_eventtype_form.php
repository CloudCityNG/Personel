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
 * @subpackage academiccalendar
 * @copyright  2012 Naveen<naveen@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
/* ---Class for creating event types--- */

class eventtype_form extends moodleform {

    public function definition() {
        $mform = $this->_form;
        isset($_REQUEST['id']) ? $id = $_REQUEST['id'] : $id = -1;
        $editoroptions = $this->_customdata['editoroptions'];
        $id > 0 ? $headstring = get_string('editeventtype', 'local_academiccalendar') : $headstring = get_string('createeventtype', 'local_academiccalendar');
        $mform->addElement('header', 'settingsheader', $headstring);
        $id > 0 ? $mform->addElement('text', 'eventtypename', get_string('editeventtype', 'local_academiccalendar')) : $mform->addElement('text', 'eventtypename', get_string('eventtypename', 'local_academiccalendar'));
        $id < 0 ? $mform->addRule('eventtypename', get_string('missingeventtypename', 'local_academiccalendar'), 'required', null, 'client') : null;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setType('eventtypename', PARAM_RAW);
        $id < 0 ? $mform->setType('createtypename', PARAM_RAW) : null;
        $id > 0 ? $this->add_action_buttons(true, 'Update Event Type') : $this->add_action_buttons(false, 'Create Event Type');
    }

}
