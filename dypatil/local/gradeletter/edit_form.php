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
require_once($CFG->dirroot . '/local/gradeletter/lib.php');

class edit_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $mform->addElement('header', 'settingsheader', get_string('addeditgradeletter', 'local_gradeletter'));

        $hierarchy = new hierarchy();
        if (is_siteadmin($USER->id)) {
            $schools = $DB->get_records('local_school', array('visible' => 1));
        } else {
            $schools = $hierarchy->get_assignedschools();
        }
        $noofschools = count($schools);

        if ($noofschools > 1) {
            $parents = $hierarchy->get_school_parent($schools);
            $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            $mform->setType('schoolid', PARAM_INT);
        } else {
            foreach($schools as $sl){
                $schoolid  = $sl->id;
                $schoolname= $sl->fullname;
            }            
            $mform->addElement('static', 'schid', get_string('select', 'local_collegestructure'), $schoolname);
            $mform->addElement('hidden', 'schoolid', $schoolid);
            $mform->setType('schoolid', PARAM_INT);
        }

        $mform->addElement('text', 'letter', get_string('lettergrades', 'local_gradeletter'));
        $mform->addRule('letter', get_string('letterreq', 'local_gradeletter'), 'required', null, 'client');
        $mform->addRule('letter', get_string('lettersonly', 'local_gradeletter'), 'lettersonly', null, 'client');
        $mform->setType('letter', PARAM_TEXT);

        $mform->addElement('text', 'markfrom', get_string('markfrom', 'local_gradeletter'));
        $mform->addRule('markfrom', get_string('marksfromreq', 'local_gradeletter'), 'required', null, 'client');
        $mform->addRule('markfrom', null, 'numeric', null, 'client');
        // $mform->addRule('markfrom', get_string('markslengthmin','local_gradeletter'), 'minlength',2, 'client');
        $mform->setType('markfrom', PARAM_INT);

        $mform->addElement('text', 'markto', get_string('markto', 'local_gradeletter'));
        $mform->addRule('markto', get_string('reqmarktoval', 'local_gradeletter'), 'required', null, 'client');
        $mform->addRule('markto', null, 'numeric', null, 'client');
        $mform->addRule('markto', get_string('markslengthmin', 'local_gradeletter'), 'minlength', 2, 'client');
        $mform->setType('markto', PARAM_INT);

        $mform->addRule(array('markfrom', 'markto'), '"Mark from" value should be lesser than "Mark to" value.', 'compare', '<');

        $mform->addElement('text', 'gradepoint', get_string('gradepoint', 'local_gradeletter'));
        $mform->addRule('gradepoint', get_string('gradepointreq', 'local_gradeletter'), 'required', null, 'client');
        $mform->addRule('gradepoint', null, 'numeric', null, 'client');
        $mform->setType('gradepoint', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('updategradeletter', 'local_gradeletter') : get_string('creategradeletter', 'local_gradeletter');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    // perform some extra moodle validations
    function validation($data, $files) {
        global $DB, $CFG, $gletters;
        $errors = parent::validation($data, $files);
        $letterid = $_REQUEST['id'];

        $gletters = graded_letters::getInstance();
        //new entry
        if ($letterid < 0) {

            $mode = 0;
            // to check for existing grade letter
            $letters = $DB->get_record('local_gradeletters', array('schoolid' => $data['schoolid'], 'letter' => $data['letter']));

            @$let1 = core_text::strtolower($letters->letter);
            $let12 = core_text::strtolower($data['letter']);

            if ($let1 == $let12) {
                $errors['letter'] = get_string('letterexists', 'local_gradeletter');
            }

            // to check for existing grade boundaries
            $boundaryerr = $gletters->gradeboundaryexits($data, $mode);
            if ($boundaryerr) {
                foreach ($boundaryerr as $i => $err) {
                    $errors["$i"] = $err;
                }
            }
            // to check for existing grade point
            $gradepointerr = $gletters->gradepointexits($data, $mode);

            if ($gradepointerr) {
                foreach ($gradepointerr as $point => $err) {
                    $errors["$point"] = $err;
                }
            }
        }

        //edit mode
        if ($letterid > 0) {

            $mode = 1;
            $letters = $DB->get_records_sql('select * from {local_gradeletters} where schoolid=' . $data["schoolid"] . ' and id NOT IN(' . $letterid . ')');

            $let1 = core_text::strtolower($data['letter']);

            foreach ($letters as $l2) {
                $let12 =core_text::strtolower($l2->letter);
                if ($let1 == $let12) {
                    $errors['letter'] = get_string('letterexists', 'local_gradeletter');
                }
            }

            // to check for existing grade  boundaries
            $boundaryerr = $gletters->gradeboundaryexits($data, $mode);
            if ($boundaryerr) {
                foreach ($boundaryerr as $i => $err) {
                    $errors["$i"] = $err;
                }
            }
            // to check for existing grade point
            $gradepointerr = $gletters->gradepointexits($data, $mode);
            if ($gradepointerr) {
                foreach ($gradepointerr as $point => $err) {
                    $errors["$point"] = $err;
                }
            }
        }
        if ($data['markfrom'] < 0) {
            $errors['markfrom'] = 'Enter Positive Values Only';
        }
        if ($data['markto'] < 0) {
            $errors['markto'] = 'Enter Positive Values Only';
        }

        return $errors;
    }

}
