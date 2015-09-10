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
 * prints the forms to choose an item-typ to create items and to choose a template to use
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package evaluation
 */
//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->libdir . '/formslib.php');

class evaluation_edit_add_question_form extends moodleform {

    public function definition() {
        $mform = $this->_form;

        //headline
        $mform->addElement('header', 'general', get_string('content'));
        // visible elements
        $evaluation_names_options = evaluation_load_evaluation_items_options();

        $attributes = 'onChange="M.core_formchangechecker.set_form_submitted(); this.form.submit()"';
        $mform->addElement('select', 'typ', '', $evaluation_names_options, $attributes);

        // hidden elements
        $mform->addElement('hidden', 'clid');
        $mform->setType('clid', PARAM_INT);
        $mform->addElement('hidden', 'cmid');
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'position');
        $mform->setType('position', PARAM_INT);

        // buttons
        $mform->addElement('submit', 'add_item', get_string('add_item', 'local_evaluations'), array('class' => 'hiddenifjs'));
    }

}

class evaluation_edit_use_template_form extends moodleform {

    private $evaluationdata;

    public function definition() {
        $this->evaluationdata = new stdClass();
        //this function can not be called, because not all data are available at this time
        //I use set_form_elements instead
    }

    //this function set the data used in set_form_elements()
    //in this form the only value have to set is class
    //eg: array('class' => $class)
    public function set_evaluationdata($data) {
        if (is_array($data)) {
            if (!isset($this->evaluationdata)) {
                $this->evaluationdata = new stdClass();
            }
            foreach ($data as $key => $val) {
                $this->evaluationdata->{$key} = $val;
            }
        }
    }

    //here the elements will be set
    //this function have to be called manually
    //the advantage is that the data are already set
    public function set_form_elements() {
        $mform = & $this->_form;

        $elementgroup = array();
        //headline
        $mform->addElement('header', 'using_templates', get_string('using_templates', 'local_evaluations'));
        // hidden elements
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'clid');
        $mform->setType('clid', PARAM_INT);

        // visible elements
        $templates_options = array();
        $owntemplates = evaluation_get_template_list($this->evaluationdata->clid, 'own');
        $publictemplates = evaluation_get_template_list($this->evaluationdata->clid, 'public');

        $options = array();
        if ($owntemplates or $publictemplates) {
            //      $options[''] = array('' => get_string('choose','local_evaluations'));

            if ($owntemplates) {
                $classoptions = array();
                foreach ($owntemplates as $template) {
                    $classoptions[$template->id] = $template->name;
                }
                $options[get_string('class', 'local_clclasses')] = $classoptions;
            }

            if ($publictemplates) {
                $publicoptions = array();
                foreach ($publictemplates as $template) {
                    $publicoptions[$template->id] = $template->name;
                }
                $options[get_string('public', 'local_evaluations')] = $publicoptions;
            }

            $attributes = 'onChange="M.core_formchangechecker.set_form_submitted(); this.form.submit()"';
            $elementgroup[] = $mform->createElement('selectgroups', 'templateid', '', $options, $attributes);

            $elementgroup[] = $mform->createElement('submit', 'use_template', get_string('use_this_template', 'local_evaluations'));

            $mform->addGroup($elementgroup, 'elementgroup', '', array(' '), false);
        } else {
            $mform->addElement('static', 'info', get_string('no_templates_available_yet', 'local_evaluations'));
        }
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if ($data['templateid'] && $data['templateid'] = '') {
            $errors['templateid'] = get_string('missingtemplate', 'local_evaluations');
        }
        return $errors;
    }

}

class evaluation_edit_create_template_form extends moodleform {

    private $evaluationdata;

    public function definition() {
        
    }

    public function data_preprocessing(&$default_values) {
        $default_values['templatename'] = '';
    }

    public function set_evaluationdata($data) {
        if (is_array($data)) {
            if (!isset($this->evaluationdata)) {
                $this->evaluationdata = new stdClass();
            }
            foreach ($data as $key => $val) {
                $this->evaluationdata->{$key} = $val;
            }
        }
    }

    public function set_form_elements() {
        $mform = & $this->_form;

        // hidden elements
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'clid');
        $mform->setType('clid', PARAM_INT);
        $mform->addElement('hidden', 'do_show');
        $mform->setType('do_show', PARAM_INT);
        $mform->addElement('hidden', 'savetemplate', 1);
        $mform->setType('savetemplate', PARAM_INT);

        //headline
        $mform->addElement('header', 'creating_templates', get_string('creating_templates', 'local_evaluations'));

        // visible elements
        $elementgroup = array();

        $elementgroup[] = $mform->createElement('static', 'templatenamelabel', get_string('name', 'local_evaluations'));

        $elementgroup[] = $mform->createElement('text', 'templatename', get_string('name', 'local_evaluations'), array('size' => '40', 'maxlength' => '200'));

        //  if (has_capability('mod/evaluation:createpublictemplate', get_system_context())) {
        $elementgroup[] = $mform->createElement('checkbox', 'ispublic', get_string('public', 'local_evaluations'), get_string('public', 'local_evaluations'));
        //  }
        // buttons
        $elementgroup[] = $mform->createElement('submit', 'create_template', get_string('save_as_new_template', 'local_evaluations'));

        $mform->addGroup($elementgroup, 'elementgroup', get_string('name', 'local_evaluations'). '<font style="color:red;"> * </font>', array(' '), false);
        $templaterules = array();
        $templaterules['templatename'][] = array(get_string('missingtemplatenane', 'local_evaluations'), 'required', null, 'client');
        /*
         * ###Bug report #186  -  Evaluations
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Added required condition to the template name
         */
        $mform->addGroupRule('elementgroup', $templaterules);

        $mform->setType('templatename', PARAM_TEXT);
    }

}
