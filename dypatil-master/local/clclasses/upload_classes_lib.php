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
 * Bulk class registration functions
 *
 * @package    local
 * @subpackage uploadclasss
 * @copyright  2013 onwards sreenivasulareddy <sreenivasula@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

define('UU_CLASS_ADDNEW', 0);
define('UU_CLASS_ADD_UPDATE', 1);
define('UU_CLASS_UPDATE', 2);

define('UU_UPDATE_NOCHANGES', 0);
define('UU_UPDATE_FILEOVERRIDE', 1);
define('UU_UPDATE_ALLOVERRIDE', 2);
define('UU_UPDATE_MISSING', 3);

define('UU_BULK_NONE', 0);
define('UU_BULK_NEW', 1);
define('UU_BULK_UPDATED', 2);
define('UU_BULK_ALL', 3);

define('UU_PWRESET_NONE', 0);
define('UU_PWRESET_WEAK', 1);
define('UU_PWRESET_ALL', 2);

/**
 * Tracking of processed classs.
 *
 * This class prints class information into a html table.
 *
 * @package    core
 * @subpackage admin
 * @copyright  2007 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class uu_progress_tracker {

    private $_row;
    public $columns = array('status', 'line', 'id', 'fullname', 'shortname', 'schoolname', 'description', 'visible');

    /**
     * Flush previous line and start a new one.
     * @return void
     */
    public function flush() {
        if (empty($this->_row) or empty($this->_row['line']['normal'])) {
            // Nothing to print - each line has to have at least number
            $this->_row = array();
            foreach ($this->columns as $col) {
                $this->_row[$col] = array('normal' => '', 'info' => '', 'warning' => '', 'error' => '');
            }
            return;
        }
        $ci = 0;
        $ri = 1;
        echo '<tr class="r' . $ri . '">';
        foreach ($this->_row as $key => $field) {
            foreach ($field as $type => $content) {
                if ($field[$type] !== '') {
                    $field[$type] = '<span class="uu' . $type . '">' . $field[$type] . '</span>';
                } else {
                    unset($field[$type]);
                }
            }
            echo '<td class="cell c' . $ci++ . '">';
            if (!empty($field)) {
                echo implode('<br />', $field);
            } else {
                echo '&nbsp;';
            }
            echo '</td>';
        }
        echo '</tr>';
        foreach ($this->columns as $col) {
            $this->_row[$col] = array('normal' => '', 'info' => '', 'warning' => '', 'error' => '');
        }
    }

    /**
     * Add tracking info
     * @param string $col name of column
     * @param string $msg message
     * @param string $level 'normal', 'warning' or 'error'
     * @param bool $merge true means add as new line, false means override all previous text of the same type
     * @return void
     */
    public function track($col, $msg, $level = 'normal', $merge = true) {

        if (empty($this->_row)) {
            $this->flush(); //init arrays
        }
        if (!in_array($col, $this->columns)) {
            debugging('Incorrect column:' . $col);
            return;
        }
        if ($merge) {
            if ($this->_row[$col][$level] != '') {
                $this->_row[$col][$level] .='<br />';
            }
            $this->_row[$col][$level] .= $msg;
        } else {
            $this->_row[$col][$level] = $msg;
        }
    }

    /**
     * Print the table end
     * @return void
     */
    public function close() {
        
    }

}

/**
 * Validation callback function - verified the column line of csv file.
 * Converts standard column names to lowercase.
 * @param csv_import_reader $cir
 * @param array $stdfields standard class fields
 * @param array $profilefields custom profile fields
 * @param moodle_url $returnurl return url in case of any error
 * @return array list of fields
 */
function uu_validate_class_upload_columns(csv_import_reader $cir, $stdfields, $profilefields, moodle_url $returnurl) {
    $columns = $cir->get_columns();

    if (empty($columns)) {
        $cir->close();
        $cir->cleanup();
        print_error('cannotreadtmpfile', 'error', $returnurl);
    }
    if (count($columns) < 2) {
        $cir->close();
        $cir->cleanup();
        print_error('csvfewcolumns', 'error', $returnurl);
    }

    // test columns
    $processed = array();

    foreach ($columns as $key => $unused) {
        $field = $columns[$key];
        $lcfield = core_text::strtolower($field);
        if (in_array($field, $stdfields) or in_array($lcfield, $stdfields)) {
            // standard fields are only lowercase
            $newfield = $lcfield;
        } else if (in_array($field, $profilefields)) {
            // exact profile field name match - these are case sensitive
            $newfield = $field;
        } else if (in_array($lcfield, $profilefields)) {
            // hack: somebody wrote uppercase in csv file, but the system knows only lowercase profile field
            $newfield = $lcfield;
        } else if (preg_match('/^(cohort|class|group|type|role|enrolperiod)\d+$/', $lcfield)) {
            // special fields for enrolments
            $newfield = $lcfield;
        } else {
            $cir->close();
            $cir->cleanup();
            print_error('invalidfieldname', 'error', $returnurl, $field);
        }
        if (in_array($newfield, $processed)) {
            $cir->close();
            $cir->cleanup();
            print_error('duplicatefieldname', 'error', $returnurl, $newfield);
        }
        $processed[$key] = $newfield;
    }

    return $processed;
}

function roomlist($startdate, $enddate, $starttime, $endtime, $schoolid, $id) {
    global $DB;
    $room = array();
    $allrooms = array();
    $i = 0;
    $j = 0;
    if ($startdate['day'][0] <= 9) {
        $startdate['day'][0] = '0' . $startdate['day'][0];
    }
    if ($enddate['day'][0] <= 9) {
        $enddate['day'][0] = '0' . $enddate['day'][0];
    }
    $startdate = $startdate['day'][0] . '-' . $startdate['month'][0] . '-' . $startdate['year'][0];
    $enddate = $enddate['day'][0] . '-' . $enddate['month'][0] . '-' . $enddate['year'][0];
    if ($starttime['starthour'][0] == null) {
        $starttime['starthour'][0] = 0;
    }
    if ($starttime['startmin'][0] == null) {
        $starttime['startmin'][0] = 0;
    }
    if ($endtime['endhour'][0] == null) {
        $endtime['endhour'][0] = 0;
    }
    if ($endtime['endmin'][0] == null) {
        $endtime['endmin'][0] = 0;
    }
    $st = $starttime['starthour'][0] . ':' . $starttime['startmin'][0];
    $et = $endtime['endhour'][0] . ':' . $endtime['endmin'][0];
    //To get busy classrooms
    $sql = "SELECT ls.id,ls.classroomid,ls.instructorid,
		          FROM_UNIXTIME(ls.startdate,'%d-%c-%Y') as startdate,
		          FROM_UNIXTIME(ls.enddate,'%d-%c-%Y') as enddate
		          FROM {local_scheduleclass} AS ls INNER JOIN {local_classroom} AS c ON ls.classroomid=c.id
			      WHERE c.schoolid={$schoolid} AND
                  starttime BETWEEN '{$st}' AND '{$et}' OR 
			      endtime BETWEEN '{$st}' AND '{$et}' ";
    $classrooms = $DB->get_records_sql($sql);
    foreach ($classrooms as $classroom) {


        if ((($classroom->startdate <= $startdate) && ($classroom->enddate >= $startdate)) ||
                (($classroom->startdate >= $enddate) && ($classroom->enddate <= $enddate))) {

            $room[$i] = $classroom->classroomid;
            $i++;
        }
    }

    //To get all classrooms
    $classroom = "SELECT id FROM {local_classroom} WHERE schoolid={$schoolid}";
    $classrooms = $DB->get_records_sql($classroom);
    foreach ($classrooms as $cms) {
        $allrooms[$j] = $cms->id;
        $j++;
    }

    $result = array_diff($allrooms, $room);
    $list = array();
    if ($id < 0) {
        $list[null] = 'Select Classroom';
    }

    foreach ($result as $id) {
        $name = $DB->get_record('local_classroom', array('id' => $id));
        $building = $DB->get_field('local_building', 'fullname', array('id' => $name->buildingid));
        $floor = $DB->get_field('local_floor', 'fullname', array('id' => $name->floorid));
        $list[$id] = $name->fullname;
    }
    return $list;
}
