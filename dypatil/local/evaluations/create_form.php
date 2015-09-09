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
 * print the form to add or edit a evaluation-instance
 *
 * @author Naveen kumar
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluations
 */
//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}
require_once($CFG->libdir . '/formslib.php');
require_once('lib.php');

class evaluation_create_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;
        $mform = $this->_form;

        $classid = $this->_customdata['classid'];
        $id = $this->_customdata['id'];

        $evaltype = array(NULL => get_string('evoltype1', 'local_evaluations'),
            '1' => get_string('evoltype2', 'local_evaluations'),
            '2' => get_string('evoltype3', 'local_evaluations'),
            '3' => get_string('evoltype4', 'local_evaluations'));
        // $evaluation = new evaluation(); 
        $PAGE->requires->yui_module('moodle-local_evaluations-evaltype', 'M.local_evaluations.init_evaltype', array(array('formid' => $mform->getAttribute('id'))));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));
        $mform->addElement('text', 'name', get_string('name', 'local_evaluations'), array('size' => '64'));

        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');
        if ($id > 0) {
            $mform->addElement('static', 'evaluationtype', get_string('evaluationtype', 'local_evaluations'));
        } else {
            $mform->addElement('select', 'evaluationtype', get_string('evaluationtype', 'local_evaluations'), $evaltype);
            $mform->addRule('evaluationtype', get_string('missingevaluationtype', 'local_evaluations'), 'required', null, 'client');
        }
        $mform->addElement('hidden', 'addevltinginst');
        $mform->setType('addevltinginst', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'addevltdinst');
        $mform->setType('addevltdinst', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string("description", "local_evaluations"), null);
        $mform->addRule('description', get_string('missingdescription', 'local_evaluations'), 'required', null, 'client');
        //   $this->add_intro_editor(true, get_string('description', 'locsl_evaluation'));
        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'timinghdr', get_string('availability', 'local_evaluations'));
        //$mform->addElement('hidden','classid');
        //$mform->setType('classid',PARAM_INT);
        $mform->addElement('date_time_selector', 'timeopen', get_string('evaluationopen', 'local_evaluations'), array('optional' => true));

        $mform->addElement('date_time_selector', 'timeclose', get_string('evaluationclose', 'local_evaluations'), array('optional' => true));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'evaluationhdr', get_string('questionandsubmission', 'local_evaluations'));

        $options = array();
        $options[1] = get_string('anonymous', 'local_evaluations');
        $options[2] = get_string('non_anonymous', 'local_evaluations');
        $mform->addElement('select', 'anonymous', get_string('anonymous_edit', 'local_evaluations'), $options);

        // check if there is existing responses to this evaluation
        //if (is_numeric($mform->_instance) AND
        //            $mform->_instance AND
        //            $evaluation = $DB->get_record("evaluation", array("id"=>$mform->_instance))) {
        //
        //    $completed_evaluation_count = evaluation_get_completeds_group_count($evaluation);
        //} else {
        //    $completed_evaluation_count = false;
        //}
        //if ($completed_evaluation_count) {
        //    $multiple_submit_value = $evaluation->multiple_submit ? get_string('yes') : get_string('no');
        //    $mform->addElement('text',
        //                       'multiple_submit_static',
        //                       get_string('multiplesubmit', 'local_evaluations'),
        //                       array('size'=>'4',
        //                            'disabled'=>'disabled',
        //                            'value'=>$multiple_submit_value));
        //    $mform->setType('multiple_submit_static', PARAM_RAW);
        //
        //    $mform->addElement('hidden', 'multiple_submit', '');
        //    $mform->setType('multiple_submit', PARAM_INT);
        //    $mform->addHelpButton('multiple_submit_static', 'multiplesubmit', 'local_evaluations');
        //} else {
        $mform->addElement('selectyesno', 'multiple_submit', get_string('multiplesubmit', 'local_evaluations'));

        $mform->addHelpButton('multiple_submit', 'multiplesubmit', 'local_evaluations');
        //   }

        $mform->addElement('selectyesno', 'email_notification', get_string('email_notification', 'local_evaluations'));
        $mform->addHelpButton('email_notification', 'email_notification', 'local_evaluations');

        $mform->addElement('selectyesno', 'autonumbering', get_string('autonumbering', 'local_evaluations'));
        $mform->addHelpButton('autonumbering', 'autonumbering', 'local_evaluations');

        //-------------------------------------------------------------------------------
        //$mform->addElement('header', 'aftersubmithdr', get_string('after_submit', 'local_evaluations'));
        //
        //$mform->addElement('selectyesno', 'publish_stats', get_string('show_analysepage_after_submit', 'local_evaluations'));
        //
        //$mform->addElement('editor',
        //                   'page_after_submit_editor',
        //                   get_string("page_after_submit", "local_evaluations"),
        //                   null,
        //                   $editoroptions);
        //
        //$mform->setType('page_after_submit_editor', PARAM_RAW);
        //
        //$mform->addElement('text',
        //                   'site_after_submit',
        //                   get_string('url_for_continue', 'local_evaluations'),
        //                   array('size'=>'64', 'maxlength'=>'255'));
        //
        //$mform->setType('site_after_submit', PARAM_TEXT);
        //$mform->addHelpButton('site_after_submit', 'url_for_continue', 'local_evaluations');
        //-------------------------------------------------------------------------------
        //    $mform->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // buttons

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        if ($id > 0) {
            $this->add_action_buttons('true', get_string('create', 'local_evaluations'));
        } else {
            $this->add_action_buttons('true', 'Create Eavalution');
        }
    }

    //public function data_preprocessing(&$default_values) {
    //
    //    $editoroptions = evaluation_get_editor_options();
    //
    //    if ($mform->current->instance) {
    //        // editing an existing evaluation - let us prepare the added editor elements (intro done automatically)
    //        $draftitemid = file_get_submitted_draft_itemid('page_after_submit');
    //        $default_values['page_after_submit_editor']['text'] =
    //                                file_prepare_draft_area($draftitemid, $mform->context->id,
    //                                'mod_evaluation', 'page_after_submit', false,
    //                                $editoroptions,
    //                                $default_values['page_after_submit']);
    //
    //        $default_values['page_after_submit_editor']['format'] = $default_values['page_after_submitformat'];
    //        $default_values['page_after_submit_editor']['itemid'] = $draftitemid;
    //    } else {
    //        // adding a new evaluation instance
    //        $draftitemid = file_get_submitted_draft_itemid('page_after_submit_editor');
    //
    //        // no context yet, itemid not used
    //        file_prepare_draft_area($draftitemid, null, 'mod_evaluation', 'page_after_submit', false);
    //        $default_values['page_after_submit_editor']['text'] = '';
    //        $default_values['page_after_submit_editor']['format'] = editors_get_preferred_format();
    //        $default_values['page_after_submit_editor']['itemid'] = $draftitemid;
    //    }
    //
    //}
    //public function get_data() {
    //    $data = parent::get_data();
    //    if ($data) {
    //        $data->page_after_submitformat = $data->page_after_submit_editor['format'];
    //        $data->page_after_submit = $data->page_after_submit_editor['text'];
    //
    //        if (!empty($data->completionunlocked)) {
    //            // Turn off completion settings if the checkboxes aren't ticked
    //            $autocompletion = !empty($data->completion) &&
    //                $data->completion == COMPLETION_TRACKING_AUTOMATIC;
    //            if (!$autocompletion || empty($data->completionsubmit)) {
    //                $data->completionsubmit=0;
    //            }
    //        }
    //    }
    //
    //    return $data;
    //}

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if ($data['timeopen'] && $data['timeclose'] &&
                $data['timeopen'] >= $data['timeclose']) {
            $errors['timeopen'] = get_string('evalbadavailabledates', 'local_evaluations');
        }
        return $errors;
    }

    //public function add_completion_rules() {
    //    $mform =& $mform->_form;
    //
    //    $mform->addElement('checkbox',
    //                       'completionsubmit',
    //                       '',
    //                       get_string('completionsubmit', 'local_evaluation'));
    //    return array('completionsubmit');
    //}
    function definition_after_data() {
        global $DB;
        $mform = $this->_form;
        $classid = $this->_customdata['classid'];
        $id = $this->_customdata['id'];
        $evaltype = $DB->get_record('local_evaluation', array('id' => $id));
        $id > 0 ? $evtype = $evaltype->evaluationtype : $evtype = $mform->getElementValue('evaluationtype');
        if (isset($evtype) && $evtype[0] < 3) {
            $instructors = $DB->get_records_sql("SELECT
                                                    u.id,u.firstname,u.lastname
                                                    FROM
                                                    {user} as u
                                                    JOIN {local_scheduleclass} as ai
                                                    ON u.id=ai.instructorid where ai.classid=$classid");
            $inst = array();
            $inst[NULL] = 'Select instructor';
            foreach ($instructors as $instructor) {
                $inst[$instructor->id] = $instructor->firstname . ' ' . $instructor->lastname;
            }
            $evltype = $mform->createElement('select', 'evaluatinginstructor', get_string('evalinst', 'local_evaluations'), $inst);
            $mform->insertElementBefore($evltype, 'addevltinginst');
            $mform->addRule('evaluatinginstructor', get_string('missingevalinst', 'local_evaluations'), 'required', null, 'client');
            $evalinst_value = $mform->getElementValue('evaluatinginstructor');
        }
        if (isset($evalinst_value) && $evtype[0] == 2 && $evalinst_value[0] != NULL && $evalinst_value[0] != 0) {
            $sevalinginstructor = $evalinst_value[0];
            $sevalinginstr_dep = $DB->get_field('local_dept_instructor', 'departmentid', array('instructorid' => $sevalinginstructor));
            $dep_instructorlist = $DB->get_records_sql('SELECT
                                                                                      u.id,u.firstname,u.lastname
                                                                                      FROM
                                                                                      {user} as u
                                                                                      JOIN {local_dept_instructor} as di
                                                                                      ON u.id=di.instructorid where di.departmentid=' . $sevalinginstr_dep . ' and di.instructorid !=' . $sevalinginstructor . '');
            $edinsl = array();
            foreach ($dep_instructorlist as $dep_inl) {
                $edinsl[$dep_inl->id] = $dep_inl->firstname . '' . $dep_inl->lastname;
            }
            $settings = array('multiple' => 'multiple', 'size' => 10, 'style' => 'width:300px');
            $evltedinst = $mform->createElement('select', 'evaluatedinstructor', get_string('evaltdinst', 'local_evaluations'), $edinsl, $settings);
            $mform->insertElementBefore($evltedinst, 'addevltdinst');
            $mform->addRule('evaluatedinstructor', get_string('missingevaltdinst', 'local_evaluations'), 'required', null, 'client');
            $mform->addHelpButton('evaluatedinstructor', 'evaluatedinstructor', 'local_evaluations');
            $evalinst_value = $mform->getElementValue('evaluatedinstructor');
        }
    }

    //public function completion_rule_enabled($data) {
    //    return !empty($data['completionsubmit']);
    //}
}
