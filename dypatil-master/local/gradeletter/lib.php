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
 * General plugin functions.
 *
 * @package    local
 * @subpackage gradeletter
 * @copyright  2013 Pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

class graded_letters {

    private static $_graded_letters;

    private function __construct() {
        
    }

    public static function getInstance() {
        if (!self::$_graded_letters) {
            self::$_graded_letters = new graded_letters();
        }
        return self::$_graded_letters;
    }

    /**
     * To add new gradeletter.
     * @param  object $tool - This parameter contains all the details to create a new gradeletter
     * @return int
     */
    function gradeletter_add_instance($tool) {
        global $DB;
        $tool->id = $DB->insert_record('local_gradeletters', $tool);
    }

    /**
     * Update existing gradeletter.
     * @param  object $tool - This parameter contains all the details to edit an existing gradeletter
     * @return void
     */
    function gradeletter_update_instance($tool) {
        global $DB;

        $DB->update_record('local_gradeletters', $tool);
    }

    /**
     * Delete a gradeletter.
     * @param  object $tool - This parameter contains 'id' to delete an existing gradeletter.
     * @return void
     */
    function gradeletter_delete_instance($tool) {
        global $DB;
        
        $DB->delete_records('local_gradeletters', array('id' => $tool));
    }

    /**
     * Function to check for existing Grade boundaries
     * @param  object $data - This parameter contains all the form submitted data while creating a gradeletter.
     * @return $errors - array
     */
    function gradeboundaryexits($data, $mode) {
        global $CFG, $DB, $errors;
        //edit mode
        if ($mode == 1) {

            $editsql = 'select markfrom, markto from {local_gradeletters} where id =' . $data['id'];
            $result = $DB->get_record_sql($editsql);
            if (!($result->markfrom == $data['markfrom'] AND $result->markto == $data['markto'])) {

                $sql = 'select MIN(markfrom) as lower,MAX(markto) as higher from {local_gradeletters} where schoolid =' . $data['schoolid'];
                $boundary = $DB->get_record_sql($sql);

                if ($data['markfrom'] >= $boundary->lower AND $data['markfrom'] <= $boundary->higher) {
                    $errors['markfrom'] = get_string('markexits', 'local_gradeletter');
                }

                if ($data['markfrom'] > 100) {
                    $errors['markfrom'] = get_string('maxmarks', 'local_gradeletter');
                }

                if ($data['markto'] >= $boundary->lower AND $data['markto'] <= $boundary->higher) {
                    $errors['markto'] = get_string('markexits', 'local_gradeletter');
                }

                if ($data['markto'] > 100) {
                    $errors['markto'] = get_string('maxmarks', 'local_gradeletter');
                }
            }
        }

        //new entry 
        if ($mode == 0) {
            $sql = 'select MIN(markfrom) as lower,MAX(markto) as higher from {local_gradeletters} where schoolid =' . $data['schoolid'];
            $boundary = $DB->get_record_sql($sql);
            // print_object($boundary);exit;

            if (!empty($boundary->lower)) {
                if ($data['markfrom'] >= $boundary->lower AND $data['markfrom'] <= $boundary->higher) {
                    $errors['markfrom'] = get_string('markexits', 'local_gradeletter');
                }

                if ($data['markfrom'] > 100) {
                    $errors['markfrom'] = get_string('maxmarks', 'local_gradeletter');
                }

                if ($data['markto'] >= $boundary->lower AND $data['markto'] <= $boundary->higher) {
                    $errors['markto'] = get_string('markexits', 'local_gradeletter');
                } elseif ($data['markto'] != ($boundary->lower) - 1) {
                    $missedmarks = ($boundary->lower) - ($data['markto']);
                    $errors['markto'] = get_string('missingmarks', 'local_gradeletter', $missedmarks - 1);
                }

                if ($data['markto'] > 100) {
                    $errors['markto'] = get_string('maxmarks', 'local_gradeletter');
                }
            }
        }
        return $errors;
    }

    /**
     * Function to check for existing Grade point
     * @param  object $data - This parameter contains all the form submitted data while creating a gradeletter.
     * @return $errors - array
     */
    function gradepointexits($data, $mode) {
        global $CFG, $DB, $errors;
        //edit mode
        if ($mode == 1) {
            $editsql = 'SELECT gradepoint FROM {local_gradeletters} where id =' . $data['id'];
            $result = $DB->get_record_sql($editsql);
            if (!($result->gradepoint == $data['gradepoint'])) {
                $sql = 'SELECT gradepoint FROM {local_gradeletters} where schoolid =' . $data['schoolid'] . ' ORDER BY gradepoint ';
                $gradepoint = $DB->get_records_sql($sql);

                $cnt = array();
                foreach ($gradepoint as $point) {
                    $cnt[] = $point->gradepoint;
                }

                $len = count($cnt);
                if ($len == 1) {
                    for ($j = 0; $j < $len; $j++) {
                        if ($data['gradepoint'] >= $cnt[$j]) {
                            $errors['gradepoint'] = get_string('gradepointexits', 'local_gradeletter');
                        }

                        if ($data['gradepoint'] > end($cnt)) {
                            $errors['gradepoint'] = get_string('gradepointupperlimit', 'local_gradeletter');
                        }
                    }
                } else {
                    for ($j = 0; $j < $len; $j++) {
                        if ($data['gradepoint'] >= $cnt[$j] AND $data['gradepoint'] <= $cnt[$j + 1]) {
                            $errors['gradepoint'] = get_string('gradepointexits', 'local_gradeletter');
                        }

                        if ($data['gradepoint'] > end($cnt)) {
                            $errors['gradepoint'] = get_string('gradepointupperlimit', 'local_gradeletter');
                        }
                    }
                }
            }
        }
        //new entry
        if ($mode == 0) {
            $sql = 'SELECT gradepoint FROM {local_gradeletters} where schoolid =' . $data['schoolid'] . ' ORDER BY gradepoint ';
            $gradepoint = $DB->get_records_sql($sql);
            $cnt = array();
            foreach ($gradepoint as $point) {
                $cnt[] = $point->gradepoint;
            }

            $len = count($cnt);
            if ($len == 1) {
                for ($j = 0; $j < $len; $j++) {

                    if ($data['gradepoint'] >= $cnt[$j]) {
                        $errors['gradepoint'] = get_string('gradepointexits', 'local_gradeletter');
                    }

                    if ($data['gradepoint'] > end($cnt)) {
                        $errors['gradepoint'] = get_string('gradepointupperlimit', 'local_gradeletter');
                    }
                }
            } else {
                for ($j = 0; $j < $len; $j++) {
                    if ($data['gradepoint'] >= $cnt[$j] AND $data['gradepoint'] <= $cnt[$j + 1]) {
                        $errors['gradepoint'] = get_string('gradepointexits', 'local_gradeletter');
                    }

                    if ($data['gradepoint'] > end($cnt)) {
                        $errors['gradepoint'] = get_string('gradepointupperlimit', 'local_gradeletter');
                    }
                }
            }
        }

        return $errors;
    }

    /**
     * @method createtabview
     * @todo provides the tab view
     * @param  currenttab(string)
     * */
    function createtabview_gl($currenttab, $id = -1) {
        global $OUTPUT;
        $tabs = array();
        $systemcontext =context_system::instance();
        $string = ($id > 0) ? get_string('updategradeletter', 'local_gradeletter') : get_string('creategradeletter', 'local_gradeletter');
        if (has_capability('local/gradeletter:manage', $systemcontext))
            $tabs[] = new tabobject('create', new moodle_url('/local/gradeletter/edit.php'), $string);

        $tabs[] = new tabobject('view', new moodle_url('/local/gradeletter/index.php'), get_string('view', 'local_gradeletter'));
        $tabs[] = new tabobject('info', new moodle_url('/local/gradeletter/info.php'), get_string('info', 'local_gradeletter'));

        echo $OUTPUT->tabtree($tabs, $currenttab);
    }

}
