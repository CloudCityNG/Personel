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

require_once($CFG->libdir.'/formslib.php');

define('EVALUATION_ITEM_NAME_TEXTBOX_SIZE', 80);
define('EVALUATION_ITEM_LABEL_TEXTBOX_SIZE', 20);
abstract class evaluation_item_form extends moodleform {
    public function definition() {
        $item = $this->_customdata['item']; //the item object

        //common is an array like:
        //    array('cmid'=>$cm->id,
        //         'id'=>isset($item->id) ? $item->id : NULL,
        //         'typ'=>$item->typ,
        //         'items'=>$evaluationitems,
        //         'local_evaluations'=>$evaluation->id);
        $common = $this->_customdata['common'];

        //positionlist is an array with possible positions for the item location
        $positionlist = $this->_customdata['positionlist'];

        //the current position of the item
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        if ($common['items']) {
            $mform->addElement('select',
                                'dependitem',
                                get_string('dependitem', 'local_evaluations').'&nbsp;',
                                $common['items']
                                );
            $mform->addHelpButton('dependitem', 'depending', 'local_evaluations');
            $mform->addElement('text',
                                'dependvalue',
                                get_string('dependvalue', 'local_evaluations'),
                                array('size'=>EVALUATION_ITEM_LABEL_TEXTBOX_SIZE, 'maxlength'=>255));
        } else {
            $mform->addElement('hidden', 'dependitem', 0);
            $mform->addElement('hidden', 'dependvalue', '');
        }

        $mform->setType('dependitem', PARAM_INT);
        $mform->setType('dependvalue', PARAM_RAW);

        $position_select = $mform->addElement('select',
                                            'position',
                                            get_string('position', 'local_evaluations').'&nbsp;',
                                            $positionlist);
        $position_select->setValue($position);

        $mform->addElement('hidden', 'cmid', $common['cmid']);
        $mform->setType('cmid', PARAM_INT);
        $mform->addElement('hidden', 'clid', $common['clid']);
        $mform->setType('clid', PARAM_INT);
        $mform->addElement('hidden', 'id', $common['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'evaluation', $common['evaluation']);
        $mform->setType('evaluation', PARAM_INT);

        $mform->addElement('hidden', 'template', 0);
        $mform->setType('template', PARAM_INT);

        $mform->setType('name', PARAM_RAW);
        $mform->setType('label', PARAM_ALPHANUM);

        $mform->addElement('hidden', 'typ', $this->type);
        $mform->setType('typ', PARAM_ALPHA);

        $mform->addElement('hidden', 'hasvalue', 0);
        $mform->setType('hasvalue', PARAM_INT);

        $mform->addElement('hidden', 'options', '');
        $mform->setType('options', PARAM_ALPHA);

        $buttonarray = array();
        if (!empty($item->id)) {
            $buttonarray[] = &$mform->createElement('submit',
                                                    'update_item',
                                                    get_string('update_item', 'local_evaluations'));

            $buttonarray[] = &$mform->createElement('submit',
                                                    'clone_item',
                                                    get_string('save_as_new_item', 'local_evaluations'));
        } else {
            $mform->addElement('hidden', 'clone_item', 0);
            $mform->setType('clone_item', PARAM_INT);
            $buttonarray[] = &$mform->createElement('submit',
                                                    'save_item',
                                                    get_string('save_item', 'local_evaluations'));
        }
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '&nbsp;', array(' '), false);

    }
}

