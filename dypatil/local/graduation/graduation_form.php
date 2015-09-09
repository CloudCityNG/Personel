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
 * Defines form fields for a gradeletter plugin
 *
 * @package    local
 * @subpackage gradeletter
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');

//require_once($CFG->dirroot.'/local/graduation/lib.php');

class graduation_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        // $mform->addElement('header', 'settingsheader', get_string('addeditgradeletter', 'local_graduation'));

        $PAGE->requires->yui_module('moodle-local_graduation-heirarchyselector', 'M.local_graduation.init_heirarchyselector', array(array('formid' => $mform->getAttribute('id'))));
        $id = $this->_customdata['id'];
        $disable = ($id > 0) ? 'disabled="disabled"' : '';
        $hierarchy = new hierarchy();
        $schools = $hierarchy->get_assignedschools();
        $count = count($schools);

        if ($count > 1) {
            $parents = $hierarchy->get_school_parent($schools);
            $mform->addElement('select', 'schoolid', get_string('schoolname', 'local_scheduleexam'), $parents, $disable);
            if ($id < 0) {
                $mform->addRule('schoolid', get_string('schoolname', 'local_scheduleexam'), 'required', null, 'client');
            }
            $mform->setType('schoolid', PARAM_INT);
        } else {
            $school_name = $DB->get_record('local_school', array('id' => $schools[0]->id));
            $mform->addElement('static', 'schid', get_string('schoolname', 'local_scheduleexam'), $school_name->fullname);
            $mform->addElement('hidden', 'schoolid', $schools[0]->id);
            $mform->setType('schoolid', PARAM_INT);
        }

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        $mform->addElement('hidden', 'addprogramslisthere');
        $mform->setType('addprogramslisthere', PARAM_INT);
        $mform->addElement('hidden', 'addtabledata');
        $mform->setType('addtabledata', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        //  $this->add_action_buttons();
    }

    function definition_after_data() {
        global $DB, $PAGE, $USER, $hierarchy, $exams, $selected_class, $data;
        $hierarchy = new hierarchy();
        $gradesub = new grade_submission();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $selected_school = $mform->getElementValue('schoolid');
        $selected_program = array();
        $disable = ($id > 0) ? 'disabled="disabled"' : '';

        // for programs and semesters in a school
        if ($selected_school[0] > 0) {
            $programs_list = array();
            $programs_list = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$selected_school[0] AND visible=1", null, '', 'id,fullname', '--Select--');
            $programs_listdrop = $mform->createElement('select', 'programid', get_string('programslist', 'local_scheduleexam'), $programs_list, $disable);
            $mform->insertElementBefore($programs_listdrop, 'addprogramslisthere');
            if ($id < 0) {
                $mform->addRule('programid', get_string('programreq', 'local_scheduleexam'), 'required', null, 'client');
            }
            $selected_program = array();
            $selected_program = $mform->getElementValue('programid');
        }

        if ($selected_school[0] > 0 AND $selected_program[0] > 0) {

            $table = new html_table();
            $table->head = array(
                get_string('studentid', 'local_graduation'), get_string('studentname', 'local_gradesubmission'), get_string('program', 'local_programs'), get_string('graduation', 'local_graduation'));
            $table->size = array('30%', '30%', '20%', '19%');
            $table->align = array('left', 'left', 'left', 'center');
            $table->width = '99%';
            $table->data = $data;
            $tabledata = html_writer::table($table);
            $mform->addElement('html', $tabledata);
        }
    }

    // perform some extra moodle validations
    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $letterid = $_REQUEST['id'];
        return $errors;
    }

}
