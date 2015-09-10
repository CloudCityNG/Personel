<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class createclass_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $mform = & $this->_form;
        /*  Bug report #304 -Edit Class>Short Name- Error
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved- added valid condition avoiding duplication of shortname and also updated strings
         */
        $id = $this->_customdata['id'];
        $PAGE->requires->yui_module('moodle-local_clclasses-chooser', 'M.local_clclasses.init_chooser', array(array('formid' => $mform->getAttribute('id'))));
        $hierarchy = new hierarchy();
        if (is_siteadmin()) {
            $scho = $hierarchy->get_school_items();
        } else {
            $scho = $hierarchy->get_assignedschools();
        }
        $count = count($scho);
        $school = $hierarchy->get_school_parent($scho);

        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $school);
        $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        $mform->setType('schoolid', PARAM_RAW);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addsemesterlisthere');
        $mform->setType('addsemesterlisthere', PARAM_RAW);

        $mform->addElement('hidden', 'adddepartmentlisthere');
        $mform->setType('adddepartmentlisthere', PARAM_RAW);
        $style = "style='height:25px !important;'";
        $mform->addElement('hidden', 'adddepartmentemptymsg');
        $mform->setType('adddepartmentemptymsg', PARAM_RAW);

        $mform->addElement('hidden', 'addcobaltcoursehere');
        $mform->setType('addcobaltcoursehere', PARAM_RAW);


        $mform->addElement('text', 'fullname', get_string('classesname', 'local_clclasses'), $style);
        $mform->addHelpButton('fullname', 'classesname', 'local_clclasses');
        $mform->addRule('fullname', get_string('missingclassesname', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $mform->addElement('text', 'shortname', get_string('classesshortname', 'local_clclasses'), $style);
        $mform->addHelpButton('shortname', 'classesshortname', 'local_clclasses');
        $mform->addRule('shortname', get_string('missingclassesshort', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('shortname', PARAM_RAW);



        $mform->addElement('text', 'classlimit', get_string('classlimit', 'local_clclasses'), $style);
        $mform->addHelpButton('classlimit', 'classlimit', 'local_clclasses');
        $mform->addRule('classlimit', get_string('missinglimit', 'local_clclasses'), 'required', null, 'client');
        $mform->addRule('classlimit', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->setType('classlimit', PARAM_RAW);

        $selecttype = array();
        $selecttype['1'] = get_string('clsmode_1', 'local_clclasses');
        $selecttype['2'] = get_string('clsmode_2', 'local_clclasses');
        $mform->addElement('select', 'type', get_string('classmode', 'local_clclasses'), $selecttype);
        $mform->addHelpButton('type', 'classmode', 'local_clclasses');
        $mform->addRule('type', get_string('missingtype', 'local_clclasses'), 'required', null, 'client');
        $mform->setType('type', PARAM_INT);



        $mform->addElement('hidden', 'addonlinelisthere');
        $mform->setType('addonlinelisthere', PARAM_RAW);

        $mform->addElement('hidden', 'addonlinecoursehere');
        $mform->setType('addonlinecoursehere', PARAM_RAW);


        $mform->addElement('hidden', 'addinstructorhere');
        $mform->setType('addinstructorhere', PARAM_RAW);


        $mform->addElement('editor', 'description', get_string('description', 'local_clclasses'), null);
        $mform->setType('description', PARAM_RAW);

        /* $style="style='height:25px !important;'";
          $mform->addElement('text', 'fullname', get_string('classesname', 'local_clclasses'),$style);
          $mform->addHelpButton('fullname', 'classesname', 'local_clclasses');
          $mform->addRule('fullname', get_string('missingclassesname','local_clclasses'), 'required', null, 'client');
          $mform->setType('fullname', PARAM_RAW);

          $mform->addElement('text', 'shortname', get_string('classesshortname', 'local_clclasses'),$style);
          $mform->addHelpButton('shortname', 'classesshortname', 'local_clclasses');
          $mform->addRule('shortname', get_string('missingclassesshort','local_clclasses'), 'required', null, 'client');
          $mform->setType('shortname', PARAM_RAW);

          $mform->addElement('editor', 'description', get_string('description', 'local_clclasses'), null, $editoroptions);
          $mform->setType('description', PARAM_RAW);

          $mform->addElement('text', 'classlimit',get_string('classlimit','local_clclasses'),$style);
          $mform->addHelpButton('classlimit', 'classlimit', 'local_clclasses');
          $mform->addRule('classlimit', get_string('missinglimit','local_clclasses'), 'required', null, 'client');
          $mform->addRule('classlimit', get_string('numeric','local_admission'), 'numeric', null,'client');
          $mform->setType('classlimit', PARAM_RAW);

          $selecttype=array();
          $selecttype['1']=get_string('clsmode_1','local_clclasses');
          $selecttype['2']=get_string('clsmode_2','local_clclasses');
          $mform->addElement('select', 'type',get_string('classmode','local_clclasses'),$selecttype);
          $mform->addHelpButton('type', 'classmode', 'local_clclasses');
          $mform->addRule('type', get_string('missingtype','local_clclasses'), 'required', null, 'client');
          $mform->setType('type', PARAM_INT); */

        $mform->addElement('html', '<div id="myratings1"></div>');
        $mform->addElement('html', '<div id="myratings2"></div>');
        $submit = get_string('createclasses', 'local_clclasses');

        $time = time();
        $userid = $USER->id;
        $mform->addElement('hidden', 'timecreated', $time);
        $mform->setType('timecreated', PARAM_INT);

        $mform->addElement('hidden', 'timemodified', $time);
        $mform->setType('timemodified', PARAM_INT);

        $mform->addElement('hidden', 'usermodified', $userid);
        $mform->setType('usermodified', PARAM_INT);


        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $actionbutton = ($id > 0) ? get_string('updateclass', 'local_clclasses') : get_string('createclasses', 'local_clclasses');


        $this->add_action_buttons($cancel = true, $actionbutton);
    }

    function definition_after_data() {
        global $DB, $CFG;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $schoid = $this->_customdata['schoid'];
        $hierarchy = new hierarchy();
        $formatvalue = $mform->getElementValue('schoolid');
        $formatvalue = $formatvalue[0];
        if ($formatvalue > 0) {
            /*
             * ###Bug report #245  -  Training Management
             * @author Naveen Kumar<naveen@eabyas.in>
             * (Resolved) Changed function to get semesters. We need to get all upcoming and present semesters.
             *  Previous method only get presesnt active semester
             */
            $tools = classes_get_school_semesters($formatvalue);
            $newel = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $tools);
            $mform->insertElementBefore($newel, 'addsemesterlisthere');
            $mform->addHelpButton('semesterid', 'semester', 'local_semesters');
            $mform->addRule('semesterid', get_string('missingsemester', 'local_semesters'), 'required', null, 'client');
            $mform->setType('semesterid', PARAM_RAW);

            $departments = $hierarchy->get_departments_forschool($formatvalue, $none = "");
            $dept = $mform->createElement('select', 'departmentid', get_string('department', 'local_clclasses'), $departments);
            $mform->insertElementBefore($dept, 'adddepartmentlisthere');

            if (count($departments) <= 1) {
                $empty_deptmsg = $mform->createElement('static', 'department_emptyinfo', '', $hierarchy->cobalt_navigation_msg(get_string('navigation_info', 'local_collegestructure'), get_string('create_department', 'local_departments'), $CFG->wwwroot . '/local/departments/departments.php'));
                $mform->insertElementBefore($empty_deptmsg, 'adddepartmentemptymsg');
            }
            $mform->addHelpButton('departmentid', 'department', 'local_clclasses');

            $mform->addRule('departmentid', get_string('departmentmissing', 'local_clclasses'), 'required', null, 'client');
            $mform->setType('departmentid', PARAM_RAW);

            $departmentvalue = $mform->getElementValue('departmentid');
            $departmentvalue = $departmentvalue[0];
            if ($departmentvalue > 0) {
                $selectonline = array();
                $selectonline[NULL] = get_string('select', 'local_clclasses');
                $selectonline['1'] = get_string('online', 'local_clclasses');
                $selectonline['2'] = get_string('offline', 'local_clclasses');
                $cobaltclasstype = $mform->createElement('select', 'online', get_string('classtype', 'local_clclasses'), $selectonline);
                $mform->insertElementBefore($cobaltclasstype, 'addonlinelisthere');
                $mform->addRule('online', get_string('missingonline', 'local_clclasses'), 'required', null, 'client');

                $mform->setType('online', PARAM_INT);
                $mform->addHelpButton('online', 'classtype', 'local_clclasses');
            }
            if ($departmentvalue > 0) {
                $online = $mform->getElementValue('online');
                if (isset($online)) {

                    $online = $online[0];
                    if ($online == 1) {
                        $onlinecourses = $hierarchy->get_records_cobaltselect_menu('course', 'visible=1 AND category>0', null, '', 'id,concat(shortname,": ",fullname)', '--Select--');
                        $cobaltcourse2 = $mform->createElement('select', 'onlinecourseid', get_string('onlinecourse', 'local_clclasses'), $onlinecourses);
                        $mform->insertElementBefore($cobaltcourse2, 'addonlinecoursehere');
                        $mform->addRule('onlinecourseid', get_string('required'), 'required', null, 'client');
                        $mform->setType('onlinecourseid', PARAM_RAW);
                        $mform->addHelpButton('onlinecourseid', 'onlinecourse', 'local_clclasses');

                        $new1 = $mform->createElement('html', '<a id="newonlinecourse" style="float:right;margin-right:240px;cursor:pointer;"
			onclick="onlinecourse(' . $formatvalue . ',' . $departmentvalue . ')">' . get_string('addnewonlinecourse', 'local_clclasses') . '</a>');
                        $mform->insertElementBefore($new1, 'onlinecourseid');
                    }
                }
            }
            if ($departmentvalue > 0) {
                $cobaltcourses = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', "departmentid=$departmentvalue AND visible=1", null, '', 'id,concat(shortname,": ",fullname)', '--Select--');
                $cobaltcourse = $mform->createElement('select', 'cobaltcourseid', get_string('cobaltcourse', 'local_clclasses'), $cobaltcourses);
                $mform->insertElementBefore($cobaltcourse, 'addcobaltcoursehere');
                $mform->addHelpButton('cobaltcourseid', 'cobaltcourse', 'local_clclasses');
                $mform->addRule('cobaltcourseid', get_string('cobaltcoursemissing', 'local_clclasses'), 'required', null, 'client');
                $mform->setType('cobaltcourseid', PARAM_RAW);

                $new = $mform->createElement('html', '<a id="newcobaltcourse" style="float:right;margin-right: 240px;cursor:pointer;"
			onclick="cobaltcourse(' . $formatvalue . ',' . $departmentvalue . ')">' . get_string('addnewcobaltcourse', 'local_clclasses') . '</a>');

                $mform->insertElementBefore($new, 'cobaltcourseid');
                $online = $mform->getElementValue('online');
            }
            // Task code : T1.6 - Assigning instructor to class             
            //-------- selecting  instructor to class-----------------------------------------------------------

            if ($formatvalue > 0 && $departmentvalue > 0 && $online[0] > 0) {
                $cobaltcourses1 = $hierarchy->get_department_instructors($departmentvalue, $formatvalue);
                $instructorfield = $mform->createElement('select', 'instructor', get_string('instructor', 'local_clclasses'), $cobaltcourses1, array('multiple' => 'multiple'));


                if (count($cobaltcourses1) <= 1) {
                    $insroleid = $DB->get_record_sql("SELECT * FROM {role} where shortname='instructor'");
                    $instructorexits = $DB->get_records('local_school_permissions', array('schoolid' => $schoid, 'roleid' => $insroleid->id));
                    if ($instructorexits)
                        $navigationlink = $CFG->wwwroot . '/local/departments/assign_instructor.php?slsid=' . $schoid . '';
                    else
                        $navigationlink = $CFG->wwwroot . '/local/users/user.php';
                    $navigationmsg = get_string('nodata_assigninstructorpage', 'local_departments');
                    $linkname = get_string('linkname_assigninstructorpage', 'local_departments');
                    $instructorfield = $mform->createElement('static', 'department_emptyinfo', '', $hierarchy->cobalt_navigation_msg($navigationmsg, $linkname, $navigationlink, 'margin-bottom: 0;
            line-height: 0px;'));
                }
                $mform->insertElementBefore($instructorfield, 'addinstructorhere');
                $mform->setType('instructor', PARAM_INT);
                //   $mform->addRule('instructor', get_string('required'), 'required', null, 'client');
            }
            //---------------------------------------------------------------------------------------------------
        }
    }

    public function validation($data, $files) {
        global $DB, $CFG;
        $errors = array();
        $id = $this->_customdata['id'];
        /*
         * ###Bugreport #110- Current classes
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) Checking criteria set or grades submitted for the class,
         *  to restrict from updating online to offline viceversa
         */
        if ($id > 0) {
            $currentclass = $DB->get_record('local_clclasses', array('id' => $id));
            $examcriteriaexist = $DB->record_exists_sql("select * from {local_class_completion} where classid=$currentclass->id and schoolid=$currentclass->schoolid and semesterid=$currentclass->semesterid");
            if ($currentclass->online == 1) {

                $sql = "SELECT lse.*,le.examtype as examname FROM {local_class_completion} cmp,{local_scheduledexams} lse,{local_examtypes} le WHERE lse.semesterid={$currentclass->semesterid} AND lse.classid={$currentclass->id} AND lse.schoolid={$currentclass->schoolid} AND cmp.examid=lse.id
AND lse.examtype=le.id";
                $query = $DB->get_records_sql($sql);
                $offline_sql = $sql = "SELECT lse.*,le.examtype as examname FROM {local_user_examgrades} cmp,{local_scheduledexams} lse,{local_examtypes} le WHERE lse.semesterid={$currentclass->semesterid} AND lse.classid={$currentclass->id} AND lse.schoolid={$currentclass->schoolid} AND cmp.examid=lse.id
AND lse.examtype=le.id";
                $query2 = $DB->get_records_sql($offline_sql);
                $totalcount = count($query) + count($query2);
            } else if ($currentclass->online == 2) {
                $sql = "SELECT lse.*,le.examtype as examname FROM {local_class_completion} cmp,{local_scheduledexams} lse,{local_examtypes} le WHERE lse.semesterid={$currentclass->semesterid} AND lse.classid={$currentclass->id} AND lse.schoolid={$currentclass->schoolid} AND cmp.examid=lse.id
AND lse.examtype=le.id";
                $query = $DB->get_records_sql($sql);
                $totalcount = count($query);
            }
        }

        if ($examcriteriaexist && $data['online'] != $currentclass->online) {
            $errors['online'] = 'Exam criteria set for this class. Please remove the criteria before changing the class type';
        }
        if ($totalcount && $data['online'] != $currentclass->online) {
            $errors['online'] = "Already grades submitted for this class. Can't change the class type.";
        }

        if ($data['classlimit'] <= 0) {
            $errors['classlimit'] = 'Limit value should be greater than zero';
        }
        /* Bug-id 271
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * resolved- removing duplication of the class shortname
         */
        if ($id > 0) {
            $sh = ( $data['shortname']);
            $shortname_exist = $DB->get_records_sql("select * from {local_clclasses} where id!=$id and shortname = '{$sh}'");
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        else {
            if ($DB->record_exists('local_clclasses', array('shortname' => $data['shortname'])))
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        return $errors;
    }

}

