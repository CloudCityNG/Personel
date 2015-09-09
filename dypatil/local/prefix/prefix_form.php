<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class prefix_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        //------used for editing purpose(used to put static schoolname and shortname)
        $eid = $this->_customdata['temp'];
        $hierarchy= new hierarchy();
        $PAGE->requires->yui_module('moodle-local_prefix-hideshow', 'M.local_prefix.init_hideshow', array(array('formid' => $mform->getAttribute('id'))));
        if ($eid->id > 0)
            $mform->addElement('header', 'settingsheader', get_string('editprefix_settings', 'local_prefix'));
        else
            $mform->addElement('header', 'settingsheader', get_string('prefixs', 'local_prefix'));

        $prefix = prefix_suffix::getInstance();
        $entitylist = $hierarchy->get_records_cobaltselect_menu('local_create_entity', '', null, '', 'id,entity_name', '--Select--');
        $mform->addElement('select', 'entityid', get_string('entityid', 'local_prefix'), $entitylist);
        $mform->addRule('entityid', get_string('entityid', 'local_prefix'), 'required', null, 'client');
        //------used for editing purpose(used to put static schoolname )
        if ($eid->id > 0) {
            $school = $DB->get_record('local_school', array('id' => $eid->schoolid));
            $mform->addElement('static', 'esid', get_string('select', 'local_collegestructure'), $school->fullname);

            $program = $DB->get_record('local_program', array('id' => $eid->programid));
            $mform->addElement('static', 'epid', get_string('selectprogram', 'local_programs'), $program->fullname);
        } else {
            $hier = new hierarchy();
            if (is_siteadmin($USER->id)) {
                $schoolids = $DB->get_records('local_school', array('visible' => 1));
            } else {
                $schoolids = $hier->get_assignedschools();
            }
            $count = sizeof($schoolids);
            if ($count > 1) {
                $items = $hier->get_school_items();
                $parents = $hier->get_school_parent($schoolids, '', true);
                $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
                $mform->addRule('schoolid', get_string('select', 'local_collegestructure'), 'required', null, 'client');
                $mform->registerNoSubmitButton('updatecourseformat');
                $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
            } else {
                  
                 foreach($schoolids as $sid){
                  $schoolid=$sid->id;
                  $schoolname=$sid->fullname;
                }          
                $mform->addElement('static', 'sid', get_string('schoolid', 'local_collegestructure'), $schoolname);
                $mform->addElement('hidden', 'schoolid', $schoolid);
            }
        }

        $mform->setType('schoolid', PARAM_INT);
        $mform->addElement('hidden', 'addprogramlisthere');
        $mform->setType('addprogramlisthere', PARAM_INT);

        $mform->addElement('text', 'sequence_no', get_string('sequence', 'local_prefix'));
        $mform->addRule('sequence_no', get_string('spaces', 'local_prefix'), 'regex', '/[^ ]+/', 'client');
        $mform->addRule('sequence_no', get_string('num', 'local_prefix'), 'numeric', null, 'client');
        $mform->addRule('sequence_no', get_string('seq1', 'local_prefix'), 'required', null, 'client');
        $mform->setType('sequence_no', PARAM_RAW);

        $mform->addElement('text', 'prefix', get_string('prefix', 'local_prefix'));
        $mform->addRule('prefix', get_string('spaces', 'local_prefix'), 'regex', '/[^ ]+/', 'client');
        $mform->addRule('prefix', get_string('pre1', 'local_prefix'), 'required', null, 'client');
        $mform->setType('prefix', PARAM_RAW);

        $mform->addElement('text', 'suffix', get_string('suffix', 'local_prefix'));
        $mform->addRule('suffix', get_string('spaces', 'local_prefix'), 'regex', '/[^ ]+/', 'client');
        $mform->addRule('suffix', get_string('suf1', 'local_prefix'), 'required', null, 'client');
        $mform->setType('suffix', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_INT);
        $i = get_string('submit', 'local_prefix');

        $this->add_action_buttons(true, 'Submit');
    }

    function definition_after_data() {
        global $DB;
        $mform = $this->_form;
        $eid = $this->_customdata['temp'];
        if ($eid->id < 0) {
            $school = $mform->getElementValue('schoolid');
    
            $tools = array();
            if ($school[0] > 0) {
                $fid = $school[0];
                $hierarchy = new hierarchy();
                $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$fid AND visible=1", null, '', 'id,fullname', '--Select--');
                $newel = $mform->createElement('select', 'programid', get_string('selectprogram', 'local_programs'), $program);
                $mform->insertElementBefore($newel, 'addprogramlisthere');
                $formatvalue2 = $mform->getElementValue('schoolid');
                $mform->addRule('programid', get_string('selectprogram', 'local_programs'), 'required', null, 'client');
                $formatvalue2 [0];
            }
        }
    }

}
