<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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

require_once($CFG->dirroot.'/local/evaluations/item/evaluation_item_form_class.php');

class evaluation_label_form extends evaluation_item_form {
    protected $type = "label";
    private $area;

    public function definition() {
        global $CFG;

        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $presentationoptions = $this->_customdata['presentationoptions'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];
      //  $classid = $this->_customdata['clid'];
      //  $classid = $this->_customdata['cmid'];

        $mform =& $this->_form;

        $mform->addElement('hidden', 'required', 0);
        $mform->setType('required', PARAM_INT);
        $mform->addElement('hidden', 'clid', 0);
        $mform->setType('clid', PARAM_INT);
                $mform->addElement('hidden', 'cmid', 0);
        $mform->setType('cmid', PARAM_INT);   
        $mform->addElement('hidden', 'name', 'label');
        $mform->setType('template', PARAM_ALPHA);
        $mform->addElement('hidden', 'label', '-');
        $mform->setType('label', PARAM_ALPHA);

        $mform->addElement('header', 'general', get_string($this->type, 'local_evaluations'));
        $mform->addElement('editor', 'presentation_editor', get_string('label_name', 'local_evaluations'), null, $presentationoptions);
     $mform->addRule('presentation_editor', get_string('missingmcname','local_evaluations'), 'required', null, 'client');
        $mform->setType('presentation_editor', PARAM_RAW);

        parent::definition();
        $this->set_data($item);

    }

}
