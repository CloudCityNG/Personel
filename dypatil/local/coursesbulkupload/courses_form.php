<?php

defined('MOODLE_INTERNAL') || die();
Global $CFG;
require_once $CFG->libdir.'/formslib.php';
require_once($CFG->dirroot . '/user/editlib.php');
//require_once($CFG->dirroot . '/admin/tool/uploadcourse/classes/base_form.php');

class uploadcourses_form5 extends moodleform {
    function definition () {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('uploadcourses','local_coursesbulkupload'));
        
        $mform->addElement('filepicker','coursefile', get_string('file','local_coursesbulkupload'));
        $mform->addRule('coursefile', null , 'required');
        
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'local_coursesbulkupload'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
        
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'local_coursesbulkupload'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $choices = array('10'=>10, '20'=>20, '100'=>100, '1000'=>1000, '100000'=>100000);
        $mform->addElement('select', 'previewrows', get_string('priviewrows', 'local_coursesbulkupload'), $choices);
        $mform->setType('previewrows', PARAM_INT);
        
         $this->add_import_options();

        //$mform->addElement('hidden', 'showpreview', 1);
        //$mform->setType('showpreview', PARAM_INT);
        
         $this->add_action_buttons(false, get_string('uploadcourses', 'local_coursesbulkupload'));
    }
    
      public function add_import_options() {
        $mform = $this->_form;

        // Upload settings and file.
        $mform->addElement('header', 'importoptionshdr', get_string('importoptions', 'tool_uploadcourse'));
        $mform->setExpanded('importoptionshdr', true);

        $choices = array(
            tool_uploadcourse_processor::MODE_CREATE_NEW => get_string('createnew', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_CREATE_ALL => get_string('createall', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_CREATE_OR_UPDATE => get_string('createorupdate', 'tool_uploadcourse'),
            tool_uploadcourse_processor::MODE_UPDATE_ONLY => get_string('updateonly', 'tool_uploadcourse')
        );
        $mform->addElement('select', 'options[mode]', get_string('mode', 'tool_uploadcourse'), $choices);
        $mform->addHelpButton('options[mode]', 'mode', 'tool_uploadcourse');

        $choices = array(
            tool_uploadcourse_processor::UPDATE_NOTHING => get_string('nochanges', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_ONLY => get_string('updatewithdataonly', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_ALL_WITH_DATA_OR_DEFAUTLS =>
                get_string('updatewithdataordefaults', 'tool_uploadcourse'),
            tool_uploadcourse_processor::UPDATE_MISSING_WITH_DATA_OR_DEFAUTLS => get_string('updatemissing', 'tool_uploadcourse')
        );
        $mform->addElement('select', 'options[updatemode]', get_string('updatemode', 'tool_uploadcourse'), $choices);
        $mform->setDefault('options[updatemode]', tool_uploadcourse_processor::UPDATE_NOTHING);
        $mform->disabledIf('options[updatemode]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[updatemode]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[updatemode]', 'updatemode', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowdeletes]', get_string('allowdeletes', 'tool_uploadcourse'));
        $mform->setDefault('options[allowdeletes]', 0);
        $mform->disabledIf('options[allowdeletes]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowdeletes]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowdeletes]', 'allowdeletes', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowrenames]', get_string('allowrenames', 'tool_uploadcourse'));
        $mform->setDefault('options[allowrenames]', 0);
        $mform->disabledIf('options[allowrenames]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowrenames]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowrenames]', 'allowrenames', 'tool_uploadcourse');

        $mform->addElement('selectyesno', 'options[allowresets]', get_string('allowresets', 'tool_uploadcourse'));
        $mform->setDefault('options[allowresets]', 0);
        $mform->disabledIf('options[allowresets]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_NEW);
        $mform->disabledIf('options[allowresets]', 'options[mode]', 'eq', tool_uploadcourse_processor::MODE_CREATE_ALL);
        $mform->addHelpButton('options[allowresets]', 'allowresets', 'tool_uploadcourse');
    }
}


/**
 * Specify courses upload details
 *
 * @copyright  Anilkumar.Cheguri  { cheguri.anilkumar@gmail.com }
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_courses_form2 extends moodleform {

    function definition() {
        global $CFG;

        $mform = $this->_form;
        $columns = $this->_customdata['columns'];
        $data = $this->_customdata['data'];

        // upload settings and file
        $mform->addElement('header', 'settingsheader', get_string('settings'));
        /* Bug-Id 263
         * @author hemalatha c arun<hemalatha@eabyas.in>
         * resolved- added proper language string
         */
        $choices = array(UU_DEPARTMENT_ADDNEW => get_string('dept_uuoptype_addnew', 'local_departments'),
            // UU_DEPARTMENT_ADDINC     => get_string('dept_uuoptype_addinc', 'local_departments'),
            UU_DEPARTMENT_ADD_UPDATE => get_string('dept_uuoptype_addupdate', 'local_departments'),
            UU_DEPARTMENT_UPDATE => get_string('dept_uuoptype_update', 'local_departments'));
        $mform->addElement('select', 'uutype', get_string('dept_uuoptype', 'local_departments'), $choices);

        $choices = array(UU_UPDATE_NOCHANGES => get_string('dept_nochanges', 'local_departments'),
            UU_UPDATE_FILEOVERRIDE => get_string('dept_uuupdatefromfile', 'local_departments'),
            UU_UPDATE_ALLOVERRIDE => get_string('dept_uuupdateall', 'local_departments'),
            UU_UPDATE_MISSING => get_string('dept_uuupdatemissing', 'local_departments'));
        $mform->addElement('select', 'uuupdatetype', get_string('dept_uuupdatetype', 'local_departments'), $choices);
        $mform->setDefault('uuupdatetype', UU_UPDATE_NOCHANGES);
        $mform->disabledIf('uuupdatetype', 'uutype', 'eq', UU_DEPARTMENT_ADDNEW);
        //$mform->disabledIf('uuupdatetype', 'uutype', 'eq', UU_DEPARTMENT_ADDINC);
        // hidden fields
        $mform->addElement('hidden', 'iid');
        $mform->setType('iid', PARAM_INT);

        $mform->addElement('hidden', 'previewrows');
        $mform->setType('previewrows', PARAM_INT);

        $this->add_action_buttons(true, get_string('upload'));
        $this->set_data($data);
    }
}