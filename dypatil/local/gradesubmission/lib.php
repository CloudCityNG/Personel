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
 * @subpackage gradesubmission
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') or die;
require_once($CFG->dirroot . '/local/lib.php');
require_once($CFG->dirroot . '/message/lib.php');

class grade_submission {

    private static $_submission;

    public static function getInstance() {
        if (!self::$_submission) {
            self::$_submission = new grade_submission();
        }
        return self::$_submission;
    }

    /**
     * @method get_clclasseslist_gradesub
     * Function to get clclasses under a semester.
     * @param  object $semid - This parameter contains semesterid.
     * @return array of class list
     */
    function get_clclasseslist_gradesub($schid, $semid) {
        global $DB, $USER;
        $hierarchy = new hierarchy();
        $out = $hierarchy->get_records_cobaltselect_menu('local_clclasses', "schoolid=$schid and semesterid=$semid AND visible=1", null, '', 'id,fullname', 'Select Class');
        return $out;
    }

    /**
     * @method get_class_users
     * @todo To get users for particular semester and class and registrar approved
     * @param int $semid Semester ID
     * @param int $clsid Class ID
     * @return object $userslist Users list
     */
    function get_class_users($semid, $clsid) {
        global $CFG, $DB, $USER;

        $out = array();

        $sql = "SELECT clsusers.*,u.firstname,u.lastname FROM {local_user_clclasses} clsusers 
             JOIN {user} u on clsusers.userid=u.id
             where clsusers.semesterid = $semid and clsusers.classid = $clsid and clsusers.registrarapproval=1";

        $userslist = $DB->get_records_sql($sql);
        return $userslist;
    }

    /**
     * @method get_class_exams
     * @todo To get exams for particular semester and class
     * @param int $semid Semester ID
     * @param int $clsid Class ID
     * @return object $classexams Exams list
     */
    function get_class_exams($semid, $clsid) {
        global $CFG, $DB, $USER;

        $sql = "select comp.*,ex.examtype,ex.lecturetype,ex.opendate,ex.endtimehour,ex.endtimemin,ex.grademax from {local_class_completion} comp
            LEFT JOIN {local_scheduledexams} ex on comp.examid=ex.id where comp.semesterid = {$semid} and comp.classid = {$clsid} AND ex.visible=1"; //exit;

        $classexams = $DB->get_records_sql($sql);
        return $classexams;
    }

    /**
     * @method get_clclasseslist_gradesubmission
     * @Todo to get clclasses under a semester.
     * @param  object $semid - This parameter contains semesterid.
     * @return array
     */
    function get_clclasseslist_gradesubmission($schid, $semid) {
        global $DB, $USER;
        $hierarchy = new hierarchy();
        $out = $hierarchy->get_records_cobaltselect_menu('local_clclasses', "semesterid=$semid and schoolid=$schid AND visible=1", null, '', 'id,fullname', 'Select Class');
        return $out;
    }

    /**
     * @method local_insert_gradesubmission
     * @todo To submit user grades for particular users from class and semester
     * @param object $data Form submitted data
     * @param int $sem Semester ID
     * @param int $cls Class ID
     */
    function local_insert_gradesubmission($data, $sem, $cls) {
        global $CFG, $DB, $USER;

        $users = $this->get_class_users($sem, $cls);

        // All users in a class
        foreach ($users as $user) {

            // All users submitted exams data (examid array)
            foreach ($data as $exam) {

                // each users's examid array ; $useridarray has Userids
                foreach ($exam as $exid => $useridarray) {

                    $exam = new stdClass();
                    $exam->examid = $exid;
                    $exam->schoolid = $data['school'];
                    $exam->semesterid = $data['semester'];
                    $exam->classid = $data['class'];
                    $exam->timecreated = time();
                    $exam->timemodified = time();
                    $exam->usermodified = $USER->id;

                    // each userid's -exam type array ; $examofferingarray has offline or online type of exam
                    foreach ($useridarray as $uid => $examsourcearray) {
                        $exam->userid = $uid;
                        /* ---start of vijaya--- */
                        /*  $conf->username=$DB->get_field('user','username',array('id'=>$exam->userid));
                          $conf->classname=$DB->get_field('local_clclasses','fullname',array('id'=>$exam->classid));
                          $message=get_string('msg_stu_grade','local_gradesubmission',$conf);
                          $userfrom = $DB->get_record('user', array('id' => $USER->id));
                          $userto = $DB->get_record('user', array('id' =>$exam->userid));
                          $message_post_message = message_post_message($userfrom,$userto,$message,FORMAT_HTML); */
                        /* ---end of vijaya--- */

                        // examsource and examgrade
                        foreach ($examsourcearray as $examsource => $examgrade) {

                            $source = $examsource;
                            $exam->finalgrade = $examgrade;

                            $exam->finalgrade;
                            $exam->source = $source;


                            if ($source == 'offline') {
                                $grademax = $DB->get_field('local_scheduledexams', 'grademax', array('id' => $exid)); //exit;
                            } else {
                                $grademax = $DB->get_field('grade_items', 'grademax', array('id' => $exid));
                            }
                            if ($exam->finalgrade > round($grademax)) {
                                //echo "hello";exit;
                                return 'err';
                            }

                            $sql = "SELECT * from {local_user_examgrades} where userid={$uid} and semesterid={$data['semester']} and classid={$data['class']} and examid={$exid} and source='{$source}'";

                            $record_exist = $DB->get_record_sql($sql);
                            // $record_exist = $DB->get_record('local_user_examgrades', array('userid'=>$user->userid,'semesterid'=>$data['semester'], 'classid'=>$data['class'], 'examid'=>$exid, 'source'=>$source));

                            if ($record_exist) {
                                $exam->id = $record_exist->id;
                                $exam->timemodified = time();
                                $exam->usermodified = $USER->id;

                                //print_object($exam);exit;
                                $updated = $DB->update_record('local_user_examgrades', $exam);
                            } else {   //print_object($exam);exit;
                                if (!empty($exam->finalgrade)) {
                                    $inserted = $DB->insert_record('local_user_examgrades', $exam);
                                }
                            }
                            $sqlhist = "SELECT * from {local_user_exgrade_hist} where userid={$uid} and semesterid={$data['semester']} and classid={$data['class']} and examid={$exid} and source='{$source}'";

                            $record_exist_history = $DB->get_record_sql($sqlhist);
                            //print_object($record_exist_history);exit;
                            if ($record_exist_history->finalgrade == $exam->finalgrade) {
                                
                            } else {
                                $y = $DB->insert_record('local_user_exgrade_hist', $exam);
                            }
                        }
                    }
                }
            }
        }
        if ($inserted) {
            return 1;
        }
        if ($updated) {
            return 2;
        }
    }

}

/**
 * @method student_coursegrades
 * @todo To get course grade of a particular user 
 * @param int $semid Semester ID 
 * @param int $semuserid User ID
 * @return array of objects( student course grade info)
 */
function student_coursegrades($semid, $semuserid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT lug.* FROM {local_user_clclasses} AS luc,{local_user_classgrades} AS lug
          WHERE  luc.userid={$semuserid} AND lug.userid={$semuserid}
          AND luc.semesterid={$semid}
          AND luc.registrarapproval=1
          AND luc.classid=lug.classid ";

    $grds = $DB->get_records_sql($sql);
    return $grds;
}

/**
 * @method local_gradesubmission_cron
 * @todo To submit grades of students of semester when cron runs
 * @param int $semid Semester ID 
 * @param int $semuserid User ID
 * @return int inserted row ID.
 */
function local_gradesubmission_cron() {
    global $DB;

    $today = time();
    $validdate = strtotime('+1 month', $today);

    $sql = 'SELECT * from {local_semester} where visible=1';

    $semesters = $DB->get_records_sql($sql);

    foreach ($semesters as $sem) {

        if ($sem->enddate <= $validdate) {
            $semid = $sem->id;
            $usql = "SELECT * from {local_user_semester} usem
                      JOIN {user} u on u.id=usem.userid
                      where u.deleted<>1 and semesterid ={$semid} ORDER BY userid";

            $sem_users = $DB->get_records_sql($usql);

            foreach ($sem_users as $semuser) {
                echo '</br> UserID : ' . $semuser->userid;
                $semuserid = $semuser->userid;

                $stucursql = "SELECT * from {local_userdata} where userid={$semuserid}";

                $stucur = $DB->get_records_sql($stucursql);

                //print_object($stucur);

                foreach ($stucur as $stucur) {

                    $classgrades = student_coursegrades($semid, $semuserid);
                    // print_object($classgrades); //exit;

                    $totcoursecredits = $totalwgps = $GPA = 0;

                    foreach ($classgrades as $clgrade) {

                        $courseid = $clgrade->courseid;

                        $cur_pro_sql = "SELECT * FROM {local_curriculum_plancourses} planco 
                                        JOIN {local_userdata} udat on udat.curriculumid=planco.curriculumid 
                                        where planco.curriculumid={$stucur->curriculumid} and planco.courseid={$courseid} and udat.userid={$semuserid}";

                        $course_exist_incur = $DB->get_records_sql($cur_pro_sql);
                        //print_object($course_exist_incur);

                        if ($course_exist_incur) {

                            $coursecredits = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
                            $totcoursecredits = $totcoursecredits + $coursecredits;
                            $wgp = $clgrade->gradepoint * $coursecredits;
                            $totalwgps = $totalwgps + $wgp;
                        }
                    }

                    //temporary 
                    echo '</br>  totalGPs ' . $totalwgps;
                    echo ' </br> totcrhours  ' . $totcoursecredits;

                    if ($totalwgps AND $totcoursecredits) {
                        $GPA = round($totalwgps, 2) / round($totcoursecredits, 2);
                        echo ' </br> GPA : ' . round($GPA, 2);
                    } else {
                        echo ' </br> GPA : ' . $GPA;
                    }
                    echo '</br> ';
                    echo '------------';
                    // print_object($classgrades);
                    //temporary 
                    if ($totalwgps AND $totcoursecredits) {
                        $GPA = round($totalwgps, 2) / round($totcoursecredits, 2);
                        $GPA = round($GPA, 2);
                    }


                    $academiclevel = $DB->get_field('local_program', 'programlevel', array('id' => ($stucur->programid)));
                    $probationgpa = $DB->get_field('local_cobalt_gpasettings', 'probationgpa', array('schoolid' => ($stucur->schoolid), 'sub_entityid' => ($academiclevel)));
                    $dismissalgpa = $DB->get_field('local_cobalt_gpasettings', 'dismissalgpa', array('schoolid' => ($stucur->schoolid), 'sub_entityid' => ($academiclevel)));

                    $sql = "SELECT * from {local_user_sem_details} where userid={$semuserid} and semesterid={$semid} and curriculumid={$stucur->curriculumid} and programid={$stucur->programid}";
                    $record_exists_gpa = $DB->get_record_sql($sql);

                    if ($record_exists_gpa) {

                        $semgradedet = new stdClass();
                        $semgradedet->id = $record_exists_gpa->id;
                        $semgradedet->gpa = $GPA;


                        if (empty($probationgpa) OR empty($dismissalgpa)) {
                            $semgradedet->studentstatus = 99;
                            $semgradedet->reason = 'NOT DEFINED';
                        } elseif ($GPA < $probationgpa AND $GPA > $dismissalgpa) {
                            $semgradedet->studentstatus = 1;
                            $semgradedet->reason = 'Probation';
                        } elseif ($GPA < $dismissalgpa) {
                            $semgradedet->studentstatus = 2;
                            $semgradedet->reason = 'Academic Dismissal';
                        } else {
                            $semgradedet->studentstatus = 0;
                            $semgradedet->reason = 'Good Standing';
                        }

                        $semgradedet->totgradepoints = $totalwgps;
                        $semgradedet->totcredithours = $totcoursecredits;
                        $semgradedet->timemodified = time();
                        $DB->update_record('local_user_sem_details', $semgradedet);
                    } else {
                        $semgradedet = new stdClass();
                        $semgradedet->userid = $semuserid;
                        $semgradedet->semesterid = $semid;
                        $semgradedet->curriculumid = $stucur->curriculumid;
                        $semgradedet->programid = $stucur->programid;
                        $semgradedet->gpa = $GPA;


                        if (empty($probationgpa) OR empty($dismissalgpa)) {
                            $semgradedet->studentstatus = 99;
                            $semgradedet->reason = 'NOT DEFINED';
                        } elseif ($GPA < $probationgpa AND $GPA > $dismissalgpa) {
                            $semgradedet->studentstatus = 1;
                            $semgradedet->reason = 'Probation';
                        } elseif ($GPA < $dismissalgpa) {
                            $semgradedet->studentstatus = 2;
                            $semgradedet->reason = 'Academic Dismissal';
                        } else {
                            $semgradedet->studentstatus = 0;
                            $semgradedet->reason = 'Good Standing';
                        }
                        echo $semgradedet->totgradepoints = $totalwgps;
                        $semgradedet->totcredithours = $totcoursecredits;
                        $semgradedet->timecreated = time();
                        $semgradedet->timemodified = time();
                        print_object($semgradedet);
                        $DB->insert_record('local_user_sem_details', $semgradedet);
                    }
                }
            }
        }
    }
}

/**
 * @method get_school_parent
 * @param1  form object
 * @param2 Element position (string)
 * @param3 Schoolid(int)
 * @return Element value
 * */
function get_school_parent($schools, $selected = array(), $inctop = true, $all = false) {
    $out = array();
    //if an integer has been sent, convert to an array
    if (!is_array($selected)) {
        $selected = ($selected) ? array(intval($selected)) : array();
    }
    if ($inctop) {
        $out[null] = '---Select---';
    }
    if ($all) {
        $out[0] = get_string('all');
    }
    if (is_array($schools)) {
        foreach ($schools as $parent) {
            // An item cannot be its own parent and cannot be moved inside itself or one of its own children
            // what we have in $selected is an array of the ids of the parent nodes of selected branches
            // so we must exclude these parents and all their children
            //add using same spacing style as the bulkitems->move available & selected multiselects
            foreach ($selected as $key => $selectedid) {
                if (preg_match("@/$selectedid(/|$)@", $parent->path)) {
                    continue 2;
                }
            }
            $out[$parent->id] = /* str_repeat('&nbsp;', 4 * ($parent->depth - 1)) . */ format_string($parent->fullname);
        }
    }

    return $out;
}

/**
 * @method get_assignedschools_inst
 * @Todo To get instructors data of schools
 * @return array of school(school instructor list)
 * */
function get_assignedschools_inst() {
    global $DB, $CFG, $USER;
    $items = array();
    $sql = "SELECT * FROM " . $CFG->prefix . "local_dept_instructor WHERE instructorid = {$USER->id}";
    //echo $sql;
    $schools = $DB->get_records_sql($sql);
    if(empty($schools))
       print_cobalterror('not_assigned_dept','local_gradesubmission');
    foreach ($schools as $school) {
        $items[] = $DB->get_record('local_school', array('id' => $school->schoolid, 'visible' => 1));
    }
    if (!empty($items)) {
        foreach ($items as $item) {
            //check the school is allowed to access the child school
            $list = array();
            if ($item->childpermission) {
                //get te child school upto only one level
                $childs = $DB->get_records('local_school', array('parentid' => $item->id, 'visible' => 1));
                foreach ($childs as $child) {
                    $list[] = $DB->get_record('local_school', array('id' => $child->id, 'visible' => 1));
                }
            }
        }
        $items = array_merge($items, $list);
    }
    return $items;
}

/**
 * @method get_school_semesters_inst
 * @Todo To get instructors of a school of a semester
 * @param int $id School ID
 * @return array of school(school instructor list)
 * */
function get_school_semesters_inst($id) {
    global $DB, $USER;

    $today = time();
    $edate = strtotime('+1 month', $today);

    $out = array();
    $sql = "SELECT ls.id,ls.fullname
                    FROM {local_scheduleclass} AS ss
                    JOIN {local_semester} AS ls
                    ON ss.semesterid=ls.id where ss.schoolid={$id} AND ls.visible=1 and ls.startdate<{$today} and ls.enddate>{$today} and ss.instructorid={$USER->id} group by ls.id";
    $out[NULL] = "---Select---";
    $semesterlists = $DB->get_records_sql($sql);
    foreach ($semesterlists as $semesterlist) {
        $out[$semesterlist->id] = $semesterlist->fullname;
    }
    return $out;
}

/**
 * @method get_school_semesters_inst
 * @Todo To get class list of a particular instructor,semesterid and schoolid
 * @param int $schid School ID
 * @param int $semid Semester ID
 * @param int $instid instructor ID
 * @return array of class list
 * */
function get_clclasseslist_gradesub_inst($schid, $semid, $instid) {
    global $DB, $USER;

    $out = array();
    $out[null] = "Select Class";
    $sql = "SELECT * from {local_scheduleclass} where schoolid={$schid} and semesterid={$semid} and instructorid={$instid}";
    $clclasses = $DB->get_records_sql($sql);
    foreach ($clclasses as $cla) {
        $classname = $DB->get_field('local_clclasses', 'fullname', array('id' => ($cla->classid)));
        $out[$cla->classid] = format_string($classname);
    }
    return $out;
}

/**
 * @method createtabview_gsub
 * @Todo To display tab view
 * @param string $currenttab Current tab 
 * @return print tab tree
 * */
function createtabview_gsub($currenttab, $inst = 0) {
    global $OUTPUT, $USER;
    $tabs = array();
    $systemcontext = context_system::instance();
    if (has_capability('local/scheduleexam:manage', $systemcontext)) {
        $tabs[] = new tabobject('submitgrades', new moodle_url('/local/gradesubmission/submitgrades.php'), get_string('submitgrades', 'local_gradesubmission'));
    } else {
        $tabs[] = new tabobject('submitgrades', new moodle_url('/local/gradesubmission/instructor.php'), get_string('submitgrades', 'local_gradesubmission'));
    }
    $tabs[] = new tabobject('viewgrades', new moodle_url('/local/gradesubmission/index.php'), get_string('viewgrades', 'local_gradesubmission'));
    $tabs[] = new tabobject('upload', new moodle_url('/local/gradesubmission/upload.php'), get_string('uploadgrades', 'local_gradesubmission'));
    $tabs[] = new tabobject('info', new moodle_url('/local/gradesubmission/info.php'), get_string('info', 'local_gradesubmission'));
    echo $OUTPUT->tabtree($tabs, $currenttab);
}
