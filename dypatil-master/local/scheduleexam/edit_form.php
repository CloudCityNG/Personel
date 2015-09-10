<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/scheduleexam/lib.php');

class edit_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE, $hierarchy;

        $mform = $this->_form;

        $PAGE->requires->yui_module('moodle-local_scheduleexam-scheduleexamchooser', 'M.local_scheduleexam.init_scheduleexamchooser', array(array('formid' => $mform->getAttribute('id'))));
        $id = $this->_customdata['id'];

        $disable = ($id > 0) ? 'disabled="disabled"' : '';

        $hierarchy = new hierarchy();
        if (is_siteadmin($USER->id)) {
            $schools = $DB->get_records('local_school', array('visible' => 1));
        } else {
            $schools = $hierarchy->get_assignedschools();
        }

        if ($id > 0) {
            $mform->addElement('header', 'editexamheader', get_string('editexamheader', 'local_scheduleexam'));
            $mform->addHelpButton('editexamheader', 'editexamheader', 'local_scheduleexam');
        } else {
            $mform->addElement('header', 'createexamheader', get_string('createexamheader', 'local_scheduleexam'));
            $mform->addHelpButton('createexamheader', 'createexamheader', 'local_scheduleexam');
        }

        $parents = $hierarchy->get_school_parent($schools);
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents, $disable);
        if ($id < 0) {
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        }
        $mform->setType('schoolid', PARAM_INT);


        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        $mform->addElement('hidden', 'addsemesterslisthere');
        $mform->setType('addsemesterslisthere', PARAM_INT);
        $mform->addElement('hidden', 'addclclasseslisthere');
        $mform->setType('addclclasseslisthere', PARAM_INT);
        
        $mform->addElement('hidden', 'addcobaltcourseehere');
        $mform->setType('addcobaltcourseehere', PARAM_INT);
        
        $mform->addElement('hidden', 'addexamtypehere');
        $mform->setType('addexamtypehere', PARAM_INT);
        
        $mform->addElement('hidden', 'addexamtypeempty_msg');
        $mform->setType('addexamtypeempty_msg', PARAM_RAW);
        
        $mform->addElement('hidden', 'addlecturetypehere');
        $mform->setType('addlecturetypehere', PARAM_INT);
        
        $mform->addElement('hidden', 'addlecturetypeempty_msg');
        $mform->setType('addlecturetypeempty_msg', PARAM_RAW);
        
        $mform->addElement('date_selector', 'opendate', get_string('opendate', 'local_scheduleexam'));
        $mform->addRule('opendate', get_string('opendatereq', 'local_scheduleexam'), 'required', null, 'client');

        //*************************************************************************************************************
        $shour['null'] = 'Hour';
        for ($sthour = 0; $sthour <= 23; $sthour++) {
            if ($sthour < 10)
                $shour['0' . $sthour] = '0' . $sthour;
            else
                $shour[$sthour] = $sthour;
        }
        $smin['null'] = 'Mins';
        for ($stmin = 0; $stmin <= 59; $stmin++) {
            if ($stmin < 10)
                $smin['0' . $stmin] = '0' . $stmin;
            else
                $smin[$stmin] = $stmin;
        }

        $starttime = array();
        $starttime[] = & $mform->createElement('select', 'starttimehour', '', $shour);
        $starttime[] = & $mform->createElement('select', 'starttimemin', '', $smin);
        $mform->addGroup($starttime, 'starttimearr', get_string('starttimehour', 'local_scheduleexam'), array(' '), false);
        $mform->addRule('starttimearr', get_string('missingstarttimehour', 'local_scheduleexam'), 'required', null, 'client');

        $endtime = array();
        $endtime[] = & $mform->createElement('select', 'endtimehour', '', $shour);
        $endtime[] = & $mform->createElement('select', 'endtimemin', '', $smin);
        $mform->addGroup($endtime, 'endtimearr', get_string('endtimehour', 'local_scheduleexam'), array(' '), false);
        $mform->addRule('endtimearr', get_string('missingendtimehour', 'local_scheduleexam'), 'required', null, 'client');


        $mform->addElement('text', 'grademin', get_string('grademin', 'local_scheduleexam'), $disable);
        $mform->setType('grademin', PARAM_INT);
        if ($id < 0) {
            $mform->addRule('grademin', get_string('grademinreq', 'local_scheduleexam'), 'required', null, 'client');
            $mform->addRule('grademin', get_string('grademinnum', 'local_scheduleexam'), 'numeric', null, 'client');
        }

        $mform->addElement('text', 'grademax', get_string('grademax', 'local_scheduleexam'), $disable);
        $mform->setType('grademax', PARAM_INT);
        if ($id < 0) {
            $mform->addRule('grademax', get_string('grademaxreq', 'local_scheduleexam'), 'required', null, 'client');
            $mform->addRule('grademax', get_string('grademaxnum', 'local_scheduleexam'), 'numeric', null, 'client');
        }
//            $mform->addElement('text', 'examweightage', get_string('examweightage', 'local_scheduleexam'), $disable);
//            $mform->setType('examweightage', PARAM_INT);
//        if($id < 0){
//            $mform->addRule('examweightage', get_string('examweightagereq', 'local_scheduleexam'), 'required', null, 'client');
//            $mform->addRule('examweightage', get_string('examweightagenum', 'local_scheduleexam'), 'numeric', null, 'client');
//        }
        //*****************************************************************************************

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if ($id < 0) {
            $this->add_action_buttons('false', 'Schedule Assessment');
        } else {
            $this->add_action_buttons('false', 'Update Assessment');
        }
    }

    function definition_after_data() {
        global $CFG,$DB, $PAGE, $USER, $hierarchy, $exams, $selected_class;
        $hierarchy = new hierarchy();
        $exams = new schedule_exam();
        $linkmsg=get_string('navigation_info','local_collegestructure');
        $linkstyle='line-height: 0px;margin-bottom: -10px;';
        $mform = $this->_form;


        $id = $this->_customdata['id'];
        $selected_school = $mform->getElementValue('schoolid');
        //$selected_program = array();
        $disable = ($id > 0) ? 'disabled="disabled"' : '';

        // for programs and semesters in a school
        if ($selected_school[0] > 0) {

//            $programs_list = array();
//            $programs_list = $hierarchy->get_school_programs($selected_school[0]);
//            
//            $programs_listdrop = $mform->createElement('select', 'programid', get_string('programslist', 'local_scheduleexam'), $programs_list, $disable);
//            $mform->insertElementBefore($programs_listdrop, 'addprogramslisthere');
//            if($id < 0){
//                 $mform->addRule('programid', get_string('programreq', 'local_scheduleexam'), 'required', null, 'client');
//             }
//            $selected_program = $mform->getElementValue('programid');

            $semesters_list = array();
            $semesters_list = $exams->get_semesterslists_scheduleexam($selected_school[0]);

            $semesters_listdrop = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $semesters_list, $disable);
            $mform->insertElementBefore($semesters_listdrop, 'addsemesterslisthere');
            if ($id < 0) {
                $mform->addRule('semesterid', get_string('missingsemester', 'local_semesters'), 'required', null, 'client');
            }

            $selected_semester = array();
            $selected_semester = $mform->getElementValue('semesterid');
        }

        // for examtypes in a school
        if (($selected_school[0])) {

            $examtype = array();        
            $examtype = $hierarchy->get_records_cobaltselect_menu('local_examtypes', "schoolid = $selected_school[0] AND visible=1", null, '', 'id,examtype', 'Select Assessment Type');
            $examtypelist_listdrop = $mform->createElement('select', 'examtype', get_string('examtype', 'local_examtype'), $examtype, $disable);
            $mform->insertElementBefore($examtypelist_listdrop, 'addexamtypehere');
           if(count($examtype)<=1){ 
              $linkname=get_string('addeditexamtype','local_examtype');
              $examtypelist_empty=$mform->createElement('static', 'examtype_emptyinfo', '',$hierarchy->cobalt_navigation_msg($linkmsg,$linkname,$CFG->wwwroot.'/local/examtype/edit.php',$linkstyle));  
              $mform->insertElementBefore($examtypelist_empty, 'addexamtypeempty_msg');  
           }
            
            if ($id < 0) {
                $mform->addRule('examtype', get_string('examtypereq', 'local_scheduleexam'), 'required', null, 'client');
            }
        }

        // for clclasses assigned to a semester
        if (($selected_school[0]) AND ( $selected_semester[0] > 0)) {

            $clclasses_list = array();           
            $clclasses_list = $hierarchy->get_records_cobaltselect_menu('local_clclasses', "semesterid=$selected_semester[0] and schoolid=$selected_school[0] and visible=1", null, '', 'id,fullname', 'Select Class');
            $clclasses_listdrop = $mform->createElement('select', 'classid', get_string('class', 'local_clclasses'), $clclasses_list, $disable);
            $mform->insertElementBefore($clclasses_listdrop, 'addclclasseslisthere');
            if ($id < 0)
                $mform->addRule('classid', get_string('clclassesreq', 'local_clclasses'), 'required', null, 'client');

            $selected_class = array();
            $selected_class = $mform->getElementValue('classid');
        }

        // for lecturetypes in a school
        if ($selected_school[0]) {
            
            $lecturetype = array();
           // $lecturetype = $exams->get_lecturetype_scheduleexam($selected_school[0]);
            $lecturetype = $hierarchy->get_records_cobaltselect_menu('local_lecturetype', "schoolid=$selected_school[0] ", null, '', 'id,lecturetype', 'Select Mode of Assessment'); 
            $lecturetype_listdrop = $mform->createElement('select', 'lecturetype', get_string('lecturetype', 'local_scheduleexam'), $lecturetype, $disable);

            $mform->insertElementBefore($lecturetype_listdrop, 'addlecturetypehere');
            if(count($lecturetype)<=1){
             $linkname=get_string('create','local_lecturetype');     
             $lecturetypelist_empty=$mform->createElement('static', 'lecturetype_emptyinfo', '',$hierarchy->cobalt_navigation_msg( $linkmsg, $linkname,$CFG->wwwroot.'/local/lecturetype/edit.php',$linkstyle));  
             $mform->insertElementBefore($lecturetypelist_empty, 'addlecturetypeempty_msg');  
            }
            if ($id < 0) {
                $mform->addRule('lecturetype', get_string('lecturetypereq', 'local_scheduleexam'), 'required', null, 'client');
            }
        }

        if ($selected_class[0]) {
            //$cobaltcourse = array();
            $cobcourse = $exams->get_cobaltcourse_scheduleexam($selected_class[0]);

            $cobaltcourse = $mform->createElement('static', 'cobcourse', get_string('coursename', 'local_cobaltcourses'), $cobcourse);

            $mform->insertElementBefore($cobaltcourse, 'addcobaltcourseehere');
        }
    }

/// perform some extra moodle validation
    function validation($data, $files) {
        global $DB, $CFG;

        $errors = array();
        $errors = parent::validation($data, $files);

        $id = $data['id'];
        $stimeh = $data['starttimehour'];
        $stimem = $data['starttimemin'];
        $etimeh = $data['endtimehour'];
        $etimem = $data['endtimemin'];
        $opendate = $data['opendate'];
        $school = $data['schoolid'];
        //  $program = $data['programid'];
        $semester = $data['semesterid'];
        $classid = $data['classid'];
        $grademinval = $data['grademin'];
        $grademaxval = $data['grademax'];

        if (($stimeh == 'null') AND ( $stimem == 'null')) {
            $errors['starttimearr'] = 'Select the exam start time';
        } else {
            if ($stimeh == 'null') {
                $errors['starttimearr'] = 'Select the exam start time hour';
            }
            if ($stimem == 'null') {
                $errors['starttimearr'] = 'Select the exam start time minutes';
            }
        }

        if (($etimeh == 'null') AND ( $etimem == 'null')) {
            $errors['endtimearr'] = 'Select the exam end time';
        } else {
            if ($etimeh == 'null') {
                $errors['endtimearr'] = 'Select the exam end time hour';
            }
            if ($etimem == 'null') {
                $errors['endtimearr'] = 'Select the exam end time minutes';
            }
        }


        if ($id < 0) {

            $examexists = $DB->get_record('local_scheduledexams', array('schoolid' => $data['schoolid'], 'semesterid' => $data['semesterid'],
                'classid' => $data['classid'], 'examtype' => $data['examtype'], 'lecturetype' => $data['lecturetype']));
            if ($examexists) {
                $exam1 = $examexists->examtype;
                $exam2 = $data['examtype'];

                if ($exam1 == $exam2) {
                    $errors['examtype'] = get_string('examexits', 'local_scheduleexam');
                }
            }
        }

        if ($stimeh > $etimeh) {
            $errors['endtimearr'] = get_string('timevalidation', 'local_scheduleexam');
        }
        if ($id < 0) {
            $records = $DB->get_records_sql("SELECT * FROM {$CFG->prefix}local_scheduledexams
                                       WHERE schoolid = $school AND
                                       semesterid = $semester AND opendate = $opendate");
        }
        if ($id > 0) {
            $records = $DB->get_records_sql("SELECT * FROM {$CFG->prefix}local_scheduledexams
                                       WHERE schoolid = $school AND
                                       semesterid = $semester AND opendate = $opendate AND id!=$id");
        }

        foreach ($records as $record) {
            $record->starttimemin = ($record->starttimemin < 10) ? '0' . $record->starttimemin : $record->starttimemin;
            $record->endtimemin = ($record->endtimemin < 10) ? '0' . $record->endtimemin : $record->endtimemin;
            $starttime = $record->starttimehour . $record->starttimemin;
            $endtime = $record->endtimehour . $record->endtimemin;
            $thisstarttime = $stimeh . $stimem;
            $thisendtime = $etimeh . $etimem;

            //check if starttime or endtime of this is in the times of previous exams
            if (($thisstarttime >= $starttime && $thisstarttime <= $endtime) || ($thisendtime >= $starttime && $thisendtime <= $endtime)) {
                $errors['starttimearr'] = "An exam is already created from $record->starttimehour:$record->starttimemin to $record->endtimehour:$record->endtimemin for another class in this semester. Please change the timings";
            }
        }

        $date = $DB->get_record('local_semester', array('id' => $data['semesterid']));

        $check = (($date->startdate < $opendate) && ($date->enddate > $opendate));
        if (!$check) {
            if ($date->enddate > time()) {
                $date->startdate = date('m/d/Y', $date->startdate);
                $date->enddate = date('m/d/Y', $date->enddate);
                $errors['opendate'] = get_string('datevalidation', 'local_scheduleexam', $date);
            }
        }
        //-------------- Edited by hema--------------------------------------------
        /* Bug report #306  -  Exam Scheduling
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved- Added condition exam date should be greater than exam schedule date
         */


        $classschedule_info = $DB->get_record('local_scheduleclass', array('classid' => $classid));

        if ($classschedule_info) {
            $classdate = new stdclass();
            $classdate->startdate = date('d-M-Y', $classschedule_info->startdate);
            $classdate->enddate = date('d-M-Y', $classschedule_info->enddate);
        }
        $opendate_test = strtotime(date('d-m-Y', $opendate));
        $class_startdate = strtotime(date('d-m-Y', $classschedule_info->startdate));

        if ($opendate_test <= $class_startdate) {
            $errors['opendate'] = get_string('class_datevalidation', 'local_scheduleexam', $classdate);
        }

        //------------------------------------------------------------------------  
        if ($grademaxval < $grademinval) {
            $errors['grademax'] = "Maximum Grade value should be greater than Minimum Grade Value";
        }
        if ($data['grademin'] <= 0) {
            $errors['grademin'] = get_string('numeric', 'local_admission');
        }
        if ($data['grademax'] <= 0) {
            $errors['grademax'] = get_string('numeric', 'local_admission');
        }

        return $errors;
    }

}
