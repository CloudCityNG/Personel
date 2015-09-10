<?php

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->dirroot . '/local/gradesubmission/lib.php');
global $CFG, $DB, $USER;
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