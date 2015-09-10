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
 * Bulk admission upload forms
 *
 * @package    local
 * @subpackage admission
 * @copyright  2013 D.Sreenivas
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once $CFG->libdir . '/formslib.php';

/**
 * Upload a file CSV file with admission information.
 *
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class admin_admission_form1 extends moodleform {

    function definition() {
        $mform = $this->_form;
        $mform->addElement('header', 'settingsheader', get_string('upload'));
        $mform->addElement('filepicker', 'admissionfile', get_string('file'));
        $mform->addRule('admissionfile', null, 'required');
        $choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'local_admission'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }
        //$choices = textlib::get_encodings();
        $choices =  core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'local_admission'), $choices);
        $mform->setDefault('encoding', 'UTF-8');
        $choices = array('10' => 10, '20' => 20, '100' => 100, '1000' => 1000, '100000' => 100000);
        $mform->addElement('select', 'previewrows', get_string('rowpreviewnum', 'local_admission'), $choices);
        $mform->setType('previewrows', PARAM_INT);
        $this->add_action_buttons(false, get_string('uploadadmission', 'local_admission'));
    }

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
