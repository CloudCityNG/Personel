<?php

// in renderer.php:
class block_universitystructure_menu {
 
    //public function __construct(stdclass $submission, $anonymous = false, array $attachments = null) {
    //    // here the widget is prepared and all necessary logic is performed
    //    if ($anonymous) {
    //        $this->authorname = get_string('anonymousauthor', 'workshop');
    //    } else {
    //        $this->authorname = fullname(...);
    //    }
    //}
    	  public $myopentag = '';
          public $startcategories = array();
	  public $categories = array();
    	  public $content = array();
	  public $items = array();
	  public $active_module_course = null;
		 public $is_active = false;
		 public $mybranch = array();
		 public $icon ='';
		 public $topnodelevel = 0;
		 public $sn_home=null;
    
    
    
      public function universitystructure_collect_items ($myclass, $myid, $myname, $mydepth, $mytype, $mypath, $myicon, $myvisibility) {
		$item = array('myclass'=>$myclass, 'myid'=>$myid, 'myname'=>$myname, 'mydepth'=>$mydepth, 'mytype'=>$mytype, 'mypath'=>$mypath, 'myicon'=>$myicon, 'myvisibility'=>$myvisibility);

		return $item;
	}
        
     /**

	 * Looks at the navigation items and checks if
	 * the actual item is active*
         * @param array $alist (array  sublinks of tab )
	 * @return  returns string (class) active_tree_node if acitv otherwise null
	 */
	public function universitystructure_get_class_if_active ($myid, $mytype, $alist=null,$mydepth=null) {
               
		global $CFG, $PAGE,$USER;    
		$myclass = null;                
                $addressbarurl=$_SERVER['REQUEST_URI']; 
                $addressurl_components=parse_url($addressbarurl);  

		//$url=$PAGE->url;
		$url= $addressurl_components['path'];	
		$url_elements=explode('/',$url);
		//print_object($url_elements);
	        $count = sizeof($url_elements);	// used to  make links to active dynamically
		if(empty($url_elements[$count-1]))
		    $url_elements[$count-1]='index.php';      
		$url_components=parse_url($url);  
                

                
                $url_elements[$count-2]=(isset($url_elements[$count-2])?$url_elements[$count-2]:null);			
	        $url_elements[$count-1]=(isset($url_elements[$count-1])?$url_elements[$count-1]:null);
                
                if(!empty($url_components)){
                $editadvanced=explode('/',$url_components['path']);
             
	      
		if(!empty($url_components['query']))
		$query=explode('&', $url_components['query']);
		} 	
		
		if ($mytype == null && ($PAGE->pagetype <> 'site-index' && $PAGE->pagetype <>'admin-index')) {
                   
			return $myclass;
		}
		elseif (($myid == 'school' ||  $myid =='mylearning' || $myid =='studentprofile' )  && ($PAGE->pagetype == 'site-index' || $PAGE->pagetype =='admin-index')) {
			$myclass = 'active_tree_node';
			return $myclass;
		}
		elseif (!$mytype == null && ($PAGE->pagetype == 'site-index' || $PAGE->pagetype =='admin-index')) {                    
			return $myclass;
		}          
                
		// enable or activating two level hierarichcal tree level		
		elseif( ($mytype == 'u_root' || $mytype == 'u_leaf' || $myid=='departments' )&&($mydepth==2 ) ){
		    $myclass = ' active_tree_node';
			return $myclass;
		}
		// enable or activating department and semester program levels
		elseif(( $myid=='departments' || $myid =='programs' || $myid =='semesters' )&& $mydepth==3  ){
		    $myclass = ' active_tree_node';
			return $myclass;
		}
                //-------------- by deafault activating student link------------------
                elseif($myid == 'mylearning' && $mytype=='u_leaf'){
                    $myclass = ' active_tree_node';
			return $myclass;
                    
                }
                elseif(isloggedin()){
                $context = context_user::instance($USER->id);   
                // only for student
                 
                 if (has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()) {
                    if($myid == 'editadvanced.php' && $mytype=='u_leaf'){
                     $myclass = ' active_tree_node';
		  	return $myclass;
                    
                   }
                 }
                }
                //---------------- end of student link-----------------------
		
		else {	
      
		  if( isset($editadvanced[3]) && $editadvanced[3]==$myid  && $mytype=='u_root'  ){
		   	 $myclass = ' active_tree_node';				
			 return $myclass;
		  }
		   if( isset($editadvanced[3]) && $editadvanced[3]==$myid  && $mytype=='u_leaf' ){		   
		   	$myclass = ' active_tree_node';				
			 return $myclass;
		  } 
                  if(!empty($alist)){ 
                     // To active leaf nodes(sublinks of tab view)         
                     if($myid=='myclasses' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$alist ))
                     {                    
                         $myclass = ' active_tree_node';                         
			 return $myclass;                         
                     }       
                   
                     else{		
                    if( isset($editadvanced[3]) && $editadvanced[3]==$myid && in_array($editadvanced[4], $alist)){                        
		   	 $myclass = ' active_tree_node';				
			 return $myclass;
		       
                        }
                   
                    }
                  }  // end of if      
		  
		
		
                    // used to 	active root node		
			if($url_elements[$count-3]=='local'&& $myid == $url_elements[$count-2] && $mytype=='u_root' ){
				$myclass = ' active_tree_node';				
				return $myclass;
			}

	          // used to active leaf nodes		  
			if($url_elements[$count-3]=='local' && $myid == $url_elements[$count-2] && $mytype==$url_elements[$count-1]){
                 
				$myclass = ' active_tree_node ';				
				return $myclass;
			}
			
			$gardes_links=array('gradeletter','examtype','lecturetype','gradesubmission');
			$myprofile_links=array('profile.php','changeprofile.php','change_password.php');
			$manage_user=array('users/user.php','users/index.php','users/upload.php','users/info.php');
			$myclasses_links=array('classroomresources/timetable.php','scheduleexam/index.php','gradesubmission/instructor.php','courseregistration/myclasses.php','courseregistration/mycur.php');
			$course_registration_links = array('courseregistration/registrar.php','adddrop/registrar.php','adddrop/index.php','courseregistration/index.php','courseregistration/mentor.php','adddrop/mentor.php');
                        $my_academics=array('courseregistration/mycur.php','courseregistration/stuclasses.php','courseregistration/myclasses.php','scheduleexam/index.php','myacademics/transcript.php','academiccalendar/index.php','evaluations/index.php');
			$cobaltsettings=array( 'cobaltsettings/category_level.php','cobaltsettings/school_settings.php','cobaltsettings/view_gpasettings.php','prefix/index.php');
			// used to active root(parent) node , when associated leaf node is active 
			if($myid == 'grades' && $mytype=='u_root' && in_array($url_elements[$count-2],$gardes_links )){
				$myclass = ' active_tree_node';				
				return $myclass;
			}
			
			
			elseif($myid == 'editadvanced.php' && $mytype=='u_leaf' && in_array($url_elements[$count-1],$myprofile_links )){
			     
				$myclass = ' active_tree_node';				
				return $myclass;
			}
			
			
			elseif($myid == 'manage_users' && $mytype=='u_root' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$manage_user )){
				$myclass = ' active_tree_node';				
				return $myclass;
			}
			elseif($myid == 'cobaltsettings' && $mytype=='u_root' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$cobaltsettings )){
                            $myclass = ' active_tree_node';				
				return $myclass;
			}
                        elseif($url_elements[$count-2]=='prefix'){
                            if($myid == 'cobaltsettings' && $mytype=='u_root') {
                               $myclass = ' active_tree_node';				
				return $myclass;
                         }     
                        }
			elseif($myid == 'myclasses' && $mytype=='u_leaf' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$myclasses_links )){
				$myclass = ' active_tree_node';				
				return $myclass;
			}
                        elseif($myid == 'courseregistrations' && $mytype=='u_leaf' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$course_registration_links )){
			   	$myclass = ' active_tree_node';			
				return $myclass;
			}
                        
		       elseif($myid == 'myacademics' && $mytype=='u_leaf' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$my_academics)){
				$myclass = ' active_tree_node';				
				return $myclass;
		         }
			 else{
		         $student_cr=array('courseregistration/index.php');			
		         if($myid == 'student_coursereg' && $mytype=='u_root' && in_array($url_elements[$count-2].'/'.$url_elements[$count-1],$student_cr)){
				$myclass = ' active_tree_node';				
				return $myclass;
		         }
	  	
	           }		
	         
		} 
		
	  } 
          
          
          function get_filelisting($dirname){
              global $CFG,$DB;
              $output=array();
                $directory=$CFG->dirroot . '/local/'.$dirname.'/';   
                //get all image files with a .jpg extension.
                $files = glob($directory . "*.php");
               // print_object($files);
                foreach($files as $file){          
                    $explodedfile=explode('/',$file);
                    $count=sizeof($explodedfile);
                    $output[]=$explodedfile[$count-1];
                    
                    
                }         
                return $output;
              
          }
        
	public function checking_capabilities($pluginname, $extracapabilities = null){		
           global $CFG, $OUTPUT,$DB, $USER;
		   if($pluginname=='classes')
		   $pluginname='clclasses';
		   
           $systemcontext = context_system::instance();
           $cap = array('local/'.$pluginname .':manage',
			'local/'. $pluginname .':delete',
			'local/'. $pluginname .':create',
			'local/'. $pluginname .':update',
			'local/'. $pluginname .':visible',
			'local/'. $pluginname .':view');
	   if( $extracapabilities){
		$cap =$cap + $extracapabilities;
	   }
           if (has_any_capability($cap, $systemcontext)) {
		return true;
	   }
	   else
	        return false;
		
	}
	
	public function nohome_menu(){
	  global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	  $myclass = $this->universitystructure_get_class_if_active(null, null);
		$items[]=$this->universitystructure_collect_items('active_tree_node', null, $this->sn_home, null, 'nohome', 0, null, null);
		
           return $items;		
	  
	}// end of function
	
        
        public function settings_menu(){
            
               global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	       $systemcontext = context_system::instance();
	       $usercontext = context_user::instance($USER->id);
               
		
		$myclass = $this->universitystructure_get_class_if_active('school','u_root',null,2);
		$items[]=$this->universitystructure_collect_items($myclass,'null',get_string('school','block_universitystructure'),2,'u_root', null ,'pix/sprites/N-university settings', 0);
                
		 $sublinks_collegestructure= $this->get_filelisting('collegestructure');		
		$myclass = $this->universitystructure_get_class_if_active('collegestructure','index.php',$sublinks_collegestructure);
		$items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('add/editschool','block_universitystructure'),3,'u_leaf',$CFG->wwwroot.'/local/collegestructure/index.php' ,'pix/sprites/N-university settings', 2);
		
		 if(has_capability('local/cobaltsettings:manage', $systemcontext) || has_capability('local/cobaltsettings:view', $systemcontext) ){
		  $myclass = $this->universitystructure_get_class_if_active('cobaltsettings', 'u_root');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('universitysettings','local_collegestructure'),3,'u_leaf', null ,'pix/sprites/N-university settings', 0);           
          
                  $sublinks_cobaltsettings=array('category_level.php','view_categorylevel.php','default_entities.php');
		  $myclass = $this->universitystructure_get_class_if_active('cobaltsettings', 'category_level.php', $sublinks_cobaltsettings);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('cobaltLMSentitysettings','local_collegestructure'),4,'module',$CFG->wwwroot.'/local/cobaltsettings/view_categorylevel.php', null, 1);
		  
		  $myclass = $this->universitystructure_get_class_if_active('cobaltsettings', 'school_settings.php');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('schoolsettings','local_collegestructure'),4,'module',$CFG->wwwroot.'/local/cobaltsettings/school_settings.php', null, 1);
		  
                  $sublinks_gpasettings=array('view_gpasettings.php','gpa_settings.php','info.php');
		  $myclass = $this->universitystructure_get_class_if_active('cobaltsettings','view_gpasettings.php',$sublinks_gpasettings);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('GPA/CGPAsettings','local_collegestructure'),4,'module',$CFG->wwwroot.'/local/cobaltsettings/view_gpasettings.php', null, 1);                 
	
		  
		  $sublinks_prefixsettings=array('prefix2.php','entity.php','info.php');
                  $myclass = $this->universitystructure_get_class_if_active('prefix', 'index.php',$sublinks_prefixsettings);
	          $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('PrefixandSuffix','local_collegestructure'),4,'module', $CFG->wwwroot.'/local/prefix',null, 1);					  
		  
		}
                
              return $items;  
            
         }// end of function
         
        public function department_menu(){
               global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	       $systemcontext = context_system::instance();
	       $usercontext = context_user::instance($USER->id);
            if($this->checking_capabilities('departments')){		
                  
		  $myclass = $this->universitystructure_get_class_if_active('departments','u_leaf',null, 3);		
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('department','local_departments'),3,'u_leaf', $CFG->wwwroot.'/local/departments' , 'pix/sprites/N-Departments', 0); 			
		
		  $myclass = $this->universitystructure_get_class_if_active('departments', 'index.php', array('departments.php'));		
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editdepartment','block_universitystructure'),4,'module', $CFG->wwwroot.'/local/departments' , null, 1);
		  
		 if($this->checking_capabilities('cobaltcourses')){
                  $sublinks_cobaltc= $this->get_filelisting('cobaltcourses');    
		  $myclass = $this->universitystructure_get_class_if_active('cobaltcourses', 'index.php', $sublinks_cobaltc);	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('cobaltcourses','local_cobaltcourses'),4,'module', $CFG->wwwroot.'/local/cobaltcourses' ,null, 1);	
        	 }		 
		 
		  $myclass = $this->universitystructure_get_class_if_active('departments', 'display_instructor.php');		
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('instructor', 'local_departments'),4,'module', $CFG->wwwroot.'/local/departments/display_instructor.php' , null, 1);		  
		  
		  //$myclass = $this->universitystructure_get_class_if_active('departments', 'assign_school.php');		
		  //$items[]=$this->universitystructure_collect_items($myclass,'null',get_string('assignschool', 'local_collegestructure'),4,'module', $CFG->wwwroot.'/local/departments/assign_school.php' , null, 1);
		  
		
		 }
            if(empty($items))
            return array();
	    else 
            return $items;
            
            
        } // end of functions
        
        
        public function gradesettings_menu(){
            
             global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
              if(has_capability('local/gradeletter:manage', $systemcontext) || has_capability('local/gradeletter:view', $systemcontext) ){		 
		   $myclass = $this->universitystructure_get_class_if_active('grades', 'u_root',null, 3);		
		   $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('stuview','local_gradesubmission'),3,'u_leaf', null, 'pix/grades',0);		
	        }
		
	        if(has_capability('local/gradeletter:manage', $systemcontext) || has_capability('local/gradeletter:view', $systemcontext) ){
                    $sublinks_gradeletter= $this->get_filelisting('gradeletter'); 
		    $myclass = $this->universitystructure_get_class_if_active('gradeletter', 'index.php',$sublinks_gradeletter);
                    $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('gradeletter','local_gradesubmission'),4,'module',$CFG->wwwroot.'/local/gradeletter', null, 1);
	        }		

	       if(has_capability('local/examtype:manage', $systemcontext) || has_capability('local/examtype:view', $systemcontext) ){
                    $sublinks_examtype= $this->get_filelisting('examtype'); 
		    $myclass = $this->universitystructure_get_class_if_active('examtype', 'index.php',$sublinks_examtype);
                    $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('examtype', 'local_examtype'),4,'module',$CFG->wwwroot.'/local/examtype', null, 1);		
	        }
		
	         if(has_capability('local/lecturetype:manage', $systemcontext) || has_capability('local/lecturetype:view', $systemcontext) ){
                    $sublinks_lecturetype= $this->get_filelisting('lecturetype'); 
		    $myclass = $this->universitystructure_get_class_if_active('lecturetype', 'index.php', $sublinks_lecturetype);
                    $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('lecturetypename','local_lecturetype'),4,'module',$CFG->wwwroot.'/local/lecturetype', null, 1);	

	        }
            if(empty($items))
            return array();
	    else 
            return $items;
            
        }//end offunction
        
        
    public function program_menu(){
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
         if($this->checking_capabilities('programs')){			
  
		  $myclass = $this->universitystructure_get_class_if_active('programs', 'u_root',null,3);	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('programs','local_programs'),3,'u_root', $CFG->wwwroot.'/local/programs','pix/sprites/N_PROGRAMS', 0);
		  
	          $sublinks_prg= $this->get_filelisting('programs');   
		  $myclass = $this->universitystructure_get_class_if_active('programs', 'index.php',$sublinks_prg);	
		  $items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('add/editprogram','block_universitystructure'),4,'u_leaf', $CFG->wwwroot.'/local/programs','pix/sprites/N_PROGRAMS', 2);
		  
		if($this->checking_capabilities('curriculum')){
                 // $sublinks_cur= $this->get_filelisting('curriculum');     
		  $myclass = $this->universitystructure_get_class_if_active('curriculum', 'u_leaf');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_curriculum'),4,'u_leaf', $CFG->wwwroot.'/local/curriculum' ,'pix/sprites/N-curriculum', 0);
		  
	          $sublinks_cur= $this->get_filelisting('curriculum');     
		  $myclass = $this->universitystructure_get_class_if_active('curriculum', 'index.php', $sublinks_cur);	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editcurriculum','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/curriculum' ,null, 1);
		  
		}
		
		if($this->checking_capabilities('curriculum')){
                  $sublinks_cur= $this->get_filelisting('curriculum');     
		  $myclass = $this->universitystructure_get_class_if_active('curriculum', 'index.php', $sublinks_cur);	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('manageplan', 'local_curriculum'),5,'module', $CFG->wwwroot.'/local/curriculum' ,null, 1);/*/viewcurriculum.php*/
		}
		
//		if($this->checking_capabilities('curriculum')){
//                  $sublinks_cur= $this->get_filelisting('curriculum');     
//		  $myclass = $this->universitystructure_get_class_if_active('curriculum', 'index.php', $sublinks_cur);	
//		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('addplan', 'local_curriculum'),5,'module', $CFG->wwwroot.'/local/curriculum/plan.php' ,null, 1);
//		}
		
	        if($this->checking_capabilities('modules')){
                //  $sublinks_modu= $this->get_filelisting('modules');   
		  $myclass = $this->universitystructure_get_class_if_active('modules', 'u_leaf');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_modules'),4,'u_leaf', $CFG->wwwroot.'/local/modules' ,'pix/sprites/N-Modules', 0);
		  
		   $sublinks_modu= $this->get_filelisting('modules');   
		  $myclass = $this->universitystructure_get_class_if_active('modules', 'index.php',$sublinks_modu);	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editmodule','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/modules' ,null, 1);	
	        }  
	        
		if(has_capability('local/admission:manage', $systemcontext)){
                  $sublinks_admission= $this->get_filelisting('admission');   
		  $myclass = $this->universitystructure_get_class_if_active('admission','u_leaf');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_admission'),4,'u_leaf', $CFG->wwwroot.'/local/admission/viewapplicant.php' , 'pix/sprites/N-Aprooved User', 0);	

	        }
		if(has_capability('local/admission:manage', $systemcontext)){
                  $sublinks_admission= $this->get_filelisting('admission');   
		  $myclass = $this->universitystructure_get_class_if_active('admission', 'viewapplicant.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('Approveapplicants','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/admission/viewapplicant.php' , null, 1);	

	        }
		if(has_capability('local/admission:manage', $systemcontext)){
                  $sublinks_admission= $this->get_filelisting('admission');   
		  $myclass = $this->universitystructure_get_class_if_active('admission', 'uploaduser.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('enrollstudent','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/admission/uploaduser.php' , null, 1);	

	        }
		if(has_capability('local/admission:manage', $systemcontext)){
                  $sublinks_admission= $this->get_filelisting('admission');   
		  $myclass = $this->universitystructure_get_class_if_active('admission', 'uploadapplicant.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('uploadapplicant','local_admission'),5,'module', $CFG->wwwroot.'/local/admission/uploadapplicant.php' , null, 1);	

	        }
		
	
		}
        
        if(empty($items))
            return array();
	    else 
            return $items;
    } //end of function
         
	 public function semester_menu(){
        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext =  context_system::instance();
	     $usercontext = context_user::instance($USER->id);
	     
	    if($this->checking_capabilities('semesters')){
                 // $sublinks_sem= $this->get_filelisting('semesters');   
		  $myclass = $this->universitystructure_get_class_if_active('semesters', 'u_root',null, 3);	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('semesters','local_semesters'),3,'u_root', $CFG->wwwroot.'/local/semesters' , 'pix/sprites/N-semester', 0);
		  
	          $sublinks_sem= $this->get_filelisting('semesters');   
		  $myclass = $this->universitystructure_get_class_if_active('semesters', 'index.php', $sublinks_sem);	
		  $items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('add/editsemesters','block_universitystructure'),4,'u_leaf', $CFG->wwwroot.'/local/semesters' , 'pix/sprites/N-semester', 2);
		  
	        }
            if($this->checking_capabilities('academiccalendar')){
                  $sublinks_academiccal= $this->get_filelisting('academiccalendar');      
		  $myclass = $this->universitystructure_get_class_if_active('academiccalendar', 'u_leaf');		  
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_academiccalendar'),4,'u_leaf', $CFG->wwwroot.'/local/academiccalendar' ,'pix/N-Acalender', 0);
		  
	         // $sublinks_academiccal= $this->get_filelisting('academiccalendar');      
		  $myclass = $this->universitystructure_get_class_if_active('academiccalendar', 'index.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editacademiccalendar','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/academiccalendar/index.php',null, 1);
		  
		  
	          //$sublinks_academiccal= $this->get_filelisting('academiccalendar');      
		
		  $myclass = $this->universitystructure_get_class_if_active('academiccalendar', 'edit_event.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null','Registration event',5,'module', $CFG->wwwroot.'/local/academiccalendar/edit_event.php' ,null, 1);
		  
		  // $sublinks_academiccal= $this->get_filelisting('academiccalendar');      
		  $myclass = $this->universitystructure_get_class_if_active('academiccalendar','edit_event.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null','Add/drop event',5,'module', $CFG->wwwroot.'/local/academiccalendar/edit_event.php' ,null, 1);	
	        }		
		
           if($this->checking_capabilities('classes')){
                 // $sublinks_classes= $this->get_filelisting('classes');    
		  $myclass = $this->universitystructure_get_class_if_active('classes', 'u_leaf');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('classesmanagement','local_clclasses'),4,'u_leaf', $CFG->wwwroot.'/local/clclasses' ,'pix/sprites/class manegment', 0);
		  
		  $sublinks_classes= $this->get_filelisting('classes');    
		  $myclass = $this->universitystructure_get_class_if_active('classes', 'index.php',$sublinks_classes);	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit', get_string('add/editclasses','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/clclasses' ,null, 1);
		  
		  $sublinks_classes= $this->get_filelisting('classes');    
		  $myclass = $this->universitystructure_get_class_if_active('classes', 'index.php',$sublinks_classes);	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('enrollstudent','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/clclasses' ,null, 1);
		  
		//   $sublinks_classes= $this->get_filelisting('classes');    
		//  $myclass = $this->universitystructure_get_class_if_active('classes', 'index.php',$sublinks_classes);	
		//  $items[]=$this->universitystructure_collect_items($myclass,'null','Evaluation',5,'module', $CFG->wwwroot.'/local/clclasses' ,null, 1);
		  
		  $myclass = $this->universitystructure_get_class_if_active('scheduleexam', 'index.php');		 
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('scheduledexams','local_scheduleexam'),5,'module', $CFG->wwwroot.'/local/scheduleexam',null, 1);
		    
		  $myclass = $this->universitystructure_get_class_if_active('gradesubmission', 'index.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_gradesubmission'),5,'module',$CFG->wwwroot.'/local/gradesubmission',null, 1);  		  
		  
              	}
		   if(isset($items)){
	          $timetablelinks  = $this->timetable_menu();			
		  $items= array_merge($items,$timetablelinks);
		   }
               if(has_capability('local/courseregistration:manage', $systemcontext)){
                  $myclass = $this->universitystructure_get_class_if_active('courseregistrations', 'u_leaf');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_courseregistration'),4,'u_leaf', null ,'pix/sprites/N-course registration', 0);
		  
		  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'registrar.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approveenrolledcourses','local_courseregistration'),5,'module', $CFG->wwwroot.'/local/courseregistration/registrar.php?current=pending' , null, 1);	
                  

                  $myclass = $this->universitystructure_get_class_if_active('adddrop', 'registrar.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approveadddropcourses','local_courseregistration'),5,'module', $CFG->wwwroot.'/local/adddrop/registrar.php?current=pending' , null, 1);	
		  
	        }
		
		if(empty($items))
            return array();
	    else 
            return $items;
		
	 }// end of function
	 
	 
	 public function timetable_menu(){
              global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	      $systemcontext = context_system::instance();
	      if(isloggedin())
	      $usercontext = context_user::instance($USER->id);
	  
	  
	         if($this->checking_capabilities('timetable')){
                 // $sublinks_classes= $this->get_filelisting('classes');    
		  $myclass = $this->universitystructure_get_class_if_active('timetable', 'u_leaf');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('timetablemanagement','block_universitystructure'),4,'u_leaf', $CFG->wwwroot.'/local/timetable' ,'pix/sprites/class manegment', 0);
		  
		 
		  $myclass = $this->universitystructure_get_class_if_active('timetable', 'index.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/edittimeintervals','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/timetable' ,null, 1);
		  
   
		  $myclass = $this->universitystructure_get_class_if_active('timetable', 'classtype.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editclasstypes','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/timetable/classtype.php' ,null, 1);   
		  		  
		 //  $sublinks_classes= $this->get_filelisting('classes');    
		  $myclass = $this->universitystructure_get_class_if_active('timetable', 'scheduleclassview.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('add/editscheduleclass','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/timetable/scheduleclassview.php' ,null, 1);
		  
		  $myclass = $this->universitystructure_get_class_if_active('timetable', 'calendarview.php');		 
		  $items[]=$this->universitystructure_collect_items($myclass,'add/edit',get_string('calendarview','block_universitystructure'),5,'module', $CFG->wwwroot.'/local/timetable/calendarview.php',null, 1);
		    			  
		  
              	} // end of capabilities
		return $items;
	  
	 }// end of function
	 
	  public function resourcemgt_menu(){
           global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
	     
	     	        if(has_capability('local/classroomresources:view', $systemcontext)){
			
		  $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'u_root',null,3);
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('resourcemanagement','local_classroomresources'),3,'u_leaf', null ,'pix/sprites/N-resource', 0);	
			
                  $sublink_building=array('index.php','building.php','infobuilding.php');
		  $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'index.php',$sublink_building);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('managebuildings','local_classroomresources'),4,'module',$CFG->wwwroot.'/local/classroomresources/index.php', null, 1);
	  
                  $sublink_floor=array('floor.php','viewfloor.php','infofloor.php');
	          $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'viewfloor.php',$sublink_floor);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('managefloor','local_classroomresources'),4,'module',$CFG->wwwroot.'/local/classroomresources/viewfloor.php', null, 1);
		
                  $sublink_classroom=array('classroom.php','viewclassroom.php','infoclassroom.php');
		  $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'viewclassroom.php', $sublink_classroom);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('manageclass','local_classroomresources'),4,'module',$CFG->wwwroot.'/local/classroomresources/viewclassroom.php', null, 1);
		
                  $sublink_resource=array('resource.php','viewresource.php','inforesource.php','assignresource.php','view.php');
		  $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'viewresource.php', $sublink_resource);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('manageresource','local_classroomresources'),4,'module',$CFG->wwwroot.'/local/classroomresources/viewresource.php', null, 1);
		  
	
             }
	     
	    if(empty($items))
            return array();
	    else 
            return $items;
	  }// end of function
	 
	  
	  public function manageuser_menu(){
          global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
	            if(has_capability('local/users:manage', $systemcontext)){			
	          		
		  $myclass = $this->universitystructure_get_class_if_active('users', 'u_leaf',null,3);
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_users'),3,'u_leaf', null ,'pix/sprites/N-browse user', 0);		
		
                  $sublink_adduser=array('user.php','upload.php','info.php');
		  $myclass = $this->universitystructure_get_class_if_active('users', 'user.php', $sublink_adduser);
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('adduser','local_users'),4,'module',$CFG->wwwroot.'/local/users/user.php', null, 1);
		  
		  $myclass = $this->universitystructure_get_class_if_active('users', 'index.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('users:view','local_users'),4,'module',$CFG->wwwroot.'/local/users/index.php', null, 1);		
	        }
	         if(has_capability('local/assignmentor:view', $systemcontext)){
                  $sublinks_assignmentor= $this->get_filelisting('assignmentor');    
		  $myclass = $this->universitystructure_get_class_if_active('assignmentor', 'index.php', $sublinks_assignmentor);
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_assignmentor'),4,'module', $CFG->wwwroot.'/local/assignmentor',null, 1);				
	        }
	       if(empty($items))
            return array();
	    else 
            return $items;
	  }// end of function
	  
	  public function student_menu(){
	     global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	    
		//For students starts-----------------------------------------------------
		if(isloggedin()){
	        $context =  context_user::instance($USER->id);
	        if(has_capability('local/clclasses:enrollclass', $context) && !is_siteadmin()){
		 //----------------------starts of my academics------------------------------
		 
		 
		 $myclass = $this->universitystructure_get_class_if_active('mylearning', 'u_leaf');
		 $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('mylearning','local_myacademics'),2,'u_leaf', null ,'pix/academic', 0);		 
		
			  
		 $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'mycurplan.php');
                 $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('myplan','local_courseregistration'),3,'module',$CFG->wwwroot.'/local/courseregistration/mycurplans.php', null, 1);
		  
		/*  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'stuclasses.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('currentclasses','local_classes'),3,'u_leaf',$CFG->wwwroot.'/local/courseregistration/stuclasses.php', null, 1);
*/

		  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'myclasses.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('mycurrentplan','local_courseregistration'),3,'module',$CFG->wwwroot.'/local/courseregistration/myclasses.php', null, 1);
                  
                  $myclass = $this->universitystructure_get_class_if_active('timetable', 'calendarview.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('timetable_link','block_universitystructure'),3,'module',$CFG->wwwroot.'/local/timetable/calendarview.php', null, 1);
		  
                  $myclass = $this->universitystructure_get_class_if_active('scheduleexam', 'index.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('scheduledexams','local_scheduleexam'),3,'module',$CFG->wwwroot.'/local/scheduleexam', null, 1);
		  
		  $myclass = $this->universitystructure_get_class_if_active('myacademics', 'transcript.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('transcript','local_myacademics'),3,'module',$CFG->wwwroot.'/local/myacademics/transcript.php', null, 1);
		  
//		  $myclass = $this->universitystructure_get_class_if_active('academiccalendar', 'index.php');
//                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_academiccalendar'),3,'module',$CFG->wwwroot.'/local/academiccalendar', null, 1);
//		 
//	          $myclass = $this->universitystructure_get_class_if_active('timetable', 'calendarview.php');
//	          $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_timetable'),3,'module', $CFG->wwwroot.'/local/timetable/calendarview.php',null, 1);
//		 
//                  $myclass = $this->universitystructure_get_class_if_active('evaluations', 'index.php');		
//		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginnames','local_evaluations'),3,'module', $CFG->wwwroot.'/local/evaluations/index.php' , null, 1);
//		

                 //--------------------------------end of my academics--------------------------------------------------------------------------------
		 //--------------------------------course registration--------------------------------------------------------------------------------
//                  $myclass = $this->universitystructure_get_class_if_active('courseregistrations', 'u_leaf');
//		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('courseregistration','local_mentor'),2,'u_leaf', null ,'pix/sprites/N-course registration', 0);
//		  
//		  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'index.php');	
//		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('registrartoacourse','local_adddrop'),3,'module', $CFG->wwwroot.'/local/courseregistration/index.php' , null, 1);	
//                  
//                  $myclass = $this->universitystructure_get_class_if_active('adddrop', 'index.php');	
//		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_adddrop'),3,'module', $CFG->wwwroot.'/local/adddrop/index.php' , null, 1);	
//                 
                  //--------------------end of course registration----------------------------------------------------------------------------------------------
	 
		//--------------------Requests links----------------------------------------------------------------------------------------------------------
		  $myclass = $this->universitystructure_get_class_if_active('request', 'u_leaf');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('my','local_request'),2,'u_leaf', null ,'pix/request_1', 0);
		  
		  $myclass = $this->universitystructure_get_class_if_active('request', 'request_id.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('id','local_request'),3,'module',$CFG->wwwroot.'/local/request/request_id.php', null, 1);	
		
		  $myclass = $this->universitystructure_get_class_if_active('request', 'request_profile.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('profile','local_request'),3,'module',$CFG->wwwroot.'/local/request/request_profile.php', null, 1);	
		
		  $myclass = $this->universitystructure_get_class_if_active('request', 'request_transcript.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('transcript','local_myacademics'),3,'module',$CFG->wwwroot.'/local/request/request_transcript.php', null, 1);	
		
	          $myclass = $this->universitystructure_get_class_if_active('request', 'course_exem.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('course_exem','local_request'),3,'module',$CFG->wwwroot.'/local/request/course_exem.php', null, 1);	
                  
/*feb5*/
// $myclass = $this->universitystructure_get_class_if_active('onlinepayment', 'u_leaf');		
//		 $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('mypayments','local_onlinepayment'),2,'u_leaf', null , 'pix/sprites/N-Payments', 0);
//		 
//		 $myclass = $this->universitystructure_get_class_if_active('onlinepayment', 'studentstatus.php');		
//		 $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('paymentstatus','local_onlinepayment'),3,'module', $CFG->wwwroot.'/local/onlinepayment/studentstatus.php' , null, 1);
//                  
//		 $myclass = $this->universitystructure_get_class_if_active('onlinepayment', 'pendingpay.php');		
//		 $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('paynow','local_onlinepayment'),3,'module', $CFG->wwwroot.'/local/onlinepayment/pendingpay.php' , null, 1);
///**/
                  // $myclass = $this->universitystructure_get_class_if_active('evaluations', 'index.php');		
		  // $items[]=$this->universitystructure_collect_items($myclass,'null','Evaluations',2,'u_root', $CFG->wwwroot.'/local/evaluations/index.php' , null, 1);
		

//                  $myclass = $this->universitystructure_get_class_if_active('evaluations', 'index.php');		
//		  $items[]=$this->universitystructure_collect_items($myclass,'null','Evaluations',2,'u_root', $CFG->wwwroot.'/local/evaluations/index.php' , 'pix/sprites/N-evulutions', 1);
//		

		//-----------------------------end of requests links-------------------------------------------------------------------------------------------
	     }
		  }
	    //For students ends------------------------------------------------------	    
		    
		    
		if(empty($items))
            return array();
	    else 
            return $items; 
		    
	  }// end of function
	  
	  public function instructor_menu(){
		        global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
		    		//for instructor starts-------------------------------------------------------
                if(has_capability('local/clclasses:submitgrades', $systemcontext) ){                
                if(!is_siteadmin()){
		
				  
	          $myclass = $this->universitystructure_get_class_if_active('myclasses', 'u_leaf');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('myclasses','local_clclasses'),2,'u_leaf', null ,'pix/sprites/N-Class schedule', 0);
		  
                 // $sublink_currentclasses=array('courseregistration/mycur.php','myacademics/transcript.php','scheduleexam/index.php','courseregistration/myclasses.php'); , $sublink_currentclasses
		  $myclass = $this->universitystructure_get_class_if_active('myclasses', 'myclasses.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('currentclasses','local_clclasses'),3,'module',$CFG->wwwroot.'/local/courseregistration/myclasses.php',null, 1);        
		 
		//}	  
	          // $myclass = $this->universitystructure_get_class_if_active('classroomresources', 'timetable.php');
		//  // $items[]=$this->universitystructure_collect_items($myclass,'null','Scheduled Classes',3,'u_leaf', $CFG->wwwroot.'/local/classroomresources/timetable.php',null, 1);				
	        //  if(!is_siteadmin()){ 
		  $myclass = $this->universitystructure_get_class_if_active('scheduleexam', 'index.php');		 
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('scheduledexams','local_scheduleexam'),3,'module', $CFG->wwwroot.'/local/scheduleexam',null, 1);
		    
		  $myclass = $this->universitystructure_get_class_if_active('gradesubmission', 'instructor.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_gradesubmission'),3,'module',$CFG->wwwroot.'/local/gradesubmission/instructor.php',null, 1);        
		    
		    $myclass = $this->universitystructure_get_class_if_active('academiccalendar', 'index.php');
		  $items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('pluginname','local_academiccalendar'),2,'u_leaf', $CFG->wwwroot.'/local/academiccalendar','pix/N-Acalender', 2);              
		    
	           $myclass = $this->universitystructure_get_class_if_active('timetable', 'calendarview.php');
		   $items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('pluginname','local_timetable'),2,'u_leaf', $CFG->wwwroot.'/local/timetable/calendarview.php','pix/N-Acalender', 2);
		   
                  $myclass = $this->universitystructure_get_class_if_active('evaluations', 'index.php');		
		  $items[]=$this->universitystructure_collect_items($myclass,'nomodule',get_string('pluginnames','local_evaluations'),2,'u_leaf', $CFG->wwwroot.'/local/evaluations/index.php' , 'pix/sprites/N-evulutions', 2);
		     
		   }
		  
	        }
		    
             	if(empty($items))
            return array();
	    else 
            return $items;   
		    
	  }  // end of instructor menu function
	  
	  public function mentor_menu(){
               global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
	     
	     		/*for mentor starts*/
		
                 if(has_capability('local/clclasses:approvemystudentclasses', $systemcontext) && !is_siteadmin() ){       
				
		  $myclass = $this->universitystructure_get_class_if_active('mentor', 'index.php');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('mystudents','local_mentor'),2,'u_leaf', $CFG->wwwroot.'/local/mentor','pix/N-Acalender', 2);  
		  				 
            
		   $myclass = $this->universitystructure_get_class_if_active('courseregistrations', 'u_leaf');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('courseregistration','local_mentor'),2,'u_leaf', null ,'pix/sprites/N-course registration', 0);
		  
		  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'mentor.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approveenrolledcourses','local_mentor'),3,'module', $CFG->wwwroot.'/local/courseregistration/mentor.php?current=pending' , null, 1);	
                  
                  $myclass = $this->universitystructure_get_class_if_active('adddrop', 'mentor.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approvecourses','local_mentor'),3,'module', $CFG->wwwroot.'/local/adddrop/mentor.php?current=pending' , null, 1);		
		
                
                
                 }
		if(empty($items))
            return array();
	    else 
            return $items;		               // mentor ends
		    
	  }// end of function
	  
	  
	  public function global_submenu(){
		     global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext = context_system::instance();
	     $usercontext = context_user::instance($USER->id);
                  if (has_capability('local/clclasses:enrollclass', $usercontext) && !is_siteadmin())
	          $myclass = $this->universitystructure_get_class_if_active('editadvanced.php', 'u_leaf');
                  else 
                  $myclass = $this->universitystructure_get_class_if_active('studentprofile', 'u_leaf');
              
                  
              
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('myprofile','local_profilechange'),3,'u_leaf', null ,'pix/sprites/N-my profile', 0);
		  
		  $myclass = $this->universitystructure_get_class_if_active('users', 'profile.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('viewprofile','local_profilechange'),4,'module',$CFG->wwwroot.'/local/users/profile.php', null, 1);
		  
		  if(is_siteadmin()){
		  $myclass = $this->universitystructure_get_class_if_active('user', 'editadvanced.php');		  
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_profilechange'),4,'module',$CFG->wwwroot.'/user/editadvanced.php?id='.$USER->id.'', null, 1);	
		  }
		  else {
		  $myclass = $this->universitystructure_get_class_if_active('profilechange', 'changeprofile.php');		  
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('pluginname','local_profilechange'),4,'module',$CFG->wwwroot.'/local/profilechange/changeprofile.php', null, 1);	
		  }	
		
		  $myclass = $this->universitystructure_get_class_if_active('users', 'change_password.php');
                  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('changepasswrd','local_profilechange'),4,'module',$CFG->wwwroot.'/local/users/change_password.php', null, 1);	
		
                 //  print_object($items);
              if(empty($items))
            return array();
	    else 
            return $items;
		    
	  }// end of function
	  
	  
	  public function mentorcuminstructor_menu(){
		     global $CFG, $USER, $DB, $OUTPUT, $PAGE;
	     $systemcontext =context_system::instance();
	     $usercontext = context_user::instance($USER->id);
		         if(has_capability('local/clclasses:submitgrades', $systemcontext) && has_capability('local/classes:approvemystudentclasses', $systemcontext)  && !is_siteadmin()){
		  $myclass = $this->universitystructure_get_class_if_active('courseregistrations', 'u_leaf');
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('courseregistration','local_mentor'),2,'u_leaf', null ,'pix/sprites/N-course registration', 0);
		  
		  $myclass = $this->universitystructure_get_class_if_active('courseregistration', 'registrar.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approveenrolledcourses','local_courseregistration'),3,'module', $CFG->wwwroot.'/local/courseregistration/registrar.php?current=pending' , null, 1);	
                  
                  $myclass = $this->universitystructure_get_class_if_active('adddrop', 'index.php');	
		  $items[]=$this->universitystructure_collect_items($myclass,'null',get_string('approveadddropcourses','local_courseregistration'),3,'module', $CFG->wwwroot.'/local/adddrop/mentor.php?current=pending' , null, 1);	
	   }
	   
	    if(empty($items))
            return array();
	    else 
            return $items;
		    
	  }
	  
	  
    }// end of class
    
    
    

 
 
 
?>