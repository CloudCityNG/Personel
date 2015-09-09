<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');

$hierarchy = new hierarchy();

class settimings_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE, $OUTPUT;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $scid = null;
        if (isset($this->_customdata['scid'])) {
            $scid = $this->_customdata['scid'];
        }
        $PAGE->requires->yui_module('moodle-local_timetable-timetable', 'M.local_timetable.init_timetable', array(array('formid' => $mform->getAttribute('id'))));
        //  $heading = ($id > 0) ? get_string('editclass', 'local_class') : get_string('createclass', 'local_class') ;

        $mform->addElement('header', 'settingsheader', get_string('settime_intervals', 'local_timetable'));
        //if ($id < 0) {
        //    $mform->addHelpButton('settingsheader', 'createclass', 'local_timetable');
        //}

        $hier = new hierarchy();
        $timetable_ob = manage_timetable::getInstance();
        $timetable_ob->school_formelement_condition($mform);
        
        
        
        $mform->addElement('hidden', 'addsemesterlisthere');
        $mform->setType('addsemesterlisthere', PARAM_INT);

        //$mform->addElement('advcheckbox', 'visible', get_string('publish','local_class'), null, array('group'=>1), array(0, 1));
        $mform->addElement('checkbox', 'visible', get_string('publish', 'local_timetable'), '', array('checked' => 'checked', 'name' => 'my-checkbox', 'data-size' => 'small', 'data-on-color' => 'info', 'data-off-color' => 'warning', 'data-on-text' => 'Yes', 'data-switch-set' => 'size', 'data-off-text' => 'No', 'class' => 'btn btn-default'));
        $mform->addHelpButton('visible', 'publish', 'local_timetable');
        $mform->setDefault('visible', true);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);


        // Sections start *******************************************************************

        include('settimeintervals_form.php');
        //class_section_fields($mform, $id, $style);

        $submitlable = ($id > 0) ? get_string('submit') : get_string('submit');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    function definition_after_data() {
        global $DB;
        $mform = $this->_form;

        $hierarchy = new hierarchy();
        //$eid = $this->_customdata['temp'];
        //  if ($eid->id < 0) {
        $school = $mform->getElementValue('schoolid');      

        $tools = array();
        if ($school[0] > 0 || $school>0) {
              $fid = $school[0];
            if(isset($school[0]))
             $schoolid=$school[0];
            else
            $schoolid = $school;
            $hierarchy = new hierarchy();

            $upcomingsemester = $hierarchy->get_upcoming_school_semesters($schoolid);
            //   print_object($upcomingsemester);
            $activesemester = $hierarchy->get_allmyactivesemester(null, $schoolid);
            //     print_object($activesemester);
            $active_upcomingsemester = $upcomingsemester + $activesemester;

            $newel = $mform->createElement('select', 'semesterid', get_string('selectsemester_timetable', 'local_timetable'), $active_upcomingsemester);
            $mform->insertElementBefore($newel, 'addsemesterlisthere');    
            $mform->addRule('semesterid', get_string('selectsemester_timetable', 'local_timetable'), 'required', null, 'client');
            // $formatvalue2 [0];
        }
        //  }
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;


        $schoolid = $data['schoolid'];
        $semesterid = $data['semesterid'];
        $repeating_counts = $data['section_repeats'];
        $starthours = $data['starthours'];
        $startminutes = $data['startminutes'];
        $endhours = $data['endhours'];
        $endminutes = $data['endminutes'];
        $start_td = $data['start_td'];
        $end_td = $data['end_td'];


        for ($i = 0; $i < $repeating_counts; $i++) {

            //$starttime_sec = ($starthours[$i]*3600)+($startminutes[$i]*60); // coverting into seconds
            //$endtime_sec = ($endhours[$i]*3600)+($endminutes[$i]*60);


            $starttime = sprintf("%02d", $starthours[$i]) . ':' . sprintf("%02d", $startminutes[$i]) . $start_td[$i];
            $endtime = sprintf("%02d", $endhours[$i]) . ':' . sprintf("%02d", $endminutes[$i]) . $end_td[$i];
            $start_timestamp = strtotime($starttime);
            $end_timestamp = strtotime($endtime);
    
 
           //    if(( !$starthours[$i]) || ( !$endhours[$i])){
           //         $errors['section_array[' . $i . ']'] = get_string('notvalid_time','local_timetable');               
          //     }

          

            if ($start_timestamp > $end_timestamp)
                $errors['section_array[' . $i . ']'] = get_string('starttimelessthan_endtime','local_timetable');

            //echo "select * from {local_timeintervals} where schoolid=$schoolid and semesterid=$semesterid
            //                     and (CAST($starttime AS TIME) between starttime and endtime) and
            //                     (CAST($endtime AS TIME) between starttime and endtime) ";
            
            // check between times
            //$response = $DB->get_records_sql("select * from {local_timeintervals} where schoolid=$schoolid and semesterid=$semesterid
            //                     and (CAST('$starttime' AS TIME) = starttime and endtime) or
            //                     (CAST('$endtime' AS TIME) between starttime and endtime) ");

          //echo    "select * from {local_timeintervals} where schoolid=$schoolid and semesterid=$semesterid
          //                       and ((CAST('$starttime' AS TIME) = starttime) or
          //                       (CAST('$endtime' AS TIME) = endtime)) ";
          //                       
          //                       exit;
            // check same  time                     
          //  $sametime_response = $DB->get_records_sql("select * from {local_timeintervals} where schoolid=$schoolid and semesterid=$semesterid
          //                       and (CAST('$starttime' AS TIME) = starttime) or
          //                       (CAST('$endtime' AS TIME) = endtime) ");                      
          ////  if($response)
          //  if($sametime_response){
          //        $errors['section_array[' . $i . ']'] = '' . $i . ' Enter valid time'; 
          //      
          //  }
  
            // WHERE 
            //(CAST(`rangeStart` AS TIME) BETWEEN `StartTime` AND `EndTime`) AND 
            //(CAST(`rangeEnd` AS TIME) BETWEEN `StartTime` AND `EndTime`)

            $row_starttime = $start_timestamp;
            $row_endtime = $end_timestamp;
            for ($j = 0; $j < $repeating_counts; $j++) {
                //  echo $j.'</br>';
                if ($i != $j) {
                    $starttime = sprintf("%02d", $starthours[$j]) . ':' . sprintf("%02d", $startminutes[$j]) . $start_td[$j];

                    $endtime = sprintf("%02d", $endhours[$j]) . ':' . sprintf("%02d", $endminutes[$j]) . $end_td[$j];
                    $start_timestamp = strtotime($starttime);              
                    $end_timestamp = strtotime($endtime);
                    if ( $start_timestamp > 0 && $end_timestamp>0 && $start_timestamp == $row_starttime && $row_endtime == $end_timestamp) {

                        $errors['section_array[' . $i . ']'] = '' . $i . get_string('notvalid_time','local_timetable') ;
                    }
                    if ($start_timestamp < $row_starttime && $row_starttime < $end_timestamp) {

                        $errors['section_array[' . $i . ']'] = '' . $i . get_string('intervaltime_error','local_timetable');
                    }
                    if ($start_timestamp < $row_endtime && $row_endtime < $end_timestamp) {
                        $errors['section_array[' . $i . ']'] = '' . $i . get_string('intervaltime_error','local_timetable');
                    }
                }
            }
        }
        //exit;
        // Add field validation check for duplicate shortname.
        //if ($course = $DB->get_record('course', array('shortname' => $data['shortname']), '*', IGNORE_MULTIPLE)) {
        //    if (empty($data['id']) || $course->id != $data['id']) {
        //        $errors['shortname'] = get_string('shortnametaken', '', $course->fullname);
        //    }
        //}
        //
        //// Add field validation check for duplicate idnumber.
        //if (!empty($data['idnumber']) && (empty($data['id']) || $this->course->idnumber != $data['idnumber'])) {
        //    if ($course = $DB->get_record('course', array('idnumber' => $data['idnumber']), '*', IGNORE_MULTIPLE)) {
        //        if (empty($data['id']) || $course->id != $data['id']) {
        //            $errors['idnumber'] = get_string('idnumbertaken', 'error');
        //        }
        //    }
        //}
        //
        //$errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));
        //
        //$courseformat = course_get_format((object)array('format' => $data['format']));
        //$formaterrors = $courseformat->edit_form_validation($data, $files, $errors);
        //if (!empty($formaterrors) && is_array($formaterrors)) {
        //    $errors = array_merge($errors, $formaterrors);
        //}

        return $errors;
    }

}

?>    