<?php
require_once($CFG->libdir.'/formslib.php');
//class to create session form
class local_attendance_add_form extends moodleform {
public function definition() {

        global $CFG, $USER;
        
        $mform    =& $this->_form;
        $attendanceid=$this->_customdata['attendanceid'];
        $classid=$this->_customdata['classid'];
        $fromcalendarview = $this->_customdata['fromcalendar'];
        $mform->addElement('header', 'general', get_string('addsession', 'local_attendance'));
        
       // if($fromcalendarview){
          $mform->addElement('checkbox', 'addmultiply', '', get_string('createmultiplesessions', 'local_attendance'),array('readonly'=>'readonly'));
          $mform->addHelpButton('addmultiply', 'createmultiplesessions', 'local_attendance');
     //   }
         
         
        $mform->addElement('date_time_selector', 'sessiondate', get_string('sessiondate', 'local_attendance'));

        for ($i=0; $i<=23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i=0; $i<60; $i+=5) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        
        $durtime = array();
        $durtime[] =& $mform->createElement('select', 'hours', get_string('hour', 'form'), $hours, false, true);
        $durtime[] =& $mform->createElement('select', 'minutes', get_string('minute', 'form'), $minutes, false, true);
        $mform->addGroup($durtime, 'durtime', get_string('duration', 'local_attendance'), array(' '), true);
        $mform->addElement('date_selector', 'sessionenddate', get_string('sessionenddate', 'local_attendance'));
        $mform->disabledIf('sessionenddate', 'addmultiply', 'notchecked');

        $sdays = array();
        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
        }
        $sdays[] =& $mform->createElement('checkbox', 'Mon', '', get_string('monday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 'Tue', '', get_string('tuesday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 'Wed', '', get_string('wednesday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 'Thu', '', get_string('thursday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 'Fri', '', get_string('friday', 'calendar'));
        $sdays[] =& $mform->createElement('checkbox', 'Sat', '', get_string('saturday', 'calendar'));
        if ($CFG->calendar_startwday !== '0') { // Week start from sunday.
            $sdays[] =& $mform->createElement('checkbox', 'Sun', '', get_string('sunday', 'calendar'));
        }
        $mform->addGroup($sdays, 'sdays', get_string('sessiondays', 'local_attendance'), array('&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'), true);
        $mform->disabledIf('sdays', 'addmultiply', 'notchecked');

        $period = array(1=>1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36);
        $periodgroup = array();
        $periodgroup[] =& $mform->createElement('select', 'period', '', $period, false, true);
        $periodgroup[] =& $mform->createElement('static', 'perioddesc', '', get_string('week', 'local_attendance'));
        $mform->addGroup($periodgroup, 'periodgroup', get_string('period', 'local_attendance'), array(' '), false);
        $mform->disabledIf('periodgroup', 'addmultiply', 'notchecked');
//
//        $mform->addElement('editor', 'sdescription', get_string('description', 'local_attendance'),
//                           null, array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$modcontext));
        $mform->setType('sdescription', PARAM_RAW);
        $mform->addElement('hidden','sessiontype','common');
        $mform->setType('sessiontype', PARAM_RAW);
        $mform->addElement('hidden','attendanceid',$attendanceid);
        $mform->setType('attendanceid', PARAM_RAW);
        $mform->addElement('hidden','classid',$classid);
        $mform->setType('classid', PARAM_RAW);
        $submit_string = get_string('addsession', 'local_attendance');
        $this->add_action_buttons(false, $submit_string);
    }

    

}
//class to edit session form
class local_attendance_edit_form extends moodleform {

    /**
     * Called to define this moodle form
     *
     * @return void
     */
    public function definition() {

        global $DB;
        $mform    =& $this->_form;

        $classid     = $this->_customdata['classid'];
        $id     = $this->_customdata['id'];

        if (!$sess = $DB->get_record('local_attendance_sessions', array('id'=> $id) )) {
           error('No such session in this subject');
        }
        $dhours = floor($sess->duration / HOURSECS);
        $dmins = floor(($sess->duration - $dhours * HOURSECS) / MINSECS);
        $defopts = array('maxfiles'=>EDITOR_UNLIMITED_FILES, 'noclean'=>true, 'context'=>$modcontext);
        $sess = file_prepare_standard_editor($sess, 'description', $defopts, $modcontext, 'mod_attendance', 'session', $sess->id);
        $data = array('sessiondate' => $sess->sessdate,
                'durtime' => array('hours' => $dhours, 'minutes' => $dmins),
                'sdescription' => $sess->description_editor);

        $mform->addElement('header', 'general', get_string('changesession', 'local_attendance'));

        $mform->addElement('static', 'olddate', get_string('olddate', 'local_attendance'),
                           userdate($sess->sessdate, get_string('strftimedmyhm', 'local_attendance')));
        $mform->addElement('date_time_selector', 'sessiondate', get_string('newdate', 'local_attendance'));

        for ($i=0; $i<=23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i=0; $i<60; $i+=5) {
            $minutes[$i] = sprintf("%02d", $i);
        }
        $durselect[] =& $mform->createElement('select', 'hours', '', $hours);
        $durselect[] =& $mform->createElement('select', 'minutes', '', $minutes, false, true);
        $mform->addGroup($durselect, 'durtime', get_string('duration', 'local_attendance'), array(' '), true);

        $mform->addElement('editor', 'description', get_string('description', 'local_attendance'), null, $defopts);
        $mform->setType('description', PARAM_RAW);

        $mform->setDefaults($data);
        
        $mform->addElement('hidden','classid',$classid);
        $mform->setType('classid', PARAM_RAW);
        
        $mform->addElement('hidden','id',$id);
        $mform->setType('id', PARAM_RAW);
 
        $submit_string = get_string('update', 'local_attendance');
        $this->add_action_buttons(true, $submit_string);
    }
}


