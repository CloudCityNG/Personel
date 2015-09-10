<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk department upload forms
 *
 * @package    local
 * @subpackage uploaddepartments
 * @copyright  2013 D.Sreenivas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Upload a file CSV file with department information.
 *
 * @package    local
 * @subpackage uploaddepartments
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_department_form1 extends moodleform {

    function definition() {
        $mform = $this->_form;

        $mform->addElement('header', 'settingsheader', get_string('upload'));

        $mform->addElement('filepicker', 'departmentfile', get_string('file'));
        $mform->addRule('departmentfile', null, 'required');

        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('dept_csvdelimiter', 'local_departments'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

        $choices =  core_text::get_encodings();
        //$choices = textlib::get_encodings();
        $mform->addElement('select', 'encoding', get_string('dept_encoding', 'local_departments'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

        $choices = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
        $mform->addElement('select', 'previewrows', get_string('dept_rowpreviewnum', 'local_departments'), $choices);
        $mform->setType('previewrows', PARAM_INT);

        $this->add_action_buttons(true, get_string('upload'));
    }

}

/**
 * Specify department upload details
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_department_form2 extends moodleform {

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

    /**
     * Form tweaks that depend on current data.
     */
    function definition_after_data() {
        $mform = $this->_form;
        $columns = $this->_customdata['columns'];

        foreach ($columns as $column) {
            if ($mform->elementExists($column)) {
                $mform->removeElement($column);
            }
        }
    }

    /**
     * Used to reformat the data from the editor component
     *
     * @return stdClass
     */
    function get_data() {
        $data = parent::get_data();
        if ($data !== null and isset($data->description)) {
            $data->descriptionformat = $data->description['format'];
            $data->description = $data->description['text'];
        }
        return $data;
    }

}
