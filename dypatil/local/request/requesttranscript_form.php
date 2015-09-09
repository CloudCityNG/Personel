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

class requesttranscript_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $requestid = new requests();
        $schools = $requestid->school();
        foreach ($schools as $school) {
            $value = $school->fullname;
            $key = $school->id;
        }
        $programs = $requestid->program($key);
        foreach ($programs as $pro) {
            $pro_val = $pro->fullname;
            $pro_key = $pro->id;
        }
        $semester = $requestid->previoussemsofuser($key, $pro_key);
        $cur_semester = $requestid->current_sem($key, $pro_key);
        $list = array();
        $list[''] = get_string("select", "local_request");
        foreach ($semester as $ses) {
            $key1 = $ses->id;
            $list[$key1] = $ses->fullname;
        }
        foreach ($cur_semester as $cus_ses) {
            global $sesid;
            $sesid = $cus_ses->id;
        }
        unset($list[$sesid]);
        $mform->addElement('select', 'semester_name', get_string('semester', 'local_semesters'), $list);
        $mform->addRule('semester_name', get_string('missingsemester', 'local_semesters'), 'required', null, 'server');

        $mform->addElement('editor', 'reason', get_string("reason_id", "local_request"));
        $mform->addRule('reason', get_string('error_request_id', 'local_request'), 'required', null, 'client');
        $this->add_action_buttons(true, get_string('submitbutton', 'local_request'));
    }

}

?>