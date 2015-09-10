<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class createdept_form extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        //------used for editing purpose(used to put static schoolname and shortname)
        $eid = $this->_customdata['temp'];
        //-------used for adding department from college structure plugin or other plugin
        $schoolid_collegestruct = $this->_customdata['scid'];
        /* Bug-id #262 
         * @author hemalatha c arun <hemalatha@eabyas.in>  
         * resolved -string issue
         */

        if ($eid->id > 0) {
            $mform->addElement('header', 'editheader', get_string('edit_department', 'local_departments'));
            $mform->addHelpButton('editheader', 'deptnote', 'local_departments');
        } else {
            // get_string('create_note','local_departments');
            $mform->addElement('header', 'createheader', get_string('create_department', 'local_departments'));
            $mform->addHelpButton('createheader', 'deptnote', 'local_departments');
        }
        $attributes = 'style="height:30px; "';
        //------used for editing purpose(used to put static schoolname )
        if ($eid->id > 0) {
            $school = $DB->get_record('local_school', array('id' => $eid->schoolid));
            $mform->addElement('static', 'esid', get_string('schoolid', 'local_collegestructure'), $school->fullname);
        } else {
            $hier = new hierarchy();
            if (is_siteadmin($USER->id)) {
                $schoolids = $DB->get_records('local_school', array('visible' => 1));
            } else
                $schoolids = $hier->get_assignedschools();

            $count = sizeof($schoolids);
            if ($count > 1) {
                //  $items = $hier->get_school_items();
                $parents = $hier->get_school_parent($schoolids, '', true);
                //$attributes1='style="height:30px; width:29%; "';
                $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $parents);
                $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
                if ($schoolid_collegestruct > 0)
                    $mform->setDefault('schoolid', $schoolid_collegestruct);
            }
            else {          
                foreach($schoolids as $sid){
                  $schoolid=$sid->id;
                  $schoolname=$sid->fullname;
                }          
                $mform->addElement('static', 'sid', get_string('schoolid', 'local_collegestructure'), $schoolname);
                $mform->addElement('hidden', 'schoolid', $schoolid);
            }
        }
        $mform->setType('schoolid', PARAM_INT);

        $mform->addElement('text', 'fullname', get_string('deptfullname', 'local_departments'), $attributes);
        $mform->addRule('fullname', get_string('missing_deptfullname', 'local_departments'), 'required', null, 'client');
        $mform->setType('fullname', PARAM_RAW);
        $mform->addHelpButton('fullname', 'fullname_note', 'local_departments');

        //-----used to put static shortname while editing---------
        if ($eid->id > 0) {
            $mform->addElement('static', 'eshortname', get_string('deptid', 'local_departments'), $eid->shortname);
        } else {
            $mform->addElement('text', 'shortname', get_string('deptid', 'local_departments'), $attributes);
            $mform->addRule('shortname', get_string('missing_deptid', 'local_departments'), 'required', null, 'client');
            $mform->setType('shortname', PARAM_TEXT);
            $mform->addHelpButton('shortname', 'shortname_note', 'local_departments');
        }

        //$mform->addElement('textarea', 'description', get_string("description", "local_departments"), 'wrap="virtual" rows="6" cols="150"',$attributes);
        //$mform->setType('description', PARAM_TEXT);

        $mform->addElement('editor', 'description', get_string("description", "local_departments"));
        $mform->setType('description', PARAM_RAW);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $submitbuttonname = ($eid->id > 0) ? 'update_department' : 'create_department';
        $this->add_action_buttons(true, get_string($submitbuttonname, 'local_departments'));
    }

    /* Bug -id #261
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * Resolved- providing proper validation, When the user entered exists shortname  */

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $id = $data['id'];
        //  $shortname=mysql_real_escape_string($data['shortname']);  
        $sh = ( $data['shortname']);
        if ($id > 0 && $sh) {
            $compare_scale_clause = $DB->sql_compare_text('shortname') . ' = ' . $DB->sql_compare_text(':short');
            $shortname_exist = $DB->get_records_sql("select * from {local_department} where id!= ? and $compare_scale_clause", array($id, 'short' => $sh));
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        else {
            $compare_scale_clause = $DB->sql_compare_text('shortname') . ' = ' . $DB->sql_compare_text(':short');
            $shortname_exist = $DB->get_records_sql("select * from {local_department} where $compare_scale_clause", array('short' => $sh));
            //if($DB->record_exists('local_department', array('shortname'=>$data['shortname'])))
            if ($shortname_exist)
                $errors['shortname'] = get_string('shortnameexists', 'local_curriculum');
        }
        return $errors;
    }

// end of function
}

// end of class

	


