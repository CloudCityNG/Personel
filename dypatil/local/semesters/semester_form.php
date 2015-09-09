<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/local/programs/lib.php');

$hierarchy = new hierarchy();

class createsemester extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
        $heading = ($id > 0) ? get_string('editsemester', 'local_semesters') : get_string('createsemester', 'local_semesters');
        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $school = $hierarchy->get_school_items();
        }
        $parents = $hierarchy->get_school_parent($school, '', $top = true, $all = false);
        $mform->addElement('header', 'settingsheader', $heading);

		$count = count($school);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);

        if ($count == 1) {
            //registrar is assigned to only one school, display as static
            foreach ($school as $scl) {
                $key = $scl->id;
                $value = $scl->fullname;
            }
            $mform->addElement('static', 'schools', get_string('schoolid', 'local_collegestructure'), $value);
            $mform->addElement('hidden', 'schoolid', $key);
            $mform->setType('schoolid', PARAM_INT);
        } else {
            $school = $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
            $mform->addHelpButton('schoolid', 'school', 'local_semesters');
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
            // $school->setMultiple(true);
            // if ($id < 0)
                // $school->setSelected(array('0'));
        }

        $mform->addElement('text', 'fullname', get_string('semestername', 'local_semesters'));
        $mform->addRule('fullname', get_string('missingfullname', 'local_semesters'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);

        $options = array(
            'stopyear' => date('Y') + 5,
            'timezone' => 99,
            'optional' => false
        );
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_semesters'), $options);
        $mform->addHelpButton('startdate', 'startdate', 'local_semesters');
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_semesters'), $options);

        $size = 'style="width:50px !important;"';
        $mform->addElement('text', 'mincredit', get_string('mincredits', 'local_semesters'), $size);
        $mform->addRule('mincredit', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('mincredit', get_string('missingmincredits', 'local_semesters'), 'required', null, 'client');
        $mform->addRule('mincredit', get_string('numericmincredits', 'local_semesters'), 'numeric', null, 'client');
        $mform->setType('mincredit', PARAM_INT);

        $mform->addElement('text', 'maxcredit', get_string('maxcredits', 'local_semesters'), $size);
        $mform->addRule('maxcredit', get_string('numeric', 'local_admission'), 'numeric', null, 'client');
        $mform->addRule('maxcredit', get_string('missingmaxcredits', 'local_semesters'), 'required', null, 'client');
        $mform->addRule('maxcredit', get_string('numericmaxcredits', 'local_semesters'), 'numeric', null, 'client');
        $mform->setType('maxcredit', PARAM_INT);

        $mform->addElement('editor', 'description', get_string('description', 'local_semesters'), null, $editoroptions);
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'mode');
        $mform->setType('mode', PARAM_RAW);

        $submitlable = ($id > 0) ? get_string('update', 'local_semesters') : get_string('createsemester', 'local_semesters');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $startdate = $data['startdate'];
        $enddate = $data['enddate'];
        $id = $data['id'];
        if ($startdate >= $enddate) {
            $errors['enddate'] = get_string('enddategreater', 'local_semesters');
        }
        if (($startdate < time()) && ($id < 0)) {
            $errors['startdate'] = get_string('lessthannow', 'local_semesters');
        }
        $count = $data['count'];
        // if ($count == 1) {
            // $schoolin = $data['schoolid'];
        // } else {
            // $school = array();
            // $flag = false;
            // foreach ($data['schoolid'] as $sid) {
                // if ($sid == 0) {
                    // $s = $DB->get_records('local_school', array('visible' => 1));
                    // $flag = true;
                // }
            // }
            // if ($flag) {
                // foreach ($s as $s1) {
                    // $school[] = $s1->id;
                // }
            // } else {
                // $school = $data['schoolid'];
            // }
            // $schoolin = implode(',', $school);
        // }
        $schoolin = $data['schoolid'];
	
	// while resticting semester date (two semester not be active at a time)
	if(empty($schoolin)  &&  $id>0 ){
	$schoolid =$DB->get_field('local_school_semester','schoolid',array('semesterid'=>$id));
	$schoolin = $schoolid;
	}
	
        $sql = "SELECT s.*, from_unixtime(s.startdate, '%Y-%m-%d') AS datestart, from_unixtime(s.enddate, '%Y-%m-%d') AS dateend
                            FROM {local_semester} s
                            INNER JOIN {local_school_semester} ss
                            ON ss.semesterid = s.id
                            WHERE ss.schoolid IN ($schoolin) AND
                            s.id != {$id} GROUP BY ss.semesterid";

        $semesters = $DB->get_records_sql($sql);

        foreach ($semesters as $semester) {
            if ($semester->startdate == $startdate || $semester->enddate == $startdate || $semester->startdate == $enddate || $semester->enddate == $enddate) {
                $errors['enddate'] = "A semester is created between " . date('d/m/Y', $semester->startdate) . " AND " . date('d/m/Y', $semester->enddate) . ". Please change the dates.";
            } else
            if (($semester->startdate < $startdate) && ($semester->enddate > $startdate) || ($semester->startdate < $enddate) && ($semester->enddate > $enddate)) {
                $errors['enddate'] = "A semester is created between " . date('d/m/Y', $semester->startdate) . " AND " . date('d/m/Y', $semester->enddate) . ". Please change the dates.";
            } else
            if (($semester->startdate > $startdate) && ($semester->enddate < $enddate) || ($semester->startdate < $startdate) && ($semester->enddate > $enddate)) {
                $errors['enddate'] = "A semester is created between " . date('d/m/Y', $semester->startdate) . " AND " . date('d/m/Y', $semester->enddate) . ". Please change the dates.";
            }
        }

        if ($data['maxcredit'] == 0) {
            $errors['maxcredit'] = get_string('maxcreditscannotzero', 'local_semesters');
        }
        if ($data['maxcredit'] < $data['mincredit']) {
            $errors['maxcredit'] = get_string('maxcreditsgreater', 'local_semesters');
        }
        if ($data['maxcredit'] < 0) {
            $errors['maxcredit'] = get_string('numeric', 'local_admission');
        }
        if ($data['mincredit'] < 0) {
            $errors['mincredit'] = get_string('numeric', 'local_admission');
        } if ($data['id'] > 0) {
            /*
             * ###Bug report #246  -  Course Offerings
             * @author Naveen Kumar<naveen@eabyas.in>
             * (Resolved) Added schoolid also to check the condition only for the selected schools
             */
            $sql = "SELECT s.* FROM {local_semester} s,{local_school_semester} ss WHERE s.id=ss.semesterid  AND ss.schoolid IN($schoolin) AND s.startdate <={$startdate} AND s.enddate >= {$enddate} AND s.id!={$data['id']}";
            $query = $DB->get_records_sql($sql);
            if (!empty($query)) {
                $errors['startdate'] = 'Semester already Exist';
            }
        }
        return $errors;
    }

}
