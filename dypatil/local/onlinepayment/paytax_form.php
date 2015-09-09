<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->dirroot . '/local/onlinepayment/lib.php');
require_once($CFG->dirroot . '/local/lib.php');

class paytax_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        global $hierarchy;
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];

        $heading = ($id > 0) ? get_string('edittaxtype', 'local_onlinepayment') : get_string('createtaxtype', 'local_onlinepayment');
        $mform->addElement('header', 'settingsheader', $heading);

        $mform->addElement('text', 'name', get_string('taxname', 'local_onlinepayment'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('missingtaxname', 'local_onlinepayment'), 'required', null, 'client');

        $mform->addElement('text', 'display_name', get_string('displaytaxname', 'local_onlinepayment'));
        $mform->setType('display_name', PARAM_TEXT);
        $mform->addRule('display_name', get_string('missingdisplaytaxname', 'local_onlinepayment'), 'required', null, 'client');

        $mform->addElement('editor', 'description', get_string('typedescription', 'local_onlinepayment'), null, $editoroptions);
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_INT);

        $mform->addElement('hidden', 'timemodified', time());
        $mform->setType('timemodified', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('update') : get_string('create');

        $mform->addElement('submit', 'submitbutton', $submitlable);
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
    }

}

class taxrate_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $editoroptions = $this->_customdata['editoroptions'];
         $hierarchy= new hierarchy();
        $heading = ($id > 0) ? get_string('edittaxrate', 'local_onlinepayment') : get_string('createtaxrate', 'local_onlinepayment');
        $mform->addElement('header', 'settingsheader', $heading);

        $tax = tax::getInstance();
        $taxtype = $hierarchy->get_records_cobaltselect_menu('local_tax_type', '', null, '', 'id,display_name', 'Select Type');
        $mform->addElement('select', 'typeid', get_string('taxtype', 'local_onlinepayment'), $taxtype);
        $mform->addHelpButton('typeid', 'taxtype', 'local_onlinepayment');
        $mform->setType('typeid', PARAM_INT);
        $mform->addRule('typeid', get_string('missingtypeid', 'local_onlinepayment'), 'required', null, 'client');

        $mform->addElement('text', 'name', get_string('taxname', 'local_onlinepayment'));
        $mform->addHelpButton('name', 'taxname', 'local_onlinepayment');
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', get_string('missingtaxname', 'local_onlinepayment'), 'required', null, 'client');

        $country = get_string_manager()->get_list_of_countries();
        $default_country[''] = get_string('selectacountry');
        $default_country['all'] = get_string('all');
        $country = array_merge($default_country, $country);
        $mform->addElement('select', 'country', get_string('country'), $country);
        $mform->addHelpButton('country', 'country', 'local_onlinepayment');
        $mform->setType('country', PARAM_RAW);
        $mform->addRule('country', get_string('missingcountry', 'local_onlinepayment'), 'required', null, 'client');

        $size = 'style="width:50px !important;"';
        $ratearray = array();
        $ratearray[] = & $mform->createElement('text', 'rate', '', $size);
        $ratearray[] = & $mform->createElement('static', 'ratepercent', '', ' %');
        $mform->addGroup($ratearray, 'ratearray', get_string('taxrate', 'local_onlinepayment'), array(' '), false);
        $mform->addHelpButton('ratearray', 'taxrate', 'local_onlinepayment');
        $mform->setType('rate', PARAM_TEXT);
        $mform->addRule('ratearray', get_string('missingtaxrate', 'local_onlinepayment'), 'required', null, 'client');

        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_academiccalendar'), array('optional' => true));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        $mform->addRule('startdate', get_string('missingstartdate_payment', 'local_onlinepayment'), 'required', null, 'client');
        $mform->addRule('enddate', get_string('missingenddate', 'local_academiccalendar'), 'required', null, 'client');


        $mform->addElement('editor', 'description', get_string('typedescription', 'local_onlinepayment'), null, $editoroptions);
        $mform->setType('description', PARAM_TEXT);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'usermodified', $USER->id);
        $mform->setType('usermodified', PARAM_INT);

        $mform->addElement('hidden', 'timemodified', time());
        $mform->setType('timemodified', PARAM_INT);

        $submitlable = ($id > 0) ? get_string('update') : get_string('create');
        $this->add_action_buttons($cancel = true, $submitlable);
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $rate = $data['rate'];
        if ($rate < 0) {
            $errors['ratearray'] = get_string('entervalidrate', 'local_onlinepayment');
        }
        if (!is_numeric($rate)) {
            $errors['ratearray'] = get_string('enternumericvalue', 'local_onlinepayment');
        }
        $id = $data['id'];
        $typeid = $data['typeid'];
        $type = $DB->get_field('local_tax_type', 'display_name', array('id' => $typeid));
        $country = $data['country'];
        $startdate = $data['startdate'];
        $enddate = $data['enddate'];
        if ($enddate < $startdate) {
            $errors['enddate'] = get_string('enddateshouldbegreater', 'local_onlinepayment');
        }
        $records = $DB->get_records_select('local_tax_rate', 'country = ? AND typeid = ? AND id <> ?', array($country, $typeid, $id));
        foreach ($records as $record) {
            if ($record->startdate == $startdate || $record->enddate == $startdate || $record->startdate == $enddate || $record->enddate == $enddate) {
                $errors['enddate'] = get_string('taxrate', 'local_onlinepayment') . " of this type \"$type\" is created between " . date('d/m/Y', $record->startdate) . " AND " . date('d/m/Y', $record->enddate) . " for this " . get_string('country') . ". Please change the dates.";
            } else
            if (($record->startdate < $startdate) && ($record->enddate > $startdate) || ($record->startdate < $enddate) && ($record->enddate > $enddate)) {
                $errors['enddate'] = get_string('taxrate', 'local_onlinepayment') . " of this type \"$type\" is created between " . date('d/m/Y', $record->startdate) . " AND " . date('d/m/Y', $record->enddate) . " for this " . get_string('country') . ". Please change the dates.";
            } else
            if (($record->startdate > $startdate) && ($record->enddate < $enddate) || ($record->startdate < $startdate) && ($record->enddate > $enddate)) {
                $errors['enddate'] = get_string('taxrate', 'local_onlinepayment') . " of this type \"$type\" is created between " . date('d/m/Y', $record->startdate) . " AND " . date('d/m/Y', $record->enddate) . " for this " . get_string('country') . ". Please change the dates.";
            }
        }
        $today = strtotime(date('Y-m-d'));
        if ($startdate < $today && $id < 0) {
            $errors['startdate'] = get_string('missingstartdate_payment', 'local_onlinepayment');
        }
        return $errors;
    }

}

class status_filter_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;

        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('filter'));
        $mform->addElement('text', 'fullname', get_string('student', 'local_onlinepayment'));
        $mform->setType('fullname', PARAM_TEXT);

        $mform->addElement('submit', 'submitbutton', 'Search');
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
    }

}

class accountingperiod_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $hierarchy = new hierarchy();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $mform->addElement('header', 'settingsheader', get_string('accountingperiod', 'local_onlinepayment'));
        $schoollist = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $schoollist = $hierarchy->get_school_items();
        }
        $schoollist = $hierarchy->get_school_parent($schoollist, $selected = array(), $inctop = true, $all = false);
        if ($id < 0) {
            $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $schoollist);
            $mform->setType('schoolid', PARAM_INT);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
        } else {
            $mform->addElement('static', 'school_name', get_string('schoolname', 'local_collegestructure'));
            $mform->addElement('hidden', 'schoolid');
            $mform->setType('schoolid', PARAM_INT);
        }
        $mform->addElement('date_selector', 'datefrom', get_string('startdate', 'local_academiccalendar'), array('optional' => true));
        $mform->addElement('date_selector', 'dateto', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        $mform->addRule('datefrom', get_string('missingstartdate', 'local_academiccalendar'), 'required', null, 'client');
        $mform->addRule('dateto', get_string('missingenddate', 'local_academiccalendar'), 'required', null, 'client');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('submit'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $id = $data['id'];
        $schoolid = $data['schoolid'];
        $datefrom = $data['datefrom'];
        $dateto = $data['dateto'];
        if ($dateto <= $datefrom) {
            $errors['dateto'] = get_string('enddateshouldbegreater', 'local_onlinepayment');
        }
        $records = $DB->get_records_select('local_accounting_period', 'schoolid = ? AND id <> ?', array($schoolid, $id));
        foreach ($records as $record) {
            if ($record->datefrom == $datefrom || $record->dateto == $datefrom || $record->datefrom == $dateto || $record->dateto == $dateto) {
                $errors['dateto'] = get_string('accountingperiod', 'local_onlinepayment') . " is created between " . date('d/m/Y', $record->datefrom) . " AND " . date('d/m/Y', $record->dateto) . " for this School. Please change the dates.";
            } else
            if (($record->datefrom < $datefrom) && ($record->dateto > $datefrom) || ($record->datefrom < $dateto) && ($record->dateto > $dateto)) {
                $errors['dateto'] = get_string('accountingperiod', 'local_onlinepayment') . " is created between " . date('d/m/Y', $record->datefrom) . " AND " . date('d/m/Y', $record->dateto) . " for this School. Please change the dates.";
            } else
            if (($record->datefrom > $datefrom) && ($record->dateto < $dateto) || ($record->datefrom < $datefrom) && ($record->dateto > $dateto)) {
                $errors['dateto'] = get_string('accountingperiod', 'local_onlinepayment') . " is created between " . date('d/m/Y', $record->datefrom) . " AND " . date('d/m/Y', $record->dateto) . " for this School. Please change the dates.";
            }
        }
        $today = strtotime(date('Y-m-d'));
        if ($datefrom < $today && $id < 0) {
            $errors['datefrom'] = get_string('accountingperiod', 'local_onlinepayment') . " should start from today or later.";
        }
        return $errors;
    }

}

class modcostfilter_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $hierarchy = new hierarchy();
        $tax = tax::getInstance();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $schools = $tax->get_thisuser_schools($hierarchy);
        $programs = $hierarchy->get_records_cobaltselect_menu('local_program', 'visible=1', null, '', 'id,fullname', '--Select--');
        $courses = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', 'id IN (select cobaltcourseid from {local_clclasses} where visible=1)', null, '', 'id,fullname', '--Select--');
        $mooccourses = $hierarchy->get_records_cobaltselect_menu('course', 'id>1', null, '', 'id,fullname', '--Select--');

        $mform->addElement('header', 'settingsheader', get_string('filters', 'local_onlinepayment'));
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $schools);
        $mform->addElement('select', 'programid', get_string('course', 'local_onlinepayment'), $programs);
        $mform->addElement('select', 'cobaltcourseid', get_string('module', 'local_onlinepayment'), $courses);
        $mform->addElement('select', 'moodlecourseid', get_string('mooc', 'local_onlinepayment'), $mooccourses);
        $mform->addElement('submit', 'submitbutton', 'Search');
    }

}

class addcost_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $hierarchy = new hierarchy();
        $tax = tax::getInstance();
        $mform = $this->_form;
        $classid = isset($this->_customdata['classid']) ? $this->_customdata['classid'] : 0;
        $onlinecourseid = isset($this->_customdata['onlinecourseid']) ? $this->_customdata['onlinecourseid'] : 0;
        $moocid = isset($this->_customdata['moocid']) ? $this->_customdata['moocid'] : 0;

        if ($classid) {
            $mform->addElement('static', 'module_name', get_string('module', 'local_onlinepayment'));
            $mform->addElement('static', 'class_name', get_string('class', 'local_onlinepayment'));
            $mform->addElement('static', 'credithours', get_string('credithours', 'local_onlinepayment'));
        }
        if ($moocid || $onlinecourseid) {
            $mform->addElement('static', 'mooc_name', get_string('mooc', 'local_onlinepayment'));
        }

        $disable = '';
        if ($classid) {
            if ($DB->record_exists('local_item', array('moduleid' => $classid))) {
                $mform->addElement('static', 'disable_message', '', get_string('paymentsdonedontchange', 'local_onlinepayment'));
                $disable = ' disabled="disabled"';
            }
        } else if ($moocid) {
            if ($DB->record_exists('local_item', array('online_courseid' => $moocid))) {
                $mform->addElement('static', 'disable_message', '', get_string('paymentsdonedontchange', 'local_onlinepayment'));
                $disable = ' disabled="disabled"';
            }
        }
        $size = 'style="width:100px !important;"';
        $costpersize = 'style="width:100px !important; margin-top: -10px; min-height: 30px;"';
        //$currency_code = array('GBP'=>'&pound;', '2'=>'$');
        $costper = array();
        if ($classid) {
            $costper['1'] = 'Per Class';
            $costper['2'] = 'Per Module Credit Hour';
        } else {
            $costper['1'] = 'Per Module';
        }
        $cost = array();
        $cost[] = & $mform->createElement('text', 'cost', '', $size . $disable);
        $cost[] = & $mform->createElement('select', 'costper', '', $costper, $costpersize . $disable);
        $mform->addGroup($cost, 'costarray', get_string('priceinpounds', 'local_onlinepayment'), array(' '), false);
        $mform->setType('cost', PARAM_RAW);
        $mform->setType('costper', PARAM_INT);



        $discountcode = $tax->randomAlphaNum(6);
        $disc = array();
        $disc[] = & $mform->createElement('text', 'discount', '', $size);
        $disc[] = & $mform->createElement('static', 'disc_percent', '', ' %');
        $disc[] = & $mform->createElement('static', 'disc_percent1', '', ';&nbsp;&nbsp;&nbsp;');
        $disc[] = & $mform->createElement('static', 'disc_codelabel', '', get_string('code', 'local_onlinepayment') . ' -');
        $disc[] = & $mform->createElement('text', 'discountcode', '', $size);
        $disc[] = & $mform->createElement('hidden', 'descid');

        $mform->setType('discount', PARAM_RAW);
        $mform->setType('discountcode', PARAM_RAW);
        $mform->setType('descid', PARAM_INT);
        $count = 0;
        if ($classid || $moocid) {
            if ($classid) {
                if ($costRecord = $DB->get_record('local_classcost', array('classid' => $classid))) {
                    if ($DB->record_exists('local_costdiscounts', array('costid' => $costRecord->id))) {
                        $count = $DB->count_records('local_costdiscounts', array('costid' => $costRecord->id));
                    }
                }
            } else {
                if ($costRecord = $DB->get_record('local_classcost', array('courseid' => $moocid))) {
                    if ($DB->record_exists('local_costdiscounts', array('costid' => $costRecord->id))) {
                        $count = $DB->count_records('local_costdiscounts', array('costid' => $costRecord->id));
                    }
                }
            }
        }
        $default = max($count, 1);
        //$mform->registerNoSubmitButton('removebutton');
        $repeatarray = array();
        $repeatarray[] = $mform->createElement('header', 'settingsheader', get_string('discount', 'local_onlinepayment'));
        $repeatarray[] = $mform->createElement('group', 'disc_array', get_string('discount', 'local_onlinepayment'), $disc, array(' '), false);
        $repeatarray[] = $mform->createElement('date_selector', 'startdate', get_string('startdate', 'local_academiccalendar'), array('optional' => true));
        $repeatarray[] = $mform->createElement('date_selector', 'enddate', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        //$repeatarray[] = $mform->createElement('submit', 'removebutton', get_string('remove'));

        $repeatcount = $this->repeat_elements($repeatarray, $default, null, 'option_repeats', 'option_add_fields', 1, get_string('addonemorediscountcode', 'local_onlinepayment'), false);

        $mform->addElement('hidden', 'classid', $classid);
        $mform->setType('classid', PARAM_INT);
        $mform->addElement('hidden', 'moocid', $moocid);
        $mform->setType('moocid', PARAM_INT);
        $mform->addElement('hidden', 'time', time());
        $mform->setType('time', PARAM_INT);
        $mform->addElement('hidden', 'user', $USER->id);
        $mform->setType('user', PARAM_INT);
        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $CFG, $DB;
        $mform = & $this->_form;
    }

    public function validation($data, $files) {
        $errors = array();
        global $DB, $CFG;
        $errors = parent::validation($data, $files);
        $data = (object) $data;

        if (!is_numeric($data->cost) || $data->cost < 0) {
            $errors['costarray'] = get_string('entervalidcost', 'local_onlinepayment');
        }

        foreach ($data->discount as $k => $v) {
            if (!is_numeric($v) || $v < 0 || $v > 100) {
                $errors['disc_array[' . $k . ']'] = get_string('entervaliddiscount', 'local_onlinepayment');
            }
        }

        foreach ($data->discountcode as $key => $value) {
            if (empty($value)) {
                $errors['disc_array[' . $key . ']'] = get_string('enterdiscountcode', 'local_onlinepayment');
            } else {
                unset($data->discountcode[$key]);
                if (in_array($value, $data->discountcode)) {
                    $errors['disc_array[' . $key . ']'] = get_string('enteruniquediscountcode', 'local_onlinepayment');
                }
            }
            $start = 'startdate[' . $key . ']';
            $end = 'enddate[' . $key . ']';
            if ($data->$start > $data->$end) {
                $errors[$end] = get_string('endgreaterthanstart', 'local_onlinepayment');
            }
            if ($data->$start == 0) {
                $errors[$start] = get_string('entervaliddates', 'local_onlinepayment');
            }
            if ($data->$end == 0) {
                $errors[$end] = get_string('entervaliddates', 'local_onlinepayment');
            }
            $today = strtotime(date('Y-m-d'));
            if ($data->$start < $today) {
                $errors[$start] = get_string('startdategreatertoday', 'local_onlinepayment');
            }

            if ($data->$start < $today && !$data->descid[$key]) {
                $errors[$start] = get_string('startdategreatertoday', 'local_onlinepayment');
            }
        }
        return $errors;
    }

}

class reportfilter_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $hierarchy = new hierarchy();
        $tax = tax::getInstance();
        $mform = $this->_form;
        $id = $this->_customdata['id'];
        $schools = $tax->get_thisuser_schools($hierarchy);
        $programs = $hierarchy->get_records_cobaltselect_menu('local_program', 'visible=1', null, '', 'id,fullname', '--Select--');
        $courses = $hierarchy->get_records_cobaltselect_menu('local_cobaltcourses', 'id IN (select cobaltcourseid from {local_clclasses} where visible=1)', null, '', 'id,fullname', '--Select--');
        $mooccourses = $hierarchy->get_records_cobaltselect_menu('course', 'id>1', null, '', 'id,fullname', '--Select--');
        $mform->addElement('header', 'settingsheader', get_string('filters', 'local_onlinepayment'));
        $mform->addElement('select', 'schoolid', get_string('schoolid', 'local_collegestructure'), $schools);
        $mform->addElement('select', 'programid', get_string('course', 'local_onlinepayment'), $programs);
        $mform->addElement('select', 'cobaltcourseid', get_string('module', 'local_onlinepayment'), $courses);
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_academiccalendar'), array('optional' => true));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        //$mform->addElement('select', 'moodlecourseid', get_string('mooc', 'local_onlinepayment'), $mooccourses);
        $mform->addElement('submit', 'submitbutton', 'Search');
    }

}

class vatreportfilter_form extends moodleform {

    function definition() {
        global $USER, $CFG, $DB, $PAGE;
        $hierarchy = new hierarchy();
        $tax = tax::getInstance();
        $mform = $this->_form;

        $months = $tax->get_months();
        $years = $tax->get_years();

        $mform->addElement('header', 'settingsheader', get_string('filters', 'local_onlinepayment'));
        $montharray = array();
        $montharray[] = & $mform->createElement('select', 'month', '', $months);
        $montharray[] = & $mform->createElement('select', 'year', '', $years);
        $montharray[] = & $mform->createElement('checkbox', 'checkforenable', '', 'Enable');
        $mform->addGroup($montharray, 'montharray', get_string('selectmonth', 'local_onlinepayment'), array(' '), false);
        $mform->disabledIf('month', 'checkforenable');
        $mform->disabledIf('year', 'checkforenable');

        $mform->addElement('static', 'or', '', '(OR)');
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_academiccalendar'), array('optional' => true));
        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_academiccalendar'), array('optional' => true));
        $mform->addElement('hidden', 'page');
        $mform->setType('page', PARAM_INT);
        $mform->addElement('hidden', 'perpage');
        $mform->setType('perpage', PARAM_RAW);
        $mform->addElement('submit', 'submitbutton', 'Search');
    }

}
