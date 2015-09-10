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
 * @subpackage scheduleexam
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot . '/message/lib.php');
defined('MOODLE_INTERNAL') or die;

class schedule_exam {

   /**
     * @method scheduleexam_add_instance
     * @todo To schedule new exam.
     * @param  object $tool Contains all the details to create a new exam
     * @return int Recently created insatnce ID
     */
    function scheduleexam_add_instance($tool1) {
        global $DB, $USER;
        $cres = $DB->insert_record('local_scheduledexams', $tool1);
        $sql = "SELECT * FROM {local_user_clclasses} WHERE classid={$tool1->classid}";
        $users = $DB->get_records_sql($sql);
        foreach ($users as $user) {
            $conf = new object();
            $conf->username = $DB->get_field('user', 'username', array('id' => $user->userid));
            $conf->classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $tool1->classid));
            $message = get_string('msg_stu_exam', 'local_scheduleexam', $conf);
            $userfrom = $DB->get_record('user', array('id' => $USER->id));
            $userto = $DB->get_record('user', array('id' => $user->userid));
            $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
        }
        return $cres;
    }

    /**
     * @method scheduleexam_update_instance
     * @todo Update existing exam.
     * @param  object $scheduledexam Contains all the details to edit an existing exam
     * @return int Updated instance ID
     */
    function scheduleexam_update_instance($tool) {
        global $DB, $USER;
//$DB->update_record('local_scheduledexams', $tool);
        $ures = $DB->update_record('local_scheduledexams', $tool);
        $sql = "SELECT * FROM {local_user_clclasses} WHERE classid={$tool->classid}";
        $users = $DB->get_records_sql($sql);
        foreach ($users as $user) {
            $conf = new object();
            $conf->username = $DB->get_field('user', 'username', array('id' => $user->userid));
            $conf->classname = $DB->get_field('local_clclasses', 'fullname', array('id' => $tool->classid));
            $message = get_string('msg_stu_exam', 'local_scheduleexam', $conf);
            $userfrom = $DB->get_record('user', array('id' => $USER->id));
            $userto = $DB->get_record('user', array('id' => $user->userid));
            $message_post_message = message_post_message($userfrom, $userto, $message, FORMAT_HTML);
        }

        return $ures;
    }

    /**
     * @method scheduleexam_delete_instance
     * @todo  Delete an exam.
     * @param  object $tool - This parameter contains 'id' to delete an existing exam.
     * @return void
     */
    function scheduleexam_delete_instance($tool) {
        global $DB;

        $dres = $DB->delete_records('local_scheduledexams', array('id' => $tool));
        return $dres;
    }

    /**
     * @method get_semesterslists_scheduleexam
     * @Todo Function to get semesters under a school.
     * @param  object $sid - This parameter contains schoolid.
     * @return array
     */
    function get_semesterslists_scheduleexam($sid) {
        global $CFG, $DB, $USER;
        $today = time();

        $out = array();
        /*
         *Bug ID #351(display only current and upcoming semesters for create exam)
         *Resolved by vinod
         */
        $today = time();
        $sql = "SELECT * from {local_school_semester} where schoolid = {$sid}";
        $semesters = $DB->get_records_sql($sql);

        $out[null] = "Select Course Offering";
        foreach ($semesters as $sn) {
            $visible = $DB->get_field('local_semester', 'visible', array('id' => $sn->semesterid));
            $enddate = $DB->get_field('local_semester', 'enddate', array('id'=>$sn->semesterid));
            if (($visible > 0) && ($enddate > $today)) {
                $semname = $DB->get_field('local_semester', 'fullname', array('id' => $sn->semesterid));

                $out[$sn->semesterid] = format_string($semname);
            }
        }

        return $out;
    }


     /**
     * @method get_cobaltcourse_scheduleexam    
     * @Todo Function to get cobaltcourse name of a class
     * @param int $clid Class id.
     * @return string cobaltcourse fullname
     */    
    function get_cobaltcourse_scheduleexam($clid) {
        global $CFG, $DB, $USER;

        // $sql = "SELECT cobco.fullname from {local_cobaltcourses} cobco 
        // JOIN {local_clclasses} cl on cl.cobaltcourseid=cobco.id and cl.id={$clid}";
        $sql = "SELECT cobco.fullname from {local_cobaltcourses} cobco 
            JOIN {local_clclasses} cl on cl.cobaltcourseid=cobco.id and cl.id={$clid}";

        //$out = array();      

        $cobaltcourse = $DB->get_record_sql($sql);

        return $cobaltcourse->fullname;
    }

    /**
     * @method  tabs  
     * @Todo Function to display tabs
     * @param string $currenttab  current tab name
     * @param int $id used to change the string while editing
     * @return array
     */
    function tabs($currenttab, $id = -1) {

        GLOBAL $OUTPUT;

        $toprow = array();
        // $string = ($id>0) ? get_string('editexamheader', 'local_scheduleexam') : get_string('createexamheader', 'local_scheduleexam') ;
        $update_cap = array('local/scheduleexam:manage', 'local/scheduleexam:update');
        $create_cap = array('local/scheduleexam:manage', 'local/scheduleexam:create');
        $systemcontext =context_system::instance(); 
        if ($id > 0) {
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/scheduleexam/edit.php'), get_string('editexamheader', 'local_scheduleexam'));
        }
        else {
            if (has_any_capability($create_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/scheduleexam/edit.php'), get_string('createexamheader', 'local_scheduleexam'));
        }


        $toprow[] = new tabobject('view', new moodle_url('/local/scheduleexam/index.php'), get_string('view', 'local_scheduleexam'));
        if (has_capability('local/scheduleexam:manage', $systemcontext)) {
            $toprow[] = new tabobject('upload', new moodle_url('/local/scheduleexam/upload.php'), get_string('uploadexams', 'local_scheduleexam'));
        }
        $toprow[] = new tabobject('info', new moodle_url('/local/scheduleexam/info.php'), get_string('info', 'local_scheduleexam'));
//    $toprow[] = new tabobject('reports', new moodle_url('#'), get_string('reports','local_scheduleexam'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method  studentside_tabs
     * @Todo Function to display tabs
     * @param string $currenttab  current tab name     
     * @return array
     */
    public function studentside_tabs($currenttab) {
        global $OUTPUT, $DB;
        $toprow = array();

        $toprow[] = new tabobject('myplan', new moodle_url('/local/courseregistration/mycurplans.php'), get_string('myplan', 'local_courseregistration'));
        $toprow[] = new tabobject('mycurrentplan', new moodle_url('/local/courseregistration/myclasses.php'), get_string('mycurrentplan', 'local_courseregistration'));
        /* $toprow[] = new tabobject('scheduleclclasses', new moodle_url('/local/courseregistration/myclasses.php'), get_string('scheduleclclasses','local_courseregistration'));

         */

        $toprow[] = new tabobject('scheduledexams', new moodle_url('/local/scheduleexam/'), get_string('scheduledexams', 'local_classroomresources'));
        $toprow[] = new tabobject('mytranscript', new moodle_url('/local/myacademics/transcript.php'), get_string('mytranscript', 'local_classroomresources'));
        //$toprow[] = new tabobject('academiccalendar', new moodle_url('/local/academiccalendar/'), get_string('pluginname', 'local_academiccalendar'));

        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

}
