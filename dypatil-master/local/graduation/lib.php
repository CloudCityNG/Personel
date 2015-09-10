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
 * @subpackage graduation
 * @copyright  2013 pramod <pramod@eabyas.in>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
/**
 * @method local_graduation_cron
 * @todo To calculate and add student grade point    
 */
function local_graduation_cron() {
    global $DB;
    $today = time();
    $sql = 'SELECT * from {local_semester}';
    $semesters = $DB->get_records_sql($sql);
    foreach ($semesters as $sem) {
        if ($sem->startdate <= $today AND $sem->enddate >= $today) {
            echo '</br> Semname : ' . $sem->fullname;
            $semid = $sem->id;
            $usql = "SELECT * from {local_user_semester} usem
                      JOIN {user} u on u.id=usem.userid
                      where u.deleted<>1 and semesterid ={$semid} ORDER BY userid";

            $sem_users = $DB->get_records_sql($usql);
            //print_object($sem_users);

            foreach ($sem_users as $semuser) {
                echo '</br> UserID : ' . $semuser->userid;
                $semuserid = $semuser->userid;
                $stucursql = "SELECT * from {local_userdata} where userid={$semuserid}";
                $stucur = $DB->get_records_sql($stucursql);
                //print_object($stucur);
                foreach ($stucur as $stucur) {
                    echo $sqlgpa = "SELECT * from {local_user_sem_details} where userid={$semuserid} and programid={$stucur->programid}";
                    $semgpa = $DB->get_records_sql($sqlgpa);
                    // print_object($semgpa);
                    $totalgradepoints = 0;
                    $totalcredithours = 0;
                    foreach ($semgpa as $sgp) {
                        $totalgradepoints = $totalgradepoints + $sgp->totgradepoints;
                        echo $totalcredithours = $totalcredithours + $sgp->totcredithours;
                    }
                    if ($semgpa) {
                        $CGPA = $totalgradepoints / $totalcredithours;
                        echo 'CGPA' . $CGPA = round($CGPA, 2);
                        $academiclevel = $DB->get_field('local_program', 'programlevel', array('id' => ($stucur->programid)));
                        $semcgpa = $DB->get_field('local_cobalt_gpasettings', 'cgpa', array('schoolid' => ($stucur->schoolid), 'sub_entityid' => ($academiclevel)));
                        $entitylevel = $DB->get_field('local_cobalt_entitylevels', 'level', array('schoolid' => ($stucur->schoolid), 'entityid' => 1));
                        if ($entitylevel == "PL") {
                            echo "  progid  " . $stucur->programid;
                            $mincredithours = $DB->get_field('local_level_settings', 'mincredithours', array('levelid' => ($stucur->programid), 'schoolid' => ($stucur->schoolid), 'subentityid' => $academiclevel));
                            echo " ttttt  " . $mincredithours;
                        } else {
                            echo $mincredithours = $DB->get_field('local_level_settings', 'mincredithours', array('levelid' => ($stucur->curriculumid), 'schoolid' => ($stucur->schoolid), 'subentityid' => $academiclevel));
                        }

                        if (($CGPA < $semcgpa) OR ( $totalcredithours < $mincredithours)) {
                            echo "Not Graduated";
                        } else {
                            $sql = "SELECT * from {local_graduation} where userid={$semuserid} and programid={$stucur->programid} and curriculumid={$stucur->curriculumid} ";
                            $record_exists_gpa = $DB->get_record_sql($sql);
                            if (!$record_exists_gpa) {
                                $grad = new stdClass();
                                $grad->userid = $semuserid;
                                $grad->schoolid = $stucur->schoolid;
                                $grad->programid = $stucur->programid;
                                $grad->curriculumid = $stucur->curriculumid;
                                $grad->totalcredithours = $totalcredithours;
                                $grad->finalcgpa = $CGPA;
                                $grad->year = date("Y");
                                $grad->timecreated = time();
                                $DB->insert_record('local_graduation', $grad);
                                echo "Graduated";
                            }
                        }
                    }
                }
            }
        }
    }
}

/**
 * @method user_semesters_grad
 * @todo To get user semester information  
 * @param int $sid User ID    
 * @return array of object(student semester information)
 */
function user_semesters_grad($sid) {
    global $DB, $USER;
    $today = time();
    $edate = strtotime('+1 month', $today);
    $out = array();
    $sql = "SELECT ls.id,ls.fullname
                    FROM {local_user_semester} AS us
                    JOIN {local_semester} AS ls
                    ON us.semesterid=ls.id where ls.visible=1 and registrarapproval=1 and us.userid={$sid} group by ls.id";
    $semesterlists = $DB->get_records_sql($sql);
//        foreach ($semesterlists as $semesterlist) {
//           $out[$semesterlist->id] = $semesterlist->fullname;
//        }
    return $semesterlists;
}

/**
 * @method student_clclasses_grad
 * @todo To get perticular student class information used in grading  
 * @param int $semid Semester ID
 * @param int $semuserid Student ID   
 * @return array of object(student class information)
 */
function student_clclasses_grad($semid, $semuserid) {
    global $CFG, $DB, $USER;
    $sql = "SELECT * FROM {local_user_clclasses} 
              WHERE userid={$semuserid} 
              AND semesterid={$semid}
              AND registrarapproval=1 ";

    $grds = $DB->get_records_sql($sql);
    return $grds;
}

/**
 * @method get_cobalt_course_grad
 * @todo To get cobalt course name of a class 
 * @param int $classid Class ID  
 * @return string , coursename
 */
function get_cobalt_course_grad($classid) {
    global $CFG, $DB;
    $sql = "SELECT lcc.fullname FROM {local_clclasses} AS lc,{local_cobaltcourses} AS lcc
	      WHERE lc.cobaltcourseid=lcc.id AND lc.id={$classid}";
    $courses = $DB->get_records_sql($sql);
    foreach ($courses as $cou) {
        $coursename = $cou->fullname;
    }
    return $coursename;
}

/**
 * @method total_grade_points_grad
 * @todo To calculate total grade points of graduation
 * @param int $gardepoint
 * @param int $classid Class ID 
 * @return int tota1 grade points.
 */
function total_grade_points_grad($gradepoint, $classid) {
    global $CFG, $DB, $USER;
    $courseid = $DB->get_field('local_clclasses', 'cobaltcourseid', array('id' => $classid));
    $credithours = $DB->get_field('local_cobaltcourses', 'credithours', array('id' => $courseid));
    $total = $gradepoint * $credithours;
    return $total;
}
