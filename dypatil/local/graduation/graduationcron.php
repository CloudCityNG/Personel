<?php

require_once(dirname(__FILE__) . '/../../config.php');
global $CFG, $DB, $USER;
echo $today = time();

$sql = 'SELECT * from {local_semester}';

$semesters = $DB->get_records_sql($sql);
//print_object($semesters);
foreach ($semesters as $sem) {

    if ($sem->startdate <= $today AND $sem->enddate >= $today) {
        echo '</br> Semname : ' . $sem->fullname;
        $semid = $sem->id;
        $usql = "SELECT * from {local_user_semester} usem
                      JOIN {user} u on u.id=usem.userid
                      where u.deleted<>1 and semesterid ={$semid} ORDER BY userid";

        $sem_users = $DB->get_records_sql($usql);
       // print_object($sem_users);

        foreach ($sem_users as $semuser) {

            echo '</br> UserID : ' . $semuser->userid;
            $semuserid = $semuser->userid;
            $stucursql = "SELECT * from {local_userdata} where userid={$semuserid}";
            $stucur = $DB->get_records_sql($stucursql);
            foreach ($stucur as $stucur) {
                echo $sqlgpa = "SELECT * from {local_user_sem_details} where userid={$semuserid} and programid={$stucur->programid}";

                $semgpa = $DB->get_records_sql($sqlgpa);

               //  print_object($semgpa);
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