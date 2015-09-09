<?php

// T1.7 - Schedule class with schedule types


defined('MOODLE_INTERNAL') || die;
require_once($CFG->dirroot . '/lib/formslib.php');

class sheduleclass_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $mform = & $this->_form;
        $hierarchy = new hierarchy();
        $id = $this->_customdata['id'];
        $deptid = $this->_customdata['deptid'];
        $schoid = $this->_customdata['schoid'];
        $semid = $this->_customdata['semid'];
        $courseid = $this->_customdata['courseid'];
        $classid = $this->_customdata['classid'];
        $classtype = $this->_customdata['classtype'];

        // remove empty value in classtype
        if ($classtype) {
            foreach ($classtype as $key => $value) {
                if ($value == 0)
                    unset($classtype[$key]);
            }
        }

        if (isset($classid))
            $classinfo = $DB->get_record('local_clclasses', array('id' => $classid, 'visible' => 1));


        $deptid = (isset($classinfo->departmentid) ? $classinfo->departmentid : 0);
        $schoolid = (isset($classinfo->schoolid) ? $classinfo->schoolid : 0);
        $semid = (isset($classinfo->semesterid) ? $classinfo->semesterid : 0);

        $id = $this->_customdata['id'];
        //$PAGE->requires->yui_module('moodle-local_clclasses-chooser', 'M.local_clclasses.init_chooser', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->yui_module('moodle-local_timetable-timetable', 'M.local_timetable.init_timetable', array(array('formid' => $mform->getAttribute('id'))));
        $PAGE->requires->js('/local/timetable/unassign_type.js');
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
        $mform->setDefault('schoolid', $classinfo->schoolid);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'addsemesterlisthere');
        $mform->setType('addsemesterlisthere', PARAM_RAW);

        $mform->addElement('hidden', 'addclasslisthere');
        $mform->setType('addclasslisthere', PARAM_RAW);

        // adding classtype       
        $mform->addElement('header', 'settingsheader', get_string('classtype', 'local_timetable'));
        $classtypes = $DB->get_records_menu('local_class_scheduletype', array('schoolid' => $schoolid,'visible'=>1), '', 'id, classtype');
	if(sizeof($classtypes) > 0 ){
           $string_cltype = array('Select classtype', '-1' => 'All');
           $final_classtype = $string_cltype + $classtypes;
	  } 
	else  {
	  $string_cltype = array('Select classtype');   
          $final_classtype=$string_cltype + $classtypes;
	}

        if ($classid) {
            $select = $mform->addElement('select', 'classtype', get_string('classtype', 'local_timetable'), $final_classtype);
            $select->setMultiple(true);

            $mform->setType('classtype', PARAM_INT);
            $mform->addRule('classtype', get_string('required'), 'required', null, 'client');
        }


        $mform->addElement('html', '<a id="newclasstype" style="float:right;margin-right: 240px;cursor:pointer;"
			onclick="newclasstype(' . $schoolid . ')">' . get_string('classtype', 'local_timetable') . '</a>');

        $mform->addElement('html', '<div id="createclasstype"></div>');
        $mform->addElement('html', '<div id="myratings1"></div>');

        $mform->registerNoSubmitButton('scheduleclasstype');
        $mform->addElement('submit', 'scheduleclasstype', get_string('scheduleclasstype', 'local_timetable'));


        $scheduleclass = cobalt_scheduleclass::get_instance();
        $hour = $scheduleclass->hour();
        $min = $scheduleclass->min();
        $starttime = array();
        $endtime = array();
        /* Start of scheduleclass startdate */
        //providing semester startdate and enddate helpful to select date
        if ($semid) {
            $seminfo = $DB->get_record('local_semester', array('id' => $semid));
            $seminfo->startdate = date('d M Y', $seminfo->startdate);
            $seminfo->enddate = date('d M Y', $seminfo->enddate);
            $mform->addElement('static', 'datedescription', '', get_string('classschedule_datedescription', 'local_clclasses', $seminfo));
        }

        if ($classtype && $classinfo)
            $this->reapeat_schedulecontent_basedon_type($classtype, $classinfo);

        /* Bug Report #276
         * @author hemalatha c arun<hemalatha@eabyas.in> 
         * Resolved- if classrom created under the online course, no need to display classroom dropdown box 
         */
//        if ($DB->record_exists('local_clclasses', array('id' => $classid, 'online' => 1))) {
//            
//        } else {
//            $classroom = $hierarchy->get_records_cobaltselect_menu('local_classroom', "schoolid=$schoid AND visible=1", null, '', 'id,fullname', '--Select--');
//            //$classroom=1;
//            $mform->addElement('select', 'classroomid', get_string('classroomids', 'local_classroomresources'), $classroom);
//            if (count($classroom) <= 1) {
//                $navigationlink = $CFG->wwwroot . '/local/classroomresources/classroom.php?linkschool=' . $schoid . '';
//                $navigationmsg = get_string('navigation_info', 'local_collegestructure');
//                $linkname = get_string('createclassroom', 'local_classroomresources');
//                $mform->addElement('static', 'classroomids_empty', '', $hierarchy->cobalt_navigation_msg($navigationmsg, $linkname, $navigationlink, 'margin-bottom: -15px;
//line-height: 0px;'));
//            }
//            $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');
//        }
        /* Start of hidden fields */
        $time = time();
        $user = $USER->id;
        $mform->addElement('hidden', 'timecreated', $time);
        $mform->setType('timecreated', PARAM_INT);
        $mform->addElement('hidden', 'timemodified', $time);
        $mform->setType('timemodified', PARAM_INT);
        $mform->addElement('hidden', 'usermodified', $user);
        $mform->setType('usermodified', PARAM_INT);

        $mform->addElement('hidden', 'departmentid', $deptid);
        $mform->setType('departmentid', PARAM_INT);
        //
        $mform->addElement('hidden', 'schoid', $schoid);
        $mform->setType('schoid', PARAM_INT);
        $mform->addElement('hidden', 'deptid', $deptid);
        $mform->setType('deptid', PARAM_INT);
        //
        //   $mform->addElement('hidden', 'schoolid', $schoid);
        //  $mform->setType('schoolid', PARAM_INT);
        // $mform->addElement('hidden', 'semesterid', $semid);
        // $mform->setType('semesterid', PARAM_INT);
        //$mform->addElement('hidden', 'semid', $semid);
        //$mform->setType('semid', PARAM_INT);
        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);
        //$mform->addElement('hidden', 'classid', $classid);
        //$mform->setType('classid', PARAM_INT);
        /* Start of hidden id field */
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        /* Start of action buttons */
        $actionbutton = ($id > 0) ? get_string('scheduleclassroom', 'local_clclasses') : get_string('scheduleclassroom', 'local_clclasses');
        $this->add_action_buttons($cancel = true, $actionbutton);
    }

    function definition_after_data() {
        global $DB, $CFG;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $classid = $this->_customdata['classid'];
        if (isset($classid))
            $classinfo = $DB->get_record('local_clclasses', array('id' => $classid, 'visible' => 1));

        $hierarchy = new hierarchy();

        $tmobject = manage_timetable::getInstance();
        $formatvalue = $mform->getElementValue('schoolid');
        $formatvalue = $formatvalue[0];
        if ($formatvalue > 0) {
            $hierarchy->get_school_semesters($formatvalue);
            /*
             * ###Bug report #245  -  Training Management
             * @author Naveen Kumar<naveen@eabyas.in>
             * (Resolved) Changed function to get semesters. We need to get all upcoming and present semesters.
             *  Previous method only get presesnt active semester
             */


            $tools = classes_get_school_semesters($formatvalue);
            if ($id > 0) {
                // it works only for editing 
                $editrecord = $this->_customdata['tool'];
                $editsemestersvalue = $DB->get_records_sql_menu("select id, fullname  from {local_semester} where id=$editrecord->semesterid");
                $tools = $tools + $editsemestersvalue;
            }

            $newel = $mform->createElement('select', 'semesterid', get_string('semester', 'local_semesters'), $tools);
            $mform->insertElementBefore($newel, 'addsemesterlisthere');
            $mform->addHelpButton('semesterid', 'semester', 'local_semesters');
            $mform->addRule('semesterid', get_string('missingsemester', 'local_semesters'), 'required', null, 'client');
            $mform->setType('semesterid', PARAM_RAW);
            $mform->setDefault('semesterid', $classinfo->semesterid);
            $semestervalue = $mform->getElementValue('semesterid');
            //   $mform->registerNoSubmitButton('updatecourseformat');
            //  $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
        }
        if ($formatvalue > 0 && $semestervalue[0] > 0) {
            $tools = $tmobject->timetable_get_listofclasses($formatvalue, $semestervalue[0]);
            //  $tools = classes_get_school_semesters($formatvalue);
            $newclasslist = $mform->createElement('select', 'classid', get_string('classes', 'local_timetable'), $tools);
            $mform->insertElementBefore($newclasslist, 'addclasslisthere');
            //  $mform->addHelpButton('classid', 'semester', 'local_semesters');
            $mform->addRule('classid', get_string('missingclass', 'local_timetable'), 'required', null, 'client');
            $mform->setType('classid', PARAM_RAW);
            $mform->setDefault('classid', $classinfo->id);
        }
    }

    public function reapeat_schedulecontent_basedon_type($classtypes, $classinfo = null) {
        global $CFG, $DB, $USER, $PAGE;

        $PAGE->requires->js('/local/timetable/unassign_type.js');
        $mform = $this->_form;
        $tmobject = manage_timetable::getInstance();
        $hierarchy = new hierarchy();
        $deptid = (isset($classinfo->departmentid) ? $classinfo->departmentid : 0);
        $schoolid = (isset($classinfo->schoolid) ? $classinfo->schoolid : 0);

        // removing first value in array
        $key_first = array_search(0, $classtypes);
        if ($key_first)
            unset($classtypes[$key_first]);


        // checking they selected all options or not
        //echo 'allkey'.$allkey = array_search(-1, $classtypes);
        //if($allkey==0){
        // $classtypes =$DB->get_records_menu('local_class_scheduletype',array('schoolid'=>$schoolid),'','id, classtype');
        //$classtypes  =array_keys($classtypes);
        // print_object($classtypes);
        //}


        $list = array();
        $i = 1;

        if (!empty($classtypes)) {

            foreach ($classtypes as $classtype) {


                $cltype = $DB->get_record('local_class_scheduletype', array('id' => $classtype));
                $clname = $cltype->classtype;

                $mform->addElement('header', 'settingsheader', get_string('classtype_header', 'local_timetable', $clname));

                $mform->registerNoSubmitButton('deleteclasstype');
                $mform->addElement('submit', 'deleteclasstype', get_string('deleteclasstype', 'local_timetable', $clname), array('style' => 'float:right;', 'onclick' => 'unassign_classtype(' . $classtype . ')'));
                $mform->addElement('hidden', 'deleteclasstypeid');
                $mform->setDefault('deleteclasstypeid', 0);
                $mform->setType('deleteclasstypeid', PARAM_INT);

                //  $mform->setValue('deleteclasstypeid', $classtype);
                //   $mform->addElement('html','<div style="float:right;"><a href="'.$CFG->wwwroot.'/local/timetable/scheduleclass.php?deleteclasstypeid='.$classtype.'" method="post"> Delete</a></div>');
                // --- displaying dates
                $dates = array();
                $dates[] = &$mform->createElement('static', 'startdatelabel', null, get_string('from', 'local_clclasses'));
                $dates[] = &$mform->createElement('date_selector', 'startdate', '', get_string('from', 'local_clclasses'));

                $dates[] = &$mform->createElement('static', 'enddatelabel', null, get_string('to', 'local_clclasses'));
                $dates[] = &$mform->createElement('date_selector', 'enddate', '', get_string('to', 'local_clclasses'));

                $mform->addGroup($dates, $clname . 'dates', get_string('selectdates', 'local_timetable'), array('  '), true);
                $mform->addRule($clname . 'dates', null, 'required', null, 'client');

                $mform->setDefault($clname . 'dates[startdate]', time() + 3600 * 24);
                $mform->setDefault($clname . 'dates[enddate]', time() + 3600 * 24);

                //-------------------------------------------------------------
                //----------------- displaying days----------------------------------
                $days = array();
                $days[] = &$mform->createElement('advcheckbox', 'sun', '', get_string('sunday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'mon', '', get_string('monday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'tue', '', get_string('tuesday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'wed', '', get_string('wednesday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'thu', '', get_string('thursday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'fri', '', get_string('friday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $days[] = &$mform->createElement('advcheckbox', 'sat', '', get_string('saturday', 'local_clclasses'), array('group' => 1), array(0, 1));
                $mform->addGroup($days, $clname . 'days', 'Select Days', array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
                $mform->addRule($clname . 'days', null, 'required', null, 'client');

                // ------------------instructor info---------------------
                $instructorinfo = array();
                $instructors = $tmobject->timetable_addinstructor_field($classinfo->instructor);
                $count = sizeof($instructors);
                if (empty($instructors)) {
                    $instructorinfo[] = &$mform->createElement('static', 'instructor_emptyinfo', '', get_string('instructor_emptyinfo', 'local_timetable'));
                } else {
                    // if ($count > 1) {
                    $instructorinfo[] = &$mform->createElement('select', 'instructorid', get_string('instructor', 'local_clclasses'), $instructors);
                    //} else {
                    //    foreach ($instructors as $ins)
                    //        $instructorinfo[] = &$mform->createElement('static', 'department_emptyinfo', '', $ins);
                    //}
                }
                $mform->addGroup($instructorinfo, $clname . 'instructor', get_string('instructor', 'local_clclasses'), array('  ', '</br>'), true);


                //------------- displaying class intervals-----------------------------------         
                if (isset($classinfo->semesterid) && isset($classinfo->schoolid)) {
                    $classintervals = array();
                    $timeintervalslist = $DB->get_records_sql("select * from {local_timeintervals} where schoolid=$classinfo->schoolid and semesterid=$classinfo->semesterid order by starttime");
                    // $mform->addElement('header', 'timeintervals', get_string('intervals_header', 'local_timetable'));
                    //  $calssiuntervals[]=$mform->addElement('static', 'timeintervals[]', '', get_string('intervals_header', 'local_timetable'));

                    if (($timeintervalslist)) {
                        $i = 1;

                        foreach ($timeintervalslist as $timeintervals) {
                            // $timings[$timeintervals->id]=  $timeintervals
                            $timings[$timeintervals->id] = date('h:i a', strtotime($timeintervals->starttime)) . ' To ' . date('h:i a', strtotime($timeintervals->endtime)) . ' (Interval' . $i . ')';

                            $i++;
                        }
                        $timings = array('Select Timeintervals') + $timings;
                        $mform->addElement('select', $clname . '[timeinterval]', get_string('selecttimeinterval', 'local_timetable'), $timings);
                        $mform->setDefault($clname . '[timeinterval]', 0);
                    } else {

                        $mform->addElement('static', 'timeinterval_emptyinfo', get_string('selecttimeinterval', 'local_timetable'), get_string('timeinterval_emptyinfo', 'local_timetable'));
                    }
                }


                // $mform->addGroup($instructorinfo, $clname, '',  array('  ','</br>'), true);

                $mform->addElement('checkbox', $clname . '[othertimeinterval]', '', get_string('othetimeinterval', 'local_timetable'));
                // $mform->setType($clname.'othertimeinterval', PARAM_INT);
                // $mform->disabledIf($clname.'othertimeinterval', 'schoolid', 'eq', null);
                //  $mform->disabledIf($clname.'othertimeinterval', 'classroom_switch[cl_switch]', 'eq', 'online');



                for ($i = 0; $i <= 12; $i++) {
                    $hours[$i] = sprintf("%02d", $i);
                }
                for ($i = 0; $i < 60; $i+=5) {
                    $minutes[$i] = sprintf("%02d", $i);
                }


                $size = 'style="width:100px;"';
                $sec = array();
                $sec[] = & $mform->createElement('static', 'from_label', '', '<b>' . get_string('from', 'local_timetable') . ' : </b>');
                $sec[] = & $mform->createElement('select', 'starthours', get_string('hour', 'form'), $hours, false, true);
                $sec[] = & $mform->createElement('select', 'startminutes', get_string('minute', 'form'), $minutes, false, true);
                $sec[] = & $mform->createElement('select', 'start_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);
                $sec[] = & $mform->createElement('static', 'section_space', '', '&nbsp;&nbsp;&nbsp;');
                $sec[] = & $mform->createElement('static', 'to_label', '', '<b>' . get_string('to', 'local_timetable') . ' : </b>');
                $sec[] = & $mform->createElement('select', 'endhours', get_string('hour', 'form'), $hours, false, true);
                $sec[] = & $mform->createElement('select', 'endminutes', get_string('minute', 'form'), $minutes, false, true);
                $sec[] = & $mform->createElement('select', 'end_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);


                $mform->addGroup($sec, $clname . '[customtimeintervals]', '', array(''), true);
                //$mform->addRule($clname . '[customtimeintervals]', null, 'required', null, 'client');
                $mform->disabledIf($clname . '[customtimeintervals]', $clname . '[othertimeinterval]', 'notchecked');
                $mform->disabledIf($clname . '[timeinterval]', $clname . '[othertimeinterval]', 'checked');

                // displaying classroom resources
                if ($DB->record_exists('local_clclasses', array('id' => $classinfo->id, 'online' => 1))) {
                    
                } else {
                    $classroom = array();
                    if (isset($classinfo->semesterid) && isset($classinfo->schoolid) && isset($classinfo->id)) {

                        $classroom = $hierarchy->get_records_cobaltselect_menu('local_classroom', "schoolid=$classinfo->schoolid AND visible=1", null, '', 'id,fullname', '--Select--');
                    }
                    $mform->addElement('select', $clname . 'classroom[classroomid]', get_string('classroomids', 'local_classroomresources'), $classroom);
                    $mform->setType($clname . 'classroom[classroomid]', PARAM_INT);

                    //   $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');

                    $mform->addElement('checkbox', $clname . 'classroom[otherclroom]', '', get_string('otherroom', 'local_timetable'));
                    $mform->setType($clname . 'classroom[otherclroom]', PARAM_INT);
                    $mform->disabledIf($clname . 'classroom[otherclroom]', 'schoolid', 'eq', null);

                    $clroom_resource = array();
                    $clroom_resource[] = & $mform->createElement('text', 'building', '', array('placeholder' => 'Building'));
                    $clroom_resource[] = & $mform->createElement('text', 'floor', '', array('placeholder' => 'Floor'));
                    $clroom_resource[] = & $mform->createElement('text', 'classroom', '', array('placeholder' => 'Classroom'));
                    $mform->addGroup($clroom_resource, $clname . 'classroom[customclroom]', '', array('     '), true);
                    //  $mform->addRule('classroom_res', get_string('required'), 'required', null, 'client');
                    $mform->disabledIf($clname . 'classroom[customclroom]', 'eq', null);
                    $mform->disabledIf($clname . 'classroom[customclroom]', $clname . 'classroom[otherclroom]', 'notchecked');
                    $mform->disabledIf($clname . 'classroom[classroomid]', $clname . 'classroom[otherclroom]', 'checked');

                    $mform->setType($clname . 'classroom[customclroom][building]', PARAM_TEXT);
                    $mform->setType($clname . 'classroom[customclroom][floor]', PARAM_TEXT);
                    $mform->setType($clname . 'classroom[customclroom][classroom]', PARAM_TEXT);
                }
                // end of displaying classroom
            }// end of  main foreach
        }// end of if condition 
    }

// end of function

    public function validation($data, $files) {
        global $DB, $CFG;
        $tmobject = manage_timetable::getInstance();
        $errors = array();
        $classtypes = $data['classtype'];
        foreach ($classtypes as $cltype) {

            $classtypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $cltype));
            $clname = $classtypeinfo->classtype;
            $userstartdate = $data[$clname . 'dates[startdate]'];

            $userenddate = $data[$clname . 'dates[enddate]'];

            $noofdays = $userenddate - $userstartdate;
            $noofdays = floor($noofdays / (60 * 60 * 24)) + 1;


            // to hold available days from  slected date range
            for ($i = $userstartdate; $i <= $userenddate; $i += 86400) {
                // if (date('w', $i) != 0 && date('w', $i) != 6)
                $availabledays[] = date('w', $i);
            }

            $days_arrayname = $clname . 'days';
            $mon = $data[$days_arrayname]['mon'];
            $tue = $data[$days_arrayname]['tue'];
            $wed = $data[$days_arrayname]['wed'];
            $thu = $data[$days_arrayname]['thu'];
            $fri = $data[$days_arrayname]['fri'];
            $sat = $data[$days_arrayname]['sat'];
            $sun = $data[$days_arrayname]['sun'];

	    
	         // check selected dates fallin corresponding selected days  
           if ($noofdays < 7 && ((!in_array('1', $availabledays) && $mon) || (!in_array('2', $availabledays) && $tue) || (!in_array('3', $availabledays) && $wed) || (!in_array('4', $availabledays) && $thu) || (!in_array('5', $availabledays) && $fri)|| (!in_array('0', $availabledays) && $sun)|| (!in_array('6', $availabledays) && $sat))) {
                
              $workingdays = array(0,1, 2, 3, 4, 5, 6);
              $missingdays = array_diff($workingdays, $availabledays);           
              $dayslist = array('0' =>'Sunday','1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday','6' =>'Saturday');
              if (!empty($missingdays)) {
                $dateerror = get_string('availabledays_error','local_clclasses');
                foreach ($availabledays as $key => $value) {
                    $dateerror .= '<li>' . $dayslist[$value] . '</li>';
                }
                $errors[$clname . 'days'] = $dateerror;
              }
            }
	



            $no_day = $mon == 0 && $tue == 0 && $wed == 0 && $thu == 0 && $fri == 0 && $sun == 0 && $sat == 0;
            if ($no_day) {
                $errors[$clname . 'days'] = get_string('selecttheday', 'local_clclasses');
            }

            /* Bug report #283  -  Classes>Scheduling- Not before semester and   Bug report #284  
             * @author hemalatha c arun <hemalatha@eabyas.in>
             * Resolved - added validation when user select the startdate and enddate not in between the semester enddate and startdate 
             */

            // $semid = $this->_customdata['semesterid'];
            $semid = $data['semesterid'];
            $semesterinfo = $DB->get_record('local_semester', array('id' => $semid));

            $startdate = strtotime(date('d-m-Y', $userstartdate));
            $enddate = strtotime(date('d-m-Y', $userenddate));

            if ($semesterinfo) {
                $semdate = new stdclass();
                $semdate->startdate = date('d-M-Y', $semesterinfo->startdate);
                $semdate->enddate = date('d-M-Y', $semesterinfo->enddate);
            }

            $semstartdate = strtotime(date('d-m-Y', $semesterinfo->startdate));
            $semenddate = strtotime(date('d-m-Y', $semesterinfo->enddate));
            if ($startdate > $enddate)
                $errors[$clname . 'dates'] = get_string('startdatelessthan_enddate', 'local_clclasses');


            if ($semstartdate <= $startdate && $startdate <= $semenddate) {
                
            } else
                $errors[$clname . 'dates'] = get_string('datenotmatch_semstardate', 'local_clclasses', $semdate);

            if ($semstartdate <= $enddate && $enddate <= $semenddate) {
                
            } else
                $errors[$clname . 'dates'] = get_string('datenotmatch_semenddate', 'local_clclasses', $semdate);

            // if user selected custom timings, valdating is starttime and endtime     
            if (isset($data[$clname]['othertimeinterval'])) {
                $customtimeintervals = $data[$clname]['customtimeintervals'];

                $starthours = $customtimeintervals['starthours'];
                $startminutes = $customtimeintervals['startminutes'];
                $endhours = $customtimeintervals['endhours'];
                $endminutes = $customtimeintervals['endminutes'];
                $start_td = $customtimeintervals['start_td'];
                $end_td = $customtimeintervals['end_td'];
                if (($starthours == 0 && $startminutes == 0 ) || ($endhours == 0 && $endminutes == 0)) {
                    $errors[$clname . '[customtimeintervals]'] = get_string('customintervalempty', 'local_timetable');
                }


                $starttime = sprintf("%02d", $starthours) . ':' . sprintf("%02d", $startminutes) . $start_td;
                $endtime = sprintf("%02d", $endhours) . ':' . sprintf("%02d", $endminutes) . $end_td;
                $start_timestamp = strtotime($starttime);
                $end_timestamp = strtotime($endtime);
		
		
		// checking already is time intervals available or what , if so forcing user to select the defined intervals
	        $schoolid= $data['schoolid']; $semesterid=$data['semesterid'];
	    
	        $tempstarttime = date('H:i:s', strtotime($starttime));
	        $tempendtime = date('H:i:s', strtotime($endtime));
	    
	        $sql= "select * from mdl_local_timeintervals where schoolid=1 and semesterid=1
                                and '$tempstarttime' = starttime and '$tempendtime' = endtime";
				 
	        $exists_timeintervals = $DB->get_records_sql($sql);
	        if(sizeof($exists_timeintervals)>0) 
	        $errors[$clname . '[timeinterval]'] = get_string('existtimeintervals', 'local_timetable');
            //---------------------------------------------------------------------------------------------------


                if ($start_timestamp > $end_timestamp)
                    $errors[$clname . '[customtimeintervals]'] = 'starttime should be less than endtime';
            }// end of timeintervals validation
            else {
                if ($data[$clname]['timeinterval'] <= 0)
                    $errors[$clname . '[timeinterval]'] = get_string('emptytimeinterval', 'local_timetable');
            }

            // comparision starts from here

            $response = $this->checking_conflicts_static_classtypes($data, $classtypes, $cltype);

            if ($response)
                $errors[$clname . '[timeinterval]'] = 'class room is busy ';


            $timearray = $tmobject->timetable_get_timeformat($data, $clname);
            $availabledaysarray = $tmobject->timetable_get_availabledays_format($data, $clname);
            $classroomarrayname = $clname . 'classroom';
            if (isset($classroomarrayname['classroomid']))
                $classroomid = $classroomarrayname['classroomid'];
            else
                $classroomid = 0;

            if ($userstartdate != null && $userenddate != null && $timearray['starttime'] != null && $timearray['endtime'] != null && !empty($availabledaysarray)) {
                $value = $tmobject->check_conflicts($userstartdate, $userenddate, $timearray['starttime'], $timearray['endtime'], $data['schoolid'], $classroomid, $data['id'], $availabledaysarray);
                if ($value > 0) {
                    $errors[$clname . '[timeinterval]'] = 'This classroom is busy';
                }
            }// end of foreach
        }// end of function 
        //if ($data['timeintervalid'] != null && $data['classroomid'] != null && $data['instructorid'] != null) {
        //
        //    /* Bug report #254 
        //     * @author hemalatha c arun<hemalatha@eabyas.in>
        //     * Resolved- (provided proper validation)while scheduling classroom -eventhough classroom is free it showing busy.
        //     */
        //    //$starttime['starthour'] = array($data['starthour']);
        //    //$starttime['startmin'] = array($data['startmin']);
        //    //$endtime['endhour'] = array($data['endhour']);
        //    //$endtime['endmin'] = array($data['endmin']);
        //    $timeintervals = $DB->get_record('local_timeintervals', array('id' => $data['timeintervalid']));
        //    $starttime = $timeintervals->starttime;
        //    $endtime = $timeintervals->endtime;
        //    $data['mon'] ? $availabledates[] = 'M' : null;
        //    $data['tue'] ? $availabledates[] = 'TU' : null;
        //    $data['wed'] ? $availabledates[] = 'W' : null;
        //    $data['thu'] ? $availabledates[] = 'TH' : null;
        //    $data['fri'] ? $availabledates[] = 'F' : null;
        //    $data['sat'] ? $availabledates[] = 'SA' : null;
        //    $data['sun'] ? $availabledates[] = 'SU' : null;
        //    $availabledates = implode('-', $availabledates);
        //
        //
        //    $value = check_conflicts($data['startdate'], $data['enddate'], $starttime, $endtime, $data['schoolid'], $data['instructorid'], $data['classroomid'], $data['id'], $availabledates);
        //    if ($value > 0) {
        //        $errors['classroomid'] = 'This classroom is busy';
        //    }
        //}
        return $errors;
    }

    function checking_conflicts_static_classtypes($data, $classtypes, $cltype) {
        global $CFG, $DB;
        $tmobject = manage_timetable::getInstance();
        $flag = 0;
        // function is called from outer array
        $outerclasstypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $cltype));
        $outerclname = $outerclasstypeinfo->classtype;
        $outerstartdate = $data[$outerclname . 'dates[startdate]'];
        $outerenddate = $data[$outerclname . 'dates[enddate]'];
        $outertimearray = $tmobject->timetable_get_timeformat($data, $outerclname);

        $outerdaysarray = $tmobject->timetable_get_availabledays_format($data, $outerclname);
        $innerclasstypes = $classtypes;

        foreach ($innerclasstypes as $innerclasstype) {
            $innerclasstypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $innerclasstype));
            if ($outerclasstypeinfo->id != $innerclasstypeinfo->id) {

                $innerclname = $innerclasstypeinfo->classtype;
                $innerstartdate = $data[$innerclname . 'dates[startdate]'];
                $innerenddate = $data[$innerclname . 'dates[enddate]'];

                if (($innerstartdate <= $outerstartdate && $outerstartdate <= $innerenddate) || ($innerstartdate <= $outerenddate && $outerenddate <= $innerenddate)) {

                    $innertimearray = $tmobject->timetable_get_timeformat($data, $innerclname);
                    if (($innertimearray['starttime'] < $outertimearray['starttime'] && $outertimearray['starttime'] < $innertimearray['endtime']) || ($innertimearray['starttime'] < $outertimearray['endtime'] && $outertimearray['endtime'] < $innertimearray['endtime'])) {

                        $innerdaysarray = $tmobject->timetable_get_availabledays_format($data, $innerclname);

                        $result = array_intersect($outerdaysarray, $innerdaysarray);
                        if (empty($result))
                            $result = array_intersect($innerdaysarray, $outerdaysarray);
                        if (!empty($result)) {

                            $flag = 1;
                            break;
                        }
                    }
                }
            }// end of main if
        } // end of foreach
        if ($flag)
            return true;
        else
            return false;
    }

// end of function   
}

// end of class
// edit scheduleclass form
class edit_sheduleclass_form extends moodleform {

    public function definition() {
        global $CFG, $DB, $PAGE, $USER;
        $mform = & $this->_form;
        $hierarchy = new hierarchy();
        $id = $this->_customdata['id'];

        $classid = $this->_customdata['classid'];
        $classtype = $this->_customdata['classtype'];
        $courseid = $this->_customdata['courseid'];
        $classtypeid = $this->_customdata['classtypeid'];

        $scheduledinfo = $DB->get_record('local_scheduleclass', array('id' => $id));

        if (isset($classid))
            $classinfo = $DB->get_record('local_clclasses', array('id' => $classid, 'visible' => 1));


        $deptid = (isset($classinfo->departmentid) ? $classinfo->departmentid : 0);
        $schoolid = (isset($classinfo->schoolid) ? $classinfo->schoolid : 0);
        $semid = (isset($classinfo->semesterid) ? $classinfo->semesterid : 0);

        $id = $this->_customdata['id'];

        $classtypes = $DB->get_records_menu('local_class_scheduletype', array('schoolid' => $schoolid), '', 'id, classtype');
        $string_cltype = array('Select classtype', '-1' => 'All');
        $final_classtype = $string_cltype + $classtypes;

        $scheduleclass = cobalt_scheduleclass::get_instance();
        $hour = $scheduleclass->hour();
        $min = $scheduleclass->min();
        $starttime = array();
        $endtime = array();
        /* Start of scheduleclass startdate */
        //providing semester startdate and enddate helpful to select date
        if ($semid) {
            $seminfo = $DB->get_record('local_semester', array('id' => $semid));
            $seminfo->startdate = date('d M Y', $seminfo->startdate);
            $seminfo->enddate = date('d M Y', $seminfo->enddate);
            $mform->addElement('static', 'datedescription', '', get_string('classschedule_datedescription', 'local_clclasses', $seminfo));
        }

        //  $mform->addElement('', PARAM_INT);
        //  $mform->setDefault('classtype',$classtype);

        if ($classtypeid && $classinfo)
            $this->reapeat_schedulecontent_basedon_type($classtypeid, $classinfo);


        $time = time();
        $user = $USER->id;
        $mform->addElement('hidden', 'timecreated', $time);
        $mform->setType('timecreated', PARAM_INT);
        $mform->addElement('hidden', 'timemodified', $time);
        $mform->setType('timemodified', PARAM_INT);
        $mform->addElement('hidden', 'usermodified', $user);
        $mform->setType('usermodified', PARAM_INT);

        $mform->addElement('hidden', 'departmentid', $deptid);
        $mform->setType('departmentid', PARAM_INT);

        $mform->addElement('hidden', 'schoid', $schoolid);
        $mform->setType('schoid', PARAM_INT);

        $mform->addElement('hidden', 'schoolid', $schoolid);
        $mform->setType('schoolid', PARAM_INT);

        $mform->addElement('hidden', 'deptid', $deptid);
        $mform->setType('deptid', PARAM_INT);

        $mform->addElement('hidden', 'courseid', $courseid);
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'classid', $classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'semesterid', $semid);
        $mform->setType('semesterid', PARAM_INT);

        $mform->addElement('hidden', 'classtypeid', $classtypeid);
        $mform->setType('classtypeid', PARAM_INT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        /* Start of action buttons */
        $actionbutton = ($id > 0) ? get_string('scheduleclassroom', 'local_clclasses') : get_string('scheduleclassroom', 'local_clclasses');
        $this->add_action_buttons($cancel = true, $actionbutton);
    }

    public function reapeat_schedulecontent_basedon_type($classtypeid, $classinfo = null) {
        global $CFG, $DB, $USER, $PAGE;

        $PAGE->requires->js('/local/timetable/unassign_type.js');
        $mform = $this->_form;
        $tmobject = manage_timetable::getInstance();
        $hierarchy = new hierarchy();
        $deptid = (isset($classinfo->departmentid) ? $classinfo->departmentid : 0);
        $schoolid = (isset($classinfo->schoolid) ? $classinfo->schoolid : 0);



        $list = array();
        $i = 1;

        if (!empty($classtypeid)) {

            //  foreach ($classtypes as $classtype) {

            $cltype = $DB->get_record('local_class_scheduletype', array('id' => $classtypeid));
            $clname = $cltype->classtype;

            $mform->addElement('header', 'settingsheader', get_string('classtype_header', 'local_timetable', $clname));

            // --- displaying dates
            $dates = array();
            $dates[] = &$mform->createElement('static', 'startdatelabel', null, get_string('from', 'local_clclasses'));
            $dates[] = &$mform->createElement('date_selector', 'startdate', '', get_string('from', 'local_clclasses'));

            $dates[] = &$mform->createElement('static', 'enddatelabel', null, get_string('to', 'local_clclasses'));
            $dates[] = &$mform->createElement('date_selector', 'enddate', '', get_string('to', 'local_clclasses'));

            $mform->addGroup($dates, $clname . 'dates', get_string('selectdates', 'local_timetable'), array('  '), true);
            $mform->addRule($clname . 'dates', null, 'required', null, 'client');

            $mform->setDefault($clname . 'dates[startdate]', time() + 3600 * 24);
            $mform->setDefault($clname . 'dates[enddate]', time() + 3600 * 24);

            //-------------------------------------------------------------
            //----------------- displaying days----------------------------------
            $days = array();
            $days[] = &$mform->createElement('advcheckbox', 'sun', '', get_string('sunday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'mon', '', get_string('monday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'tue', '', get_string('tuesday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'wed', '', get_string('wednesday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'thu', '', get_string('thursday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'fri', '', get_string('friday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $days[] = &$mform->createElement('advcheckbox', 'sat', '', get_string('saturday', 'local_clclasses'), array('group' => 1), array(0, 1));
            $mform->addGroup($days, $clname . 'days', 'Select Days', array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
            $mform->addRule($clname . 'days', null, 'required', null, 'client');


            // ------------------instructor info---------------------
                $instructorinfo = array();
                $instructors = $tmobject->timetable_addinstructor_field($classinfo->instructor);
                $count = sizeof($instructors);
                if (empty($instructors)) {
                    $instructorinfo[] = &$mform->createElement('static', 'instructor_emptyinfo', '', get_string('instructor_emptyinfo', 'local_timetable'));
                } else {
                    // if ($count > 1) {
                    $instructorinfo[] = &$mform->createElement('select', 'instructorid', get_string('instructor', 'local_clclasses'), $instructors);
                    //} else {
                    //    foreach ($instructors as $ins)
                    //        $instructorinfo[] = &$mform->createElement('static', 'department_emptyinfo', '', $ins);
                    //}
                }
                $mform->addGroup($instructorinfo, $clname . 'instructor', get_string('instructor', 'local_clclasses'), array('  ', '</br>'), true);


            //------------- displaying class intervals-----------------------------------         
            if (isset($classinfo->semesterid) && isset($classinfo->schoolid)) {
                $classintervals = array();
                $timeintervalslist = $DB->get_records_sql("select * from {local_timeintervals} where schoolid=$classinfo->schoolid and semesterid=$classinfo->semesterid and visible=>1 order by starttime");
                // $mform->addElement('header', 'timeintervals', get_string('intervals_header', 'local_timetable'));
                //  $calssiuntervals[]=$mform->addElement('static', 'timeintervals[]', '', get_string('intervals_header', 'local_timetable'));

                if (($timeintervalslist)) {
                    $i = 1;

                    foreach ($timeintervalslist as $timeintervals) {
                        // $timings[$timeintervals->id]=  $timeintervals
                        $timings[$timeintervals->id] = date('h:i a', strtotime($timeintervals->starttime)) . ' To ' . date('h:i a', strtotime($timeintervals->endtime)) . ' (Interval' . $i . ')';

                        $i++;
                    }
                    $timings = array('Select Timeintervals') + $timings;
                    $mform->addElement('select', $clname . '[timeinterval]', get_string('selecttimeinterval', 'local_timetable'), $timings);
                    $mform->setDefault($clname . '[timeinterval]', 0);
                } else {

                    $mform->addElement('static', 'timeinterval_emptyinfo', get_string('selecttimeinterval', 'local_timetable'), get_string('timeinterval_emptyinfo', 'local_timetable'));
                }
            }


            // $mform->addGroup($instructorinfo, $clname, '',  array('  ','</br>'), true);

            $mform->addElement('checkbox', $clname . '[othertimeinterval]', '', get_string('othetimeinterval', 'local_timetable'));
            // $mform->setType($clname.'othertimeinterval', PARAM_INT);
            // $mform->disabledIf($clname.'othertimeinterval', 'schoolid', 'eq', null);
            //  $mform->disabledIf($clname.'othertimeinterval', 'classroom_switch[cl_switch]', 'eq', 'online');



            for ($i = 0; $i <= 12; $i++) {
                $hours[$i] = sprintf("%02d", $i);
            }
            for ($i = 0; $i < 60; $i+=5) {
                $minutes[$i] = sprintf("%02d", $i);
            }


            $size = 'style="width:100px;"';
            $sec = array();
            $sec[] = & $mform->createElement('static', 'from_label', '', '<b>' . get_string('from', 'local_timetable') . ' : </b>');
            $sec[] = & $mform->createElement('select', 'starthours', get_string('hour', 'form'), $hours, false, true);
            $sec[] = & $mform->createElement('select', 'startminutes', get_string('minute', 'form'), $minutes, false, true);
            $sec[] = & $mform->createElement('select', 'start_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);
            $sec[] = & $mform->createElement('static', 'section_space', '', '&nbsp;&nbsp;&nbsp;');
            $sec[] = & $mform->createElement('static', 'to_label', '', '<b>' . get_string('to', 'local_timetable') . ' : </b>');
            $sec[] = & $mform->createElement('select', 'endhours', get_string('hour', 'form'), $hours, false, true);
            $sec[] = & $mform->createElement('select', 'endminutes', get_string('minute', 'form'), $minutes, false, true);
            $sec[] = & $mform->createElement('select', 'end_td', get_string('minute', 'form'), array('am' => 'AM', 'pm' => 'PM'), false, true);


            $mform->addGroup($sec, $clname . '[customtimeintervals]', '', array(''), true);
            // $mform->addRule($clname . '[customtimeintervals]', null, 'required', null, 'client');
            $mform->disabledIf($clname . '[customtimeintervals]', $clname . '[othertimeinterval]', 'notchecked');
            $mform->disabledIf($clname . '[timeinterval]', $clname . '[othertimeinterval]', 'checked');

            // displaying classroom resources
            if ($DB->record_exists('local_clclasses', array('id' => $classinfo->id, 'online' => 1))) {
                
            } else {
                $classroom = array();
                if (isset($classinfo->semesterid) && isset($classinfo->schoolid) && isset($classinfo->id)) {

                    $classroom = $hierarchy->get_records_cobaltselect_menu('local_classroom', "schoolid=$classinfo->schoolid AND visible=1", null, '', 'id,fullname', '--Select--');
                }
                $mform->addElement('select', $clname . 'classroom[classroomid]', get_string('classroomids', 'local_classroomresources'), $classroom);
                $mform->setType($clname . 'classroom[classroomid]', PARAM_INT);

                //   $mform->addRule('classroomid', get_string('required'), 'required', null, 'client');

                $mform->addElement('checkbox', $clname . 'classroom[otherclroom]', '', get_string('otherroom', 'local_timetable'));
                $mform->setType($clname . 'classroom[otherclroom]', PARAM_INT);
                $mform->disabledIf($clname . 'classroom[otherclroom]', 'schoolid', 'eq', null);

                $clroom_resource = array();
                $clroom_resource[] = & $mform->createElement('text', 'building', '', array('placeholder' => 'Building'));
                $clroom_resource[] = & $mform->createElement('text', 'floor', '', array('placeholder' => 'Floor'));
                $clroom_resource[] = & $mform->createElement('text', 'classroom', '', array('placeholder' => 'Classroom'));
                $mform->addGroup($clroom_resource, $clname . 'classroom[customclroom]', '', array('     '), true);
                //  $mform->addRule('classroom_res', get_string('required'), 'required', null, 'client');
                $mform->disabledIf($clname . 'classroom[customclroom]', 'eq', null);
                $mform->disabledIf($clname . 'classroom[customclroom]', $clname . 'classroom[otherclroom]', 'notchecked');
                $mform->disabledIf($clname . 'classroom[classroomid]', $clname . 'classroom[otherclroom]', 'checked');

                $mform->setType($clname . 'classroom[customclroom][building]', PARAM_TEXT);
                $mform->setType($clname . 'classroom[customclroom][floor]', PARAM_TEXT);
                $mform->setType($clname . 'classroom[customclroom][classroom]', PARAM_TEXT);
            }
            // end of displaying classroom
            //   }// end of  main foreach
        }// end of if condition 
    }

    public function validation($data, $files) {
        global $DB, $CFG;
	
        $tmobject = manage_timetable::getInstance();
        $errors = array();
        $classtypeid = $data['classtypeid'];
	
	
        // foreach ($classtypes as $cltype) {

        $classtypeinfo = $DB->get_record('local_class_scheduletype', array('id' => $classtypeid));
        $clname = $classtypeinfo->classtype;
        $userstartdate = $data[$clname . 'dates[startdate]'];

        $userenddate = $data[$clname . 'dates[enddate]'];

        $noofdays = $userenddate - $userstartdate;
        $noofdays = floor($noofdays / (60 * 60 * 24)) + 1;


        // to hold available days from  slected date range
        for ($i = $userstartdate; $i <= $userenddate; $i += 86400) {
            // if (date('w', $i) != 0 && date('w', $i) != 6)
            $availabledays[] = date('w', $i);
        }

	$days_arrayname = $clname . 'days';
        $mon = $data[$days_arrayname]['mon'];
        $tue = $data[$days_arrayname]['tue'];
        $wed = $data[$days_arrayname]['wed'];
        $thu = $data[$days_arrayname]['thu'];
        $fri = $data[$days_arrayname]['fri'];
        $sat = $data[$days_arrayname]['sat'];
        $sun = $data[$days_arrayname]['sun'];	
	
	     // check selected dates fallin corresponding selected days  
       if ($noofdays < 7 && ((!in_array('1', $availabledays) && $mon) || (!in_array('2', $availabledays) && $tue) || (!in_array('3', $availabledays) && $wed) || (!in_array('4', $availabledays) && $thu) || (!in_array('5', $availabledays) && $fri)|| (!in_array('0', $availabledays) && $sun)|| (!in_array('6', $availabledays) && $sat))) {
                
            $workingdays = array(0,1, 2, 3, 4, 5, 6);
            $missingdays = array_diff($workingdays, $availabledays);           
            $dayslist = array('0' =>'Sunday','1' => 'Monday', '2' => 'Tuesday', '3' => 'Wednesday', '4' => 'Thursday', '5' => 'Friday','6' =>'Saturday');
            if (!empty($missingdays)) {
                $dateerror = get_string('availabledays_error','local_clclasses');
                foreach ($availabledays as $key => $value) {
                    $dateerror .= '<li>' . $dayslist[$value] . '</li>';
                }
                $errors[$clname . 'days'] = $dateerror;
            }
        }
	



        $no_day = $mon == 0 && $tue == 0 && $wed == 0 && $thu == 0 && $fri == 0 && $sun == 0 && $sat == 0;
        if ($no_day) {
            $errors[$clname . 'days'] = get_string('selecttheday', 'local_clclasses');
        }

        /* Bug report #283  -  Classes>Scheduling- Not before semester and   Bug report #284  
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved - added validation when user select the startdate and enddate not in between the semester enddate and startdate 
         */

        // $semid = $this->_customdata['semesterid'];
        $semid = $data['semesterid'];
        $semesterinfo = $DB->get_record('local_semester', array('id' => $semid));

        $startdate = strtotime(date('d-m-Y', $userstartdate));
        $enddate = strtotime(date('d-m-Y', $userenddate));
        if ($semesterinfo) {
            $semdate = new stdclass();
            $semdate->startdate = date('d-M-Y', $semesterinfo->startdate);
            $semdate->enddate = date('d-M-Y', $semesterinfo->enddate);
        }

	 if ($startdate > $enddate)
                $errors[$clname . 'dates'] = get_string('startdatelessthan_enddate', 'local_clclasses');    
	    
	    
	    
        $semstartdate = strtotime(date('d-m-Y', $semesterinfo->startdate));
        $semenddate = strtotime(date('d-m-Y', $semesterinfo->enddate));
        if ($semstartdate <= $startdate && $startdate <= $semenddate) {
            
        } else
            $errors[$clname . 'dates'] = get_string('datenotmatch_semstardate', 'local_clclasses', $semdate);

        if ($semstartdate <= $enddate && $enddate <= $semenddate) {
            
        } else
            $errors[$clname . 'dates'] = get_string('datenotmatch_semenddate', 'local_clclasses', $semdate);

	    
	 
        // if user selected custom timings, valdating is starttime and endtime     
        if (isset($data[$clname]['othertimeinterval'])) {
            $customtimeintervals = $data[$clname]['customtimeintervals'];

            $starthours = $customtimeintervals['starthours'];
            $startminutes = $customtimeintervals['startminutes'];
            $endhours = $customtimeintervals['endhours'];
            $endminutes = $customtimeintervals['endminutes'];
            $start_td = $customtimeintervals['start_td'];
            $end_td = $customtimeintervals['end_td'];
            if (($starthours == 0 && $startminutes == 0 ) || ($endhours == 0 && $endminutes == 0)) {
                $errors[$clname . '[customtimeintervals]'] = get_string('customintervalempty', 'local_timetable');
            }


            $starttime = sprintf("%02d", $starthours) . ':' . sprintf("%02d", $startminutes) . $start_td;
            $endtime = sprintf("%02d", $endhours) . ':' . sprintf("%02d", $endminutes) . $end_td;
            $start_timestamp = strtotime($starttime);
            $end_timestamp = strtotime($endtime);
	    
	    // checking already is time intervals available or what , if so forcing user to select the defined intervals
	    $schoolid= $data['schoolid']; $semesterid=$data['semesterid'];
	    
	    $tempstarttime = date('H:i:s', strtotime($starttime));
	    $tempendtime = date('H:i:s', strtotime($endtime));
	    
	    $sql= "select * from mdl_local_timeintervals where schoolid=1 and semesterid=1
                                and '$tempstarttime' = starttime and '$tempendtime' = endtime";
				 
	    $exists_timeintervals = $DB->get_records_sql($sql);
	    if(sizeof($exists_timeintervals)>0) 
	      $errors[$clname . '[timeinterval]'] = get_string('existtimeintervals', 'local_timetable');
            //---------------------------------------------------------------------------------------------------

            if ($start_timestamp > $end_timestamp)
                $errors[$clname . '[customtimeintervals]'] = 'starttime should be less than endtime';
        }// end of timeintervals validation
        else {
            if ($data[$clname]['timeinterval'] <= 0)
                $errors[$clname . '[timeinterval]'] = get_string('emptytimeinterval', 'local_timetable');
        }

        // comparision starts from here
        //  $response = $this->checking_conflicts_static_classtypes($data, $classtypes, $cltype);
        //  if ($response)
        //     $errors[$clname . '[timeinterval]'] = 'class room is busy ';


        $timearray = $tmobject->timetable_get_timeformat($data, $clname);

        $availabledaysarray = $tmobject->timetable_get_availabledays_format($data, $clname);
        $classroomarrayname = $clname . 'classroom';
        if (isset($classroomarrayname['classroomid']))
            $classroomid = $classroomarrayname['classroomid'];
        else
            $classroomid = 0;

        if ($userstartdate != null && $userenddate != null && $timearray['starttime'] != null && $timearray['endtime'] != null && !empty($availabledaysarray)) {
            $value = $tmobject->check_conflicts($userstartdate, $userenddate, $timearray['starttime'], $timearray['endtime'], $data['schoolid'], $classroomid, $data['id'], $availabledaysarray);
            if ($value > 0) {
                $errors[$clname . '[timeinterval]'] = 'This classroom is busy';
            }
            // }// end of foreach
        }// end of function 

        return $errors;
    }

}

// end of class





