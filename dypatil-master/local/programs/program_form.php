<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/programs/lib.php');
$hierarchy = new hierarchy();

class createprogram extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $heading = ($id > 0) ? get_string('editprogram', 'local_programs') : get_string('createprogram', 'local_programs');

        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $school = $hierarchy->get_school_items();
        }
        $parents = $hierarchy->get_school_parent($school);

        $PAGE->requires->yui_module('moodle-local_programs-programs', 'M.local_programs.init_programs', array(array('formid' => $mform->getAttribute('id'))));

        $mform->addElement('header', 'settingsheader', $heading);
        if ($id < 0)
            $mform->addHelpButton('settingsheader', 'createprogram', 'local_programs');
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        if (isset($this->_customdata['scid']))
            $mform->getElement('schoolid')->setSelected($this->_customdata['scid']);

        if (isset($this->_customdata['scid'])) {
            $mform->addElement('hidden', 'scid', $this->_customdata['scid']);
            $mform->setType('scid', PARAM_INT);
        }

        $mform->addElement('hidden', 'adddepartment');
        $mform->setType('adddepartment', PARAM_RAW);

        $mform->addElement('text', 'fullname', get_string('programname', 'local_programs'));
        $mform->addRule('fullname', get_string('missingfullname', 'local_programs'), 'required', null, 'client');
        $mform->addHelpButton('fullname', 'programname', 'local_programs');
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('shortname', 'local_programs'));
        $mform->addRule('shortname', get_string('missingshortname', 'local_programs'), 'required', null, 'client');
        $mform->addHelpButton('shortname', 'shortname', 'local_programs');
        $mform->setType('shortname', PARAM_RAW);
        
        $type = array('1' => 'Online', '2' => 'Offline');
        $mform->addElement('select', 'type', get_string('programtype', 'local_programs'), $type);
        $mform->addHelpButton('type', 'programtype', 'local_programs');
        $mform->addElement('static', 'programtypeinfo', '', get_string('static_prgtypeinfo','local_programs'));

        $level = array('1' => 'Undergraduate', '2' => 'Post Graduate');
        $mform->addElement('select', 'programlevel', get_string('programlevel', 'local_programs'), $level);
        $mform->addHelpButton('programlevel', 'programlevel', 'local_programs');

        $mform->addElement('hidden', 'addduration');
        $mform->setType('addduration', PARAM_RAW);

        $mform->addElement('editor', 'description', get_string('description', 'local_programs'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        //displaying the program settings here
        $mform->registerNoSubmitButton('updatesettings');
        $mform->addElement('submit', 'updatesettings', get_string('coursesettings'));
        $mform->addElement('hidden', 'addsettinglisthere');
        $mform->setType('addsettinglisthere', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submitlable = ($id > 0) ? get_string('updateprogram', 'local_programs') : get_string('createprogram', 'local_programs');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];    
        $schoolvalue = $mform->getElementValue('schoolid');
        $selectedschoolid = $schoolvalue[0];
        $prolevel = $mform->getElementValue('programlevel');
        $prolevel = $prolevel[0];
        if ($id < 0) {
            $department = 0;
            $department = array();
            if (/* $countvalue!=1 && */is_array($schoolvalue) && !empty($schoolvalue) && $schoolvalue[0] > 0 && $department) {
                $department = $hierarchy->get_departments_forschool($schoolvalue[0], $none = true, $top = false);
                $dept = array();
                $dept[] = & $mform->createElement('select', 'departmentid', '', $department);
                //$dept[] =& $mform->createElement('static', 'suggestion', '', get_string('suggestion', 'local_programs'));
                $myselect = $mform->createElement('group', 'departmentfield', get_string('assigntodepartment', 'local_programs'), $dept, '  ', false);

                $mform->insertElementBefore($myselect, 'adddepartment');
                $mform->addHelpButton('departmentfield', 'department', 'local_programs');
                //$mform->addRule('departmentfield', get_string('missingdepartment','local_programs'), 'required', null, 'client');
            }
        }
        $i = 1;
        if ($level = $mform->getElementValue('programlevel')) {
            $i = $level[0];
        }
        $dur = $i == 1 ? 4 : 2;
        if ($id > 0)
            $dur = $this->_customdata['duration'];

        $size = 'style="width:50px !important;"';
        $duration = array();
        $duration[] = & $mform->createElement('text', 'duration_' . $i, '', $size);
        //$duration[] =& $mform->createElement('static', 'example', '', get_string('example', 'local_programs'));

        /* Bug -id #85
         * @author hemalatha c arun
         * @resolved(added drop down form element to select program duration format)   
         */
        $duration_format = array('Y' => 'Years', 'M' => 'Months');
        $duration[] = & $mform->createElement('select', 'duration_format', get_string('forumtype', 'forum'), $duration_format);

        $myduration = $mform->createElement('group', 'durationfield', get_string('programduration', 'local_programs'), $duration, '  ', false);
        $mform->insertElementBefore($myduration, 'addduration');
        //$grouprule = array();
        //$grouprule['duration_'.$i][] = array(get_string('missingduration','local_programs'), 'required', null, 'client');
        // $mform->addGroupRule('duration_'.$i, $templaterules);
        // $mform->addRule('duration_'.$i, get_string('missingduration','local_programs'), 'required', null, 'server');
        $mform->setType('duration_' . $i, PARAM_INT);
        $mform->setDefault('duration_' . $i, $dur);


        /** program settings like credithours are displayed here on selecting the school/university
         * get_entitysetting() function exect if credit hours are set at curriculum level
         */
        if ($id > 0) {
            $selectedschoolid = $DB->get_record('local_program', array('id' => $id));
            $selectedschoolid = $selectedschoolid->schoolid;
        }
        
        if ($selectedschoolid/* && $countvalue!=1 */) {
            $checkentitysetting = $hierarchy->get_entitysetting('PL', $selectedschoolid);
            if ($checkentitysetting) {
                foreach ($checkentitysetting as $settings) {
                    $level = "PL";
                    $level = $mform->createElement('hidden', 'level', $level);

                    $mform->insertElementBefore($level, 'addsettinglisthere');
                    if ($settings->entityid == 1) {
                        $settingheading = $mform->createElement('header', 'moodle', get_string('settingone', 'local_curriculum'));
                        $mform->insertElementBefore($settingheading, 'addsettinglisthere');

                        //print_object($prolevel);
                        $entityids = 1;
                        $entitys = $mform->createElement('hidden', 'entityids', $entityids);
                        $mform->insertElementBefore($entitys, 'addsettinglisthere');

                        $subentitys = $mform->createElement('hidden', 'subentityidse', $prolevel);
                        $mform->insertElementBefore($subentitys, 'addsettinglisthere');
                        $mform->setDefault('subentityidse', $prolevel);
                        $credithours = $mform->createElement('text', 'mincrhour', get_string('minch', 'local_curriculum'));
                        $mform->insertElementBefore($credithours, 'addsettinglisthere');
                        $mform->addRule('mincrhour', get_string('missingtotalch', 'local_curriculum'), 'required', null, 'client');
                        $mform->setType('mincrhour', PARAM_INT);
                    }
                    if ($settings->entityid == 2) {


                        $settingheading = $mform->createElement('header', 'moodle', get_string('settingtwo', 'local_curriculum'));
                        $mform->insertElementBefore($settingheading, 'addsettinglisthere');

                        $entity = 2;
                        $entity = $mform->createElement('hidden', 'entityid', $entity);
                        $mform->insertElementBefore($entity, 'addsettinglisthere');

                        $subentity = $mform->createElement('hidden', 'subentityid', $prolevel);
                        $mform->insertElementBefore($subentity, 'addsettinglisthere');
                        $mform->setDefault('subentityid', $prolevel);
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
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $schoolid = $data['schoolid'];
        $fullname = $data['fullname'];
        $shortname = $data['shortname'];
        if (isset($data['duration_1']))
            $duration = $data['duration_1'];
        if (isset($data['duration_2']))
            $duration = $data['duration_2'];

        $id = $data['id'];

        if ($duration < 0) {
            $errors['durationfield'] = 'Please Enter Positive Value';
        }
        $record = $DB->get_record_sql('SELECT * FROM {local_program} WHERE shortname = ? AND schoolid = ? AND id <> ?', array($shortname, $schoolid, $id));
        //$sql = "SELECT * FROM {local_program} WHERE shortname = \"{$shortname}\" and schoolid = {$schoolid} and id != {$id}";
        // $record = $DB->get_record_sql($sql);
        if (!empty($record)) {
            $errors['shortname'] = get_string('shortnameexists', 'local_programs');
        }
        $programname = $DB->get_record_sql('SELECT * FROM {local_program} WHERE fullname = ? AND schoolid = ? AND id <> ?', array($fullname, $schoolid, $id));
        //$programname = $DB->get_record_sql("SELECT * FROM {local_program} WHERE fullname = \"{$fullname}\" and schoolid = {$schoolid} and id != {$id}");
        if (!empty($programname)) {
            $errors['fullname'] = get_string('fullnameexists', 'local_programs');
        }
        /* Bug -id #85
         * @author hemalatha c arun
         * @resolved(added proper validation messages)   
         */
        if (!is_numeric($duration)) {
            $errors['durationfield'] = get_string('numericduration', 'local_programs');
        } else if ($duration <= 0) {
            $errors['durationfield'] = get_string('nonzeroduration', 'local_programs');
        } else {
            if (empty($duration)) {
                $errors['durationfield'] = get_string('missingduration', 'local_programs');
            }
        }
        if (isset($data['mincrhour'])) {
            if (!is_numeric($data['mincrhour'])) {
                $errors['mincrhour'] = 'Credit Hours must be in numeric';
            }
            if ($data['mincrhour'] <= 0) {
                $errors['mincrhour'] = 'Credit Hours must be greater than zero and Numercic';
            }
        }
        return $errors;
    }

}
