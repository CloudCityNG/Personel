<?php

defined('MOODLE_INTERNAL') || die;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');

class categorylevel_settingsform extends moodleform {

    protected $course;
    protected $context;

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-local_cobaltsettings-cobaltsetting', 'M.local_cobaltsettings.init_cobaltsetting', array(array('formid' => $mform->getAttribute('id'))));
        /* ---used for editing purpose--- */
        $eid = $this->_customdata['temp'];

        if ($eid->id <= 0)
            $mform->addElement('header', 'settingsheader', get_string('category_level_settings', 'local_cobaltsettings'));
        else
            $mform->addElement('header', 'settingsheader', get_string('editentity_level_settings', 'local_cobaltsettings'));
        $hier = new hierarchy();
        $global_ob = global_settings::getInstance();

        $global_ob->school_formelement_condition($mform);
        $aca_list = $hier->get_records_cobaltselect_menu('local_cobalt_entity', '', null, '', 'id,name', 'Select Category');

        $mform->addElement('select', 'entityid', get_string('category', 'local_cobaltsettings'), $aca_list);
        $mform->addRule('entityid', get_string('category', 'local_cobaltsettings'), 'required', null, 'client');
        $mform->setType('entityid', PARAM_INT);

        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('hidden', 'categorytypehere');
        $mform->setType('categorytypehere', PARAM_TEXT);
        $mform->addElement('hidden', 'radiobuttonhere');
        $mform->setType('radiobuttonhere', PARAM_INT);

        $radioarray = array();
        $radioarray[] = & $mform->createElement('radio', 'level', 'SCH', get_string('schoollevel', 'local_collegestructure'), 'SL');
        $radioarray[] = & $mform->createElement('radio', 'level', 'PCH', get_string('programlevel', 'local_programs'), 'PL');
        $radioarray[] = & $mform->createElement('radio', 'level', 'CCH', get_string('curriculumlevel', 'local_curriculum'), 'CL');
        $mform->addGroup($radioarray, 'radioarray', '', array(' '), false);
        $mform->addRule('radioarray', get_string('category', 'local_cobaltsettings'), 'required', null, 'client');

        /* if($eid->id <= 0){	
          $mform->disabledIf('radioarray', 'radiobuttondisable', 'eq', 1);
          } */

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $eid->id;
        if ($eid->id > 0)
            $string = get_string('updateentity', 'local_cobaltsettings');
        else
            $string = get_string('createentity', 'local_cobaltsettings');
        $this->add_action_buttons(true, $string);
    }

    /* ---end of definition--- */

    function definition_after_data() {
        global $DB;
        $mform = $this->_form;
        $category = $mform->getElementValue('entityid');
        $schoolid = $mform->getElementValue('schoolid');
        $tools = array();
        if ($category[0] > 0) {
            $entityid = $category[0];
            $global_ob = global_settings::getInstance();
            $cate_types = $global_ob->get_category_types($entityid);

            foreach ($cate_types as $types) {

                $types->name = $mform->createElement('static', $types->name, null, $types->name);
                $mform->insertElementBefore($types->name, 'categorytypehere');
            }
        }
        if ($schoolid[0] > 0) {
            $exists = $DB->get_records('local_cobalt_entitylevels', array('schoolid' => $schoolid[0]));
            if ($exists) {
                $new1 = $mform->createElement('hidden', 'radiobuttondisable', '1');
                $mform->insertElementBefore($new1, 'radiobuttonhere');
            }
        }
    }

    public function validation($data, $files) {
        $errors = array();
        global $COURSE, $DB, $CFG;
        if ($data['entityid'] == 0) {
            $errors['entityid'] = 'Select entity Level';
        }
        $sql = "select * FROM {local_cobalt_entitylevels} WHERE schoolid={$data['schoolid']} AND 
	 entityid={$data['entityid']} AND id!={$data['id']}";
        $query = $DB->get_records_sql($sql);
        if (!empty($query)) {
            $errors['entityid'] = 'Entity Level Already Exist';
        }
        /* Bug-id #257
         * @author hemalatha c arun <hemalatha@eabyas.in>
         * Resolved(added proper validation) */ else {
            $sql = "select * FROM {local_cobalt_entitylevels} WHERE schoolid={$data['schoolid']}";
            $query = $DB->get_records_sql($sql);
            if (!empty($query))
                $errors['entityid'] = get_string('schoolalreadyassigned', 'local_cobaltsettings');
        }

        return $errors;
    }

    /* ---end of definition after data--- */
}

/* ---end of class--- */
?>
