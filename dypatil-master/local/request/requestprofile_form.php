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
 * @subpackage requsets(profile_change)
 * @copyright  2013 rajuadla <rajuadla@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class requestprofile_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $requestpro = new requests();
        $PAGE->requires->yui_module('moodle-local_request-schoolchooser', 'M.local_request.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $users = $requestpro->current_user();
        $mform->addElement('header', 'settingsheader', get_string('PresentProfileDetails', 'local_request'));
        $mform->addHelpButton('settingsheader', 'PresentProfileDetails', 'local_request');
        $mform->addElement('hidden', 'userid', $u->id);
        $mform->setType('userid', PARAM_RAW);

        $schools = $requestpro->school();
        $ass_school = $requestpro->assigned_school();
        $count = count($schools);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_RAW);
        // if one school assigned to a user condition
        if ($count == 1) {
            foreach ($schools as $school) {
                $value = $school->fullname;
                $key = $school->id;
            }
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'), $value);

            $users = $requestpro->users($key);

            $mform->addElement('static', 'name', get_string('name', 'local_request'), $users->fullname);
            $mform->addElement('static', 'email_id', get_string('email_id', 'local_request'), $users->email);

            $mform->addElement('hidden', 'schoolid', $key);
            $mform->setType('schoolid', PARAM_RAW);
            $programs = $requestpro->program($key);
            $count1 = count($programs);
            $mform->addElement('hidden', 'count_pros', $count1);
            $mform->setType('count_pros', PARAM_RAW);
            if ($count1 == 1) {
                foreach ($programs as $pro) {
                    $pro_val = $pro->fullname;
                    $pro_key = $pro->id;
                }
                $serviceid = $requestpro->service($key, $pro_key);
                $mform->addElement('static', 'serviceid', get_string('student_id', 'local_request'), $serviceid->serviceid);
                $mform->addElement('static', 'program_name', get_string('program', 'local_programs'), $pro_val);
                $mform->addElement('hidden', 'programid', $pro_key);
                $mform->setType('programid', PARAM_RAW);

                $semester = $requestpro->semester($key, $pro_key);
                foreach ($semester as $ses) {
                    $value1 = $ses->fullname;
                    $key1 = $ses->id;
                }
                $mform->addElement('static', 'semester_name', get_string('semester', 'local_semesters'), $value1);
                $mform->addElement('hidden', 'semesterid', $key1);
                $mform->setType('semesterid', PARAM_RAW);
            } else {
                $pros = array();
                $pros[''] = get_string('selectprogram', 'local_programs');
                foreach ($programs as $pr) {
                    $pros[$pr->id] = $pr->fullname;
                }
                // single school  and more than one program 
                $mform->addElement('select', 'program_name1', get_string('program', 'local_programs'), $pros);
            }
            //end of single assigned to user condition
        } else {
            //if user assigned to more than one school going to defination_after_data function
            $mform->addElement('select', 'school_name', get_string('schoolid', 'local_collegestructure'), $ass_school);
            $mform->addRule('school_name', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        }
        $mform->registerNoSubmitButton('chooseschool');
        $mform->addElement('submit', 'chooseschool', get_string('courseformatudpate'));
        $mform->addElement('hidden', 'addschool');
        $mform->setType('addschool', PARAM_RAW);
        $mform->addElement('header', 'selectfields', get_string('selectfield', 'local_request'));
        $mform->addHelpButton('selectfields', 'selectfield', 'local_request');
        $fieldvalues = array();
        $fieldvalues[''] = get_string("select", "local_request");
        $fieldvalues[0] = get_string('name', 'local_request');
        $fieldvalues[1] = get_string('email_id', 'local_request');
        $mform->addElement('select', 'field_select', get_string('selectfield', 'local_request'), $fieldvalues);
        $mform->addRule('field_select', get_string('field_select', 'local_request'), 'required', null, 'client');
        $mform->registerNoSubmitButton('fieldset');
        $mform->addElement('submit', 'fieldset', get_string('field_select', 'local_request'));
        $mform->addElement('hidden', 'fields');
        $mform->setType('fields', PARAM_RAW);
        $this->add_action_buttons(true, get_string('submitbutton', 'local_request'));
    }

    function definition_after_data() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $requestpro = new requests();
        $school = $mform->getElementValue('school_name');
        $countvalue = $mform->getElementValue('count');
        $fields = $mform->getElementValue('field_select');
        $fid = $school[0];
        if ($countvalue == 1) {
            $pro_count = $mform->getElementValue('count_pros');
            if ($pro_count > 1) {
                // single school and more than one program assigned to a user
                $program1 = $mform->getElementValue('program_name1');
                $schoolid = $mform->getElementValue('schoolid');

                echo $pro_index = $program1[0];
                if ($pro_index > 0 && $pro_count > 1) {
                    $semesteres = $requestpro->semester($schoolid, $pro_index);
                    $serviceid = $requestpro->service($schoolid, $pro_index);

                    foreach ($semesteres as $s) {
                        $value2 = $s->fullname;
                    }
                    $ser_id = $mform->createElement('static', 'serviceid', get_string('student_id', 'local_request'), $serviceid->serviceid);
                    $mform->insertElementBefore($ser_id, 'name');
                    $sem_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $value2);
                    $mform->insertElementBefore($sem_name, 'addschool');
                }
            }
        }
        // more than one school assiged to a user
        if ($school[0] > 0 && isset($school) && $countvalue > 1) {

            $users = $requestpro->users($fid);
            $programses = $requestpro->program($fid);
            $mypro = array();
            $mypro[''] = '--Select--';
            foreach ($programses as $p) {
                $mypro[$p->id] = $p->fullname;
            }
            $service = $requestpro->service($fid, $p->id);
            $username = $mform->createElement('static', 'name', get_string('name', 'local_request'), $users->fullname);
            $mform->insertElementBefore($username, 'addschool');
            $emailid = $mform->createElement('static', 'email_id', get_string('email_id', 'local_request'), $users->email);
            $mform->insertElementBefore($emailid, 'addschool');

            $ser_id = $mform->createElement('static', 'serviceid', get_string('student_id', 'local_request'), $service->serviceid);
            $mform->insertElementBefore($ser_id, 'addschool');
            if (count($mypro) == 2) {
                // more than school and one program to user
                $pro_name = $mform->createElement('static', 'program_name', get_string('program', 'local_programs'), $mypro[$p->id]);
                $mform->insertElementBefore($pro_name, 'addschool');
                $pro_id = $mform->createElement('hidden', 'programid', $p->id);
                $mform->insertElementBefore($pro_id, 'addschool');
                $semesteres = $requestpro->semester($fid, $p->id);
                foreach ($semesteres as $s) {
                    $mysemvalue = $s->fullname;
                }
                $sems_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $mysemvalue);
                $mform->insertElementBefore($sems_name, 'addschool');
                $sem_id = $mform->createElement('hidden', 'semesterid', $s->id);
                $mform->insertElementBefore($sem_id, 'addschool');
            } else {
                // more than one program and school assined to user
                $pro_name = $mform->createElement('select', 'program_name', get_string('program', 'local_programs'), $mypro);
                $mform->insertElementBefore($pro_name, 'addschool');
            }
            $formatvalue3 = $mform->getElementValue('program_name');
            if ($formatvalue3[0] > 0) {
                $semesteres = $requestpro->semester($fid, $formatvalue3[0]);
                foreach ($semesteres as $s) {
                    $mysemvalue = $s->fullname;
                }
                $se_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $mysemvalue);
                $mform->insertElementBefore($se_name, 'addschool');
            }
        }
        if (isset($fields[0]) && $fields[0] != null) {
            if ($fields[0] == 0) {
                $result = $DB->get_record('local_request_profile_change', array('reg_approval' => 0, 'subjectcode' => 1, 'studentid' => $USER->id));
            }if ($fields[0] == 1) {
                $result = $DB->get_record('local_request_profile_change', array('reg_approval' => 0, 'subjectcode' => 2, 'studentid' => $USER->id));
            }
            if (!empty($result)) {
                if ($fields[0] == 0) {
                    $errormsg = $mform->createElement('html', '<div class="alert alert-danger">' . get_string('noteforname', 'local_request') . '</div>');
                    $mform->insertElementBefore($errormsg, 'fields');
                }if ($fields[0] == 1) {
                    $errormsg = $mform->createElement('html', '<div class="alert alert-danger">' . get_string('noteforemail', 'local_request') . '</div>');
                    $mform->insertElementBefore($errormsg, 'fields');
                }
            } else {
                $editheader = $mform->createElement('header', 'settingsheader', get_string('edit_settings', 'local_request'));
                $mform->insertElementBefore($editheader, 'fields');
                $users = $requestpro->current_user();
                $fullname = $users->firstname . " " . $users->lastname;
                if ($fields[0] == 0 && $fields[0] != null) {
                    $username = $mform->createElement('static', 'fullname', get_string('present_data', 'local_request'), $fullname);
                    $mform->insertElementBefore($username, 'fields');
                }
                if ($fields[0] == 1 && $fields[0] != null) {
                    $email_id = $mform->createElement('static', 'email_id', get_string('present_data', 'local_request'), $users->email);
                    $mform->insertElementBefore($email_id, 'fields');
                }
                $changeto = $mform->createElement('text', 'changeto', get_string("request_to_chage", "local_request"));
                $mform->insertElementBefore($changeto, 'fields');
                $mform->addRule('changeto', get_string('error_change_to', 'local_request'), 'required', null, 'client');

                $reason = $mform->createElement('editor', 'reason', get_string("reason_id", "local_request"));
                $mform->setType('reason', PARAM_RAW);
                $mform->insertElementBefore($reason, 'fields');

                $mform->addRule('reason', get_string('error_request_id', 'local_request'), 'required', null, 'client');
            }
        }
    }

}

?>