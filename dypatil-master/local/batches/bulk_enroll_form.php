<?php
// $Id: inscriptions_massives_form.php 352 2010-02-27 12:16:55Z ppollet $

require_once ($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/batches/lib.php');

 $hierarchy = new hierarchy();
 $batches = new local_batches(0);
 
class bulk_batch_enroll_form extends moodleform {

	function definition() {
	 global $CFG, $hierarchy, $PAGE,$batches ;
	 $mform = & $this->_form;
	 $batchid = $this->_customdata['batchid'];
	 $mode = $this->_customdata['mode'];
		
	$PAGE->requires->yui_module('moodle-local_batches-batches', 'M.local_batches.init_batches', array(array('formid' => $mform->getAttribute('id'))));
        if($batchid>0)
	 $batchinfo=$batches->get_specific_batchinformation($batchid);
	
	$availablefromgroup=array();
        $availablefromgroup[] =& $mform->createElement('radio', 'enroll', '','Add newstudents to Batch', 0);
        $availablefromgroup[] =& $mform->createElement('radio', 'enroll','', 'Add existing student to Batch', 1);
        $mform->addGroup($availablefromgroup, 'availablefromgroup', '', '', false);
	// used enable bydefault based on parameter mode ( from index page  button)
	if(isset($mode) && $mode){
 	   if($mode=='exists')
	   $mform->setDefault('enroll',1);
	   else{
	   if($mode=='new')
	   $mform->setDefault('enroll',0);	
		
	   }
		
	}
	
	
        $school = $hierarchy->get_assignedschools();
        if (is_siteadmin()) {
            $school = $hierarchy->get_school_items();
        }
        $parents = $hierarchy->get_school_parent($school);
       // $mform->addElement('header', 'settingsheader', get_string('createbatch', 'local_batches'));
        $count = count($school);
        $mform->addElement('hidden', 'count', $count);
        $mform->setType('count', PARAM_INT);
        //if ($count == 1) {
        //    /* ---registrar is assigned to only one school, display as static--- */
        //    foreach ($school as $scl) {
        //        $key = $scl->id;
        //        $value = $scl->fullname;
        //    }
        //    $mform->addElement('static', 'schools', get_string('select', 'local_collegestructure'), $value);
        //    $mform->addElement('hidden', 'schoolid', $key);
        //    $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$key AND visible=1", null, '', 'id,fullname', '--Select--');
        //    $mform->addElement('select', 'programid', get_string('selectprogram', 'local_programs'), $program);
        //    $mform->addRule('programid', get_string('missingprogram', 'local_programs'), 'required', null, 'client');
        //} else {
            $mform->addElement('select', 'schoolid', get_string('select', 'local_collegestructure'), $parents);
            $mform->addRule('schoolid', get_string('missingschool', 'local_collegestructure'), 'required', null, 'client');
	    if(isset($batchinfo->schoolid) && $batchinfo->schoolid>0)
	    $mform->setDefault('schoolid',$batchinfo->schoolid);
	//}   
      
        $mform->addElement('hidden', 'addprogram');
        $mform->setType('addprogram', PARAM_RAW);
	
	$batcheslist=$batches->get_batches_list();
	$mform->addElement('select', 'batchid', get_string('batches', 'local_batches'), $batcheslist);
        $mform->addRule('batchid', get_string('missingbatches', 'local_batches'), 'required', null, 'client');
	if(isset($batchinfo->batchid) && $batchinfo->batchid>0)
	$mform->setDefault('batchid',$batchinfo->batchid);
		
	$mform->addElement('hidden', 'addcurriculum');
        $mform->setType('addcurriculum', PARAM_RAW);
	
	$mform->addElement('hidden', 'addbatches');
        $mform->setType('addbatches', PARAM_RAW);
       
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));
	

       // $mform->disabledIf('availablefromgroup', 'availablefromenabled');
	
	
        //$mform->addElement('radio', 'deleteolditems', '', 'new', true);
        //
        //$mform->addElement('radio', 'deleteolditems', '', 'existst');
	
		//$mform->addElement('header', 'general', get_string('bulkenrol','block_learning_plan')); //fill in the data depending on page params
		//later using set_data
		$mform->addElement('filepicker', 'attachment', get_string('location', 'enrol_flatfile'));

		$mform->addRule('attachment', null, 'required');
		
		$choices = csv_import_reader::get_delimiter_list();
        $mform->addElement('select', 'delimiter_name', get_string('csvdelimiter', 'tool_uploaduser'), $choices);
        if (array_key_exists('cfg', $choices)) {
            $mform->setDefault('delimiter_name', 'cfg');
        } else if (get_string('listsep', 'langconfig') == ';') {
            $mform->setDefault('delimiter_name', 'semicolon');
        } else {
            $mform->setDefault('delimiter_name', 'comma');
        }

       // $choices = textlib::get_encodings();
        $choices = core_text::get_encodings();
        $mform->addElement('select', 'encoding', get_string('encoding', 'tool_uploaduser'), $choices);
        $mform->setDefault('encoding', 'UTF-8');

//         $mform->addElement('hidden','roleassign');
//		$mform->setDefault('roleassign', 5); //student
//		$mform->setType('roleassign','int');
//		$ids = array (
//			'idnumber' => get_string('idnumber', 'local_mass_enroll'),
//			'username' => get_string('username', 'local_mass_enroll'),
//			'email' => get_string('email')
//		);
//		$mform->addElement('select', 'firstcolumn', get_string('firstcolumn', 'block_learning_plan'), $ids);
//		$mform->setDefault('firstcolumn', 'username');

		$mform->addElement('selectyesno', 'mailreport', get_string('mailreport', 'local_batches'));
		$mform->setDefault('mailreport', 1);

		$this->add_action_buttons(true, get_string('enroll_batch', 'local_batches'));
	}


	
	
    function definition_after_data() {
        global $DB,$batches;
        global $hierarchy, $mybatch;
        $mform = $this->_form;
       // $id = $this->_customdata['id'];
	
	$batchid = $this->_customdata['batchid'];
	if($batchid>0)
	 $batchinfo=$batches->get_specific_batchinformation($batchid);

        
        $schoolvalue = $mform->getElementValue('schoolid');
        $program = array();
          if (isset($schoolvalue) &&  $schoolvalue[0]>0 ) {            
            $program = $hierarchy->get_records_cobaltselect_menu('local_program', "schoolid=$schoolvalue[0] AND visible=1", null, '', 'id,fullname', '--Select--');     
            $myselect = $mform->createElement('select', 'program', get_string('program', 'local_programs'), $program);
            $mform->insertElementBefore($myselect, 'addprogram');
            $mform->addRule('program', get_string('missingprogram', 'local_programs'), 'required', null, 'client');
	    if(isset($batchinfo->programid) && $batchinfo->programid>0)
	    $mform->setDefault('program',$batchinfo->programid);
            $programvalue = $mform->getElementValue('program');
          }
	  if (isset($programvalue) &&  $programvalue[0]>0 ) {
		$uploadingmethod = $mform->getElementValue('availablefromgroup');
		//----------------------student value------------------------------------
	
	        // -it indicates that uploading new  students list to btach   
	        if( $uploadingmethod['enroll']==0){
		//------------get curriculum list--------------------------------------
		$curriculumlist=$batches->get_active_curriculumlist($schoolvalue[0],$programvalue[0]);
                $curriculumelement = $mform->createElement('select', 'curriculumid', get_string('curriculum', 'local_cobaltcourses'), $curriculumlist);
                $mform->insertElementBefore($curriculumelement, 'addcurriculum');
                $mform->addRule('curriculumid', get_string('missingcurriculum', 'local_batches'), 'required', null, 'client');
		 if(isset($batchinfo->curriculumid) && $batchinfo->curriculumid>0)
	         $mform->setDefault('curriculumid',$batchinfo->curriculumid);
		 $curriculumvalue = $mform->getElementValue('curriculumid');
		}
		
		
		//---------------- get batches list-------------------------------------
		$batcheslist=$batches->get_batches_list($schoolvalue[0],$programvalue[0]);
		if(isset($curriculumvalue) &&  $curriculumvalue[0]>0)
		$batcheslist=$batches->get_batches_list($schoolvalue[0],$programvalue[0],$curriculumvalue[0]);	
                $batchelement = $mform->createElement('select', 'batchid', get_string('batches', 'local_batches'), $batcheslist);
                $mform->insertElementBefore($batchelement, 'addbatches');
                $mform->addRule('batchid', get_string('missingbatches', 'local_batches'), 'required', null, 'client');
		if(isset($batchinfo->batchid) && $batchinfo->batchid>0)
	        $mform->setDefault('batchid',$batchinfo->batchid);
                $mform->removeElement('batchid');
           }
	  
	  
        } // end of definition_after_data
	
	
	
	function validation($data, $files) {
		$errors = parent :: validation($data, $files);
		return $errors;
	}
	
	
}