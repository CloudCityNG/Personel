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
 * Code for handling mass enrolment from a cvs file
 *
 *
 * @package local
 * @subpackage mass_enroll
 * @copyright 1999 onwards Martin Dougiamas and others {@link http://moodle.com}
 * @copyright 2012 onwards Patrick Pollet {@link mailto:pp@patrickpollet.net
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require($CFG->dirroot.'/cohort/lib.php');
require_once($CFG->dirroot.'/local/admission/upload_admissions_lib.php');
require_once ($CFG->dirroot.'/local/admission/lib.php');
require_once ($CFG->dirroot . '/group/lib.php');

defined('MOODLE_INTERNAL') || die();



/**
 * process the mass enrolment
 * @param csv_import_reader $cir  an import reader created by caller
 * @param Object $course  a course record from table mdl_course
 * @param Object $context  course context instance
 * @param Object $data    data from a moodleform 
 * @return string  log of operations 
 */
//function bulk_batch_enroll($cir,   $data) {
//    global $CFG,$DB,$USER;
//    require_once ($CFG->dirroot . '/group/lib.php');
//
//    $result = '';
//    
//    $roleid = $data->roleassign;
//    $useridfield = $data->firstcolumn;
//
//    $enrollablecount = 0;
//    $createdgroupscount = 0;
//    $createdgroupingscount = 0;
//    $createdgroups = '';
//    $createdgroupings = '';
//    
//    $plugin = enrol_get_plugin('manual');
//    // init csv import helper
//    $cir->init();
//    while ($fields = $cir->next()) {
//        $a = new StdClass();
//        if (empty ($fields))
//            continue;
//        
//        // 1rst column = id Moodle (idnumber,username or email)    
//        // get rid on eventual double quotes unfortunately not done by Moodle CSV importer 
//            $fields[0]= str_replace('"', '', trim($fields[0]));
//           $fields[1]= str_replace('"', '', trim($fields[1]));
//        if (!$batch = $DB->get_record('cohort', array('idnumber' => $fields[0]))) {
//            $result .= '<div class="alert alert-error">'.get_string('im:batch_unknown', 'local_batches', $fields[1] ). '</div>';
//            continue;
//        }
//        
//        if (!$user = $DB->get_record('user', array($useridfield => $fields[1]))) {
//            $result .= '<div class="alert alert-error">'.get_string('im:user_unknown', 'local_batches', $fields[1] ). '</div>';
//            continue;
//        }
//		$batch_costcenter = $DB->get_field('local_costcenter_batch','costcenterid',array('batchid'=>$batch->id));
//        if(!$DB->record_exists_sql("select id from {local_userdata} where costcenterid=$batch_costcenter AND userid=$user->id")){
//            $costcentername = $DB->get_field('local_costcenter','fullname',array('id'=>$batch_costcenter));
//            $cs_object = new stdClass();
//            $cs_object->csname = $costcentername;
//            $cs_object->user   = fullname($user);
//            $result .= '<div class="alert alert-error">'.get_string('im:user_notcostcenter', 'local_mass_enroll',$cs_object ). '</div>';
//            continue; 
//        }
//        //already enroled ?
//        if ($DB->record_exists('cohort_members',array('userid'=>$user->id, 'cohortid'=>$batch->id))) {
//            $result .= '<div class="alert alert-error">'.get_string('im:already_in', 'local_mass_enroll', fullname($user)). '</div>';
//
//        } else {
//               cohort_add_member($batch->id, $user->id);
//			   $coursefields = "SELECT c.* FROM {local_batch_courses} AS bc
//		    JOIN {course} AS c ON c.id = bc.courseid
//		    WHERE bc.batchid = {$batch->id} AND c.visible = 1";
//	$courses = $DB->get_records_sql($coursefields);
//	
//	$userfields = "SELECT u.* FROM {user} u
//	     JOIN {cohort_members} cm ON (cm.userid = u.id AND cm.cohortid = $batch->id)
//	    WHERE u.id <> 1 AND u.deleted = 0 AND u.confirmed = 1";
//	$users = $DB->get_records_sql($userfields);
//	
//	if(!empty($courses)){
//	    foreach($courses as $course){
//
//			$sql = "SELECT ue.* FROM {user_enrolments} as ue 
//                                              JOIN {enrol} as e ON e.id=ue.enrolid
//					      JOIN {course} as c ON c.id =e.courseid
//                                             WHERE c.id = $course->id AND ue.userid = $user->id
//                                             AND e.status = 0 AND ue.status = 0";
//			$enrolled = $DB->get_records_sql($sql);
//			if($enrolled){
//			    continue;
//			}
//			$manual = enrol_get_plugin('manual');
//			$studentrole = $DB->get_record('role', array('shortname'=>'student'));
//			$instance = $DB->get_record('enrol', array('courseid'=>$course->id, 'enrol'=>'manual'), '*', MUST_EXIST);
//			$manual->enrol_user($instance, $user->id, $studentrole->id,time());
//
//	    }
//	}
//                $result .= '<div class="alert alert-success">'.get_string('im:enrolled_ok', 'local_mass_enroll', fullname($user)).'</div>';
//            $enrollablecount++;
//        }
//    }
//    $result .= '<br />';
//
//    $result .= get_string('im:stats_i', 'local_mass_enroll', $enrollablecount) . "";
// 
//    return $result;
//}



/**
 * process the mass enrolment
 * @param csv_import_reader $cir  an import reader created by caller
 * @param Object $course  a course record from table mdl_course
 * @param Object $context  course context instance
 * @param Object $data    data from a moodleform 
 * @return string  log of operations 
 */
function bulk_batch_enroll_newstudents($cir, $data) {
    global $CFG,$DB,$USER; $optype='';

    $returnurl = new moodle_url('/local/batches/bulk_enroll.php');
    //$STD_FIELDS = array('firstname', 'middlename', 'lastname', 'gender', 'dob', 'birthcountry', 'birthplace', 'fathername', 'pob', 'region', 'town', 'current_home_no', 'current_country', 'phone', 'email', 'howlong', 'same', 'permenant_country', 'permenant_home_no', 'state', 'city', 'pincode', 'contactname', 'primary_school', 'primary_year', 'primary_score', 'primary_place', 'undergraduate_in', 'ugname', 'ug_year', 'ug_score', 'ug_place', 'graduate_in', 'graduate_name', 'graduate_year', 'graduate_score', 'graduate_place', 'examname', 'hallticketno', 'score', 'no_of_months', 'reason', 'description','typeofstudent', 'typeofapplication', 'previousstudent', 'serviceid', 'typeofprogram');
    $STD_FIELDS = array('firstname', 'middlename', 'lastname', 'fathername','mothername','gender', 'dob','caste','category', 'current_country', 'phone', 'email', 'otherphone','city', 'pincode',   'typeofapplication',  'serviceid', 'fatheremail','address');

    $PRF_FIELDS = array();
   
    $result = '';
    
    //$roleid = $data->roleassign;
    //$useridfield = $data->firstcolumn;

    $enrollablecount = 0; $skipped=0;
    $createdgroupscount = 0;
    $createdgroupingscount = 0;
    $createdgroups = '';
    $createdgroupings = '';
    
    
    $filecolumns = uu_validate_admission_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
    $upt = new uu_progress_tracker();
    $plugin = enrol_get_plugin('manual');
    // init csv import helper
    $cir->init();

    $linenum = 1;
    loop:
    while ($line = $cir->next()) {
    $result=''; $existsmail=0;
    
    $upt->flush();
    $linenum++;
    $admission = new stdClass();
    // add fields to admission object
    foreach ($line as $keynum => $value) {
        if (!isset($filecolumns[$keynum])) {
            // this should not happen
            continue;
        }
        $key = $filecolumns[$keynum];
	
        $admission->$key = $value;
    }
    


    $admission->linenum = $linenum;    
    
//    	 // ----------  getting curriculumid--------------------
//        $curriculum = trim($admission->curriculumname);
//        $curriculumid = $DB->get_field('local_curriculum', 'id', array('fullname' => $curriculum));
//	
//	 // ---------- getting batchid---------------
//        $batchname = trim($admission->batchname);
//
//        $batchid = $DB->get_field('cohort', 'id', array('name' => $batchname));

	if(empty($data->batchid)){
	  $result .= '<div class="alert alert-error">'.get_string('emptybatch','local_batches').'</div>';
                goto loop;
	}
        $batchid =$data->batchid;
        $batchmapinfo=$DB->get_record('local_batch_map',array('batchid'=>$batchid));
    
    
//     if (!empty($admission->previousstudent) && !empty($admission->serviceid) && $admission->previousstudent == 2) {
//
//        // schools validation
//       // $scname = trim($admission->schoolname);
//        $schoollist = $DB->get_record('local_school', array('fullname' => $data->schoolid));
//        if (empty($schoollist)) {	    
//	    $result .= '<div class="alert alert-error">'.get_string('invalidschool_ub', 'local_batches', $admission). '</div>';         
//            goto loop;
//        }
//        $scid = $schoollist->id;
//        // check schoolname
//        $hier = new hierarchy();
//        $schools = $hier->get_assignedschools();
//        if (is_siteadmin()) {
//            $schools = $hier->get_school_items();
//        }
//        $c = 0;
//        foreach ($schools as $scl) {
//            if ($scid == $scl->id) {
//                ++$c;
//                break;
//            }
//        }
//        if ($c == 0) {
//	    $result .= '<div class="alert alert-error">'.get_string('invalidschoolpermission_ub', 'local_batches', $admission). '</div>';                 
//            goto loop;
//        }
//
//        if (empty($admission->typeofprogram)) {
//	    $result .= '<div class="alert alert-error">'.get_string('emptytypeofprogram_ub', 'local_batches', $admission). '</div>';         
//          
//            goto loop;
//        }
//        // type of program validation
//        if ($admission->typeofprogram > 2 || $admission->typeofprogram < 1) {
//	    $result .= '<div class="alert alert-error">'.get_string('invalidtypeofprogram_ub', 'local_batches', $admission). '</div>';
//      
//            goto loop;
//        }
//        if (empty($admission->typeofapplication)) {
//	     $result .= '<div class="alert alert-error">'.get_string('emptytypeofapplication_ub', 'local_batches', $admission). '</div>';
//       
//            goto loop;
//        }
//        if ($admission->typeofapplication > 2 || $admission->typeofapplication < 1) {
//	      $result .= '<div class="alert alert-error">'.get_string('invalidtypeofapplication_ub', 'local_batches', $admission). '</div>';
//       
//            goto loop;
//        }
//	
//        //  program validation
////        $prg = trim($admission->programname);
////        $programlist = $DB->get_record('local_program', array('fullname' => $prg));
////        if (empty($programlist)) {
////	    $result .= '<div class="alert alert-error">'.get_string('invalidprogramname_ub', 'local_batches', $admission). '</div>';
////       
////            goto loop;
////        }
////        $prgid = $programlist->id;
////
////        $programs = $DB->get_records('local_program', array('id' => $prgid, 'schoolid' => $scid, 'programlevel' => $admission->typeofprogram));
////        if (empty($programs)) {
////	    $result .= '<div class="alert alert-error">'.get_string('invalidmapprogramname_ub', 'local_batches', $admission). '</div>';
////          //  echo '<h3 style="color:red;">Program "' . $admission->programname . '" is not under given School "' . $admission->schoolname . '" or not under given typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of excelsheet.</h3>';
////            goto loop;
////        }
//
//          
//        $scid= $data->schoolid;
//	$prgid=$data->program;
//        $time = date('m/d/Y');
//        $time = strtotime($time);
//        $openadms = $DB->get_record_sql('select * from {local_event_activities} where schoolid = ' . $scid . ' and programid = ' . $prgid . ' and publish = 1 and ((' . $time . ' BETWEEN startdate and enddate) or (' . $time . ' >= startdate and enddate = 0))  and eventtypeid = 1');
//        $serviceid = trim($admission->serviceid);
//        if (empty($openadms)) {
//             $result .='<h3 style="color:red;">Admissions are not opened for the applied program entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
//            goto loop;
//        }
//        $applcntid = $DB->get_field('local_userdata', 'applicantid', array('serviceid' => $serviceid, 'schoolid' => $scid));
//        if (empty($applcntid)) {
//            $result .= '<h3 style="color:red;">You have entered invalid serviceid "' . $admission->serviceid . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
//            goto loop;
//        }
//        $result = $DB->get_record('local_admission', array('id' => $applcntid));
//        $previous = $result;
//        $previous->dateofapplication = time();
//        $previous->typeofprogram = $admission->typeofprogram;
//        $previous->typeofapplication = $admission->typeofapplication;
//        $previous->program = $prgid;
//        $previous->status = 0;
//        $DB->insert_record('local_admission', $previous);
//        $admissionsnew++;
//        // print_object($previous);
//    } else {
	
		
	// ----------to checking unique email id---------------
	if($admission->email){
	   if($existsuser=$DB->get_record('user',array('email'=>$admission->email))){

	      $existsmail= 1;
	     //  if( $DB->record_exists('cohort_members',array('userid'=>$existsuser->id,'cohortid'=>$batchid)))
	     $result .= '<div class="alert alert-error">'.get_string('useremailidexists_ub', 'local_batches', $admission). '</div>';
       
            goto loop;	    
	   }    
	}
	// ----------to checking unique service id (Rollid)---------------
        if($admission->serviceid){
	   if($existsuser=$DB->get_record('local_userdata',array('serviceid'=>$admission->serviceid))){
	     $result .= '<div class="alert alert-error">'.get_string('existsserviceid', 'local_batches', $admission). '</div>';
       
            goto loop;	    
	    
	   }
	   
	    
	}
        
        //if ($admission->previousstudent != 1) {
        //     $result .= '<h3 style="color:red;">You have entered invalid previousstudent "' . $admission->previousstudent . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
        //    goto loop;
        //}
        if (!isset($admission->email)) {
            // prevent warnings below
            $admission->email = '';
        }

        // make sure we really have admissionname
        if (empty($admission->email)) {
            $admissionserrors++;
            continue;
        }
        $admission->email = trim($admission->email);
        $existingadmission = $DB->get_record_sql("select * from {local_admission} where email = '$admission->email' and (status=0 or status=1)");
        // add default values for remaining fields
        $formdefaults = array();
        foreach ($STD_FIELDS as $field) {
            if (isset($admission->$field)) {
                continue;
            }
            // all validation moved to form2
            if (isset($formdata->$field)) {
                // process templates
                $formdefaults[$field] = true;
            }
        }
        foreach ($PRF_FIELDS as $field) {
            if (isset($admission->$field)) {
                continue;
            }
            if (isset($formdata->$field)) {
                $formdefaults[$field] = true;
            }
        }
        // can we process with update or insert?
        $skip = false;
        if ($optype == 'UU_ADMISSION_ADDNEW') {
            if ($existingadmission) {
                $admissionsskipped++;
                $skip = true;
            }
        }
        if ($skip) {
            continue;
        }
        // save the new admission to the database
        // schools validation
        //$scname = trim($admission->schoolname);
        //$schoollist = $DB->get_record('local_school', array('fullname' => $scname));
        //if (empty($schoollist)) {
        //    $result .= '<h3 style="color:red;">Invalid school  "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of excelsheet.</h3>';
        //    goto loop;
        //}
       // $scid = $schoollist->id;
        // check schoolname
        $hier = new hierarchy();
        $schools = $hier->get_assignedschools();
        if (is_siteadmin()) {
            $schools = $hier->get_school_items();
        }
        $c = 0;
        foreach ($schools as $scl) {
            if ($scid == $scl->id) {
                ++$c;
                break;
            }
        }
        //if ($c == 0) {
        //     $result .='<h3 style="color:red;">Sorry you are not assigned to this school "' . $admission->schoolname . '" entered at line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        //    goto loop;
        //}
        //if (empty($admission->typeofprogram)) {
        //     $result .= '<h3 style="color:red;">Please enter value for "typeofprogram" field in line no. "' . $linenum . '" of uploaded excelsheet.</h3>';
        //    goto loop;
        //}
        if (empty($admission->typeofapplication)) {
             $result .= '<div class="alert alert-error">Please enter value for "typeofapplication" field in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        if ($admission->typeofapplication > 2 || $admission->typeofapplication < 1) {
             $result .= '<div class="alert alert-error">You have entered invalid typeofapplication "' . $admission->typeofapplication . '" at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        // type of program validation
        //if ($admission->typeofprogram > 2 || $admission->typeofprogram < 1) {
        //     $result .= '<h3 style="color:red;">You have entered invalid typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of uploaded excelsheet.';
        //    goto loop;
        //}
        //  program validation
        //$prg = trim($admission->programname);
        //$programlist = $DB->get_record('local_program', array('fullname' => $prg));
        //if (empty($programlist)) {
        //     $result .='<div class="alert alert-error">Invalid program name "' . $admission->programname . '" entered at line no. "' . $linenum . '" of excelsheet.</div>';
        //    goto loop;
        //}
        //$prgid = $programlist->id;

        if($batchmapinfo->programid>0)
        $prgid=$batchmapinfo->programid;
        else
	$prgid= $data->program;
        
        if($batchmapinfo->schoolid>0)
        $scid=$batchmapinfo->schoolid;
        else        
	$scid =$data->schoolid;
        
        if($batchmapinfo->curriculumid>0)
        $curriculumid=$batchmapinfo->curriculumid;
        else        
	$curriculumid =$data->curriculumid;
        
        //$programs = $DB->get_records('local_program', array('id' => $prgid, 'schoolid' => $scid, 'programlevel' => $admission->typeofprogram));
        //if (empty($programs)) {
        //     $result .='<div class="alert alert-error">Program "' . $admission->programname . '" is not under given School "' . $admission->schoolname . '" or not under given typeofprogram "' . $admission->typeofprogram . '" at line no. "' . $linenum . '" of excelsheet.</div>';
        //    goto loop;
        //}
        //$time = date('m/d/Y');
        //$time = strtotime($time);
        //$openadms = $DB->get_record_sql('select * from {local_event_activities} where schoolid = ' . $scid . ' and programid = ' . $prgid . ' and publish = 1 and ((' . $time . ' BETWEEN startdate and enddate) or (' . $time . ' >= startdate and enddate = 0))  and eventtypeid = 1');
        //if (empty($openadms)) {
        //     $result .= '<div class="alert alert-error">Admissions are not opened for the applied program entered at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        // type of student validation
        //if ($admission->typeofstudent > 3 || $admission->typeofstudent < 1) {
        //    $result .='<div class="alert alert-error">You have entered invalid typeofstudent "' . $admission->typeofstudent . '" at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        $year = time();
        $year_in_number = date('Y');
        if (empty($admission->dob)) {
            $result .= '<div class="alert alert-error">Please enter dob in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        $ex = explode('/', $admission->dob);        
        if((sizeof($ex))<2){
         $ex = explode('-', $admission->dob);
        }
        if((sizeof($ex))<2){
           $ex = explode('.', $admission->dob);
        }
        
        $howlong = $year_in_number - $ex[2];
        $dob = strtotime($ex[0].'/'.$ex[1].'/'.$ex[2]);

        $true = checkdate($ex[0],$ex[1],$ex[2]);
        if ($true == false) {
            $result .= '<div class="alert alert-error">Invalid  dob "' . $admission->dob . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </div>';
            goto loop;
        }
        
        if(empty($dob)){
              $result .= '<div class="alert alert-error">check  dob date format "' . $admission->dob . '" format entered at line no. "' . $linenum . '" of uploaded excelsheet. </div>';  
            
        }
                
        // check dob
        if ($dob > $year) {
             $result .='<div class="alert alert-error">dob "' . $admission->dob . '" should be less than present date at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        // check howlong
        //if ($howlong < $admission->howlong) {
        //     $result .= '<div class="alert alert-error"> howlong ' . $admission->howlong . '  should not be greater than your age at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //// check primary year
        //if ($admission->primary_year > $year_in_number) {
        //     $result .= '<div class="alert alert-error">primary_year "' . $admission->primary_year . '" should not br greater than present date at line no. "' . $linenum . '".</div>';
        //    goto loop;
        //}
        //if ($admission->primary_year < $ex[2]) {
        //     $result .= '<div class="alert alert-error">primary_year "' . $admission->primary_year . '" should not be less than dob at line no. "' . $linenum . '".</div>';
        //    goto loop;
        //}
        //if ($admission->typeofprogram == 2) {
        //    if ($admission->ug_year > $year_in_number) {
        //        $result .= '<div class="alert alert-error">ug_year  "' . $admission->ug_year . '" should not be greater than present date at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if ($admission->ug_year < $admission->primary_year) {
        //       $result .= '<div class="alert alert-error">ug_year "' . $admission->ug_year . '"  should not be less than primary year at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if ($admission->ug_year < $ex[2]) {
        //        $result .= '<div class="alert alert-error">ug_year "' . $admission->ug_year . '" should not be less than dob at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if ($admission->ug_year == $admission->primary_year) {
        //        $result .= '<div class="alert alert-error">ug_year "' . $admission->ug_year . '"  should not be less than primary year at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //}
        //if ($admission->same > 2 || $admission->same < 1) {
        //    $result .='<div class="alert alert-error">Number entered for field same "' . $admission->same . '" is invalid at line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //$value1 = numeric_validation($admission->pob);
        //if ($value1 == 0) {
        //     $result .= '<div class="alert alert-error">Enter valid value for field pob in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        // check phone number
        $phone_length = strlen($admission->phone);
        //if ($phone_length > 15 || $phone_length < 10) {
        //    echo '<div class="alert alert-error">Enter valid phone number in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->current_home_no)) {
        //    echo '<div class="alert alert-error">Please enter current_home_no in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        if (empty($admission->current_country)) {
            echo '<div class="alert alert-error">Please enter current_country in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        
        if(empty($admission->address)){
            echo '<div class="alert alert-error">Please enter address in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop; 
            
        }
        //if (empty($admission->fathername)) {
        //    echo '<div class="alert alert-error">Please enter fathername in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->pob)) {
        //    echo '<div class="alert alert-error">Please enter pob in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (!trim($admission->region)) {
        //    echo '<div class="alert alert-error">Please enter region in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->town)) {
        //    echo '<div class="alert alert-error">Please enter town in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->birthcountry)) {
        //    echo '<div class="alert alert-error">Please enter birthcountry in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        $check_mail = filter_var($admission->email, FILTER_VALIDATE_EMAIL);
        if ($check_mail == false) {
            echo '<div class="alert alert-error">Please enter valid email in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        $country = get_string_manager()->get_list_of_countries();
        //if (!empty($admission->birthcountry)) {
        //    if (!array_key_exists($admission->birthcountry, $country)) {
        //        echo '<div class="alert alert-error">Please enter valid code for birthcountry in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //}
        if (!empty($admission->current_country)) {
            if (!array_key_exists($admission->current_country, $country)) {
                echo '<div class="alert alert-error">Please enter valid code for current_country  in line  no. "' . $linenum . '" of uploaded excelsheet.</div>';
                goto loop;
            }
        }

        //if (empty($admission->same)) {
        //    echo '<div class="alert alert-error">Please enter value for "same" field in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        if (empty($admission->firstname)) {
            echo '<div class="alert alert-error">Please enter firstname in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        if (empty($admission->lastname)) {
            echo '<div class="alert alert-error">Please enter lastname in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }
        if (empty($admission->gender)) {
            echo '<div class="alert alert-error">Please enter gender in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            goto loop;
        }

        //if (empty($admission->birthplace)) {
        //    echo '<div class="alert alert-error">Please enter birthplace in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->primary_school)) {
        //    echo '<div class="alert alert-error">Please enter primary_school in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->primary_year)) {
        //    echo '<div class="alert alert-error">Please enter primary_year in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->primary_score)) {
        //    echo '<div class="alert alert-error">Please enter primary_score in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->primary_place)) {
        //    echo '<div class="alert alert-error">Please enter primary_place in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if (empty($admission->typeofstudent)) {
        //    echo '<div class="alert alert-error">Please enter typeofstudent in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //    goto loop;
        //}
        //if ($admission->typeofstudent == 2) {
        //    if (empty($admission->examname)) {
        //        echo '<div class="alert alert-error">Please enter examname in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->hallticketno)) {
        //        echo '<div class="alert alert-error">Please enter hallticketno in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->score)) {
        //        echo '<div class="alert alert-error">Please enter score in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //}
        //if ($admission->typeofstudent == 3) {
        //    if (empty($admission->no_of_months)) {
        //        echo '<div class="alert alert-error">Please enter no_of_months in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->reason)) {
        //        echo '<div class="alert alert-error">Please enter reason in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->description)) {
        //        echo '<div class="alert alert-error">Please enter description in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //}
        //if ($admission->typeofprogram == 2) {
        //    if (empty($admission->undergraduate_in)) {
        //        echo '<div class="alert alert-error">Please enter undergraduate_in in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->ugname)) {
        //        echo '<div class="alert alert-error">Please enter ugname in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->ug_year)) {
        //        echo '<div class="alert alert-error">Please enter ug_year in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->ug_score)) {
        //        echo '<div class="alert alert-error">Please enter ug_score in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //    if (empty($admission->ug_place)) {
        //        echo '<div class="alert alert-error">Please enter ug_place in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
        //        goto loop;
        //    }
        //}
      //  if ($admission->same == 2) {
            //if (empty($admission->permenant_country)) {
            //    echo '<div class="alert alert-error">Please enter permenant_country in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}
            //if (!empty($admission->permenant_country)) {
            //    if (!array_key_exists($admission->permenant_country, $country)) {
            //        echo '<div class="alert alert-error">Please enter valid code for permenant_country in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //        goto loop;
            //    }
            //}
            if (empty($admission->city)) {
                echo '<div class="alert alert-error">Please enter city in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
                goto loop;
            }
            //if (empty($admission->permenant_home_no)) {
            //    echo '<div class="alert alert-error">Please enter permenant_home_no in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}
            //if (empty($admission->state)) {
            //    echo '<div class="alert alert-error">Please enter state in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}
            //if (empty($admission->pincode)) {
            //    echo '<div class="alert alert-error">Please enter pincode in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}
            //$value2 = numeric_validation($admission->pincode);
            //if ($value2 == 0) {
            //    echo '<div class="alert alert-error">Enter valid value for field pincode in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}

            //if (empty($admission->contactname)) {
            //    echo '<div class="alert alert-error">Please enter contactname in line no. "' . $linenum . '" of uploaded excelsheet.</div>';
            //    goto loop;
            //}
       
	
        $data->firstname = $admission->firstname;
        $data->middlename = $admission->middlename;
        $data->lastname = $admission->lastname;
        $data->gender = $admission->gender;
        $data->dob = $dob;
        //$data->birthcountry = $admission->birthcountry;
        //$data->birthplace = $admission->birthplace;
        $data->fathername = $admission->fathername;
        //$data->pob = $admission->pob;
        $data->caste = $admission->caste;
        $data->category = $admission->category;
        $data->otherphone = $admission->otherphone;
        $data->fatheremail = $admission->fatheremail;
        //$data->town = $admission->town;
        //$data->howlong = $admission->howlong;
        //$data->currenthno = $admission->current_home_no;
        $data->currentcountry = $admission->current_country;
        $data->phone = $admission->phone;
        $data->email = $admission->email;
        $data->mothername = $admission->mothername;
       // $data->same = $admission->same;
	// permanent address same as temporary address or current address
        //if ($data->same == 1) {
        //    $data->pcountry = $admission->current_country;
        //    $data->permenanthno = $admission->current_home_no;
        //    $data->state = $admission->region;
        //    $data->city = $admission->town;
        //    $data->pincode = $admission->pob;
        //    $data->contactname = $admission->fathername;
        //}
        //if ($data->same == 2) {
        //    $data->pcountry = $admission->permenant_country;
        //    $data->permanenthno = $admission->permenant_home_no;
        //    $data->state = $admission->state;
            $data->city = $admission->city;
            $data->pincode = $admission->pincode;
        //    $data->contactname = $admission->contactname;
       // }
        //$data->primaryschoolname = $admission->primary_school;
        //$data->primaryyear = $admission->primary_year;
        //$data->primaryscore = $admission->primary_score;
        //$data->primaryplace = $admission->primary_place;
        //$data->typeofstudent = $admission->typeofstudent;
        //international student
        //if ($data->typeofstudent == 2) {
        //    $data->examname = $admission->examname;
        //    $data->hallticketno = $admission->hallticketno;
        //    $data->score = $admission->score;
        //} elseif ($data->typeofstudent == 3) {   //mature student
        //    $data->noofmonths = $admission->no_of_months;
        //    $data->reason = $admission->reason;
        //    $data->description = $admission->description;
        //}

        $data->programid = $prgid;
        $data->typeofapplication = 1;
        
        $data->status = 0;
      //  $data->typeofprogram = $admission->typeofprogram;
        //if ($data->typeofprogram == 2) { // graduate
        //    $data->ugin = $admission->undergraduate_in;
        //    $data->ugname = $admission->ugname;
        //    $data->ugyear = $admission->ug_year;
        //    $data->ugscore = $admission->ug_score;
        //    $data->ugplace = $admission->ug_place;
        //}
        $data->previousstudent = 1;
        $data->dateofapplication = time();
	$data->uploadfile=0;
        $data->address= $admission->address;
         
        $data->id = $DB->insert_record('local_admission', $data);
	$applicantid =  $data->id;
        $program = $DB->get_field('local_program', 'shortname', array('id' => $prgid));
        $random = random_string(5);
        $update = new Stdclass();
        $update->id = $data->id;
        $update->applicationid = substr($program,0,8).$data->id.$random;
        
        
        $applicationid = $DB->update_record('local_admission', $update);
        $data->id ++;
        $admissionsnew++;

        $admission->batchid= $batchid;
        $csvfields= $admission;
	if(isset($data->id)){	  
	//  $result =$data->id;
	
	 $response = local_batches_enroll_batches($applicantid,$curriculumid,$batchid, $csvfields );
	 }
         if($response){
            $enrollablecount++;
         }
         else
            $skipped++; 
         
   } // end of while

   
      $result .= get_string('numberofenrolled', 'local_batches', $enrollablecount) . "";
      $result .= '<div class="alert alert-error">'.get_string('numberofskippedusers','local_batches', $skipped).'</div>';
   
   return $result;
}// end of  function


function local_batches_enroll_batches($applicantid,$curriculumid,$batchid,$csvfields){
    global $CFG,$DB,$USER;
      
    $admission = cobalt_admission::get_instance();    
    $applicant = $DB->get_record('local_admission', array('id' => $applicantid));
    $username = $applicant->email;
    //$password = generatePassword();
    
    $password = 'Dypatil123$';
    $userinfo = $admission->cobalt_admission_info($applicantid, $curriculumid, $username, $password, true, $csvfields);

    $userid=$userinfo['userid'];
            //already enroled ?
        if ($DB->record_exists('cohort_members',array('userid'=>$userid, 'cohortid'=>$batchid))) {
            echo  '<div class="alert alert-error">'.get_string('im:already_in', 'local_mass_enroll', fullname($user)). '</div>';

        } else {
               cohort_add_member($batchid, $userid);
	}
        
    return true;
    
} //  end of function



function bulk_batch_enroll_existingstudents($cir, $data){
    
    
    global $CFG,$DB,$USER;
    require_once ($CFG->dirroot . '/group/lib.php');
    $returnurl = new moodle_url('/local/batches/bulk_enroll.php');
    $STD_FIELDS = array('userid', 'serviceid');

    $PRF_FIELDS = array();
   
    $result = '';
    
    $roleid = $data->roleassign;
    $useridfield = $data->firstcolumn;

    $enrollablecount = 0;
    $createdgroupscount = 0;
    $createdgroupingscount = 0;
    $createdgroups = '';
    $createdgroupings = '';
    
    $filecolumns = uu_validate_admission_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
    $upt = new uu_progress_tracker();
    $plugin = enrol_get_plugin('manual');
    // init csv import helper
    $cir->init();

    $linenum = 1;
    loop:
    while ($line = $cir->next()) {
    $result=''; $existsmail=0;
    
    $upt->flush();
    $linenum++;
    $existuser = new stdClass();
    // add fields to admission object
    foreach ($line as $keynum => $value) {
        if (!isset($filecolumns[$keynum])) {
            // this should not happen
            continue;
        }
        $key = $filecolumns[$keynum];
	
        $existuser->$key = $value;
    }
     $existuser->linenum = $linenum;
        $batchid= $data->batchid;
        

	if(empty($batchid)){	    
	    echo '<div class="alert alert-error">'.get_string('batchempty', 'local_batches'). '</div>';
            continue;	
	}
	
	if(empty($existuser->serviceid)){
	     echo '<div class="alert alert-error">'.get_string('provideserviceid', 'local_batches'). '</div>';
            continue;	 
	    
	}
	
	if(empty($existuser->userid)){
	     echo '<div class="alert alert-error">'.get_string('provideuserid', 'local_batches'). '</div>';
            continue;	 	    
	}
        
	if($existuser->userid){
	    if(!$DB->record_exists('local_userdata',array('userid'=>$existuser->userid))){
	    echo '<div class="alert alert-error">'.get_string('provideuserid', 'local_batches'). '</div>';
            continue;
		
	    }
	    
	    
	}
	
	
            
        if($DB->record_exists('cohort_members',array('userid'=>$existuser->userid, 'cohortid'=>$batchid))) {
        echo '<div class="alert alert-error">'.get_string('im:already_in', 'local_mass_enroll', fullname($user)). '</div>';

        } else {
               cohort_add_member($batchid, $existuser->userid);
	}    
    
    }// end of while   
} // end of function


function local_batches_upload_preview($cir,$previewrows=10){
     global $DB,$CFG,$USER;
    // preview table data    
     $returnurl = new moodle_url('/local/batches/bulk_enroll.php');
    //$STD_FIELDS = array('firstname', 'middlename', 'lastname', 'gender', 'dob', 'birthcountry', 'birthplace', 'fathername', 'pob', 'region', 'town', 'current_home_no', 'current_country', 'phone', 'email', 'howlong', 'same', 'permenant_country', 'permenant_home_no', 'state', 'city', 'pincode', 'contactname', 'primary_school', 'primary_year', 'primary_score', 'primary_place', 'undergraduate_in', 'ugname', 'ug_year', 'ug_score', 'ug_place', 'graduate_in', 'graduate_name', 'graduate_year', 'graduate_score', 'graduate_place', 'examname', 'hallticketno', 'score', 'no_of_months', 'reason', 'description','typeofstudent', 'typeofapplication', 'previousstudent', 'serviceid', 'typeofprogram');
    $STD_FIELDS = array('firstname', 'middlename', 'lastname', 'gender', 'dob','region','category', 'current_country', 'phone', 'email', 'otherphone','city', 'pincode',   'typeofapplication',  'serviceid', 'fatheremail','address');

    $PRF_FIELDS = array();
   
    $result = '';    

    $enrollablecount = 0;
    $createdgroupscount = 0;
    $createdgroupingscount = 0;
    $createdgroups = '';
    $createdgroupings = '';

    
    $filecolumns = uu_validate_admission_upload_columns($cir, $STD_FIELDS, $PRF_FIELDS, $returnurl);
    
    
    $data = array();
    $cir->init();
    $linenum = 1; //column header is first line
    $noerror = true; // Keep status of any error.
    while ($linenum <= $previewrows and $fields = $cir->next()) {
	$linenum++;
	$rowcols = array();
	$rowcols['line'] = $linenum;
	foreach($fields as $key => $field) {
	    $rowcols[$filecolumns[$key]] = s(trim($field));
	}
	//$rowcols['status'] = array();
	//   
	//if (isset($rowcols['username'])) {
	//    $stdusername = clean_param($rowcols['username'], PARAM_USERNAME);
	//    if ($rowcols['username'] !== $stdusername) {
	//	$rowcols['status'][] = get_string('invalidusernameupload');
	//    }
	//    if ($userid = $DB->get_field('user', 'id', array('username'=>$stdusername, 'mnethostid'=>$CFG->mnet_localhost_id))) {
	//	$rowcols['username'] = html_writer::link(new moodle_url('/user/profile.php', array('id'=>$userid)), $rowcols['username']);
	//    }
	//} else {
	//  //  $rowcols['status'][] = get_string('missingusername');
	//}
    
	if (isset($rowcols['email'])) {
	    if (!validate_email($rowcols['email'])) {
		//$rowcols['status'][] = get_string('invalidemail');
	    }
	    if ($DB->record_exists('user', array('email'=>$rowcols['email']))) {
		//$rowcols['status'][] = $stremailduplicate;
	    }
	}
    
	if (isset($rowcols['city'])) {
	    $rowcols['city'] = $rowcols['city'];
	}
	//// Check if rowcols have custom profile field with correct data and update error state.
	//$noerror = uu_check_custom_profile_data($rowcols) && $noerror;
	//$rowcols['status'] = implode('<br />', $rowcols['status']);
	$data[] = $rowcols;
    }
    if ($fields = $cir->next()) {
	$data[] = array_fill(0, count($fields) + 2, '...');
    }
    $cir->close();
    
    $table = new html_table();
    $table->id = "uupreview";
    $table->attributes['class'] = 'generaltable';
    $table->tablealign = 'center';
    $table->summary = get_string('uploaduserspreview', 'tool_uploaduser');
    $table->head = array();
    $table->data = $data;
    
    $table->head[] = get_string('uucsvline', 'tool_uploaduser');
    foreach ($filecolumns as $column) {
	$table->head[] = $column;
    }
    $table->head[] = get_string('status');
    
    echo html_writer::tag('div', html_writer::table($table), array('class'=>'flexible-wrap'));    
    
} 