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
 * @subpackage classes
 * @copyright  2013 Niranjan <niranjan@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;

use moodle\local\classes as classes;

/**
 * Function to add the data into the database
 */
class schoolclasses {

    /**
     * @method schedule
     * @todo Schedule the classes
     * @param object $data Submitted data
     * @param object $classes classID
     * @return Schedule the class and returns the scheduled classID
     */
    public function scheudle($data, $classes) {
        global $DB, $USER;
        $schedule = new stdClass();
        $schedule->classid = $classes;
        $schedule->schoolid = $data->schoolid;
        $schedule->semesterid = $data->semesterid;
        $schedule->courseid = $data->cobaltcourseid;
        $schedule->classroomid = $data->classroomid;
        $schedule->startdate = $data->startdate;
        $schedule->enddate = $data->enddate;
        $schedule->starttime = $data->starthour . ':' . $data->startmin;
        $schedule->endtime = $data->endhour . ':' . $data->endmin;
        $schedule->usermodified = $USER->id;
        $schedule->timecreated = time();
        $schedule->timemodified = time();
        $instructors = $data->instructorid;
        $schedule->departmentinid = $data->departmentinid;

        if (is_array($instructors)) {
            foreach ($instructors as $instructor) {
                $schedule->instructorid = $instructor;
                if ($data->onlinecourseid && $data->online == 1)
                    $this->addecoursetoinstr($data->onlinecourseid, $schedule->instructorid);
                if (!$DB->record_exists('local_scheduleclass', array('schoolid' => $schedule->schoolid, 'instructorid' => $schedule->instructorid, 'classid' => $classes, 'semesterid' => $schedule->semesterid)))
                    $scheduleclass = $DB->insert_record('local_scheduleclass', $schedule);
            }
        }
        else {
            $schedule->instructorid = $instructors;
            if ($data->onlinecourseid && $data->online == 1 && $schedule->instructorid)
                $this->addecoursetoinstr($data->onlinecourseid, $schedule->instructorid);
            if (!$DB->record_exists('local_scheduleclass', array('schoolid' => $schedule->schoolid, 'instructorid' => $schedule->instructorid, 'classid' => $schedule->classid, 'semesterid' => $schedule->semesterid)))
                return $scheduleclass = $DB->insert_record('local_scheduleclass', $schedule);
        }
    }

    /**
     * @method addcoursetoinstr
     * @todo Add couses to the instructors
     * @param int $ecourseid CourseID
     * @param int $instructorid InstructorID
     */
    public function addecoursetoinstr($ecourseid, $instructorid) {
        global $DB, $CFG, $USER;
        $hierarchy = new hierarchy();
        $sql = "SELECT * FROM {context} where contextlevel=50 AND instanceid=$ecourseid";
        $userfrom = $DB->get_record('user', array('id' => $USER->id));
        $cont = $DB->get_records_sql($sql);
        foreach ($cont as $contexts) {
            $contextids = $contexts->id;
            $roleassign = new object();
            $roleassign->userid = $instructorid;
            $roleassign->roleid = 3;
            $roleassign->contextid = $contextids;
            $roleassign->modifierid = $USER->id;
            $roleassign->timemodified = time();
            $roleassign->enrol = "manual";

            $sql = "SELECT ra.id FROM {role_assignments} ra where ra.userid={$instructorid} AND ra.roleid=5 AND ra.contextid={$contextids} ";

            $exist = $DB->get_records_sql($sql);

            if ($exist)
                return get_string('alreadyreg', 'local_courseregistration');
            else
                $rem = $DB->insert_record('role_assignments', $roleassign);


            $enroll = $this->get_enrollmentid($ecourseid);
            $userenroll = new object();
            $userenroll->userid = $instructorid;
            $userenroll->enrolid = $enroll;

            $sql = "SELECT ue.id FROM {user_enrolments} ue where userid={$instructorid} AND enrolid={$enroll}";

            $existenroll = $DB->get_records_sql($sql);
            if ($existenroll)
                return get_string('alreadyreg', 'local_courseregistration');
            else
                $enrol = $DB->insert_record('user_enrolments', $userenroll);

            if ($enrol) {

                $message = get_string('scheduledclasses', 'local_courseregistration');
                $message_post_message = message_post_message($userfrom, $instructorid, $message, FORMAT_HTML);
            }
        }
    }

    /**
     * @method get_enrollmentid
     * @todo Get enrollment id's for particular course which is not approved
     * @param int $courseid CourseID
     * @return Return the enrollment ID
     */
    public function get_enrollmentid($courseid) {
        global $DB;
        $courseenroll = $DB->get_records('enrol', array('courseid' => $courseid, 'status' => 0));
        foreach ($courseenroll as $enrolid) {
            return $enrolid->id;
        }
    }

    /**
     * @method classes_add_instance
     * @todo To add the classes
     * @param object $data
     */
    public function classes_add_instance($data) {
        global $DB, $USER, $CFG;

        $classes->id = $DB->insert_record('local_clclasses', $data);

        $scheduleclass = $this->scheudle($data, $classes->id);
        $hierarchy = new hierarchy();
        $currenturl = "{$CFG->wwwroot}/local/clclasses/index.php";
        ;
        $conf = new object();
        $conf->class = $data->fullname;
        if ($classes->id) {
            $message = get_string('createsuccess', 'local_clclasses', $conf);
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        } else {
            $message = get_string('failclass', 'local_clclasses', $conf);
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifyproblem'));
        }
    }

    function updateclassinstructor($data, $classid) {
        global $DB, $USER, $CFG;
    }

    /**
     * @method classes_update_instance
     * @todo To update the class
     * @param object $updateclass Classes update
     * 
     */
    public function classes_update_instance($updateclass) {
        global $DB, $USER, $CFG;


        $oldinstructor = $DB->get_records('local_scheduleclass', array('classid' => $classid));

        if ($oldinstructor->instructorid == $updateclass->instructorid) {
            $this->updateclassinstructor($updateclass, $updateclass->id);
        }

        $update->id = $DB->update_record('local_clclasses', $updateclass);
        $DB->delete_records('local_scheduleclass', array('classid' => $updateclass->id));

        $updateschedule = $this->scheudle($updateclass, $updateclass->id, $updateclass->instructorid);
        $conf = new object();
        $conf->class = $updateclass->fullname;
        $hierarchy = new hierarchy();
        $currenturl = "{$CFG->wwwroot}/local/clclasses/index.php";
        ;
        if ($updateschedule && $update) {


            $message = get_string('updatesuccess', 'local_clclasses', $conf);
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        }
    }

    /**
     * @method classes_delete_instance
     * @todo To delete the class
     * @param object $data
     * 
     */
    public function classes_delete_instance($data) {
        global $DB, $CFG;

        $classins = $DB->delete_records('local_clclasses', array('id' => $data));
        $DB->delete_records('local_scheduleclass', array('classid' => $data));

        $hierarchy = new hierarchy();
        $currenturl = "{$CFG->wwwroot}/local/clclasses/index.php";
        ;
        if ($classins) {
            $message = get_string('deletesuccess', 'local_clclasses');
            $hierarchy->set_confirmation($message, $currenturl, array('style' => 'notifysuccess'));
        }
    }

    /**
     * @method print_classtabs
     * @todo To create tab tree for classes
     * @param int $id ID field
     * @param string $currenttab For active tab
     * 
     */
    public function print_classestabs($currenttab, $id = -1) {
        global $OUTPUT;
        $systemcontext = context_system::instance();
        $toprow = array();
        // $string = ($id>0) ? get_string('editclasses', 'local_clclasses') : get_string('createclasses', 'local_clclasses') ;
        //$toprow[] = new tabobject('create', new moodle_url('/local/clclasses/classes.php'),$string);
        if ($id > 0) {
            $update_cap = array('local/clclasses:manage', 'local/clclasses:update');
            if (has_any_capability($update_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/clclasses/createclass.php'), get_string('editclasses', 'local_clclasses'));
        }
        else {
            $create_cap = array('local/clclasses:manage', 'local/clclasses:create');
            if (has_any_capability($create_cap, $systemcontext))
                $toprow[] = new tabobject('create', new moodle_url('/local/clclasses/createclass.php'), get_string('createclasses', 'local_clclasses'));
        }

        $toprow[] = new tabobject('view', new moodle_url('/local/clclasses/index.php'), get_string('view', 'local_clclasses'));
        $toprow[] = new tabobject('upload', new moodle_url('/local/clclasses/upload.php'), get_string('uploadclass', 'local_clclasses'));
        $toprow[] = new tabobject('report', new moodle_url('/local/clclasses/classesreport.php'), get_string('reports', 'local_clclasses'));
        $toprow[] = new tabobject('info', new moodle_url('/local/clclasses/info.php'), get_string('info', 'local_clclasses'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method print_scheduletabs
     * @todo To create tab tree for classe scheduling
     * @param string $currenttab For active tab
     * @param int $id ID
     * @param int $value Value
     * @param int $flag Flag variable
     */
    public function print_scheduletabs($currenttab, $id = -1, $value = 0, $flag = 0) {
        global $OUTPUT;

        $toprow = array();
        $string = ($id > 0) ? get_string('editclasses', 'local_clclasses') : get_string('createclasses', 'local_clclasses');
        $toprow[] = new tabobject('create', new moodle_url('/local/clclasses/createclass.php'), $string);
        if ($flag == 1) {
            $toprow[] = new tabobject('schedule', new moodle_url('/local/clclasses/scheduleclass.php'), get_string('scheduleclassroom', 'local_clclasses'));
        }
        if ($value == 1) {
            $toprow[] = new tabobject('enroll', new moodle_url('/local/clclasses/enroluser.php'), get_string('enrollusers', 'local_clclasses'));
        }
        $toprow[] = new tabobject('view', new moodle_url('/local/clclasses/index.php'), get_string('view', 'local_clclasses'));
        $toprow[] = new tabobject('upload', new moodle_url('/local/clclasses/upload.php'), get_string('uploadclass', 'local_clclasses'));
        $toprow[] = new tabobject('report', new moodle_url('/local/clclasses/classesreport.php'), get_string('reports', 'local_clclasses'));
        $toprow[] = new tabobject('info', new moodle_url('/local/clclasses/info.php'), get_string('info', 'local_clclasses'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method get_classinstructors
     * @param int $classid Class ID
     * @return object $data instructors list  
     *
     */
    public function get_classinstructors($classid) {
        global $DB, $CFG;

        $sql = "select u.firstname,u.id,u.lastname FROM {local_scheduleclass} as lsc,{user} as u where lsc.instructorid=u.id AND lsc.classid={$classid} AND u.deleted=0";

        $instructorlists = $DB->get_records_sql($sql);

        $data = array();
        if ($instructorlists) {
            foreach ($instructorlists as $instructor) {
                $data[] = html_writer::link(new moodle_url('/user/profile.php', array('id' => $instructor->id, 'sesskey' => sesskey())), $instructor->firstname . '&nbsp' . $instructor->lastname);
            }
        } else
            $data[] = get_string('notassing', 'local_clclasses');

        return $data;
    }

    /**
     * @method get_classexams
     * @param int $id ID
     * @return Exam data with name and lecture type
     */
    public function get_classexams($id) {
        global $DB;
        $exams = "select se.*,et.examtype as examname,ll.lecturetype FROM {local_class_completion} cc,{local_scheduledexams} se,{local_examtypes} et,{local_lecturetype} ll where cc.classid={$id} AND cc.examid=se.id AND se.examtype=et.id AND se.lecturetype=ll.id AND se.classid={$id}";

        $exams = $DB->get_records_sql($exams);
        $examdata = array();
        $examdata[] = '<b>' . get_string('offlineexam', 'local_clclasses') . '</b><br/>';
        $i = 1;
        foreach ($exams as $classexams) {

            $examdata[] = $i . '.&nbsp' . $classexams->examname . '/' . $classexams->lecturetype . '<br/>';
            $i++;
        }
        return $examdata;
    }

    /**
     * @method get_onlineexams
     * @param int $id as classid
     * @return online exam data
     */
    public function get_onlineexams($id) {
        global $DB;
        $onlineexams = "SELECT * FROM {grade_items} gi, {local_class_completion} cc where cc.examid=gi.id AND cc.classid={$id}";
        $onlineexams = $DB->get_records_sql($onlineexams);
        $onlineexamdata = array();
        $onlineexamdata[] = '<b>' . get_string('onlineexam', 'local_clclasses') . '</b><br/>';
        $i = 1;
        foreach ($onlineexams as $classonlineexams) {

            $onlineexamdata[] = $i . '.&nbsp' . $classonlineexams->itemname . '<br/>';
            $i++;
        }
        return $onlineexamdata;
    }

    /**
     * @method get_evaluations
     * @param int $id Class ID
     * @return Evaluation names for that class
     */
    public function get_evaluations($id) {
        global $DB, $CFG;
        $evaluation = "select e.* FROM {local_evaluation} le where le.classid={$id}";

        $evaluations = $DB->get_records('local_evaluation', array('classid' => $id));
        $evaluate = array();
        $i = 1;
        foreach ($evaluations as $evaluation) {

            $evaluate[] = $i . '.&nbsp<a>' . $evaluation->name . '</a><br/>';
            $i++;
        }
        return $evaluate;
    }

    /**
     * @method print_instructorviewtabs
     * @param string $currenttab For active tab
     * @todo To create tabs for instructors view
     */
    public function print_instructorviewtabs($currenttab) {
        global $OUTPUT;
        $toprow = array();
        $toprow[] = new tabobject('completed', new moodle_url('/local/clclasses/instview.php', array('mode' => 'completed')), get_string('complete', 'local_semesters'));
        $toprow[] = new tabobject('current', new moodle_url('/local/clclasses/instview.php', array('mode' => 'current')), get_string('current', 'local_semesters'));
        $toprow[] = new tabobject('upcoming', new moodle_url('/local/clclasses/instview.php', array('mode' => 'upcoming')), get_string('coming', 'local_semesters'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    public function get_selecteddepartment($id) {
        global $DB;

        $sql = "SELECT ls.departmentid as id FROM {local_clclasses} ls WHERE ls.id={$id} ";
        $departmentlist = $DB->get_record_sql($sql);
        return $departmentlist->id;
    }

    public function get_selectedinstructor($id) {
        global $DB;
        $sql = "SELECT ls.instructorid as id FROM {local_scheduleclass} AS ls WHERE ls.classid={$id}";
        $instructorlist = $DB->get_records_sql($sql);
        $instructorid = array();
        foreach ($instructorlist as $instructor) {
            $instructorid[$instructor->id] = $instructor->id;
        }
        return $instructorid;
    }

    /**
     * @method class_completion
     * @todo Set the course completion
     * @param int $classids Class ID
     * @param array $examsid Exams ID list
     * @param object $onlineexams List of online courses
     */
    public function class_completion($classids, $examsid, $onlineexams) {
        global $CFG, $DB, $OUTPUT, $USER;
        $hierarche = new hierarchy ();

        $currenturl = "{$CFG->wwwroot}/local/clclasses/examsetting.php?id={$classids}";
        $classes = $DB->get_record('local_clclasses', array('id' => $classids));
        $examids = array_reverse($examsid);
        foreach ($examids as $examid) {


            $registrar = new stdClass();
            $registrar->source = strtolower(get_string('offline', 'local_clclasses'));
            $registrar->examid = $examid;
            $registrar->schoolid = $classes->schoolid;
            $registrar->programid = $classes->programid;
            $registrar->semesterid = $classes->semesterid;
            $registrar->examweightage = 0;
            $registrar->classid = $classids;


            $checkexistofline = $DB->get_record('local_class_completion', array('classid' => $classids, 'schoolid' => $classes->schoolid, 'examid' => $examid));
            if ($checkexistofline) {
                break;
            } else
                $ll = $DB->insert_record('local_class_completion', $registrar);
        }

        $onlinecorses = array_reverse($onlineexams);
        foreach ($onlinecorses as $onlinecorse) {

            $registrar = new stdClass();
            $registrar->source = strtolower(get_string('online', 'local_clclasses'));
            $registrar->examid = $onlinecorse;
            $registrar->schoolid = $classes->schoolid;
            $registrar->programid = $classes->programid;
            $registrar->semesterid = $classes->semesterid;
            $registrar->examweightage = 0;
            $registrar->classid = $classids;

            $classes = $DB->get_record('local_clclasses', array('id' => $onlinecorse));
            $checkexistonline = $DB->get_record('local_class_completion', array('classid' => $onlinecorse, 'schoolid' => $schoolid, 'examid' => $onlinecorse));
            if ($checkexistonline)
                break;
            else
                $ll = $DB->insert_record('local_class_completion', $registrar);
        }
        if ($checkexistofline && $checkexistonline)
            $hierarche->set_confirmation(get_string('alreadyassigned', 'local_clclasses', array('id' => $onlinecorse)), $currenturl, array('style' => 'notifyproblem'));
        else
            $hierarche->set_confirmation(get_string('successinadding', 'local_clclasses', array('id' => $onlinecorse)), $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method classes_remove_criteria
     * @param int $exam Exam ID
     * @todo To delete the class completion
     */
    public function classes_remove_criteria($exam, $id) {
        global $DB, $CFG;
        $hierarche = new hierarchy ();
        $currenturl = "{$CFG->wwwroot}/local/clclasses/examsetting.php?id={$id}";
        $delete = $DB->delete_records('local_class_completion', array('id' => $exam));
        if ($delete)
            $hierarche->set_confirmation(get_string('removedscuccess', 'local_clclasses'), $currenturl, array('style' => 'notifysuccess'));
    }

    /**
     * @method view_classes
     * @param int $id ClassID
     * @todo To display the table for classes
     */
    public function view_classes($id) {        
        global $DB, $CFG;
        
        
        //   (SELECT MAX(CONCAT(b.shortname,'/',f.shortname,'/',cls.shortname)) as classroom FROM {local_scheduleclass} cs JOIN {local_classroom} cls ON cls.id=cs.classroomid JOIN {local_floor} f ON f.id=cls.floorid JOIN {local_building} b ON b.id=cls.buildingid WHERE cs.classid = lc.id) AS classroom,
        //     (select Max(concat(FROM_UNIXTIME(lsc.startdate, '%d-%b-%Y'),'&nbsp; - &nbsp;',FROM_UNIXTIME(lsc.enddate, '%d-%b-%Y'))) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduledate,
         //    (select lsc.availableweekdays FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0) as availableweekdays ,
          //s   (select Max(concat(lsc.starttime,'&nbsp;-&nbsp;',lsc.endtime)) FROM {local_scheduleclass} as lsc where lsc.classid=lc.id AND lsc.startdate>0 AND enddate>0 ) AS scheduletime
        $sql = "SELECT lc.*,
             cc.fullname AS coursename,
             ls.fullname AS semestername,ls.id AS semesterid,
             s.fullname AS schoolname,s.id AS schoolid,
             (select oc.fullname from {course} oc where oc.id=lc.onlinecourseid) AS onlinecoursename         

       FROM {local_clclasses} lc JOIN {local_semester} ls 
       ON lc.semesterid=ls.id JOIN {local_school} s 
       ON lc.schoolid=s.id JOIN {local_cobaltcourses} cc 
       ON lc.cobaltcourseid=cc.id where lc.id={$id}";
        $classes = $DB->get_record_sql($sql);


        $table = new html_table();
        $table->align = array('right', 'left', 'right', 'left');
        $table->size = array('15%', '35%', '15%', '35%');
        $table->width = '100%';

        $clas = new html_table_cell();
        $clas->text = $classes->description;
        $clas->colspan = 3;
        $classmode = ($classes->type == 1) ? get_string('clsmode_1', 'local_clclasses') : get_string('clsmode_2', 'local_clclasses');
        $classtype = ($classes->online == 1) ? get_string('online', 'local_clclasses') : get_string('offline', 'local_clclasses');
        $instructor = array();
        $exams = array();
        $evaluations = array();
        $clastype = array();
        $instructor[] = get_classinst($id);
        $exams[] = $this->get_classexams($id);

        $onlineexams = array();
        $onlineexams[] = $this->get_onlineexams($id);
        $evaluations[] = $this->get_evaluations($id);
        $evaluation = implode('', $evaluations[0]);
        $instruct = implode('', $instructor[0]);
        !empty($classes->availableweekdays) ? $classes->scheduledate = $classes->scheduledate . '(' . $classes->availableweekdays . ')' : null;

        $exams = implode(' ', $exams[0]);
        $onlineexams = implode(' ', $onlineexams[0]);
        $table->data[] = array('<b>' . get_string('classesshortname', 'local_clclasses') . '</b>', $classes->shortname, '<b>' . get_string('semestername', 'local_semesters') . ':</b>', $classes->semestername);
        $table->data[] = array('<b>' . get_string('course', 'local_cobaltcourses') . ':</b>', $classes->coursename, '<b>' . get_string('classmode', 'local_clclasses') . ':</b>', $classmode);
        $table->data[] = array('<b>' . get_string('classlimit', 'local_clclasses') . ':</b>', $classes->classlimit, '<b>' . get_string('instructor', 'local_clclasses') . ':</b>', $instruct);
        $clstype[] = '<b>' . get_string('classtype', 'local_cobaltcourses') . ':</b>';
        $clstype[] = $classtype;
        /*
         * ###Bugreport #184- Class details page
         * @author Naveen Kumar<naveen@eabyas.in>
         * (Resolved) If class is offline not showing onlinecoursename replaced with(--)
         */
        $classes->online == 1 ? $clstype[] = '<b>' . get_string('onlinecourse', 'local_clclasses') . ':</b>' : $clstype[] = '--';
        $classes->online == 1 ? $clstype[] = $classes->onlinecoursename : $clstype[] = '--';
        $table->data[] = $clstype;      
        $table->data[] = array('<b>'.  get_string('schoolid', 'local_collegestructure') . ':</b>', $classes->schoolname,'','','');
        $table->data[] = array('<b>' . get_string('exam', 'local_clclasses') . ':</b>', $exams . $onlineexams, '<b>' . get_string('evaluation', 'local_clclasses') . ':</b>', $evaluation);

        $table->data[] = array('<b>' . get_string('description', 'local_clclasses') . ':</b>', $clas);
        $optionrow = new html_table_row();                 
        $optioncell = new html_table_cell(table_multiplescheduled_view($id));
        $optioncell->colspan = 3;
        $optionrow->cells[] ='<b>'.get_string('scheduledinformation','local_clclasses').'</b>';
        $optionrow->cells[] = $optioncell;
           $table->data[] =  $optionrow->cells;

        echo html_writer::table($table);
    }

    function enrollinstructor_tocourse_duringclasscreation($insertedclassid) {
        global $DB, $CFG, $USER, $PAGE;

        if ($insertedclassid) {
            $query = "select cl.* FROM  
            {local_clclasses} cl WHERE cl.id = $insertedclassid and cl.onlinecourseid!=0 and cl.online=1 ";
            $onlinecourse_data = $DB->get_record_sql($query);
            if ($onlinecourse_data) {
                $instructorlist = explode(',', $onlinecourse_data->instructor);
                foreach ($instructorlist as $instructor) {
                    $manual = enrol_get_plugin('manual');
                    $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
                    $instance = $DB->get_record('enrol', array('courseid' => $onlinecourse_data->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                    $manual->enrol_user($instance, $instructor, $instructorrole->id);
                }
            }// end of inner if
        } // end of outer if
    }

    function enrollinstructor_tocourse_duringclassupdation($data) {
        global $DB, $CFG, $USER, $PAGE;
        /* Bug-id#255
         * @author hemalatha c arun<hemaltha@eabyas.in>
         * Resolved(enrolling instructor to online course, while assiinging class to instructor)
         */
        $query = "select cl.* from {local_clclasses} cl where  cl.onlinecourseid!=0 and cl.id= $data->id and cl.online=1 ";
        $onlinecourse_data = $DB->get_record_sql($query);
        if ($onlinecourse_data) {
            // $existingdata = $DB->get_record('local_scheduleclass', array('classid' => $data->classid));
            $existingdata = $DB->get_record('local_clclasses', array('id' => $data->id));
            $existinstructors = explode(',', $existingdata->instructor);
            $unassign_instructorlist = array_diff($existinstructors, $data->instructor);
            
            // function will be called when, online courseid changed
             if($existingdata->onlinecourseid != $data->onlinecourseid)
             $this->assign_unassign_instructor_toupdated_onlinecourseid($data);
             
            if ($unassign_instructorlist) {
                foreach ($unassign_instructorlist as $instructor) {
                    if ($instructor) {
                        // unassigning instructor from schedule class
                        $scheduled_records=$DB->get_records('local_scheduleclass',array('classid'=>$data->id,'instructorid'=>$instructor));
                        foreach($scheduled_records as $rec){
                            $rec->instructorid=0;
                            $DB->update_record('local_scheduleclass', $rec);
                            
                        }
                                       
                        
                        // unenroll exists instructor from course, when the instructor changed in a class                        
                        $manual = enrol_get_plugin('manual');
                        $instance = $DB->get_record('enrol', array('courseid' => $existingdata->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->unenrol_user($instance, $instructor);
                    }
                }
            }// end of if unassigning instructor from course


            $assign_instructorlist = array_diff($data->instructor, $existinstructors);

            if ($assign_instructorlist) {
                foreach ($assign_instructorlist as $instructor) {
                    if ($instructor) {
                        // enroll the updated instructor to online course
                        $manual = enrol_get_plugin('manual');
                        $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
                        $instance = $DB->get_record('enrol', array('courseid' => $data->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $instructor, $instructorrole->id);
                    }
                }
            }// end of assigning instructor to course
        }// end of main if
    } // end of function
    
    function assign_unassign_instructor_toupdated_onlinecourseid($data){        
         global $DB, $CFG, $USER, $PAGE;
    
        $query = "select cl.* from {local_clclasses} cl where  cl.onlinecourseid!=0 and cl.id= $data->id and cl.online=1 ";
        $onlinecourse_data = $DB->get_record_sql($query);
        if ($onlinecourse_data) {
            // $existingdata = $DB->get_record('local_scheduleclass', array('classid' => $data->classid));
            $existingdata = $DB->get_record('local_clclasses', array('id' => $data->id));
            $existinstructors = explode(',', $existingdata->instructor);
             $difference= array_diff( $existinstructors, $data->instructor );
             if(sizeof($difference)==0){
                        foreach($data->instructor as $instructor){
                
                         // unenroll exists instructor from course, when the instructor changed in a class                        
                        $manual = enrol_get_plugin('manual');
                        $instance = $DB->get_record('enrol', array('courseid' => $existingdata->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->unenrol_user($instance, $instructor);
                        
                        // enroll the updated instructor to online course
                        $manual = enrol_get_plugin('manual');
                        $instructorrole = $DB->get_record('role', array('shortname' => 'instructor'));
                        $instance = $DB->get_record('enrol', array('courseid' => $data->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
                        $manual->enrol_user($instance, $instructor, $instructorrole->id);
                        } // end of foreach
             }// end of if condition
             

        }
        
        
        
    } // end of function
    
    
}// end of class

function enrolled_user_list_status() {
    global $CFG, $DB;
    $sql = "SELECT u.email,u.firstname,u.lastname,luc.registrarapproval,luc.mentorapproval,luc.semesterid,lc.fullname as classname FROM {local_clclasses} lc,{local_user_clclasses} luc,{user} u WHERE lc.id=luc.classid 
AND luc.userid=u.id ;";
    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method potentialuserselector
 * @param int $classid ClassID
 * @return array Returns the users list
 */
function potentialuserselector($classid, $returncount = false) {
    global $CFG, $DB;
    $list = array();
    $users = array();
    $i = 0;
    $j = 0;
    $k = 0;
    $x = array();
    $y = array();
    $z = array();
    $sql = "SELECT distinct(userid) FROM {local_user_clclasses} WHERE classid={$classid} AND registrarapproval=1 ";
    $array2 = $DB->get_records_sql($sql);
    foreach ($array2 as $a2) {
        $x[$i] = $a2->userid;
        $i++;
    }

    $allsql = "SELECT r.*  FROM {user} u,{role_assignments} r WHERE 
u.id=r.userid AND
r.roleid=5 ";
    $array1 = $DB->get_records_sql($allsql);
    foreach ($array1 as $a1) {
        $y[$j] = $a1->userid;
        $j++;
    }
    $coblatcourse = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
    $enrolled = "SELECT luc.id,luc.userid FROM {local_clclasses} lc,{local_user_clclasses} luc WHERE lc.cobaltcourseid={$coblatcourse} AND lc.id=luc.classid AND luc.registrarapproval=1";
    $enrollusers = $DB->get_records_sql($enrolled);
    foreach ($enrollusers as $enrolluser) {
        $z[$k] = $enrolluser->userid;
        $k++;
    }
    $result = array_diff($y, $x);
    $allusers = array_diff($result, $z);
    /* Bug report #249  -  Classes Management/Enroll Users
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * Resolved - displaying only selected school student instead of displaying all potential users
     */


    $classinfo = $DB->get_record('local_clclasses', array('id' => $classid));
    $classschoolid = $classinfo->schoolid;
    $applicants = $DB->get_records('local_userdata', array('schoolid' => $classschoolid));
    $applicant = array();
    foreach ($applicants as $app) {
        $applicant[] = $app->userid;
    }
    $users = array_intersect($applicant, $allusers);

    if ($returncount) {
        return sizeof($users);
    }
    $list[] = get_string('listuser', 'local_clclasses') . ( sizeof($users) );
    $list = array();
    foreach ($users as $a) {
        $user = $DB->get_record('user', array('id' => $a));
        $list[$a] = $user->firstname . ' ' . $user->lastname;
    }
    //$available = get_string('listuser', 'local_clclasses') . ' ('. sizeof($users) .')';
    //$list = array($available=>array());
    //foreach ($users as $a) {
    //    $user = $DB->get_record('user', array('id' => $a));
    //    $list[$available][$a] = $user->firstname . ' ' . $user->lastname;
    //}
    $list1 = $list;
    /*
     * ###Bugreport #classroom management
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * (Resolved)arranging student name in alphabetical order. 
     */
// sorting case sensitive value
    natcasesort($list1);
    $available = get_string('listuser', 'local_clclasses') . ' (' . sizeof($users) . ')';
    $newlist = array($available => array());
    //$newlist[] = get_string('listuser', 'local_clclasses') . ' ('. sizeof($users) .')';
    foreach ($list1 as $key => $value) {

        $newlist[$available][$key] = $value;
    }
//print_object($list1);
    return $newlist;
}

// end of function

/**
 * @method userelector
 * @param int $classid ClassID
 * @return array Returns the users list
 */
function userselector($classid, $returncount = false) {
    global $CFG, $DB;
    $list = array();
    $sql = "SELECT userid FROM {local_user_clclasses} WHERE classid={$classid} AND registrarapproval=1";
    $ausers = $DB->get_records_sql($sql);

    $count = count($ausers);
    if ($returncount) {
        return $count;
    }
    //$enrolled = get_string('listselusers', 'local_clclasses') . ( userselector($classid, true) );
    $enrolled = get_string('listselusers', 'local_clclasses') . ' (' . $count . ')';
    $list = array($enrolled => array());
    foreach ($ausers as $a) {
        $user = $DB->get_record('user', array('id' => $a->userid));
        $list[$enrolled][$a->userid] = $user->firstname . ' ' . $user->lastname;
    }
    return $list;
}

function free_classrooms($schoolid) {
    global $CFG, $DB;
    $allrooms = array();
    $allrooms[NULL] = '---Select---';
    $classroom = "SELECT * FROM {local_classroom} WHERE schoolid={$schoolid} AND visible=1";
    $classrooms = $DB->get_records_sql($classroom);
    foreach ($classrooms as $cms) {
        $allrooms[$cms->id] = $cms->fullname;
    }
    return $allrooms;
}

/**
 * @method check_conflicts
 * @todo To check the classes sheduling time conflicts
 * @param int $startdate Class Startdate
 * @param int $enddate Class Enddate
 * @param int $starttime Class Starttime
 * @param int $endtime Class endtime
 * @param int $schoolid SchoolID
 * @param int $insid Instructor ID
 * @param int $classroomid Class room ID
 * @return boolean true or false
 */
function check_conflicts($startdate, $enddate, $starttime, $endtime, $schoolid, $insid, $classroomid, $id = -1, $availabledates) {
    global $CFG, $DB;

    /* if($startdate['day'][0]<=9) {
      $startdate['day'][0]='0'.$startdate['day'][0];
      }
      if($enddate['day'][0]<=9) {
      $enddate['day'][0]='0'.$enddate['day'][0];
      }
      $startdate=$startdate['day'][0].'-'.$startdate['month'][0].'-'.$startdate['year'][0];
      $enddate=$enddate['day'][0].'-'.$enddate['month'][0].'-'.$enddate['year'][0]; */
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
    /* Bug report #254 
     * @author hemalatha c arun<hemalatha@eabyas.in>
     * Resolved- (provided proper validation)while scheduling classroom -eventhough classroom is free it showing busy.
     */

    $sql = "SELECT * FROM {local_scheduleclass} 
			         WHERE id!=$id AND schoolid={$schoolid} 
					 AND classroomid={$classroomid} AND
					( startdate BETWEEN '{$startdate}' AND '{$enddate}' OR 
					 enddate BETWEEN '{$startdate}' AND '{$enddate}' )AND 
                     (starttime BETWEEN '{$st}' AND '{$et}' OR 
			         endtime BETWEEN '{$st}' AND '{$et}') ";


    $classrooms = $DB->get_records_sql($sql);
    // Edited by hema

    $presentavailabledates = explode('-', $availabledates);
    $flag = 0;
    foreach ($classrooms as $class) {
        $existavailabledates = explode('-', $class->availableweekdays);
        $result = array_intersect($presentavailabledates, $existavailabledates);
        if (empty($result))
            $result = array_intersect($existavailabledates, $presentavailabledates);
        //print_object($result);
        if (!empty($result)) {
            $flag = 1;
            break;
        } else
            continue;
    }

    if (empty($classrooms) && $flag == 0) {
        return 0;
    } elseif (!empty($classrooms) && $flag == 0)
        return 0;
    else {
        return 1;
    }
}

function classes_get_school_semesters($id) {
    global $DB;
    $today = date('Y-m-d');
    $out = array();

    $sql = "SELECT ls.id,ls.fullname
                    FROM {local_school_semester} AS ss
                    JOIN {local_semester} AS ls
                    ON ss.semesterid=ls.id where ss.schoolid={$id} AND '{$today}' < DATE(FROM_UNIXTIME(ls.enddate)) AND visible=1 group by ls.id";
    $out[NULL] = "---Select---";
    $semesterlists = $DB->get_records_sql($sql);
    foreach ($semesterlists as $semesterlist) {

        $out[$semesterlist->id] = $semesterlist->fullname;
    }
    return $out;
}

function classes_add_scheduledsesion_event($formdata, $insertedscheduledid) {
    global $CFG, $USER, $DB;
   // print_object($formdata);
    $classinfo = $DB->get_record('local_clclasses', array('id' => $formdata->classid));
    $temp = new stdClass();
    $temp->name = $classinfo->shortname;
    $temp->description = '';
    $temp->format = 0;
    $temp->courseid = 1;
    $temp->groupid = 0;
    $temp->instance = 0;
    $temp->eventtype = 'site';
    $temp->timestart = $formdata->startdate;
    $temp->timeduration = $formdata->enddate - $formdata->startdate;
    $temp->visible = 1;
    $temp->sequence = 1;
    $temp->timemodified = time();
    $temp->subscriptionid = null;
    if ($insertedscheduledid) {
        $insertedrecordid = $DB->insert_record('event', $temp);
    }
}

// end of function


/*
 *
 *
 **/
function class_enrol_user($classid, $userid, $classlist = array()){
    global $DB, $CFG, $USER, $OUTPUT;
    $class = $DB->get_record('local_clclasses', array('id'=>$classid), '*', MUST_EXIST);
    $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
    $record = new Stdclass();
    $record->userid = $userid;
    $record->classid = $classid;
    if ($class->online == 1 && $class->onlinecourseid != 0) {
        $manual = enrol_get_plugin('manual');
        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $instance = $DB->get_record('enrol', array('courseid' => $class->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual->enrol_user($instance, $userid, $studentrole->id);
    }
    $record->semesterid = $class->semesterid;
    $record->programid = 0;
    $record->studentapproval = 1;
    $record->registrarapproval = 1;
    $record->timecreated = time();
    $record->timemodified = time();
    $record->usermodified = $USER->id;
    $record->ecourseid = 0;
 
    if(!$exist = $DB->get_record('local_user_clclasses', array('userid'=>$userid, 'classid'=>$classid))){
        $DB->insert_record('local_user_clclasses', $record);
    } else {
        if($exist->registrarapproval == 0 || $exist->registrarapproval == 2 || $exist->registrarapproval == 5){
            $exist->registrarapproval = 1;
            $DB->update_record('local_user_clclasses', $exist);
        }
    }
    
    $sem = new Stdclass();
    $sem->userid = $userid;
    $sem->semesterid = $class->semesterid;
    $sem->registrarapproval = 1;
    $sem->timecreated = time();
    $sem->timemodified = time();
    $sem->usermodified = $USER->id;
    $sem->programid = 0;
    $sem->curriculumid = 0;
    
    if (!$semuser = $DB->get_record('local_user_semester', array('userid' => $userid, 'semesterid' => $class->semesterid))) {
        $DB->insert_record('local_user_semester', $sem);
    } else {
        if($semuser->registrarapproval == 0 || $semuser->registrarapproval == 2 || $semuser->registrarapproval == 5){
            $semuser->registrarapproval = 1;
            $DB->update_record('local_user_semester', $semuser);
        }
    }
    require_once($CFG->dirroot . '/message/lib.php');
    $class->student = $user->firstname;
    $message = get_string('message_enrolledtoclass', 'local_clclasses', $class);
    $message = text_to_html($message);
    message_post_message($USER, $user, $message, FORMAT_HTML);
    return true;
}

function class_unenrol_user($classid, $userid){
    global $DB, $CFG, $USER;
    $class = $DB->get_record('local_clclasses', array('id'=>$classid), '*', MUST_EXIST);
    $user = $DB->get_record('user', array('id'=>$userid), '*', MUST_EXIST);
    if($uclass = $DB->get_record('local_user_clclasses', array('userid'=>$userid, 'classid'=>$class->id))){
        $uclass->registrarapproval = 5;
        $DB->update_record('local_user_clclasses', $uclass);
    }
    if($usem = $DB->get_record('local_user_semester', array('userid'=>$userid, 'semesterid'=>$class->semesterid))){
        $uclass->registrarapproval = 5;
        $DB->update_record('local_user_semester', $usem);
    }
    if($class->online == 1 && $class->onlinecourseid != 0){
        $manual = enrol_get_plugin('manual');
        $instance = $DB->get_record('enrol', array('courseid' => $class->onlinecourseid, 'enrol' => 'manual'), '*', MUST_EXIST);
        $manual->unenrol_user($instance, $userid);
    }
    require_once($CFG->dirroot . '/message/lib.php');
    $class->student = $user->firstname;
    $message = get_string('message_unenrolledfromclass', 'local_clclasses', $class);
    $message = text_to_html($message);
    message_post_message($USER, $user, $message, FORMAT_HTML);
    return true;
}

function check_enrolment_status($class, $userstoadd){
    global $DB, $CFG, $USER, $OUTPUT;
    $classlist = $DB->get_records_select('local_clclasses', 'id <> '.$class->id.' AND cobaltcourseid = '.$class->cobaltcourseid);
    $classcount = $DB->count_records('local_clclasses', array('cobaltcourseid'=>$class->cobaltcourseid));
    $enrolled = $rejected = $unenrolled = array();
    if(!empty($classlist)){
        foreach($classlist as $list){
            foreach($userstoadd as $user){
                $enrolledothers = $DB->get_record('local_user_clclasses', array('userid'=>$user->id, 'classid'=>$list->id, 'registrarapproval'=>1));
                $exist = $DB->get_record('local_user_clclasses', array('userid'=>$user->id, 'classid'=>$class->id));
                $u = $DB->get_record('user', array('id'=>$user->id), '*', MUST_EXIST);
                if($enrolledothers){
                    $enrolled[$user->id] = fullname($u);
                }
                if($exist){
                    if($exist->registrarapproval == 2){
                        $rejected[$user->id] = fullname($u);
                    } else if($exist->registrarapproval == 5){
                        $unenrolled[$user->id] = fullname($u);
                    }
                }
            }
        }
    }
    $msg = '';
    if(!empty($enrolled)){
        $return = $enrolled = array_unique($enrolled);
        $msg .= '<br/>'.$OUTPUT->rarrow() . '&nbsp;' . get_string('enrolledtootherclasses', 'local_clclasses', $list->fullname);
        $msg .= '<br/>'.implode(', ', $enrolled);
    }
    if(!empty($rejected)){
        $rejected = array_unique($rejected);
        $msg .= '<br/>'.$OUTPUT->rarrow() . '&nbsp;' . get_string('rejecteduser', 'local_clclasses');
        $msg .= '<br/>'.implode(', ', $rejected);
    }
    if(!empty($unenrolled)){
        $unenrolled = array_unique($unenrolled);
        $msg .= '<br/>'.$OUTPUT->rarrow() . '&nbsp;' . get_string('unenrolleduser', 'local_clclasses');
        $msg .= '<br/>'.implode(', ', $unenrolled);
    }
    echo '<p style="color:#008000;">'.$msg.'</p>';
    return $return;
}