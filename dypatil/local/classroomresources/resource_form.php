<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');

//Class for building creation
class createbuilding_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;

        $heading = ($this->_customdata['id'] > 0) ? get_string('editbuilding', 'local_classroomresources') : get_string('createbuilding', 'local_classroomresources');
        $mform->addElement('header', 'settingsheader', $heading);
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }

        $count = count($scho);
        $school = $hierarchy->get_school_parent($scho);
        $c = 'class="one";style="border:none";readonly="readonly";';

        if ($this->_customdata['id'] > 0) {
            $mform->addElement('text', 'schoolid', get_string('schoolid', 'local_collegestructure'), $c);
            $mform->setType('schoolid', PARAM_RAW);
        } else {


            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
        }
        $mform->addElement('text', 'fullname', get_string('fullname', 'local_classroomresources'));
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_classroomresources'));
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_classroomresources'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submit = ($this->_customdata['id'] > 0) ? 'Update Building' : 'Create Building';
        $this->add_action_buttons('false', $submit);
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();



        if ($data['id'] < 0) {
            $bname = mysql_real_escape_string($data['fullname']);
            $sql = "SELECT * FROM {local_building} WHERE fullname='{$bname}' AND schoolid={$data['schoolid']}";
            $building = $DB->get_records_sql($sql);
            $count = count($building);
            if ($count > 0) {
                $errors['fullname'] = 'Building Already Exist';
            }
            /* Bug report #278 -Buildings>Short Name-Cannot be same
             * @author hemalatha c arun <hemalatha@eabyas.in> 
             * Resolved- removed the  duplication building shortname 
             */
            if ($DB->record_exists('local_building', array('shortname' => $data['shortname'])))
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
            //-------------------------------
        }
        if ($data['id'] > 0) {
            $schoolid = $DB->get_field('local_building', 'schoolid', array('id' => $data['id']));
            $buildingname = $DB->get_field('local_building', 'fullname', array('id' => $data['id']));
            $bname = mysql_real_escape_string($data['fullname']);
            if (strtolower($buildingname) != strtolower($data['fullname'])) {
                $sql = "SELECT * FROM {local_building} WHERE fullname='{$bname}' AND schoolid={$schoolid}";
                $building = $DB->get_records_sql($sql);
                $count = count($building);
                if ($count > 0) {
                    $errors['fullname'] = 'Building Already Exist';
                }
            }
            // Edited by hema
            $sh = ( $data['shortname']);
            $id = $data['id'];
            $shortname_exist = $DB->get_records_sql("select * from {local_building} where id != $id and shortname = '{$sh}'");
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
            //---------------------------------
        }
        return $errors;
    }

}

//Class for floor creation
class createfloor_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;
        $mform = & $this->_form;

        $heading = ($this->_customdata['id'] > 0) ? get_string('editfloor', 'local_classroomresources') : get_string('createfloor', 'local_classroomresources');
        $mform->addElement('header', 'settingsheader', $heading);
        $c = 'class="one";style="border:none";readonly="readonly";';
        /*
         * ###Bugreport #  classroom management and Bug report #280 Editing Tools
         * @hemalatha c arun<hemalatha@eabyas.in>
         * (Resolved) removing unwanted condition while loading yui and avoiding calling yui while editing floor.
         */
        $id = $this->_customdata['id'];
        if ($id <= 0) {
            $PAGE->requires->yui_module('moodle-local_classroomresources-building', 'M.local_classroomresources.init_building', array(array('formid' => $mform->getAttribute('id'))));
        }
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        $school = $hierarchy->get_school_parent($scho);

        if ($this->_customdata['id'] > 0) {
            $mform->addElement('text', 'schoolid', get_string('schoolid', 'local_collegestructure'), $c);
            $mform->setType('schoolid', PARAM_RAW);
            $mform->addElement('text', 'buildingid', get_string('buildingid', 'local_classroomresources'), $c);
            $mform->setType('buildingid', PARAM_RAW);
        } else {

            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
            $mform->addElement('hidden', 'beforefloor');
            $mform->setType('beforefloor', PARAM_RAW);
            
            $mform->addElement('hidden', 'beforefloor_empty');
            $mform->setType('beforefloor_empty', PARAM_RAW);
            
            $mform->registerNoSubmitButton('updatecourseformat');
            $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        }

        $mform->addElement('text', 'fullname', get_string('floorname', 'local_classroomresources'));
        $mform->setType('fullname', PARAM_RAW);
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_classroomresources'));
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_classroomresources'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submit = ($this->_customdata['id'] > 0) ? 'Update Floor' : 'Create Floor';
        $this->add_action_buttons('false', $submit);
    }

    function definition_after_data() {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $mform = $this->_form;
        $sid = $mform->getElementValue('schoolid');
        if (isset($sid) && !empty($sid) && $sid[0] > 0) {
            $resource = cobalt_resources::get_instance();
            $building = $hierarchy->get_records_cobaltselect_menu('local_building', "schoolid=$sid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
            $one = $mform->createElement('select', 'buildingid', get_string('buildingid', 'local_classroomresources'), $building);
            $mform->insertElementBefore($one, 'beforefloor');
            if (count($building) <= 1) {
                $navigationmsg = get_string('navigation_info', 'local_collegestructure');
                $linkname = get_string('create', 'local_classroomresources');
                $navigationlink = $CFG->wwwroot . '/local/classroomresources/building.php?linkschool=' . $sid[0] . '';
                $oneempty = $mform->createElement('static', 'buildingid_empty', '', $hierarchy->cobalt_navigation_msg($navigationmsg, $linkname, $navigationlink, 'margin-bottom: 0;
line-height: 0px;'));
                $mform->insertElementBefore($oneempty, 'beforefloor_empty');
            }
        }
    }

    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();


        if ($data['buildingid'] == 0 && $data['id'] < 0) {
            $errors['buildingid'] = 'Select Building Name';
        }
        if ($data['id'] < 0 && isset($data['fullname']) && $data['schoolid'] > 0 && $data['buildingid'] > 0) {
            $fname = mysql_real_escape_string($data['fullname']);
            $sql = "SELECT * FROM {local_floor} 
		      WHERE fullname='{$fname}' AND 
			  schoolid={$data['schoolid']} AND 
			  buildingid={$data['buildingid']}";
            $floor = $DB->get_records_sql($sql);
            $count = count($floor);
            if ($count > 0) {
                $errors['fullname'] = 'Floor Already Exist!';
            }
        }
        if ($data['id'] > 0) {
            $schoolid = $DB->get_field('local_floor', 'schoolid', array('id' => $data['id']));
            $buildingid = $DB->get_field('local_floor', 'buildingid', array('id' => $data['id']));
            $floorname = $DB->get_field('local_floor', 'fullname', array('id' => $data['id']));
            if (strtolower($floorname) != strtolower($data['fullname'])) {
                $fname = mysql_real_escape_string($data['fullname']);
                $sql = "SELECT * FROM {local_floor} WHERE fullname='{$fname}' AND schoolid={$schoolid} AND buildingid={$buildingid}";
                $building = $DB->get_records_sql($sql);
                $count = count($building);
                if ($count > 0) {
                    $errors['fullname'] = 'Floor Already Exist';
                }
            }
        }

        /* Bug report #279 -Floor>Short Name-Cannot be same
         * @author hemalatha c arun <hemalatha@eabyas.in> 
         * Resolved- removed the  duplication of floor shortname 
         */
        $id = $data['id'];
        if ($id > 0) {
            $sh = ( $data['shortname']);
            $shortname_exist = $DB->get_records_sql("select * from {local_floor} where id!=$id and shortname = '{$sh}'");
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        else {
            if ($DB->record_exists('local_floor', array('shortname' => $data['shortname'])))
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }

        return $errors;
    }

}

//Class for Classroom Creation

class createclassroom_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;
        $mform = & $this->_form;
        $id = $this->_customdata['id'];
        $heading = ($this->_customdata['id'] > 0) ? get_string('editclassroom', 'local_classroomresources') : get_string('createclassroom', 'local_classroomresources');
        $mform->addElement('header', 'settingsheader', $heading);
        $c = 'class="one";style="border:none";readonly="readonly";';
        // echo 'idjlj'.$id;
        /*
         * ###Bugreport #  classroom management
         * @hemalatha c arun<hemalatha@eabyas.in>
         * (Resolved) removing unwanted condition while loading yui.
         */
        if ($id < 0) {
            $PAGE->requires->yui_module('moodle-local_classroomresources-building', 'M.local_classroomresources.init_building', array(array('formid' => $mform->getAttribute('id'))));
            $PAGE->requires->yui_module('moodle-local_classroomresources-floor', 'M.local_classroomresources.init_floor', array(array('formid' => $mform->getAttribute('id'))));
        }
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        $school = $hierarchy->get_school_parent($scho);
        if ($this->_customdata['id'] > 0) {
            $mform->addElement('text', 'schoolid', get_string('schoolname', 'local_collegestructure'), $c);
            $mform->setType('schoolid', PARAM_RAW);
            $mform->addElement('text', 'buildingid', get_string('buildingname', 'local_classroomresources'), $c);
            $mform->setType('buildingid', PARAM_RAW);
            $mform->addElement('text', 'floorid', get_string('floorname', 'local_classroomresources'), $c);
            $mform->setType('floorid', PARAM_RAW);
        } else {

            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');

            $mform->addElement('hidden', 'beforefloor');
            $mform->setType('beforefloor', PARAM_RAW);

            $mform->addElement('hidden', 'beforefloor_empty');
            $mform->setType('beforefloor_empty', PARAM_RAW);

            $mform->addElement('hidden', 'beforeclass');
            $mform->setType('beforeclass', PARAM_RAW);

            $mform->addElement('hidden', 'beforeclass_empty');
            $mform->setType('beforeclass_empty', PARAM_RAW);

            $mform->registerNoSubmitButton('updatecourseformat');
            $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        }
        $mform->addElement('text', 'fullname', get_string('classroomname', 'local_classroomresources'));
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_classroomresources'));
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_classroomresources'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submit = ($this->_customdata['id'] > 0) ? 'Update Classroom' : 'Create Classroom';
        $this->add_action_buttons('false', $submit);
    }

    function definition_after_data() {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $mform = $this->_form;
        $sid = $mform->getElementValue('schoolid');
        if (isset($sid) && !empty($sid) && $sid[0] > 0) {
            $resource = cobalt_resources::get_instance();
            $building = $hierarchy->get_records_cobaltselect_menu('local_building', "schoolid=$sid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));

            $one = $mform->createElement('select', 'buildingid', get_string('buildingid', 'local_classroomresources'), $building);
            $mform->insertElementBefore($one, 'beforefloor');
            if (count($building) <= 1) {
                $navigationmsg = get_string('navigation_info', 'local_collegestructure');
                $linkname = get_string('create', 'local_classroomresources');
                $navigationlink = $CFG->wwwroot . '/local/classroomresources/building.php?linkschool=' . $sid[0] . '';
                $oneempty = $mform->createElement('static', 'buildingid_empty', '', $hierarchy->cobalt_navigation_msg($navigationmsg, $linkname, $navigationlink, 'margin-bottom: 0;
line-height: 0px;'));

                $mform->insertElementBefore($oneempty, 'beforefloor_empty');
            }
        }
        if (isset($one)) {
            $bid = $mform->getElementValue('buildingid');
            if (isset($bid) && !empty($bid) && $bid[0] > 0) {
                $resource = cobalt_resources::get_instance();
                $floor = $hierarchy->get_records_cobaltselect_menu('local_floor', "buildingid=$bid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
                $two = $mform->createElement('select', 'floorid', get_string('floorname', 'local_classroomresources'), $floor);
                $mform->insertElementBefore($two, 'beforeclass');
                if (count($floor) <= 1) {
                    $navigationmsg = get_string('navigation_info', 'local_collegestructure');
                    $linkname = get_string('createfloor', 'local_classroomresources');
                    $navigationlink = $CFG->wwwroot . '/local/classroomresources/floor.php?linkschool=' . $sid[0] . '&linkbuild=' . $bid[0] . '';
                    $floorempty = $mform->createElement('static', 'floorid_empty', '', $hierarchy->cobalt_navigation_msg($navigationmsg, $linkname, $navigationlink, 'margin-bottom: 0;
line-height: 0px;'));

                    $mform->insertElementBefore($floorempty, 'beforeclass_empty');
                }
            }
        }
    }

    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();

        if ($data['buildingid'] == 0 && $data['id'] < 0) {
            $errors['buildingid'] = 'Select Building Name ';
        }
        if ($data['floorid'] == 0 && $data['id'] < 0) {
            $errors['floorid'] = 'select Floor Name';
        }
        if ($data['id'] < 0 && isset($data['fullname']) && $data['schoolid'] > 0 && $data['buildingid'] > 0 && $data['floorid'] > 0) {
            $cname = mysql_real_escape_string($data['fullname']);
            $sql = "SELECT * FROM {local_classroom} 
		      WHERE fullname='{$cname}' AND 
			  schoolid={$data['schoolid']} AND 
			  buildingid={$data['buildingid']} AND floorid={$data['floorid']}";
            $floor = $DB->get_records_sql($sql);
            $count = count($floor);
            if ($count > 0) {
                $errors['fullname'] = 'Classroom Already Exist!';
            }
        }
        if ($data['id'] > 0) {
            $schoolid = $DB->get_field('local_classroom', 'schoolid', array('id' => $data['id']));
            $buildingid = $DB->get_field('local_classroom', 'buildingid', array('id' => $data['id']));
            $floorid = $DB->get_field('local_classroom', 'floorid', array('id' => $data['id']));
            $classname = $DB->get_field('local_classroom', 'fullname', array('id' => $data['id']));
            if (strtolower($classname) != strtolower($data['fullname'])) {
                $cname = mysql_real_escape_string($data['fullname']);
                $sql = "SELECT * FROM {local_classroom} WHERE fullname='{$cname}' AND schoolid={$schoolid} AND buildingid={$buildingid} AND floorid={$floorid}";
                $class = $DB->get_records_sql($sql);
                $count = count($class);
                if ($count > 0) {
                    $errors['fullname'] = 'Room Already Exist';
                }
            }
        }
        /* Bug report #281
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * Resolved -added restrictions that not to add same classroom shortname
         */

        $id = $data['id'];
        $shortname = $data['shortname'];
        if ($id > 0) {
            $classroomid = $DB->get_record_sql("SELECT * FROM {local_classroom} WHERE id != {$id} AND shortname = '{$shortname}' ");
        } else {
            $classroomid = $DB->get_record_sql("SELECT * FROM {local_classroom} WHERE shortname = '{$shortname}' ");
            // AND id != $id       
        }
        if (!empty($classroomid)) {
            $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        //---------------------------------------------------------------------------------
        return $errors;
    }

}

//Class for creation of resource 
class createresource_form extends moodleform {

    public function definition() {
        global $CFG, $DB;
        $mform = & $this->_form;
        $heading = ($this->_customdata['id'] > 0) ? get_string('editresource', 'local_classroomresources') : get_string('createresource', 'local_classroomresources');
        $mform->addElement('header', 'settingsheader', $heading);
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        $c = 'class="one";style="border:none";readonly="readonly";';
        $school = $hierarchy->get_school_parent($scho);
        if ($this->_customdata['id'] > 0) {
            $mform->addElement('text', 'schoolid', get_string('schoolid', 'local_collegestructure'), $c);
            $mform->setType('schoolid', PARAM_RAW);
        } else {

            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');
        }
        $mform->addElement('text', 'fullname', get_string('resourcename', 'local_classroomresources'));
        $mform->addRule('fullname', get_string('required'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);
        $mform->addElement('text', 'shortname', get_string('shortname', 'local_classroomresources'));
        $mform->addRule('shortname', get_string('required'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_classroomresources'));
        $mform->addRule('description', get_string('required'), 'required', null, 'client');
        $mform->setType('description', PARAM_RAW);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submit = ($this->_customdata['id'] > 0) ? 'Update Resource' : 'Create Resource';
        $this->add_action_buttons('false', $submit);
    }

    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();

        if ($data['id'] < 0 && isset($data['fullname']) && $data['schoolid'] > 0) {
            $rname = mysql_real_escape_string($data['fullname']);
            $sql = "SELECT * FROM {local_resource} 
		      WHERE fullname='{$rname}' AND 
			  schoolid={$data['schoolid']}";
            $resource = $DB->get_records_sql($sql);
            $count = count($resource);
            if ($count > 0) {
                $errors['fullname'] = 'Resource Already Exist!';
            }
        }
        if ($data['id'] > 0) {
            $schoolid = $DB->get_field('local_resource', 'schoolid', array('id' => $data['id']));
            $name = $DB->get_field('local_resource', 'fullname', array('id' => $data['id']));
            if (strtolower($name) != strtolower($data['fullname'])) {
                $rname = mysql_real_escape_string($data['fullname']);
                $sql = "SELECT * FROM {local_resource} WHERE fullname='{$rname}' AND schoolid={$schoolid}";
                $class = $DB->get_records_sql($sql);
                $count = count($class);
                if ($count > 0) {
                    $errors['fullname'] = 'Resource Already Exist';
                }
            }
        }

        return $errors;
    }

}

//Class for assigning resources to classroom
class assignresource_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE;

        $mform = & $this->_form;

        $schoolid = $this->_customdata['sid'];
        $PAGE->requires->yui_module('moodle-local_classroomresources-building', 'M.local_classroomresources.init_building', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_classroomresources-floor', 'M.local_classroomresources.init_floor', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_classroomresources-classroom', 'M.local_classroomresources.init_classroom', array(array('formid' => $mform->getAttribute('id'))));

        $c = 'class="one";style="border:none";readonly="readonly";';
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        $school = $hierarchy->get_school_parent($scho);
        $this->_customdata['id'];
        if ($this->_customdata['id'] > 0) {

            $mform->addElement('static', 'schoolid', get_string('schoolname', 'local_collegestructure'));
            $mform->setType('schoolid', PARAM_RAW);
            $mform->addElement('text', 'buildingid', get_string('buildingname', 'local_classroomresources'), $c);
            $mform->setType('buildingid', PARAM_RAW);
            $mform->addElement('text', 'floorid', get_string('floorname', 'local_classroomresources'), $c);
            $mform->setType('floorid', PARAM_RAW);
            $mform->addElement('text', 'classroomid', get_string('classroomid', 'local_classroomresources'), $c);
            $mform->setType('classroomid', PARAM_RAW);
            $resource = cobalt_resources::get_instance();
            $resourcelist = $hierarchy->get_records_cobaltselect_menu('local_resource', "schoolid=$schoolid AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
            $select = $mform->addElement('select', 'resourceid', get_string('resourceid', 'local_classroomresources'), $resourcelist);
            $select->setMultiple(true);
        } else {

            $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
            $mform->addRule('schoolid', get_string('required'), 'required', null, 'client');


            $mform->addElement('hidden', 'beforefloor');
            $mform->setType('beforefloor', PARAM_RAW);
            $mform->addElement('hidden', 'beforeclass');
            $mform->setType('beforeclass', PARAM_RAW);
            $mform->addElement('hidden', 'beforeresource');
            $mform->setType('beforeresource', PARAM_RAW);
            $mform->addElement('hidden', 'beforer');
            $mform->setType('beforer', PARAM_RAW);
            $mform->registerNoSubmitButton('updatecourseformat');
            $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        }

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $submit = ($this->_customdata['id'] > 0) ? 'Update' : 'Assign Resources';
        $this->add_action_buttons('false', $submit);
    }

    function definition_after_data() {
        global $DB, $CFG;
        $hierarchy = new hierarchy();
        $mform = $this->_form;
        $sid = $mform->getElementValue('schoolid');

        if (isset($sid) && !empty($sid) && $sid[0] > 0) {
            $resource = cobalt_resources::get_instance();
            $building = $hierarchy->get_records_cobaltselect_menu('local_building', "schoolid=$sid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
            $one = $mform->createElement('select', 'buildingid', get_string('buildingid', 'local_classroomresources'), $building);
            $mform->insertElementBefore($one, 'beforefloor');
            $mform->addRule('buildingid', get_string('required'), 'required', null, 'client');
        }
        if (isset($one)) {
            $bid = $mform->getElementValue('buildingid');

            if (isset($bid) && !empty($bid) && $bid[0] > 0) {
                $resource = cobalt_resources::get_instance();
                $floor = $hierarchy->get_records_cobaltselect_menu('local_floor', "buildingid=$bid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
                $two = $mform->createElement('select', 'floorid', get_string('floorname', 'local_classroomresources'), $floor);
                $mform->insertElementBefore($two, 'beforeclass');
                $mform->addRule('floorid', get_string('required'), 'required', null, 'client');
            }
        }
        if (isset($two)) {
            $sid = $mform->getElementValue('schoolid');
            $fid = $mform->getElementValue('floorid');
            if (isset($fid) && !empty($fid) && $fid[0] > 0) {
                $resource = cobalt_resources::get_instance();
                $classroom = $hierarchy->get_records_cobaltselect_menu('local_classroom', "floorid=$fid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
                /*
                 * ###Bugreport #  classroom management
                 * @hemalatha c arun<hemalatha@eabyas.in>
                 * (Resolved) adding proper addrule method for form field classroomid.
                 */
                $three = $mform->createElement('select', 'classroomid', get_string('classroomid', 'local_classroomresources'), $classroom);
                $mform->insertElementBefore($three, 'beforeresource');
                $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');



                $resourcelist = $hierarchy->get_records_cobaltselect_menu('local_resource', "schoolid=$sid[0] AND visible=1", null, '', 'id,fullname', get_string('select', 'local_classroomresources'));
                $a = $mform->createElement('select', 'resourceid', get_string('resourceid', 'local_classroomresources'), $resourcelist);
                $mform->insertElementBefore($a, 'beforer');
                $b = $mform->getElement('resourceid')->setMultiple(true);
                $mform->addRule('resourceid', get_string('required'), 'required', null, 'client');
            }
        }
    }

    public function validation($data, $files) {
        global $CFG, $DB;
        $errors = array();

        if ($data['id'] < 0 && $data['schoolid'] > 0 && $data['buildingid'] == 0) {
            $errors['buildingid'] = 'Select Building';
        }
        if ($data['id'] < 0 && $data['buildingid'] > 0 && $data['floorid'] == 0) {
            $errors['floorid'] = 'Select Floor';
        }
        if ($data['id'] < 0 && $data['floorid'] > 0 && $data['classroomid'] == 0) {
            $errors['classroomid'] = 'Select Classroom';
        }

        if ($data['id'] < 0 && $data['floorid'] > 0 && $data['resourceid'] == 0) {
            $errors['resourceid'] = 'Select Resource';
        }

        if ($data['id'] < 0 && $data['classroomid'] > 0) {
            $sql = "SELECT * FROM {local_classroomresources} WHERE classroomid={$data['classroomid']}";
            $class = $DB->get_records_sql($sql);
            $count = count($class);
            if ($count > 0) {
                $errors['classroomid'] = 'Resources Already Assigned to this class';
            }
        }
        if ($data['id'] < 0 && in_array(0, $data['resourceid'])) {
            $errors['resourceid'] = 'Dont Select SelectResource';
        }

        if ($data['id'] > 0 && in_array(0, $data['resourceid'])) {
            $errors['resourceid'] = 'Select Resource';
        }
        return $errors;
    }

}
