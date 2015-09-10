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

require_once($CFG->dirroot . '/lib/formslib.php');

class classes_form extends moodleform {

    public function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-local_clclasses-schoolchooser', 'M.local_clclasses.init_schoolchooser', array(array('formid' => $mform->getAttribute('id'))));
        $editoroptions = $this->_customdata['editoroptions'];
        $id = $this->_customdata['id'];
        if ($id > 0)
            $mform->addElement('header', 'settingsheader', get_string('editclasses', 'local_clclasses'));
        else
            $mform->addElement('header', 'settingsheader', get_string('createclasses', 'local_clclasses'));

        $tools = array();
        $enddate = date("d/m/Y");
        $startdate = date("d/m/Y");

        $hierarchy = new hierarchy();
        $items = $hierarchy->get_school_items();
        $parents = $hierarchy->get_school_parent($items);
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        $mform->setType('schoolid', PARAM_RAW);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addsemesterlisthere');
        $mform->setType('addsemesterlisthere', PARAM_RAW);

        $mform->addElement('hidden', 'adddepartmentlisthere');
        $mform->setType('adddepartmentlisthere', PARAM_RAW);
        $mform->registerNoSubmitButton('updatedepartment');
        $mform->addElement('submit', 'updatedepartment', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addcobaltcoursehere');
        $mform->setType('addcobaltcoursehere', PARAM_RAW);

        $mform->addElement('text', 'fullname', get_string('classesname', 'local_clclasses'), $tools);
        $mform->addHelpButton('fullname', 'classesname', 'local_clclasses');
        $mform->addRule('fullname', get_string('missingclassesname', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('classesshortname', 'local_clclasses'), $tools);
        $mform->addHelpButton('shortname', 'classesshortname', 'local_clclasses');
        $mform->addRule('shortname', get_string('missingclassesshort', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);
        $mform->addElement('editor', 'description', get_string('description', 'local_clclasses'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        $now = date("d-m-Y");
        $now = strtotime($now);
        $mform->addElement('text', 'classlimit', get_string('classlimit', 'local_clclasses'));
        $mform->addHelpButton('classlimit', 'classlimit', 'local_clclasses');
        $mform->addRule('classlimit', get_string('missinglimit', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('classlimit', PARAM_RAW);
        $selecttype = array();
        $selecttype['1'] = get_string('clsmode_1', 'local_clclasses');
        $selecttype['2'] = get_string('clsmode_2', 'local_clclasses');

        $mform->addElement('select', 'type', get_string('classmode', 'local_clclasses'), $selecttype);
        $mform->addHelpButton('type', 'classmode', 'local_clclasses');
        $mform->addRule('type', get_string('missingtype', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('type', PARAM_INT);

        $selectonline = array();
        $selectonline[''] = get_string('select', 'local_clclasses');
        $selectonline['1'] = get_string('online', 'local_clclasses');
        $selectonline['2'] = get_string('offline', 'local_clclasses');
        $mform->addElement('select', 'online', get_string('classtype', 'local_clclasses'), $selectonline);
        $mform->addHelpButton('online', 'classtype', 'local_clclasses');
        $mform->addRule('online', get_string('missingonline', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('online', PARAM_INT);

        $mform->registerNoSubmitButton('updatecourseid');
        $mform->addElement('submit', 'updatecourseid', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addonlinecoursehere');
        $mform->setType('addonlinecoursehere', PARAM_RAW);
        $mform->addElement('html', '<a name="Iheader"> </a>');
        /* ---assing instructor heading task--- */
        $mform->addElement('header', 'settingsheader', get_string('assigninstructor', 'local_clclasses'));

        $mform->addElement('hidden', 'adddepartmentinhere');
        $mform->setType('adddepartmentinhere', PARAM_RAW);
        $mform->registerNoSubmitButton('updateinstructor');
        $mform->addElement('submit', 'updateinstructor', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addinstructorhere');
        $mform->setType('addinstructorhere', PARAM_RAW);
        /* ---Area for Scheduling the classes--- */

        $mform->addElement('header', 'settingsheader', get_string('scheduleclassroom', 'local_clclasses'));

        $scheduleclass = cobalt_scheduleclass::get_instance();
        $hour = $scheduleclass->hour();
        $min = $scheduleclass->min();
        $starttime = array();
        $endtime = array();
        $mform->addElement('html', '<a name="zheader"> </a>');
        $mform->addElement('date_selector', 'startdate', get_string('from', 'local_clclasses'), array('optional' => true));
        $mform->addHelpButton('startdate', 'from', 'local_clclasses');
        $mform->setDefault('startdate', time() + 3600 * 24);
        $mform->addElement('date_selector', 'enddate', get_string('to', 'local_clclasses'), array('optional' => true));
        $mform->addHelpButton('enddate', 'to', 'local_clclasses');
        $mform->setDefault('enddate', time() + 3600 * 24);
        $starttime[] = &$mform->createElement('select', 'starthour', get_string('starthour', 'local_classroomresources'), $hour);
        $starttime[] = &$mform->createElement('select', 'startmin', get_string('startmin', 'local_classroomresources'), $min);
        $mform->addGroup($starttime, 'starttime', 'Start Time', array(' '), false);
        $endtime[] = &$mform->createElement('select', 'endhour', get_string('endhour', 'local_classroomresources'), $hour);
        $endtime[] = &$mform->createElement('select', 'endmin', get_string('endmin', 'local_classroomresources'), $min);
        $mform->addGroup($endtime, 'endtime', 'End Time', array(' '), false);



        /* ---End of schedule classes--- */

        $mform->addElement('advcheckbox', 'choose', get_string('getfreeclass', 'local_clclasses'));
        $mform->addHelpButton('choose', 'getfreeclass', 'local_clclasses');
        $mform->setDefault('choose', 0);
        $mform->addHelpButton('startdate', 'from', 'local_clclasses');
        $mform->addElement('hidden', 'beforeclassroom');
        $mform->setType('beforeclassroom', PARAM_RAW);
        $mform->registerNoSubmitButton('updateclassrooms');
        $mform->addElement('submit', 'updateclassrooms', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'timecreated', $now);
        $mform->setType('timecreated', PARAM_RAW);
        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $actionbutton = ($id > 0) ? get_string('updateclass', 'local_clclasses') : get_string('createclasses', 'local_clclasses');


        $this->add_action_buttons($cancel = true, $actionbutton);
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;
        $id = $this->_customdata['id'];

        $formatvalue = $mform->getElementValue('schoolid');


        $tools = array();
        if ($formatvalue) {
            $hierarchy = new hierarchy();

            $formatvalue = $formatvalue[0];
            if ($formatvalue > 0) {
                $tools = classes_get_school_semesters($formatvalue);
                /* create a semester dropdown */
                $newel = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $tools);
                $mform->insertElementBefore($newel, 'addsemesterlisthere');
                $mform->addHelpButton('semesterid', 'semester', 'local_semesters');
                $mform->addRule('semesterid', get_string('missingsemester', 'local_semesters'), 'required', null, 'client');
                $mform->setType('addsemesterlisthere', PARAM_RAW);
                /* end of the semester dropdown */
                $departments = $hierarchy->get_departments_forschool($formatvalue, $none = "");

                /* create a department dropdown */
                $dept = $mform->createElement('select', 'departmentid', get_string('department', 'local_clclasses'), $departments);
                $mform->insertElementBefore($dept, 'adddepartmentlisthere');
                $mform->addRule('departmentid', get_string('departmentmissing', 'local_clclasses'), 'required', null, 'client');
                $mform->addHelpButton('departmentid', 'department', 'local_clclasses');

                /*  End of department dropdown */

                $departmentvalue = $mform->getElementValue('departmentid');
                $departmentvalue = $departmentvalue[0];

                if ($departmentvalue > 0) {
                    $cobaltcourses = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', "departmentid=$departmentvalue AND visible=1", null, '', 'id,fullname', '--Select--');
                    $cobaltcourse = $mform->createElement('select', 'cobaltcourseid', get_string('cobaltcourse', 'local_clclasses'), $cobaltcourses);
                    $mform->insertElementBefore($cobaltcourse, 'addcobaltcoursehere');
                    $mform->setType('addcobaltcoursehere', PARAM_RAW);
                    $mform->addRule('cobaltcourseid', get_string('cobaltcoursemissing', 'local_clclasses'), 'required', null, 'client');
                    $mform->addHelpButton('cobaltcourseid', 'cobaltcourse', 'local_clclasses');
                }
            }
            $online = $mform->getElementValue('online');
            $online = $online[0];

            if ($online == 1 && $id > 0) {
                $onlinecourses = $hierarchy->get_records_cobaltselect_menu('course', "visible=1 AND category>0", null, '', 'id,fullname', '--Select--');
                $cobaltcourse2 = $mform->createElement('select', 'onlinecourseid', get_string('onlinecourse', 'local_clclasses'), $onlinecourses);
                $mform->addRule('onlinecourseid', get_string('required'), 'required', null, 'client');
                $mform->insertElementBefore($cobaltcourse2, 'addonlinecoursehere');
                $mform->setType('addonlinecoursehere', PARAM_RAW);
                $mform->addHelpButton('onlinecourseid', 'onlinecourse', 'local_clclasses');
            } else if ($online == 2 && $id > 0) {
                $default = 0;
                $cobaltcourse2 = $mform->createElement('hidden', 'onlinecourseid', get_string('onlinecourse', 'local_clclasses'), $default);
                // $mform->addRule('onlinecourseid', get_string('required'), 'required', null, 'client');
                $mform->insertElementBefore($cobaltcourse2, 'addonlinecoursehere');
                $mform->setDefault('onlinecourseid', $default);
                $mform->setType('addonlinecoursehere', PARAM_RAW);
            } else if ($online == 1 && $id < 0) {

                $onlinecourses = $hierarchy->get_records_cobaltselect_menu('course', "visible=1 AND category>0", null, '', 'id,fullname', '--Select--');
                $cobaltcourse2 = $mform->createElement('select', 'onlinecourseid', get_string('onlinecourse', 'local_clclasses'), $onlinecourses);
                $mform->addRule('onlinecourseid', get_string('required'), 'required', null, 'client');
                $mform->insertElementBefore($cobaltcourse2, 'addonlinecoursehere');
                $mform->setType('addonlinecoursehere', PARAM_RAW);
                $mform->addHelpButton('onlinecourseid', 'onlinecourse', 'local_clclasses');
            }

            /* end of the department dropdown */
            /* create a department dropdown for instructor selection */
            $depts = $mform->createElement('select', 'departmentinid', get_string('department', 'local_clclasses'), $departments);
            $mform->insertElementBefore($depts, 'adddepartmentinhere');
            $mform->addHelpButton('departmentinid', 'departmentin', 'local_clclasses');
            /* End of department dropdown for instructor selection */
            $departmentin = $mform->getElementValue('departmentinid');
            $departmentin = $departmentin[0];
            if ($departmentin > 0) {
                $cobaltcourses1 = $hierarchy->get_department_instructors($departmentin, $formatvalue);
                $instructor = $mform->createElement('select', 'instructorid', get_string('instructor', 'local_clclasses'), $cobaltcourses1);
                $mform->insertElementBefore($instructor, 'addinstructorhere');
                $mform->setType('instructorid', PARAM_RAW);
                $mform->getElement('instructorid')->setMultiple(true);
            }


            $check = $mform->getElementValue('choose');
            $startdate = $mform->getElementValue('startdate');
            $enddate = $mform->getElementValue('enddate');
            $starttime = $mform->getElementValue('starttime');
            $endtime = $mform->getElementValue('endtime');
            $scheduleclass = cobalt_scheduleclass::get_instance();
            if ($id < 0 && $startdate > 0 && $formatvalue > 0 && $check > 0) {
                $classroom = $scheduleclass->classroomlist($startdate, $enddate, $starttime, $endtime, $formatvalue, $id);
                $two = $mform->createElement('select', 'classroomid', get_string('classroomids', 'local_classroomresources'), $classroom);
                $mform->insertElementBefore($two, 'beforeclassroom');
                $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');
            }
            if ($id > 0 && $check > 0) {
                $name = $DB->get_record('local_scheduleclass', array('classid' => $id));
                $names = $DB->get_record('local_classroom', array('id' => $name->classroomid));
                $building = $DB->get_field('local_building', 'fullname', array('id' => $names->buildingid));
                $floor = $DB->get_field('local_floor', 'fullname', array('id' => $names->floorid));
                $classrooms = $scheduleclass->classroomlist($startdate, $enddate, $starttime, $endtime, $formatvalue, $id);
                if (!empty($name)) {
                    $classrooms[$name->classroomid] = $building . '/' . $floor . '/' . $names->fullname;
                }
                $two = $mform->createElement('select', 'classroomid', get_string('classroomids', 'local_classroomresources'), $classrooms);
                $mform->insertElementBefore($two, 'beforeclassroom');
                $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');
            }
        }
    }

    public function validation($data, $files) {
        global $COURSE, $DB, $CFG;

        $errors = array();
        $id = $this->_customdata['id'];


        if ($data['online'] != null) {
            if ($data['online'] == 1 && $data['onlinecourseid'] == null) {
                $errors['onlinecourseid'] = 'Please Select Online Course';
            }
        }
        if ($data['classlimit'] <= 0) {
            $errors['classlimit'] = 'Limit value should be greater than zero';
        }
        /* condition starts */
        if ($data['startdate'] != null || $data['enddate'] != null) {
            if ($data['starthour'] == null) {
                $errors['starttime'] = 'Starttime Hours Required';
            }
            if ($data['startmin'] == null) {
                $errors['starttime'] = 'Starttime Minutes Required';
            }
            if ($data['endhour'] == null) {
                $errors['endtime'] = 'Endtime Hours Required';
            }
            if ($data['endmin'] == null) {
                $errors['endtime'] = 'Endtime Minutes Required';
            }
            if ($data['startdate'] > $data['enddate']) {
                $errors['startdate'] = 'Startdate should not greater than Enddate';
            }

            if ($data['starthour'] > $data['endhour']) {
                $errors['starttime'] = 'Start Time should not greater than End Time';
            }
            $sem = $DB->get_record('local_semester', array('id' => $data['semesterid']));
            if ($data['startdate'] < $sem->startdate || $data['startdate'] > $sem->enddate || $data['enddate'] < $sem->startdate || $data['enddate'] > $sem->enddate) {
                $errors['startdate'] = 'Start Date and End Date should be between Semester Start Date and Semester End Date';
            }
        }
        return $errors;
    }

}
