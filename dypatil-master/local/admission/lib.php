<?php

require_once($CFG->dirroot . '/local/lib.php');
/*
 * Admission class contains library functions
 */

class cobalt_admission {

    //Declaring static object variable
    private static $admission;
    //Declaring static array variables
    public $student = array('---Select---',
        'New Student',
        'Previous Student');
    //indicates types of applicant
    public $type = array('---Select---',
        'New Applicant',
        'Transfer Applicant'
    );
    //types of program
    public $pgm = array('---Select---',
        'Intermediate',
        'Advanced'
    );
    //Student level
    public $level = array('---Select---', 'Local Student', 'International Student', 'Mature Student');
    public $cur = array('---Select---');
    public $ptype = array('---Select---',
        'Undergraduate',
        'Graduate',
        'Postgraduate');

    //Declaring constructor as private for singleton
    private function __construct() {
        
    }

    /**
     * We are using singleton for this class 
     * @method get_instance
     * @todo get object for cobalt_admission class
     * @return object of this class
     */

    public static function get_instance() {
        if (!self :: $admission) {
            self :: $admission = new cobalt_admission();
        }
        return self :: $admission;
    }

    /**
     * @method admission_tabs
     * @todo To render tab tree
     * @param string $currenttab For active tab
     * @return string renders tabs
     */

    public function admission_tabs($currenttab) {
        global $OUTPUT, $DB;
        $toprow = array();
        $toprow[] = new tabobject('undergraduate', new moodle_url('/local/admission/index.php'), get_string('undergraduate', 'local_programs'));
        $toprow[] = new tabobject('graduate', new moodle_url('/local/admission/graduate.php'), get_string('graduate', 'local_programs'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    public function get_pgmlist($pgmtype) {
        global $CFG, $DB;
        $sql = "SELECT p.*,s.fullname as schoolname 
	        FROM {local_school} AS s INNER JOIN {local_program} AS p ON s.id=p.schoolid 
	        WHERE p.type={$pgmtype} AND s.visible=1 AND p.visible=1";
        return $sql;
    }

    public function get_clalender_pgm($pgmtype) {
        global $CFG, $DB;
        $today = date('Y-m-d');
        $sql = "SELECT ea.id,ea.schoolid,ea.programid,p.fullname,p.shortname,p.description,p.type,p.duration,
            FROM_UNIXTIME(ea.startdate,'%D %M, %Y') as startdate,
	        FROM_UNIXTIME(ea.enddate,'%D %M, %Y') as enddate,
            ea.enddate as enddates			
	        FROM {local_event_activities} ea,{local_program} p 
	        WHERE ea.programid=p.id AND ea.publish=1 AND p.visible=1 AND p.programlevel={$pgmtype} AND '{$today}' >= DATE(FROM_UNIXTIME(ea.startdate))";
        return $sql;
    }

    /**
     * @method cobalt_admission_curculum
     * @todo To get the curriculum select menu
     * @param int $schoolid select curriculum for particular school and program
     * @param int $programid
     * @return curriculum select menu with the id and fullname comination...
     */

    public function cobalt_admission_curculum($schoolid, $programid) {
        global $DB, $USER;
        $cur = array();
        $today = date('Y-m-d');
        $cur[0] = "---Select---";
        $sql = "SELECT id,fullname 
	      FROM
	      {local_curriculum} 
		  WHERE
		  schoolid={$schoolid} AND 
		  programid={$programid} AND 
		  visible=1 AND
		  '{$today}'  BETWEEN DATE(FROM_UNIXTIME(startdate)) and  DATE(FROM_UNIXTIME(enddate))";
        $currculums = $DB->get_records_sql($sql);
        foreach ($currculums as $curculum) {
            $cur[$curculum->id] = $curculum->fullname;
        }
        return $cur;
    }

    /**
     * @method get_prgms
     * @todo To create programs select menu
     */

    public function get_pgrms() {
        global $DB, $USER;
        $pgm = array();
        $pgm[] = "---Select---";
        $sql = "SELECT p.id,p.fullname 
	      FROM {local_school_permissions} sp,{local_program} p 
	      WHERE
		  sp.userid={$USER->id} and roleid=9 and sp.schoolid=p.schoolid and sp.value=1 and p.visible=1";
        $prgrms = $DB->get_records_sql($sql);
        foreach ($prgrms as $prgm) {
            $pgm[$prgm->id] = $prgm->fullname;
        }
        return $pgm;
    }

    /**
     * @method cobalt_admission_applicant
     * @todo To return the applicant query
     * @param int $typeid Applicant typeid
     * @param int $levelid Applicant levelid
     * @param int $schoolid Applicant schoolid
     * @param iny $programid Applicant programid
     * @return string return the query 
     */

    public function cobalt_admission_applicant($typeid, $levelid, $schoolid, $programid) {

        global $DB, $USER;
        $list = "SELECT a.* FROM {local_school_permissions} sp,{local_admission} a WHERE";
        $condition = "AND sp.schoolid=a.schoolid and sp.userid={$USER->id} AND status=0";
        if ($typeid > 0 && $levelid > 0 && $schoolid > 0 && $programid > 0) {
            $sql = "$list typeofapplication={$typeid} AND 
	      typeofprogram={$levelid} AND 
		  a.schoolid={$schoolid} AND 
		  a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid == 0 && $schoolid == 0 && $programid == 0) {
            $sql = "$list typeofapplication={$typeid} $condition";
        } elseif ($typeid == 0 && $levelid > 0 && $schoolid == 0 && $programid == 0) {
            $sql = "$list typeofprogram={$levelid} $condition";
        } elseif ($typeid == 0 && $levelid == 0 && $schoolid > 0 && $programid == 0) {
            echo "hi";
            $sql = "$list a.schoolid={$schoolid} $condition";
        } elseif ($typeid == 0 && $levelid == 0 && $schoolid == 0 && $programid > 0) {
            $sql = "$list a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid > 0 && $schoolid == 0 && $programid == 0) {
            $sql = "$list typeofapplication={$typeid} AND 
	      typeofprogram={$levelid} $condition";
        } elseif ($typeid == 0 && $levelid > 0 && $schoolid > 0 && $programid == 0) {
            $sql = "$list typeofprogram={$levelid} AND 
		  a.schoolid={$schoolid} $condition";
        } elseif ($typeid == 0 && $levelid == 0 && $schoolid > 0 && $programid > 0) {
            $sql = "$list a.schoolid={$schoolid} AND 
		  a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid == 0 && $schoolid == 0 && $programid > 0) {
            $sql = "$list typeofapplication={$typeid} AND
	      a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid > 0 && $schoolid > 0 && $programid == 0) {
            $sql = "$list typeofapplication={$typeid} AND 
	      typeofprogram={$levelid} AND 
		  a.schoolid={$schoolid} $condition";
        } elseif ($typeid == 0 && $levelid > 0 && $schoolid > 0 && $programid > 0) {
            $sql = "$list typeofprogram={$levelid} AND 
		  a.schoolid={$schoolid} AND
	      a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid == 0 && $schoolid > 0 && $programid > 0) {
            $sql = "$list typeofapplication={$typeid} AND 
	      a.schoolid={$schoolid} AND
	      a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid > 0 && $schoolid == 0 && $programid > 0) {
            $sql = "$list typeofapplication={$typeid} AND
	      typeofprogram={$levelid} AND
		  a.programid={$programid} $condition";
        } elseif ($typeid > 0 && $levelid == 0 && $schoolid > 0 && $programid == 0) {
            $sql = "$list typeofapplication={$typeid} AND
	      a.schoolid={$schoolid} $condition";
        } else {
            if (is_siteadmin()) {
                $sql = "SELECT * FROM {local_admission} WHERE status=0 ";
            } else {
                $sql = "SELECT a.* FROM {local_school_permissions} sp,{local_admission} a 
	      WHERE sp.schoolid=a.schoolid and sp.userid={$USER->id} AND a.status=0 AND sp.value=1 ";
            }
        }
        return $sql;
    }

    /**
     * @method cobalt_admission_conflict
     * @todo To check all the students belongs to the same admission type
     * @param array $userlist lsit of applicants
     * @return boolean Return true or false
     */

    public function cobalt_admission_conflict($userlist) {
        global $DB;
        $i = 0;
        $adtype = array();
        foreach ($userlist as $u) {
            $adtype[$i] = $DB->get_field('local_admission', 'typeofapplication', array('id' => $u));
            $i++;
        }
        $adtype = array_unique($adtype);
        $length = sizeof($adtype);
        if ($length == 1) {
            $result = 'TRUE';
        } else {
            $result = 'FALSE';
        }
        return $result;
    }

     /**
     * @method cobalt_admission_info
     * @todo process the appicants
     * @param int $id applcationid
     * @param int $curid curriculumid
     * @param string $username Username
     * @param string $password Password
     */

    public function cobalt_admission_info($id, $curid, $username, $password, $needuserid=false,$batchuploadfields=false) {

        global $DB, $USER;
        $applicant = $DB->get_record('local_admission', array('id' => $id));	
        $dbpwd = hash_internal_user_password($password);
        $record = new stdclass();
        $record->id = $id;
        $record->status = 1;
        $localadmission = $DB->update_record('local_admission', $record);
        //Inserting in user table
        if ($applicant->previousstudent == 1) {
	    $existsuser=$DB->get_record('user',array('email'=>$applicant->email));
	     if(!$existsuser->id){
            $user = new stdclass();
            $user->confirmed = 1;
            $user->mnethostid = 1;
            $user->username = strtolower( $username);
            $user->password = $dbpwd;
            $user->firstname = $applicant->firstname;
            $user->lastname = $applicant->lastname;
            $user->email = $applicant->email;
            $user->phone1 = $applicant->phone;
            $user->address = $applicant->address;
            $user->city = $applicant->city;
            $user->country = $applicant->currentcountry;

            $user->theme = $DB->get_field('local_school', 'theme', array('id' => $applicant->schoolid));

            //$userid = $DB->insert_record('user', $user);
            //
            ////Inserting in context table
            //$context = new stdClass();
            //$context->contextlevel = 30;
            //$context->instanceid = $userid;
            //$context->depth = 2;
            //$contextid = $DB->insert_record('context', $context);
            ////Updating context table
            //$updatecontext = new stdClass();
            //$updatecontext->id = $contextid;
            //$updatecontext->path = '/1/' . $contextid . '';
            //$updated = $DB->update_record('context', $updatecontext);
	    $userid =  user_create_user($user, false, false);
	    
	    
	    $contextid =$DB->get_field('context','id',array('contextlevel'=>30, 'instanceid'=>$userid));
            /* ---code to display blocks starts here--- */
            $blocks = array("academic_status", "events", "calendar_upcoming");
            foreach ($blocks as $key => $block) {
                $blockdata = new stdClass();
                $blockdata->blockname = $block;
                $blockdata->parentcontextid = $contextid;
                $blockdata->showinsubcontexts = 0;
                $blockdata->pagetypepattern = 'my-index';
                $blockdata->subpagepattern = 16;
                $blockdata->defaultregion = 'dashboard-one';
                $blockdata->defaultweight = $key;
                $blockinstanceid = $DB->insert_record('block_instances', $blockdata);
                $blockcontext = new stdClass();
                $blockcontext->contextlevel = 80;
                $blockcontext->instanceid = $blockinstanceid;
                $blockcontext->depth = 3;
                $bkcontext = $DB->insert_record('context', $blockcontext);
                $updatebkcontext = new stdClass();
                $updatebkcontext->id = $bkcontext;
                $updatebkcontext->path = '/1/' . $contextid . '/' . $bkcontext . '';
                $blockresult = $DB->update_record('context', $updatebkcontext);
            }
            /* ---code to display blocks ends here--- */

            //Inserting in role_assignments
            $role = new stdClass();
            $role->roleid = 5;
            $role->contextid = $contextid;
            $role->userid = $userid;
            $role->timemodified = time();
            $role->modifierid = $USER->id;
            $roleassign = $DB->insert_record('role_assignments', $role);
            $prefix = $DB->get_record('local_prefix_suffix', array('entityid' => 2, 'schoolid' => $applicant->schoolid, 'programid' => $applicant->programid));
            $random = random_string(5);
            $serviceid = $prefix->prefix . $userid . $prefix->suffix . $random;
	  }
        }
	
        if(isset($existsuser->id))
	$userid = $existsuser->id;
	else
	$userid = $userid;
	
	
        //Inserting in local_admission
        $local = new stdclass();
        $local->middlename = $applicant->middlename;
        $local->gender = $applicant->gender;
        $local->dob = $applicant->dob;
        $local->birthcountry = $applicant->birthcountry;
        $local->birthplace = $applicant->birthplace;
        $local->fathername = $applicant->fathername;
        $local->pob = $applicant->pob;
        $local->region = $applicant->region;
        $local->town = $applicant->town;
        $local->currenthno = $applicant->currenthno;
        $local->currentcountry = $applicant->currentcountry;
        $local->howlong = $applicant->howlong;
        $local->same = $applicant->same;
        $local->permanenthno = $applicant->permanenthno;
        $local->state = $applicant->state;
        $local->pincode = $applicant->pincode;
        $local->contactname = $applicant->contactname;
        $local->primaryschoolname = $applicant->primaryschoolname;
        $local->primaryyear = $applicant->primaryyear;
        $local->primaryscore = $applicant->primaryscore;
        $local->ugin = $applicant->ugin;
        $local->ugname = $applicant->ugname;
        $local->ugyear = $applicant->ugyear;
        $local->ugscore = $applicant->ugscore;
        $local->graduatein = $applicant->graduatein;
        $local->graduatename = $applicant->graduatename;
        $local->graduateyear = $applicant->graduateyear;
        $local->graduatescore = $applicant->graduatescore;
        $local->examname = $applicant->examname;
        $local->hallticketno = $applicant->hallticketno;
        $local->score = $applicant->score;
        $local->noofmonths = $applicant->noofmonths;
        $local->reason = $applicant->reason;
        $local->description = $applicant->description;
        $local->typeofprogram = $applicant->typeofprogram;
        $local->typeofapplication = $applicant->typeofapplication;
        $local->typeofstudent = $applicant->typeofstudent;
        $local->dateofapplication = $applicant->dateofapplication;
        $local->applicationid = $applicant->applicationid;
        $local->applicantid = $id;
        $local->status = 0;
        $local->usermodified = $USER->id;
        $local->timemodified = time();
        $local->timecreated = time();

        $local->userid = $userid;
        $local->primaryplace = $applicant->primaryplace;
        $local->ugplace = $applicant->ugplace;
        $local->graduateplace = $applicant->graduateplace;
        $local->previousstudent = $applicant->previousstudent;
	$local->otherphone= $applicant->otherphone;
	$local->fatheremail= $applicant->fatheremail;
	$local->category= $applicant->category;
	$local->caste= $applicant->caste;
	$local->mothername= $applicant->mothername;
        $localuser = $DB->insert_record('local_users', $local);

        if ($applicant->previousstudent == 2) {
            $userid = $DB->get_field('user', 'id', array('email' => $applicant->email));
            $result = $DB->get_record('local_userdata', array('userid' => $userid));
            if (empty($result) || !isset($result)) {
                $prefix = $DB->get_record('local_prefix_suffix', array('entityid' => 2, 'schoolid' => $applicant->schoolid, 'programid' => $applicant->programid));
                $random = random_string(5);
                $serviceid = $prefix->prefix . $userid . $prefix->suffix . $random;
            } else {
                $serviceid = $result->serviceid;
            }
        }
	
	if($applicant->previousstudent == 1){
	    $serviceid= $batchuploadfields->serviceid;
	}
	
        //Inserting in user details
        $data = new stdclass();
        $data->schoolid = $applicant->schoolid;
        $data->programid = $applicant->programid;

        $data->curriculumid = $curid;
        $data->userid = $userid;
        $data->usermodified = $USER->id;
        $data->timemodified = time();
        $data->timecreated = time();
        $data->serviceid = $serviceid;
        $data->applicationid = $applicant->applicationid;
        $data->applicantid = $id;
        $data->fundsbygovt = $applicant->fundsbygovt;
	$data->batchid=$batchuploadfields->batchid;

        $result = $DB->insert_record('local_userdata', $data);
	if($needuserid){
	    return array('serviceid'=>$serviceid ,'userid' =>$userid);
	}
	else
        return $serviceid;
    }

    /**
     * @method applicant_tabs
     * @todo Create tab tree for admissions
     * @param string $currenttab For active tabs
     * @return renders the tabs object
     */

    public function applicant_tabs($currenttab) {
        global $OUTPUT, $DB;
        $toprow = array();
        $toprow[] = new tabobject('viewapplicant', new moodle_url('/local/admission/viewapplicant.php'), get_string('viewapplicant', 'local_admission'));
        $toprow[] = new tabobject('' . $currenttab . '', new moodle_url('/local/admission/' . $currenttab . '.php'), get_string('' . $currenttab . '', 'local_admission'));
        $toprow[] = new tabobject('report', new moodle_url('/local/admission/admissionreport.php'), get_string('report', 'local_admission'));
        $toprow[] = new tabobject('uploadapplicant', new moodle_url('/local/admission/uploadapplicant.php'), get_string('uploadapplicant', 'local_admission'));
        $toprow[] = new tabobject('uploaduser', new moodle_url('/local/admission/uploaduser.php'), get_string('uploaduser', 'local_admission'));
        $toprow[] = new tabobject('upload', new moodle_url('/local/admission/upload.php'), get_string('upload', 'local_admission'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method report_tabs
     * @todo Create tab tree for reports
     * @param string $currenttab For active tabs
     * @return renders the tabs object
     */

    public function report_tabs($currenttab) {
        global $OUTPUT, $DB;
        $toprow = array();
        $toprow[] = new tabobject('viewapplicant', new moodle_url('/local/admission/viewapplicant.php'), get_string('viewapplicant', 'local_admission'));
        $toprow[] = new tabobject('transferapplicant', new moodle_url('/local/admission/transferapplicant.php'), get_string('transferapplicant', 'local_admission'));
        $toprow[] = new tabobject('report', new moodle_url('/local/admission/admissionreport.php'), get_string('report', 'local_admission'));
        $toprow[] = new tabobject('uploadapplicant', new moodle_url('/local/admission/uploadapplicant.php'), get_string('uploadapplicant', 'local_admission'));
        $toprow[] = new tabobject('uploaduser', new moodle_url('/local/admission/uploaduser.php'), get_string('uploaduser', 'local_admission'));
        $toprow[] = new tabobject('upload', new moodle_url('/local/admission/upload.php'), get_string('uploadadmission', 'local_admission'));
        echo $OUTPUT->tabtree($toprow, $currenttab);
    }

    /**
     * @method admission_report
     * @todo To get list of admissions for particular user
     * @return object list of applications
     */

    public function admission_report() {
        global $OUTPUT, $DB, $CFG, $USER;
        $sql = "SELECT a.* FROM {local_school_permissions} sp,{local_admission} a WHERE sp.userid={$USER->id} and sp.schoolid=a.schoolid ";
        $query = $DB->get_records_sql($sql);
        return $query;
    }

}

/**
 * @method generatePassword
 * @todo To create random generated password
 * @param int level $level Default value 4 - Password level
 * @param int length $length Default value 10 - Password length
 * @return string return the randomly generated password
 */

function generatePassword($level = 4, $length = 10) {
    $chars[4] = "23456789abcdefghijkmnopqrstuvwxyzABCDEFGHIJKLMNPQRSTUVWXYZ";
    $name = "STD";
    $spl = "@";
    $i = 1;
    $str = "" . $name . "" . $spl . "" . $i . "";
    while ($i <= $length) {
        $str .= $chars[$level][mt_rand(0, strlen($chars[$level]))];
        $i++;
    }

    return $str;
}

/**
 * @method generateusername
 * @todo To generate username  by  concatinating applicant firstname,id with @ symbol and randomly generated string
 * @param int id $id Application ID
 * @return string $username Randomly generated username
 */
function generateusername($id) {
    global $DB, $USER;
    $applicant = $DB->get_record('local_admission', array('id' => $id));
    $random = random_string(5);
    $user=preg_replace('/\\s/','',$applicant->firstname);
    $username = strtolower($user) . $id . '@' . $random;
    return $username;
}

function admission_type($value) {
    global $DB, $CFG;
    switch ($value) {
        case 1:
            $result = "New Applicant";
            break;
        case 2:
            $result = "Transfer Applicant ";
            break;
        case 3:
            $result = "Readmitted Applicant";
            break;
    }
    return $result;
}

function student_type($value) {
    global $DB, $CFG;
    switch ($value) {
        case 1:
            $result = "Local Student";
            break;
        case 2:
            $result = "International Student";
            break;
        case 3:
            $result = "Mature Student";
            break;
    }
    return $result;
}

function numeric_validation($data) {
    $i = 0;
    $strings = str_split($data);
    foreach ($strings as $string) {
        if (is_numeric($string)) {
            $i++;
        }
    }
    return $i;
}

function get_admin_pgrms() {
    global $DB, $USER;
    $pgm = array();
    $pgm[] = "---Select---";
    $sql = "SELECT id,fullname 
	      FROM {local_program}  
	      WHERE
		  visible=1";
    $prgrms = $DB->get_records_sql($sql);
    foreach ($prgrms as $prgm) {
        $pgm[$prgm->id] = $prgm->fullname;
    }
    return $pgm;
}

/**
 * @method tansferaccept
 *  @todo Process the transfered applicants
 *  @param int $id applicant ID
 *  @param int $curid Curriculum ID
 *  @param string $username Username
 *  @param string $password Password
 */
function tansferaccept($id, $curid, $username, $password) {
    global $DB, $USER, $CFG;
    $applicant = $DB->get_record('local_admission', array('id' => $id));

    $dbpwd = hash_internal_user_password($password);
    if ($applicant->previousstudent == 1) {
        // (1)
        $user = new stdclass();
        $user->confirmed = 1;
        $user->mnethostid = 1;
        $user->username = $username;
        $user->password = $dbpwd;
        $user->firstname = $applicant->firstname;
        $user->lastname = $applicant->lastname;
        $user->email = $applicant->email;
        $user->phone1 = $applicant->phone;
        $user->address = $applicant->state;
        $user->city = $applicant->city;
        $user->country = $applicant->pcountry;
        $userid = $DB->insert_record('user', $user);
        //(2)
        $context = new stdClass();
        $context->contextlevel = 30;
        $context->instanceid = $userid;
        $context->depth = 2;
        $contextid = $DB->insert_record('context', $context);
        //(3)
        $updatecontext = new stdClass();
        $updatecontext->id = $contextid;
        $updatecontext->path = '/1/' . $contextid . '';
        $updated = $DB->update_record('context', $updatecontext);
        //(4)
        $role = new stdClass();
        $role->roleid = 5;
        $role->contextid = $contextid;
        $role->userid = $userid;
        $role->timemodified = time();
        $role->modifierid = $USER->id;
        $roleassign = $DB->insert_record('role_assignments', $role);
        //(6)
        $prefix = $DB->get_record('local_prefix_suffix', array('entityid' => 2, 'schoolid' => $applicant->schoolid, 'programid' => $applicant->programid));
        $random = random_string(5);
        $serviceid = $prefix->prefix . $userid . $prefix->suffix . $random;
    }
    //(5)
    $local = new stdclass();
    $local->middlename = $applicant->middlename;
    $local->gender = $applicant->gender;
    $local->dob = $applicant->dob;
    $local->birthcountry = $applicant->birthcountry;
    $local->birthplace = $applicant->birthplace;
    $local->fathername = $applicant->fathername;
    $local->pob = $applicant->pob;
    $local->region = $applicant->region;
    $local->town = $applicant->town;
    $local->currenthno = $applicant->currenthno;
    $local->currentcountry = $applicant->currentcountry;
    $local->howlong = $applicant->howlong;
    $local->same = $applicant->same;
    $local->permanenthno = $applicant->permanenthno;
    $local->state = $applicant->state;
    $local->pincode = $applicant->pincode;
    $local->contactname = $applicant->contactname;
    $local->primaryschoolname = $applicant->primaryschoolname;
    $local->primaryyear = $applicant->primaryyear;
    $local->primaryscore = $applicant->primaryscore;
    $local->ugin = $applicant->ugin;
    $local->ugname = $applicant->ugname;
    $local->ugyear = $applicant->ugyear;
    $local->ugscore = $applicant->ugscore;
    $local->graduatein = $applicant->graduatein;
    $local->graduatename = $applicant->graduatename;
    $local->graduateyear = $applicant->graduateyear;
    $local->graduatescore = $applicant->graduatescore;
    $local->examname = $applicant->examname;
    $local->hallticketno = $applicant->hallticketno;
    $local->score = $applicant->score;
    $local->noofmonths = $applicant->noofmonths;
    $local->reason = $applicant->reason;
    $local->description = $applicant->description;
    $local->typeofprogram = $applicant->typeofprogram;
    $local->typeofapplication = $applicant->typeofapplication;
    $local->typeofstudent = $applicant->typeofstudent;
    $local->dateofapplication = $applicant->dateofapplication;
    $local->applicationid = $applicant->applicationid;
    $local->applicantid = $id;
    $local->status = 0;
    $local->usermodified = $USER->id;
    $local->timemodified = time();
    $local->timecreated = time();
    $local->userid = $userid;
    $local->primaryplace = $applicant->primaryplace;
    $local->ugplace = $applicant->ugplace;
    $local->graduateplace = $applicant->graduateplace;
    $local->previousstudent = $applicant->previousstudent;
    $localuser = $DB->insert_record('local_users', $local);

    if ($applicant->previousstudent == 2) {
        $userid = $DB->get_field('user', 'id', array('email' => $applicant->email));
        $result = $DB->get_record('local_userdata', array('userid' => $userid));
        if (empty($result) || !isset($result)) {
            $prefix = $DB->get_record('local_prefix_suffix', array('entityid' => 2, 'schoolid' => $applicant->schoolid, 'programid' => $applicant->programid));
            $random = random_string(5);
            $serviceid = $prefix->prefix . $userid . $prefix->suffix . $random;
        } else {
            $serviceid = $result->serviceid;
        }
    }
    $data = new stdclass();
    $data->schoolid = $applicant->schoolid;
    $data->programid = $applicant->programid;
    $data->curriculumid = $curid;
    $data->userid = $userid;
    $data->usermodified = $USER->id;
    $data->timemodified = time();
    $data->timecreated = time();
    $data->serviceid = $serviceid;
    $data->applicationid = $applicant->applicationid;
    $data->applicantid = $id;
    $data->fundsbygovt = $applicant->fundsbygovt;
    $result = $DB->insert_record('local_userdata', $data);
    return $serviceid;
    //(7)
}

/**
 * @method get_list_sems
 * @todo To create semester select tree
 * @param int $schoolid SchoolID
 * @return To create semester select menu
 */
function get_list_sems($schoolid) {

    global $CFG, $USER, $DB;
    $sem = array();
    $sem[0] = "---Select---";
    $sql = "SELECT ls.id,ls.fullname FROM {local_school_semester} as sm,{local_semester} as ls WHERE sm.schoolid={$schoolid}
	AND sm.semesterid=ls.id AND ls.visible=1";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $sems) {
        $sem[$sems->id] = $sems->fullname;
    }
    return $sem;
}

/**
 * @method get_courses_ad
 * @todo To get cobalt courses list
 * @param int $schoolid SchoolID
 * @param int $programid ProgramID
 * @param int $semid SemesterID
 * @return object Cobaltcourses object
 */
function get_courses_ad($schoolid, $programid, $semid) {
    global $CFG, $USER, $DB;
    $sql = "SELECT cc.id,cc.fullname,lc.id as classid FROM {local_clclasses} AS lc,{local_cobaltcourses} AS cc WHERE lc.schoolid={$schoolid} AND lc.programid={$programid} AND lc.semesterid={$semid} AND lc.cobaltcourseid=cc.id";
    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method get_score_grade
 * @param int $schoolid SchoolID
 * @param int $programid ProgramID
 * @param int $score Score
 * @return object return gradeletters object with score condition
 */
function get_score_grade($schoolid, $programid, $score) {
    global $CFG, $USER, $DB;
    $score = round($score);
    $sql = "SELECT * FROM {local_gradeletters} WHERE schoolid={$schoolid}
	 AND markfrom<={$score} AND markto>={$score} ";
    $query = $DB->get_records_sql($sql);
    return $query;
}

/**
 * @method transfer_get_cobalt_courses
 * @param int $cid CurrculumID
 * @return array Returns Courses array with id and fullname
 */
function transfer_get_cobalt_courses($cid) {
    global $CFG, $USER, $DB;
    $course = array();
    $course[0] = "---Select---";
    $sql = "SELECT distinct(lcc.id),lcc.fullname FROM {local_curriculum_plancourses} AS lcp,
	      {local_cobaltcourses} AS lcc 
          WHERE lcp.courseid=lcc.id AND lcp.curriculumid={$cid}";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $cou) {
        $course[$cou->id] = $cou->fullname;
    }
    return $course;
}

/**
 * @method transfer_score
 * @param array $course Courses
 * @param int $score Score
 * @param int $id Application ID
 * @return transfer the data to local grade table
 */
function transfer_score($course, $score, $id) {
    global $CFG, $USER, $DB;

    $user = $DB->get_record('local_users', array('applicantid' => $id));
    $users = $DB->get_record('local_userdata', array('userid' => $user->userid));


    for ($i = 0; $i < count($course); $i++) {
        $courseid = $course[$i];
        $grades = $score[$i];
        //
        $grade = new stdclass();
        $grade->userid = $user->userid;
        $grade->schoolid = $users->schoolid;
        $grade->programid = $users->programid;
        $grade->courseid = $courseid;
        $grade->coursetotal = $score[$i];
        $grade->percentage = $score[$i];
        $grad = get_score_grade($users->schoolid, $users->programid, $score[$i]);

        foreach ($grad as $gra) {
            $grade->gradeletter = $gra->letter;
            $grade->gradepoint = $gra->gradepoint;
        }
        $grade->timecreated = time();
        $grade->timemodified = time();
        $grade->usermodified = $USER->id;
        $DB->insert_record('local_user_classgrades', $grade);
        $DB->insert_record('local_user_clsgrade_hist', $grade);
        //
    }
}

/**
 * @method get_school_program
 * @todo Get programlist for particular user and programlevel
 * @param int $tid ProgramLevel
 * @return array Return array of program id and fullname
 */
function get_school_program($tid) {
    global $CFG, $DB, $USER;
    $school = array();
    $school[] = "---Select---";
    $sql = "SELECT p.id,p.fullname,s.fullname as schoolname FROM {local_school_permissions} sp,{local_school} s,{local_program} p WHERE sp.schoolid=s.id AND sp.userid={$USER->id} AND s.id=p.schoolid AND p.programlevel={$tid} AND p.visible=1";
    $programs = $DB->get_records_sql($sql);
    foreach ($programs as $program) {
        $school[$program->id] = $program->schoolname . '-' . $program->fullname;
    }
    return $school;
}

/**
 * @method get_user_cur_admission
 * @todo Get curriculumlist for particular program and school
 * @param itn $pid Program id
 * @return array Return array of curriculum id and fullname
 */
function get_user_cur_admission($pid) {
    global $CFG, $DB;
    $curculum = array();
    $curculum[] = "---Select---";
    $sid = $DB->get_field('local_program', 'schoolid', array('id' => $pid));
    $sql = "SELECT id,fullname FROM {local_curriculum} WHERE 
	      schoolid={$sid} AND programid={$pid} AND visible=1";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $cur) {
        $curculum[$cur->id] = $cur->fullname;
    }
    return $curculum;
}

/**
 * @method get_new_programs
 * @todo Get programlist for particular user and programtype
 * @param int $pgmtype Programtype
 * @return array Return array of program id and fullname
 */
function get_new_programs($pgmtype) {
    global $CFG, $DB;
    $today = date('Y-m-d');
    $program = array();
    $program[] = "---Select---";

    $sql = "SELECT ea.id,ea.schoolid,ea.programid,p.fullname,p.shortname,p.description,p.type,p.duration,
            FROM_UNIXTIME(ea.startdate,'%D %M, %Y') as startdate,
	        FROM_UNIXTIME(ea.enddate,'%D %M, %Y') as enddate,
            ea.enddate as enddates			
	        FROM {local_event_activities} ea,{local_program} p 
	        WHERE ea.programid=p.id AND ea.publish=1 AND p.visible=1 AND p.programlevel={$pgmtype} AND '{$today}' >= DATE(FROM_UNIXTIME(ea.startdate))";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $pgm) {
        $program[$pgm->programid] = $pgm->fullname;
    }
    return $program;
}


/**
 * @method get_new_schools
 * @todo Get newly(currently) admitted school information 
 * @param int $id Admission id
 * @return array Return array of school id and fullname
 */
function get_new_schools($id) {
    global $DB, $USER;
    $applicant = $DB->get_record('local_admission', array('id' => $id));
    $school = array();
    $school[$applicant->schoolid] = $DB->get_field('local_school', 'fullname', array('id' => $applicant->schoolid));
    $sql = "SELECT id,fullname FROM {local_school} WHERE visible=1";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $scho) {
        $school[$scho->id] = $scho->fullname;
    }
    return $school;
}

/**
 * @method get_new_program
 * @todo Get programlist for particular applicantid and schoolid
 * @param int $id ApplicantID
 * @param int $schoolid SchoolID
 * @return array Return array of program id and fullname
 */
function get_new_program($id, $schoolid) {        
    global $DB, $USER;
    $hierarchy = new hierarchy();
    $pgm = array();
    $applicant = $DB->get_record('local_admission', array('id' => $id));
    if ($schoolid == 0) {
        $pgm[$applicant->programid] = $DB->get_field('local_program', 'fullname', array('id' => $applicant->programid));
    } else {
        $pgm = $hierarchy->get_records_cobaltselect_menu('local_program', "visible=1 AND schoolid =$schoolid AND programlevel=$applicant->typeofprogram", null, '', 'id,fullname', '--Select--');        
    }
    return $pgm;
}

/**
 * @method get_new_curriculum
 * @todo Get curriculum list
 * @param int $id Applicant ID
 * @param int $sid School ID
 * @param int $pid Program ID
 * @return array Curriculum list with id and fullname
 */
function get_new_curculum($id, $sid, $pid) {
    global $DB, $USER;
    $hierarchy = new hierarchy();
    $curculum = array();
    $applicant = $DB->get_record('local_admission', array('id' => $id));
    if ($sid == 0) {
        $record = $DB->get_record('local_userdata', array('applicantid' => $id));
        $curculum[$record->curriculumid] = $DB->get_field('local_curriculum', 'fullname', array('id' => $record->curriculumid));
    } else {
        $curculum = $hierarchy->get_records_cobaltselect_menu('local_curriculum', "schoolid={$sid} AND programid={$pid} AND visible=1", null, '', 'id,fullname', '--Select--');       
    }
    return $curculum;
}

/**
 * @method get_cur
 * @todo Get curriculum list
 * @param int $id Applicant ID
 * @param int $cid Curriculum ID
 * @return array Curriculum list with id and fullname
 */
function get_cur($id, $cid) {
    global $DB, $USER;
    $record = $DB->get_record('local_admission', array('id' => $id));
    $curculum = array();

    $curculum[$cid] = $DB->get_field('local_curriculum', 'fullname', array('id' => $cid));
    $sql = "SELECT id,fullname FROM {local_curriculum} WHERE 
	      schoolid={$record->schoolid} AND programid={$record->programid} AND visible=1";
    $query = $DB->get_records_sql($sql);
    foreach ($query as $cur) {
        $curculum[$cur->id] = $cur->fullname;
    }
    return $curculum;
}



function local_admission_pluginfile($course, $cm, context $context, $filearea, $args, $forcedownload) {
    global $DB;

    if (strpos($filearea, 'applicantfile_') !== 0) {
        return false;
    }  
 
    $fieldid = substr($filearea, strlen('applicantfile_'));   
    array_shift($args); // ignore revision - designed to prevent caching problems only

    $relativepath = implode('/', $args);
    $fullpath = "/{$context->id}/local_admission/$filearea/$fieldid/$relativepath";
    $fs = get_file_storage();
    if (!($file = $fs->get_file_by_hash(sha1($fullpath))) || $file->is_directory()) {
        return false;
    }

    // Force download
    send_stored_file($file, 0, 0, true);
 }
  
    /**
    * @method local_admission_display_data
    * @todo to get uploaded filename and filepath
    * @param int $id Applicant ID  
    * @return array list of files
    */
    function  local_admission_display_data($applicant) {
        global $CFG, $DB, $USER, $COURSE;
 
        $fs = get_file_storage();
	$context =context_course::instance($COURSE->id);
	
      //  $dir = $fs->get_area_tree($context->id, 'local_admission', "applicantfile_$applicant", 0);
        $files = $fs->get_area_files($context->id, 'local_admission', "applicantfile_$applicant",
                                     $applicant,
                                     'timemodified',
                                     false);

        $data = array();
	
        foreach ($files as $file) {
            $path = '/' . $context->id . '/local_admission/applicantfile_' .$applicant. '/' .
                    $file->get_itemid() .
                    $file->get_filepath() .
                    $file->get_filename();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", $path, true);
            $filename = $file->get_filename();
            $data[] = html_writer::link($url, $filename);
        }
        return $data;
    }
