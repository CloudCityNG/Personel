<?php

defined('MOODLE_INTERNAL') or die;

class local_batches {

    var $batchid;

    function __construct($id) {
        $this->batchid = $id;
    }

    function local_batches_add_map($data) {
        global $DB, $CFG, $USER;
        $batchmappingtemp = new stdclass();
        $batchmappingtemp->schoolid = $data->schoolid;
        $batchmappingtemp->programid = $data->programid;
        $batchmappingtemp->curriculumid = $data->curriculumid;
        $batchmappingtemp->academicyear = $data->academicyear;
        $batchmappingtemp->timecreated = $data->timecreated;
        $batchmappingtemp->timemodified = $data->timemodified;
        $batchmappingtemp->usermodified = $USER->id;
        $batchmappingtemp->batchid = $data->batchid;
        return $DB->insert_record('local_batch_map', $batchmappingtemp);
    }

    function get_batches_list($schoolid = 0, $programid = 0, $curriculumid = 0) {
        global $DB, $CFG, $USER;

        $sql = "select  ba.id, ba.name  from {cohort} as ba
                   join {local_batch_map} as map on map.batchid=ba.id
                   where (map.schoolid=$schoolid or map.schoolid=0) AND (map.programid=$programid or map.programid=0)
                    and ba.visible=1";
        if ($curriculumid)
            $sql .= " AND curriculumid=$curriculumid";

        $batches = $DB->get_records_sql_menu($sql);
        $batcheslist = array(NULL => '---Select---') + $batches;

        return $batcheslist;
    }

    function get_specific_batchinformation($batchid) {
        global $DB, $CFG, $USER;
        $batchsql = "select map.*,c.name  from {local_batch_map} as map
                join {cohort} as c on c.id=map.batchid and c.id=$batchid";

        $batchinfo = $DB->get_record_sql($batchsql);
        return $batchinfo;
    }

    function get_active_curriculumlist($schoolid, $programid) {
        global $DB, $CFG, $USER;

        $sql = "select id, fullname from {local_curriculum}                    
             where schoolid=$schoolid AND programid=$programid and visible=1  ";
        $curriculum = $DB->get_records_sql_menu($sql);
        $curriculumlist = array(NULL => 'Select Curriculum') + $curriculum;

        return $curriculumlist;
    }

// end of function

    function delete() {
        global $DB, $CFG;
        if ($members = $DB->get_records('cohort_members', array('cohortid' => $this->batchid))) {
            
        }
        $DB->delete_records('local_batch_map', array('batchid' => $this->batchid));
    }

    function add_course($courseid) {
        global $DB, $CFG;
        $data = new stdClass();
        $data->batchid = $this->batchid;
        $data->courseid = $courseid;
        $data->timeadded = time();
        return $DB->insert_record('local_batch_courses', $data);
    }

    function remove_course($courseid) {
        global $DB, $CFG;
        $this->unenrol_course($courseid);
        $DB->delete_records('local_batch_courses', array('batchid' => $this->batchid, 'courseid' => $courseid));
    }

    function unenrol_course($courseid) {
        global $DB, $CFG;
        $sql = "SELECT ue.* FROM {user_enrolments} as ue 
		JOIN {enrol} as e ON e.id=ue.enrolid
		JOIN {course} as c ON c.id =e.courseid
		WHERE c.id = $courseid AND ue.userid IN (SELECT userid FROM {cohort_members} WHERE cohortid = $this->batchid)
		AND ue.userid NOT IN (SELECT u_id FROM {learning_user_trainingplan} lut, {learning_plan_training} lpt WHERE lpt.id = lut.lpt_id AND lpt.t_id = {$courseid})
		AND e.status = 0 AND ue.status = 0";
        $uenrol = $DB->get_records_sql($sql);
        if (!empty($uenrol)) {
            foreach ($uenrol as $ue) {
                $instance = $DB->get_record('enrol', array('id' => $ue->enrolid), '*', MUST_EXIST);
                $plugin = enrol_get_plugin($instance->enrol);
                $plugin->unenrol_user($instance, $ue->userid);
            }
        }
        return true;
    }

    function assign_existing_userto_batches($data) {
        global $CFG, $USER, $DB, $OUTPUT;
        $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $data->id));
        $batchprogramlevel = $DB->get_record('local_program', array('id' => $batchmapinfo->programid));

        if (isset($data->moveto)) {
            if (isset($data->user_id)) {
                foreach ($data->user_id as $userid) {
                    $this->inner_functionality_assignexistinguser_tobatch($userid,$data->id);                 
                 
                } // end of foreach
            }// end od second if 

            redirect($returnurl);
        }
    }// end of function
    
    
    
    function inner_functionality_assignexistinguser_tobatch($userid, $batchid){
        global $DB,$CFG, $USER, $OUTPUT;
           $batchmapinfo = $DB->get_record('local_batch_map', array('batchid' => $batchid));
           $batchprogramlevel = $DB->get_record('local_program', array('id' => $batchmapinfo->programid));
        
                            $userdatainfo = $DB->get_records('local_userdata', array('userid' => $userid));
                    foreach($userdatainfo as $record)
                        $userdata= $record;
                        
                    $userprogram = $DB->get_record('local_program', array('id' => $userdata->programid));
                    $length =sizeof($userdatainfo);           

                    //------- comparing program level exists batch  and new batch--------------
                    //-------and also checking  already student exists in two batches not to assign more than that
                    if (($userprogram->programlevel < $batchprogramlevel->programlevel)  || ($length<2)) { 
                        $userdatatemp = $userdata;
                        $userdatatemp->schoolid = $batchmapinfo->schoolid;
                        $userdatatemp->programid = $batchmapinfo->programid;
                        $userdatatemp->curriculumid = $batchmapinfo->curriculumid;
                        $userdatatemp->batchid = $batchmapinfo->batchid;
                        $userdatatemp->serviceid= $batchprogramlevel->shortname.$userid;

                        // before  inserting checking already assigned to same batch or not
                        if (!$DB->record_exists('local_userdata', array('schoolid'=>$batchmapinfo->schoolid ,'programid'=>$batchmapinfo->programid,'userid'=>$userid ))) {

                            $insertedid = $DB->insert_record('local_userdata', $userdatatemp);
                            cohort_add_member($batchid, $userid);
                            if ($insertedid)
                                echo $OUTPUT->notification(get_string('success_assign_student', 'local_batches'), 'notifysuccess');
                        } else
                            echo $OUTPUT->notification(get_string('cannotassign', 'local_batches'), 'notifyproblem');
                    } else {
                        // need show error  
                        echo $OUTPUT->notification(get_string('cannotassign', 'local_batches'), 'notifyproblem');
                    }
        
        
        
        
        
        
        
    }// end of function
    
    
    function assign_existing_userto_batches_from_assignuser_interface($userlist, $batchid) {
        global $CFG, $USER, $DB, $OUTPUT;
        
       
            if (!empty($userlist)) {
                foreach ($userlist as $user) {
                    $this->inner_functionality_assignexistinguser_tobatch($user->id,$batchid);                 
                 
                } // end of foreach
            }// end od second if 



    }// end of function
    
    
    
    
    
}// end of classes
?>  