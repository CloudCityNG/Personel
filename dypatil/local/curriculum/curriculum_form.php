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
 * @subpackage Collegestructure
 * @copyright  2012 Niranjan<niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/curriculum/lib.php');

$hierarchy = new hierarchy();
$cplan = curriculumplan::getInstance();

/// get url variables
class curriculum_form extends moodleform {

    // Define the form
    public function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-local_curriculum-schoolchooser', 'M.local_curriculum.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));

        $editoroptions = $this->_customdata['editoroptions'];
        $id = $this->_customdata['id'];
        if ($id < 0)
            $mform->addElement('header', 'settingsheader', get_string('createcurriculum', 'local_curriculum'));
        else
            $mform->addElement('header', 'settingsheader', get_string('editcurriculum', 'local_curriculum'));

        $tools = array();
        $enddate = date("d/m/Y");
        $startdate = date("d/m/Y");

        $items = $hierarchy->get_school_items();
        $parents = $hierarchy->get_school_parent($items);
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        $mform->setType('schoolid', PARAM_INT);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        $mform->addElement('hidden', 'addprogramslisthere');
        $mform->setType('addprogramslisthere', PARAM_RAW);
        $mform->addElement('text', 'fullname', get_string('curriculumname', 'local_curriculum'), $tools);
        $mform->addRule('fullname', get_string('missingcurriculumname', 'local_curriculum'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('curriculumshortname', 'local_curriculum'), $tools);
        $mform->addRule('shortname', get_string('missingcurriculumshort', 'local_curriculum'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        //Enable Curriculum Plans Starts-----
        $yesno = array('0' => 'No', '1' => 'Yes');
        $set = $mform->addElement('select', 'enableplan', get_string('enableplan', 'local_curriculum'), $yesno);
        $mform->setType('enableplan', PARAM_BOOL);
        if ($id < 0)
            $set->setSelected('1');
        //Enable Curriculum Plans Ends-----

        $mform->addElement('editor', 'description', get_string('description', 'local_curriculum'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);
        //  $startclass='class="EtDate" readonly="readonly"';

        $mform->addElement('date_selector', 'enddate', get_string('validtill', 'local_curriculum'));
        $mform->addRule('enddate', get_string('missingvalidtilldate', 'local_curriculum'), 'required', null, 'client');
        $mform->setType('enddate', PARAM_RAW);

        //displaying the curriculum settings here
        $mform->addElement('hidden', 'addsettinglisthere');
        $mform->setType('addsettinglisthere', PARAM_RAW);


        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('updatecurriculum', 'local_curriculum') : get_string('createcurriculum', 'local_curriculum');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB,$CFG;
        $mform = $this->_form;
        $formatvalue = $mform->getElementValue('schoolid');
        $id = $this->_customdata['id'];
        $tools = array();
        if ($formatvalue) {
            $hierarchy = new hierarchy();
            $curricula = new curricula();
            $formatvalue = $formatvalue[0];
            $tools = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$formatvalue AND visible=1", null, '', 'id,fullname', '--Select--');
            $checkentitysetting = array();
            $checkentitysetting = $hierarchy->get_entitysetting('CL', $formatvalue);

            if ($formatvalue > 0) {              
                if(array_key_exists($tools))
                $newel = $mform->createElement('static', 'programid', get_string('program', 'local_programs'), $hierarchy->cobalt_navigation_msg('No program available yet, Click here ','Create Program',$CFG->wwwroot.'/local/programs/program.php'));
                else
                $newel = $mform->createElement('select', 'programid', get_string('program', 'local_programs'), $tools);
                
                $mform->insertElementBefore($newel, 'addprogramslisthere');
                //$mform->setType('addprogramslisthere', PARAM_RAW);
                $mform->addRule('programid', get_string('missingfullname', 'local_programs'), 'required', null, 'client');
                $mform->setType('id', PARAM_INT);

                /** curriculum settings like credithours are displayed here on selecting the school/university
                 * get_entitysetting() function exect if credit hours are set at curriculum level
                 */
                if ($checkentitysetting) {
                    foreach ($checkentitysetting as $settings) {

                        $level = "CL";
                        $level = $mform->createElement('hidden', 'level', $level);
                        $mform->insertElementBefore($level, 'addsettinglisthere');
                        if ($settings->entityid == 1) {
                            $settingheading = $mform->createElement('header', 'moodle', get_string('settingone', 'local_curriculum'));
                            $mform->insertElementBefore($settingheading, 'addsettinglisthere');
                            $entityids = 1;
                            $entitys = $mform->createElement('hidden', 'entityids', $entityids);
                            $mform->insertElementBefore($entitys, 'addsettinglisthere');

                            $subentitys = $mform->createElement('hidden', 'subentityidse', $entityids);
                            $mform->insertElementBefore($subentitys, 'addsettinglisthere');

                            $credithours = $mform->createElement('text', 'mincrhour', get_string('minch', 'local_curriculum'));
                            $mform->insertElementBefore($credithours, 'addsettinglisthere');
                            $mform->addRule('mincrhour', get_string('missingtotalch', 'local_curriculum'), 'required', null, 'client');
                            $mform->setType('mincrhour', PARAM_RAW);
                        }
                        if ($settings->entityid == 2) {


                            $settingheading = $mform->createElement('header', 'moodle', get_string('settingtwo', 'local_curriculum'));
                            $mform->insertElementBefore($settingheading, 'addsettinglisthere');

                            $entity = 2;
                            $entity = $mform->createElement('hidden', 'entityid', $entity);
                            $mform->insertElementBefore($entity, 'addsettinglisthere');

                            $subentity = $mform->createElement('hidden', 'subentityid', $entityids);
                            $mform->insertElementBefore($subentity, 'addsettinglisthere');

                            $freshman = $mform->createElement('text', 'mincredithours[0]', get_string('freshmancrhr', 'local_curriculum'));

                            $mform->insertElementBefore($freshman, 'addsettinglisthere');

                            $sophomore = $mform->createElement('text', 'mincredithours[1]', get_string('sophomorecrhr', 'local_curriculum'));
                            $mform->insertElementBefore($sophomore, 'addsettinglisthere');
                            $junior = $mform->createElement('text', 'mincredithours[2]', get_string('juniorcrhr', 'local_curriculum'));
                            $mform->insertElementBefore($junior, 'addsettinglisthere');

                            $senior = $mform->createElement('text', 'mincredithours[3]', get_string('seniorcrhr', 'local_curriculum'));
                            $mform->insertElementBefore($senior, 'addsettinglisthere');
                            if ($id < 0) {
                                $mform->setDefault('mincredithours[0]', NULL);
                                $mform->setDefault('mincredithours[1]', NULL);
                                $mform->setDefault('mincredithours[2]', NULL);
                                $mform->setDefault('mincredithours[3]', NULL);
                            }
                        }
                    }
                }
                /*                 * End of the curriculum settings
                 * 
                 */
            }
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $id = $data['id'];
        if ($data['enddate'] < time()) {
            $errors['enddate'] = get_string('validtillvalidation', 'local_curriculum');
        }
        if ($id > 0) {
            if ($DB->record_exists('local_curriculum_plan', array('curriculumid' => $id)) && $data['enableplan'] == 0)
                $errors['enableplan'] = get_string('planscreateddontchange', 'local_curriculum');
            else if ($DB->record_exists('local_curriculum_plancourses', array('curriculumid' => $id, 'planid' => 0)) && $data['enableplan'] == 1)
                $errors['enableplan'] = get_string('coursesassigned', 'local_curriculum');
        } /* Bug -id #259
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * Resolved- providing proper validation, When the user entered exists shortname  */

        if ($id > 0) {
            $sh = ( $data['shortname']);
            $compare_scale_clause = $DB->sql_compare_text("shortname")  . ' = ' . $DB->sql_compare_text(":sh");
            $shortname_exist = $DB->get_records_sql("select * from {local_curriculum} where id!=$id and $compare_scale_clause",array('sh'=>$sh));
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        else {
            if ($DB->record_exists('local_curriculum', array('shortname' => $data['shortname'])))
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        //$DB->record_exists('local_curriculum_plan', array('id'=>$id));
        return $errors;
    }

}

class curriculumplan_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy, $cplan;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $cid = $this->_customdata['cid'];

        $editoroptions = $this->_customdata['editoroptions'];
        $heading = ($id > 0) ? get_string('editplan', 'local_curriculum') : get_string('createplan', 'local_curriculum');

        $mform->addElement('header', 'settingsheader', $heading);
        $mform->addHelpButton('settingsheader', 'createplan', 'local_curriculum');

        $mform->addElement('hidden', 'schoolid');
        $mform->addElement('hidden', 'programid');
        $mform->addElement('hidden', 'curriculumid');
        $mform->setType('schoolid', PARAM_INT);
        $mform->setType('programid', PARAM_INT);
        $mform->setType('curriculumid', PARAM_INT);
        $mform->addElement('static', 'curriculum_name', get_string('curriculum', 'local_curriculum'));

        $parentplan = $cplan->get_parentplans($cid);
        $mform->addElement('select', 'parentid', get_string('parentplan', 'local_curriculum'), $parentplan);

        $mform->addElement('text', 'fullname', get_string('planname', 'local_curriculum'));
        $mform->addRule('fullname', get_string('missingfullname', 'local_curriculum'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $type = array('Year', 'Semester', 'Module');
        $mform->addElement('select', 'type', get_string('plantype', 'local_curriculum'), $type);

        $mform->addElement('editor', 'description', get_string('description', 'local_curriculum'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('update') : get_string('create');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

}

class assigncourse_form extends moodleform {
    #code

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('assigncourses', 'local_curriculum'));
        $PAGE->requires->yui_module('moodle-local_curriculum-schoolchooser', 'M.local_curriculum.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'type', '', 'Program/Module ', 1);
        $radioarray[] = & $mform->createElement('radio', 'type', '', get_string('department', 'local_cobaltcourses'), 2);
        $mform->addGroup($radioarray, 'assigntype', 'Choose', ' ', false);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addprogram');
        $mform->setType('addprogram', PARAM_RAW);

        $mform->addElement('hidden', 'id', $_REQUEST['id']);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'cid', $_REQUEST['cid']);
        $mform->setType('cid', PARAM_INT);
        $mform->addElement('hidden', 'mode', $_REQUEST['mode']);
        $mform->setType('mode', PARAM_RAW);

        //$mform->addElement('select', 'parentid', get_string('parentplan','local_curriculum'), $parentplan);
    }

    function definition_after_data() {
        global $DB, $hierarchy, $cplan;
        $mform = $this->_form;
        $school = $this->_customdata['school'];
        $assigntype = $mform->getElementValue('assigntype');

        if ($assigntype['type'] == 1) {
            list($usql, $params) = $DB->get_in_or_equal($school);
            $programlist = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid $usql AND visible = 1", $params, '', 'id,fullname', 'Select Program');
            $mypgm = $mform->createElement('select', 'pid', get_string('selectprogram', 'local_programs'), $programlist);
            $mform->insertElementBefore($mypgm, 'addprogram');
            $program = $mform->getElementValue('pid');
        }
        if (isset($program) && $program[0] > 0) {
            $modulelist = $hierarchy->get_records_cobaltselect_menu('local_module', "programid=$program[0] AND visible=1", null, '', 'id,fullname', 'Select Module');
            $mymod = $mform->createElement('select', 'moduleid', get_string('selectmodule', 'local_modules'), $modulelist);
            $mform->insertElementBefore($mymod, 'addprogram');
        }
        if ($assigntype['type'] == 2) {
            $departmentlist = $cplan->get_assigned_deptlist($school);
            $mydept = $mform->createElement('select', 'did', get_string('selectdepartment', 'local_cobaltcourses'), $departmentlist);
            $mform->insertElementBefore($mydept, 'addprogram');
        }
    }

}
