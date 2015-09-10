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
 * Cohort UI related functions and classes.
 *
 * @package    core_cohort
 * @copyright  2012 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cohort/lib.php');
require_once($CFG->dirroot . '/user/selector/lib.php');


/**
 * Cohort assignment candidates
 */
class batch_candidate_selector extends user_selector_base {
    protected $cohortid;
    protected $costid;

    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
       // $this->costid = $options['costid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB,$USER;

        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $batchmapinfo=$DB->get_record('local_batch_map',array('batchid' => $this->cohortid ));
      
        
        $params['cohortid'] = $this->cohortid;
        $params['cohortid1'] = $this->cohortid;
        

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

       /* $sql = " FROM {local_userdata} lu
            JOIN {user} u ON u.id = lu.userid
            LEFT JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
            WHERE cm.id IS NULL "; */
        
      /*  $sql = " FROM {local_userdata} AS udata 
        JOIN {user} AS u ON u.id = udata.userid   
        AND u.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = :cohortid ) 
        AND udata.schoolid = :schoolid AND udata.programid= :programid AND udata.curriculumid= :curriculumid AND udata.batchid IS NULL     
        AND u.deleted <> 1 AND u.confirmed = 1 AND u.id <> 1
        UNION        
        $fields FROM {local_userdata} AS udata 
        JOIN {user} AS u ON u.id = udata.userid   
        AND u.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = :cohortid1 ) 
        AND udata.schoolid= :schoolid1 AND udata.programid != :programid1
        AND u.deleted <> 1 AND u.confirmed = 1 AND u.id <> 1 ";*/
        
        $params['schoolid'] = $batchmapinfo->schoolid;
        $params['programid']= $batchmapinfo->programid;
        $params['schoolid1'] = $batchmapinfo->schoolid;
        $params['programid1']= $batchmapinfo->programid;
        $params['curriculumid']= $batchmapinfo->curriculumid; 
         $params['guestid1']=  $params['guestid']; 
        
            if(is_siteadmin()){
            // $sql .=" AND lu.costcenterid = $this->costid";
            }else{
            // $costcenterid = $DB->get_field('local_costcenter_permissions','costcenterid',array('userid'=>$USER->id));
           //  $sql .= " AND lu.costcenterid = $costcenterid";
            }
         //    $sql .= "  AND $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

        if (!$this->is_validating()) {
           $sql =$this->get_available_userquery($countfields,$wherecondition,$params,$order);

            $potentialmemberscount = $DB->count_records_sql($sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $sql =$this->get_available_userquery($fields,$wherecondition,$params,$order);
       // $availableusers = $DB->get_records_sql($sql , array_merge($params, $sortparams));
        
        // making duplication of parameter, if they dynmamic value is added while adding student
        foreach($params as $key=>$value){
           $ss= strpos($key,'val');            
           if( $ss !== false){              
              $valindex= explode('val',$key);
                if(isset($valindex[1]) && isset($params['val'.$valindex[1]]) ){              
                 $params['val'.$valindex[1].'1']=$params['val'.$valindex[1]];              
            }             
           }         
        }      

        $availableusers = $DB->get_records_sql($sql, $params); 
           
        if (empty($availableusers)) {
            return array();
        }
        
        if ($search) {
            $groupname = get_string('potusersmatching', 'cohort', $search);
        } else {
            $groupname = get_string('potusers', 'cohort');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
      //  $options['costid'] = $this->costid;
        $options['file'] = 'local/batches/assignuserlib.php';
        return $options;
    }
    
    
    protected function get_available_userquery( $fields, $wherecondition ,$params, $order=''){
    global $DB, $CFG, $USER;
 
    $wherecondition1=str_replace("guestid","guestid1",$wherecondition);
    // making duplication of parameter, if they dynmamic value is added while adding student
          foreach($params as $key=>$value){
            $ss= strpos($key,'val');            
            if( $ss !== false){              
              $valindex= explode('val',$key);
              if(isset($valindex[1]))
              $wherecondition1=str_replace('val'.$valindex[1],'val'.$valindex[1].'1',$wherecondition1);
            }
          } 
          
    $sql = "$fields FROM {local_userdata} AS udata 
        JOIN {user} AS u ON u.id = udata.userid   
        AND u.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = :cohortid ) 
        AND udata.schoolid = :schoolid AND udata.programid= :programid AND udata.curriculumid= :curriculumid AND udata.batchid IS NULL     
        AND u.deleted <> 1 AND u.confirmed = 1 AND u.id <> 1  AND $wherecondition 
        UNION        
        $fields FROM {local_userdata} AS udata 
        JOIN {user} AS u ON u.id = udata.userid   
        AND u.id NOT IN (select userid FROM {cohort_members} WHERE cohortid = :cohortid1 ) 
        AND udata.schoolid= :schoolid1 AND udata.programid != :programid1
       AND u.deleted <> 1 AND u.confirmed = 1 AND u.id <> 1  AND $wherecondition1 ";      

     return $sql;         
    
    } // end of function
    
}


/**
 * Cohort assignment candidates
 */
class batch_existing_selector extends user_selector_base {
    protected $cohortid;

    public function __construct($name, $options) {
        $this->cohortid = $options['cohortid'];
        parent::__construct($name, $options);
    }

    /**
     * Candidate users
     * @param string $search
     * @return array
     */
    public function find_users($search) {
        global $DB;
        
        // By default wherecondition retrieves all users except the deleted, not confirmed and guest.
        list($wherecondition, $params) = $this->search_sql($search, 'u');
        $params['cohortid'] = $this->cohortid;

        $fields      = 'SELECT ' . $this->required_fields_sql('u');
        $countfields = 'SELECT COUNT(1)';

        $sql = " FROM {user} u
                 JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = :cohortid)
                WHERE $wherecondition";

        list($sort, $sortparams) = users_order_by_sql('u', $search, $this->accesscontext);
        $order = ' ORDER BY ' . $sort;

   
        
        if (!$this->is_validating()) {
            $potentialmemberscount = $DB->count_records_sql($countfields . $sql, $params);
            if ($potentialmemberscount > $this->maxusersperpage) {
                return $this->too_many_results($search, $potentialmemberscount);
            }
        }

        $availableusers = $DB->get_records_sql($fields . $sql . $order, array_merge($params, $sortparams));

        if (empty($availableusers)) {
            return array();
        }


        if ($search) {
            $groupname = get_string('currentusersmatching', 'cohort', $search);
        } else {
            $groupname = get_string('currentusers', 'cohort');
        }

        return array($groupname => $availableusers);
    }

    protected function get_options() {
        $options = parent::get_options();
        $options['cohortid'] = $this->cohortid;
        $options['file'] = 'local/batches/assignuserlib.php';
        return $options;
    }
}

