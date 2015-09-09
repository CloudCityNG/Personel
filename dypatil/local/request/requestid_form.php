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

class requestid_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $requestid = new requests();
        $PAGE->requires->yui_module('moodle-local_request-schoolchooser', 'M.local_request.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $schools = $requestid->school();
        $ass_school = $requestid->assigned_school();
        $count = count($schools);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_RAW);
        // if on school assigned to a user condition
        if ($count == 1) {
            foreach ($schools as $school) {
                $value = $school->fullname;
                $key = $school->id;
            }

            $mform->addElement('hidden', 'schoolid', $key);
            $mform->setType('schoolid', PARAM_RAW);
            $mform->addElement('static', 'school_name', get_string('schoolid', 'local_collegestructure'), $value);

            $mform->addElement('hidden', 'studentid', $USER->id);
            $programs = $requestid->program($key);

            $count1 = count($programs);
            $mform->addElement('hidden', 'count_pros', $count1);
            $mform->setType('count_pros', PARAM_RAW);
            if ($count1 == 1) {
                foreach ($programs as $pro) {
                    $pro_val = $pro->fullname;
                    $pro_key = $pro->id;
                }
                $users = $requestid->users($key);

                $serviceid = $requestid->service($key, $pro_key, $USER->id);
                $mform->addElement('static', 'serviceid', get_string('student_id', 'local_request'), $serviceid->serviceid);
                $mform->addElement('static', 'name', get_string('name', 'local_request'), $users->fullname);

                $mform->addElement('static', 'program_name', get_string('program', 'local_programs'), $pro_val);
                $mform->addElement('hidden', 'programid', $pro_key);
                $mform->setType('programid', PARAM_RAW);
                $semester = $requestid->semester($key, $pro_key);
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
        $mform->addElement('editor', 'reason', get_string("reason_id", "local_request"));
        $mform->setType('reason', PARAM_RAW);

        $mform->addRule('reason', get_string('error_request_id', 'local_request'), 'required', null, 'client');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'studentid', $USER->id);
        $mform->setType('studentid', PARAM_RAW);
        $this->add_action_buttons(true, get_string('submitbutton', 'local_request'));
    }

    function definition_after_data() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $requestid = new requests();
        $id = $this->_customdata['id'];
        $school = $mform->getElementValue('school_name');
        $countvalue = $mform->getElementValue('count');
        $fid = $school[0];
        if ($countvalue == 1) {
            $pro_count = $mform->getElementValue('count_pros');
            if ($pro_count > 1) {
                // single school and more than one program assigned to a user
                $program1 = $mform->getElementValue('program_name1');
                $schoolid = $mform->getElementValue('schoolid');
                $pro_index = $program1[0];
                if ($pro_index > 0 && $pro_count > 1) {
                    $semesteres = $requestid->semester($schoolid, $pro_index);
                    $serviceid = $requestid->service($schoolid, $pro_index, $USER->id);
                    foreach ($semesteres as $s) {
                        $value2 = $s->fullname;
                    }
                    $users = $requestid->users($schoolid);

                    $ser_id = $mform->createElement('static', 'serviceid', get_string('student_id', 'local_request'), $serviceid->serviceid);
                    $mform->insertElementBefore($ser_id, 'school_name');
                    $name = $mform->createElement('static', 'name', get_string('name', 'local_request'), $users->fullname);
                    $mform->insertElementBefore($name, 'school_name');

                    $sem_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $value2);
                    $mform->insertElementBefore($sem_name, 'addschool');
                }
            }
        }
// more than one school assiged to a user
        if ($school[0] > 0 && isset($school) && $countvalue > 1) {

            $users = $requestid->users($fid);
            $programses = $requestid->program($fid);
            $mypro = array();
            $mypro[''] = get_string('programslist', 'local_programs');
            foreach ($programses as $p) {
                $mypro[$p->id] = $p->fullname;
                $proid = $p->id;
            }
            $service = $requestid->service($fid, $proid, $USER->id);
            $ser_id = $mform->createElement('static', 'serviceid', get_string('student_id', 'local_request'), $service->serviceid);
            $mform->insertElementBefore($ser_id, 'addschool');
            $name = $mform->createElement('static', 'name', get_string('name', 'local_request'), $users->fullname);
            $mform->insertElementBefore($name, 'addschool');
            if (count($mypro) == 2) {
                // more than school and one program to user
                $pro_name = $mform->createElement('static', 'program_name', get_string('program', 'local_programs'), $mypro[$p->id]);
                $mform->insertElementBefore($pro_name, 'addschool');
                $pro_id = $mform->createElement('hidden', 'programid', $p->id);
                $mform->insertElementBefore($pro_id, 'addschool');
                $semesteres = $requestid->semester($fid, $p->id);
                foreach ($semesteres as $s) {
                    $mysemvalue = $s->fullname;
                    $mysemid = $s->id;
                }
                $sems_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $mysemvalue);
                $mform->insertElementBefore($sems_name, 'addschool');
                $sem_id = $mform->createElement('hidden', 'semesterid', $mysemid);
                $mform->insertElementBefore($sem_id, 'addschool');
            } else {
                // more than one program and school assined to user
                $pro_name = $mform->createElement('select', 'program_name', get_string('program', 'local_programs'), $mypro);
                $mform->insertElementBefore($pro_name, 'addschool');
            }
            $formatvalue3 = $mform->getElementValue('program_name');
            if ($formatvalue3[0] > 0) {
                $semesteres = $requestid->semester($fid, $formatvalue3[0]);
                foreach ($semesteres as $s) {
                    $mysemvalue = $s->fullname;
                }
                $sem_name = $mform->createElement('static', 'semester_name', get_string('semester', 'local_semesters'), $mysemvalue);
                $mform->insertElementBefore($sem_name, 'addschool');
            }
        }
    }

}

?>